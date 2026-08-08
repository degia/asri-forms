<?php

namespace App\Livewire\Admin\Employees;

use App\Helpers\ActivityLogger;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Site;
use App\Models\SubDepartement;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterSearch = '';
    public string $filterSite = '';
    public string $filterPosition = '';
    public string $filterStatus = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public bool $showDeleteModal = false;
    public ?string $deleteEmployeeId = null;
    public string $deleteEmployeeName = '';
    public array $selected = [];
    public bool $showBulkDeleteModal = false;
    public bool $showBulkEditModal = false;
    public string $bulkEditField = '';
    public string $bulkEditValue = '';
    public bool $showAssetsModal = false;
    public array $viewAssets = [];
    public string $viewAssetsEmployeeName = '';

    protected $queryString = [
        'filterSearch' => ['except' => ''],
        'filterSite' => ['except' => ''],
        'filterPosition' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filter')) {
            $this->resetPage();
        }
    }

    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function getStatusBadge(string $status): string
    {
        return $status === Employee::STATUS_RESIGNED
            ? 'bg-gray-500/15 text-gray-400'
            : 'bg-emerald-500/15 text-emerald-400';
    }

    public function getStatusLabel(string $status): string
    {
        return $status === Employee::STATUS_RESIGNED ? 'Resigned' : 'Active';
    }

    public function confirmDelete(string $nik, string $name): void
    {
        $this->deleteEmployeeId = $nik;
        $this->deleteEmployeeName = $name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteEmployeeId = null;
        $this->deleteEmployeeName = '';
    }

    public function deleteEmployee(): void
    {
        $employee = Employee::findOrFail($this->deleteEmployeeId);

        if ($employee->assignedAssets()->count() > 0) {
            $this->dispatch('delete-error', message: 'Employee masih memiliki asset terpasang. Kembalikan asset terlebih dahulu melalui Form Pengembalian Asset.');
            $this->cancelDelete();
            return;
        }

        $employee->delete();
        $this->selected = array_values(array_diff($this->selected, [$this->deleteEmployeeId]));

        ActivityLogger::log('delete', "Menghapus employee: {$this->deleteEmployeeName}", 'App\Models\Employee', $this->deleteEmployeeId);
        $this->cancelDelete();
        $this->dispatch('employee-deleted');
    }

    public function toggleSelectAll(): void
    {
        $pageIds = collect($this->filteredQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12)->items())->pluck('nik')->all();

        if (count($pageIds) === count(array_intersect($pageIds, $this->selected))) {
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
        }
    }

    public function confirmBulkDelete(): void
    {
        $this->showBulkDeleteModal = true;
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function openAssets(string $nik): void
    {
        $employee = Employee::find($nik);

        if (! $employee) {
            return;
        }

        $this->viewAssetsEmployeeName = $employee->name;
        $this->viewAssets = $employee->assignedAssets()
            ->orderBy('no_asset')
            ->get(['id', 'no_asset', 'nama_perangkat', 'brand', 'tipe', 'no_serial', 'status'])
            ->toArray();
        $this->showAssetsModal = true;
    }

    public function closeAssets(): void
    {
        $this->showAssetsModal = false;
        $this->viewAssets = [];
        $this->viewAssetsEmployeeName = '';
    }

    public function bulkDelete(): void
    {
        $employees = Employee::whereIn('nik', $this->selected)->get();
        $deleted = 0;
        foreach ($employees as $employee) {
            if ($employee->assignedAssets()->count() > 0) {
                continue;
            }
            $employee->delete();
            $deleted++;
        }

        ActivityLogger::log('delete', "Menghapus {$deleted} employee secara massal");
        $this->selected = [];
        $this->cancelBulkDelete();
        $this->dispatch('show-toast', message: "{$deleted} employee berhasil dihapus.", type: 'success');
        $this->dispatch('employee-deleted');
    }

    public function openBulkEdit(): void
    {
        $this->bulkEditField = '';
        $this->bulkEditValue = '';
        $this->showBulkEditModal = true;
    }

    public function updatedBulkEditField(): void
    {
        $this->bulkEditValue = '';
    }

    public function cancelBulkEdit(): void
    {
        $this->showBulkEditModal = false;
        $this->bulkEditField = '';
        $this->bulkEditValue = '';
    }

    public function bulkEdit(): void
    {
        if (empty($this->selected)) {
            $this->cancelBulkEdit();

            return;
        }

        $allowed = ['status', 'site', 'position_id', 'sub_departement_id'];
        if (! in_array($this->bulkEditField, $allowed)) {
            $this->addError('bulkEditField', 'Pilih field terlebih dahulu.');

            return;
        }

        if ($this->bulkEditField === 'status' && ! $this->bulkEditValue) {
            $this->addError('bulkEditValue', 'Pilih status terlebih dahulu.');

            return;
        }

        if ($this->bulkEditField === 'status' && $this->bulkEditValue === Employee::STATUS_RESIGNED) {
            $withAssets = Employee::whereIn('nik', $this->selected)
                ->whereHas('assignedAssets')
                ->count();

            if ($withAssets > 0) {
                $this->dispatch('show-toast', message: 'Tidak bisa set Resigned: masih ada employee terpilih yang memiliki asset terpasang. Kembalikan asset terlebih dahulu.', type: 'error');

                return;
            }
        }

        $value = $this->bulkEditField === 'status'
            ? $this->bulkEditValue
            : (trim($this->bulkEditValue) ?: null);

        $employees = Employee::whereIn('nik', $this->selected)->get();

        foreach ($employees as $employee) {
            $data = [$this->bulkEditField => $value];
            $user = $employee->user;

            if ($this->bulkEditField === 'status') {
                if ($value === Employee::STATUS_RESIGNED) {
                    $data['akun_login'] = 'No Access';
                    $data['date_resign'] = $employee->date_resign ?? today();
                    $user?->update(['status' => User::STATUS_RESIGNED]);
                } else {
                    $data['akun_login'] = $employee->email ? 'Connect' : 'No Access';
                    $data['date_resign'] = null;
                    $user?->update(['status' => User::STATUS_ACTIVE]);
                }
            }

            $employee->update($data);
        }

        ActivityLogger::log('update', "Mengubah {$this->bulkEditField} ".count($employees)." employee menjadi '{$value}'");
        $this->selected = [];
        $this->cancelBulkEdit();
        $this->dispatch('show-toast', message: "{$this->getBulkEditFieldLabel($this->bulkEditField)} ".count($employees).' employee diperbarui.', type: 'success');
        $this->dispatch('employee-updated');
    }

    public function getBulkEditFieldLabel(string $field): string
    {
        return match ($field) {
            'status' => 'Status',
            'site' => 'Site',
            'position_id' => 'Position',
            'sub_departement_id' => 'Sub Departemen',
            default => ucfirst($field),
        };
    }

    private function filteredQuery()
    {
        return Employee::withCount('assignedAssets')
            ->with(['siteDetail', 'user', 'directorate', 'divisi', 'departement', 'subDepartement', 'position'])
            ->when($this->filterSearch, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->filterSearch}%")
                    ->orWhere('email', 'like', "%{$this->filterSearch}%")
                    ->orWhere('nik', 'like', "%{$this->filterSearch}%");
            }))
            ->when($this->filterSite, fn ($q) => $q->where('site', $this->filterSite))
            ->when($this->filterPosition, fn ($q) => $q->where('position_id', $this->filterPosition))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus));
    }

    public function getSiteOptions(): array
    {
        return Site::orderBy('site')->get(['id_site', 'site'])
            ->mapWithKeys(fn ($s) => [$s->id_site => $s->site.' ('.$s->id_site.')'])
            ->toArray();
    }

    public function getPositionOptions(): array
    {
        return Position::orderBy('sort_order')->orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($p) => [$p->id => $p->name])
            ->toArray();
    }

    public function getSubDepartementOptions(): array
    {
        return SubDepartement::orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($s) => [$s->id => $s->name])
            ->toArray();
    }

    public function render()
    {
        $employees = $this->filteredQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);

        $pageIds = collect($employees->items())->pluck('nik')->all();
        $allSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        return view('livewire.admin.employees.index', [
            'employees' => $employees,
            'pageIds' => $pageIds,
            'allSelected' => $allSelected,
        ]);
    }
}

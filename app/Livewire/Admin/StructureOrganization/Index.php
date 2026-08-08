<?php

namespace App\Livewire\Admin\StructureOrganization;

use App\Helpers\ActivityLogger;
use App\Models\Departement;
use App\Models\Directorate;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\SubDepartement;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'directorate';

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $modalName = '';

    public string $modalCode = '';

    public ?int $modalParentId = null;

    public int $modalSortOrder = 0;

    public bool $showDeleteModal = false;

    public ?int $deleteId = null;

    public string $deleteName = '';

    protected $queryString = ['activeTab'];

    protected array $tabs = ['directorate', 'divisi', 'departement', 'sub_departement', 'position'];

    public function setTab(string $tab): void
    {
        if (in_array($tab, $this->tabs)) {
            $this->activeTab = $tab;
            $this->search = '';
            $this->resetPage();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->modalName = '';
        $this->modalCode = '';
        $this->modalParentId = null;
        $this->modalSortOrder = ((int) Position::max('sort_order')) + 1;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        $record = $this->recordForTab($id);

        if (! $record) {
            return;
        }

        $this->editingId = $id;
        $this->modalName = $record->name;
        $this->modalCode = $record->code ?? '';
        $this->modalParentId = $this->requiresParent() ? (int) $record->{$this->parentColumn()} : null;
        $this->modalSortOrder = (int) ($record->sort_order ?? 0);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function save(): void
    {
        $this->validate([
            'modalName' => ['required', 'string', 'max:255'],
            'modalCode' => ['nullable', 'string', 'max:50'],
            'modalParentId' => $this->requiresParent() ? ['required', 'integer'] : ['nullable'],
            'modalSortOrder' => ['nullable', 'integer', 'min:0'],
        ], [
            'modalName.required' => 'Nama wajib diisi.',
            'modalParentId.required' => 'Parent wajib dipilih.',
            'modalSortOrder.integer' => 'Urutan harus berupa angka.',
            'modalSortOrder.min' => 'Urutan minimal 0.',
        ]);

        if ($this->duplicateExists()) {
            $this->addError('modalName', 'Nama sudah digunakan pada level ini.');
            $this->dispatch('show-toast', message: 'Nama sudah digunakan pada level ini.', type: 'error');

            return;
        }

        $modelClass = $this->modelForTab();
        $attributes = [
            'name' => $this->modalName,
            'code' => $this->modalCode !== '' ? $this->modalCode : null,
        ];

        if ($this->requiresParent()) {
            $attributes[$this->parentColumn()] = $this->modalParentId;
        }

        if ($this->activeTab === 'position') {
            $attributes['sort_order'] = $this->modalSortOrder;
        }

        $label = $this->tabLabel();

        if ($this->editingId) {
            $record = $modelClass::findOrFail($this->editingId);
            $record->update($attributes);
            ActivityLogger::log('update', "Mengubah {$label}: {$this->modalName}", $modelClass, $record->getKey());
        } else {
            $record = $modelClass::create($attributes);
            ActivityLogger::log('create', "Menambahkan {$label}: {$this->modalName}", $modelClass, $record->getKey());
        }

        $this->dispatch('show-toast', message: "{$this->modalName} berhasil disimpan.", type: 'success');
        $this->closeModal();
        $this->resetPage();
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
    }

    public function delete(): void
    {
        $record = $this->recordForTab($this->deleteId);

        if (! $record) {
            $this->cancelDelete();

            return;
        }

        if ($record->employees()->exists()) {
            $this->dispatch('show-toast', message: "{$this->deleteName} masih digunakan oleh employee.", type: 'error');
            $this->cancelDelete();

            return;
        }

        if ($this->hasChildren($record)) {
            $this->dispatch('show-toast', message: "{$this->deleteName} masih memiliki sub-level. Hapus sub-level terlebih dahulu.", type: 'error');
            $this->cancelDelete();

            return;
        }

        $label = $this->tabLabel();
        $record->delete();
        ActivityLogger::log('delete', "Menghapus {$label}: {$this->deleteName}", $record::class, $this->deleteId);

        $this->dispatch('show-toast', message: "{$this->deleteName} berhasil dihapus.", type: 'success');
        $this->cancelDelete();
        $this->resetPage();
    }

    public function getTabLabelProperty(): string
    {
        return $this->tabLabel();
    }

    public function getParentFieldLabelProperty(): string
    {
        return match ($this->activeTab) {
            'divisi' => 'Directorat',
            'departement' => 'Divisi',
            'sub_departement' => 'Departemen',
            default => '',
        };
    }

    public function getDirectorateOptions(): array
    {
        return Directorate::orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getDivisiOptions(): array
    {
        return Divisi::with('directorate')->orderBy('name')->get()
            ->mapWithKeys(fn ($d) => [$d->id => trim(($d->directorate?->name ?? '').' / '.$d->name, ' /')])
            ->toArray();
    }

    public function getDepartementOptions(): array
    {
        return Departement::with('divisi.directorate')->orderBy('name')->get()
            ->mapWithKeys(fn ($d) => [$d->id => trim(($d->divisi?->directorate?->name ?? '').' / '.($d->divisi?->name ?? '').' / '.$d->name, ' /')])
            ->toArray();
    }

    private function tabQuery()
    {
        $query = match ($this->activeTab) {
            'directorate' => Directorate::withCount('divisis'),
            'divisi' => Divisi::with('directorate')->withCount('departements'),
            'departement' => Departement::with('divisi.directorate')->withCount('subDepartements'),
            'sub_departement' => SubDepartement::with('departement.divisi.directorate'),
            'position' => Position::withCount('employees'),
            default => Directorate::query(),
        };

        return $query
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->activeTab === 'position' ? 'sort_order' : 'name');
    }

    public function render()
    {
        return view('livewire.admin.structure-organization.index', [
            'records' => $this->tabQuery()->paginate(50),
        ]);
    }

    private function modelForTab(): string
    {
        return match ($this->activeTab) {
            'divisi' => Divisi::class,
            'departement' => Departement::class,
            'sub_departement' => SubDepartement::class,
            'position' => Position::class,
            default => Directorate::class,
        };
    }

    private function recordForTab(int $id): ?\Illuminate\Database\Eloquent\Model
    {
        $modelClass = $this->modelForTab();

        return $modelClass::find($id);
    }

    private function requiresParent(): bool
    {
        return in_array($this->activeTab, ['divisi', 'departement', 'sub_departement']);
    }

    private function parentColumn(): string
    {
        return match ($this->activeTab) {
            'divisi' => 'directorate_id',
            'departement' => 'divisi_id',
            'sub_departement' => 'departement_id',
            default => '',
        };
    }

    private function tabLabel(): string
    {
        return match ($this->activeTab) {
            'directorate' => 'Directorat',
            'divisi' => 'Divisi',
            'departement' => 'Departemen',
            'sub_departement' => 'Sub Departemen',
            'position' => 'Position',
            default => '',
        };
    }

    private function duplicateExists(): bool
    {
        $modelClass = $this->modelForTab();
        $query = $modelClass::where('name', $this->modalName);

        if ($this->requiresParent()) {
            $query->where($this->parentColumn(), $this->modalParentId);
        }

        if ($this->editingId) {
            $query->whereKeyNot($this->editingId);
        }

        return $query->exists();
    }

    private function hasChildren($record): bool
    {
        return match ($this->activeTab) {
            'directorate' => $record->divisis()->exists(),
            'divisi' => $record->departements()->exists(),
            'departement' => $record->subDepartements()->exists(),
            default => false,
        };
    }
}

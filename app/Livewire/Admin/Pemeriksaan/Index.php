<?php

namespace App\Livewire\Admin\Pemeriksaan;

use App\Helpers\ActivityLogger;
use App\Models\FormPemeriksaan;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $kondisi = '';

    public string $site = '';

    public ?array $viewingForm = null;

    public array $selected = [];

    public bool $showBulkDeleteModal = false;

    public bool $showBulkEditModal = false;

    public string $bulkEditField = '';

    public string $bulkEditValue = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'kondisi' => ['except' => ''],
        'site' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedKondisi(): void
    {
        $this->resetPage();
    }

    public function updatedSite(): void
    {
        $this->resetPage();
    }

    public function clearSiteFilter(): void
    {
        $this->site = '';
        $this->resetPage();
    }

    public function deleteForm(int $id): void
    {
        $this->authorizeAdmin();

        $form = FormPemeriksaan::find($id);
        if ($form) {
            $nomorForm = $form->nomor_form;
            $form->items()->delete();
            $form->approvals()->delete();
            $form->attachments()->delete();
            $form->delete();
        } else {
            return;
        }

        ActivityLogger::log('delete', "Menghapus form pemeriksaan: {$nomorForm}", 'App\Models\FormPemeriksaan', $id);
        $this->dispatch('show-toast', message: 'Form pemeriksaan berhasil dihapus.', type: 'success');
        $this->dispatch('form-bulk');
    }

    public function viewForm(int $id): void
    {
        $form = FormPemeriksaan::with(['teknisi', 'pengguna.position', 'pengguna.divisi', 'asset', 'site', 'items', 'approvals.user', 'attachments'])
            ->findOrFail($id);

        $this->viewingForm = [
            'id' => $form->id,
            'nomor_form' => $form->nomor_form,
            'status' => $form->status,
            'submitted_at' => $form->submitted_at,
            'submitted_at_formatted' => $form->submitted_at?->format('d/m/Y H:i'),
            'kondisi' => $form->kondisi,
            'kondisi_keterangan' => $form->kondisi_keterangan,
            'notes' => $form->notes,
            'tindakan_categories' => $form->tindakan_categories,
            'tindakan_solution' => $form->tindakan_solution,
            'location_detail' => $form->location_detail,
            'teknisi' => $form->teknisi ? ['name' => $form->teknisi->name, 'email' => $form->teknisi->email] : null,
            'pengguna' => $form->pengguna ? [
                'name' => $form->pengguna->name,
                'nik' => $form->pengguna->nik,
                'email' => $form->pengguna->email,
                'no_telepon' => $form->pengguna->no_telepon,
                'site_name' => $form->pengguna->site_name,
                'position' => $form->pengguna->position ? ['name' => $form->pengguna->position->name] : null,
                'divisi' => $form->pengguna->divisi ? ['name' => $form->pengguna->divisi->name] : null,
            ] : null,
            'asset' => $form->asset ? [
                'nama_perangkat' => $form->asset->nama_perangkat,
                'no_asset' => $form->asset->no_asset,
                'kategori' => $form->asset->kategori,
                'brand' => $form->asset->brand,
                'tipe' => $form->asset->tipe,
                'no_serial' => $form->asset->no_serial,
            ] : null,
            'site' => $form->site ? ['site' => $form->site->site] : null,
            'site_location' => $form->site_location,
            'items' => $form->items->map(fn ($item) => [
                'name' => $item->name,
                'category' => $item->category,
                'status' => $item->status,
                'keterangan' => $item->keterangan,
                'full_charge_capacity' => $item->full_charge_capacity,
                'design_capacity' => $item->design_capacity,
                'sort_order' => $item->sort_order,
            ])->toArray(),
            'approvals' => $form->approvals->map(fn ($a) => [
                'approval_level' => $a->approval_level,
                'status' => $a->status,
                'user_name' => $a->user?->name ?? $a->custom_signer_name,
                'user' => $a->user ? ['name' => $a->user->name] : null,
                'signature_path' => $a->signature_path,
                'approved_at' => $a->approved_at,
                'approved_at_formatted' => $a->approved_at?->format('d/m/Y H:i'),
            ])->toArray(),
        ];
    }

    public function closeView(): void
    {
        $this->viewingForm = null;
    }

    public function toggleSelectAll(): void
    {
        $pageIds = collect($this->filteredQuery()
            ->latest('submitted_at')
            ->paginate(15)->items())->pluck('id')->all();

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

    public function bulkDelete(): void
    {
        $this->authorizeAdmin();

        $deleted = 0;
        foreach (FormPemeriksaan::whereIn('id', $this->selected)->get() as $form) {
            $form->items()->delete();
            $form->approvals()->delete();
            $form->attachments()->delete();
            $form->delete();
            $deleted++;
        }

        ActivityLogger::log('delete', "Menghapus {$deleted} form pemeriksaan secara massal");
        $this->selected = [];
        $this->cancelBulkDelete();
        $this->dispatch('show-toast', message: "{$deleted} form pemeriksaan berhasil dihapus.", type: 'success');
        $this->dispatch('form-bulk');
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
        $this->authorizeAdmin();

        if (empty($this->selected)) {
            $this->cancelBulkEdit();
            return;
        }

        $allowed = ['status', 'kondisi'];
        if (!in_array($this->bulkEditField, $allowed)) {
            $this->addError('bulkEditField', 'Pilih field terlebih dahulu.');
            return;
        }

        if ($this->bulkEditField === 'status' && !in_array($this->bulkEditValue, ['draft', 'submitted', 'diketahui', 'disetujui', 'selesai', 'revisi'])) {
            $this->addError('bulkEditValue', 'Pilih status terlebih dahulu.');
            return;
        }

        if ($this->bulkEditField === 'kondisi' && !in_array($this->bulkEditValue, ['baru', 'lama'])) {
            $this->addError('bulkEditValue', 'Pilih kondisi terlebih dahulu.');
            return;
        }

        $count = FormPemeriksaan::whereIn('id', $this->selected)
            ->update([$this->bulkEditField => $this->bulkEditValue]);

        ActivityLogger::log('update', "Mengubah {$this->bulkEditField} {$count} form pemeriksaan menjadi {$this->bulkEditValue}");
        $this->dispatch('show-toast', message: "{$this->getBulkEditFieldLabel($this->bulkEditField)} {$count} form pemeriksaan diperbarui menjadi {$this->bulkEditValue}.", type: 'success');
        $this->selected = [];
        $this->cancelBulkEdit();
        $this->dispatch('form-bulk');
    }

    public function getBulkEditFieldLabel(string $field): string
    {
        return match ($field) {
            'status' => 'Status',
            'kondisi' => 'Kondisi',
            default => ucfirst($field),
        };
    }

    public function downloadBulkPdf(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $url = route('admin.pemeriksaan.bulk-pdf', ['ids' => $this->selected]);
        $this->dispatch('open-url', url: $url);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    private function filteredQuery()
    {
        return FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('nomor_form', 'like', "%{$this->search}%")
                    ->orWhereHas('teknisi', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('pengguna', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('asset', fn ($q) => $q->where('nama_perangkat', 'like', "%{$this->search}%")
                        ->orWhere('no_asset', 'like', "%{$this->search}%"));
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->kondisi, fn ($q) => $q->where('kondisi', $this->kondisi))
            ->when($this->site, fn ($q) => $q->whereHas('site', fn ($sq) => $sq->where('site', $this->site)));
    }

    public function render()
    {
        $forms = $this->filteredQuery()->latest('submitted_at')->paginate(15);

        $pageIds = collect($forms->items())->pluck('id')->all();
        $allSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        return view('livewire.admin.pemeriksaan.index', [
            'forms' => $forms,
            'pageIds' => $pageIds,
            'allSelected' => $allSelected,
        ]);
    }
}

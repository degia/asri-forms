<?php

namespace App\Livewire\Admin\Perawatan;

use App\Helpers\ActivityLogger;
use App\Models\FormPerawatan;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $kondisi_akhir = '';

    public ?array $viewingForm = null;

    public array $selected = [];

    public bool $showBulkDeleteModal = false;

    public bool $showBulkEditModal = false;

    public string $bulkEditField = '';

    public string $bulkEditValue = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'kondisi_akhir' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedKondisiAkhir(): void
    {
        $this->resetPage();
    }

    public function viewForm(int $id): void
    {
        $form = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals.user', 'attachments'])
            ->findOrFail($id);

        $this->viewingForm = [
            'id' => $form->id,
            'nomor_form' => $form->nomor_form,
            'status' => $form->status,
            'submitted_at' => $form->submitted_at?->format('d/m/Y H:i'),
            'kondisi_akhir' => $form->kondisi_akhir,
            'kondisi_akhir_notes' => $form->kondisi_akhir_notes,
            'notes' => $form->notes,
            'location_detail' => $form->location_detail,
            'barcode_fisik' => (bool) ($form->barcode_fisik ?? false),
            'teknisi' => $form->teknisi ? ['name' => $form->teknisi->name, 'email' => $form->teknisi->email] : null,
            'pengguna' => $form->pengguna ? ['name' => $form->pengguna->name, 'nik' => $form->pengguna->nik, 'site' => $form->pengguna->site_name] : null,
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
            ])->toArray(),
            'approvals' => $form->approvals->map(fn ($a) => [
                'approval_level' => $a->approval_level,
                'status' => $a->status,
                'user_name' => $a->user?->name ?? $a->custom_signer_name,
                'approved_at' => $a->approved_at?->format('d/m/Y H:i'),
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
        $deleted = 0;
        foreach (FormPerawatan::whereIn('id', $this->selected)->get() as $form) {
            $form->items()->delete();
            $form->approvals()->delete();
            $form->attachments()->delete();
            $form->delete();
            $deleted++;
        }

        ActivityLogger::log('delete', "Menghapus {$deleted} form perawatan secara massal");
        $this->selected = [];
        $this->cancelBulkDelete();
        $this->dispatch('show-toast', message: "{$deleted} form perawatan berhasil dihapus.", type: 'success');
        $this->dispatch('form-bulk');
    }

    public function openBulkEdit(): void
    {
        $this->bulkEditField = '';
        $this->bulkEditValue = '';
        $this->showBulkEditModal = true;
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

        $allowed = ['status', 'kondisi_akhir'];
        if (!in_array($this->bulkEditField, $allowed)) {
            $this->addError('bulkEditField', 'Pilih field terlebih dahulu.');
            return;
        }

        if ($this->bulkEditField === 'status' && !in_array($this->bulkEditValue, ['draft', 'submitted', 'diketahui', 'disetujui', 'selesai', 'revisi'])) {
            $this->addError('bulkEditValue', 'Pilih status terlebih dahulu.');
            return;
        }

        if ($this->bulkEditField === 'kondisi_akhir' && !in_array($this->bulkEditValue, ['baru', 'lama'])) {
            $this->addError('bulkEditValue', 'Pilih kondisi terlebih dahulu.');
            return;
        }

        $count = FormPerawatan::whereIn('id', $this->selected)
            ->update([$this->bulkEditField => $this->bulkEditValue]);

        ActivityLogger::log('update', "Mengubah {$this->bulkEditField} {$count} form perawatan menjadi {$this->bulkEditValue}");
        $this->dispatch('show-toast', message: "{$this->getBulkEditFieldLabel($this->bulkEditField)} {$count} form perawatan diperbarui menjadi {$this->bulkEditValue}.", type: 'success');
        $this->selected = [];
        $this->cancelBulkEdit();
        $this->dispatch('form-bulk');
    }

    public function getBulkEditFieldLabel(string $field): string
    {
        return match ($field) {
            'status' => 'Status',
            'kondisi_akhir' => 'Kondisi Akhir',
            default => ucfirst($field),
        };
    }

    private function filteredQuery()
    {
        return FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('nomor_form', 'like', "%{$this->search}%")
                    ->orWhereHas('teknisi', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('pengguna', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('asset', fn ($q) => $q->where('nama_perangkat', 'like', "%{$this->search}%")
                        ->orWhere('no_asset', 'like', "%{$this->search}%"));
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->kondisi_akhir, fn ($q) => $q->where('kondisi_akhir', $this->kondisi_akhir));
    }

    public function render()
    {
        $forms = $this->filteredQuery()->latest('submitted_at')->paginate(15);

        $pageIds = collect($forms->items())->pluck('id')->all();
        $allSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        return view('livewire.admin.perawatan.index', [
            'forms' => $forms,
            'pageIds' => $pageIds,
            'allSelected' => $allSelected,
        ]);
    }
}

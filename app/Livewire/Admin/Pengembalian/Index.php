<?php

namespace App\Livewire\Admin\Pengembalian;

use App\Helpers\ActivityLogger;
use App\Models\FormPengembalian;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?array $viewingForm = null;

    public ?int $deleteId = null;

    public string $deleteNomorForm = '';

    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function viewForm(int $id): void
    {
        $form = FormPengembalian::with(['teknisi', 'pengguna', 'items.asset'])
            ->findOrFail($id);

        $this->viewingForm = [
            'id' => $form->id,
            'nomor_form' => $form->nomor_form,
            'status' => $form->status,
            'submitted_at' => $form->submitted_at?->format('d/m/Y H:i'),
            'tanggal_pengembalian' => $form->tanggal_pengembalian?->format('d/m/Y'),
            'kondisi' => $form->kondisi,
            'kelengkapan' => $form->kelengkapan,
            'notes' => $form->notes,
            'teknisi' => $form->teknisi ? ['name' => $form->teknisi->name, 'email' => $form->teknisi->email] : null,
            'pengguna' => $form->pengguna ? ['name' => $form->pengguna->name, 'nik' => $form->pengguna->nik, 'site' => $form->pengguna->site_name] : null,
            'items' => $form->items->map(fn ($item) => [
                'no_asset' => $item->asset?->no_asset,
                'nama_perangkat' => $item->asset?->nama_perangkat,
                'brand' => $item->asset?->brand,
                'tipe' => $item->asset?->tipe,
                'no_serial' => $item->asset?->no_serial,
            ])->toArray(),
        ];
    }

    public function closeView(): void
    {
        $this->viewingForm = null;
    }

    public function confirmDelete(int $id, string $nomorForm): void
    {
        $this->deleteId = $id;
        $this->deleteNomorForm = $nomorForm;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteNomorForm = '';
    }

    public function delete(): void
    {
        $form = FormPengembalian::findOrFail($this->deleteId);
        $nomor = $form->nomor_form;
        $form->items()->delete();
        $form->delete();

        ActivityLogger::log('delete', "Menghapus Form Pengembalian Asset: {$nomor}", 'App\Models\FormPengembalian', $form->id);
        $this->cancelDelete();
        $this->dispatch('show-toast', message: "Form Pengembalian Asset {$nomor} berhasil dihapus.", type: 'success');
    }

    public function getKondisiLabel(?string $kondisi): string
    {
        return match ($kondisi) {
            'baik' => 'Baik',
            'rusak' => 'Rusak',
            'hilang' => 'Hilang',
            default => '—',
        };
    }

    public function getKelengkapanLabel(?string $kelengkapan): string
    {
        return match ($kelengkapan) {
            'lengkap' => 'Lengkap',
            'tidak_lengkap' => 'Tidak Lengkap',
            default => '—',
        };
    }

    private function filteredQuery()
    {
        return FormPengembalian::with(['teknisi', 'pengguna'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('nomor_form', 'like', "%{$this->search}%")
                    ->orWhereHas('teknisi', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('pengguna', fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nik', 'like', "%{$this->search}%"));
            }));
    }

    public function render()
    {
        $forms = $this->filteredQuery()->latest('submitted_at')->paginate(15);

        return view('livewire.admin.pengembalian.index', [
            'forms' => $forms,
        ]);
    }
}

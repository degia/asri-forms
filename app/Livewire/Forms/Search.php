<?php

namespace App\Livewire\Forms;

use App\Helpers\ActivityLogger;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Search extends Component
{
    use WithPagination;

    public string $search = '';
    public string $formType = '';
    public string $status = '';
    public string $kondisi = '';
    public ?string $userId = null;
    public ?int $assetId = null;
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $sortBy = 'submitted_at';
    public string $sortDir = 'desc';

    public string $userSearch = '';
    public array $userResults = [];
    public bool $showUserDropdown = false;

    public int $perPage = 15;

    public ?array $viewingForm = null;

    protected $listeners = [
        'resetFilters' => 'resetFilters',
    ];

    public function mount(): void
    {
        if (request('status')) $this->status = request('status');
        if (request('type')) $this->formType = request('type');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFormType(): void
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

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function searchUser(): void
    {
        if (strlen($this->userSearch) < 2) {
            $this->userResults = [];
            $this->showUserDropdown = false;
            return;
        }

        $this->userResults = User::where('name', 'like', "%{$this->userSearch}%")
            ->orWhere('nik', 'like', "%{$this->userSearch}%")
            ->limit(10)
            ->get()
            ->toArray();

        $this->showUserDropdown = count($this->userResults) > 0;
    }

    public function selectUser(?string $userId = null): void
    {
        if ($userId) {
            $user = User::find($userId);
            $this->userId = $userId;
            $this->userSearch = $user->name;
        } else {
            $this->userId = null;
            $this->userSearch = '';
        }
        $this->showUserDropdown = false;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->formType = '';
        $this->status = '';
        $this->kondisi = '';
        $this->userId = null;
        $this->assetId = null;
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->userSearch = '';
        $this->resetPage();
    }

    public function toggleSort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'desc';
        }
    }

    public function getResults()
    {
        $pemeriksaan = $this->getPemeriksaanQuery();
        $perawatan = $this->getPerawatanQuery();

        $combined = $pemeriksaan->concat($perawatan);

        $combined = match ($this->sortBy) {
            'submitted_at' => $this->sortDir === 'asc' ? $combined->sortBy('submitted_at') : $combined->sortByDesc('submitted_at'),
            'nomor_form' => $this->sortDir === 'asc' ? $combined->sortBy('nomor_form') : $combined->sortByDesc('nomor_form'),
            'status' => $this->sortDir === 'asc' ? $combined->sortBy('status') : $combined->sortByDesc('status'),
            default => $combined->sortByDesc('submitted_at'),
        };

        return $combined->values();
    }

    private function getPemeriksaanQuery()
    {
        $query = FormPemeriksaan::with(['teknisi', 'asset', 'pengguna', 'approvals.user']);
        $this->applyRoleScope($query);

        if ($this->formType && $this->formType !== 'pemeriksaan') {
            return collect();
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nomor_form', 'like', "%{$this->search}%")
                    ->orWhereHas('teknisi', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('pengguna', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('asset', fn($q2) => $q2->where('nama_perangkat', 'like', "%{$this->search}%")
                        ->orWhere('no_asset', 'like', "%{$this->search}%"));
            });
        }

        if ($this->status) $query->where('status', $this->status);
        if ($this->kondisi) $query->where('kondisi', $this->kondisi);
        if ($this->userId) $query->where('user_id', $this->userId);
        if ($this->dateFrom) $query->where('submitted_at', '>=', $this->dateFrom);
        if ($this->dateTo) $query->where('submitted_at', '<=', $this->dateTo . ' 23:59:59');

        return $query->get()->map(fn($f) => [
            'type' => 'pemeriksaan',
            'id' => $f->id,
            'user_id' => $f->user_id,
            'asset_id' => $f->asset_id,
            'nomor_form' => $f->nomor_form,
            'teknisi' => $f->teknisi->name ?? '-',
            'pengguna' => $f->pengguna->name ?? '-',
            'perangkat' => $f->asset->nama_perangkat ?? '-',
            'no_asset' => $f->asset->no_asset ?? '-',
            'kondisi' => $f->kondisi === 'baru' ? 'Baru' : 'Lama',
            'status' => $f->status,
            'disetujui' => $f->approvals->where('approval_level', 'disetujui_oleh')->first()?->user->name ?? '-',
            'submitted_at' => $f->submitted_at,
            'submitted_at_formatted' => $f->submitted_at?->format('d M Y H:i') ?? '-',
        ]);
    }

    private function getPerawatanQuery()
    {
        $query = FormPerawatan::with(['teknisi', 'asset', 'pengguna', 'approvals.user']);
        $this->applyRoleScope($query);

        if ($this->formType && $this->formType !== 'perawatan') {
            return collect();
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nomor_form', 'like', "%{$this->search}%")
                    ->orWhereHas('teknisi', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('pengguna', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('asset', fn($q2) => $q2->where('nama_perangkat', 'like', "%{$this->search}%")
                        ->orWhere('no_asset', 'like', "%{$this->search}%"));
            });
        }

        if ($this->status) $query->where('status', $this->status);
        if ($this->kondisi) $query->where('kondisi_akhir', $this->kondisi);
        if ($this->userId) $query->where('user_id', $this->userId);
        if ($this->dateFrom) $query->where('submitted_at', '>=', $this->dateFrom);
        if ($this->dateTo) $query->where('submitted_at', '<=', $this->dateTo . ' 23:59:59');

        return $query->get()->map(fn($f) => [
            'type' => 'perawatan',
            'id' => $f->id,
            'user_id' => $f->user_id,
            'asset_id' => $f->asset_id,
            'nomor_form' => $f->nomor_form,
            'teknisi' => $f->teknisi->name ?? '-',
            'pengguna' => $f->pengguna->name ?? '-',
            'perangkat' => $f->asset->nama_perangkat ?? '-',
            'no_asset' => $f->asset->no_asset ?? '-',
            'kondisi' => match($f->kondisi_akhir) { 'good' => 'Good', 'fair' => 'Fair', 'critical' => 'Critical', 'poor' => 'Poor', default => '-' },
            'status' => $f->status,
            'disetujui' => $f->approvals->where('approval_level', 'disetujui_oleh')->first()?->user->name ?? '-',
            'submitted_at' => $f->submitted_at,
            'submitted_at_formatted' => $f->submitted_at?->format('d M Y H:i') ?? '-',
        ]);
    }

    public function viewForm(int $id, string $type): void
    {
        if ($type === 'pemeriksaan') {
            $form = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'items', 'approvals.user', 'attachments'])
                ->find($id);
        } else {
            $form = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'items', 'approvals.user', 'attachments'])
                ->find($id);
        }

        if (!$form) return;

        $this->viewingForm = array_merge(
            $form->toArray(),
            ['type' => $type]
        );
    }

    public function closeView(): void
    {
        $this->viewingForm = null;
    }

    public function canEditDelete(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('admin') || $user->hasRole('teknisi'));
    }

    public function deleteForm(int $id, string $type): void
    {
        if (!$this->canEditDelete()) return;

        if ($type === 'pemeriksaan') {
            $form = FormPemeriksaan::find($id);
        } else {
            $form = FormPerawatan::find($id);
        }

        if ($form) {
            $form->items()->delete();
            $form->approvals()->delete();
            $form->attachments()->delete();
            $form->delete();
        }

        ActivityLogger::log('delete', "Menghapus form {$type}: {$id}", $type === 'pemeriksaan' ? 'App\Models\FormPemeriksaan' : 'App\Models\FormPerawatan', $id);

        $this->dispatch('formDeleted');
    }

    public function getStatusBg(string $status): string
    {
        return match ($status) {
            'draft' => 'rgba(107, 114, 128, 0.25)',
            'submitted' => 'rgba(59, 130, 246, 0.25)',
            'diketahui' => 'rgba(234, 179, 8, 0.25)',
            'disetujui' => 'rgba(3, 3, 3, 0.25)',
            'selesai' => 'rgba(16, 185, 129, 0.25)',
            'revisi' => 'rgba(239, 68, 68, 0.25)',
            default => 'rgba(107, 114, 128, 0.25)',
        };
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'draft' => 'bg-gray-500/15 text-gray-400',
            'submitted' => 'bg-blue-500/15 text-blue-400',
            'diketahui' => 'bg-yellow-500/15 text-yellow-400',
            'disetujui' => 'bg-green-500/15 text-green-400',
            'selesai' => 'bg-emerald-500/15 text-emerald-400',
            'revisi' => 'bg-red-500/15 text-red-400',
            default => 'bg-gray-500/15 text-gray-400',
        };
    }

    public function render()
    {
        return view('livewire.forms.search', [
            'results' => $this->getResults(),
        ])->layout('components.app-layout');
    }

    private function applyRoleScope($query): void
    {
        $user = Auth::user();
        if (!$user) return;

        if ($user->hasPermissionTo('view-all-forms')) return;

        if ($user->hasPermissionTo('view-assigned-forms')) {
            $query->where('pengguna_employee_id', $user->nik);
            return;
        }

        if ($user->hasPermissionTo('view-own-forms')) {
            $query->where('user_id', $user->email);
        }
    }
}

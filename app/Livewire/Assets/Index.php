<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterKategori = '';
    public string $filterStatus = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterKategori' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterKategori(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
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

    public function getKondisiLabel(?string $kondisi): string
    {
        return match ($kondisi) {
            'baru' => 'Baru',
            'lama' => 'Lama',
            'good' => 'Good',
            'fair' => 'Fair',
            'critical' => 'Critical',
            'poor' => 'Poor',
            default => '-',
        };
    }

    public function getKategoriList(): array
    {
        $query = Asset::select('kategori')->distinct()->whereNotNull('kategori')->orderBy('kategori');
        $this->applyAssetScope($query);
        return $query->pluck('kategori')->toArray();
    }

    private function applyAssetScope($query): void
    {
        $user = Auth::user();
        if (!$user) return;
        if ($user->hasPermissionTo('view-all-forms')) return;
        if ($user->hasPermissionTo('view-assigned-forms')) {
            $query->where('assigned_employee_id', $user->nik);
        }
    }

    public function render()
    {
        $query = Asset::query()
            ->withCount(['pemeriksaan', 'perawatan']);

        $this->applyAssetScope($query);

        $query->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('nama_perangkat', 'like', "%{$this->search}%")
                    ->orWhere('no_asset', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%")
                    ->orWhere('tipe', 'like', "%{$this->search}%")
                    ->orWhere('no_serial', 'like', "%{$this->search}%");
            }))
            ->when($this->filterKategori, fn ($q) => $q->where('kategori', $this->filterKategori))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy($this->sortBy, $this->sortDirection);

        $assets = $query->paginate(12);

        return view('livewire.assets.index', [
            'assets' => $assets,
        ]);
    }
}

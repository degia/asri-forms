<?php

namespace App\Livewire\Admin\Assets;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\FormPerawatan;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterNoAsset = '';

    public string $filterNama = '';

    public string $filterKategori = '';

    public string $filterBrand = '';

    public string $filterTipe = '';

    public string $filterNoSerial = '';

    public string $filterStatus = '';

    public string $filterOperatingUnit = '';

    public string $filterPerawatanStatus = '';

    public bool $showDeleteModal = false;

    public ?int $deleteAssetId = null;

    public string $deleteAssetName = '';

    public array $selected = [];

    public bool $showBulkDeleteModal = false;

    public bool $showBulkEditModal = false;

    public string $bulkEditField = '';

    public string $bulkEditValue = '';

    protected $queryString = [
        'filterNoAsset' => ['except' => ''],
        'filterNama' => ['except' => ''],
        'filterKategori' => ['except' => ''],
        'filterBrand' => ['except' => ''],
        'filterTipe' => ['except' => ''],
        'filterNoSerial' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterOperatingUnit' => ['except' => ''],
        'filterPerawatanStatus' => ['except' => ''],
    ];

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filter')) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteAssetId = $id;
        $this->deleteAssetName = $name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteAssetId = null;
        $this->deleteAssetName = '';
    }

    public function deleteAsset(): void
    {
        Asset::find($this->deleteAssetId)->delete();
        $this->selected = array_values(array_diff($this->selected, [$this->deleteAssetId]));

        ActivityLogger::log('delete', "Menghapus asset: {$this->deleteAssetName}", 'App\Models\Asset', $this->deleteAssetId);
        $this->cancelDelete();
        $this->dispatch('asset-deleted');
    }

    public function toggleSelectAll(): void
    {
        $pageIds = collect($this->filteredQuery()
            ->orderBy('no_asset')
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
        $assets = Asset::whereIn('id', $this->selected)->get();
        $deleted = 0;
        foreach ($assets as $asset) {
            $asset->delete();
            $deleted++;
        }

        ActivityLogger::log('delete', "Menghapus {$deleted} asset secara massal");
        $this->selected = [];
        $this->cancelBulkDelete();
        $this->dispatch('show-toast', message: "{$deleted} asset berhasil dihapus.", type: 'success');
        $this->dispatch('asset-bulk');
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

        $allowed = ['status', 'kategori', 'brand', 'tipe', 'no_serial', 'operating_unit', 'site_location_asset'];
        if (!in_array($this->bulkEditField, $allowed)) {
            $this->addError('bulkEditField', 'Pilih field terlebih dahulu.');
            return;
        }

        if ($this->bulkEditField === 'status' && !$this->bulkEditValue) {
            $this->addError('bulkEditValue', 'Pilih status terlebih dahulu.');
            return;
        }

        $value = trim($this->bulkEditValue);
        $count = Asset::whereIn('id', $this->selected)
            ->update([$this->bulkEditField => $value ?: null]);

        ActivityLogger::log('update', "Mengubah {$this->bulkEditField} {$count} asset menjadi '{$value}'");
        $this->dispatch('show-toast', message: "{$this->getBulkEditFieldLabel($this->bulkEditField)} {$count} asset diperbarui.", type: 'success');
        $this->selected = [];
        $this->cancelBulkEdit();
        $this->dispatch('asset-bulk');
    }

    public function getBulkEditFieldLabel(string $field): string
    {
        return match ($field) {
            'status' => 'Status',
            'kategori' => 'Kategori',
            'brand' => 'Brand',
            'tipe' => 'Tipe',
            'no_serial' => 'No. Serial',
            'operating_unit' => 'Operating Unit',
            'site_location_asset' => 'Site Location',
            default => ucfirst($field),
        };
    }

    private function filteredQuery()
    {
        $query = Asset::with('assignedEmployee', 'operatingUnitSite', 'siteAsset')
            ->when($this->filterNoAsset, fn ($q) => $q->where('no_asset', 'like', "%{$this->filterNoAsset}%"))
            ->when($this->filterNama, fn ($q) => $q->where('nama_perangkat', 'like', "%{$this->filterNama}%"))
            ->when($this->filterKategori, fn ($q) => $q->where('kategori', 'like', "%{$this->filterKategori}%"))
            ->when($this->filterBrand, fn ($q) => $q->where('brand', 'like', "%{$this->filterBrand}%"))
            ->when($this->filterTipe, fn ($q) => $q->where('tipe', 'like', "%{$this->filterTipe}%"))
            ->when($this->filterNoSerial, fn ($q) => $q->where('no_serial', 'like', "%{$this->filterNoSerial}%"))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus));

        if ($this->filterOperatingUnit) {
            $query->where('operating_unit', $this->filterOperatingUnit);
        }

        if ($this->filterPerawatanStatus === 'done') {
            $assetIds = FormPerawatan::whereNotNull('submitted_at')
                ->distinct()->pluck('asset_id')->filter()->toArray();
            $query->whereIn('id', $assetIds);
        } elseif ($this->filterPerawatanStatus === 'pending') {
            $assetIds = FormPerawatan::whereNotNull('submitted_at')
                ->distinct()->pluck('asset_id')->filter()->toArray();
            $query->whereNotIn('id', $assetIds);
        }

        return $query;
    }

    public function render()
    {
        $assets = $this->filteredQuery()->orderBy('no_asset')->paginate(15);

        $pageIds = collect($assets->items())->pluck('id')->all();
        $allSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        return view('livewire.admin.assets.index', [
            'assets' => $assets,
            'pageIds' => $pageIds,
            'allSelected' => $allSelected,
        ]);
    }
}

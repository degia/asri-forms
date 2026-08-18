<?php

namespace App\Livewire\Admin\Assets;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterSearch = '';

    public string $filterKategori = '';

    public string $filterBrand = '';

    public string $filterTipe = '';

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
        'filterSearch' => ['except' => ''],
        'filterKategori' => ['except' => ''],
        'filterBrand' => ['except' => ''],
        'filterTipe' => ['except' => ''],
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

    public function getKategoriOptions(): array
    {
        return Asset::whereNotNull('kategori')->where('kategori', '!=', '')->orderBy('kategori')->distinct()->pluck('kategori')->toArray();
    }

    public function getBrandOptions(): array
    {
        return Asset::whereNotNull('brand')->where('brand', '!=', '')->orderBy('brand')->distinct()->pluck('brand')->toArray();
    }

    public function getTipeOptions(): array
    {
        return Asset::whereNotNull('tipe')->where('tipe', '!=', '')->orderBy('tipe')->distinct()->pluck('tipe')->toArray();
    }

    public function getNoSerialOptions(): array
    {
        return Asset::whereNotNull('no_serial')->where('no_serial', '!=', '')->orderBy('no_serial')->distinct()->pluck('no_serial')->toArray();
    }

    public function getOperatingUnitOptions(): array
    {
        return Asset::whereNotNull('operating_unit')->where('operating_unit', '!=', '')->orderBy('operating_unit')->distinct()->pluck('operating_unit')->toArray();
    }

    public function getSiteLocationOptions(): array
    {
        return Asset::whereNotNull('site_location_asset')->where('site_location_asset', '!=', '')->orderBy('site_location_asset')->distinct()->pluck('site_location_asset')->toArray();
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
        $this->authorizeAdmin();

        $asset = Asset::find($this->deleteAssetId);
        if (!$asset) {
            $this->cancelDelete();
            return;
        }

        FormPemeriksaan::where('asset_id', $asset->id)->update(['asset_id' => null]);
        FormPerawatan::where('asset_id', $asset->id)->update(['asset_id' => null]);
        \App\Models\FormPengembalianItem::where('asset_id', $asset->id)->delete();

        $asset->forceDelete();
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
        $this->authorizeAdmin();

        $assets = Asset::whereIn('id', $this->selected)->get();
        $deleted = 0;
        foreach ($assets as $asset) {
            FormPemeriksaan::where('asset_id', $asset->id)->update(['asset_id' => null]);
            FormPerawatan::where('asset_id', $asset->id)->update(['asset_id' => null]);
            \App\Models\FormPengembalianItem::where('asset_id', $asset->id)->delete();

            $asset->forceDelete();
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

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    private function filteredQuery()
    {
        $query = Asset::with('assignedEmployee', 'operatingUnitSite', 'siteAsset')
            ->when($this->filterSearch, fn ($q) => $q->where(function ($q) {
                $q->where('no_asset', 'like', "%{$this->filterSearch}%")
                    ->orWhere('nama_perangkat', 'like', "%{$this->filterSearch}%")
                    ->orWhere('no_serial', 'like', "%{$this->filterSearch}%");
            }))
            ->when($this->filterKategori, fn ($q) => $q->where('kategori', $this->filterKategori))
            ->when($this->filterBrand, fn ($q) => $q->where('brand', $this->filterBrand))
            ->when($this->filterTipe, fn ($q) => $q->where('tipe', $this->filterTipe))
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

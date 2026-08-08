<?php

namespace App\Livewire\Admin\Sites;

use App\Helpers\ActivityLogger;
use App\Models\Site;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterId = '';
    public string $filterSite = '';
    public string $filterBuss = '';
    public string $filterCorp = '';
    public string $filterCountry = '';
    public string $filterProvincy = '';
    public string $filterCity = '';
    public bool $showDeleteModal = false;
    public ?string $deleteSiteId = null;
    public string $deleteSiteName = '';
    public array $selected = [];
    public bool $showBulkDeleteModal = false;
    public bool $showBulkEditModal = false;
    public string $bulkEditField = '';
    public string $bulkEditValue = '';

    protected $queryString = [
        'filterId' => ['except' => ''],
        'filterSite' => ['except' => ''],
        'filterBuss' => ['except' => ''],
        'filterCorp' => ['except' => ''],
        'filterCountry' => ['except' => ''],
        'filterProvincy' => ['except' => ''],
        'filterCity' => ['except' => ''],
    ];

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filter')) {
            $this->resetPage();
        }
    }

    public function confirmDelete(string $idSite, string $name): void
    {
        $this->deleteSiteId = $idSite;
        $this->deleteSiteName = $name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteSiteId = null;
        $this->deleteSiteName = '';
    }

    public function deleteSite(): void
    {
        Site::where('id_site', $this->deleteSiteId)->delete();
        $this->selected = array_values(array_diff($this->selected, [$this->deleteSiteId]));

        ActivityLogger::log('delete', "Menghapus site: {$this->deleteSiteId} - {$this->deleteSiteName}", 'App\Models\Site', $this->deleteSiteId);
        $this->cancelDelete();
        $this->dispatch('site-deleted');
    }

    public function toggleSelectAll(): void
    {
        $pageIds = collect($this->filteredQuery()
            ->orderBy('id_site')
            ->paginate(15)->items())->pluck('id_site')->all();

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
        $deleted = Site::whereIn('id_site', $this->selected)->delete();

        ActivityLogger::log('delete', "Menghapus {$deleted} site secara massal");
        $this->selected = [];
        $this->cancelBulkDelete();
        $this->dispatch('show-toast', message: "{$deleted} site berhasil dihapus.", type: 'success');
        $this->dispatch('site-bulk');
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

        $allowed = ['buss', 'id_corp', 'country', 'provincy', 'city'];
        if (!in_array($this->bulkEditField, $allowed)) {
            $this->addError('bulkEditField', 'Pilih field terlebih dahulu.');
            return;
        }

        $count = Site::whereIn('id_site', $this->selected)
            ->update([$this->bulkEditField => trim($this->bulkEditValue) ?: null]);

        ActivityLogger::log('update', "Mengubah {$this->bulkEditField} {$count} site menjadi '{$this->bulkEditValue}'");
        $this->dispatch('show-toast', message: "{$this->bulkEditField} {$count} site diperbarui.", type: 'success');
        $this->selected = [];
        $this->cancelBulkEdit();
        $this->dispatch('site-bulk');
    }

    private function filteredQuery()
    {
        return Site::query()
            ->when($this->filterId, fn ($q) => $q->where('id_site', 'like', "%{$this->filterId}%"))
            ->when($this->filterSite, fn ($q) => $q->where('site', 'like', "%{$this->filterSite}%"))
            ->when($this->filterBuss, fn ($q) => $q->where('buss', 'like', "%{$this->filterBuss}%"))
            ->when($this->filterCorp, fn ($q) => $q->where('id_corp', 'like', "%{$this->filterCorp}%"))
            ->when($this->filterCountry, fn ($q) => $q->where('country', 'like', "%{$this->filterCountry}%"))
            ->when($this->filterProvincy, fn ($q) => $q->where('provincy', 'like', "%{$this->filterProvincy}%"))
            ->when($this->filterCity, fn ($q) => $q->where('city', 'like', "%{$this->filterCity}%"));
    }

    public function render()
    {
        $sites = $this->filteredQuery()->orderBy('id_site')->paginate(15);

        $pageIds = collect($sites->items())->pluck('id_site')->all();
        $allSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        return view('livewire.admin.sites.index', [
            'sites' => $sites,
            'pageIds' => $pageIds,
            'allSelected' => $allSelected,
        ]);
    }
}

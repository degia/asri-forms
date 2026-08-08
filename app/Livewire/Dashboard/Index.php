<?php

namespace App\Livewire\Dashboard;

use App\Models\Asset;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public ?string $filterOperatingUnit = '';

    public array $perawatanBySite = [];

    public array $pemeriksaanBySite = [];

    public array $topAssets = [];

    public array $trendPerawatanBulanan = [];

    public array $operatingUnits = [];

    public array $perawatanVsBelum = [];

    public string $filterAssetStatus = '';

    public string $filterCorpUnit = '';

    public string $filterSite = '';

    public array $corpUnits = [];

    public array $filterSites = [];

    public array $usersBySite = [];

    public function mount(): void
    {
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subDays(29)->format('Y-m-d');
        $this->operatingUnits = Site::whereIn('id_site', Asset::whereNotNull('operating_unit')
            ->where('operating_unit', '!=', '')
            ->pluck('operating_unit'))
            ->orderBy('site')
            ->get()
            ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
            ->toArray();
        $this->corpUnits = Site::select('id_corp')
            ->distinct()
            ->whereNotNull('id_corp')
            ->where('id_corp', '!=', '')
            ->orderBy('id_corp')
            ->pluck('id_corp')
            ->toArray();
        $this->loadFilterSites();
        $this->loadAll();
    }

    public function updatedStartDate(): void
    {
        $this->loadAll();
    }

    public function updatedEndDate(): void
    {
        $this->loadAll();
    }

    public function updatedFilterOperatingUnit(): void
    {
        $this->loadTopAssets();
    }

    public function updatedFilterAssetStatus(): void
    {
        $this->loadPerawatanVsBelumByOperatingUnit();
    }

    public function updatedFilterCorpUnit(): void
    {
        $this->filterSite = '';
        $this->loadFilterSites();
        $this->loadUsersBySite();
    }

    public function updatedFilterSite(): void
    {
        $this->loadUsersBySite();
    }

    private function loadFilterSites(): void
    {
        $query = Site::query();

        if ($this->filterCorpUnit) {
            $query->where('id_corp', $this->filterCorpUnit);
        }

        $this->filterSites = $query->orderBy('id_site')
            ->get(['id_site', 'site'])
            ->map(fn ($s) => ['id' => $s->id_site, 'name' => "{$s->id_site} - {$s->site}"])
            ->toArray();
    }

    private function loadAll(): void
    {
        $this->loadPerawatanBySite();
        $this->loadPemeriksaanBySite();
        $this->loadTopAssets();
        $this->loadTrendPerawatanBulanan();
        $this->loadPerawatanVsBelumByOperatingUnit();
        $this->loadUsersBySite();
        $this->dispatch('chartsUpdated');
    }

    private function resolveSiteLocation($form): string
    {
        if ($form->site_location) {
            return $form->site_location;
        }
        if ($form->asset && $form->asset->site_location_asset) {
            return $form->asset->site_location_asset;
        }
        if ($form->asset && $form->asset->operating_unit) {
            return $form->asset->operating_unit;
        }

        return 'unknown';
    }

    private function loadPerawatanBySite(): void
    {
        $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(29)->startOfDay();
        $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

        $forms = FormPerawatan::whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$start, $end])
            ->with(['asset'])
            ->get();

        $counts = [];
        foreach ($forms as $form) {
            $site = $this->resolveSiteLocation($form);
            $counts[$site] = ($counts[$site] ?? 0) + 1;
        }

        $siteNames = Site::whereIn('id_site', array_keys($counts))
            ->pluck('site', 'id_site')
            ->toArray();

        $result = [];
        foreach ($counts as $siteId => $count) {
            $result[] = [
                'site' => $siteNames[$siteId] ?? $siteId,
                'total' => (int) $count,
            ];
        }

        usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);
        $this->perawatanBySite = $result;
    }

    private function loadPemeriksaanBySite(): void
    {
        $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(29)->startOfDay();
        $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

        $forms = FormPemeriksaan::whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$start, $end])
            ->with(['asset'])
            ->get();

        $counts = [];
        foreach ($forms as $form) {
            $site = $this->resolveSiteLocation($form);
            $counts[$site] = ($counts[$site] ?? 0) + 1;
        }

        $siteNames = Site::whereIn('id_site', array_keys($counts))
            ->pluck('site', 'id_site')
            ->toArray();

        $result = [];
        foreach ($counts as $siteId => $count) {
            $result[] = [
                'site' => $siteNames[$siteId] ?? $siteId,
                'total' => (int) $count,
            ];
        }

        usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);
        $this->pemeriksaanBySite = $result;
    }

    private function loadTopAssets(): void
    {
        $query = Asset::query()
            ->join('form_pemeriksaan', 'assets.id', '=', 'form_pemeriksaan.asset_id')
            ->selectRaw('assets.id, assets.nama_perangkat, assets.no_asset, assets.operating_unit, assets.site_location_asset, count(form_pemeriksaan.id) as total_pemeriksaan')
            ->groupBy('assets.id', 'assets.nama_perangkat', 'assets.no_asset', 'assets.operating_unit', 'assets.site_location_asset');

        if ($this->filterOperatingUnit) {
            $query->where('assets.operating_unit', $this->filterOperatingUnit);
        }

        $topAssets = $query->orderByDesc('total_pemeriksaan')
            ->limit(10)
            ->get()
            ->toArray();

        $allSiteIds = collect($topAssets)
            ->pluck('site_location_asset')
            ->merge(collect($topAssets)->pluck('operating_unit'))
            ->filter()
            ->unique()
            ->toArray();

        $siteNames = Site::whereIn('id_site', $allSiteIds)
            ->pluck('site', 'id_site')
            ->toArray();

        $result = [];
        foreach ($topAssets as $a) {
            $result[] = [
                'id' => $a['id'],
                'nama_perangkat' => $a['nama_perangkat'],
                'no_asset' => $a['no_asset'],
                'operating_unit' => $siteNames[$a['operating_unit']] ?? ($a['operating_unit'] ?? '-'),
                'site_location' => $siteNames[$a['site_location_asset']] ?? ($a['site_location_asset'] ?? '-'),
                'total' => (int) $a['total_pemeriksaan'],
            ];
        }

        $this->topAssets = $result;
    }

    private function loadTrendPerawatanBulanan(): void
    {
        $trend = FormPerawatan::whereNotNull('submitted_at')
            ->select(
                DB::raw("DATE_FORMAT(submitted_at, '%Y-%m') as month"),
                DB::raw('count(*) as total')
            )
            ->where('submitted_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $this->trendPerawatanBulanan = $trend;
    }

    private function loadPerawatanVsBelumByOperatingUnit(): void
    {
        $assetIds = FormPerawatan::whereNotNull('submitted_at')
            ->distinct()
            ->pluck('asset_id')
            ->filter()
            ->toArray();

        $query = Asset::whereNotNull('operating_unit')
            ->where('operating_unit', '!=', '');

        if ($this->filterAssetStatus !== '') {
            $query->where('status', $this->filterAssetStatus);
        }

        $allAssets = $query->get();

        $counts = [];
        foreach ($allAssets as $asset) {
            $ou = $asset->operating_unit;
            if (! isset($counts[$ou])) {
                $counts[$ou] = ['dilakukan' => 0, 'belum' => 0];
            }
            if (in_array($asset->id, $assetIds)) {
                $counts[$ou]['dilakukan']++;
            } else {
                $counts[$ou]['belum']++;
            }
        }

        $siteNames = Site::whereIn('id_site', array_keys($counts))
            ->pluck('site', 'id_site')
            ->toArray();

        $result = [];
        foreach ($counts as $ouId => $data) {
            $result[] = [
                'operating_unit_id' => $ouId,
                'operating_unit' => $siteNames[$ouId] ?? $ouId,
                'dilakukan' => $data['dilakukan'],
                'belum' => $data['belum'],
                'total' => $data['dilakukan'] + $data['belum'],
            ];
        }

        usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);
        $this->perawatanVsBelum = $result;
    }

    private function loadUsersBySite(): void
    {
        $query = User::query()
            ->join('employees', 'employees.nik', '=', 'users.nik')
            ->select('employees.site', DB::raw('count(*) as total'))
            ->whereNotNull('employees.site')
            ->where('employees.site', '!=', '');

        if ($this->filterCorpUnit) {
            $query->whereIn('employees.site', Site::where('id_corp', $this->filterCorpUnit)->pluck('id_site'));
        }

        if ($this->filterSite) {
            $query->where('employees.site', $this->filterSite);
        }

        $rows = $query->groupBy('employees.site')->get();

        $siteNames = Site::whereIn('id_site', $rows->pluck('site')->toArray())
            ->pluck('site', 'id_site')
            ->toArray();

        $corpUnits = Site::whereIn('id_site', $rows->pluck('site')->toArray())
            ->pluck('id_corp', 'id_site')
            ->toArray();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'site_id' => $row->site,
                'site' => $siteNames[$row->site] ?? $row->site,
                'corp_unit' => $corpUnits[$row->site] ?? '-',
                'total' => (int) $row->total,
            ];
        }

        usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);
        $this->usersBySite = $result;
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}

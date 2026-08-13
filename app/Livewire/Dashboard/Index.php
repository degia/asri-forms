<?php

namespace App\Livewire\Dashboard;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use App\Models\Site;
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

    public array $trendPerawatanHarian = [];

    public string $trendMode = 'harian';

    public string $filterTrendAssetOu = '';

    public string $filterTrendSiteLocation = '';

    public string $filterTrendSiteUser = '';

    public array $trendAssetOus = [];

    public array $trendSiteLocations = [];

    public array $trendSiteUsers = [];

    public array $operatingUnits = [];

    public array $perawatanVsBelum = [];

    public string $filterAssetStatus = '';

    public array $employeesAssetBySite = [];

    public string $filterEmpAssetSite = '';

    public array $empAssetSites = [];

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
        $this->loadEmpAssetSites();
        $this->loadTrendFilterOptions();
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

    public function updatedFilterEmpAssetSite(): void
    {
        $this->loadEmployeesAssetBySite();
    }

    public function updatedFilterTrendAssetOu(): void
    {
        $this->loadTrendPerawatan();
    }

    public function updatedFilterTrendSiteLocation(): void
    {
        $this->loadTrendPerawatan();
    }

    public function updatedFilterTrendSiteUser(): void
    {
        $this->loadTrendPerawatan();
    }

    private function loadTrendFilterOptions(): void
    {
        $this->trendAssetOus = Site::whereIn('id_site', Asset::whereHas('perawatan')
            ->whereNotNull('operating_unit')
            ->where('operating_unit', '!=', '')
            ->pluck('operating_unit')
            ->unique())
            ->orderBy('site')
            ->get(['id_site', 'site'])
            ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
            ->toArray();

        $this->trendSiteLocations = Site::whereIn('id_site', FormPerawatan::whereNotNull('submitted_at')
            ->whereNotNull('site_location')
            ->where('site_location', '!=', '')
            ->distinct()
            ->pluck('site_location'))
            ->orderBy('site')
            ->get(['id_site', 'site'])
            ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
            ->toArray();

        $this->trendSiteUsers = Site::whereIn('id_site', FormPerawatan::whereNotNull('submitted_at')
            ->whereNotNull('pengguna_employee_id')
            ->join('employees', 'employees.nik', '=', 'form_perawatan.pengguna_employee_id')
            ->whereNotNull('employees.site')
            ->where('employees.site', '!=', '')
            ->distinct()
            ->pluck('employees.site'))
            ->orderBy('site')
            ->get(['id_site', 'site'])
            ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
            ->toArray();
    }

    private function trendQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = FormPerawatan::whereNotNull('submitted_at');

        if ($this->filterTrendAssetOu) {
            $query->whereHas('asset', fn ($q) => $q->where('assets.operating_unit', $this->filterTrendAssetOu));
        }

        if ($this->filterTrendSiteLocation) {
            $query->where('form_perawatan.site_location', $this->filterTrendSiteLocation);
        }

        if ($this->filterTrendSiteUser) {
            $query->whereHas('pengguna', fn ($q) => $q->where('employees.site', $this->filterTrendSiteUser));
        }

        return $query;
    }

    private function loadTrendPerawatan(): void
    {
        $this->loadTrendPerawatanBulanan();
        $this->loadTrendPerawatanHarian();
    }

    private function loadAll(): void
    {
        $this->loadPerawatanBySite();
        $this->loadPemeriksaanBySite();
        $this->loadTopAssets();
        $this->loadTrendPerawatan();
        $this->loadPerawatanVsBelumByOperatingUnit();
        $this->loadEmployeesAssetBySite();
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
            ->whereNull('form_pemeriksaan.deleted_at')
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
        $trend = $this->trendQuery()
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

    private function loadTrendPerawatanHarian(): void
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $rows = $this->trendQuery()
            ->select(
                DB::raw("DATE_FORMAT(submitted_at, '%Y-%m-%d') as day"),
                DB::raw('count(*) as total')
            )
            ->whereBetween('submitted_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $trend = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('d M');
            $trend[$key] = (int) ($rows[$d->format('Y-m-d')] ?? 0);
        }

        $this->trendPerawatanHarian = $trend;
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

    private function loadEmpAssetSites(): void
    {
        $this->empAssetSites = Site::whereIn('id_site', Employee::whereNotNull('site')
            ->where('site', '!=', '')
            ->distinct()
            ->pluck('site'))
            ->orderBy('site')
            ->get(['id_site', 'site'])
            ->map(fn ($s) => ['id' => $s->id_site, 'name' => "{$s->id_site} - {$s->site}"])
            ->toArray();
    }

    private function loadEmployeesAssetBySite(): void
    {
        $assignedNik = Asset::whereNotNull('assigned_employee_id')
            ->where('assigned_employee_id', '!=', '')
            ->distinct()
            ->pluck('assigned_employee_id')
            ->toArray();

        $query = Employee::whereNotNull('site')
            ->where('site', '!=', '');

        if ($this->filterEmpAssetSite) {
            $query->where('site', $this->filterEmpAssetSite);
        }

        $employees = $query->get(['nik', 'site']);

        $counts = [];
        foreach ($employees as $emp) {
            if (! isset($counts[$emp->site])) {
                $counts[$emp->site] = ['punya' => 0, 'tidak' => 0];
            }
            if (in_array($emp->nik, $assignedNik)) {
                $counts[$emp->site]['punya']++;
            } else {
                $counts[$emp->site]['tidak']++;
            }
        }

        $siteNames = Site::whereIn('id_site', array_keys($counts))
            ->pluck('site', 'id_site')
            ->toArray();

        $result = [];
        foreach ($counts as $siteId => $data) {
            $total = $data['punya'] + $data['tidak'];
            $result[] = [
                'site_id' => $siteId,
                'site' => $siteNames[$siteId] ?? $siteId,
                'punya' => $data['punya'],
                'tidak' => $data['tidak'],
                'total' => $total,
                'pct' => $total > 0 ? round(($data['punya'] / $total) * 100, 1) : 0,
            ];
        }

        usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);
        $this->employeesAssetBySite = $result;
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}

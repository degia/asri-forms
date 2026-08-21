<?php

namespace App\Livewire\Dashboard;

use App\Models\Asset;
use App\Models\Directorate;
use App\Models\Employee;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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

    public string $filterAssetStatus = 'active';

    public array $employeesAssetBySite = [];

    public string $filterEmpAssetSite = '';

    public array $empAssetSites = [];

    public array $orgHierarchy = [];

    public array $empStatusData = [];

    public string $filterEmpStatusSite = '';

    public array $empStatusSites = [];

    private int $cacheTTL = 300;

    public function mount(): void
    {
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subDays(29)->format('Y-m-d');
        $this->operatingUnits = Cache::remember('dashboard:operatingUnits', $this->cacheTTL, function () {
            return Site::whereIn('id_site', Asset::whereNotNull('operating_unit')
                ->where('operating_unit', '!=', '')
                ->pluck('operating_unit'))
                ->orderBy('site')
                ->get()
                ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
                ->toArray();
        });
        $this->loadEmpAssetSites();
        $this->loadEmpStatusSites();
        $this->loadTrendFilterOptions();
        $this->loadAll();
    }

    public function updatedStartDate(): void
    {
        $this->loadPerawatanBySite();
        $this->loadPemeriksaanBySite();
    }

    public function updatedEndDate(): void
    {
        $this->loadPerawatanBySite();
        $this->loadPemeriksaanBySite();
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

    public function updatedFilterEmpStatusSite(): void
    {
        $this->loadEmpStatusData();
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
        $this->trendAssetOus = Cache::remember('dashboard:trendAssetOus', $this->cacheTTL, function () {
            return Site::whereIn('id_site', Asset::whereHas('perawatan')
                ->whereNotNull('operating_unit')
                ->where('operating_unit', '!=', '')
                ->pluck('operating_unit')
                ->unique())
                ->orderBy('site')
                ->get(['id_site', 'site'])
                ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
                ->toArray();
        });

        $this->trendSiteLocations = Cache::remember('dashboard:trendSiteLocations', $this->cacheTTL, function () {
            return Site::whereIn('id_site', FormPerawatan::whereNotNull('submitted_at')
                ->whereNotNull('site_location')
                ->where('site_location', '!=', '')
                ->distinct()
                ->pluck('site_location'))
                ->orderBy('site')
                ->get(['id_site', 'site'])
                ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
                ->toArray();
        });

        $this->trendSiteUsers = Cache::remember('dashboard:trendSiteUsers', $this->cacheTTL, function () {
            return Site::whereIn('id_site', FormPerawatan::whereNotNull('submitted_at')
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
        });
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
        $this->loadOrgHierarchy();
        $this->loadEmpStatusData();
        $this->dispatch('chartsUpdated');
    }

    private function cacheKey(string $prefix, ...$parts): string
    {
        return $prefix . ':' . md5(implode(':', array_map(fn ($p) => (string) $p, $parts)));
    }

    private function loadPerawatanBySite(): void
    {
        $key = $this->cacheKey('dashboard:perawatanBySite', $this->startDate, $this->endDate);

        $this->perawatanBySite = Cache::remember($key, $this->cacheTTL, function () {
            $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(29)->startOfDay();
            $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

            $counts = DB::table('form_perawatan')
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [$start, $end])
                ->join('assets', 'assets.id', '=', 'form_perawatan.asset_id')
                ->leftJoin('sites', 'sites.id_site', '=', 'assets.site_location_asset')
                ->selectRaw('COALESCE(sites.site, assets.site_location_asset, assets.operating_unit, ?) as site_name, COUNT(*) as total', ['unknown'])
                ->groupBy('site_name')
                ->orderByDesc('total')
                ->pluck('total', 'site_name')
                ->toArray();

            $result = [];
            foreach ($counts as $site => $total) {
                $result[] = ['site' => $site, 'total' => (int) $total];
            }

            return $result;
        });
    }

    private function loadPemeriksaanBySite(): void
    {
        $key = $this->cacheKey('dashboard:pemeriksaanBySite', $this->startDate, $this->endDate);

        $this->pemeriksaanBySite = Cache::remember($key, $this->cacheTTL, function () {
            $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(29)->startOfDay();
            $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

            $counts = DB::table('form_pemeriksaan')
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [$start, $end])
                ->join('assets', 'assets.id', '=', 'form_pemeriksaan.asset_id')
                ->leftJoin('sites', 'sites.id_site', '=', 'assets.site_location_asset')
                ->selectRaw('COALESCE(sites.site, assets.site_location_asset, assets.operating_unit, ?) as site_name, COUNT(*) as total', ['unknown'])
                ->groupBy('site_name')
                ->orderByDesc('total')
                ->pluck('total', 'site_name')
                ->toArray();

            $result = [];
            foreach ($counts as $site => $total) {
                $result[] = ['site' => $site, 'total' => (int) $total];
            }

            return $result;
        });
    }

    private function loadTopAssets(): void
    {
        $key = $this->cacheKey('dashboard:topAssets', $this->filterOperatingUnit ?? '');

        $this->topAssets = Cache::remember($key, $this->cacheTTL, function () {
            $query = DB::table('form_pemeriksaan')
                ->join('assets', 'assets.id', '=', 'form_pemeriksaan.asset_id')
                ->whereNull('form_pemeriksaan.deleted_at')
                ->select('assets.id', 'assets.nama_perangkat', 'assets.no_asset', 'assets.operating_unit', 'assets.site_location_asset')
                ->selectRaw('COUNT(form_pemeriksaan.id) as total_pemeriksaan')
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
                    'id' => $a->id,
                    'nama_perangkat' => $a->nama_perangkat,
                    'no_asset' => $a->no_asset,
                    'operating_unit' => $siteNames[$a->operating_unit] ?? ($a->operating_unit ?? '-'),
                    'site_location' => $siteNames[$a->site_location_asset] ?? ($a->site_location_asset ?? '-'),
                    'total' => (int) $a->total_pemeriksaan,
                ];
            }

            return $result;
        });
    }

    private function loadTrendPerawatanBulanan(): void
    {
        $key = $this->cacheKey('dashboard:trendBulanan', $this->filterTrendAssetOu, $this->filterTrendSiteLocation, $this->filterTrendSiteUser);

        $this->trendPerawatanBulanan = Cache::remember($key, $this->cacheTTL, function () {
            return $this->trendQuery()
                ->select(
                    DB::raw("DATE_FORMAT(submitted_at, '%Y-%m') as month"),
                    DB::raw('count(*) as total')
                )
                ->where('submitted_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray();
        });
    }

    private function loadTrendPerawatanHarian(): void
    {
        $key = $this->cacheKey('dashboard:trendHarian', $this->filterTrendAssetOu, $this->filterTrendSiteLocation, $this->filterTrendSiteUser);

        $this->trendPerawatanHarian = Cache::remember($key, $this->cacheTTL, function () {
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

            return $trend;
        });
    }

    private function loadPerawatanVsBelumByOperatingUnit(): void
    {
        $key = $this->cacheKey('dashboard:pvb', $this->filterAssetStatus ?: 'all');

        $this->perawatanVsBelum = Cache::remember($key, $this->cacheTTL, function () {
            $dilakukanSub = FormPerawatan::whereNotNull('submitted_at')
                ->join('assets', 'assets.id', '=', 'form_perawatan.asset_id')
                ->whereNotNull('assets.operating_unit')
                ->where('assets.operating_unit', '!=', '')
                ->when($this->filterAssetStatus, fn ($q) => $q->where('assets.status', $this->filterAssetStatus))
                ->select('assets.operating_unit', DB::raw('COUNT(DISTINCT assets.id) as dilakukan'))
                ->groupBy('assets.operating_unit');

            $dilakukan = (clone $dilakukanSub)->pluck('dilakukan', 'operating_unit')->toArray();

            $totals = Asset::whereNotNull('operating_unit')
                ->where('operating_unit', '!=', '')
                ->when($this->filterAssetStatus, fn ($q) => $q->where('status', $this->filterAssetStatus))
                ->select('operating_unit', DB::raw('COUNT(*) as total'))
                ->groupBy('operating_unit')
                ->pluck('total', 'operating_unit')
                ->toArray();

            $siteNames = Site::whereIn('id_site', array_keys($totals))
                ->pluck('site', 'id_site')
                ->toArray();

            $result = [];
            foreach ($totals as $ouId => $total) {
                $d = $dilakukan[$ouId] ?? 0;
                $result[] = [
                    'operating_unit_id' => $ouId,
                    'operating_unit' => $siteNames[$ouId] ?? $ouId,
                    'dilakukan' => (int) $d,
                    'belum' => (int) ($total - $d),
                    'total' => (int) $total,
                ];
            }

            usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);

            return $result;
        });
    }

    private function loadEmpAssetSites(): void
    {
        $this->empAssetSites = Cache::remember('dashboard:empAssetSites', $this->cacheTTL, function () {
            return Site::whereIn('id_site', Employee::whereNotNull('site')
                ->where('site', '!=', '')
                ->where('status', Employee::STATUS_ACTIVE)
                ->distinct()
                ->pluck('site'))
                ->orderBy('site')
                ->get(['id_site', 'site'])
                ->map(fn ($s) => ['id' => $s->id_site, 'name' => "{$s->id_site} - {$s->site}"])
                ->toArray();
        });
    }

    private function loadEmployeesAssetBySite(): void
    {
        $key = $this->cacheKey('dashboard:empAsset', $this->filterEmpAssetSite ?: 'all');

        $this->employeesAssetBySite = Cache::remember($key, $this->cacheTTL, function () {
            $assignedNik = Asset::whereNotNull('assigned_employee_id')
                ->where('assigned_employee_id', '!=', '')
                ->distinct()
                ->pluck('assigned_employee_id')
                ->toArray();

            $query = Employee::whereNotNull('site')
                ->where('site', '!=', '')
                ->where('status', Employee::STATUS_ACTIVE);

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

            return $result;
        });
    }

    private function loadOrgHierarchy(): void
    {
        $this->orgHierarchy = Cache::remember('dashboard:orgHierarchy', $this->cacheTTL, function () {
            $employees = Employee::whereNull('deleted_at')
                ->get(['nik', 'directorate_id', 'divisi_id', 'departement_id', 'sub_departement_id']);

            $directorates = Directorate::with(['divisis.departements.subDepartements'])->get();

            $hierarchy = [];
            $di = 0;
            foreach ($directorates as $dir) {
                $dirCount = $employees->where('directorate_id', $dir->id)->count();
                $divisis = [];
                $vi = 0;
                foreach ($dir->divisis as $div) {
                    $divCount = $employees->where('divisi_id', $div->id)->count();
                    $departements = [];
                    $dei = 0;
                    foreach ($div->departements as $dep) {
                        $depCount = $employees->where('departement_id', $dep->id)->count();
                        $subDeps = [];
                        $si = 0;
                        foreach ($dep->subDepartements as $sub) {
                            $subDeps[] = [
                                'key' => "d{$di}v{$vi}e{$dei}s{$si}",
                                'name' => $sub->name,
                                'count' => $employees->where('sub_departement_id', $sub->id)->count(),
                            ];
                            $si++;
                        }
                        $departements[] = [
                            'key' => "d{$di}v{$vi}e{$dei}",
                            'name' => $dep->name,
                            'count' => $depCount,
                            'sub_departements' => $subDeps,
                        ];
                        $dei++;
                    }
                    $divisis[] = [
                        'key' => "d{$di}v{$vi}",
                        'name' => $div->name,
                        'count' => $divCount,
                        'departements' => $departements,
                    ];
                    $vi++;
                }
                $hierarchy[] = [
                    'key' => "d{$di}",
                    'name' => $dir->name,
                    'count' => $dirCount,
                    'divisis' => $divisis,
                ];
                $di++;
            }

            return $hierarchy;
        });
    }

    private function loadEmpStatusSites(): void
    {
        $this->empStatusSites = Cache::remember('dashboard:empStatusSites', $this->cacheTTL, function () {
            return Site::whereIn('id_site', Employee::whereNull('deleted_at')
                ->whereNotNull('site')
                ->where('site', '!=', '')
                ->distinct()
                ->pluck('site'))
                ->orderBy('site')
                ->get(['id_site', 'site'])
                ->map(fn ($s) => ['id' => $s->id_site, 'name' => "{$s->id_site} - {$s->site}"])
                ->toArray();
        });
    }

    private function loadEmpStatusData(): void
    {
        $key = $this->cacheKey('dashboard:empStatus', $this->filterEmpStatusSite ?: 'all');

        $this->empStatusData = Cache::remember($key, $this->cacheTTL, function () {
            $query = Employee::whereNull('deleted_at')
                ->whereNotNull('site')
                ->where('site', '!=', '');

            if ($this->filterEmpStatusSite) {
                $query->where('site', $this->filterEmpStatusSite);
            }

            $active = (clone $query)->where('status', Employee::STATUS_ACTIVE)->count();
            $resigned = (clone $query)->where('status', Employee::STATUS_RESIGNED)->count();

            return [
                'active' => $active,
                'resigned' => $resigned,
            ];
        });
    }

    public function clearDashboardCache(): void
    {
        $keys = [
            'dashboard:operatingUnits',
            'dashboard:trendAssetOus',
            'dashboard:trendSiteLocations',
            'dashboard:trendSiteUsers',
            'dashboard:empAssetSites',
            'dashboard:empStatusSites',
            'dashboard:orgHierarchy',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}

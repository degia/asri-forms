<?php

namespace App\Livewire\Dashboard;

use App\Models\Asset;
use App\Models\Directorate;
use App\Models\Employee;
use App\Models\FormPerawatan;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public ?string $filterOperatingUnit = '';

    public ?string $filterPerawatanStatus = '';

    public string $filterPerawatanGroup = 'site';

    public string $filterPemeriksaanGroup = 'site';

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

    public string $filterKondisiYear = '';

    public string $filterKondisiOu = '';

    public array $perawatanKondisi = [];

    public array $kondisiYears = [];

    public array $kondisiOus = [];

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
        $this->loadKondisiFilterOptions();
        $this->filterKondisiYear = '';
        $this->filterKondisiOu = Site::where('site', 'PIK Avenue')->value('id_site') ?? '';
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

    public function updatedFilterPerawatanStatus(): void
    {
        $this->loadPerawatanBySite();
    }

    public function updatedFilterPerawatanGroup(): void
    {
        $this->loadPerawatanBySite();
    }

    public function updatedFilterPemeriksaanGroup(): void
    {
        $this->loadPemeriksaanBySite();
    }

    public function updatedFilterAssetStatus(): void
    {
        $this->loadPerawatanVsBelumByOperatingUnit();
        $this->loadPerawatanKondisi();
    }

    public function updatedFilterEmpAssetSite(): void
    {
        $this->loadEmployeesAssetBySite();
    }

    public function updatedFilterEmpStatusSite(): void
    {
        $this->loadEmpStatusData();
    }

    public function updatedFilterKondisiYear(): void
    {
        $this->loadPerawatanKondisi();
    }

    public function updatedFilterKondisiOu(): void
    {
        $this->loadPerawatanKondisi();
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

    private function trendQuery(): Builder
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
        $this->loadPerawatanKondisi();
        $this->dispatch('chartsUpdated');
    }

    private function cacheKey(string $prefix, ...$parts): string
    {
        $key = $prefix.':'.md5(implode(':', array_map(fn ($p) => (string) $p, $parts)));

        $registry = Cache::get('dashboard:cacheRegistry', []);
        if (! in_array($key, $registry)) {
            $registry[] = $key;
            Cache::put('dashboard:cacheRegistry', $registry, $this->cacheTTL);
        }

        return $key;
    }

    private function loadPerawatanBySite(): void
    {
        $key = $this->cacheKey('dashboard:perawatanBySite', $this->startDate, $this->endDate, $this->filterPerawatanStatus ?: 'all', $this->filterPerawatanGroup);

        $this->perawatanBySite = Cache::remember($key, $this->cacheTTL, function () {
            $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(29)->startOfDay();
            $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

            $query = DB::table('form_perawatan')
                ->whereNull('form_perawatan.deleted_at')
                ->whereNotNull('submitted_at')
                ->where('form_perawatan.status', '!=', 'draft')
                ->whereBetween('submitted_at', [$start, $end]);

            if ($this->filterPerawatanStatus) {
                $query->where('form_perawatan.status', $this->filterPerawatanStatus);
            }

            if ($this->filterPerawatanGroup === 'site') {
                $query->leftJoin('sites', 'sites.id_site', '=', 'form_perawatan.site_location');
                $groupColumn = 'COALESCE(sites.site, form_perawatan.site_location)';
            } else {
                $query->leftJoin('users', 'users.email', '=', 'form_perawatan.user_id');
                $groupColumn = "COALESCE(NULLIF(TRIM(users.name), ''), 'Tidak Diketahui')";
            }

            $rows = $query->selectRaw("{$groupColumn} as kelompok, form_perawatan.status as status, COUNT(*) as total")
                ->groupBy('kelompok', 'form_perawatan.status')
                ->orderByDesc('total')
                ->get()
                ->toArray();

            $grouped = [];
            $statusTotals = [];
            foreach ($rows as $row) {
                $kelompok = $row->kelompok;
                $status = $row->status;
                $total = (int) $row->total;

                if (! isset($grouped[$kelompok])) {
                    $grouped[$kelompok] = ['kelompok' => $kelompok, 'total' => 0, 'statuses' => []];
                }
                $grouped[$kelompok]['total'] += $total;
                $grouped[$kelompok]['statuses'][$status] = $total;

                $statusTotals[$status] = ($statusTotals[$status] ?? 0) + $total;
            }

            $result = array_values($grouped);
            usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);

            return ['rows' => $result, 'statusTotals' => $statusTotals];
        });
    }

    private function loadPemeriksaanBySite(): void
    {
        $key = $this->cacheKey('dashboard:pemeriksaanBySite', $this->startDate, $this->endDate, $this->filterPemeriksaanGroup ?: 'all');

        $this->pemeriksaanBySite = Cache::remember($key, $this->cacheTTL, function () {
            $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(29)->startOfDay();
            $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

            $query = DB::table('form_pemeriksaan')
                ->whereNull('form_pemeriksaan.deleted_at')
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [$start, $end]);

            if ($this->filterPemeriksaanGroup === 'site') {
                $query->leftJoin('sites', 'sites.id_site', '=', 'form_pemeriksaan.site_location');
                $groupColumn = 'COALESCE(sites.site, form_pemeriksaan.site_location) as site_name';
            } else {
                $query->leftJoin('users', 'users.email', '=', 'form_pemeriksaan.user_id');
                $groupColumn = "COALESCE(NULLIF(TRIM(users.name), ''), 'Tidak Diketahui') as site_name";
            }

            $counts = $query->selectRaw("{$groupColumn}, COUNT(*) as total")
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
                ->whereNotNull('form_pemeriksaan.submitted_at')
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
                ->where('status', Employee::STATUS_ACTIVE)
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
                                'id' => $sub->id,
                                'name' => $sub->name,
                                'count' => $employees->where('sub_departement_id', $sub->id)->count(),
                            ];
                            $si++;
                        }
                        $departements[] = [
                            'key' => "d{$di}v{$vi}e{$dei}",
                            'id' => $dep->id,
                            'name' => $dep->name,
                            'count' => $depCount,
                            'sub_departements' => $subDeps,
                        ];
                        $dei++;
                    }
                    $divisis[] = [
                        'key' => "d{$di}v{$vi}",
                        'id' => $div->id,
                        'name' => $div->name,
                        'count' => $divCount,
                        'departements' => $departements,
                    ];
                    $vi++;
                }
                $hierarchy[] = [
                    'key' => "d{$di}",
                    'id' => $dir->id,
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

    private function loadKondisiFilterOptions(): void
    {
        $this->kondisiYears = Cache::remember('dashboard:kondisiYears', $this->cacheTTL, function () {
            return FormPerawatan::whereNotNull('submitted_at')
                ->whereNotNull('kondisi_akhir')
                ->where('kondisi_akhir', '!=', '')
                ->pluck('submitted_at')
                ->map(fn ($d) => Carbon::parse($d)->format('Y'))
                ->unique()
                ->sortDesc()
                ->values()
                ->toArray();
        });

        $this->kondisiOus = Cache::remember('dashboard:kondisiOus', $this->cacheTTL, function () {
            return Site::whereIn('id_site', FormPerawatan::whereNotNull('submitted_at')
                ->whereNotNull('kondisi_akhir')
                ->where('kondisi_akhir', '!=', '')
                ->join('assets', 'assets.id', '=', 'form_perawatan.asset_id')
                ->whereNotNull('assets.operating_unit')
                ->where('assets.operating_unit', '!=', '')
                ->distinct()
                ->pluck('assets.operating_unit'))
                ->orderBy('site')
                ->get(['id_site', 'site'])
                ->map(fn ($s) => ['id' => $s->id_site, 'name' => $s->site])
                ->toArray();
        });
    }

    private function loadPerawatanKondisi(): void
    {
        $key = $this->cacheKey('dashboard:perawatanKondisi', $this->filterKondisiYear ?: 'all', $this->filterKondisiOu ?: 'all', $this->filterAssetStatus ?: 'all');

        $this->perawatanKondisi = Cache::remember($key, $this->cacheTTL, function () {
            $query = DB::table('form_perawatan')
                ->join('assets', 'assets.id', '=', 'form_perawatan.asset_id')
                ->whereNull('form_perawatan.deleted_at')
                ->whereNotNull('form_perawatan.submitted_at')
                ->whereNotNull('form_perawatan.kondisi_akhir')
                ->where('form_perawatan.kondisi_akhir', '!=', '')
                ->whereNotNull('assets.operating_unit')
                ->where('assets.operating_unit', '!=', '');

            if ($this->filterKondisiYear) {
                $query->whereYear('form_perawatan.submitted_at', (int) $this->filterKondisiYear);
            }

            if ($this->filterKondisiOu) {
                $query->where('assets.operating_unit', $this->filterKondisiOu);
            }

            if ($this->filterAssetStatus) {
                $query->where('assets.status', $this->filterAssetStatus);
            }

            $rows = $query->select(
                'assets.operating_unit',
                'form_perawatan.kondisi_akhir as kondisi',
                DB::raw('COUNT(DISTINCT form_perawatan.asset_id) as total')
            )
                ->groupBy('assets.operating_unit', 'form_perawatan.kondisi_akhir')
                ->get();

            $siteIds = collect($rows)->pluck('operating_unit')->unique()->values()->toArray();
            $siteNames = $siteIds ? Site::whereIn('id_site', $siteIds)->pluck('site', 'id_site')->toArray() : [];

            $result = [];
            foreach ($rows as $row) {
                $ouId = $row->operating_unit;
                $kondisi = $row->kondisi === 'good_normal' ? 'good' : $row->kondisi;
                if (! isset($result[$ouId])) {
                    $result[$ouId] = [
                        'ou_id' => $ouId,
                        'ou' => $siteNames[$ouId] ?? $ouId,
                        'totals' => ['good' => 0, 'fair' => 0, 'critical' => 0, 'poor' => 0],
                        'total' => 0,
                    ];
                }
                if (isset($result[$ouId]['totals'][$kondisi])) {
                    $result[$ouId]['totals'][$kondisi] += (int) $row->total;
                    $result[$ouId]['total'] += (int) $row->total;
                }
            }

            $result = array_values($result);
            usort($result, fn ($a, $b) => $b['total'] <=> $a['total']);

            return $result;
        });
    }

    public function clearDashboardCache(): void
    {
        self::clearAllDashboardCache();
    }

    public static function clearAllDashboardCache(): void
    {
        $staticKeys = [
            'dashboard:operatingUnits',
            'dashboard:trendAssetOus',
            'dashboard:trendSiteLocations',
            'dashboard:trendSiteUsers',
            'dashboard:empAssetSites',
            'dashboard:empStatusSites',
            'dashboard:orgHierarchy',
            'dashboard:kondisiYears',
            'dashboard:kondisiOus',
        ];

        foreach ($staticKeys as $key) {
            Cache::forget($key);
        }

        $registry = Cache::get('dashboard:cacheRegistry', []);
        foreach ($registry as $key) {
            Cache::forget($key);
        }
        Cache::forget('dashboard:cacheRegistry');
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}

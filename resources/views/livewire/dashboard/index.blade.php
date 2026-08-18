<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Dashboard') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Laporan & Analitik') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <label class="text-xs text-muted">{{ __('Dari') }}:</label>
            <input wire:model.live.debounce.500ms="startDate" type="date"
                class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            <label class="text-xs text-muted">{{ __('Sampai') }}:</label>
            <input wire:model.live.debounce.500ms="endDate" type="date"
                class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
        </div>
    </div>

    {{-- Report 1: Perawatan by Site --}}
    <div class="glass-card p-5">
        <h3 class="text-sm font-bold text-primary mb-4">{{ __('Laporan Perawatan Perangkat by Site Lokasi') }}</h3>
        @if(count($perawatanBySite) > 0)
            @php
                $pwLabels = json_encode(array_column($perawatanBySite, 'site'));
                $pwData = json_encode(array_column($perawatanBySite, 'total'));
            @endphp
            <div x-data="{
                chart: null,
                init() {
                    this.$nextTick(() => {
                        const ctx = this.$refs.chartPerawatan;
                        if (!ctx || typeof Chart === 'undefined') return;
                        if (this.chart) this.chart.destroy();
                        const existing = Chart.getChart(ctx);
                        if (existing) existing.destroy();
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $pwLabels }},
                                datasets: [{
                                    label: '{{ __('Jumlah Perawatan') }}',
                                    data: {{ $pwData }},
                                    backgroundColor: 'rgba(168, 85, 247, 0.6)',
                                    borderColor: 'rgba(168, 85, 247, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { ticks: { color: 'rgb(156,163,175)', stepSize: 1 }, grid: { color: 'rgb(229,231,235)' } },
                                    y: { ticks: { color: 'rgb(156,163,175)', font: { size: 11 } }, grid: { display: false } }
                                }
                            }
                        });
                    });
                },
                destroy() { if (this.chart) this.chart.destroy(); }
            }" style="height: {{ max(200, count($perawatanBySite) * 40 + 60) }}px;" wire:ignore wire:key="pw-{{ md5($pwLabels.$pwData) }}">
                <canvas x-ref="chartPerawatan"></canvas>
            </div>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data perawatan pada periode ini') }}</p>
        @endif
        <div class="mt-3 text-right">
            <a href="{{ route('admin.perawatan.index') }}" wire:navigate class="text-xs font-medium transition-colors duration-200" style="color: var(--color-primary);">
                {{ __('Lihat Semua Perawatan') }} →
            </a>
        </div>
    </div>

    {{-- Report 2: Pemeriksaan by Site --}}
    <div class="glass-card p-5">
        <h3 class="text-sm font-bold text-primary mb-4">{{ __('Laporan Pemeriksaan Perangkat by Site Lokasi') }}</h3>
        @if(count($pemeriksaanBySite) > 0)
            @php
                $pmLabels = json_encode(array_column($pemeriksaanBySite, 'site'));
                $pmData = json_encode(array_column($pemeriksaanBySite, 'total'));
            @endphp
            <div x-data="{
                chart: null,
                init() {
                    this.$nextTick(() => {
                        const ctx = this.$refs.chartPemeriksaan;
                        if (!ctx || typeof Chart === 'undefined') return;
                        if (this.chart) this.chart.destroy();
                        const existing = Chart.getChart(ctx);
                        if (existing) existing.destroy();
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $pmLabels }},
                                datasets: [{
                                    label: '{{ __('Jumlah Pemeriksaan') }}',
                                    data: {{ $pmData }},
                                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                                    borderColor: 'rgba(59, 130, 246, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { ticks: { color: 'rgb(156,163,175)', stepSize: 1 }, grid: { color: 'rgb(229,231,235)' } },
                                    y: { ticks: { color: 'rgb(156,163,175)', font: { size: 11 } }, grid: { display: false } }
                                }
                            }
                        });
                    });
                },
                destroy() { if (this.chart) this.chart.destroy(); }
            }" style="height: {{ max(200, count($pemeriksaanBySite) * 40 + 60) }}px;" wire:ignore wire:key="pm-{{ md5($pmLabels.$pmData) }}">
                <canvas x-ref="chartPemeriksaan"></canvas>
            </div>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data pemeriksaan pada periode ini') }}</p>
        @endif
        <div class="mt-3 text-right">
            <a href="{{ route('admin.pemeriksaan.index') }}" wire:navigate class="text-xs font-medium transition-colors duration-200" style="color: var(--color-primary);">
                {{ __('Lihat Semua Pemeriksaan') }} →
            </a>
        </div>
    </div>

    {{-- Report 4: Top 10 Assets --}}
    <div class="glass-card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-primary">{{ __('10 Perangkat yang Sering Diperiksa') }}</h3>
            <div class="flex items-center gap-2">
                <label class="text-xs text-muted">{{ __('Operating Unit') }}:</label>
                <select wire:model.live.debounce.500ms="filterOperatingUnit"
                    class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach($operatingUnits as $ou)
                        <option value="{{ $ou['id'] }}">{{ $ou['name'] }} ({{ $ou['id'] }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if(count($topAssets) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="text-left py-2 text-xs text-muted font-medium">#</th>
                            <th class="text-left py-2 text-xs text-muted font-medium">{{ __('No Asset') }}</th>
                            <th class="text-left py-2 text-xs text-muted font-medium">{{ __('Nama Perangkat') }}</th>
                            <th class="text-left py-2 text-xs text-muted font-medium hidden md:table-cell">{{ __('Operating Unit') }}</th>
                            <th class="text-left py-2 text-xs text-muted font-medium hidden lg:table-cell">{{ __('Site Location') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Jumlah') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($topAssets as $i => $a)
                            <tr class="transition-colors cursor-pointer" onclick="window.Livewire.navigate('{{ route('admin.assets.index', ['search' => $a['no_asset']]) }}')" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor=''">
                                <td class="py-2.5 text-muted text-xs">{{ $i + 1 }}</td>
                                <td class="py-2.5 font-mono text-secondary text-xs">{{ $a['no_asset'] }}</td>
                                <td class="py-2.5 font-medium text-primary">{{ $a['nama_perangkat'] }}</td>
                                <td class="py-2.5 text-secondary text-xs hidden md:table-cell">{{ $a['operating_unit'] }}</td>
                                <td class="py-2.5 text-secondary text-xs hidden lg:table-cell">{{ $a['site_location'] }}</td>
                                <td class="py-2.5 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-500/15 text-blue-400">
                                        {{ $a['total'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-right">
                <a href="{{ route('admin.assets.index') }}" wire:navigate class="text-xs font-medium transition-colors duration-200" style="color: var(--color-primary);">
                    {{ __('Lihat Semua Asset') }} →
                </a>
            </div>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data pemeriksaan asset') }}</p>
        @endif
    </div>

    {{-- Report 5: Tren Perawatan (Bulanan / Harian) --}}
    <div class="glass-card p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-primary">{{ __('Tren Perawatan') }}</h3>
            <div class="flex items-center gap-1 p-1 rounded-lg w-fit" style="background: var(--color-bg-tertiary);">
                <button wire:click="$set('trendMode', 'bulanan')" type="button"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors duration-200 {{ $trendMode === 'bulanan' ? 'text-white' : 'text-muted' }}"
                    style="{{ $trendMode === 'bulanan' ? 'background: var(--color-primary);' : '' }}">
                    {{ __('Tren Perawatan per Bulan (12 Bulan)') }}
                </button>
                <button wire:click="$set('trendMode', 'harian')" type="button"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors duration-200 {{ $trendMode === 'harian' ? 'text-white' : 'text-muted' }}"
                    style="{{ $trendMode === 'harian' ? 'background: var(--color-primary);' : '' }}">
                    {{ __('Tren Perawatan per Hari (30 Hari)') }}
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center gap-2 flex-wrap mb-4">
            <label class="text-xs text-muted">{{ __('Asset Operating Unit') }}:</label>
            <select wire:model.live.debounce.300ms="filterTrendAssetOu"
                class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="">{{ __('Semua') }}</option>
                @foreach($trendAssetOus as $ou)
                    <option value="{{ $ou['id'] }}">{{ $ou['name'] }}</option>
                @endforeach
            </select>
            <label class="text-xs text-muted">{{ __('Location Site Perawatan') }}:</label>
            <select wire:model.live.debounce.300ms="filterTrendSiteLocation"
                class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="">{{ __('Semua') }}</option>
                @foreach($trendSiteLocations as $s)
                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                @endforeach
            </select>
            <label class="text-xs text-muted">{{ __('Site User') }}:</label>
            <select wire:model.live.debounce.300ms="filterTrendSiteUser"
                class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="">{{ __('Semua') }}</option>
                @foreach($trendSiteUsers as $s)
                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                @endforeach
            </select>
        </div>

        @if($trendMode === 'bulanan')
            @if(count($trendPerawatanBulanan) > 0)
                @php
                    $trLabels = json_encode(array_keys($trendPerawatanBulanan));
                    $trData = json_encode(array_values($trendPerawatanBulanan));
                @endphp
                <div x-data="{
                    chart: null,
                    init() {
                        this.$nextTick(() => {
                            const ctx = this.$refs.chartTrend;
                            if (!ctx || typeof Chart === 'undefined') return;
                            if (this.chart) this.chart.destroy();
                            const existing = Chart.getChart(ctx);
                            if (existing) existing.destroy();
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: {{ $trLabels }},
                                    datasets: [{
                                        label: '{{ __('Jumlah Perawatan') }}',
                                        data: {{ $trData }},
                                        borderColor: 'rgba(168, 85, 247, 1)',
                                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                                        fill: true,
                                        tension: 0.4,
                                        pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointRadius: 4,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { ticks: { color: 'rgb(156,163,175)', font: { size: 10 } }, grid: { display: false } },
                                        y: { ticks: { color: 'rgb(156,163,175)', stepSize: 1 }, grid: { color: 'rgb(229,231,235)' } }
                                    }
                                }
                            });
                        });
                    },
                    destroy() { if (this.chart) this.chart.destroy(); }
                }" style="height: 220px;" wire:ignore wire:key="tr-{{ md5($trLabels.$trData) }}">
                    <canvas x-ref="chartTrend"></canvas>
                </div>
            @else
                <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data tren perawatan') }}</p>
            @endif
        @else
            @if(count($trendPerawatanHarian) > 0)
                @php
                    $trhLabels = json_encode(array_keys($trendPerawatanHarian));
                    $trhData = json_encode(array_values($trendPerawatanHarian));
                @endphp
                <div x-data="{
                    chart: null,
                    init() {
                        this.$nextTick(() => {
                            const ctx = this.$refs.chartTrendHarian;
                            if (!ctx || typeof Chart === 'undefined') return;
                            if (this.chart) this.chart.destroy();
                            const existing = Chart.getChart(ctx);
                            if (existing) existing.destroy();
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: {{ $trhLabels }},
                                    datasets: [{
                                        label: '{{ __('Jumlah Perawatan') }}',
                                        data: {{ $trhData }},
                                        borderColor: 'rgba(168, 85, 247, 1)',
                                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                                        fill: true,
                                        tension: 0.4,
                                        pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointRadius: 2,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { ticks: { color: 'rgb(156,163,175)', font: { size: 10 }, maxRotation: 45, minRotation: 0 }, grid: { display: false } },
                                        y: { ticks: { color: 'rgb(156,163,175)', stepSize: 1 }, grid: { color: 'rgb(229,231,235)' } }
                                    }
                                }
                            });
                        });
                    },
                    destroy() { if (this.chart) this.chart.destroy(); }
                }" style="height: 220px;" wire:ignore wire:key="trh-{{ md5($trhLabels.$trhData) }}">
                    <canvas x-ref="chartTrendHarian"></canvas>
                </div>
            @else
                <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data tren perawatan') }}</p>
            @endif
        @endif
        <div class="mt-3 text-right">
            <a href="{{ route('admin.perawatan.index') }}" wire:navigate class="text-xs font-medium transition-colors duration-200" style="color: var(--color-primary);">
                {{ __('Lihat Semua Perawatan') }} →
            </a>
        </div>
    </div>

    {{-- Report 6: Perawatan vs Belum by Operating Unit --}}
    <div class="glass-card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-primary">{{ __('Perangkat Dilakukan Perawatan vs Belum Perawatan by Operating Unit') }}</h3>
            <div class="flex items-center gap-2">
                <label class="text-xs text-muted">{{ __('Status Asset') }}:</label>
                <select wire:model.live.debounce.300ms="filterAssetStatus"
                    class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        @if(count($perawatanVsBelum) > 0)
            @php
                $pvbLabels = json_encode(array_column($perawatanVsBelum, 'operating_unit'));
                $pvbDilakukan = json_encode(array_column($perawatanVsBelum, 'dilakukan'));
                $pvbBelum = json_encode(array_column($perawatanVsBelum, 'belum'));
                $chartHeight = max(260, count($perawatanVsBelum) * 50 + 80);
            @endphp
            <div x-data="{
                chart: null,
                init() {
                    this.$nextTick(() => {
                        const ctx = this.$refs.chartPerawatanVsBelum;
                        if (!ctx || typeof Chart === 'undefined') return;
                        if (this.chart) this.chart.destroy();
                        const existing = Chart.getChart(ctx);
                        if (existing) existing.destroy();
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $pvbLabels }},
                                datasets: [
                                    {
                                        label: '{{ __('Dilakukan Perawatan') }}',
                                        data: {{ $pvbDilakukan }},
                                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                        borderColor: 'rgba(16, 185, 129, 1)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                    },
                                    {
                                        label: '{{ __('Belum Perawatan') }}',
                                        data: {{ $pvbBelum }},
                                        backgroundColor: 'rgba(239, 68, 68, 0.5)',
                                        borderColor: 'rgba(239, 68, 68, 1)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: { color: 'rgb(156,163,175)', font: { size: 11 }, usePointStyle: true, pointStyle: 'rectRounded' }
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: true,
                                        ticks: { color: 'rgb(156,163,175)', stepSize: 1 },
                                        grid: { color: 'rgb(229,231,235)' },
                                        title: { display: true, text: '{{ __('Jumlah Asset') }}', color: 'rgb(156,163,175)', font: { size: 11 } }
                                    },
                                    y: {
                                        stacked: true,
                                        ticks: { color: 'rgb(156,163,175)', font: { size: 11 } },
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                    });
                },
                destroy() { if (this.chart) this.chart.destroy(); }
            }" style="height: {{ $chartHeight }}px;" wire:ignore wire:key="pvb-{{ md5($pvbLabels.$pvbDilakukan.$pvbBelum) }}">
                <canvas x-ref="chartPerawatanVsBelum"></canvas>
            </div>

            {{-- Summary Table --}}
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="text-left py-2 text-xs text-muted font-medium">{{ __('Operating Unit') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Total Asset') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Dilakukan') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Belum') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">% {{ __('Selesai') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($perawatanVsBelum as $row)
                            @php
                                $pct = $row['total'] > 0 ? round(($row['dilakukan'] / $row['total']) * 100, 1) : 0;
                                $ouId = $row['operating_unit_id'] ?? '';
                            @endphp
                            <tr class="transition-colors" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor=''">
                                <td class="py-2.5 font-medium text-primary">{{ $row['operating_unit'] }}</td>
                                <td class="py-2.5 text-right text-secondary">
                                    <a href="{{ route('admin.assets.index', ['filterOperatingUnit' => $ouId]) }}" wire:navigate class="hover:underline font-semibold" style="color: var(--color-text-secondary);">
                                        {{ $row['total'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('admin.assets.index', ['filterOperatingUnit' => $ouId, 'filterPerawatanStatus' => 'done']) }}" wire:navigate class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold transition-opacity hover:opacity-70" style="background: rgba(16,185,129,0.15); color: #10b981;">
                                        {{ $row['dilakukan'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('admin.assets.index', ['filterOperatingUnit' => $ouId, 'filterPerawatanStatus' => 'pending']) }}" wire:navigate class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold transition-opacity hover:opacity-70" style="background: rgba(239,68,68,0.15); color: #ef4444;">
                                        {{ $row['belum'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 h-1.5 rounded-full overflow-hidden" style="background: var(--color-bg-tertiary);">
                                            <div class="h-full rounded-full" style="width: {{ $pct }}%; background: {{ $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#eab308' : '#ef4444') }};"></div>
                                        </div>
                                        <span class="text-xs text-secondary w-10 text-right">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data asset') }}</p>
        @endif
    </div>

    {{-- Report 6b: Employee Active vs Resigned Bar Chart --}}
    <div class="glass-card p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-primary">{{ __('Employee Active vs Resigned') }}</h3>
            <div class="flex items-center gap-2">
                <label class="text-xs text-muted">{{ __('Site') }}:</label>
                <select wire:model.live.debounce.300ms="filterEmpStatusSite"
                    class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach($empStatusSites as $site)
                        <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $empActive = $empStatusData['active'] ?? 0;
            $empResigned = $empStatusData['resigned'] ?? 0;
            $empTotal = $empActive + $empResigned;
        @endphp

        @if($empTotal > 0)
            @php
                $empChartKey = md5($empActive . $empResigned . $filterEmpStatusSite);
            @endphp
            <div x-data="empBarChart()" wire:ignore wire:key="emp-status-{{ $empChartKey }}"
                 data-active="{{ $empActive }}"
                 data-resigned="{{ $empResigned }}">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div class="flex-1 h-52">
                        <canvas x-ref="empBar"></canvas>
                    </div>
                    <div class="flex-shrink-0 space-y-3 w-full md:w-48">
                        <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--color-bg-secondary);">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background: #22c55e;"></span>
                                <span class="text-sm font-medium" style="color: var(--color-text);">{{ __('Active') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold" style="color: var(--color-text);">{{ $empActive }}</span>
                                <span class="text-xs ml-1" style="color: var(--color-text-muted);">
                                    ({{ $empTotal > 0 ? round(($empActive / $empTotal) * 100, 1) : 0 }}%)
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--color-bg-secondary);">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background: #ef4444;"></span>
                                <span class="text-sm font-medium" style="color: var(--color-text);">{{ __('Resigned') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold" style="color: var(--color-text);">{{ $empResigned }}</span>
                                <span class="text-xs ml-1" style="color: var(--color-text-muted);">
                                    ({{ $empTotal > 0 ? round(($empResigned / $empTotal) * 100, 1) : 0 }}%)
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg font-bold" style="background: var(--color-bg-secondary);">
                            <span class="text-sm" style="color: var(--color-text);">{{ __('Total') }}</span>
                            <span class="text-sm" style="color: var(--color-text);">{{ $empTotal }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function empBarChart() {
                    return {
                        chart: null,
                        init() {
                            this.$nextTick(() => {
                                var ctx = this.$refs.empBar;
                                if (!ctx || typeof Chart === 'undefined') return;
                                if (this.chart) this.chart.destroy();
                                var existing = Chart.getChart(ctx);
                                if (existing) existing.destroy();
                                var active = parseInt(this.$el.dataset.active) || 0;
                                var resigned = parseInt(this.$el.dataset.resigned) || 0;
                                var isDark = document.documentElement.classList.contains('dark');
                                var gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';
                                var textColor = isDark ? '#d1d5db' : '#6b7280';
                                var labels = {!! json_encode([__('Active'), __('Resigned')]) !!};
                                this.chart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            data: [active, resigned],
                                            backgroundColor: ['#22c55e', '#ef4444'],
                                            borderRadius: 6,
                                            maxBarThickness: 80,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        animation: { duration: 400 },
                                        plugins: {
                                            legend: { display: false },
                                            tooltip: {
                                                backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                                titleColor: isDark ? '#f3f4f6' : '#111827',
                                                bodyColor: isDark ? '#d1d5db' : '#4b5563',
                                                borderColor: isDark ? '#374151' : '#e5e7eb',
                                                borderWidth: 1,
                                                padding: 10,
                                                callbacks: {
                                                    label: function(tipCtx) {
                                                        var total = tipCtx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                                        var pct = total > 0 ? ((tipCtx.raw / total) * 100).toFixed(1) : 0;
                                                        return tipCtx.raw + ' (' + pct + '%)';
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            x: { grid: { display: false }, ticks: { color: textColor, font: { size: 12, weight: '500' } } },
                                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, stepSize: 10 } }
                                        }
                                    }
                                });
                            });
                        },
                        destroy() { if (this.chart) this.chart.destroy(); }
                    }
                }
            </script>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data employee') }}</p>
        @endif
    </div>

    {{-- Report 7: Employee Punya vs Tidak Punya Asset by Site --}}
    <div class="glass-card p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-primary">{{ __('Employee Punya vs Tidak Punya Asset by Site') }}</h3>
            <div class="flex items-center gap-2">
                <label class="text-xs text-muted">{{ __('Site') }}:</label>
                <select wire:model.live.debounce.300ms="filterEmpAssetSite"
                    class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach($empAssetSites as $site)
                        <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if(count($employeesAssetBySite) > 0)
            @php
                $eabLabels = json_encode(array_column($employeesAssetBySite, 'site'));
                $eabPunya = json_encode(array_column($employeesAssetBySite, 'punya'));
                $eabTidak = json_encode(array_column($employeesAssetBySite, 'tidak'));
                $eabChartHeight = max(260, count($employeesAssetBySite) * 50 + 80);
            @endphp
            <div x-data="{
                chart: null,
                init() {
                    this.$nextTick(() => {
                        const ctx = this.$refs.chartEmployeesAssetBySite;
                        if (!ctx || typeof Chart === 'undefined') return;
                        if (this.chart) this.chart.destroy();
                        const existing = Chart.getChart(ctx);
                        if (existing) existing.destroy();
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $eabLabels }},
                                datasets: [
                                    {
                                        label: '{{ __('Punya Asset') }}',
                                        data: {{ $eabPunya }},
                                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                        borderColor: 'rgba(16, 185, 129, 1)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                    },
                                    {
                                        label: '{{ __('Tidak Punya Asset') }}',
                                        data: {{ $eabTidak }},
                                        backgroundColor: 'rgba(239, 68, 68, 0.5)',
                                        borderColor: 'rgba(239, 68, 68, 1)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: { color: 'rgb(156,163,175)', font: { size: 11 }, usePointStyle: true, pointStyle: 'rectRounded' }
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: true,
                                        ticks: { color: 'rgb(156,163,175)', stepSize: 1 },
                                        grid: { color: 'rgb(229,231,235)' },
                                        title: { display: true, text: '{{ __('Jumlah Employee') }}', color: 'rgb(156,163,175)', font: { size: 11 } }
                                    },
                                    y: {
                                        stacked: true,
                                        ticks: { color: 'rgb(156,163,175)', font: { size: 11 } },
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                    });
                },
                destroy() { if (this.chart) this.chart.destroy(); }
            }" style="height: {{ $eabChartHeight }}px;" wire:ignore wire:key="eab-{{ md5($eabLabels.$eabPunya.$eabTidak) }}">
                <canvas x-ref="chartEmployeesAssetBySite"></canvas>
            </div>

            {{-- Summary Table --}}
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="text-left py-2 text-xs text-muted font-medium">{{ __('Site') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Total Employee') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Punya Asset') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Tidak Punya Asset') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">% {{ __('Punya') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($employeesAssetBySite as $row)
                            <tr class="transition-colors" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor=''">
                                <td class="py-2.5 font-medium text-primary">{{ $row['site'] }}</td>
                                <td class="py-2.5 text-right text-secondary">
                                    <a href="{{ route('admin.employees.index', ['filterSite' => $row['site_id']]) }}" wire:navigate class="hover:underline font-semibold" style="color: var(--color-text-secondary);">
                                        {{ $row['total'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('admin.employees.index', ['filterSite' => $row['site_id'], 'filterAssetStatus' => 'punya']) }}" wire:navigate class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold transition-opacity hover:opacity-70" style="background: rgba(16,185,129,0.15); color: #10b981;">
                                        {{ $row['punya'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('admin.employees.index', ['filterSite' => $row['site_id'], 'filterAssetStatus' => 'tidak']) }}" wire:navigate class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold transition-opacity hover:opacity-70" style="background: rgba(239,68,68,0.15); color: #ef4444;">
                                        {{ $row['tidak'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 h-1.5 rounded-full overflow-hidden" style="background: var(--color-bg-tertiary);">
                                            <div class="h-full rounded-full" style="width: {{ $row['pct'] }}%; background: {{ $row['pct'] >= 80 ? '#10b981' : ($row['pct'] >= 50 ? '#eab308' : '#ef4444') }};"></div>
                                        </div>
                                        <span class="text-xs text-secondary w-10 text-right">{{ $row['pct'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data employee') }}</p>
        @endif
        <div class="mt-3 text-right">
            <a href="{{ route('admin.employees.index') }}" wire:navigate class="text-xs font-medium transition-colors duration-200" style="color: var(--color-primary);">
                {{ __('Lihat Semua Employees') }} →
            </a>
        </div>
    </div>

    {{-- Report 8: Organization Hierarchy Tree --}}
    @php
        $totalEmployees = count($orgHierarchy) > 0 ? collect($orgHierarchy)->sum('count') : 0;
    @endphp
    <div class="glass-card p-5 overflow-hidden">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="text-lg font-semibold" style="color: var(--color-text);">
                {{ __('Hierarki Organisasi') }}
                <span class="text-sm font-normal ml-2 px-2 py-0.5 rounded-full" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);">
                    {{ $totalEmployees }} {{ __('Employee') }}
                </span>
            </h3>
            <div class="flex items-center gap-1.5" x-data="orgTree()">
                <button @click="toggleAll(false)"
                    class="text-xs px-2.5 py-1.5 rounded-lg font-medium transition-colors"
                    style="background: var(--color-bg-secondary); color: var(--color-text-secondary); border: 1px solid var(--color-border);">
                    {{ __('Sembunyikan Semua') }}
                </button>
                <button @click="toggleAll(true)"
                    class="text-xs px-2.5 py-1.5 rounded-lg font-medium transition-colors"
                    style="background: var(--color-bg-secondary); color: var(--color-text-secondary); border: 1px solid var(--color-border);">
                    {{ __('Tampilkan Semua') }}
                </button>
                <div class="w-px h-5 mx-1" style="background: var(--color-border);"></div>
                <button @click="zoomOut()"
                    class="w-7 h-7 flex items-center justify-center rounded-lg font-bold text-sm transition-colors"
                    style="background: var(--color-bg-secondary); color: var(--color-text); border: 1px solid var(--color-border);"
                    title="Zoom Out">
                    −
                </button>
                <span class="text-xs font-medium w-12 text-center" style="color: var(--color-text-secondary);" x-text="Math.round(scale * 100) + '%'"></span>
                <button @click="zoomIn()"
                    class="w-7 h-7 flex items-center justify-center rounded-lg font-bold text-sm transition-colors"
                    style="background: var(--color-bg-secondary); color: var(--color-text); border: 1px solid var(--color-border);"
                    title="Zoom In">
                    +
                </button>
                <button @click="resetZoom()"
                    class="text-xs px-2 py-1.5 rounded-lg font-medium transition-colors"
                    style="background: var(--color-bg-secondary); color: var(--color-text-secondary); border: 1px solid var(--color-border);"
                    title="Reset Zoom">
                    {{ __('Reset') }}
                </button>
            </div>
        </div>

        @if(count($orgHierarchy) > 0)
            <style>
                .org-tree-wrap { overflow: auto; cursor: grab; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-bg-primary); }
                .org-tree-wrap:active { cursor: grabbing; }
                .org-tree { display: flex; flex-direction: column; align-items: center; padding: 24px 32px; transform-origin: top center; min-width: min-content; }
                .org-tree ul { position: relative; display: flex; justify-content: center; padding-top: 20px; list-style: none; }
                .org-tree li { position: relative; padding: 20px 6px 0; display: flex; flex-direction: column; align-items: center; }
                .org-tree li::before, .org-tree li::after { content: ''; position: absolute; top: 0; width: 50%; height: 20px; border-top: 2px solid var(--color-border); }
                .org-tree li::before { right: 50%; border-right: 2px solid var(--color-border); }
                .org-tree li::after { left: 50%; border-left: 2px solid var(--color-border); }
                .org-tree li:first-child::before { border: 0; }
                .org-tree li:last-child::after { border: 0; }
                .org-tree li:first-child::after { border-radius: 5px 0 0 0; }
                .org-tree li:last-child::before { border-right: 2px solid var(--color-border); border-radius: 0 5px 0 0; }
                .org-tree ul ul::before { content: ''; position: absolute; top: 0; left: 50%; border-left: 2px solid var(--color-border); width: 0; height: 20px; }
                .org-node { display: inline-flex; flex-direction: column; align-items: center; padding: 8px 14px; border-radius: 8px; font-size: 11px; font-weight: 500; text-decoration: none; white-space: nowrap; border: 2px solid; min-width: 80px; text-align: center; transition: transform 0.15s, box-shadow 0.15s; }
                .org-node:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
                .org-node.has-children { cursor: pointer; }
                .org-node .count { font-size: 13px; font-weight: 700; margin-top: 2px; }
                .org-node .arrow { display: inline-block; font-size: 9px; margin-left: 4px; transition: transform 0.2s; }
                .org-node .arrow.open { transform: rotate(90deg); }
                .org-root { background: var(--color-primary); color: white; border-color: var(--color-primary); padding: 10px 20px; font-size: 13px; font-weight: 700; }
                .org-dir { background: var(--color-primary); color: white; border-color: var(--color-primary); }
                .org-div { background: var(--color-accent); color: white; border-color: var(--color-accent); }
                .org-dep { background: var(--color-bg-secondary); color: var(--color-text); border-color: var(--color-border); }
                .org-sub { background: var(--color-bg-tertiary); color: var(--color-text-secondary); border-color: var(--color-border); font-size: 10px; padding: 5px 10px; min-width: 60px; }
            </style>

            <div x-data="orgTree()" @org-toggle.window="toggleAll($event.detail.expanded)">
                <div class="org-tree-wrap" x-ref="treeWrap"
                     @wheel.prevent="onWheel($event)"
                     @mousedown="panStart($event)"
                     @mousemove="panMove($event)"
                     @mouseup="panEnd($event)"
                     @mouseleave="panEnd($event)"
                     style="max-height: 600px;">
                    <div class="org-tree" :style="'transform: scale(' + scale + '); margin-bottom: ' + ((1 - scale) * 400) + 'px;'">
                        <div class="org-node org-root mb-1 has-children" @click="toggle('root')">
                            <span>
                                {{ __('Total Employee') }}
                                <span class="arrow" :class="isOpen('root') && 'open'">▶</span>
                            </span>
                            <span class="count">{{ $totalEmployees }}</span>
                        </div>

                        <div x-show="isOpen('root')" x-transition.duration.300ms>
                        <ul>
                            @foreach($orgHierarchy as $dir)
                                <li>
                                    <div class="org-node org-dir has-children" @click="toggle('{{ $dir['key'] }}')">
                                        <span>
                                            {{ $dir['name'] }}
                                            @if(count($dir['divisis']) > 0)
                                                <span class="arrow" :class="isOpen('{{ $dir['key'] }}') && 'open'">▶</span>
                                            @endif
                                        </span>
                                        <span class="count">{{ $dir['count'] }}</span>
                                    </div>

                                    @if(count($dir['divisis']) > 0)
                                        <div x-show="isOpen('{{ $dir['key'] }}')" x-transition.duration.300ms>
                                        <ul>
                                            @foreach($dir['divisis'] as $div)
                                                <li>
                                                    <div class="org-node org-div has-children" @click="toggle('{{ $div['key'] }}')">
                                                        <span>
                                                            {{ $div['name'] }}
                                                            @if(count($div['departements']) > 0)
                                                                <span class="arrow" :class="isOpen('{{ $div['key'] }}') && 'open'">▶</span>
                                                            @endif
                                                        </span>
                                                        <span class="count">{{ $div['count'] }}</span>
                                                    </div>

                                                    @if(count($div['departements']) > 0)
                                                        <div x-show="isOpen('{{ $div['key'] }}')" x-transition.duration.300ms>
                                                        <ul>
                                                            @foreach($div['departements'] as $dep)
                                                                <li>
                                                                    <div class="org-node org-dep has-children" @click="toggle('{{ $dep['key'] }}')">
                                                                        <span>
                                                                            {{ $dep['name'] }}
                                                                            @if(count($dep['sub_departements']) > 0)
                                                                                <span class="arrow" :class="isOpen('{{ $dep['key'] }}') && 'open'">▶</span>
                                                                            @endif
                                                                        </span>
                                                                        <span class="count">{{ $dep['count'] }}</span>
                                                                    </div>

                                                                    @if(count($dep['sub_departements']) > 0)
                                                                        <div x-show="isOpen('{{ $dep['key'] }}')" x-transition.duration.300ms>
                                                                        <ul>
                                                                            @foreach($dep['sub_departements'] as $sub)
                                                                                <li>
                                                                                    <div class="org-node org-sub">
                                                                                        <span>{{ $sub['name'] }}</span>
                                                                                        <span class="count">{{ $sub['count'] }}</span>
                                                                                    </div>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                        </div>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function orgTree() {
                    return {
                        open: {},
                        allExpanded: true,
                        scale: 0.55,
                        minScale: 0.2,
                        maxScale: 2,
                        panning: false,
                        startX: 0,
                        startY: 0,
                        scrollLeft: 0,
                        scrollTop: 0,
                        toggle(key) {
                            this.open[key] = !this.open[key];
                        },
                        isOpen(key) {
                            return this.open[key] ?? true;
                        },
                        toggleAll(expanded) {
                            this.allExpanded = expanded;
                            this.open = {};
                        },
                        zoomIn() {
                            this.scale = Math.min(this.maxScale, +(this.scale + 0.1).toFixed(2));
                        },
                        zoomOut() {
                            this.scale = Math.max(this.minScale, +(this.scale - 0.1).toFixed(2));
                        },
                        resetZoom() {
                            this.scale = 0.55;
                            var wrap = this.$refs.treeWrap;
                            if (wrap) { wrap.scrollLeft = 0; wrap.scrollTop = 0; }
                        },
                        onWheel(e) {
                            if (e.deltaY < 0) { this.zoomIn(); } else { this.zoomOut(); }
                        },
                        panStart(e) {
                            this.panning = true;
                            this.startX = e.pageX - this.$refs.treeWrap.offsetLeft;
                            this.startY = e.pageY - this.$refs.treeWrap.offsetTop;
                            this.scrollLeft = this.$refs.treeWrap.scrollLeft;
                            this.scrollTop = this.$refs.treeWrap.scrollTop;
                        },
                        panMove(e) {
                            if (!this.panning) return;
                            e.preventDefault();
                            var x = e.pageX - this.$refs.treeWrap.offsetLeft;
                            var y = e.pageY - this.$refs.treeWrap.offsetTop;
                            this.$refs.treeWrap.scrollLeft = this.scrollLeft - (x - this.startX);
                            this.$refs.treeWrap.scrollTop = this.scrollTop - (y - this.startY);
                        },
                        panEnd() {
                            this.panning = false;
                        }
                    }
                }
            </script>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data organisasi') }}</p>
        @endif
    </div>
</div>

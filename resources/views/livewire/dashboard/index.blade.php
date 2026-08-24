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
                $pwTotal = collect($perawatanBySite)->sum('total');
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
                        const valueLabelPlugin = {
                            id: 'pwValueLabel',
                            afterDatasetsDraw(chart) {
                                const { ctx: c, chartArea } = chart;
                                chart.getDatasetMeta(0).data.forEach((bar, i) => {
                                    const val = chart.data.datasets[0].data[i];
                                    if (val === 0) return;
                                    c.save();
                                    c.font = '600 11px system-ui, -apple-system, sans-serif';
                                    c.fillStyle = 'rgb(168, 85, 247)';
                                    c.textAlign = 'left';
                                    c.textBaseline = 'middle';
                                    c.fillText(val, bar.x + 6, bar.y);
                                    c.restore();
                                });
                            }
                        };
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
                                layout: { padding: { right: 40 } },
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { ticks: { color: 'rgb(156,163,175)', stepSize: 1 }, grid: { color: 'rgb(229,231,235)' } },
                                    y: { ticks: { color: 'rgb(156,163,175)', font: { size: 11 } }, grid: { display: false } }
                                }
                            },
                            plugins: [valueLabelPlugin]
                        });
                    });
                },
                destroy() { if (this.chart) this.chart.destroy(); }
            }" style="height: {{ max(200, count($perawatanBySite) * 40 + 60) }}px;" wire:ignore wire:key="pw-{{ md5($pwLabels.$pwData) }}">
                <canvas x-ref="chartPerawatan"></canvas>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="text-left py-2 text-xs text-muted font-medium">{{ __('Site') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Jumlah Perawatan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($perawatanBySite as $row)
                            <tr class="transition-colors cursor-pointer" onclick="window.Livewire.navigate('{{ route('admin.perawatan.index', ['site' => $row['site']]) }}')" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor=''">
                                <td class="py-2.5 font-medium text-primary">{{ $row['site'] }}</td>
                                <td class="py-2.5 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background: rgba(168,85,247,0.15); color: #a855f7;">
                                        {{ $row['total'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold border-t-2" style="border-color: var(--color-border);">
                            <td class="py-2.5 text-primary">{{ __('Total') }}</td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background: rgba(168,85,247,0.15); color: #a855f7;">
                                    {{ $pwTotal }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
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
                $pmTotal = collect($pemeriksaanBySite)->sum('total');
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
                        const valueLabelPlugin = {
                            id: 'pmValueLabel',
                            afterDatasetsDraw(chart) {
                                const { ctx: c, chartArea } = chart;
                                chart.getDatasetMeta(0).data.forEach((bar, i) => {
                                    const val = chart.data.datasets[0].data[i];
                                    if (val === 0) return;
                                    c.save();
                                    c.font = '600 11px system-ui, -apple-system, sans-serif';
                                    c.fillStyle = 'rgb(59, 130, 246)';
                                    c.textAlign = 'left';
                                    c.textBaseline = 'middle';
                                    c.fillText(val, bar.x + 6, bar.y);
                                    c.restore();
                                });
                            }
                        };
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
                                layout: { padding: { right: 40 } },
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { ticks: { color: 'rgb(156,163,175)', stepSize: 1 }, grid: { color: 'rgb(229,231,235)' } },
                                    y: { ticks: { color: 'rgb(156,163,175)', font: { size: 11 } }, grid: { display: false } }
                                }
                            },
                            plugins: [valueLabelPlugin]
                        });
                    });
                },
                destroy() { if (this.chart) this.chart.destroy(); }
            }" style="height: {{ max(200, count($pemeriksaanBySite) * 40 + 60) }}px;" wire:ignore wire:key="pm-{{ md5($pmLabels.$pmData) }}">
                <canvas x-ref="chartPemeriksaan"></canvas>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="text-left py-2 text-xs text-muted font-medium">{{ __('Site') }}</th>
                            <th class="text-right py-2 text-xs text-muted font-medium">{{ __('Jumlah Pemeriksaan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($pemeriksaanBySite as $row)
                            <tr class="transition-colors cursor-pointer" onclick="window.Livewire.navigate('{{ route('admin.pemeriksaan.index', ['site' => $row['site']]) }}')" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor=''">
                                <td class="py-2.5 font-medium text-primary">{{ $row['site'] }}</td>
                                <td class="py-2.5 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background: rgba(59,130,246,0.15); color: #3b82f6;">
                                        {{ $row['total'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold border-t-2" style="border-color: var(--color-border);">
                            <td class="py-2.5 text-primary">{{ __('Total') }}</td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background: rgba(59,130,246,0.15); color: #3b82f6;">
                                    {{ $pmTotal }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
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
                                <td class="py-2.5 font-mono text-xs">
                                    <a href="{{ route('assets.show', $a['id']) }}" wire:navigate onclick="event.stopPropagation()"
                                        class="font-semibold hover:underline transition-opacity duration-200 hover:opacity-80"
                                        style="color: var(--color-primary);"
                                        title="{{ __('Lihat detail asset') }}">
                                        {{ $a['no_asset'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 font-medium text-primary">{{ $a['nama_perangkat'] }}</td>
                                <td class="py-2.5 text-secondary text-xs hidden md:table-cell">{{ $a['operating_unit'] }}</td>
                                <td class="py-2.5 text-secondary text-xs hidden lg:table-cell">{{ $a['site_location'] }}</td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('admin.pemeriksaan.index', ['search' => $a['no_asset']]) }}" wire:navigate onclick="event.stopPropagation()"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-500/15 text-blue-400 transition-opacity duration-200 hover:opacity-80"
                                        title="{{ __('Lihat form pemeriksaan untuk asset ini') }}">
                                        {{ $a['total'] }}
                                    </a>
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
                        const pvbValueLabelPlugin = {
                            id: 'pvbValueLabel',
                            afterDatasetsDraw(chart) {
                                const { ctx: c } = chart;
                                const isDark = document.documentElement.classList.contains('dark');
                                const totals = chart.data.datasets[0].data.map((v, i) => (v || 0) + (chart.data.datasets[1].data[i] || 0));
                                [0, 1].forEach((di) => {
                                    const meta = chart.getDatasetMeta(di);
                                    meta.data.forEach((seg, i) => {
                                        const val = chart.data.datasets[di].data[i];
                                        if (!seg || !val || val === 0 || totals[i] === 0) return;
                                        const len = Math.abs(seg.x - seg.base);
                                        if (len < 44) return;
                                        const pct = ((val / totals[i]) * 100).toFixed(1);
                                        const label = val + ' (' + pct + '%)';
                                        const cx = (seg.x + seg.base) / 2;
                                        const cy = seg.y;
                                        c.save();
                                        c.font = '700 11px system-ui, -apple-system, sans-serif';
                                        c.textAlign = 'center';
                                        c.textBaseline = 'middle';
                                        c.lineJoin = 'round';
                                        c.lineWidth = 3;
                                        c.strokeStyle = isDark ? 'rgba(17,24,39,0.85)' : 'rgba(255,255,255,0.9)';
                                        c.strokeText(label, cx, cy);
                                        c.fillStyle = isDark ? '#f9fafb' : '#111827';
                                        c.fillText(label, cx, cy);
                                        c.restore();
                                    });
                                });
                            }
                        };
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
                            },
                            plugins: [pvbValueLabelPlugin]
                        });
                        this.themeObserver = new MutationObserver(() => {
                            if (this.chart) this.chart.update('none');
                        });
                        this.themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                    });
                },
                destroy() {
                    if (this.themeObserver) this.themeObserver.disconnect();
                    if (this.chart) this.chart.destroy();
                }
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
                                $statusQs = filled($filterAssetStatus) ? ['filterStatus' => $filterAssetStatus] : [];
                            @endphp
                            <tr class="transition-colors" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor=''">
                                <td class="py-2.5 font-medium text-primary">{{ $row['operating_unit'] }}</td>
                                <td class="py-2.5 text-right text-secondary">
                                    <a href="{{ route('admin.assets.index', array_merge(['filterOperatingUnit' => $ouId], $statusQs)) }}" wire:navigate class="hover:underline font-semibold" style="color: var(--color-text-secondary);">
                                        {{ $row['total'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('admin.assets.index', array_merge(['filterOperatingUnit' => $ouId, 'filterPerawatanStatus' => 'done'], $statusQs)) }}" wire:navigate class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold transition-opacity hover:opacity-70" style="background: rgba(16,185,129,0.15); color: #10b981;">
                                        {{ $row['dilakukan'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('admin.assets.index', array_merge(['filterOperatingUnit' => $ouId, 'filterPerawatanStatus' => 'pending'], $statusQs)) }}" wire:navigate class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold transition-opacity hover:opacity-70" style="background: rgba(239,68,68,0.15); color: #ef4444;">
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
                    <tfoot>
                        @php
                            $totalAsset = collect($perawatanVsBelum)->sum('total');
                            $totalDilakukan = collect($perawatanVsBelum)->sum('dilakukan');
                            $totalBelum = collect($perawatanVsBelum)->sum('belum');
                            $avgPct = $totalAsset > 0 ? round(($totalDilakukan / $totalAsset) * 100, 1) : 0;
                        @endphp
                        <tr class="font-bold border-t-2" style="border-color: var(--color-border);">
                            <td class="py-2.5 text-primary">{{ __('Total') }}</td>
                            <td class="py-2.5 text-right font-semibold text-secondary">{{ $totalAsset }}</td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background: rgba(16,185,129,0.15); color: #10b981;">
                                    {{ $totalDilakukan }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background: rgba(239,68,68,0.15); color: #ef4444;">
                                    {{ $totalBelum }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 h-1.5 rounded-full overflow-hidden" style="background: var(--color-bg-tertiary);">
                                        <div class="h-full rounded-full" style="width: {{ $avgPct }}%; background: {{ $avgPct >= 80 ? '#10b981' : ($avgPct >= 50 ? '#eab308' : '#ef4444') }};"></div>
                                    </div>
                                    <span class="text-xs text-secondary w-10 text-right">{{ $avgPct }}%</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
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
            <h3 class="text-sm font-bold text-primary">{{ __('Employee Punya vs Tidak Punya Asset by Site (Active Only)') }}</h3>
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
            <div class="flex items-center gap-1.5">
                <button type="button" @click="$dispatch('org-set-all', false)"
                    class="text-xs px-2.5 py-1.5 rounded-lg font-medium transition-colors"
                    style="background: var(--color-bg-secondary); color: var(--color-text-secondary); border: 1px solid var(--color-border);">
                    {{ __('Sembunyikan Semua') }}
                </button>
                <button type="button" @click="$dispatch('org-set-all', true)"
                    class="text-xs px-2.5 py-1.5 rounded-lg font-medium transition-colors"
                    style="background: var(--color-bg-secondary); color: var(--color-text-secondary); border: 1px solid var(--color-border);">
                    {{ __('Tampilkan Semua') }}
                </button>
            </div>
        </div>

        @if(count($orgHierarchy) > 0)
            @php
                $orgDefaults = [];
                foreach ($orgHierarchy as $d) {
                    $orgDefaults[$d['key']] = true;
                    foreach ($d['divisis'] as $v) {
                        $orgDefaults[$v['key']] = false;
                        foreach ($v['departements'] as $e) {
                            $orgDefaults[$e['key']] = false;
                        }
                    }
                }
            @endphp


            {{-- Hierarki organisasi: kartu bertingkat (mobile & desktop) --}}
            <div x-data='orgAccordion(@json($orgDefaults))' @org-set-all.window="setAll($event.detail)">
                <p class="text-xs text-muted mb-2">{{ __('Ketuk untuk membuka / menutup cabang') }}</p>
                <div class="space-y-2">
                    @foreach($orgHierarchy as $dir)
                    <div class="rounded-xl border overflow-hidden" style="border-color: var(--color-border);">
                        <button type="button" @click="toggle('{{ $dir['key'] }}')"
                            class="w-full flex items-center gap-2 px-3 py-2.5 text-start"
                            style="background: var(--color-primary);">
                            <svg class="w-4 h-4 shrink-0 transition-transform duration-200" :class="! isOpen('{{ $dir['key'] }}') && '-rotate-90'" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            <span class="flex-1 min-w-0 text-sm font-bold truncate" style="color: white;">{{ $dir['name'] }}</span>
                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold" style="background: rgba(255,255,255,0.25); color: white;">{{ $dir['count'] }}</span>
                        </button>

                        @if(count($dir['divisis']) > 0)
                        <div x-show="isOpen('{{ $dir['key'] }}')" class="p-2 space-y-1.5" style="background: var(--color-bg-secondary);">
                            @foreach($dir['divisis'] as $div)
                            <div class="rounded-lg border overflow-hidden" style="border-color: var(--color-border);">
                                <button type="button" @click="toggle('{{ $div['key'] }}')"
                                    class="w-full flex items-center gap-2 px-2.5 py-2 text-start"
                                    style="background: var(--color-card-bg);">
                                    <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 {{ count($div['departements']) > 0 ? '' : 'invisible' }}" :class="! isOpen('{{ $div['key'] }}') && '-rotate-90'" style="color: var(--color-text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                    <span class="flex-1 min-w-0 text-xs font-semibold truncate text-primary">{{ $div['name'] }}</span>
                                    <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold" style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">{{ $div['count'] }}</span>
                                </button>

                                @if(count($div['departements']) > 0)
                                <div x-show="isOpen('{{ $div['key'] }}')" x-cloak class="ps-4 pe-1 py-1 space-y-1 border-t" style="border-color: var(--color-border); background: var(--color-bg-secondary);">
                                    @foreach($div['departements'] as $dep)
                                    <div class="rounded-lg border overflow-hidden" style="border-color: var(--color-border);">
                                        <button type="button" @click="toggle('{{ $dep['key'] }}')"
                                            class="w-full flex items-center gap-2 px-2.5 py-1.5 text-start"
                                            style="background: var(--color-card-bg);">
                                            <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 {{ count($dep['sub_departements']) > 0 ? '' : 'invisible' }}" :class="! isOpen('{{ $dep['key'] }}') && '-rotate-90'" style="color: var(--color-text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                            <span class="flex-1 min-w-0 text-xs font-medium truncate text-primary">{{ $dep['name'] }}</span>
                                            <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold" style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">{{ $dep['count'] }}</span>
                                        </button>

                                        @if(count($dep['sub_departements']) > 0)
                                        <div x-show="isOpen('{{ $dep['key'] }}')" x-cloak class="ps-4 pe-1 py-1 space-y-0.5 border-t" style="border-color: var(--color-border); background: var(--color-bg-tertiary);">
                                            @foreach($dep['sub_departements'] as $sub)
                                            <div class="flex items-center justify-between gap-2 px-2 py-1 rounded-md" style="background: var(--color-card-bg);">
                                                <span class="min-w-0 text-[11px] truncate text-secondary">{{ $sub['name'] }}</span>
                                                <span class="shrink-0 text-[10px] font-semibold text-muted">{{ $sub['count'] }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <script>
                function orgAccordion(defaults) {
                    return {
                        open: defaults,
                        toggle(key) {
                            this.open[key] = !this.open[key];
                        },
                        isOpen(key) {
                            return !!this.open[key];
                        },
                        setAll(value) {
                            Object.keys(this.open).forEach((k) => { this.open[k] = value; });
                        }
                    }
                }
            </script>
        @else
            <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data organisasi') }}</p>
        @endif
    </div>
</div>

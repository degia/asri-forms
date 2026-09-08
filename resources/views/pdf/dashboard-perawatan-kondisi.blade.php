<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, sans-serif; margin: 22px; color: #1f2937; font-size: 12px; }
        h1 { font-size: 17px; text-align: center; margin: 0 0 4px; color: #111827; }
        .subtitle { text-align: center; font-size: 11px; color: #6b7280; margin-bottom: 14px; }
        .info-box { border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 6px; padding: 9px 12px; margin-bottom: 14px; font-size: 11px; line-height: 1.6; }
        .info-box b { color: #111827; }
        .chart-wrap { text-align: center; margin-bottom: 18px; page-break-inside: avoid; }
        .chart-wrap svg { width: 100%; height: auto; max-width: 980px; }
        h2 { font-size: 14px; color: #111827; margin: 22px 0 8px; border-bottom: 2px solid #2563eb; padding-bottom: 4px; }
        table { border-collapse: collapse; width: 100%; font-size: 11px; table-layout: auto; }
        th { background: #2563eb; color: white; padding: 7px 9px; text-align: left; word-wrap: break-word; }
        th.center, td.center { text-align: center; }
        td { padding: 6px 9px; border-bottom: 1px solid #e5e7eb; word-wrap: break-word; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        tr.total-row td { background: #eff6ff; font-weight: bold; border-top: 2px solid #2563eb; }
        .chip { display: inline-block; padding: 1px 8px; border-radius: 10px; color: #fff; font-size: 10px; }
        .bg-good { background: #22c55e; }
        .bg-fair { background: #3b82f6; }
        .bg-critical { background: #f97316; }
        .bg-poor { background: #ef4444; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #9ca3af; }
        @media print {
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="subtitle">IT Department &mdash; ASRI | Exported: {{ $exportedAt->format('d/m/Y H:i') }}</div>

    <div class="info-box">
        @foreach($filters as $label => $value)
            <b>{{ $label }}:</b> {{ $value }}
            @if(! $loop->last) &nbsp;|&nbsp; @endif
        @endforeach
        &nbsp;|&nbsp; <b>Total Unit Asset Terawat:</b> {{ $totals['grand'] }}
        &nbsp;|&nbsp; <b>Jumlah Operating Unit:</b> {{ count($rows) }}
        &nbsp;|&nbsp; <b>Jumlah Form Perawatan:</b> {{ $forms->count() }}
    </div>

    @php
        $conditions = [
            'good'     => ['label' => 'GOOD',     'color' => '#22c55e'],
            'fair'     => ['label' => 'FAIR',     'color' => '#3b82f6'],
            'critical' => ['label' => 'CRITICAL', 'color' => '#f97316'],
            'poor'     => ['label' => 'POOR',     'color' => '#ef4444'],
        ];

        $yMax = 1;
        foreach ($rows as $row) {
            foreach ($conditions as $k => $c) {
                $yMax = max($yMax, $row['totals'][$k] ?? 0);
            }
        }
        $step = max(1, (int) ceil($yMax / 5));
        $scaleMax = $step * (int) ceil($yMax / $step);

        $n = count($rows);
        $plotH = 250;
        $plotW = 780;
        $yAxisW = 46;
        $groupW = $n > 0 ? (int) (($plotW - $yAxisW) / $n) : 0;
        $innerW = (int) min($groupW * 0.62, 120);
        $barW = (int) max(6, ($innerW - 8) / 4);
        $barGap = (int) max(2, round($barW * 0.35));
    @endphp

    <div class="chart-wrap">
        <h2 style="margin-top:0;">1. Grafik Bar</h2>
        @if($n > 0)
            <table style="margin:0 auto;">
                <tr>
                    <td style="text-align:center;">
                        <div style="margin-bottom:8px;">
                            @foreach($conditions as $k => $c)
                                <span style="margin-right:16px; font-size:11px; color:#374151;">
                                    <span style="display:inline-block; width:12px; height:12px; background:{{ $c['color'] }}; vertical-align:middle;"></span>
                                    {{ $c['label'] }}
                                </span>
                            @endforeach
                        </div>
                        <div style="position:relative; height:{{ $plotH }}px; width:{{ $plotW }}px; border-left:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb;">
                            @for ($t = 0; $t <= $scaleMax; $t += $step)
                                @php $tickTop = $plotH - (($t / $scaleMax) * $plotH); @endphp
                                <div style="position:absolute; left:{{ $yAxisW }}px; top:{{ $tickTop }}px; width:{{ $plotW - $yAxisW }}px; border-top:{{ $t === 0 ? '1px solid #e5e7eb' : '1px dashed #e5e7eb' }};"></div>
                                <div style="position:absolute; left:0; top:{{ $tickTop - 6 }}px; width:{{ $yAxisW - 8 }}px; text-align:right; font-size:10px; color:#6b7280;">{{ $t }}</div>
                            @endfor
                            @foreach(array_values($rows) as $g => $row)
                                @php
                                    $center = $yAxisW + $g * $groupW + $groupW / 2;
                                    $firstX = $center - (3 * ($barW + $barGap)) / 2;
                                    $siteTotal = max(1, (int) $row['total']);
                                @endphp
@foreach(array_keys($conditions) as $i => $code)
                                @php
                                    $cond = $conditions[$code];
                                    $val = (int) ($row['totals'][$code] ?? 0);
                                    $barH = $val > 0 ? (($val / $scaleMax) * $plotH) : 0;
                                    $barTop = $plotH - $barH;
                                    $bx = $firstX + $i * ($barW + $barGap);
                                    $pct = $val > 0 ? round(($val / $siteTotal) * 100) : 0;
                                @endphp
                                    @if($val > 0)
                                        <div style="position:absolute; left:{{ $bx }}px; top:{{ $barTop }}px; width:{{ $barW }}px; height:{{ $barH }}px; background:{{ $cond['color'] }};"></div>
                                        <div style="position:absolute; left:{{ $bx - 6 }}px; top:{{ max(1, $barTop - 7) }}px; width:{{ $barW + 12 }}px; text-align:center; font-size:8px; color:#374151;">{{ $val }} ({{ $pct }}%)</div>
                                    @endif
                                @endforeach
                                <div style="position:absolute; left:{{ $yAxisW + $g * $groupW }}px; top:{{ $plotH + 6 }}px; width:{{ $groupW }}px; text-align:center; font-size:10px; color:#111827;">{{ $row['ou'] }}</div>
                                <div style="position:absolute; left:{{ $yAxisW + $g * $groupW }}px; top:{{ $plotH + 20 }}px; width:{{ $groupW }}px; text-align:center; font-size:8px; color:#9ca3af;">{{ $row['ou_id'] }}</div>
                            @endforeach
                        </div>
                    </td>
                </tr>
            </table>
        @else
            <p style="text-align:center; color:#9ca3af; padding:10px;">Tidak ada data</p>
        @endif
    </div>

    <div style="page-break-before: always;"></div>
    <h2>2. Data Detail Form Perawatan (PWT)</h2>
    @php
        $kondisiColors = [
            'good' => '#10b981',
            'good_normal' => '#10b981',
            'fair' => '#3b82f6',
            'critical' => '#f59e0b',
            'poor' => '#ef4444',
            'caution_poor' => '#f59e0b',
        ];
        $kondisiLabels = [
            'good_normal' => 'Good/Normal',
            'caution_poor' => 'Caution/Poor',
        ];
        $statusColors = [
            'draft' => '#6b7280',
            'submitted' => '#3b82f6',
            'diketahui' => '#eab308',
            'disetujui' => '#22c55e',
            'selesai' => '#10b981',
            'revisi' => '#ef4444',
        ];
    @endphp
    <table>
        <thead>
            <tr>
                <th style="width:15%;">No. Form</th>
                <th style="width:13%;">Teknisi</th>
                <th style="width:13%;">Pengguna</th>
                <th style="width:18%;">Perangkat</th>
                <th style="width:12%;">Site</th>
                <th style="width:10%;" class="center">Kondisi Akhir</th>
                <th style="width:11%;" class="center">Status</th>
                <th style="width:8%;" class="center">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($forms as $form)
                <tr>
                    <td class="font-mono">{{ $form['nomor_form'] }}</td>
                    <td>{{ $form['teknisi'] }}</td>
                    <td>{{ $form['pengguna'] }}</td>
                    <td>
                        {{ $form['perangkat'] }}
                        @if($form['no_asset'] !== '')
                            <span style="color:#6b7280;">({{ $form['no_asset'] }})</span>
                        @endif
                    </td>
                    <td>{{ $form['site'] }}</td>
                    <td class="center">
                        @if($form['kondisi_akhir'])
                            <span class="chip" style="background: {{ $kondisiColors[$form['kondisi_akhir']] ?? '#6b7280' }};">
                                {{ $kondisiLabels[$form['kondisi_akhir']] ?? ucfirst($form['kondisi_akhir']) }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="center">
                        <span class="chip" style="background: {{ $statusColors[$form['status']] ?? '#6b7280' }};">
                            {{ ucfirst($form['status']) }}
                        </span>
                    </td>
                    <td class="center">{{ $form['tanggal'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:18px;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">ASRI IT Department &mdash; Exported {{ $exportedAt->format('d/m/Y H:i') }}</div>
</body>
</html>
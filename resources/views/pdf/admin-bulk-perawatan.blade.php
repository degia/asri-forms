<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Form Perawatan</title>
    <style>
        @page { margin: 10mm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.3; }
        h1 { font-size: 14px; text-align: center; margin-bottom: 4px; }
        .subtitle { font-size: 9px; text-align: center; color: #666; margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
        th, td { border: 1px solid #ccc; padding: 3px 4px; font-size: 8px; text-align: left; }
        th { background: #f0f0f0; font-weight: 600; font-size: 7px; text-transform: uppercase; }
        tr:nth-child(even) td { background: #fafafa; }
        .footer { margin-top: 8px; text-align: center; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <h1>REKAPITULASI FORM PERAWATAN PERANGKAT</h1>
    <div class="subtitle">IT Department &mdash; ASRI | Total: {{ $forms->count() }} form | Dicetak: {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Form</th>
                <th>Tanggal</th>
                <th>Teknisi</th>
                <th>Pengguna</th>
                <th>Perangkat</th>
                <th>No. Asset</th>
                <th>Site</th>
                <th>Kondisi Akhir</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($forms as $i => $form)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $form->nomor_form }}</td>
                    <td>{{ $form->submitted_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $form->teknisi->name ?? '-' }}</td>
                    <td>{{ $form->pengguna->name ?? '-' }}</td>
                    <td>{{ $form->asset->nama_perangkat ?? '-' }}</td>
                    <td>{{ $form->asset->no_asset ?? '-' }}</td>
                    <td>{{ $form->site->site ?? $form->site_location ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $form->kondisi_akhir ?? '-')) }}</td>
                    <td>{{ ucfirst($form->status) }}</td>
                    <td>{{ Str::limit($form->notes ?? '-', 30) }}</td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Rekap Form Perawatan &mdash; ASRI IT Department</div>
</body>
</html>

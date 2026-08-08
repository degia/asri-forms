<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Export Form Pemeriksaan - HTML</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; color: #333; }
        h1 { font-size: 18px; text-align: center; margin-bottom: 5px; color: #1a1a1a; }
        .subtitle { text-align: center; font-size: 12px; color: #666; margin-bottom: 15px; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th { background: #2563eb; color: white; padding: 8px 10px; text-align: left; font-size: 11px; }
        td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        tr:hover { background: #eff6ff; }
        .status { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-submitted { background: #dbeafe; color: #2563eb; }
        .status-diketahui { background: #fef3c7; color: #d97706; }
        .status-disetujui { background: #d1fae5; color: #059669; }
        .status-selesai { background: #d1fae5; color: #059669; }
        .status-revisi { background: #fee2e2; color: #dc2626; }
        .footer { text-align: center; margin-top: 15px; font-size: 11px; color: #999; }
    </style>
</head>
<body>
    <h1>Form Pemeriksaan Perangkat</h1>
    <div class="subtitle">IT Department &mdash; ASRI | Total: {{ $forms->count() }} form | Exported: {{ now()->format('d/m/Y H:i') }}</div>

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
                <th>Kondisi</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($forms as $i => $form)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-family: monospace; font-weight: 600;">{{ $form->nomor_form }}</td>
                    <td>{{ $form->submitted_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $form->teknisi->name ?? '-' }}</td>
                    <td>{{ $form->pengguna->name ?? '-' }}</td>
                    <td>{{ $form->asset->nama_perangkat ?? '-' }}</td>
                    <td style="font-family: monospace;">{{ $form->asset->no_asset ?? '-' }}</td>
                    <td>{{ $form->site->site ?? $form->site_location ?? '-' }}</td>
                    <td>{{ ucfirst($form->kondisi ?? '-') }}</td>
                    <td><span class="status status-{{ $form->status }}">{{ ucfirst($form->status) }}</span></td>
                    <td>{{ Str::limit($form->notes ?? '-', 40) }}</td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center; padding:20px;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">ASRI IT Department &mdash; Exported {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - HTML</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; color: #333; }
        h1 { font-size: 18px; text-align: center; margin-bottom: 5px; color: #1a1a1a; }
        .subtitle { text-align: center; font-size: 12px; color: #666; margin-bottom: 15px; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th { background: #2563eb; color: white; padding: 8px 10px; text-align: left; font-size: 11px; }
        td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        tr:hover { background: #eff6ff; }
        .footer { text-align: center; margin-top: 15px; font-size: 11px; color: #999; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="subtitle">IT Department &mdash; ASRI | Total: {{ count($rows) }} data | Exported: {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ is_array($cell) ? json_encode($cell) : $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) }}" style="text-align:center; padding:20px;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">ASRI IT Department &mdash; Exported {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>

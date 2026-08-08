<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
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
    <h1>{{ $title }}</h1>
    <div class="subtitle">IT Department &mdash; ASRI | Total: {{ count($rows) }} data | Dicetak: {{ now()->format('d/m/Y H:i') }}</div>

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
                <tr><td colspan="{{ count($headers) }}" style="text-align:center;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Rekap {{ $title }} &mdash; ASRI IT Department</div>
</body>
</html>

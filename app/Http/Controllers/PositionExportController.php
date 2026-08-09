<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsFormats;
use App\Models\Position;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PositionExportController extends Controller
{
    use ExportsFormats;

    public string $exportKey = 'positions';

    public string $exportTitle = 'Data Positions';

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_positions.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['name', 'code', 'sort_order']);

            fputcsv($file, [
                'Manager IT',
                'MGR-IT',
                '1',
            ]);

            fputcsv($file, [
                'Staf Teknisi',
                'STF-TEK',
                '2',
            ]);

            fputcsv($file, [
                'Analyst',
                '',
                '3',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportQuery()
    {
        return Position::orderBy('sort_order')->orderBy('name');
    }

    protected function exportHeadings(): array
    {
        return ['name', 'code', 'sort_order'];
    }

    protected function exportRow($position): array
    {
        return [
            $position->name,
            $position->code ?? '',
            (int) $position->sort_order,
        ];
    }
}

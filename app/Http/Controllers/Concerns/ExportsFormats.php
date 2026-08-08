<?php

namespace App\Http\Controllers\Concerns;

use App\Exports\ArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsFormats
{
    public function export(string $format)
    {
        $baseName = "{$this->exportKey}_export_" . now()->format('Y-m-d_His');
        $headings = $this->exportHeadings();
        $rows = $this->exportRows();

        return match ($format) {
            'pdf' => Pdf::loadView('pdf.admin-bulk-export', [
                'title' => $this->exportTitle,
                'headers' => $headings,
                'rows' => $rows,
            ])->setPaper('a4', 'landscape')->download("{$baseName}.pdf"),
            'xlsx' => Excel::download(new ArrayExport($headings, $rows), "{$baseName}.xlsx"),
            'xls' => Excel::download(new ArrayExport($headings, $rows), "{$baseName}.xls"),
            'csv' => $this->exportCsv($headings, $rows, $baseName),
            'html' => $this->exportHtml($headings, $rows, $baseName),
            default => redirect()->back()->with('error', 'Format export tidak valid.'),
        };
    }

    protected function exportRows(): array
    {
        return $this->exportQuery()
            ->get()
            ->map(fn ($item) => $this->exportRow($item))
            ->all();
    }

    private function exportCsv(array $headings, array $rows, string $baseName): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$baseName}.csv\"",
        ];

        $callback = function () use ($headings, $rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, $headings);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportHtml(array $headings, array $rows, string $baseName)
    {
        $html = view('pdf.admin-export-html', [
            'title' => $this->exportTitle,
            'headers' => $headings,
            'rows' => $rows,
        ])->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$baseName}.html\"");
    }
}

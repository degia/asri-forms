<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsFormats;
use App\Models\Departement;
use App\Models\Directorate;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\SubDepartement;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StructureOrganizationExportController extends Controller
{
    use ExportsFormats;

    protected string $type = 'directorate';

    public function export(string $type, string $format)
    {
        $this->type = $type;
        $this->exportKey = "structure-organization-{$type}";
        $this->exportTitle = "Data {$this->typeLabel($type)}";

        return $this->exportFormat($format);
    }

    public function template(string $type): StreamedResponse
    {
        $this->type = $type;

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"template_import_{$type}.csv\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, $this->exportHeadings());

            foreach ($this->templateRows() as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportQuery()
    {
        return match ($this->type) {
            'directorate' => Directorate::orderBy('name'),
            'divisi' => Divisi::with('directorate')->orderBy('name'),
            'departement' => Departement::with('divisi.directorate')->orderBy('name'),
            'sub_departement' => SubDepartement::with('departement.divisi.directorate')->orderBy('name'),
            default => Position::orderBy('sort_order')->orderBy('name'),
        };
    }

    protected function exportHeadings(): array
    {
        return match ($this->type) {
            'divisi' => ['name', 'code', 'directorate'],
            'departement' => ['name', 'code', 'divisi'],
            'sub_departement' => ['name', 'code', 'departement'],
            'position' => ['name', 'code', 'sort_order'],
            default => ['name', 'code'],
        };
    }

    protected function exportRow($record): array
    {
        return match ($this->type) {
            'divisi' => [
                $record->name,
                $record->code ?? '',
                $record->directorate?->name ?? '',
            ],
            'departement' => [
                $record->name,
                $record->code ?? '',
                $record->divisi?->name ?? '',
            ],
            'sub_departement' => [
                $record->name,
                $record->code ?? '',
                $record->departement?->name ?? '',
            ],
            'position' => [
                $record->name,
                $record->code ?? '',
                (int) $record->sort_order,
            ],
            default => [
                $record->name,
                $record->code ?? '',
            ],
        };
    }

    private function templateRows(): array
    {
        return match ($this->type) {
            'divisi' => [
                ['Divisi Teknik', 'DV-TEK', 'Directorat Operasional'],
                ['Divisi Finance', 'DV-FIN', 'Directorat Keuangan'],
            ],
            'departement' => [
                ['Departemen Maintenance', 'DP-MNT', 'Divisi Teknik'],
                ['Departemen Accounting', 'DP-ACC', 'Divisi Finance'],
            ],
            'sub_departement' => [
                ['Sub Departemen Preventive', 'SD-PRV', 'Departemen Maintenance'],
                ['Sub Departemen General Ledger', 'SD-GL', 'Departemen Accounting'],
            ],
            'position' => [
                ['Manager IT', 'MGR-IT', '1'],
                ['Staf Teknisi', 'STF-TEK', '2'],
                ['Analyst', '', '3'],
            ],
            default => [
                ['Directorat Operasional', 'DO-01'],
                ['Directorat Keuangan', 'DK-02'],
            ],
        };
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'divisi' => 'Divisi',
            'departement' => 'Departemen',
            'sub_departement' => 'Sub Departemen',
            'position' => 'Position',
            default => 'Directorat',
        };
    }
}

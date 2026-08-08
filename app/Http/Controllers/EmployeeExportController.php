<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsFormats;
use App\Models\Employee;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeExportController extends Controller
{
    use ExportsFormats;

    public string $exportKey = 'employees';

    public string $exportTitle = 'Data Employees';

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_employees.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['name', 'nik', 'site', 'directorate', 'divisi', 'departement', 'sub_departement', 'position', 'no_telepon', 'email', 'status', 'date_resign']);

            fputcsv($file, [
                'Andi Pratama',
                'NIK-0001',
                'A01',
                'Directorat Operasional',
                'Divisi Teknik',
                'Departemen Maintenance',
                'Sub Departemen Preventive',
                'Teknisi',
                '081234567890',
                'andi@asri.co.id',
                'Active',
                '',
            ]);

            fputcsv($file, [
                'Budi Santoso',
                'NIK-0002',
                'B02',
                'Directorat Keuangan',
                'Divisi Finance',
                'Departemen Accounting',
                '',
                'Staf Finance',
                '081298765432',
                '',
                'Active',
                '',
            ]);

            fputcsv($file, [
                'Citra Lestari',
                '',
                'C03',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Active',
                '',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportQuery()
    {
        return Employee::with(['directorate', 'divisi', 'departement', 'subDepartement', 'position'])
            ->orderBy('name');
    }

    protected function exportHeadings(): array
    {
        return ['name', 'nik', 'site', 'directorate', 'divisi', 'departement', 'sub_departement', 'position', 'no_telepon', 'email', 'status', 'date_resign'];
    }

    protected function exportRow($employee): array
    {
        return [
            $employee->name,
            $employee->nik ?? '',
            $employee->site ?? '',
            $employee->directorate?->name ?? '',
            $employee->divisi?->name ?? '',
            $employee->departement?->name ?? '',
            $employee->subDepartement?->name ?? '',
            $employee->position?->name ?? '',
            $employee->no_telepon ?? '',
            $employee->email ?? '',
            $employee->status ?? Employee::STATUS_ACTIVE,
            $employee->date_resign ?? '',
        ];
    }
}

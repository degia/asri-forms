<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsFormats;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportController extends Controller
{
    use ExportsFormats;

    public string $exportKey = 'users';

    public string $exportTitle = 'Data Users';

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_users.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['name', 'email', 'password', 'nik', 'role', 'status']);

            fputcsv($file, [
                'John Doe',
                'john@asri.co.id',
                'password',
                'USR001',
                'pengguna',
                'Enable',
            ]);

            fputcsv($file, [
                'Jane Smith',
                'jane@asri.co.id',
                'password123',
                'USR002',
                'teknisi',
                'Enable',
            ]);

            fputcsv($file, [
                'Budi Santoso',
                'budi@asri.co.id',
                'passbudi',
                'USR003',
                'admin',
                'Disable',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportQuery()
    {
        return User::with('roles')->orderBy('name');
    }

    protected function exportHeadings(): array
    {
        return ['name', 'email', 'password', 'nik', 'role', 'status'];
    }

    protected function exportRow($user): array
    {
        return [
            $user->name,
            $user->email,
            '',
            $user->nik ?? '',
            $user->getRoleNames()->first() ?? '',
            $user->status ?? 'Enable',
        ];
    }
}

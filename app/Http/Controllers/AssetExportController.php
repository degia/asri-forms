<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsFormats;
use App\Models\Asset;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetExportController extends Controller
{
    use ExportsFormats;

    public string $exportKey = 'assets';

    public string $exportTitle = 'Data Assets';

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_assets.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['no_asset', 'kategori', 'brand', 'tipe', 'nama_perangkat', 'no_serial', 'spesifikasi', 'operating_unit', 'site_location_asset', 'assigned_employee_email']);

            fputcsv($file, [
                'ASR-LPT-2024-001',
                'Laptop',
                'Lenovo',
                'ThinkPad T480',
                'Laptop Kantor Finance',
                'SN-LNV-001',
                'Processor Intel Core i5-8250U, RAM 8GB, SSD 256GB, Layar 14" FHD',
                'A01',
                'A01',
                'employee-email@asri.co.id',
            ]);

            fputcsv($file, [
                'ASR-PRN-2024-002',
                'Printer',
                'Epson',
                'L3210',
                'Printer Multifungsi Finance',
                'SN-EPS-002',
                'Resolusi 5760 dpi, Cetak hitam putih dan warna, Wi-Fi Direct',
                'A01',
                'A01',
                'employee-email@asri.co.id',
            ]);

            fputcsv($file, [
                'ASR-ACS-2024-003',
                'Access Point',
                'MikroTik',
                'hAP AC2',
                'Access Point Lantai 2',
                'SN-MIK-003',
                'Dual-band 2.4/5GHz, Port 5x Gigabit Ethernet',
                'B02',
                'B02',
                'employee-email@asri.co.id',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportQuery()
    {
        return Asset::with('assignedEmployee')->orderBy('no_asset');
    }

    protected function exportHeadings(): array
    {
        return ['no_asset', 'kategori', 'brand', 'tipe', 'nama_perangkat', 'no_serial', 'spesifikasi', 'operating_unit', 'site_location_asset', 'assigned_employee_email'];
    }

    protected function exportRow($asset): array
    {
        return [
            $asset->no_asset,
            $asset->kategori,
            $asset->brand,
            $asset->tipe,
            $asset->nama_perangkat,
            $asset->no_serial ?? '',
            $asset->spesifikasi ?? '',
            $asset->operating_unit ?? '',
            $asset->site_location_asset ?? '',
            $asset->assignedEmployee?->email ?? '',
        ];
    }
}

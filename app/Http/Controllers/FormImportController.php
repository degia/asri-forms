<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

class FormImportController extends Controller
{
    public function templatePemeriksaan(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_pemeriksaan.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header: form fields + first item fields
            fputcsv($file, [
                'nomor_form',
                'submitted_at',
                'pengguna_nik',
                'asset_no_asset',
                'site_location',
                'location_detail',
                'kondisi',
                'kondisi_keterangan',
                'notes',
                'tindakan_solution',
                'status',
                'item_category',
                'item_name',
                'item_status',
                'item_value',
                'item_keterangan',
                'item_full_charge_capacity',
                'item_design_capacity',
                'item_sort_order',
            ]);

            // Example form 1 — 2 rows (header+item1, item2)
            fputcsv($file, [
                'PMR-2026-001',
                '2026/01/15 09:00',
                'NIK-0001',
                'ASR-LPT-2024-001',
                'A01',
                'Ruang Server Lantai 2',
                'baik',
                '',
                'Perangkat dalam kondisi normal',
                '',
                'draft',
                'Komputer',
                'Laptop',
                'Normal',
                '',
                '',
                '',
                '',
                '1',
            ]);

            fputcsv($file, [
                'PMR-2026-001',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Komputer',
                'Laptop',
                'Normal',
                '',
                '',
                '',
                '',
                '2',
            ]);

            // Example form 2 — 1 row (header+item1 only)
            fputcsv($file, [
                'PMR-2026-002',
                '2026/01/15 10:30',
                'NIK-0002',
                'ASR-PRN-2024-002',
                'A01',
                'Ruang Finance',
                'perlu_perawatan',
                'Tinta habis',
                'Printer perlu isi ulang tinta',
                'Isi ulang tinta',
                'draft',
                'Printer',
                'Printer Inkjet',
                'Perlu Perawatan',
                '',
                'Tinta kosong',
                '',
                '',
                '',
                '1',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function templatePerawatan(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_perawatan.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header: form fields + first item fields
            fputcsv($file, [
                'nomor_form',
                'submitted_at',
                'pengguna_nik',
                'asset_no_asset',
                'site_location',
                'location_detail',
                'kondisi_akhir',
                'kondisi_akhir_notes',
                'barcode_fisik',
                'notes',
                'status',
                'item_category',
                'item_name',
                'item_status',
                'item_keterangan',
                'item_full_charge_capacity',
                'item_design_capacity',
                'item_sort_order',
            ]);

            // Example form 1 — 2 rows
            fputcsv($file, [
                'PWT-2026-001',
                '2026/01/15 09:00',
                'NIK-0001',
                'ASR-LPT-2024-001',
                'A01',
                'Ruang Server Lantai 2',
                'baik',
                'Perangkat dalam kondisi baik setelah perawatan',
                '',
                'Perawatan rutin selesai',
                'draft',
                'Komputer',
                'Laptop',
                'Normal',
                '',
                '',
                '',
                '1',
            ]);

            fputcsv($file, [
                'PWT-2026-001',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Komputer',
                'Laptop',
                'Normal',
                '',
                '',
                '',
                '2',
            ]);

            // Example form 2 — 1 row
            fputcsv($file, [
                'PWT-2026-002',
                '2026/01/15 10:30',
                'NIK-0002',
                'ASR-PRN-2024-002',
                'A01',
                'Rangku Finance',
                'perlu_perawatan',
                'Tinta perlu diganti',
                '',
                'Isi ulang tinta selesai',
                'draft',
                'Printer',
                'Printer Inkjet',
                'Perlu Perawatan',
                'Tinta kosong',
                '',
                '',
                '',
                '1',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::pluck('nik', 'email')->toArray();
        $getEmployeeId = fn(string $email) => $employees[$email] ?? null;

        $assets = [
            // Laptops
            ['kategori' => 'Laptop', 'brand' => 'Lenovo', 'tipe' => 'ThinkPad T480', 'nama_perangkat' => 'Laptop Kantor Finance', 'no_serial' => 'SN-LNV-001', 'no_asset' => 'ASR-LPT-2024-001', 'assigned_user_email' => 'user@asri.co.id', 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Laptop', 'brand' => 'Lenovo', 'tipe' => 'ThinkPad T14s', 'nama_perangkat' => 'Laptop Kantor HRD', 'no_serial' => 'SN-LNV-002', 'no_asset' => 'ASR-LPT-2024-002', 'assigned_user_email' => 'user3@asri.co.id', 'operating_unit' => 'A02', 'site_location_asset' => 'A02'],
            ['kategori' => 'Laptop', 'brand' => 'HP', 'tipe' => 'ProBook 450 G9', 'nama_perangkat' => 'Laptop Marketing', 'no_serial' => 'SN-HP-003', 'no_asset' => 'ASR-LPT-2024-003', 'assigned_user_email' => 'user2@asri.co.id', 'operating_unit' => 'F01', 'site_location_asset' => 'F01'],
            ['kategori' => 'Laptop', 'brand' => 'Dell', 'tipe' => 'Latitude 5530', 'nama_perangkat' => 'Laptop Purchasing', 'no_serial' => 'SN-DEL-004', 'no_asset' => 'ASR-LPT-2024-004', 'assigned_user_email' => null, 'operating_unit' => 'A03', 'site_location_asset' => 'A03'],
            ['kategori' => 'Laptop', 'brand' => 'ASUS', 'tipe' => 'ExpertBook B1', 'nama_perangkat' => 'Laptop Warehouse', 'no_serial' => 'SN-ASU-005', 'no_asset' => 'ASR-LPT-2024-005', 'assigned_user_email' => null, 'operating_unit' => 'F02', 'site_location_asset' => 'F02'],
            ['kategori' => 'Laptop', 'brand' => 'HP', 'tipe' => 'EliteBook 840 G9', 'nama_perangkat' => 'Laptop Manager IT', 'no_serial' => 'SN-HP-006', 'no_asset' => 'ASR-LPT-2024-006', 'assigned_user_email' => null, 'operating_unit' => 'M01', 'site_location_asset' => 'M01'],
            ['kategori' => 'Laptop', 'brand' => 'Apple', 'tipe' => 'MacBook Air M2', 'nama_perangkat' => 'Macbook Direksi', 'no_serial' => 'SN-APL-007', 'no_asset' => 'ASR-LPT-2024-007', 'assigned_user_email' => null, 'operating_unit' => 'OD8', 'site_location_asset' => 'OD8'],
            ['kategori' => 'Laptop', 'brand' => 'Dell', 'tipe' => 'Latitude 7430', 'nama_perangkat' => 'Laptop Supervisor IT', 'no_serial' => 'SN-DEL-008', 'no_asset' => 'ASR-LPT-2024-008', 'assigned_user_email' => null, 'operating_unit' => 'M02', 'site_location_asset' => 'M02'],

            // Desktops
            ['kategori' => 'Desktop', 'brand' => 'Lenovo', 'tipe' => 'ThinkCentre M70q', 'nama_perangkat' => 'PC Admin', 'no_serial' => 'SN-LNV-010', 'no_asset' => 'ASR-DTK-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Desktop', 'brand' => 'HP', 'tipe' => 'ProDesk 400 G9', 'nama_perangkat' => 'PC Finance', 'no_serial' => 'SN-HP-011', 'no_asset' => 'ASR-DTK-2024-002', 'assigned_user_email' => 'user@asri.co.id', 'operating_unit' => 'A02', 'site_location_asset' => 'A02'],
            ['kategori' => 'Desktop', 'brand' => 'Dell', 'tipe' => 'OptiPlex 7010', 'nama_perangkat' => 'PC GA', 'no_serial' => 'SN-DEL-012', 'no_asset' => 'ASR-DTK-2024-003', 'assigned_user_email' => null, 'operating_unit' => 'M02', 'site_location_asset' => 'M02'],
            ['kategori' => 'Desktop', 'brand' => 'Acer', 'tipe' => 'Veriton N268', 'nama_perangkat' => 'PC Resepsionis', 'no_serial' => 'SN-ACR-013', 'no_asset' => 'ASR-DTK-2024-004', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],

            // Monitors
            ['kategori' => 'Monitor', 'brand' => 'LG', 'tipe' => '24MP400', 'nama_perangkat' => 'Monitor Kantor 24"', 'no_serial' => 'SN-LG-020', 'no_asset' => 'ASR-MON-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Monitor', 'brand' => 'Dell', 'tipe' => 'P2422H', 'nama_perangkat' => 'Monitor Meeting Room', 'no_serial' => 'SN-DEL-021', 'no_asset' => 'ASR-MON-2024-002', 'assigned_user_email' => null, 'operating_unit' => 'F01', 'site_location_asset' => 'F01'],
            ['kategori' => 'Monitor', 'brand' => 'Samsung', 'tipe' => 'F24T700', 'nama_perangkat' => 'Monitor Client 1', 'no_serial' => 'SN-SAM-022', 'no_asset' => 'ASR-MON-2024-003', 'assigned_user_email' => null, 'operating_unit' => 'M01', 'site_location_asset' => 'M01'],
            ['kategori' => 'Monitor', 'brand' => 'Samsung', 'tipe' => 'S27A600', 'nama_perangkat' => 'Monitor Marketing', 'no_serial' => 'SN-SAM-023', 'no_asset' => 'ASR-MON-2024-004', 'assigned_user_email' => null, 'operating_unit' => 'OD8', 'site_location_asset' => 'OD8'],

            // Printers
            ['kategori' => 'Printer', 'brand' => 'HP', 'tipe' => 'LaserJet Pro M404dn', 'nama_perangkat' => 'Printer Kantor Pusat', 'no_serial' => 'SN-HP-030', 'no_asset' => 'ASR-PRT-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Printer', 'brand' => 'Canon', 'tipe' => 'imageRUNNER C3326i', 'nama_perangkat' => 'Printer Color GA', 'no_serial' => 'SN-CAN-031', 'no_asset' => 'ASR-PRT-2024-002', 'assigned_user_email' => null, 'operating_unit' => 'A02', 'site_location_asset' => 'A02'],
            ['kategori' => 'Printer', 'brand' => 'Epson', 'tipe' => 'L3110', 'nama_perangkat' => 'Printer Kasir', 'no_serial' => 'SN-EPS-032', 'no_asset' => 'ASR-PRT-2024-003', 'assigned_user_email' => null, 'operating_unit' => 'F01', 'site_location_asset' => 'F01'],

            // Networking
            ['kategori' => 'Networking', 'brand' => 'Cisco', 'tipe' => 'Catalyst 2960', 'nama_perangkat' => 'Switch Lantai 1', 'no_serial' => 'SN-CSO-040', 'no_asset' => 'ASR-NET-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Networking', 'brand' => 'MikroTik', 'tipe' => 'RB4011iGS+', 'nama_perangkat' => 'Router Utama', 'no_serial' => 'SN-MKT-041', 'no_asset' => 'ASR-NET-2024-002', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Networking', 'brand' => 'Ubiquiti', 'tipe' => 'UniFi AP AC Pro', 'nama_perangkat' => 'Access Point Lobby', 'no_serial' => 'SN-UBI-042', 'no_asset' => 'ASR-NET-2024-003', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Networking', 'brand' => 'TP-Link', 'tipe' => 'TL-SG1024D', 'nama_perangkat' => 'Switch Lantai 2', 'no_serial' => 'SN-TPL-043', 'no_asset' => 'ASR-NET-2024-004', 'assigned_user_email' => null, 'operating_unit' => 'A02', 'site_location_asset' => 'A02'],

            // Server
            ['kategori' => 'Server', 'brand' => 'Dell', 'tipe' => 'PowerEdge T350', 'nama_perangkat' => 'Server Internal', 'no_serial' => 'SN-DEL-050', 'no_asset' => 'ASR-SRV-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'Server', 'brand' => 'HPE', 'tipe' => 'ProLiant ML30 Gen10+', 'nama_perangkat' => 'Server Backup', 'no_serial' => 'SN-HPE-051', 'no_asset' => 'ASR-SRV-2024-002', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],

            // UPS
            ['kategori' => 'UPS', 'brand' => 'APC', 'tipe' => 'Smart-UPS SMT1500', 'nama_perangkat' => 'UPS Server Room', 'no_serial' => 'SN-APC-060', 'no_asset' => 'ASR-UPS-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'A01', 'site_location_asset' => 'A01'],
            ['kategori' => 'UPS', 'brand' => 'APC', 'tipe' => 'Back-UPS BX1100', 'nama_perangkat' => 'UPS Kantor', 'no_serial' => 'SN-APC-061', 'no_asset' => 'ASR-UPS-2024-002', 'assigned_user_email' => null, 'operating_unit' => 'A02', 'site_location_asset' => 'A02'],

            // Projector
            ['kategori' => 'Projector', 'brand' => 'Epson', 'tipe' => 'EB-X51', 'nama_perangkat' => 'Projector Meeting Room', 'no_serial' => 'SN-EPS-070', 'no_asset' => 'ASR-PRJ-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'F01', 'site_location_asset' => 'F01'],

            // Tablet
            ['kategori' => 'Tablet', 'brand' => 'Samsung', 'tipe' => 'Galaxy Tab S9', 'nama_perangkat' => 'Tablet Presentasi', 'no_serial' => 'SN-SAM-080', 'no_asset' => 'ASR-TAB-2024-001', 'assigned_user_email' => null, 'operating_unit' => 'M01', 'site_location_asset' => 'M01'],
        ];

        foreach ($assets as $data) {
            $assignedEmployeeId = $data['assigned_user_email'] ? $getEmployeeId($data['assigned_user_email']) : null;

            Asset::create([
                'kategori' => $data['kategori'],
                'brand' => $data['brand'],
                'tipe' => $data['tipe'],
                'nama_perangkat' => $data['nama_perangkat'],
                'no_serial' => $data['no_serial'],
                'no_asset' => $data['no_asset'],
                'qr_code' => $data['no_asset'],
                'status' => $assignedEmployeeId ? 'active' : 'inactive',
                'operating_unit' => $data['operating_unit'],
                'site_location_asset' => $data['site_location_asset'],
                'assigned_employee_id' => $assignedEmployeeId,
            ]);
        }
    }
}

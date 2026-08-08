<?php

namespace Database\Seeders;

use App\Models\ChecklistTemplate;
use Illuminate\Database\Seeder;

class ChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ===== FORM PEMERIKSAAN =====
            [
                'name' => 'Pemeriksaan Hardware',
                'form_type' => 'pemeriksaan',
                'category' => 'hardware',
                'items' => [
                    ['name' => 'Processor', 'sort_order' => 1],
                    ['name' => 'Mainboard', 'sort_order' => 2],
                    ['name' => 'Monitor / LCD', 'sort_order' => 3],
                    ['name' => 'Casing', 'sort_order' => 4],
                    ['name' => 'Camera', 'sort_order' => 5],
                    ['name' => 'Port USB / Charger', 'sort_order' => 6],
                    ['name' => 'Connectivity (WiFi / Bluetooth / LAN)', 'sort_order' => 7],
                    ['name' => 'Adaptor / PSU', 'sort_order' => 8],
                    ['name' => 'Trackpad / Keyboard / Mouse', 'sort_order' => 9],
                    ['name' => 'Battery', 'sort_order' => 10],
                    ['name' => 'Audio', 'sort_order' => 11],
                    ['name' => 'Memory (RAM)', 'sort_order' => 12],
                    ['name' => 'Disk / Storage', 'sort_order' => 13],
                    ['name' => 'Graphic Card', 'sort_order' => 14],
                ],
            ],
            [
                'name' => 'Pemeriksaan Aplikasi',
                'form_type' => 'pemeriksaan',
                'category' => 'aplikasi',
                'items' => [
                    ['name' => 'Antivirus', 'sort_order' => 1],
                    ['name' => 'Manage Engine', 'sort_order' => 2],
                    ['name' => 'Office 365', 'sort_order' => 3],
                    ['name' => 'OneDrive', 'sort_order' => 4],
                    ['name' => 'Microsoft Teams', 'sort_order' => 5],
                    ['name' => 'Adobe Reader', 'sort_order' => 6],
                    ['name' => 'Browser', 'sort_order' => 7],
                    ['name' => 'Anydesk', 'sort_order' => 8],
                ],
            ],
            [
                'name' => 'Pemeriksaan Operating System',
                'form_type' => 'pemeriksaan',
                'category' => 'operating_system',
                'items' => [
                    ['name' => 'Nama OS', 'sort_order' => 1],
                    ['name' => 'Nama Perangkat (Hostname)', 'sort_order' => 2],
                    ['name' => 'User Account Profile', 'sort_order' => 3],
                    ['name' => 'Disk Capacity Usage', 'sort_order' => 4],
                    ['name' => 'Kinerja Sistem', 'sort_order' => 5],
                ],
            ],
            // ===== FORM PERAWATAN =====
            [
                'name' => 'Perawatan Hardware',
                'form_type' => 'perawatan',
                'category' => 'hardware',
                'items' => [
                    ['name' => 'Temperature Check', 'sort_order' => 1],
                    ['name' => 'Physical Cleaning', 'sort_order' => 2],
                    ['name' => 'Battery Report', 'sort_order' => 3],
                    ['name' => 'Memory Test', 'sort_order' => 4],
                    ['name' => 'Hardisk Test', 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Perawatan Aplikasi',
                'form_type' => 'perawatan',
                'category' => 'aplikasi',
                'items' => [
                    ['name' => 'Antivirus (Kaspersky)', 'sort_order' => 1],
                    ['name' => 'Manage Device (Endpoint Central)', 'sort_order' => 2],
                    ['name' => 'Office 365', 'sort_order' => 3],
                    ['name' => 'Remote (Anydesk)', 'sort_order' => 4],
                    ['name' => 'Browser (Edge/Chrome)', 'sort_order' => 5],
                    ['name' => 'PDF (Adobe Acrobat/PDF SAM/PDF Gear)', 'sort_order' => 6],
                    ['name' => 'Onedrive', 'sort_order' => 7],
                    ['name' => 'Teams', 'sort_order' => 8],
                    ['name' => 'File Extraction (7-Zip)', 'sort_order' => 9],
                ],
            ],
            [
                'name' => 'Perawatan Operating System',
                'form_type' => 'perawatan',
                'category' => 'operating_system',
                'items' => [
                    ['name' => 'Clear Cache', 'sort_order' => 1],
                    ['name' => 'Defragment', 'sort_order' => 2],
                    ['name' => 'Optimized RAM', 'sort_order' => 3],
                    ['name' => 'Check Restore Point', 'sort_order' => 4],
                    ['name' => 'Performance Check', 'sort_order' => 5],
                ],
            ],
        ];

        foreach ($templates as $data) {
            $template = ChecklistTemplate::firstOrCreate(
                ['name' => $data['name'], 'form_type' => $data['form_type'], 'category' => $data['category']],
                ['is_active' => true]
            );

            $template->items()->delete();
            $template->items()->createMany($data['items']);
        }
    }
}

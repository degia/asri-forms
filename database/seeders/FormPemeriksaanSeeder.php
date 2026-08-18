<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\FormPemeriksaan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FormPemeriksaanSeeder extends Seeder
{
    public function run(): void
    {
        $hardwareItems = ChecklistTemplate::where('form_type', 'pemeriksaan')->where('category', 'hardware')->first()?->items ?? collect();
        $aplikasiItems = ChecklistTemplate::where('form_type', 'pemeriksaan')->where('category', 'aplikasi')->first()?->items ?? collect();
        $osItems = ChecklistTemplate::where('form_type', 'pemeriksaan')->where('category', 'operating_system')->first()?->items ?? collect();

        if ($hardwareItems->isEmpty() || $osItems->isEmpty()) {
            return;
        }

        $teknisi1 = User::where('email', 'technician1@asri.co.id')->first();
        $teknisi2 = User::where('email', 'technician2@asri.co.id')->first();
        $assetByNo = fn (string $no) => Asset::where('no_asset', $no)->first();

        $now = Carbon::now();

        $forms = [
            [
                'nomor_form' => 'PBR-0001',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'USR001',
                'asset' => $assetByNo('ASR-LPT-2024-001'),
                'kondisi' => 'baru',
                'kondisi_keterangan' => null,
                'notes' => 'Pemeriksaan rutin Laptop Kantor Finance (ASR-LPT-2024-001)',
                'status' => 'draft',
                'submitted_at' => null,
                'tindakan_categories' => null,
                'tindakan_solution' => null,
                'hardware' => [],
                'aplikasi' => [],
                'os' => [
                    'Nama OS' => 'Windows 11 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-FN-001',
                    'User Account Profile' => 'user1@asri.co.id',
                    'Disk Capacity Usage' => '62% used of 512GB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
            [
                'nomor_form' => 'PBR-0002',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'USR003',
                'asset' => $assetByNo('ASR-LPT-2024-002'),
                'kondisi' => 'lama',
                'kondisi_keterangan' => null,
                'notes' => 'Pemeriksaan rutin Laptop Kantor HRD (ASR-LPT-2024-002)',
                'status' => 'draft',
                'submitted_at' => null,
                'tindakan_categories' => null,
                'tindakan_solution' => null,
                'hardware' => [],
                'aplikasi' => [],
                'os' => [
                    'Nama OS' => 'Windows 10 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-HRD-001',
                    'User Account Profile' => 'user3@asri.co.id',
                    'Disk Capacity Usage' => '78% used of 256GB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
            [
                'nomor_form' => 'PBR-0003',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'USR002',
                'asset' => $assetByNo('ASR-LPT-2024-003'),
                'kondisi' => 'baru',
                'kondisi_keterangan' => null,
                'notes' => 'Pemeriksaan rutin Laptop Marketing (ASR-LPT-2024-003)',
                'status' => 'submitted',
                'submitted_at' => $now->copy()->subDays(5)->subHours(3),
                'tindakan_categories' => null,
                'tindakan_solution' => null,
                'hardware' => [],
                'aplikasi' => [],
                'os' => [
                    'Nama OS' => 'Windows 11 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-MKT-001',
                    'User Account Profile' => 'user2@asri.co.id',
                    'Disk Capacity Usage' => '41% used of 512GB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
            [
                'nomor_form' => 'PBR-0004',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'USR001',
                'asset' => $assetByNo('ASR-DTK-2024-002'),
                'kondisi' => 'lama',
                'kondisi_keterangan' => 'PC digunakan sejak 2023, sebagian komponen mulai aus.',
                'notes' => 'Pemeriksaan rutin PC Finance (ASR-DTK-2024-002)',
                'status' => 'submitted',
                'submitted_at' => $now->copy()->subDays(3)->subHours(2),
                'tindakan_categories' => null,
                'tindakan_solution' => null,
                'hardware' => [
                    'Mainboard' => ['status' => 'tidak_baik', 'keterangan' => 'Slot RAM pada mainboard bermasalah.'],
                    'Battery' => ['status' => 'tidak_baik', 'keterangan' => 'Battery CMOS perlu diganti.', 'full_charge_capacity' => 19800, 'design_capacity' => 30000],
                ],
                'aplikasi' => [],
                'os' => [
                    'Nama OS' => 'Windows 11 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-FN-002',
                    'User Account Profile' => 'user1@asri.co.id',
                    'Disk Capacity Usage' => '85% used of 1TB',
                    'Kinerja Sistem' => ['status' => 'tidak_baik', 'keterangan' => 'Sistem terasa lambat saat startup.'],
                ],
            ],
            [
                'nomor_form' => 'PBR-0005',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'USR001',
                'asset' => $assetByNo('ASR-LPT-2024-001'),
                'kondisi' => 'baru',
                'kondisi_keterangan' => null,
                'notes' => 'Pemeriksaan berkala Laptop Kantor Finance (ASR-LPT-2024-001)',
                'status' => 'diketahui',
                'submitted_at' => $now->copy()->subDays(10)->subHours(1),
                'tindakan_categories' => null,
                'tindakan_solution' => null,
                'hardware' => [
                    'Camera' => ['status' => 'tidak_baik', 'keterangan' => 'Kamera tidak terdeteksi oleh sistem.'],
                ],
                'aplikasi' => [],
                'os' => [
                    'Nama OS' => 'Windows 11 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-FN-001',
                    'User Account Profile' => 'user1@asri.co.id',
                    'Disk Capacity Usage' => '55% used of 512GB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
            [
                'nomor_form' => 'PBR-0006',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'USR003',
                'asset' => $assetByNo('ASR-LPT-2024-002'),
                'kondisi' => 'lama',
                'kondisi_keterangan' => null,
                'notes' => 'Pemeriksaan berkala Laptop Kantor HRD (ASR-LPT-2024-002)',
                'status' => 'disetujui',
                'submitted_at' => $now->copy()->subDays(20)->subHours(5),
                'tindakan_categories' => ['aplikasi'],
                'tindakan_solution' => 'Update aplikasi ke versi terbaru.',
                'hardware' => [],
                'aplikasi' => [
                    'Antivirus' => ['status' => 'tidak_baik', 'keterangan' => 'Lisensi antivirus sudah kedaluwarsa.'],
                ],
                'os' => [
                    'Nama OS' => 'Windows 10 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-HRD-001',
                    'User Account Profile' => 'user3@asri.co.id',
                    'Disk Capacity Usage' => '72% used of 256GB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
            [
                'nomor_form' => 'PBR-0007',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'USR002',
                'asset' => $assetByNo('ASR-LPT-2024-003'),
                'kondisi' => 'baru',
                'kondisi_keterangan' => null,
                'notes' => 'Pemeriksaan berkala Laptop Marketing (ASR-LPT-2024-003)',
                'status' => 'selesai',
                'submitted_at' => $now->copy()->subDays(30)->subHours(4),
                'tindakan_categories' => ['hardware', 'aplikasi'],
                'tindakan_solution' => 'Perlu penggantian komponen hardware dan update aplikasi.',
                'hardware' => [],
                'aplikasi' => [],
                'os' => [
                    'Nama OS' => 'Windows 11 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-MKT-001',
                    'User Account Profile' => 'user2@asri.co.id',
                    'Disk Capacity Usage' => '38% used of 512GB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
            [
                'nomor_form' => 'PBR-0008',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'USR001',
                'asset' => $assetByNo('ASR-DTK-2024-002'),
                'kondisi' => 'lama',
                'kondisi_keterangan' => 'Kondisi fisik baik, namun perlu pembersihan debu.',
                'notes' => 'Pemeriksaan berkala PC Finance (ASR-DTK-2024-002)',
                'status' => 'selesai',
                'submitted_at' => $now->copy()->subDays(25)->subHours(2),
                'tindakan_categories' => ['operating_system'],
                'tindakan_solution' => 'Optimasi sistem dan pembersihan disk.',
                'hardware' => [],
                'aplikasi' => [],
                'os' => [
                    'Nama OS' => 'Windows 11 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-FN-002',
                    'User Account Profile' => 'user1@asri.co.id',
                    'Disk Capacity Usage' => '51% used of 1TB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
            [
                'nomor_form' => 'PBR-0009',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'USR001',
                'asset' => $assetByNo('ASR-LPT-2024-001'),
                'kondisi' => 'lama',
                'kondisi_keterangan' => null,
                'notes' => 'Pemeriksaan berkala Laptop Kantor Finance (ASR-LPT-2024-001)',
                'status' => 'revisi',
                'submitted_at' => $now->copy()->subDays(2)->subHours(6),
                'tindakan_categories' => null,
                'tindakan_solution' => null,
                'hardware' => [
                    'Monitor / LCD' => ['status' => 'tidak_baik', 'keterangan' => 'Terdapat pixel mati pada layar.'],
                    'Port USB / Charger' => ['status' => 'tidak_baik', 'keterangan' => 'Port charger terasa longgar.'],
                ],
                'aplikasi' => [
                    'Antivirus' => ['status' => 'tidak_baik', 'keterangan' => 'Antivirus belum diperbarui.'],
                ],
                'os' => [
                    'Nama OS' => 'Windows 11 Pro',
                    'Nama Perangkat (Hostname)' => 'PC-FN-001',
                    'User Account Profile' => 'user1@asri.co.id',
                    'Disk Capacity Usage' => '66% used of 512GB',
                    'Kinerja Sistem' => 'baik',
                ],
            ],
        ];

        foreach ($forms as $data) {
            if (! $data['teknisi'] || ! $data['asset']) {
                continue;
            }

            $form = FormPemeriksaan::firstOrCreate(
                ['nomor_form' => $data['nomor_form']],
                [
                    'user_id' => $data['teknisi']->email,
                    'pengguna_employee_id' => $data['pengguna_employee_id'],
                    'asset_id' => $data['asset']->id,
                    'site_location' => $data['asset']->operating_unit,
                    'location_detail' => 'Lantai 2 Ruang B-08',
                    'kondisi' => $data['kondisi'],
                    'kondisi_keterangan' => $data['kondisi_keterangan'],
                    'notes' => $data['notes'],
                    'tindakan_categories' => $data['tindakan_categories'],
                    'tindakan_solution' => $data['tindakan_solution'],
                    'status' => $data['status'],
                    'submitted_at' => $data['submitted_at'],
                ]
            );

            if (! $form->wasRecentlyCreated) {
                continue;
            }

            $this->createHardwareItems($form, $hardwareItems, $data['hardware']);
            $this->createAplikasiItems($form, $aplikasiItems, $data['aplikasi']);
            $this->createOsItems($form, $osItems, $data['os']);
        }
    }

    private function createHardwareItems($form, $items, array $overrides = []): void
    {
        foreach ($items as $idx => $item) {
            $override = $overrides[$item->name] ?? ['status' => 'baik', 'keterangan' => null];

            $data = [
                'template_item_id' => $item->id,
                'category' => 'hardware',
                'name' => $item->name,
                'status' => $override['status'],
                'keterangan' => $override['keterangan'],
                'sort_order' => $idx,
            ];

            if (str_contains(strtolower($item->name), 'battery')) {
                $data['full_charge_capacity'] = $override['full_charge_capacity'] ?? 45238;
                $data['design_capacity'] = $override['design_capacity'] ?? 50124;
            }

            $form->items()->create($data);
        }
    }

    private function createAplikasiItems($form, $items, array $overrides = []): void
    {
        foreach ($items as $idx => $item) {
            $override = $overrides[$item->name] ?? ['status' => 'baik', 'keterangan' => null];

            $form->items()->create([
                'template_item_id' => $item->id,
                'category' => 'aplikasi',
                'name' => $item->name,
                'status' => $override['status'],
                'keterangan' => $override['keterangan'],
                'sort_order' => $idx,
            ]);
        }
    }

    private function createOsItems($form, $items, array $config): void
    {
        foreach ($items as $idx => $item) {
            $isKinerja = str_contains(strtolower($item->name), 'kinerja');

            $data = [
                'template_item_id' => $item->id,
                'category' => 'operating_system',
                'name' => $item->name,
                'sort_order' => $idx,
            ];

            $value = $config[$item->name] ?? null;

            if ($isKinerja) {
                $kinerja = is_array($value) ? $value : ['status' => $value ?? 'baik', 'keterangan' => null];
                $data['status'] = $kinerja['status'];
                $data['keterangan'] = $kinerja['keterangan'];
                $data['value'] = null;
            } else {
                $data['status'] = null;
                $data['value'] = is_array($value) ? null : $value;
                $data['keterangan'] = null;
            }

            $form->items()->create($data);
        }
    }
}

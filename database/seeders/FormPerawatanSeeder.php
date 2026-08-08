<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\FormPerawatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FormPerawatanSeeder extends Seeder
{
    public function run(): void
    {
        $hardwareItems = ChecklistTemplate::where('form_type', 'perawatan')->where('category', 'hardware')->first()?->items ?? collect();
        $aplikasiItems = ChecklistTemplate::where('form_type', 'perawatan')->where('category', 'aplikasi')->first()?->items ?? collect();
        $osItems = ChecklistTemplate::where('form_type', 'perawatan')->where('category', 'operating_system')->first()?->items ?? collect();

        if ($hardwareItems->isEmpty() || $osItems->isEmpty()) {
            return;
        }

        $teknisi1 = User::where('email', 'technician1@asri.co.id')->first();
        $teknisi2 = User::where('email', 'technician2@asri.co.id')->first();
        $assetByNo = fn (string $no) => Asset::where('no_asset', $no)->first();

        $now = Carbon::now();

        $forms = [
            [
                'nomor_form' => 'PRW-0001',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'SEED04',
                'asset' => $assetByNo('ASR-LPT-2024-001'),
                'kondisi_akhir' => 'good',
                'kondisi_akhir_notes' => null,
                'barcode_fisik' => true,
                'notes' => 'Perawatan rutin Laptop Kantor Finance (ASR-LPT-2024-001)',
                'status' => 'draft',
                'submitted_at' => null,
                'hardware' => [],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0002',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'SEED06',
                'asset' => $assetByNo('ASR-LPT-2024-002'),
                'kondisi_akhir' => 'good',
                'kondisi_akhir_notes' => null,
                'barcode_fisik' => true,
                'notes' => 'Perawatan rutin Laptop Kantor HRD (ASR-LPT-2024-002)',
                'status' => 'draft',
                'submitted_at' => null,
                'hardware' => [],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0003',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'SEED05',
                'asset' => $assetByNo('ASR-LPT-2024-003'),
                'kondisi_akhir' => 'fair',
                'kondisi_akhir_notes' => null,
                'barcode_fisik' => true,
                'notes' => 'Perawatan rutin Laptop Marketing (ASR-LPT-2024-003)',
                'status' => 'submitted',
                'submitted_at' => $now->copy()->subDays(4)->subHours(2),
                'hardware' => [
                    'Physical Cleaning' => ['status' => 'tidak_baik', 'keterangan' => 'Perlu pembersihan menyeluruh pada kipas pendingin.'],
                ],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0004',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'SEED04',
                'asset' => $assetByNo('ASR-DTK-2024-002'),
                'kondisi_akhir' => 'good',
                'kondisi_akhir_notes' => null,
                'barcode_fisik' => true,
                'notes' => 'Perawatan rutin PC Finance (ASR-DTK-2024-002)',
                'status' => 'submitted',
                'submitted_at' => $now->copy()->subDays(2)->subHours(1),
                'hardware' => [],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0005',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'SEED04',
                'asset' => $assetByNo('ASR-LPT-2024-001'),
                'kondisi_akhir' => 'good',
                'kondisi_akhir_notes' => null,
                'barcode_fisik' => true,
                'notes' => 'Perawatan berkala Laptop Kantor Finance (ASR-LPT-2024-001)',
                'status' => 'diketahui',
                'submitted_at' => $now->copy()->subDays(8)->subHours(3),
                'hardware' => [],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0006',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'SEED06',
                'asset' => $assetByNo('ASR-LPT-2024-002'),
                'kondisi_akhir' => 'fair',
                'kondisi_akhir_notes' => null,
                'barcode_fisik' => true,
                'notes' => 'Perawatan berkala Laptop Kantor HRD (ASR-LPT-2024-002)',
                'status' => 'disetujui',
                'submitted_at' => $now->copy()->subDays(15)->subHours(4),
                'hardware' => [
                    'Battery Report' => ['status' => 'tidak_baik', 'keterangan' => 'Kapasitas baterai menurun di bawah 70%.'],
                ],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0007',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'SEED05',
                'asset' => $assetByNo('ASR-LPT-2024-003'),
                'kondisi_akhir' => 'good',
                'kondisi_akhir_notes' => null,
                'barcode_fisik' => true,
                'notes' => 'Perawatan berkala Laptop Marketing (ASR-LPT-2024-003)',
                'status' => 'selesai',
                'submitted_at' => $now->copy()->subDays(28)->subHours(2),
                'hardware' => [],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0008',
                'teknisi' => $teknisi2,
                'pengguna_employee_id' => 'SEED04',
                'asset' => $assetByNo('ASR-DTK-2024-002'),
                'kondisi_akhir' => 'critical',
                'kondisi_akhir_notes' => 'Harddisk menunjukkan bad sector, disarankan penggantian segera.',
                'barcode_fisik' => false,
                'notes' => 'Perawatan berkala PC Finance (ASR-DTK-2024-002)',
                'status' => 'selesai',
                'submitted_at' => $now->copy()->subDays(22)->subHours(5),
                'hardware' => [
                    'Temperature Check' => ['status' => 'tidak_baik', 'keterangan' => 'Suhu komponen di atas ambang normal.'],
                    'Hardisk Test' => ['status' => 'tidak_baik', 'keterangan' => 'Ditemukan bad sector pada harddisk.'],
                ],
                'aplikasi' => [],
                'os' => [],
            ],
            [
                'nomor_form' => 'PRW-0009',
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'SEED04',
                'asset' => $assetByNo('ASR-LPT-2024-001'),
                'kondisi_akhir' => 'poor',
                'kondisi_akhir_notes' => 'Kinerja sistem menurun drastis.',
                'barcode_fisik' => true,
                'notes' => 'Perawatan berkala Laptop Kantor Finance (ASR-LPT-2024-001)',
                'status' => 'revisi',
                'submitted_at' => $now->copy()->subDays(1)->subHours(6),
                'hardware' => [],
                'aplikasi' => [],
                'os' => [
                    'Optimized RAM' => ['status' => 'tidak_baik', 'keterangan' => 'Perlu optimasi ulang penggunaan RAM.'],
                    'Performance Check' => ['status' => 'tidak_baik', 'keterangan' => 'Penggunaan RAM sangat tinggi.'],
                ],
            ],
        ];

        foreach ($forms as $data) {
            if (! $data['teknisi'] || ! $data['asset']) {
                continue;
            }

            $form = FormPerawatan::firstOrCreate(
                ['nomor_form' => $data['nomor_form']],
                [
                    'user_id' => $data['teknisi']->email,
                    'pengguna_employee_id' => $data['pengguna_employee_id'],
                    'asset_id' => $data['asset']->id,
                    'site_location' => $data['asset']->operating_unit,
                    'location_detail' => 'Lantai 2 Ruang B-08',
                    'kondisi_akhir' => $data['kondisi_akhir'],
                    'kondisi_akhir_notes' => $data['kondisi_akhir_notes'],
                    'barcode_fisik' => $data['barcode_fisik'],
                    'notes' => $data['notes'],
                    'status' => $data['status'],
                    'submitted_at' => $data['submitted_at'],
                ]
            );

            if (! $form->wasRecentlyCreated) {
                continue;
            }

            $this->createItems($form, $hardwareItems, 'hardware', $data['hardware']);
            $this->createItems($form, $aplikasiItems, 'aplikasi', $data['aplikasi']);
            $this->createItems($form, $osItems, 'operating_system', $data['os']);
        }
    }

    private function createItems($form, $items, string $category, array $overrides = []): void
    {
        foreach ($items as $idx => $item) {
            $override = $overrides[$item->name] ?? ['status' => 'baik', 'keterangan' => null];

            $form->items()->create([
                'template_item_id' => $item->id,
                'category' => $category,
                'name' => $item->name,
                'status' => $override['status'],
                'keterangan' => $override['keterangan'],
                'sort_order' => $idx,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\Employee;
use App\Models\FormPerawatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FormPerawatanSeeder extends Seeder
{
    public function run(): void
    {
        $teknisiUsers = User::role('teknisi')->get();
        $employees = Employee::where('status', Employee::STATUS_ACTIVE)->get();
        $assets = Asset::whereNotNull('assigned_employee_id')->get();

        if ($teknisiUsers->isEmpty() || $employees->isEmpty() || $assets->isEmpty()) {
            return;
        }

        $hardwareItems = ChecklistTemplate::where('form_type', 'perawatan')->where('category', 'hardware')->first()?->items ?? collect();
        $aplikasiItems = ChecklistTemplate::where('form_type', 'perawatan')->where('category', 'aplikasi')->first()?->items ?? collect();
        $osItems = ChecklistTemplate::where('form_type', 'perawatan')->where('category', 'operating_system')->first()?->items ?? collect();

        $statusConfig = [
            'draft' => 2,
            'submitted' => 2,
            'diketahui' => 1,
            'disetujui' => 1,
            'selesai' => 2,
            'revisi' => 1,
        ];

        $kondisiAkhirOptions = ['good', 'fair', 'critical', 'poor'];

        $formNumber = 1;
        $now = Carbon::now();

        foreach ($statusConfig as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $teknisi = $teknisiUsers->random();
                $pengguna = $employees->random();
                $asset = $assets->random();

                $submittedAt = match ($status) {
                    'draft' => null,
                    default => $now->copy()->subDays(rand(1, 30))->subHours(rand(0, 23)),
                };

                $kondisiAkhir = fake()->randomElement($kondisiAkhirOptions);

                $form = FormPerawatan::create([
                    'nomor_form' => 'PRW-' . str_pad($formNumber++, 4, '0', STR_PAD_LEFT),
                    'user_id' => $teknisi->email,
                    'pengguna_employee_id' => $pengguna->nik,
                    'asset_id' => $asset->id,
                    'site_location' => $asset->operating_unit,
                    'location_detail' => 'Lantai ' . rand(1, 5) . ' Ruang ' . fake()->randomElement(['A', 'B', 'C', 'D']) . '-' . rand(1, 20),
                    'kondisi_akhir' => $kondisiAkhir,
                    'kondisi_akhir_notes' => in_array($kondisiAkhir, ['critical', 'poor']) ? fake()->sentence() : null,
                    'barcode_fisik' => fake()->boolean(80),
                    'notes' => 'Perawatan rutin ' . $asset->nama_perangkat . ' (' . $asset->no_asset . ')',
                    'status' => $status,
                    'submitted_at' => $submittedAt,
                ]);

                $this->createItems($form, $hardwareItems, 'hardware');
                $this->createItems($form, $aplikasiItems, 'aplikasi');
                $this->createItems($form, $osItems, 'operating_system');
            }
        }
    }

    private function createItems($form, $items, string $category): void
    {
        foreach ($items as $idx => $item) {
            $status = fake()->boolean(85) ? 'baik' : 'tidak_baik';

            $form->items()->create([
                'template_item_id' => $item->id,
                'category' => $category,
                'name' => $item->name,
                'status' => $status,
                'keterangan' => $status === 'tidak_baik' ? fake()->sentence() : null,
                'sort_order' => $idx,
            ]);
        }
    }
}

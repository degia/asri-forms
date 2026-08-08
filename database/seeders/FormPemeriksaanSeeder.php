<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\Employee;
use App\Models\FormPemeriksaan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FormPemeriksaanSeeder extends Seeder
{
    public function run(): void
    {
        $teknisiUsers = User::role('teknisi')->get();
        $employees = Employee::where('status', Employee::STATUS_ACTIVE)->get();
        $assets = Asset::whereNotNull('assigned_employee_id')->get();

        if ($teknisiUsers->isEmpty() || $employees->isEmpty() || $assets->isEmpty()) {
            return;
        }

        $hardwareItems = ChecklistTemplate::where('form_type', 'pemeriksaan')->where('category', 'hardware')->first()?->items ?? collect();
        $aplikasiItems = ChecklistTemplate::where('form_type', 'pemeriksaan')->where('category', 'aplikasi')->first()?->items ?? collect();
        $osItems = ChecklistTemplate::where('form_type', 'pemeriksaan')->where('category', 'operating_system')->first()?->items ?? collect();

        $statusConfig = [
            'draft' => 2,
            'submitted' => 2,
            'diketahui' => 1,
            'disetujui' => 1,
            'selesai' => 2,
            'revisi' => 1,
        ];

        $tindakanOptions = [
            ['categories' => ['hardware', 'aplikasi'], 'solution' => 'Perlu penggantian komponen hardware dan update aplikasi'],
            ['categories' => ['aplikasi', 'operating_system'], 'solution' => 'Reinstall aplikasi dan update OS'],
            ['categories' => ['hardware'], 'solution' => 'Perbaikan fisik dan pembersihan komponen'],
            ['categories' => ['aplikasi'], 'solution' => 'Update aplikasi ke versi terbaru'],
            ['categories' => ['operating_system'], 'solution' => 'Optimasi sistem dan pembersihan disk'],
        ];

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

                $tindakan = $tindakanOptions[array_rand($tindakanOptions)];

                $form = FormPemeriksaan::create([
                    'nomor_form' => 'PBR-' . str_pad($formNumber++, 4, '0', STR_PAD_LEFT),
                    'user_id' => $teknisi->email,
                    'pengguna_employee_id' => $pengguna->nik,
                    'asset_id' => $asset->id,
                    'site_location' => $asset->operating_unit,
                    'location_detail' => 'Lantai ' . rand(1, 5) . ' Ruang ' . fake()->randomElement(['A', 'B', 'C', 'D']) . '-' . rand(1, 20),
                    'kondisi' => fake()->boolean(70) ? 'baru' : 'lama',
                    'kondisi_keterangan' => fake()->boolean(30) ? fake()->sentence() : null,
                    'notes' => 'Pemeriksaan rutin ' . $asset->nama_perangkat . ' (' . $asset->no_asset . ')',
                    'tindakan_categories' => $tindakan['categories'],
                    'tindakan_solution' => ($status === 'selesai' || $status === 'disetujui') ? $tindakan['solution'] : null,
                    'status' => $status,
                    'submitted_at' => $submittedAt,
                ]);

                $this->createHardwareItems($form, $hardwareItems);
                $this->createAplikasiItems($form, $aplikasiItems);
                $this->createOsItems($form, $osItems);
            }
        }
    }

    private function createHardwareItems($form, $items): void
    {
        foreach ($items as $idx => $item) {
            $isBattery = str_contains(strtolower($item->name), 'battery');
            $status = fake()->boolean(85) ? 'baik' : 'tidak_baik';

            $data = [
                'template_item_id' => $item->id,
                'category' => 'hardware',
                'name' => $item->name,
                'status' => $status,
                'keterangan' => $status === 'tidak_baik' ? fake()->sentence() : null,
                'sort_order' => $idx,
            ];

            if ($isBattery) {
                $data['full_charge_capacity'] = fake()->numberBetween(15000, 50000);
                $data['design_capacity'] = fake()->numberBetween(40000, 60000);
            }

            $form->items()->create($data);
        }
    }

    private function createAplikasiItems($form, $items): void
    {
        foreach ($items as $idx => $item) {
            $status = fake()->boolean(90) ? 'baik' : 'tidak_baik';

            $form->items()->create([
                'template_item_id' => $item->id,
                'category' => 'aplikasi',
                'name' => $item->name,
                'status' => $status,
                'keterangan' => $status === 'tidak_baik' ? fake()->sentence() : null,
                'sort_order' => $idx,
            ]);
        }
    }

    private function createOsItems($form, $items): void
    {
        $osVersion = fake()->randomElement(['Windows 11 Pro', 'Windows 10 Pro', 'Windows 11 Enterprise', 'Windows 10 Enterprise']);
        $hostname = 'PC-' . strtoupper(fake()->bothify('??-####'));

        foreach ($items as $idx => $item) {
            $name = strtolower($item->name);

            $value = match (true) {
                str_contains($name, 'nama os') => $osVersion,
                str_contains($name, 'hostname') => $hostname,
                str_contains($name, 'user account') => fake()->name(),
                str_contains($name, 'disk') => fake()->numberBetween(25, 85) . '% used of ' . fake()->randomElement(['256GB', '512GB', '1TB']),
                default => null,
            };

            $status = $value ? null : (fake()->boolean(90) ? 'baik' : 'tidak_baik');

            $form->items()->create([
                'template_item_id' => $item->id,
                'category' => 'operating_system',
                'name' => $item->name,
                'status' => $status,
                'value' => $value,
                'keterangan' => $status === 'tidak_baik' ? fake()->sentence() : null,
                'sort_order' => $idx,
            ]);
        }
    }
}

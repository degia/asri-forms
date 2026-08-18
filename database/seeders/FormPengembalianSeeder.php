<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\FormPengembalian;
use App\Models\FormPengembalianItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormPengembalianSeeder extends Seeder
{
    public function run(): void
    {
        $teknisi1 = User::where('email', 'technician1@asri.co.id')->first();
        $assetByNo = fn (string $no) => Asset::where('no_asset', $no)->first();

        $forms = [
            [
                'nomor_form' => '001/PNG/' . now()->format('dmY'),
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'USR001',
                'asset_no' => 'ASR-DTK-2024-002',
                'tanggal_pengembalian' => now()->subDays(6),
                'kondisi' => 'baik',
                'kelengkapan' => 'lengkap',
                'notes' => 'Pengembalian PC Finance karena user1 pindah lokasi kerja.',
                'submitted_at' => now()->subDays(6)->subHours(2),
            ],
            [
                'nomor_form' => '002/PNG/' . now()->format('dmY'),
                'teknisi' => $teknisi1,
                'pengguna_employee_id' => 'USR003',
                'asset_no' => 'ASR-LPT-2024-002',
                'tanggal_pengembalian' => now()->subDays(3),
                'kondisi' => 'baik',
                'kelengkapan' => 'tidak_lengkap',
                'notes' => 'Pengembalian Laptop Kantor HRD, adaptor tidak ikut dikembalikan.',
                'submitted_at' => now()->subDays(3)->subHours(1),
            ],
        ];

        foreach ($forms as $data) {
            $asset = $assetByNo($data['asset_no']);

            if (! $data['teknisi'] || ! $asset) {
                continue;
            }

            $form = FormPengembalian::firstOrCreate(
                ['nomor_form' => $data['nomor_form']],
                [
                    'teknisi_id' => $data['teknisi']->email,
                    'pengguna_employee_id' => $data['pengguna_employee_id'],
                    'tanggal_pengembalian' => $data['tanggal_pengembalian'],
                    'kondisi' => $data['kondisi'],
                    'kelengkapan' => $data['kelengkapan'],
                    'notes' => $data['notes'],
                    'status' => 'submitted',
                    'submitted_at' => $data['submitted_at'],
                ]
            );

            if (! $form->wasRecentlyCreated) {
                continue;
            }

            FormPengembalianItem::create([
                'form_pengembalian_id' => $form->id,
                'asset_id' => $asset->id,
            ]);

            $asset->update([
                'assigned_employee_id' => null,
                'status' => 'inactive',
            ]);
        }
    }
}

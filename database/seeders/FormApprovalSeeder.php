<?php

namespace Database\Seeders;

use App\Enums\ApprovalLevel;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FormApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFormApprovals(FormPemeriksaan::class, 'pemeriksaan');
        $this->seedFormApprovals(FormPerawatan::class, 'perawatan');
    }

    private function seedFormApprovals(string $modelClass, string $label): void
    {
        $forms = $modelClass::with(['teknisi', 'pengguna'])->get();

        $supervisor = User::role('supervisor_it')->first();
        $manager = User::role('manager_it')->first();

        foreach ($forms as $form) {
            $submittedAt = $form->submitted_at;
            if (!$submittedAt) continue;

            // Diperiksa oleh (teknisi) - auto-approved when submitted
            if (in_array($form->status, ['submitted', 'diketahui', 'disetujui', 'selesai', 'revisi'])) {
                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DiperiksaOleh->value,
                    'user_id' => $form->teknisi->email,
                    'status' => 'approved',
                    'approved_at' => $submittedAt->copy()->addMinutes(rand(5, 30)),
                    'catatan' => ucfirst($label) . ' selesai dilakukan',
                ]);
            }

            // Diketahui oleh (pengguna) - for forms that have been acknowledged
            if (in_array($form->status, ['diketahui', 'disetujui', 'selesai'])) {
                $penggunaUserId = $form->pengguna?->user?->email;
                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DiketahuiOleh->value,
                    'user_id' => $penggunaUserId,
                    'custom_signer_name' => $penggunaUserId ? null : $form->pengguna?->name,
                    'status' => 'approved',
                    'approved_at' => $submittedAt->copy()->addHours(rand(1, 24)),
                    'catatan' => 'Mengetahui hasil ' . $label . ' dan kondisi sesuai',
                ]);

                // Disetujui oleh - pending (waiting for approval)
                if ($form->status === 'diketahui') {
                    $form->approvals()->create([
                        'approval_level' => ApprovalLevel::DisetujuiOleh->value,
                        'user_id' => $supervisor?->email,
                        'custom_signer_name' => $supervisor ? null : 'Supervisor IT',
                        'status' => 'pending',
                    ]);
                }
            }

            // Disetujui oleh (supervisor/manager) - for fully approved forms
            if (in_array($form->status, ['disetujui', 'selesai'])) {
                $approver = fake()->boolean(60) ? $manager : $supervisor;

                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DisetujuiOleh->value,
                    'user_id' => $approver?->email,
                    'custom_signer_name' => $approver ? null : ($manager ? null : 'Manager IT'),
                    'status' => 'approved',
                    'approved_at' => $submittedAt->copy()->addDays(rand(1, 3)),
                    'catatan' => 'Disetujui, ' . $label . ' sudah sesuai prosedur',
                ]);
            }

            // Revisi - create approval with rejection note
            if ($form->status === 'revisi') {
                // Diketahui but rejected (needs revision)
                $penggunaUserId = $form->pengguna?->user?->email;
                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DiketahuiOleh->value,
                    'user_id' => $penggunaUserId,
                    'custom_signer_name' => $penggunaUserId ? null : $form->pengguna?->name,
                    'status' => 'approved',
                    'approved_at' => $submittedAt->copy()->addHours(rand(1, 24)),
                    'catatan' => 'Mengetahui hasil ' . $label,
                ]);

                // Disetujui - rejected with revision notes
                $rejector = fake()->boolean(50) ? $manager : $supervisor;
                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DisetujuiOleh->value,
                    'user_id' => $rejector?->email,
                    'custom_signer_name' => $rejector ? null : 'Approver',
                    'status' => 'rejected',
                    'approved_at' => $submittedAt->copy()->addDays(rand(1, 2)),
                    'catatan' => 'Perlu revisi: ' . fake()->randomElement([
                        'Data kondisi tidak lengkap',
                        'Foto pendukung belum dilampirkan',
                        'Terdapat item yang belum diperiksa',
                        'Mohon dilengkapi keterangan untuk item yang tidak baik',
                        'Tanda tangan pengguna belum ada',
                    ]),
                ]);
            }
        }
    }
}

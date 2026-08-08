<?php

namespace Database\Seeders;

use App\Enums\ApprovalLevel;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use App\Models\User;
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

            if (! $submittedAt || $form->approvals()->exists()) {
                continue;
            }

            $teknisiEmail = $form->teknisi?->email;
            $penggunaUserId = $form->pengguna?->user?->email;

            // Diperiksa oleh (teknisi) - auto-approved when submitted
            if (in_array($form->status, ['submitted', 'diketahui', 'disetujui', 'selesai', 'revisi'])) {
                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DiperiksaOleh->value,
                    'user_id' => $teknisiEmail,
                    'status' => 'approved',
                    'approved_at' => $submittedAt->copy()->addMinutes(15),
                    'catatan' => ucfirst($label) . ' selesai dilakukan',
                ]);
            }

            // Diketahui oleh (pengguna)
            if (in_array($form->status, ['diketahui', 'disetujui', 'selesai', 'revisi'])) {
                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DiketahuiOleh->value,
                    'user_id' => $penggunaUserId,
                    'custom_signer_name' => $penggunaUserId ? null : $form->pengguna?->name,
                    'status' => 'approved',
                    'approved_at' => $submittedAt->copy()->addHours(6),
                    'catatan' => 'Mengetahui hasil ' . $label . ' dan kondisi sesuai',
                ]);
            }

            // Disetujui oleh (supervisor/manager)
            if (in_array($form->status, ['diketahui', 'disetujui', 'selesai'])) {
                if ($form->status === 'diketahui') {
                    $form->approvals()->create([
                        'approval_level' => ApprovalLevel::DisetujuiOleh->value,
                        'user_id' => $supervisor?->email,
                        'custom_signer_name' => $supervisor ? null : 'Supervisor IT',
                        'status' => 'pending',
                    ]);
                } else {
                    $form->approvals()->create([
                        'approval_level' => ApprovalLevel::DisetujuiOleh->value,
                        'user_id' => $manager?->email,
                        'custom_signer_name' => $manager ? null : 'Manager IT',
                        'status' => 'approved',
                        'approved_at' => $submittedAt->copy()->addDays(2),
                        'catatan' => 'Disetujui, ' . $label . ' sudah sesuai prosedur',
                    ]);
                }
            }

            // Revisi - approval ditolak dengan catatan revisi
            if ($form->status === 'revisi') {
                $form->approvals()->create([
                    'approval_level' => ApprovalLevel::DisetujuiOleh->value,
                    'user_id' => $supervisor?->email,
                    'custom_signer_name' => $supervisor ? null : 'Supervisor IT',
                    'status' => 'rejected',
                    'approved_at' => $submittedAt->copy()->addDay(),
                    'catatan' => 'Perlu revisi: data kondisi tidak lengkap dan foto pendukung belum dilampirkan',
                ]);
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\FormApproval;
use Illuminate\Console\Command;

class CleanDuplicateApprovals extends Command
{
    protected $signature = 'approvals:dedupe {--dry-run : Hanya menampilkan jumlah tanpa menghapus}';

    protected $description = 'Menghapus baris approval duplikat, menyisakan satu baris terbaik per level per form';

    public function handle(): int
    {
        $deleted = 0;
        $groups = 0;

        foreach ($this->duplicateGroups() as $group) {
            $rows = FormApproval::where('approvable_type', $group->approvable_type)
                ->where('approvable_id', $group->approvable_id)
                ->where('approval_level', $group->approval_level)
                ->orderByRaw("FIELD(status, 'approved', 'rejected', 'pending')")
                ->orderByDesc('updated_at')
                ->get();

            if ($rows->count() <= 1) {
                continue;
            }

            $groups++;
            $keep = $rows->shift();

            if ($this->option('dry-run')) {
                $deleted += $rows->count();
                $this->line("  [DRY] {$group->approvable_type} #{$group->approvable_id} ({$group->approval_level}): simpan #{$keep->id}, buang ".$rows->count().' baris');

                continue;
            }

            foreach ($rows as $duplicate) {
                $duplicate->delete();
                $deleted++;
            }
        }

        if ($this->option('dry-run')) {
            $this->info("Dry-run selesai: {$groups} grup duplikat ditemukan, {$deleted} baris akan dihapus.");
        } else {
            $this->info("Selesai: {$deleted} baris approval duplikat dihapus dari {$groups} grup.");
        }

        return 0;
    }

    private function duplicateGroups()
    {
        return FormApproval::query()
            ->select('approvable_type', 'approvable_id', 'approval_level')
            ->groupBy('approvable_type', 'approvable_id', 'approval_level')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('approvable_type')
            ->get();
    }
}

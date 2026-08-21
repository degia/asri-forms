<?php

namespace App\Jobs;

use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public string $type,
        public ?string $status,
        public ?string $kondisi,
        public string $userEmail,
    ) {}

    public function handle(): string
    {
        $baseName = "form-{$this->type}-" . now()->format('Y-m-d_His');

        if ($this->type === 'pemeriksaan') {
            $query = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals.user']);
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->kondisi) {
                $query->where('kondisi', $this->kondisi);
            }
            $forms = $query->orderBy('submitted_at', 'desc')->get();
            $pdf = Pdf::loadView('pdf.admin-bulk-pemeriksaan', compact('forms'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals.user']);
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->kondisi) {
                $query->where('kondisi_akhir', $this->kondisi);
            }
            $forms = $query->orderBy('submitted_at', 'desc')->get();
            $pdf = Pdf::loadView('pdf.admin-bulk-perawatan', compact('forms'))
                ->setPaper('a4', 'landscape');
        }

        $filename = "{$baseName}.pdf";
        $disk = Storage::disk('local');
        $path = "exports/{$filename}";
        $disk->put($path, $pdf->output());

        $user = User::find($this->userEmail);
        if ($user) {
            $user->notify(new ExportReadyNotification($this->type, $path, $filename));
        }

        return $path;
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::find($this->userEmail);
        if ($user) {
            $user->notify(new \App\Notifications\ExportFailedNotification(
                $this->type,
                $exception->getMessage()
            ));
        }
    }
}

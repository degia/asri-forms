<?php

namespace App\Http\Controllers;

use App\Models\FormPemeriksaan;
use App\Models\FormPengembalian;
use App\Models\FormPerawatan;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class BulkPdfDownloadController extends Controller
{
    public function pemeriksaan()
    {
        return $this->downloadZip(
            type: 'pemeriksaan',
            ids: request()->input('ids', []),
            view: 'pdf.pemeriksaan',
            query: FormPemeriksaan::with(['teknisi', 'pengguna.position', 'pengguna.divisi', 'asset', 'site', 'items', 'approvals.user']),
            baseName: 'form-pemeriksaan',
        );
    }

    public function perawatan()
    {
        return $this->downloadZip(
            type: 'perawatan',
            ids: request()->input('ids', []),
            view: 'pdf.perawatan',
            query: FormPerawatan::with(['teknisi', 'pengguna.position', 'pengguna.divisi', 'asset', 'site', 'items', 'approvals.user']),
            baseName: 'form-perawatan',
        );
    }

    public function pengembalian()
    {
        return $this->downloadZip(
            type: 'pengembalian',
            ids: request()->input('ids', []),
            view: 'pdf.pengembalian',
            query: FormPengembalian::with(['teknisi', 'pengguna', 'items.asset']),
            baseName: 'form-pengembalian',
        );
    }

    private function downloadZip(
        string $type,
        array $ids,
        string $view,
        $query,
        string $baseName,
    ): \Symfony\Component\HttpFoundation\Response {
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada form dipilih.');
        }

        $forms = $query->whereIn(
            match ($type) {
                'pemeriksaan' => 'id',
                'perawatan' => 'id',
                'pengembalian' => 'id',
                default => 'id',
            },
            $ids
        )->get();

        if ($forms->isEmpty()) {
            return redirect()->back()->with('error', 'Form tidak ditemukan.');
        }

        $zipPath = storage_path("app/{$baseName}-" . now()->format('Y-m-d_His') . '.zip');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
        }

        foreach ($forms as $form) {
            $pdf = Pdf::loadView($view, ['form' => $form])
                ->setPaper('a4', 'portrait');

            $filename = str_replace('/', '-', $form->nomor_form) . '.pdf';
            $zip->addFromString($filename, $pdf->output());
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}

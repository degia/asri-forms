<?php

namespace App\Http\Controllers;

use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportPdfController extends Controller
{
    public function pemeriksaan(int $id)
    {
        $form = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals.user'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.pemeriksaan', compact('form'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("form-pemeriksaan-" . str_replace('/', '-', $form->nomor_form) . ".pdf");
    }

    public function perawatan(int $id)
    {
        $form = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals.user'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.perawatan', compact('form'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("form-perawatan-" . str_replace('/', '-', $form->nomor_form) . ".pdf");
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\FormPemeriksaanExport;
use App\Exports\FormPerawatanExport;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormExportController extends Controller
{
    public function exportPemeriksaan(string $format)
    {
        $status = request()->query('status');
        $kondisi = request()->query('kondisi');
        $timestamp = now()->format('Y-m-d_His');
        $baseName = "form-pemeriksaan-{$timestamp}";

        return match ($format) {
            'pdf' => $this->exportPdf('pemeriksaan', $status, $kondisi, $baseName),
            'xlsx' => Excel::download(new FormPemeriksaanExport($status, $kondisi), "{$baseName}.xlsx"),
            'xls' => Excel::download(new FormPemeriksaanExport($status, $kondisi), "{$baseName}.xls"),
            'csv' => $this->exportCsv('pemeriksaan', $status, $kondisi, $baseName),
            'html' => $this->exportHtml('pemeriksaan', $status, $kondisi, $baseName),
            default => redirect()->back()->with('error', 'Format export tidak valid.'),
        };
    }

    public function exportPerawatan(string $format)
    {
        $status = request()->query('status');
        $kondisi = request()->query('kondisi_akhir');
        $timestamp = now()->format('Y-m-d_His');
        $baseName = "form-perawatan-{$timestamp}";

        return match ($format) {
            'pdf' => $this->exportPdf('perawatan', $status, $kondisi, $baseName),
            'xlsx' => Excel::download(new FormPerawatanExport($status, $kondisi), "{$baseName}.xlsx"),
            'xls' => Excel::download(new FormPerawatanExport($status, $kondisi), "{$baseName}.xls"),
            'csv' => $this->exportCsv('perawatan', $status, $kondisi, $baseName),
            'html' => $this->exportHtml('perawatan', $status, $kondisi, $baseName),
            default => redirect()->back()->with('error', 'Format export tidak valid.'),
        };
    }

    private function exportPdf(string $type, ?string $status, ?string $kondisi, string $baseName)
    {
        if ($type === 'pemeriksaan') {
            $query = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals.user']);
            if ($status) {
                $query->where('status', $status);
            }
            if ($kondisi) {
                $query->where('kondisi', $kondisi);
            }
            $forms = $query->orderBy('submitted_at', 'desc')->get();

            $pdf = Pdf::loadView('pdf.admin-bulk-pemeriksaan', compact('forms'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals.user']);
            if ($status) {
                $query->where('status', $status);
            }
            if ($kondisi) {
                $query->where('kondisi_akhir', $kondisi);
            }
            $forms = $query->orderBy('submitted_at', 'desc')->get();

            $pdf = Pdf::loadView('pdf.admin-bulk-perawatan', compact('forms'))
                ->setPaper('a4', 'landscape');
        }

        return $pdf->download("{$baseName}.pdf");
    }

    private function exportCsv(string $type, ?string $status, ?string $kondisi, string $baseName): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$baseName}.csv\"",
        ];

        $callback = function () use ($type, $status, $kondisi) {
            $file = fopen('php://output', 'w');

            if ($type === 'pemeriksaan') {
                fputcsv($file, ['No. Form', 'Tanggal', 'Teknisi', 'Pengguna', 'Perangkat', 'No. Asset', 'Site', 'Kondisi', 'Status', 'Catatan']);
                $query = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site']);
                if ($status) {
                    $query->where('status', $status);
                }
                if ($kondisi) {
                    $query->where('kondisi', $kondisi);
                }
                $query->orderBy('submitted_at', 'desc')->each(function ($form) use ($file) {
                    fputcsv($file, [
                        $form->nomor_form,
                        $form->submitted_at?->format('d/m/Y H:i') ?? '-',
                        $form->teknisi->name ?? '-',
                        $form->pengguna->name ?? '-',
                        $form->asset->nama_perangkat ?? '-',
                        $form->asset->no_asset ?? '-',
                        $form->site->site ?? $form->site_location ?? '-',
                        $form->kondisi ?? '-',
                        $form->status,
                        $form->notes ?? '-',
                    ]);
                });
            } else {
                fputcsv($file, ['No. Form', 'Tanggal', 'Teknisi', 'Pengguna', 'Perangkat', 'No. Asset', 'Site', 'Kondisi Akhir', 'Status', 'Catatan']);
                $query = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site']);
                if ($status) {
                    $query->where('status', $status);
                }
                if ($kondisi) {
                    $query->where('kondisi_akhir', $kondisi);
                }
                $query->orderBy('submitted_at', 'desc')->each(function ($form) use ($file) {
                    fputcsv($file, [
                        $form->nomor_form,
                        $form->submitted_at?->format('d/m/Y H:i') ?? '-',
                        $form->teknisi->name ?? '-',
                        $form->pengguna->name ?? '-',
                        $form->asset->nama_perangkat ?? '-',
                        $form->asset->no_asset ?? '-',
                        $form->site->site ?? $form->site_location ?? '-',
                        $form->kondisi_akhir ?? '-',
                        $form->status,
                        $form->notes ?? '-',
                    ]);
                });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportHtml(string $type, ?string $status, ?string $kondisi, string $baseName)
    {
        if ($type === 'pemeriksaan') {
            $query = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site']);
            if ($status) {
                $query->where('status', $status);
            }
            if ($kondisi) {
                $query->where('kondisi', $kondisi);
            }
            $forms = $query->orderBy('submitted_at', 'desc')->get();
            $view = 'pdf.admin-export-html-pemeriksaan';
        } else {
            $query = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site']);
            if ($status) {
                $query->where('status', $status);
            }
            if ($kondisi) {
                $query->where('kondisi_akhir', $kondisi);
            }
            $forms = $query->orderBy('submitted_at', 'desc')->get();
            $view = 'pdf.admin-export-html-perawatan';
        }

        $html = view($view, compact('forms'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$baseName}.html\"");
    }
}

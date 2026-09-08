<?php

namespace App\Http\Controllers;

use App\Models\FormPerawatan;
use App\Models\Site;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardExportController extends Controller
{
    public function export(string $format, Request $request)
    {
        $year = (string) $request->query('year', '');
        $ou = (string) $request->query('ou', '');
        $assetStatus = (string) $request->query('asset_status', 'active');

        $data = $this->buildData($year, $ou, $assetStatus);
        $forms = $this->buildForms($year, $ou, $assetStatus);

        $baseName = 'dashboard_perawatan_kondisi_'.now()->format('Y-m-d_His');

        $viewData = [
            'title' => 'Laporan Perawatan by Kondisi Akhir per Operating Unit Asset',
            'filters' => $this->buildFilters($year, $ou, $assetStatus),
            'rows' => $data,
            'totals' => $this->buildTotals($data),
            'forms' => $forms,
            'exportedAt' => now(),
        ];

        return match ($format) {
            'html' => $this->exportHtml($viewData, $baseName),
            'pdf' => Pdf::loadView('pdf.dashboard-perawatan-kondisi', $viewData)
                ->setPaper('a4', 'landscape')
                ->download("{$baseName}.pdf"),
            default => redirect()->back()->with('error', 'Format export tidak valid.'),
        };
    }

    private function exportHtml(array $viewData, string $baseName)
    {
        $html = view('pdf.dashboard-perawatan-kondisi', $viewData)->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$baseName}.html\"");
    }

    private function baseQuery(string $year, string $ou, string $assetStatus)
    {
        $query = FormPerawatan::query()
            ->whereNotNull('submitted_at')
            ->whereNotNull('kondisi_akhir')
            ->where('kondisi_akhir', '!=', '')
            ->whereHas('asset', function ($q) use ($ou, $assetStatus) {
                $q->whereNotNull('operating_unit')
                    ->where('operating_unit', '!=', '');
                if ($assetStatus !== '') {
                    $q->where('status', $assetStatus);
                }
            });

        if ($ou !== '') {
            $query->whereHas('asset', fn ($q) => $q->where('operating_unit', $ou));
        }

        if ($year !== '') {
            $query->whereYear('submitted_at', (int) $year);
        }

        return $query;
    }

    private function buildData(string $year, string $ou, string $assetStatus): array
    {
        $rows = (clone $this->baseQuery($year, $ou, $assetStatus))
            ->join('assets', 'assets.id', '=', 'form_perawatan.asset_id')
            ->select(
                'assets.operating_unit',
                'form_perawatan.kondisi_akhir as kondisi',
                DB::raw('COUNT(DISTINCT form_perawatan.asset_id) as total')
            )
            ->groupBy('assets.operating_unit', 'form_perawatan.kondisi_akhir')
            ->get();

        $siteIds = collect($rows)->pluck('operating_unit')->unique()->values()->toArray();
        $siteNames = $siteIds ? Site::whereIn('id_site', $siteIds)->pluck('site', 'id_site')->toArray() : [];

        $result = [];
        foreach ($rows as $row) {
            $ouId = $row->operating_unit;
            $kondisi = $row->kondisi === 'good_normal' ? 'good' : $row->kondisi;
            if (! isset($result[$ouId])) {
                $result[$ouId] = [
                    'ou_id' => $ouId,
                    'ou' => $siteNames[$ouId] ?? $ouId,
                    'totals' => ['good' => 0, 'fair' => 0, 'critical' => 0, 'poor' => 0],
                    'total' => 0,
                ];
            }
            if (isset($result[$ouId]['totals'][$kondisi])) {
                $result[$ouId]['totals'][$kondisi] += (int) $row->total;
                $result[$ouId]['total'] += (int) $row->total;
            }
        }

        uasort($result, fn ($a, $b) => $b['total'] <=> $a['total']);

        return array_values($result);
    }

    private function buildForms(string $year, string $ou, string $assetStatus): Collection
    {
        return $this->baseQuery($year, $ou, $assetStatus)
            ->with([
                'teknisi:email,name',
                'pengguna:nik,name',
                'asset:id,nama_perangkat,no_asset,operating_unit,status',
                'site:id_site,site',
            ])
            ->latest('submitted_at')
            ->get()
            ->map(fn ($form) => [
                'nomor_form' => $form->nomor_form,
                'teknisi' => $form->teknisi?->name ?? '-',
                'pengguna' => $form->pengguna?->name ?? '-',
                'perangkat' => $form->asset?->nama_perangkat ?? '-',
                'no_asset' => $form->asset?->no_asset ?? '',
                'site' => $form->site?->site ?? $form->site_location ?? '-',
                'kondisi_akhir' => $form->kondisi_akhir,
                'kondisi_akhir_notes' => $form->kondisi_akhir_notes,
                'status' => $form->status,
                'tanggal' => $form->submitted_at?->format('d/m/Y') ?? '-',
                'notes' => $form->notes,
            ]);
    }

    private function buildTotals(array $rows): array
    {
        $totals = ['good' => 0, 'fair' => 0, 'critical' => 0, 'poor' => 0, 'grand' => 0];
        foreach ($rows as $row) {
            foreach (['good', 'fair', 'critical', 'poor'] as $k) {
                $totals[$k] += $row['totals'][$k] ?? 0;
            }
            $totals['grand'] += $row['total'];
        }

        return $totals;
    }

    private function buildFilters(string $year, string $ou, string $assetStatus): array
    {
        $ouName = $ou !== ''
            ? (Site::where('id_site', $ou)->value('site') ?? $ou)
            : 'Semua';

        return [
            'Periode (Tahun)' => $year !== '' ? $year : 'Semua Tahun',
            'Operating Unit' => $ouName,
            'Status Asset' => $assetStatus !== '' ? ucfirst($assetStatus) : 'Semua',
        ];
    }
}
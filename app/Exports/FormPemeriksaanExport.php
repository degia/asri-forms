<?php

namespace App\Exports;

use App\Models\FormPemeriksaan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormPemeriksaanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected ?string $status;

    protected ?string $kondisi;

    public function __construct(?string $status = null, ?string $kondisi = null)
    {
        $this->status = $status;
        $this->kondisi = $kondisi;
    }

    public function collection()
    {
        $query = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals']);

        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->kondisi) {
            $query->where('kondisi', $this->kondisi);
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No. Form',
            'Tanggal',
            'Teknisi',
            'Pengguna',
            'NIK',
            'Site',
            'Perangkat',
            'No. Asset',
            'Kategori',
            'Brand',
            'Tipe',
            'No. Serial',
            'Site',
            'Kondisi',
            'Kondisi Keterangan',
            'Status',
            'Catatan',
            'Tindakan',
            'Solution',
        ];
    }

    public function map($form): array
    {
        $tindakan = '';
        if ($form->tindakan_categories) {
            $parts = [];
            foreach ($form->tindakan_categories as $cat) {
                if (! empty($cat['selected'])) {
                    $parts[] = $cat['label'].': '.implode(', ', $cat['selected']);
                }
            }
            $tindakan = implode(' | ', $parts);
        }

        return [
            $form->nomor_form,
            $form->submitted_at?->format('d/m/Y H:i') ?? '-',
            $form->teknisi->name ?? '-',
            $form->pengguna->name ?? '-',
            $form->pengguna->nik ?? '-',
            $form->pengguna->site_name ?? '-',
            $form->asset->nama_perangkat ?? '-',
            $form->asset->no_asset ?? '-',
            $form->asset->kategori ?? '-',
            $form->asset->brand ?? '-',
            $form->asset->tipe ?? '-',
            $form->asset->no_serial ?? '-',
            $form->site->site ?? $form->site_location ?? '-',
            ucfirst($form->kondisi ?? '-'),
            $form->kondisi_keterangan ?? '-',
            ucfirst($form->status),
            $form->notes ?? '-',
            $tindakan ?: '-',
            $form->tindakan_solution ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

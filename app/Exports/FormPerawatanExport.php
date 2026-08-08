<?php

namespace App\Exports;

use App\Models\FormPerawatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormPerawatanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected ?string $status;

    protected ?string $kondisiAkhir;

    public function __construct(?string $status = null, ?string $kondisiAkhir = null)
    {
        $this->status = $status;
        $this->kondisiAkhir = $kondisiAkhir;
    }

    public function collection()
    {
        $query = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals']);

        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->kondisiAkhir) {
            $query->where('kondisi_akhir', $this->kondisiAkhir);
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
            'Kondisi Akhir',
            'Keterangan Kondisi',
            'Status',
            'Catatan',
        ];
    }

    public function map($form): array
    {
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
            ucfirst(str_replace('_', ' ', $form->kondisi_akhir ?? '-')),
            $form->kondisi_akhir_notes ?? '-',
            ucfirst($form->status),
            $form->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

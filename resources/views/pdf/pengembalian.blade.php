<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Pengembalian Asset {{ $form->nomor_form }}</title>
    <style>
        @page { size: A4 portrait; margin: 30mm 5mm 15mm 5mm; }
        body { margin: 0; font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; line-height: 1.4; }
        div, p, h1, h2, h3, table, tr, td, th, ul, ol, li, img, strong, span { margin: 0; padding: 0; box-sizing: border-box; }
        .pdf-content { margin: 0; }

        .pdf-header { position: fixed; top: -25mm; left: 0; right: 0; background: #ffffff; }
        .pdf-footer { position: fixed; bottom: -10mm; left: 0; right: 0; background: #ffffff; }
        table { border-collapse: collapse; }
        td, th { padding: 3px 6px; }

        .header-table { width: 100%; margin-bottom: 8px; }
        .header-table td { border: none; vertical-align: middle; }
        .header-logo { width: 55px; }
        .header-title { text-align: center; }
        .header-title h1 { font-size: 18px; font-weight: bold; margin: 0; }
        .header-title p { font-size: 10px; color: #555; margin: 1px 0 0; }

        .form-row { width: 100%; margin-bottom: 8px; }
        .form-row td { border: none; padding: 0; }
        .form-no { font-size: 12px; font-weight: bold; }
        .form-date { font-size: 12px; text-align: right; }

        .info-table { width: 100%; border: 1px solid #999; margin-bottom: 8px; }
        .info-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 11px; }
        .info-table .lbl { background: #f0f0f0; font-weight: 600; width: 16%; font-size: 10px; }
        .info-table .val { width: 34%; }

        .section-title { font-size: 12px; font-weight: bold; margin: 8px 0 4px; padding: 3px 6px; background: #e8e8e8; border-left: 3px solid #333; }

        .detail-table { width: 100%; border: 1px solid #999; margin-bottom: 8px; }
        .detail-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 11px; }
        .detail-table .lbl { background: #f0f0f0; font-weight: 600; font-size: 10px; width: 20%; }
        .detail-table .val { font-size: 11px; }

        .item-table { width: 100%; border: 1px solid #999; margin-bottom: 8px; }
        .item-table th { background: #f5f5f5; border: 1px solid #ccc; padding: 3px 6px; font-size: 10px; text-align: left; font-weight: 600; }
        .item-table td { border: 1px solid #ddd; padding: 3px 6px; font-size: 10px; }
        .item-table tr:nth-child(even) td { background: #fafafa; }

        .catatan { font-size: 11px; margin: 8px 0; padding: 5px 6px; border: 1px solid #ddd; }
        .catatan strong { display: block; margin-bottom: 2px; font-size: 11px; }

        .pdf-footer { text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>
<div class="pdf-header">
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('images/asri.png') }}" style="width: 50px;">
            </td>
            <td class="header-title">
                <h1>FORMULIR PENGEMBALIAN ASET</h1>
                <p>IT Department &mdash; ASRI</p>
            </td>
            <td style="width: 55px; border: none;"></td>
        </tr>
    </table>

    <table class="form-row">
        <tr>
            <td class="form-no">No : {{ $form->nomor_form }}</td>
            <td class="form-date">Tanggal : {{ $form->tanggal_pengembalian ? $form->tanggal_pengembalian->format('d/m/Y') : ($form->submitted_at ? $form->submitted_at->format('d/m/Y') : '-') }}</td>
        </tr>
    </table>
</div>

<div class="pdf-content">

    {{-- INFORMASI TEKNISI --}}
    <div class="section-title">Informasi Teknisi</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Nama Teknisi</td>
            <td class="val">{{ $form->teknisi->name ?? '-' }}</td>
            <td class="lbl">Email</td>
            <td class="val">{{ $form->teknisi->email ?? '-' }}</td>
        </tr>
    </table>

    {{-- INFORMASI PENGGUNA --}}
    <div class="section-title">Informasi Pengguna</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Nama - [ NIK ]</td>
            <td class="val">{{ $form->pengguna->name ?? '-' }} - [ {{ $form->pengguna->nik ?? '-' }} ]</td>
            <td class="lbl">Unit Site</td>
            <td class="val">{{ $form->pengguna->site_name ?? '-' }}</td>
        </tr>
    </table>

    {{-- DETAIL PENGEMBALIAN --}}
    <div class="section-title">Detail Pengembalian</div>
    <table class="detail-table">
        <tr>
            <td class="lbl">Tanggal Pengembalian</td>
            <td class="val">{{ $form->tanggal_pengembalian ? $form->tanggal_pengembalian->format('d/m/Y') : '-' }}</td>
            <td class="lbl">Kondisi</td>
            <td class="val">
                @if($form->kondisi === 'baik') Baik
                @elseif($form->kondisi === 'rusak') Rusak
                @elseif($form->kondisi === 'hilang') Hilang
                @else - @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Kelengkapan</td>
            <td class="val">
                @if($form->kelengkapan === 'lengkap') Lengkap
                @elseif($form->kelengkapan === 'tidak_lengkap') Tidak Lengkap
                @else - @endif
            </td>
            <td class="lbl">Status</td>
            <td class="val">{{ ucfirst($form->status ?? '-') }}</td>
        </tr>
    </table>

    {{-- DAFTAR ASET --}}
    <div class="section-title">Daftar Aset yang Dikembalikan</div>
    <table class="item-table">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:15%;">No. Asset</th>
                <th style="width:20%;">Nama Perangkat</th>
                <th style="width:15%;">Brand</th>
                <th style="width:15%;">Tipe</th>
                <th style="width:15%;">No. Serial</th>
            </tr>
        </thead>
        <tbody>
            @forelse($form->items as $idx => $item)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->asset->no_asset ?? '-' }}</td>
                    <td>{{ $item->asset->nama_perangkat ?? '-' }}</td>
                    <td>{{ $item->asset->brand ?? '-' }}</td>
                    <td>{{ $item->asset->tipe ?? '-' }}</td>
                    <td>{{ $item->asset->no_serial ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; color:#999;">Tidak ada aset</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- CATATAN --}}
    @if($form->notes)
        <div class="section-title">Catatan</div>
        <div class="catatan">
            <strong>></strong>
            {{ $form->notes }}
        </div>
    @endif

</div>

<div class="pdf-footer">
    FM-ASRI/ITE/10-00 - Form Pengembalian Aset &mdash; {{ $form->nomor_form }}
</div>
</body>
</html>

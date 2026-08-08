<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Perawatan {{ $form->nomor_form }}</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; line-height: 1.4; }
        .pdf-content { margin: 15mm 15mm 15mm 15mm; }
        .pdf-section { margin-bottom: 6px; }
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

        .section-title { font-size: 12px; font-weight: bold; margin: 8px 0 4px; padding: 3px 6px; background: #e8e8e8; border-left: 3px solid #333; }

        .info-table { width: 100%; border: 1px solid #999; margin-bottom: 8px; }
        .info-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 11px; }
        .info-table .lbl { background: #f0f0f0; font-weight: 600; font-size: 10px; }
        .info-table .val { font-size: 11px; }

        .device-table { width: 100%; border: 1px solid #999; margin-bottom: 8px; }
        .device-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 11px; text-align: center; }
        .device-table .lbl { background: #f0f0f0; font-weight: 600; font-size: 10px; }

        .two-col { width: 100%; margin-bottom: 6px; table-layout: fixed; }
        .two-col tr { vertical-align: top !important; }
        .two-col > td { vertical-align: top !important; padding: 0 4px 0 0; border: none; width: 50%; }
        .two-col > td:last-child { padding: 0 0 0 4px; }

        .checklist-table { width: 100%; border: 1px solid #999; margin-bottom: 4px; table-layout: fixed; }
        .checklist-table th { background: #f5f5f5; border: 1px solid #ccc; padding: 3px 4px; font-size: 10px; text-align: left; font-weight: 600; }
        .checklist-table td { border: 1px solid #ddd; padding: 2px 4px; font-size: 10px; }
        .checklist-table .col-name { width: 36%; }
        .checklist-table .col-kondisi { width: 20%; text-align: center; }
        .checklist-table .col-ket { width: 44%; }
        .checklist-table tr:nth-child(even) td { background: #fafafa; }
        .battery-detail { background: #f9f9f9; }
        .battery-detail td { border: 1px solid #ddd; padding: 2px 4px; font-size: 9px; }
        .battery-detail .lbl { font-weight: 600; width: 25%; text-align: right; padding-right: 6px; }
        .battery-detail .val { width: 25%; }

        .kondisi-checklist { width: 100%; border: 1px solid #999; margin-bottom: 4px; }
        .kondisi-checklist td { border: 1px solid #ccc; padding: 3px 6px; font-size: 11px; }
        .kondisi-checklist .lbl { background: #f0f0f0; font-weight: 600; width: 20%; font-size: 10px; }
        .kondisi-checklist .val { font-size: 11px; }

        .kondisi-legend { font-size: 11px; margin: 6px 0; padding: 4px 6px; border: 1px solid #ddd; background: #fafafa; }
        .kondisi-legend span { margin-right: 12px; }

        .catatan { font-size: 11px; margin: 8px 0; padding: 5px 6px; border: 1px solid #ddd; }
        .catatan strong { display: block; margin-bottom: 2px; font-size: 11px; }

        .signatures { width: 100%; border-collapse: collapse; margin-top: 25px; page-break-inside: avoid; }
        .signatures td { text-align: center; vertical-align: top; padding: 0 3px; }
        .sig-label { font-size: 11px; font-weight: bold; margin-bottom: 3px; text-decoration: underline; }
        .sig-role { font-size: 9px; color: #555; margin-bottom: 15px; }
        .sig-name { font-size: 10px; margin-top: 3px; }
        .sig-date { font-size: 9px; color: #777; margin-top: 1px; }
        .sig-img { width: 90px; height: 35px; margin: 3px auto; border: none; background: transparent; object-fit: contain; }
        .sig-line { width: 90px; border-bottom: 1px solid #999; margin: 20px auto 3px; }

        .footer { margin-top: 10px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>
<div class="pdf-content">

    {{-- HEADER --}}
    <div class="pdf-section">
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('images/asri.png') }}" style="width: 50px;">
            </td>
            <td class="header-title">
                <h1>FORMULIR PERAWATAN PERANGKAT</h1>
                <p>IT Department &mdash; ASRI</p>
            </td>
            <td style="width: 55px; border: none;"></td>
        </tr>
    </table>
    </div>

    {{-- NO. FORM --}}
    <div class="pdf-section">
    <table class="form-row">
        <tr>
            <td class="form-no">No : {{ $form->nomor_form }}</td>
            <td class="form-date">Tanggal : {{ $form->submitted_at ? $form->submitted_at->format('d/m/Y') : '-' }}</td>
        </tr>
    </table>
    </div>

    {{-- INFORMASI PENGGUNA --}}
    <div class="pdf-section">
    <div class="section-title">Informasi Pengguna</div>
    <table class="info-table">
        <tr>
            <td class="lbl" style="width:16%;">Nama User</td>
            <td style="width:34%;">{{ $form->pengguna->name ?? '-' }}</td>
            <td class="lbl" style="width:16%;">NIK User</td>
            <td style="width:34%;">{{ $form->pengguna->nik ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Site</td>
            <td>{{ $form->pengguna->site_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">No. Telepon</td>
            <td>{{ $form->pengguna->no_telepon ?? '-' }}</td>
            <td class="lbl">Alamat Email</td>
            <td>{{ $form->pengguna->email ?? '-' }}</td>
        </tr>
    </table>
    <div class="section-sub-title">Location Perawatan
        Site : {{ $form->site->site ?? $form->site_location ?? '-' }}, {{ $form->location_detail ?? '-' }}
    </div>
    </div>

    {{-- INFORMASI PERANGKAT --}}
    <div class="-pdfsection">
    <div class="section-title">Informasi Perangkat</div>
    <table class="device-table">
        <tr>
            <td class="lbl" style="width:12%;">Kategori</td>
            <td class="lbl" style="width:10%;">Brand, Tipe</td>
            <td class="lbl" style="width:13%;">Nama Perangkat</td>
            <td class="lbl" style="width:10%;">No. Serial</td>
            <td class="lbl" style="width:10%;">No. Asset</td>
        </tr>
        <tr>
            <td style="width:14%;">{{ $form->asset->kategori ?? '-' }}</td>
            <td style="width:14%;">{{ $form->asset->brand . ', ' . $form->asset->tipe ?? '-' }}</td>
            <td style="width:14%;">{{ $form->asset->nama_perangkat ?? '-' }}</td>
            <td style="width:13%;">{{ $form->asset->no_serial ?? '-' }}</td>
            <td style="width:14%;">{{ $form->asset->no_asset ?? '-' }}</td>
        </tr>
    </table>
    </div>

    {{-- PEMERIKSAAN PERANGKAT --}}
    @php
        $hardwareItems = $form->items->where('category', 'hardware')->sortBy('sort_order');
        $aplikasiItems = $form->items->where('category', 'aplikasi')->sortBy('sort_order');
        $osItems = $form->items->where('category', 'operating_system')->sortBy('sort_order');
    @endphp

    {{-- HARDWARE + OS (left) | APLIKASI (right) --}}
    <div class="pdf-section">
    <table class="two-col">
        <tr>
            {{-- LEFT: HARDWARE + OS --}}
            <td>
                <div class="section-title" style="margin-top:0;">Perawatan Hardware</div>
                <table class="checklist-table">
                    <thead>
                        <tr>
                            <th class="col-name">Name</th>
                            <th class="col-kondisi">Status</th>
                            <th class="col-ket">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hardwareItems as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td class="col-kondisi">
                                    @if($item->status === 'baik') Baik
                                    @elseif($item->status === 'tidak_baik') Tidak Baik
                                    @else - @endif
                                </td>
                                <td>{{ $item->keterangan ?? '' }}</td>
                            </tr>
                            @if(($item->name === 'Battery' || $item->name === 'Battery Report') && ($item->full_charge_capacity || $item->design_capacity))
                                <tr class="battery-detail">
                                    <td colspan="3" style="border-top: none; padding: 1px 4px 3px;">
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td class="lbl">Full Charge Capacity</td>
                                                <td class="val">{{ $item->full_charge_capacity ?? '-' }} mWh</td>
                                                <td class="lbl">Design Capacity</td>
                                                <td class="val">{{ $item->design_capacity ?? '-' }} mWh</td>
                                                <td class="lbl">Battery Health</td>
                                                <td class="val" style="font-weight: bold;">
                                                    @if($item->full_charge_capacity && $item->design_capacity && $item->design_capacity > 0)
                                                        {{ round(($item->full_charge_capacity / $item->design_capacity) * 100) }}%
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td>-</td><td class="col-kondisi">-</td><td>-</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="section-title">Perawatan Operating Sistem</div>
                <table class="checklist-table">
                    <thead>
                        <tr>
                            <th class="col-name">Name</th>
                            <th class="col-kondisi">Status</th>
                            <th class="col-ket">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($osItems as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td class="col-kondisi">
                                    @if($item->status === 'baik') Baik
                                    @elseif($item->status === 'tidak_baik') Tidak Baik
                                    @else - @endif
                                </td>
                                <td>{{ $item->keterangan ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td>-</td><td class="col-kondisi">-</td><td>-</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="section-title">Kondisi Setelah Perawatan</div>
                @php
                    $kondisiOptions = [
                        'good' => ['label' => 'Good', 'color' => '#10b981'],
                        'fair' => ['label' => 'Fair', 'color' => '#3b82f6'],
                        'critical' => ['label' => 'Critical', 'color' => '#f59e0b'],
                        'poor' => ['label' => 'Poor', 'color' => '#ef4444'],
                    ];
                    $selected = $kondisiOptions[$form->kondisi_akhir] ?? null;
                @endphp
                @if($selected)
                    <div style="padding: 6px; border: 1px solid #ccc; font-size: 12px; font-weight: bold; color: {{ $selected['color'] }};">
                        <span style="display:inline-block; width:14px; height:14px; border-radius:50%; background:{{ $selected['color'] }}; vertical-align:middle; margin-right:6px;"></span>
                        {{ $selected['label'] }}
                    </div>
                @else
                    <div style="padding: 6px; border: 1px solid #ccc; font-size: 12px; color: #999;">-</div>
                @endif
            </td>

            {{-- RIGHT: APLIKASI --}}
            <td>
                <div class="section-title" style="margin-top:0;">Perawatan Aplikasi</div>
                <table class="checklist-table">
                    <thead>
                        <tr>
                            <th class="col-name">Name</th>
                            <th class="col-kondisi">Status</th>
                            <th class="col-ket">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aplikasiItems as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td class="col-kondisi">
                                    @if($item->status === 'baik') OK
                                    @elseif($item->status === 'tidak_baik') NOT
                                    @else - @endif
                                </td>
                                <td>{{ $item->keterangan ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td>-</td><td class="col-kondisi">-</td><td>-</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{-- KONDISI LEGEND --}}
                <div class="kondisi-legend">
                    <strong style="font-size:11px;">Kondisi :</strong>
                    <span>V : DONE</span>
                    <span>X : NOT YET</span>
                </div>

                {{-- CATATAN --}}
    @if($form->kondisi_akhir_notes)
        <div style="font-size:10px; margin-top:4px;">
            <strong>Keterangan:</strong> {{ $form->kondisi_akhir_notes }}
        </div>
    @endif
    <div class="catatan" style="margin-top:8px;">
        <strong>Catatan Tambahan :</strong>
        @if($form->barcode_fisik)
            <p class="form-text"> Barcode Fisik : Ada</p>
        @else
            <p class="form-text"> Barcode Fisik : Tidak Ada</p>
        @endif
        {{ $form->notes ?? '-' }}
    </div>
            </td>
        </tr>
    </table>
    </div>



    {{-- KOTA & TANGGAL --}}
    <div style="font-size:11px; margin-top:8px;">Jakarta, {{ $form->submitted_at ? $form->submitted_at->format('d F Y') : '_______________' }}</div>

    {{-- SIGNATURES --}}
    @php
        $diperiksa = $form->approvals->firstWhere('approval_level', 'diperiksa_oleh');
        $diketahui = $form->approvals->firstWhere('approval_level', 'diketahui_oleh');
        $disetujui = $form->approvals->firstWhere('approval_level', 'disetujui_oleh');
    @endphp

    <div class="pdf-section">
    <table class="signatures">
        <tr>
            {{-- PERAWATAN OLEH --}}
            <td style="width:33%;">
                <div class="sig-label">Perawatan Oleh</div>
                <div class="sig-role">Teknisi IT Operation</div>
                @if($diperiksa && $diperiksa->signature_path)
                    <img src="{{ $diperiksa->signature_path }}" class="sig-img" alt="TTD">
                @else
                    <div class="sig-line"></div>
                @endif
                <div class="sig-name">{{ $diperiksa->user->name ?? '_______________' }}</div>
                <div class="sig-date">Tanggal : {{ $diperiksa && $diperiksa->approved_at ? $diperiksa->approved_at->format('d/m/Y') : '___/___/______' }}</div>
            </td>

            {{-- DIKETAHUI --}}
            <td style="width:33%;">
                <div class="sig-label">Diketahui Oleh</div>
                <div class="sig-role">Pengguna Perangkat</div>
                @if($diketahui && $diketahui->signature_path)
                    <img src="{{ $diketahui->signature_path }}" class="sig-img" alt="TTD">
                @else
                    <div class="sig-line"></div>
                @endif
                <div class="sig-name">{{ $diketahui->user->name ?? '_______________' }}</div>
                <div class="sig-date">Tanggal : {{ $diketahui && $diketahui->approved_at ? $diketahui->approved_at->format('d/m/Y') : '___/___/______' }}</div>
            </td>

            {{-- DISETUJUI --}}
            <td style="width:33%;">
                <div class="sig-label">Disetujui Oleh</div>
                <div class="sig-role">Supervisor / Manager IT Operation</div>
                @if($disetujui && $disetujui->signature_path)
                    <img src="{{ $disetujui->signature_path }}" class="sig-img" alt="TTD">
                @else
                    <div class="sig-line"></div>
                @endif
                <div class="sig-name">{{ $disetujui->user->name ?? '_______________' }}</div>
                <div class="sig-date">Tanggal : {{ $disetujui && $disetujui->approved_at ? $disetujui->approved_at->format('d/m/Y') : '___/___/______' }}</div>
            </td>
        </tr>
    </table>
    </div>

    <div class="footer">
        FM/ASRI/ITE/09-00 - Form Perawatan Perangkat &mdash; {{ $form->nomor_form }} &mdash; {{ $form->asset->nama_perangkat ?? '' }}
    </div>

</div>
</body>
</html>

<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component {}; ?>

<div class="max-w-5xl mx-auto px-4 py-6 space-y-4"
    x-data="{ editing: @entangle('editing') }"
    x-on:edit-saved.window="editing = false">

    {{-- SUCCESS / REJECT STATE --}}
    @if($saved)
        <div class="glass-card p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-emerald-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-primary mb-2">Approval Berhasil</h2>
            <p class="text-sm text-muted mb-6">Form {{ $formType === 'pemeriksaan' ? $pemeriksaanForm->nomor_form : $perawatanForm->nomor_form }} telah di-approve.</p>
            <a href="{{ route('dashboard') }}" wire:navigate class="glass-button-primary inline-block">Kembali ke Dashboard</a>
        </div>
    @elseif($rejected)
        <div class="glass-card p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-primary mb-2">Form Dikembalikan</h2>
            <p class="text-sm text-muted mb-6">Form {{ $formType === 'pemeriksaan' ? $pemeriksaanForm->nomor_form : $perawatanForm->nomor_form }} telah dikembalikan ke teknisi untuk revisi.</p>
            <a href="{{ route('dashboard') }}" wire:navigate class="glass-button-primary inline-block">Kembali ke Dashboard</a>
        </div>
    @else
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-primary">Review & Approval</h1>
                <p class="text-sm text-muted mt-1">
                    @if($approvalLevel === 'diketahui_oleh')
                        Approval Level: <span class="font-semibold text-primary">Diketahui Oleh</span>
                    @elseif($approvalLevel === 'disetujui_oleh')
                        Approval Level: <span class="font-semibold text-primary">Disetujui Oleh</span>
                    @else
                        <span class="font-semibold text-primary">Mode Review</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @php $form = $formType === 'pemeriksaan' ? $pemeriksaanForm : $perawatanForm; @endphp
                @if(($canApprove || $canEditAsTeknisi) && !$editing)
                    <button wire:click="toggleEdit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                @elseif($editing)
                    <button wire:click="toggleEdit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                        Batal Edit
                    </button>
                    <button wire:click="saveEdits" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">
                        <span wire:loading.remove wire:target="saveEdits">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="saveEdits">Menyimpan...</span>
                        <span wire:loading.remove wire:target="saveEdits">Simpan Perubahan</span>
                    </button>
                @endif
                <a href="{{ route($formType . '.export-pdf', $form->id) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>
                @if($form->asset_id)
                <a href="{{ route('assets.show', $form->asset_id) }}" wire:navigate
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Lihat Aset
                </a>
                @endif
                <span class="px-3 py-1 rounded-full text-xs font-medium
                    {{ $formType === 'pemeriksaan' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}">
                    {{ $formType === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan' }}
                </span>
            </div>
        </div>

        {{-- FORM SUMMARY --}}
        @php $form = $formType === 'pemeriksaan' ? $pemeriksaanForm : $perawatanForm; @endphp

        {{-- Info Ringkas --}}
        <div class="glass-card p-4">
            <div class="grid grid-cols-2 sm:grid-cols-[2fr_1fr_1fr_1fr] gap-4 text-sm">
                <div class="min-w-0">
                    <span class="text-xs text-muted">No. Form</span>
                    <p class="font-mono font-semibold text-primary truncate" title="{{ $form->nomor_form }}">{{ $form->nomor_form }}</p>
                </div>
                <div class="min-w-0">
                    <span class="text-xs text-muted">Status</span>
                    <p class="font-semibold text-primary truncate" title="{{ ucfirst($form->status) }}">{{ ucfirst($form->status) }}</p>
                </div>
                <div class="min-w-0">
                    <span class="text-xs text-muted">Teknisi</span>
                    <p class="text-primary truncate" title="{{ $form->teknisi->name }}">{{ $form->teknisi->name }}</p>
                </div>
                <div class="min-w-0">
                    <span class="text-xs text-muted">Tanggal</span>
                    <p class="text-primary truncate" title="{{ $form->submitted_at ? $form->submitted_at->format('d M Y H:i') : '-' }}">{{ $form->submitted_at ? $form->submitted_at->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Info Pengguna --}}
        <div class="glass-card p-4">
            <h3 class="text-sm font-semibold text-primary mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Info Pengguna
            </h3>
            <table class="w-full text-sm" style="border-collapse: collapse;">
                <tr>
                    <td class="px-3 py-2 font-semibold text-xs w-[16%]" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">Nama - [ NIK ]</td>
                    <td class="px-3 py-2 text-primary w-[34%]" style="border: 1px solid var(--color-border);">{{ $form->pengguna->name ?? '-' }} - [ {{ $form->pengguna->nik ?? '-' }} ]</td>
                    <td class="px-3 py-2 font-semibold text-xs w-[16%]" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">Position</td>
                    <td class="px-3 py-2 text-primary w-[34%]" style="border: 1px solid var(--color-border);">{{ $form->pengguna->position?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2 font-semibold text-xs" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">Alamat Email</td>
                    <td class="px-3 py-2 text-primary" style="border: 1px solid var(--color-border);">{{ $form->pengguna->email ?? '-' }}</td>
                    <td class="px-3 py-2 font-semibold text-xs" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">SO</td>
                    <td class="px-3 py-2 text-primary" style="border: 1px solid var(--color-border);">{{ $form->pengguna->divisi?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2 font-semibold text-xs" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">No. Telepon</td>
                    <td class="px-3 py-2 text-primary" style="border: 1px solid var(--color-border);">{{ $form->pengguna->no_telepon ?? '-' }}</td>
                    <td class="px-3 py-2 font-semibold text-xs" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">Unit Site</td>
                    <td class="px-3 py-2 text-primary" style="border: 1px solid var(--color-border);">{{ $form->pengguna->site_name ?? '-' }}</td>
                </tr>
            </table>
            <div class="text-xs text-muted mt-2">
                @if($formType === 'pemeriksaan')
                    Location Pemeriksaan: Site: {{ $form->site->site ?? $form->site_location ?? '-' }}, on {{ $form->location_detail ?? '-' }}
                @else
                    Location Perawatan: Site: {{ $form->site->site ?? $form->site_location ?? '-' }}, {{ $form->location_detail ?? '-' }}
                @endif
            </div>
        </div>

        {{-- Info Perangkat --}}
        <div class="glass-card p-4">
            <h3 class="text-sm font-semibold text-primary mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Info Perangkat
            </h3>
            <table class="w-full text-sm" style="border-collapse: collapse; text-align: center;">
                <tr style="background: var(--color-bg-secondary);">
                    <th class="px-3 py-2 font-semibold text-xs w-[20%]" style="border: 1px solid var(--color-border);">Kategori</th>
                    <th class="px-3 py-2 font-semibold text-xs w-[20%]" style="border: 1px solid var(--color-border);">Brand, Tipe</th>
                    <th class="px-3 py-2 font-semibold text-xs w-[20%]" style="border: 1px solid var(--color-border);">Hostname</th>
                    <th class="px-3 py-2 font-semibold text-xs w-[20%]" style="border: 1px solid var(--color-border);">No. Serial</th>
                    <th class="px-3 py-2 font-semibold text-xs w-[20%]" style="border: 1px solid var(--color-border);">No. Asset</th>
                </tr>
                <tr>
                    <td class="px-3 py-2 text-primary" style="border: 1px solid var(--color-border);">{{ $form->asset->kategori ?? '-' }}</td>
                    <td class="px-3 py-2 text-primary" style="border: 1px solid var(--color-border);">{{ $form->asset->brand ?? '-' }}, {{ $form->asset->tipe ?? '-' }}</td>
                    <td class="px-3 py-2 text-primary" style="border: 1px solid var(--color-border);">{{ $form->asset->nama_perangkat ?? '-' }}</td>
                    <td class="px-3 py-2 font-mono text-primary" style="border: 1px solid var(--color-border);">{{ $form->asset->no_serial ?? '-' }}</td>
                    <td class="px-3 py-2 font-mono text-primary" style="border: 1px solid var(--color-border);">{{ $form->asset->no_asset ?? '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- Kondisi --}}
        @if($formType === 'pemeriksaan')
            <div class="glass-card p-4">
                <h3 class="text-sm font-semibold text-primary mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Kondisi
                </h3>
                @if($editing)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <label class="text-xs text-muted">Kondisi Perangkat</label>
                            <select wire:model="editKondisi"
                                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                <option value="">Pilih Kondisi</option>
                                <option value="baru">Baru</option>
                                <option value="lama">Lama</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                                <option value="critical">Critical</option>
                                <option value="poor">Poor</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-muted">Keterangan</label>
                            <input wire:model="editKondisiKeterangan" type="text"
                                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                                placeholder="Keterangan kondisi..." />
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-xs text-muted">Kondisi Perangkat</span>
                            <p class="{{ $this->getStatusColor($form->kondisi ?? '') }}">{{ $this->getStatusLabel($form->kondisi ?? '') }}</p>
                        </div>
                        @if($form->kondisi_keterangan)
                            <div>
                                <span class="text-xs text-muted">Keterangan</span>
                                <p class="text-primary">{{ $form->kondisi_keterangan }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @else
            <div class="glass-card p-4">
                <h3 class="text-sm font-semibold text-primary mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Kondisi Setelah Perawatan :
                </h3>
                @if($editing)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <label class="text-xs text-muted">Kondisi Akhir</label>
                            <select wire:model="editKondisi"
                                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                <option value="">Pilih Kondisi</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                                <option value="critical">Critical</option>
                                <option value="poor">Poor</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-muted">Keterangan</label>
                            <input wire:model="editKondisiKeterangan" type="text"
                                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                                placeholder="Keterangan kondisi..." />
                        </div>
                    </div>
                @else
                    <div class="text-sm">
                        <span class="text-xs text-muted">Catatan :</span>
                        @php
                            $kondisiColor = match ($form->kondisi_akhir ?? '') {
                                'good' => '#10b981',
                                'fair' => '#3b82f6',
                                'critical' => '#f59e0b',
                                'poor' => '#ef4444',
                                default => null,
                            };
                        @endphp
                        @if($kondisiColor)
                            <div class="mt-1 inline-flex items-center gap-1.5 px-1.5 py-0.5 border rounded text-sm font-bold"
                                style="border-color: {{ $kondisiColor }}; color: {{ $kondisiColor }};">
                                <span class="inline-block w-3.5 h-3.5 rounded-full" style="background: {{ $kondisiColor }};"></span>
                                {{ $this->getStatusLabel($form->kondisi_akhir ?? '') }}
                            </div>
                        @else
                            <div class="mt-1 inline-flex items-center px-1.5 py-0.5 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-400">-</div>
                        @endif
                        @if($form->kondisi_akhir_notes)
                            <p class="mt-2 text-xs text-muted"><strong>Keterangan:</strong> <span class="text-primary">{{ $form->kondisi_akhir_notes }}</span></p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- Checklist Items (two-col, like PDF) --}}
        @php
            $hardwareItems = $form->items->where('category', 'hardware')->sortBy('sort_order');
            $aplikasiItems = $form->items->where('category', 'aplikasi')->sortBy('sort_order');
            $osItems = $form->items->where('category', 'operating_system')->sortBy('sort_order');
        @endphp

        @if($hardwareItems->count() || $aplikasiItems->count() || $osItems->count())
            <div class="glass-card p-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    {{-- LEFT: HARDWARE --}}
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-primary mb-3">{{ $formType === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan' }} Hardware</h3>
                        <table class="w-full text-sm" style="border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--color-bg-secondary);">
                                    <th class="px-3 py-2 text-left text-xs font-semibold w-[36%]" style="border: 1px solid var(--color-border);">Name</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold w-[20%]" style="border: 1px solid var(--color-border);">{{ $formType === 'pemeriksaan' ? 'Kondisi' : 'Status' }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold w-[44%]" style="border: 1px solid var(--color-border);">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hardwareItems as $item)
                                    @php
                                        $editIndex = collect($editItems)->search(fn($ei) => $ei['id'] === $item->id);
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-primary" style="border: 1px solid var(--color-border);">{{ $item->name }}</td>
                                        <td class="px-3 py-2 text-center" style="border: 1px solid var(--color-border);">
                                            @if($editing && $editIndex !== false)
                                                <select wire:model="editItems.{{ $editIndex }}.status"
                                                    class="px-2 py-1 rounded text-xs transition-colors duration-200"
                                                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                                    <option value="">Pilih Status</option>
                                                    <option value="baik">Baik</option>
                                                    <option value="tidak_baik">Tidak Baik</option>
                                                    <option value="good">Good</option>
                                                    <option value="fair">Fair</option>
                                                    <option value="critical">Critical</option>
                                                    <option value="poor">Poor</option>
                                                    <option value="baru">Baru</option>
                                                    <option value="lama">Lama</option>
                                                </select>
                                            @else
                                                @if($item->status)
                                                    <span class="text-xs font-medium {{ $this->getStatusColor($item->status) }}">{{ $this->getStatusLabel($item->status) }}</span>
                                                @else
                                                    <span class="text-xs text-muted">-</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-xs text-muted" style="border: 1px solid var(--color-border);">
                                            @if($editing && $editIndex !== false)
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @if($formType === 'pemeriksaan' && $item->value !== null)
                                                        <input wire:model="editItems.{{ $editIndex }}.value" type="text"
                                                            placeholder="Nilai"
                                                            class="w-20 px-2 py-1 rounded text-xs transition-colors duration-200"
                                                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                                                    @endif
                                                    <input wire:model="editItems.{{ $editIndex }}.keterangan" type="text"
                                                        placeholder="Keterangan"
                                                        class="flex-1 min-w-[100px] px-2 py-1 rounded text-xs transition-colors duration-200"
                                                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                                                </div>
                                            @else
                                                @if($item->value)
                                                    <span class="text-xs text-muted">{{ $item->value }}</span>
                                                @endif
                                                {{ $item->keterangan ?? '-' }}
                                                @if(($item->name === 'Battery' || $item->name === 'Battery Report') && ($item->full_charge_capacity || $item->design_capacity))
                                                    @if($item->full_charge_capacity && $item->design_capacity && $item->design_capacity > 0)
                                                        <strong>{{ round(($item->full_charge_capacity / $item->design_capacity) * 100) }} %</strong>
                                                    @else
                                                        <strong>-</strong>
                                                    @endif
                                                    [FCC {{ $item->full_charge_capacity ?? '-' }} / DC {{ $item->design_capacity ?? '-' }}]
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- RIGHT: APLIKASI + OS --}}
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-primary mb-3">{{ $formType === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan' }} Aplikasi</h3>
                        <table class="w-full text-sm" style="border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--color-bg-secondary);">
                                    <th class="px-3 py-2 text-left text-xs font-semibold w-[36%]" style="border: 1px solid var(--color-border);">Name</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold w-[20%]" style="border: 1px solid var(--color-border);">Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold w-[44%]" style="border: 1px solid var(--color-border);">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aplikasiItems as $item)
                                    @php
                                        $editIndex = collect($editItems)->search(fn($ei) => $ei['id'] === $item->id);
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-primary" style="border: 1px solid var(--color-border);">{{ $item->name }}</td>
                                        <td class="px-3 py-2 text-center" style="border: 1px solid var(--color-border);">
                                            @if($editing && $editIndex !== false)
                                                <select wire:model="editItems.{{ $editIndex }}.status"
                                                    class="px-2 py-1 rounded text-xs transition-colors duration-200"
                                                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                                    <option value="">Pilih Status</option>
                                                    <option value="baik">Baik</option>
                                                    <option value="tidak_baik">Tidak Baik</option>
                                                    <option value="good">Good</option>
                                                    <option value="fair">Fair</option>
                                                    <option value="critical">Critical</option>
                                                    <option value="poor">Poor</option>
                                                    <option value="baru">Baru</option>
                                                    <option value="lama">Lama</option>
                                                </select>
                                            @else
                                                @if($item->status)
                                                    <span class="text-xs font-medium {{ $this->getStatusColor($item->status) }}">{{ $this->getStatusLabel($item->status) }}</span>
                                                @else
                                                    <span class="text-xs text-muted">-</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-xs text-muted" style="border: 1px solid var(--color-border);">
                                            @if($editing && $editIndex !== false)
                                                <input wire:model="editItems.{{ $editIndex }}.keterangan" type="text"
                                                    placeholder="Keterangan"
                                                    class="w-full px-2 py-1 rounded text-xs transition-colors duration-200"
                                                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                                            @else
                                                {{ $item->keterangan ?? '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <h3 class="text-sm font-semibold text-primary mb-3 mt-5">{{ $formType === 'pemeriksaan' ? 'Operating System' : 'Perawatan Operating Sistem' }}</h3>
                        <table class="w-full text-sm" style="border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--color-bg-secondary);">
                                    <th class="px-3 py-2 text-left text-xs font-semibold w-[36%]" style="border: 1px solid var(--color-border);">Name</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold w-[20%]" style="border: 1px solid var(--color-border);">{{ $formType === 'pemeriksaan' ? 'Kondisi' : 'Status' }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold w-[44%]" style="border: 1px solid var(--color-border);">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($osItems as $item)
                                    @php
                                        $editIndex = collect($editItems)->search(fn($ei) => $ei['id'] === $item->id);
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-primary" style="border: 1px solid var(--color-border);">{{ $item->name }}</td>
                                        <td class="px-3 py-2 text-center" style="border: 1px solid var(--color-border);">
                                            @if($editing && $editIndex !== false)
                                                <select wire:model="editItems.{{ $editIndex }}.status"
                                                    class="px-2 py-1 rounded text-xs transition-colors duration-200"
                                                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                                    <option value="">Pilih Status</option>
                                                    <option value="baik">Baik</option>
                                                    <option value="tidak_baik">Tidak Baik</option>
                                                    <option value="good">Good</option>
                                                    <option value="fair">Fair</option>
                                                    <option value="critical">Critical</option>
                                                    <option value="poor">Poor</option>
                                                    <option value="baru">Baru</option>
                                                    <option value="lama">Lama</option>
                                                </select>
                                            @else
                                                @if($item->status)
                                                    <span class="text-xs font-medium {{ $this->getStatusColor($item->status) }}">{{ $this->getStatusLabel($item->status) }}</span>
                                                @else
                                                    <span class="text-xs text-muted">-</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-xs text-muted" style="border: 1px solid var(--color-border);">
                                            @if($editing && $editIndex !== false)
                                                <input wire:model="editItems.{{ $editIndex }}.keterangan" type="text"
                                                    placeholder="Keterangan"
                                                    class="w-full px-2 py-1 rounded text-xs transition-colors duration-200"
                                                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                                            @else
                                                {{ $item->keterangan ?? '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Notes --}}
        <div class="glass-card p-4">
            <h3 class="text-sm font-semibold text-primary mb-2">Catatan</h3>
            @if($formType === 'perawatan')
                <p class="text-sm text-secondary mb-2"><strong>Barcode Fisik :</strong> {{ $form->barcode_fisik ? 'Ada' : 'Tidak Ada' }}</p>
            @endif
            @if($editing)
                <textarea wire:model="editNotes" rows="3"
                    class="w-full px-3 py-2 rounded-lg text-sm resize-none transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                    placeholder="Tambahkan catatan..."></textarea>
            @else
                <p class="text-sm text-secondary whitespace-pre-wrap">{{ $form->notes ?? '*) : diisi untuk perangkat lama' }}</p>
            @endif
        </div>

        {{-- Previous Approvals --}}
        @if($form->approvals->count() > 0)
            <div class="glass-card p-4">
                <h3 class="text-sm font-semibold text-primary mb-3">Riwayat Approval</h3>
                <div class="space-y-3">
                    @foreach($form->approvals->sortBy('created_at') as $approval)
                        <div class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--color-bg-secondary);">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                {{ $approval->status === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : ($approval->status === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                                @if($approval->status === 'approved')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($approval->status === 'rejected')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-primary">
                                    {{ $approval->approval_level === 'diperiksa_oleh' ? 'Diperiksa Oleh' : ($approval->approval_level === 'diketahui_oleh' ? 'Diketahui Oleh' : 'Disetujui Oleh') }}
                                </div>
                                <div class="text-xs text-muted">
                                    {{ $approval->custom_signer_name ?: ($approval->user->name ?? '-') }} &middot; {{ $approval->approved_at ? $approval->approved_at->format('d M Y H:i') : '-' }}
                                    &middot; <span class="{{ $approval->status === 'approved' ? 'text-emerald-400' : ($approval->status === 'rejected' ? 'text-red-400' : 'text-yellow-400') }}">{{ ucfirst($approval->status) }}</span>
                                </div>
                                @if($approval->catatan)
                                    <div class="text-xs text-secondary mt-1">{{ $approval->catatan }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- APPROVE / REJECT --}}
        @if($canApprove)
            <div class="glass-card p-5 space-y-4 border-2" style="border-color: var(--color-primary, rgba(59, 130, 246, 0.3));">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-base font-bold text-primary">Edit, Submit & Tanda Tangan</h3>
                </div>
                <p class="text-xs text-muted -mt-2">Edit data jika diperlukan, lalu tanda tangani untuk approve.</p>

                <h3 class="text-sm font-semibold text-primary">Catatan Approval (opsional)</h3>
                <textarea wire:model.live="catatan" rows="2"
                    class="glass-input w-full rounded-lg px-3 py-2 text-sm resize-none"
                    placeholder="Tambahkan catatan approval..."></textarea>

                {{-- Signer Mode --}}
                @if($approvalLevel === 'diketahui_oleh')
                    <div>
                        <h3 class="text-sm font-semibold text-primary mb-2">Penandatangan</h3>
                        <div class="flex rounded-lg overflow-hidden border" style="border-color: var(--color-border);">
                            <button wire:click="setSignerMode('me')" type="button"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition-colors duration-200"
                                style="{{ $signerMode === 'me' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);' }}">
                                Me ({{ Auth::user()->name }})
                            </button>
                            <button wire:click="setSignerMode('custom')" type="button"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition-colors duration-200"
                                style="{{ $signerMode === 'custom' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);' }}">
                                Masukan Nama Diketahui
                            </button>
                        </div>
                        @if($signerMode === 'custom')
                            <div class="mt-2 relative" x-data="{ open: @entangle('showSignerDropdown') }">
                                <div class="flex gap-2">
                                    <div class="flex-1 relative">
                                        <input wire:model.live="customSignerName" type="text"
                                            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                                            placeholder="Cari nama, NIK, atau email..."
                                            wire:input="searchSigner"
                                            autocomplete="off" />
                                        @if($customSignerName)
                                            <button wire:click="clearSigner" type="button"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-muted hover:text-primary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if($showSignerDropdown && count($signerResults) > 0)
                                    <div class="absolute z-10 w-full mt-1 rounded-lg border shadow-lg max-h-48 overflow-y-auto"
                                        style="background: var(--color-glass-bg); border-color: var(--color-border);">
                                        @foreach($signerResults as $result)
                                            <button wire:click="selectSigner({{ json_encode($result) }})" type="button"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-primary/10 transition-colors duration-150 flex items-center gap-2"
                                                style="border-bottom: 1px solid var(--color-border);">
                                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold shrink-0"
                                                    style="background: var(--color-primary); color: var(--color-button-text);">
                                                    {{ strtoupper(substr($result['name'], 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-primary truncate">{{ $result['name'] }}</p>
                                                    <p class="text-xs text-muted truncate">{{ $result['nik'] ?? '' }} {{ $result['email'] ?? '' }}</p>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                @if($customSignerName)
                                    <div class="mt-2 flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs" style="background: var(--color-bg-secondary);">
                                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-primary font-medium">{{ $customSignerName }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($approvalLevel === 'disetujui_oleh')
                    <div>
                        <h3 class="text-sm font-semibold text-primary mb-2">Penandatangan</h3>
                        <div class="p-3 rounded-lg" style="background: var(--color-bg-secondary);">
                            <p class="text-xs text-muted mb-1">Disetujui Oleh (Supervisor IT / Manager IT):</p>
                            @php $approvers = $this->getDisetujuiApprovers(); @endphp
                            @forelse($approvers as $approver)
                                <div class="flex items-center gap-2 py-1">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold"
                                        style="background: var(--color-primary); color: var(--color-button-text);">
                                        {{ strtoupper(substr($approver->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-primary">{{ $approver->name }}</p>
                                        <p class="text-xs text-muted">{{ $approver->email }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-red-400">Tidak ada user dengan role Supervisor IT / Manager IT</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- Signature Pad --}}
                <h3 class="text-sm font-semibold text-primary">Tanda Tangan</h3>
                <div x-data="{
                    @if($userSignature)
                    mode: 'paste',
                    @else
                    mode: 'draw',
                    @endif
                    userSignature: '{{ $userSignature }}',
                    uploadedPreview: null,
                    uploadedFile: null,
                    uploadedFileSize: 0,
                    maxFileSize: 1048576,

                    handleUpload(e) {
                        const file = e.target.files[0];
                        if (!file) return;
                        if (!file.type.match('image/png')) {
                            alert('Hanya file PNG yang diperbolehkan');
                            e.target.value = '';
                            return;
                        }
                        if (file.size > this.maxFileSize) {
                            alert('Ukuran file melebihi 1 MB. Silakan kompres file terlebih dahulu.');
                            e.target.value = '';
                            return;
                        }
                        this.uploadedFile = file;
                        this.uploadedFileSize = file.size;
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            const img = new Image();
                            img.onload = () => {
                                const canvas = document.createElement('canvas');
                                const maxWidth = 800, maxHeight = 400;
                                let w = img.width, h = img.height;
                                if (w > maxWidth) { h = (h * maxWidth) / w; w = maxWidth; }
                                if (h > maxHeight) { w = (w * maxHeight) / h; h = maxHeight; }
                                canvas.width = w;
                                canvas.height = h;
                                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                                this.uploadedPreview = canvas.toDataURL('image/png');
                            };
                            img.src = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                    },

                    clearUpload() {
                        this.uploadedPreview = null;
                        this.uploadedFile = null;
                        this.uploadedFileSize = 0;
                        this.$refs.uploadInput.value = '';
                    },

                    saveUpload() {
                        if (!this.uploadedPreview) {
                            alert('Harap unggah file tanda tangan terlebih dahulu');
                            return;
                        }
                        $wire.approveForm(this.uploadedPreview);
                    },

                    canvas: null, ctx: null, drawing: false, lastX: 0, lastY: 0, canvasReady: false,
                    init() { this.canvas = this.$refs.signatureCanvas; this.ctx = this.canvas.getContext('2d'); this.resize(); this.canvasReady = true; window.addEventListener('resize', () => this.resize()); },
                    resize() { if (!this.canvas || !this.canvas.parentElement) return; const rect = this.canvas.parentElement.getBoundingClientRect(); if (rect.width === 0) return; this.canvas.width = rect.width; this.canvas.height = 200; this.ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--color-text-primary').trim(); this.ctx.lineWidth = 2; this.ctx.lineCap = 'round'; this.ctx.lineJoin = 'round'; },
                    getCoords(e) { const rect = this.canvas.getBoundingClientRect(); const touch = e.touches ? e.touches[0] : e; return { x: (touch.clientX - rect.left) * (this.canvas.width / rect.width), y: (touch.clientY - rect.top) * (this.canvas.height / rect.height) }; },
                    startDraw(e) { if (!this.canvasReady) this.init(); if (this.canvas.width === 0) this.resize(); this.drawing = true; const coords = this.getCoords(e); this.lastX = coords.x; this.lastY = coords.y; },
                    draw(e) { if (!this.drawing) return; const coords = this.getCoords(e); this.ctx.beginPath(); this.ctx.moveTo(this.lastX, this.lastY); this.ctx.lineTo(coords.x, coords.y); this.ctx.stroke(); this.lastX = coords.x; this.lastY = coords.y; },
                    stopDraw() { this.drawing = false; },
                    clear() { if (!this.ctx) return; this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height); },
                    isEmpty() { if (!this.ctx) return true; const pixel = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data; return !pixel.some(v => v !== 0); },
                    save() { if (this.isEmpty()) { alert('Harap tanda tangan terlebih dahulu'); return; } $wire.approveForm(this.canvas.toDataURL('image/png')); }
                }" class="space-y-3">
                    {{-- Tab Mode --}}
                    <div class="flex rounded-lg overflow-hidden border" style="border-color: var(--color-border);">
                        @if($userSignature)
                        <button @click="mode = 'paste'" type="button"
                            class="flex-1 px-3 py-1.5 text-xs font-medium text-center transition-colors duration-200"
                            :style="mode === 'paste' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);'">
                            Saya
                        </button>
                        @endif
                        <button @click="mode = 'draw'" type="button"
                            class="flex-1 px-3 py-1.5 text-xs font-medium text-center transition-colors duration-200"
                            :style="mode === 'draw' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);'">
                            Gambar
                        </button>
                        <button @click="mode = 'upload'" type="button"
                            class="flex-1 px-3 py-1.5 text-xs font-medium text-center transition-colors duration-200"
                            :style="mode === 'upload' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);'">
                            Upload PNG
                        </button>
                    </div>

                    {{-- Paste Mode --}}
                    @if($userSignature)
                    <div x-show="mode === 'paste'" x-cloak>
                        <div class="space-y-3">
                            <p class="text-xs text-muted">Gunakan tanda tangan yang tersimpan di profil Anda.</p>
                            <div class="rounded-lg overflow-hidden border-2 flex items-center justify-center p-4" style="border-color: var(--color-border); background: var(--color-bg-secondary); min-height: 160px;">
                                <img :src="userSignature" alt="Tanda Tangan Profil" class="max-h-32 object-contain">
                            </div>
                            <button @click="$wire.approveForm(userSignature)" type="button"
                                class="w-full glass-button-primary text-sm">
                                Gunakan Tanda Tangan Ini
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- Draw Mode --}}
                    <div x-show="mode === 'draw'" x-cloak>
                        <div class="rounded-lg overflow-hidden border-2" style="border-color: var(--color-border);">
                            <canvas x-ref="signatureCanvas" class="w-full cursor-crosshair touch-none"
                                style="background: var(--color-bg-secondary); height: 200px;"
                                @mousedown="startDraw($event)" @mousemove="draw($event)" @mouseup="stopDraw()" @mouseleave="stopDraw()"
                                @touchstart.prevent="startDraw($event)" @touchmove.prevent="draw($event)" @touchend="stopDraw()">
                            </canvas>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button @click="clear()" type="button" class="glass-button-secondary text-sm flex-1">Hapus</button>
                            <button @click="save()" type="button" class="glass-button-primary text-sm flex-1">Submit & Tanda Tangan</button>
                        </div>
                    </div>

                    {{-- Upload Mode --}}
                    <div x-show="mode === 'upload'" x-cloak>
                        <template x-if="!uploadedPreview">
                            <div class="space-y-2">
                                <label class="flex flex-col items-center justify-center w-full h-40 rounded-lg border-2 border-dashed cursor-pointer transition-colors duration-200"
                                    style="border-color: var(--color-border); background: var(--color-bg-secondary);"
                                    @dragover.prevent="$el.style.borderColor = 'var(--color-primary)'"
                                    @dragleave.prevent="$el.style.borderColor = 'var(--color-border)'"
                                    @drop.prevent="$el.style.borderColor = 'var(--color-border)'; handleUpload({target: {files: $event.dataTransfer.files}})">
                                    <svg class="w-8 h-8 mb-2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs text-muted">Klik atau seret file PNG ke sini</span>
                                    <input x-ref="uploadInput" type="file" accept="image/png" class="hidden" @change="handleUpload($event)">
                                </label>
                                <p class="text-xs text-muted text-center">Format: PNG | Maksimal: 1 MB</p>
                            </div>
                        </template>
                        <template x-if="uploadedPreview">
                            <div class="space-y-3">
                                <div class="rounded-lg overflow-hidden border-2 flex items-center justify-center p-4" style="border-color: var(--color-border); background: var(--color-bg-secondary); min-height: 160px;">
                                    <img :src="uploadedPreview" alt="Preview Tanda Tangan" class="max-h-32 object-contain">
                                </div>
                                <div class="flex items-center justify-between text-xs px-1">
                                    <span class="text-muted">Ukuran: <span class="font-semibold text-primary" x-text="(uploadedFileSize / 1024).toFixed(1) + ' KB'"></span></span>
                                    <span class="text-muted">Maksimal: 1 MB</span>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="clearUpload()" type="button" class="glass-button-secondary text-sm flex-1">Hapus</button>
                                    <button @click="saveUpload()" type="button" class="glass-button-primary text-sm flex-1">Submit & Tanda Tangan</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Reject --}}
                <div class="pt-2 border-t" style="border-color: var(--color-border);">
                    <button wire:click="toggleRejectModal" type="button"
                        class="w-full px-4 py-2 rounded-lg font-medium text-sm bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-all duration-200 border border-red-500/20">
                        Reject & Kembalikan ke Teknisi
                    </button>
                </div>
            </div>
        @endif

        {{-- REJECT MODAL --}}
        @if($showRejectModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
                x-data x-on:keydown.escape.window="$wire.set('showRejectModal', false)">
                <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.set('showRejectModal', false)">
                    <h3 class="text-lg font-bold text-primary">Reject Form</h3>
                    <p class="text-sm text-muted">Form akan dikembalikan ke teknisi dengan status "Revisi".</p>
                    <div>
                        <label class="text-xs text-muted">Alasan Reject <span class="text-red-400">*</span></label>
                        <textarea wire:model.live="rejectReason" rows="3"
                            class="glass-input w-full rounded-lg px-3 py-2 text-sm resize-none mt-1"
                            placeholder="Jelaskan alasan reject..."></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="toggleRejectModal" type="button" class="glass-button-secondary text-sm flex-1">Batal</button>
                        <button wire:click="rejectForm" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">Reject</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component {}; ?>

<div class="max-w-5xl mx-auto px-4 py-6 space-y-4">
    {{-- Asset Info Header --}}
    <div class="glass-card p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-primary">{{ $asset->nama_perangkat }}</h1>
                <p class="text-sm text-muted mt-1">{{ $asset->brand }} &middot; {{ $asset->tipe }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if (auth()->user()?->hasRole(['admin', 'teknisi']))
                    <a href="{{ route('assets.edit', $asset->id) }}" wire:navigate
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium transition-colors duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('Edit') }}
                    </a>
                @endif
                <span class="px-3 py-1 rounded-full text-xs font-medium
                    {{ $asset->status === 'active' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-gray-500/15 text-gray-400' }}">
                    {{ ucfirst($asset->status ?? 'Active') }}
                </span>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-4 text-sm">
            <div>
                <span class="text-xs text-muted">Kategori</span>
                <p class="text-primary font-medium">{{ $asset->kategori }}</p>
            </div>
            <div>
                <span class="text-xs text-muted">No. Asset</span>
                <p class="font-mono text-primary">{{ $asset->no_asset }}</p>
            </div>
            <div>
                <span class="text-xs text-muted">No. Serial</span>
                <p class="font-mono text-primary">{{ $asset->no_serial ?? '-' }}</p>
            </div>
            <div>
                <span class="text-xs text-muted">Total Pemeriksaan</span>
                <p class="text-primary font-bold">{{ $asset->pemeriksaan->count() }}</p>
            </div>
            <div>
                <span class="text-xs text-muted">Total Perawatan</span>
                <p class="text-primary font-bold">{{ $asset->perawatan->count() }}</p>
            </div>
        </div>
        @if($asset->barcode_svg)
            <div class="mt-4">
                <div class="bg-white rounded-lg" style="padding: 5px;">
                    <div style="overflow: hidden;">
                        {!! str_replace('<svg ', '<svg style="width:100%;" ', $asset->barcode_svg) !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Timeline --}}
    <div class="glass-card p-6">
        <h2 class="text-lg font-bold text-primary mb-6 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Timeline Histori
        </h2>

        @if(count($timeline) === 0)
            <div class="text-center py-12">
                <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-muted">Belum ada riwayat form untuk aset ini</p>
            </div>
        @else
            <div class="relative">
                {{-- Vertical Line --}}
                <div class="absolute left-5 top-0 bottom-0 w-0.5" style="background: var(--color-border);"></div>

                <div class="space-y-6">
                    @foreach($timeline as $index => $event)
                        <div class="relative flex gap-4">
                            {{-- Dot --}}
                            <div class="relative z-10 w-10 h-10 rounded-full flex items-center justify-center shrink-0
                                {{ $event['type'] === 'pemeriksaan' ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400' }}"
                                style="border: 2px solid var(--color-bg-primary);">
                                @if($event['type'] === 'pemeriksaan')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 glass-card p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide
                                                {{ $event['type'] === 'pemeriksaan' ? 'bg-blue-500/15 text-blue-400' : 'bg-purple-500/15 text-purple-400' }}">
                                                {{ $event['type'] === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan' }}
                                            </span>
                                            <span class="font-mono text-sm font-bold text-primary">{{ $event['nomor_form'] }}</span>
                                        </div>
                                        <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                            <div>
                                                <span class="text-muted">Teknisi</span>
                                                <p class="text-primary">{{ $event['teknisi'] }}</p>
                                            </div>
                                            <div>
                                                <span class="text-muted">Pengguna</span>
                                                <p class="text-primary">{{ $event['pengguna'] }}</p>
                                            </div>
                                            <div>
                                                <span class="text-muted">Tanggal</span>
                                                <p class="text-primary">{{ $event['date'] ? $event['date']->format('d M Y H:i') : '-' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-muted">Kondisi</span>
                                                <p class="{{ $this->getKondisiColor($event['kondisi']) }}">{{ $this->getKondisiLabel($event['kondisi']) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                <span class="px-2 py-1 rounded-full text-[10px] font-semibold {{ $this->getStatusColor($event['status']) }}">
                                    {{ ucfirst($event['status']) }}
                                </span>
                                <a href="{{ route($event['type'] . '.export-pdf', $event['id']) }}" target="_blank"
                                    class="text-[10px] text-emerald-400 hover:underline ml-2">PDF</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary">Data Assets</h1>
            <p class="text-sm text-muted mt-1">Daftar seluruh perangkat yang terdaftar</p>
        </div>
        <div class="text-sm text-muted">
            {{ $assets->total() }} aset
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="glass-card p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari nama, no. asset, brand, tipe..."
                        class="w-full pl-10 pr-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                </div>
            </div>
            <div class="flex gap-2">
                <select wire:model.live="filterKategori"
                    class="px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">Semua Kategori</option>
                    @foreach ($this->getKategoriList() as $kategori)
                        <option value="{{ $kategori }}">{{ $kategori }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus"
                    class="px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">Semua Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Assets Grid --}}
    @if ($assets->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($assets as $asset)
                <div class="glass-card p-5 transition-all duration-200 hover:scale-[1.01]">
                    <a href="{{ route('assets.show', $asset->id) }}" wire:navigate
                        class="block" style="text-decoration: none;">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-primary truncate">{{ $asset->nama_perangkat }}</h3>
                            <p class="text-sm text-secondary mt-0.5">{{ $asset->brand }} &middot; {{ $asset->tipe }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 ml-2 px-2 py-0.5 rounded-full text-xs font-medium
                            {{ ($asset->status ?? 'active') === 'active'
                                ? 'bg-emerald-500/15 text-emerald-400'
                                : (($asset->status ?? '') === 'maintenance'
                                    ? 'bg-amber-500/15 text-amber-400'
                                    : 'bg-gray-500/15 text-gray-400') }}">
                            {{ ucfirst($asset->status ?? 'Active') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-muted">Kategori</span>
                            <p class="text-primary font-medium">{{ $asset->kategori }}</p>
                        </div>
                        <div>
                            <span class="text-muted">No. Asset</span>
                            <p class="font-mono text-primary">{{ $asset->no_asset ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-muted">No. Serial</span>
                            <p class="font-mono text-primary">{{ $asset->no_serial ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-muted">Total Form</span>
                            <p class="text-primary font-medium">
                                {{ $asset->pemeriksaan_count + $asset->perawatan_count }}</p>
                        </div>
                    </div>
                    @if ($asset->barcode_svg)
                        <div class="mt-3">
                            <div class="bg-white rounded-lg" style="padding: 5px;">
                                <div style="overflow: hidden;">
                                    {!! str_replace('<svg ', '<svg style="width:100%;" ', $asset->barcode_svg) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </a>
                    @if (auth()->user()?->hasRole(['admin', 'teknisi']))
                        <div class="mt-3 pt-3 border-t"
                            style="border-color: var(--color-border); display: flex; justify-content: flex-end;">
                            <a href="{{ route('assets.edit', $asset->id) }}" wire:navigate
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                                style="background: var(--color-primary); color: var(--color-button-text);">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                {{ __('Edit') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $assets->links() }}
        </div>
    @else
        <div class="glass-card p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <p class="mt-3 text-muted">Tidak ada aset ditemukan</p>
        </div>
    @endif
</div>

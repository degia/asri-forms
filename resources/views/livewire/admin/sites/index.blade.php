<div class="space-y-6"
    x-data x-on:site-deleted.window="$wire.$refresh()" x-on:site-created.window="window.location = '{{ route('admin.sites.index') }}'" x-on:site-updated.window="window.location = '{{ route('admin.sites.index') }}'" x-on:site-bulk.window="$wire.$refresh()">
    {{-- Toast --}}
    <div x-data="{ toast: false, message: '', type: 'success' }"
        @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)"
        x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message">
    </div>
    @if (session()->has('success'))
        <div class="p-3 rounded-lg text-sm"
            style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Data Sites') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Daftar seluruh site lokasi') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm text-muted">{{ $sites->total() }} {{ __('site') }}</span>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Export') }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                    class="absolute right-0 mt-1 w-44 rounded-lg shadow-lg z-30 py-1"
                    style="background: var(--color-card-bg); border: 1px solid var(--color-card-border);"
                    x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <a href="{{ route('admin.sites.export', ['format' => 'pdf']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as PDF') }}</a>
                    <a href="{{ route('admin.sites.export', ['format' => 'xlsx']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLSX') }}</a>
                    <a href="{{ route('admin.sites.export', ['format' => 'xls']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLS') }}</a>
                    <a href="{{ route('admin.sites.export', ['format' => 'html']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as HTML') }}</a>
                    <a href="{{ route('admin.sites.export', ['format' => 'csv']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as CSV') }}</a>
                </div>
            </div>
            <a href="{{ route('admin.sites.import') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import CSV
            </a>
            <a href="{{ route('admin.sites.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Tambah Site') }}
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="glass-card p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-muted uppercase tracking-wider">{{ __('Filter Data') }}</p>
            @if($filterId || $filterSite || $filterBuss || $filterCorp || $filterCountry || $filterProvincy || $filterCity)
                <a href="{{ route('admin.sites.index') }}" wire:navigate
                    class="inline-flex items-center px-3 py-1 rounded-lg text-xs transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    {{ __('Reset') }}
                </a>
            @endif
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('ID Site') }}</label>
                <input wire:model.live.debounce.300ms="filterId" type="text" placeholder="{{ __('ID site') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Nama Site') }}</label>
                <input wire:model.live.debounce.300ms="filterSite" type="text" placeholder="{{ __('Nama site') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">Buss</label>
                <input wire:model.live.debounce.300ms="filterBuss" type="text" placeholder="Buss..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Corp Unit') }}</label>
                <input wire:model.live.debounce.300ms="filterCorp" type="text" placeholder="id_corp..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Negara') }}</label>
                <input wire:model.live.debounce.300ms="filterCountry" type="text" placeholder="{{ __('Negara') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Provinsi') }}</label>
                <input wire:model.live.debounce.300ms="filterProvincy" type="text" placeholder="{{ __('Provinsi') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Kota') }}</label>
                <input wire:model.live.debounce.300ms="filterCity" type="text" placeholder="{{ __('Kota') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
        </div>
    </div>

    {{-- Sites Table --}}
    @if($sites->count() > 0)
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" wire:click="toggleSelectAll"
                                    class="rounded cursor-pointer" style="accent-color: var(--color-primary);"
                                    @checked($allSelected)>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Nama Site') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">{{ __('Provinsi') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden md:table-cell">{{ __('Kota') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">{{ __('Negara') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($sites as $s)
                            <tr class="transition-colors duration-150" style="hover: background: var(--color-glass-bg);">
                                <td class="px-4 py-3 w-10">
                                    <input type="checkbox" value="{{ $s->id_site }}" wire:model.live="selected"
                                        class="rounded cursor-pointer" style="accent-color: var(--color-primary);">
                                </td>
                                <td class="px-4 py-3 font-mono text-secondary">{{ $s->id_site }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-primary">{{ $s->site }}</div>
                                    @if($s->city)
                                        <div class="text-xs text-muted mt-0.5">{{ $s->city }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-secondary hidden sm:table-cell">{{ $s->provincy ?? '-' }}</td>
                                <td class="px-4 py-3 text-secondary hidden md:table-cell">{{ $s->city ?? '-' }}</td>
                                <td class="px-4 py-3 text-secondary hidden lg:table-cell">{{ $s->country ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.sites.edit', $s->id_site) }}" wire:navigate
                                            class="p-1.5 rounded-lg transition-colors duration-200"
                                            style="color: var(--color-text-secondary);"
                                            title="{{ __('Edit') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button wire:click="confirmDelete('{{ $s->id_site }}', '{{ addslashes($s->site) }}')"
                                            class="p-1.5 rounded-lg transition-colors duration-200 text-red-400 hover:text-red-300"
                                            title="{{ __('Hapus') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(count($selected) > 0)
            <div class="glass-card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                style="border-color: rgba(245, 158, 11, 0.4);">
                <p class="text-sm text-primary">{{ count($selected) }} {{ __('site terpilih') }}</p>
                <div class="flex items-center gap-2">
                    <button wire:click="openBulkEdit" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('Edit Massal') }}
                    </button>
                    <button wire:click="confirmBulkDelete" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-red-500 text-white hover:bg-red-600 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Hapus Terpilih') }}
                    </button>
                </div>
            </div>
        @endif

        <div class="mt-6">
            {{ $sites->links() }}
        </div>
    @else
        <div class="glass-card p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-3 text-muted">{{ __('Tidak ada site ditemukan') }}</p>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus Site') }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus site') }} <span class="font-semibold text-primary">{{ $deleteSiteName }}</span> ({{ $deleteSiteId }})?</p>
                <div class="flex gap-2">
                    <button wire:click="cancelDelete" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="deleteSite" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Hapus') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelBulkDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelBulkDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus Site Terpilih') }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus') }} <span class="font-semibold text-primary">{{ count($selected) }} {{ __('site') }}</span> {{ __('yang terpilih') }}?</p>
                <div class="flex gap-2">
                    <button wire:click="cancelBulkDelete" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="bulkDelete" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Hapus') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Edit Modal --}}
    @if($showBulkEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelBulkEdit()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelBulkEdit()">
                <h3 class="text-lg font-bold text-primary">{{ __('Edit Massal Site') }} ({{ count($selected) }} {{ __('site') }})</h3>
                <div>
                    <label class="block text-xs font-medium text-muted mb-1">{{ __('Field') }}</label>
                    <select wire:model="bulkEditField"
                        class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Field') }}</option>
                        <option value="buss">Buss</option>
                        <option value="id_corp">Corp Unit (id_corp)</option>
                        <option value="country">{{ __('Negara') }}</option>
                        <option value="provincy">{{ __('Provinsi') }}</option>
                        <option value="city">{{ __('Kota') }}</option>
                    </select>
                    @error('bulkEditField') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted mb-1">{{ __('Nilai Baru') }}</label>
                    <input type="text" wire:model="bulkEditValue" placeholder="{{ __('Nilai baru') }}"
                        class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                </div>
                <div class="flex gap-2">
                    <button wire:click="cancelBulkEdit" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="bulkEdit" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">{{ __('Simpan') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>

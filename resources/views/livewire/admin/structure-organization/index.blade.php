<div class="space-y-6" x-data="{
    tip: false,
    tipTitle: '',
    tipItems: [],
    showTip(e, title, items) {
        this.tipTitle = title;
        this.tipItems = items;
        this.tip = true;
        this.$nextTick(() => this.moveTip(e));
    },
    moveTip(e) {
        const el = this.$refs.tip;
        if (!el) return;
        const pad = 12;
        let x = e.clientX + pad;
        let y = e.clientY + pad;
        const r = el.getBoundingClientRect();
        if (x + r.width > window.innerWidth - 8) x = e.clientX - r.width - pad;
        if (y + r.height > window.innerHeight - 8) y = e.clientY - r.height - pad;
        el.style.left = x + 'px';
        el.style.top = y + 'px';
    },
    hideTip() { this.tip = false; }
}">
    {{-- Hover Tooltip --}}
    <div x-cloak x-ref="tip" x-show="tip" x-transition
        class="fixed z-50 w-64 max-h-64 overflow-y-auto rounded-lg shadow-lg px-3 py-2 text-xs"
        style="background: var(--color-card-bg); border: 1px solid var(--color-card-border); color: var(--color-text-primary); pointer-events: none;">
        <div class="font-semibold text-secondary mb-1" x-text="tipTitle"></div>
        <ul class="space-y-0.5">
            <template x-for="(item, i) in tipItems" :key="i">
                <li class="py-0.5 border-b last:border-0" style="border-color: var(--color-border);" x-text="item"></li>
            </template>
        </ul>
        <div x-show="!tipItems.length" class="text-muted">-</div>
    </div>

    {{-- Toast --}}
    <div x-data="{ toast: false, message: '', type: 'success' }"
        @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)"
        x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message">
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Structure Organization') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Kelola directorat, divisi, departemen, sub departemen & level position') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1 p-1 rounded-lg w-fit" style="background: var(--color-bg-tertiary);">
                <button wire:click="$set('viewMode', 'table')" type="button"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors duration-200 {{ $viewMode === 'table' ? 'text-white' : 'text-muted' }}"
                    style="{{ $viewMode === 'table' ? 'background: var(--color-primary);' : '' }}">
                    {{ __('Table') }}
                </button>
                <button wire:click="$set('viewMode', 'tree')" type="button"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors duration-200 {{ $viewMode === 'tree' ? 'text-white' : 'text-muted' }}"
                    style="{{ $viewMode === 'tree' ? 'background: var(--color-primary);' : '' }}">
                    {{ __('Tree') }}
                </button>
            </div>
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
                    <a href="{{ route('admin.structure-organization.export', ['type' => $activeTab, 'format' => 'pdf']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as PDF') }}</a>
                    <a href="{{ route('admin.structure-organization.export', ['type' => $activeTab, 'format' => 'xlsx']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLSX') }}</a>
                    <a href="{{ route('admin.structure-organization.export', ['type' => $activeTab, 'format' => 'xls']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLS') }}</a>
                    <a href="{{ route('admin.structure-organization.export', ['type' => $activeTab, 'format' => 'html']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as HTML') }}</a>
                    <a href="{{ route('admin.structure-organization.export', ['type' => $activeTab, 'format' => 'csv']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as CSV') }}</a>
                </div>
            </div>
            <a href="{{ route('admin.structure-organization.import', ['type' => $activeTab]) }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import CSV
            </a>
            <button wire:click="openCreate" type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Tambah') }} {{ $this->tabLabel }}
            </button>
        </div>
    </div>

    @if($viewMode === 'table')
        {{-- Tabs --}}
        <div class="flex flex-wrap gap-1 rounded-lg p-1 glass-card">
        @foreach(['directorate' => 'Directorat', 'divisi' => 'Divisi', 'departement' => 'Departemen', 'sub_departement' => 'Sub Departemen', 'position' => 'Position'] as $key => $label)
            <button wire:click="setTab('{{ $key }}')" type="button"
                class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors duration-200"
                :style="'{{ $activeTab === $key ? 'background: var(--color-primary); color: var(--color-button-text);' : 'color: var(--color-text-secondary);' }}'">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Search --}}
    <div class="glass-card p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-muted uppercase tracking-wider">{{ __('Filter') }} {{ $this->tabLabel }}</p>
            @if($search)
                <button wire:click="search = ''" type="button"
                    class="inline-flex items-center px-3 py-1 rounded-lg text-xs transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    {{ __('Reset') }}
                </button>
            @endif
        </div>
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="{{ __('Cari nama') }} {{ $this->tabLabel }}..."
            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
    </div>

    {{-- Table --}}
    @if($records->count() > 0)
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" wire:click="toggleSelectAll"
                                    class="rounded cursor-pointer" style="accent-color: var(--color-primary);"
                                    @checked(count($records->pluck('id')->intersect($selected)) === $records->count() && $records->count() > 0)>
                            </th>
                            @if($activeTab === 'position')
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider w-20">{{ __('Urutan') }}</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Nama') }}</th>
                            @if(in_array($activeTab, ['divisi', 'departement', 'sub_departement']))
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden md:table-cell">{{ $this->parentFieldLabel }}</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">Code</th>
                            @if($activeTab === 'directorate')
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">{{ __('Divisi') }}</th>
                            @endif
                            @if($activeTab === 'divisi')
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">{{ __('Departemen') }}</th>
                            @endif
                            @if($activeTab === 'departement')
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">{{ __('Sub Departemen') }}</th>
                            @endif
                            @if($activeTab === 'position')
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">{{ __('Employee') }}</th>
                            @endif
                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($records as $record)
                            <tr class="transition-colors duration-150">
                                <td class="px-4 py-3 w-10">
                                    <input type="checkbox" value="{{ $record->getKey() }}" wire:model.live="selected"
                                        class="rounded cursor-pointer" style="accent-color: var(--color-primary);">
                                </td>
                                @if($activeTab === 'position')
                                    <td class="px-4 py-3 font-mono text-xs text-secondary w-20">{{ $record->sort_order }}</td>
                                @endif
                                <td class="px-4 py-3">
                                    @if($activeTab !== 'position')
                                        <button wire:click="showHierarchy({{ $record->getKey() }})" type="button"
                                            class="font-medium text-primary hover:underline transition-colors duration-200 cursor-pointer"
                                            title="{{ __('Lihat hirarki') }}">
                                            {{ $record->name }}
                                        </button>
                                    @else
                                        <div class="font-medium text-primary">{{ $record->name }}</div>
                                    @endif
                                </td>
                                @if(in_array($activeTab, ['divisi', 'departement', 'sub_departement']))
                                    <td class="px-4 py-3 text-secondary hidden md:table-cell">
                                        @if($activeTab === 'divisi')
                                            {{ $record->directorate?->name ?? '-' }}
                                        @elseif($activeTab === 'departement')
                                            <span class="truncate block max-w-[220px]">
                                                {{ trim(($record->divisi?->directorate?->name ?? '').' / '.($record->divisi?->name ?? ''), ' /') ?: '-' }}
                                            </span>
                                        @else
                                            <span class="truncate block max-w-[260px]">
                                                {{ trim(($record->departement?->divisi?->directorate?->name ?? '').' / '.($record->departement?->divisi?->name ?? '').' / '.($record->departement?->name ?? ''), ' /') ?: '-' }}
                                            </span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-4 py-3 font-mono text-xs text-secondary hidden lg:table-cell">{{ $record->code ?? '-' }}</td>
                                @if(in_array($activeTab, ['directorate', 'divisi', 'departement']))
                                    @php
                                        $countColumn = match ($activeTab) {
                                            'directorate' => 'divisis_count',
                                            'divisi' => 'departements_count',
                                            'departement' => 'sub_departements_count',
                                            default => null,
                                        };
                                        $childLabel = match ($activeTab) {
                                            'directorate' => __('Divisi'),
                                            'divisi' => __('Departemen'),
                                            'departement' => __('Sub Departemen'),
                                            default => '',
                                        };
                                        $childNames = match ($activeTab) {
                                            'directorate' => $record->divisis,
                                            'divisi' => $record->departements,
                                            'departement' => $record->subDepartements,
                                            default => collect(),
                                        };
                                    @endphp
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium cursor-help"
                                            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);"
                                            @mouseenter='showTip($event, @json($childLabel), @json($childNames->pluck('name')->values()->all()))'
                                            @mousemove="moveTip($event)"
                                            @mouseleave="hideTip()">
                                            {{ $record->{$countColumn} ?? 0 }}
                                        </span>
                                    </td>
                                @endif
                                @if($activeTab === 'position')
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium" style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                                            {{ $record->employees_count }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="openEdit({{ $record->getKey() }})" type="button"
                                            class="p-1.5 rounded-lg transition-colors duration-200"
                                            style="color: var(--color-text-secondary);"
                                            title="{{ __('Edit') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $record->getKey() }}, '{{ addslashes($record->name) }}')" type="button"
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
            <div class="mt-3 glass-card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                style="border-color: rgba(245, 158, 11, 0.4);">
                <p class="text-sm text-primary">{{ count($selected) }} {{ $this->tabLabel }} {{ __('terpilih') }}</p>
                <div class="flex items-center gap-2">
                    <button wire:click="confirmBulkDelete" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-red-500 text-white hover:bg-red-600 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Hapus Terpilih') }}
                    </button>
                </div>
            </div>
        @endif

        <div class="mt-6">
            {{ $records->links() }}
        </div>
    @else
        <div class="glass-card p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6h18M3 12h12M3 18h7"/>
            </svg>
            <p class="mt-3 text-muted">{{ __('Tidak ada data') }} {{ $this->tabLabel }}</p>
        </div>
    @endif
    @else
        {{-- Tree View --}}
        <div class="glass-card p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h3 class="text-sm font-bold text-primary">{{ __('Hirarki Structure Organization') }}</h3>
                <p class="text-xs text-muted">{{ __('Klik node untuk expand / collapse cabang') }}</p>
            </div>
            @if(count($fullHierarchy) > 0)
                <div class="rounded-lg p-4 overflow-x-auto" style="background: var(--color-bg-tertiary);">
                    <ul class="space-y-1 min-w-max">
                        @foreach($fullHierarchy as $node)
                            @include('livewire.admin.structure-organization._tree-node', ['node' => $node])
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="text-sm text-muted text-center py-4">{{ __('Tidak ada data hirarki') }}</p>
            @endif
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.closeModal()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.closeModal()">
                <h3 class="text-lg font-bold text-primary">{{ $editingId ? __('Edit') : __('Tambah') }} {{ $this->tabLabel }}</h3>

                @if($this->parentFieldLabel)
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1">{{ $this->parentFieldLabel }} <span class="text-red-400">*</span></label>
                        <select wire:model="modalParentId"
                            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                            <option value="">{{ __('Pilih') }} {{ $this->parentFieldLabel }}</option>
                            @if($activeTab === 'divisi')
                                @foreach($this->getDirectorateOptions() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            @elseif($activeTab === 'departement')
                                @foreach($this->getDivisiOptions() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            @else
                                @foreach($this->getDepartementOptions() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('modalParentId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Nama') }} <span class="text-red-400">*</span></label>
                    <input wire:model="modalName" type="text"
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                        placeholder="{{ __('Nama') }} {{ $this->tabLabel }}" />
                    @error('modalName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1">Code</label>
                        <input wire:model="modalCode" type="text"
                            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                            placeholder="Opsional" />
                    </div>
                    @if($activeTab === 'position')
                        <div>
                            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Urutan') }}</label>
                            <input wire:model="modalSortOrder" type="number" min="0"
                                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                            @error('modalSortOrder') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button wire:click="save" wire:loading.attr="disabled" type="button"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">
                        <span wire:loading.remove wire:target="save">{{ __('Simpan') }}</span>
                        <span wire:loading wire:target="save">{{ __('Menyimpan') }}...</span>
                    </button>
                    <button wire:click="closeModal" type="button"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                        {{ __('Batal') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus') }} {{ $this->tabLabel }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus') }} <span class="font-semibold text-primary">{{ $deleteName }}</span>?</p>
                <div class="flex gap-2">
                    <button wire:click="cancelDelete" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="delete" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Hapus') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelBulkDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelBulkDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus Massal') }} {{ $this->tabLabel }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus') }} <span class="font-semibold text-primary">{{ count($selected) }}</span> {{ strtolower($this->tabLabel) }}? <span class="text-muted">{{ __('Data yang masih digunakan akan dilewati.') }}</span></p>
                <div class="flex gap-2">
                    <button wire:click="cancelBulkDelete" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="bulkDelete" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Hapus') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Hierarchy Modal --}}
    @if($showHierarchyModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.closeHierarchy()">
            <div class="glass-card p-6 w-full max-w-lg space-y-4" @click.away="$wire.closeHierarchy()">
                <h3 class="text-lg font-bold text-primary">{{ $hierarchyTitle }}</h3>
                <div class="max-h-96 overflow-y-auto rounded-lg p-4" style="background: var(--color-bg-tertiary);">
                    <ul class="space-y-1">
                        @foreach($hierarchyTree as $node)
                            @include('livewire.admin.structure-organization._tree-node', ['node' => $node])
                        @endforeach
                    </ul>
                </div>
                <div class="flex justify-end">
                    <button wire:click="closeHierarchy" type="button" class="glass-button-secondary text-sm">{{ __('Tutup') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>

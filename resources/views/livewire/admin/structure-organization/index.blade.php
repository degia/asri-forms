<div class="space-y-6">
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
        <button wire:click="openCreate" type="button"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
            style="background: var(--color-primary); color: var(--color-button-text);">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('Tambah') }} {{ $this->tabLabel }}
        </button>
    </div>

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
                                @if($activeTab === 'position')
                                    <td class="px-4 py-3 font-mono text-xs text-secondary w-20">{{ $record->sort_order }}</td>
                                @endif
                                <td class="px-4 py-3">
                                    <div class="font-medium text-primary">{{ $record->name }}</div>
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
                                    @endphp
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium" style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
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
</div>

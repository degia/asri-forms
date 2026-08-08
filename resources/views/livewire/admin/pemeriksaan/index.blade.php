<div class="space-y-6" x-data x-on:form-deleted.window="$wire.$refresh()" x-on:form-bulk.window="$wire.$refresh()">
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
            <h1 class="text-2xl font-bold text-primary">{{ __('Form Pemeriksaan (PMR)') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Daftar seluruh formulir pemeriksaan perangkat') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm text-muted">{{ $forms->total() }} {{ __('form') }}</span>
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
                    <a href="{{ route('admin.pemeriksaan.export', ['format' => 'pdf']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as PDF') }}</a>
                    <a href="{{ route('admin.pemeriksaan.export', ['format' => 'xlsx']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLSX') }}</a>
                    <a href="{{ route('admin.pemeriksaan.export', ['format' => 'xls']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLS') }}</a>
                    <a href="{{ route('admin.pemeriksaan.export', ['format' => 'html']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as HTML') }}</a>
                    <a href="{{ route('admin.pemeriksaan.export', ['format' => 'csv']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as CSV') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="{{ __('Cari no form, teknisi, pengguna, perangkat...') }}"
                        class="w-full pl-10 pr-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"/>
                </div>
            </div>
            <div>
                <select wire:model.live="status" class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="diketahui">Diketahui</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="selesai">Selesai</option>
                    <option value="revisi">Revisi</option>
                </select>
            </div>
            <div>
                <select wire:model.live="kondisi" class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua Kondisi') }}</option>
                    <option value="baru">Baru</option>
                    <option value="lama">Lama</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    @if($forms->count() > 0)
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('No. Form') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">{{ __('Teknisi') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden md:table-cell">{{ __('Pengguna') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">{{ __('Perangkat') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">Site</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Kondisi') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden xl:table-cell">{{ __('Tanggal') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($forms as $form)
                            <tr class="transition-colors duration-150" style="hover: background: var(--color-glass-bg);">
                                <td class="px-4 py-3 w-10">
                                    <input type="checkbox" value="{{ $form->id }}" wire:model.live="selected"
                                        class="rounded cursor-pointer" style="accent-color: var(--color-primary);">
                                </td>
                                <td class="px-4 py-3 font-mono text-secondary text-xs">{{ $form->nomor_form }}</td>
                                <td class="px-4 py-3 text-primary hidden sm:table-cell">{{ $form->teknisi->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-primary hidden md:table-cell">{{ $form->pengguna->name ?? '-' }}</td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div class="font-medium text-primary text-xs">{{ $form->asset->nama_perangkat ?? '-' }}</div>
                                    @if($form->asset)
                                        <div class="text-xs text-muted mt-0.5 font-mono">{{ $form->asset->no_asset }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-secondary text-xs hidden lg:table-cell">{{ $form->site->site ?? $form->site_location ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($form->kondisi === 'baru')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background: rgba(59,130,246,0.15); color: #3b82f6;">Baru</span>
                                    @elseif($form->kondisi === 'lama')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background: rgba(234,179,8,0.15); color: #eab308;">Lama</span>
                                    @else
                                        <span class="text-xs text-muted">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'draft' => 'background: rgba(107,114,128,0.15); color: #6b7280;',
                                            'submitted' => 'background: rgba(59,130,246,0.15); color: #3b82f6;',
                                            'diketahui' => 'background: rgba(234,179,8,0.15); color: #eab308;',
                                            'disetujui' => 'background: rgba(34,197,94,0.15); color: #22c55e;',
                                            'selesai' => 'background: rgba(16,185,129,0.15); color: #10b981;',
                                            'revisi' => 'background: rgba(239,68,68,0.15); color: #ef4444;',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold" style="{{ $statusColors[$form->status] ?? '' }}">
                                        {{ ucfirst($form->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted text-xs hidden xl:table-cell">{{ $form->submitted_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="viewForm({{ $form->id }})"
                                            class="p-1.5 rounded-lg transition-colors duration-200"
                                            style="color: var(--color-text-secondary);" title="{{ __('Lihat Detail') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <a href="{{ route('pemeriksaan.export-pdf', $form->id) }}" target="_blank"
                                            class="p-1.5 rounded-lg transition-colors duration-200" style="color: var(--color-text-secondary);" title="{{ __('Export PDF') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
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
                <p class="text-sm text-primary">{{ count($selected) }} {{ __('form terpilih') }}</p>
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
            {{ $forms->links() }}
        </div>
    @else
        <div class="glass-card p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-3 text-muted">{{ __('Tidak ada form pemeriksaan ditemukan') }}</p>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($viewingForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.closeView()">
            <div class="glass-card w-full max-w-3xl max-h-[85vh] overflow-y-auto p-6 space-y-5"
                @click.away="$wire.closeView()">

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-primary">{{ __('Detail Form Pemeriksaan') }}</h2>
                        <p class="text-xs text-muted font-mono mt-0.5">{{ $viewingForm['nomor_form'] }}</p>
                    </div>
                    <button wire:click="closeView" class="text-muted hover:text-primary text-xl">&times;</button>
                </div>

                {{-- Info Section --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                    <div><span class="text-muted text-xs">{{ __('Status') }}</span>
                        @php
                            $sc = match($viewingForm['status']) {
                                'draft' => 'color: #6b7280;',
                                'submitted' => 'color: #3b82f6;',
                                'diketahui' => 'color: #eab308;',
                                'disetujui' => 'color: #22c55e;',
                                'selesai' => 'color: #10b981;',
                                'revisi' => 'color: #ef4444;',
                                default => '',
                            };
                        @endphp
                        <p class="font-semibold" style="{{ $sc }}">{{ ucfirst($viewingForm['status']) }}</p>
                    </div>
                    <div><span class="text-muted text-xs">{{ __('Tanggal') }}</span><p class="text-primary">{{ $viewingForm['submitted_at'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Kondisi') }}</span><p class="text-primary">{{ ucfirst($viewingForm['kondisi'] ?? '-') }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Teknisi') }}</span><p class="text-primary">{{ $viewingForm['teknisi']['name'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Pengguna') }}</span><p class="text-primary">{{ $viewingForm['pengguna']['name'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">NIK</span><p class="text-primary">{{ $viewingForm['pengguna']['nik'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Perangkat') }}</span><p class="text-primary">{{ $viewingForm['asset']['nama_perangkat'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('No. Asset') }}</span><p class="text-primary font-mono text-xs">{{ $viewingForm['asset']['no_asset'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">Site</span><p class="text-primary">{{ $viewingForm['site']['site'] ?? $viewingForm['site_location'] ?? '-' }}</p></div>
                    @if($viewingForm['kondisi_keterangan'])
                        <div class="col-span-2 sm:col-span-3"><span class="text-muted text-xs">{{ __('Keterangan Kondisi') }}</span><p class="text-primary">{{ $viewingForm['kondisi_keterangan'] }}</p></div>
                    @endif
                    @if($viewingForm['location_detail'])
                        <div class="col-span-2 sm:col-span-3"><span class="text-muted text-xs">Location Detail</span><p class="text-primary">{{ $viewingForm['location_detail'] }}</p></div>
                    @endif
                </div>

                {{-- Items --}}
                @if(count($viewingForm['items']) > 0)
                    @php
                        $hwItems = collect($viewingForm['items'])->where('category', 'hardware');
                        $appItems = collect($viewingForm['items'])->where('category', 'aplikasi');
                        $osItems = collect($viewingForm['items'])->where('category', 'operating_system');
                    @endphp
                    <div class="space-y-3">
                        @foreach(['Hardware' => $hwItems, 'Aplikasi' => $appItems, 'Operating System' => $osItems] as $catName => $items)
                            @if($items->count() > 0)
                                <div>
                                    <h3 class="text-xs font-semibold text-muted uppercase mb-2">{{ $catName }}</h3>
                                    <div class="space-y-1">
                                        @foreach($items as $item)
                                            <div class="flex items-center justify-between py-1.5 px-3 rounded text-xs" style="background: var(--color-bg-tertiary);">
                                                <span class="text-primary">{{ $item['name'] }}</span>
                                                <div class="flex items-center gap-3">
                                                    @if($item['full_charge_capacity'] || $item['design_capacity'])
                                                        <span class="text-muted">FCC: {{ $item['full_charge_capacity'] ?? '-' }} mWh | DC: {{ $item['design_capacity'] ?? '-' }} mWh</span>
                                                    @endif
                                                    @if($item['keterangan'])
                                                        <span class="text-muted max-w-[200px] truncate" title="{{ $item['keterangan'] }}">{{ $item['keterangan'] }}</span>
                                                    @endif
                                                    <span class="{{ $item['status'] === 'baik' ? 'text-emerald-400' : ($item['status'] === 'tidak_baik' ? 'text-red-400' : 'text-muted') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $item['status'] ?? '-')) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Tindakan --}}
                @if($viewingForm['tindakan_categories'])
                    <div>
                        <h3 class="text-xs font-semibold text-muted uppercase mb-2">{{ __('Tindakan') }}</h3>
                        <div class="space-y-1">
                            @foreach($viewingForm['tindakan_categories'] as $cat)
                                @if(!empty($cat['selected']))
                                    <div class="flex items-start gap-2 py-1 px-3 rounded text-xs" style="background: var(--color-bg-tertiary);">
                                        <span class="text-muted font-medium shrink-0">{{ $cat['label'] }}:</span>
                                        <span class="text-primary">{{ implode(', ', $cat['selected']) }}</span>
                                    </div>
                                @endif
                            @endforeach
                            @if($viewingForm['tindakan_solution'])
                                <div class="flex items-start gap-2 py-1 px-3 rounded text-xs" style="background: var(--color-bg-tertiary);">
                                    <span class="text-muted font-medium shrink-0">Solution:</span>
                                    <span class="text-primary">{{ $viewingForm['tindakan_solution'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                @if($viewingForm['notes'])
                    <div>
                        <h3 class="text-xs font-semibold text-muted uppercase mb-1">{{ __('Catatan') }}</h3>
                        <p class="text-sm text-primary px-3 py-2 rounded" style="background: var(--color-bg-tertiary);">{{ $viewingForm['notes'] }}</p>
                    </div>
                @endif

                {{-- Approvals --}}
                @if(count($viewingForm['approvals']) > 0)
                    <div>
                        <h3 class="text-xs font-semibold text-muted uppercase mb-2">Approval History</h3>                        <div class="space-y-1">
                            @foreach($viewingForm['approvals'] as $approval)
                                <div class="flex items-center justify-between py-1.5 px-3 rounded text-xs" style="background: var(--color-bg-tertiary);">
                                    <span class="text-primary">{{ ucfirst(str_replace('_', ' ', $approval['approval_level'])) }} &mdash; {{ $approval['user_name'] ?? '-' }}</span>
                                    <span class="text-muted">{{ ucfirst($approval['status']) }} {{ $approval['approved_at'] ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex justify-end gap-2 pt-2 border-t" style="border-color: var(--color-border);">
                    <a href="{{ route('pemeriksaan.export-pdf', $viewingForm['id']) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-medium transition-colors duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('Export PDF') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelBulkDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelBulkDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus Form Terpilih') }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus') }} <span class="font-semibold text-primary">{{ count($selected) }} {{ __('form pemeriksaan') }}</span> {{ __('yang terpilih? Data item, approval, dan lampiran juga akan dihapus.') }}</p>
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
                <h3 class="text-lg font-bold text-primary">{{ __('Edit Massal') }} ({{ count($selected) }} {{ __('form') }})</h3>
                <div>
                    <label class="block text-xs font-medium text-muted mb-1">{{ __('Field') }}</label>
                    <select wire:model="bulkEditField"
                        class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Field') }}</option>
                        <option value="status">{{ __('Status') }}</option>
                        <option value="kondisi">{{ __('Kondisi') }}</option>
                    </select>
                    @error('bulkEditField') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted mb-1">{{ __('Nilai Baru') }}</label>
                    @if($bulkEditField === 'kondisi')
                        <select wire:model="bulkEditValue"
                            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                            <option value="">{{ __('Pilih Kondisi') }}</option>
                            <option value="baru">Baru</option>
                            <option value="lama">Lama</option>
                        </select>
                    @elseif($bulkEditField === 'status')
                        <select wire:model="bulkEditValue"
                            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                            <option value="">{{ __('Pilih Status') }}</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="diketahui">Diketahui</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="selesai">Selesai</option>
                            <option value="revisi">Revisi</option>
                        </select>
                    @else
                        <input type="text" wire:model="bulkEditValue" placeholder="{{ __('Nilai baru') }}"
                            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                    @endif
                    @error('bulkEditValue') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
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

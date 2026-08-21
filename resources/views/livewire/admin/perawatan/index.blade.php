<div class="space-y-6" x-data x-on:form-deleted.window="$wire.$refresh()" x-on:form-bulk.window="$wire.$refresh()" x-on:open-url.window="window.open($event.detail.url, '_blank')">
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
            <h1 class="text-2xl font-bold text-primary">{{ __('Form Perawatan (PWT)') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Daftar seluruh formulir perawatan perangkat') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm text-muted">{{ $forms->total() }} {{ __('form') }}</span>
            @role('admin')
            <a href="{{ route('admin.perawatan.import') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                {{ __('Import CSV') }}
            </a>
            @endrole
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
                    <a href="{{ route('admin.perawatan.export', ['format' => 'pdf']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as PDF') }}</a>
                    <a href="{{ route('admin.perawatan.export', ['format' => 'xlsx']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLSX') }}</a>
                    <a href="{{ route('admin.perawatan.export', ['format' => 'xls']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLS') }}</a>
                    <a href="{{ route('admin.perawatan.export', ['format' => 'html']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as HTML') }}</a>
                    <a href="{{ route('admin.perawatan.export', ['format' => 'csv']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as CSV') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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
                <select wire:model.live="site" class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua Site') }}</option>
                    @foreach($sites as $s)
                        <option value="{{ $s->site }}">{{ $s->site }}</option>
                    @endforeach
                </select>
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
                <select wire:model.live="kondisi_akhir" class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua Kondisi Akhir') }}</option>
                    <option value="good_normal">Good / Normal</option>
                    <option value="caution_poor">Caution / Poor</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="critical">Critical</option>
                    <option value="poor">Poor</option>
                </select>
            </div>
        </div>
        @if($site)
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-muted">{{ __('Filter Site:') }}</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/15 text-purple-400">
                    {{ $site }}
                    <button wire:click="clearSiteFilter" class="ml-0.5 hover:text-purple-300" title="{{ __('Hapus filter') }}">&times;</button>
                </span>
            </div>
        @endif
    </div>

    {{-- Table --}}
    @if($forms->count() > 0)
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            @if(auth()->user()->hasRole('admin'))
                                <th class="px-4 py-3 w-10">
                                    <input type="checkbox" wire:click="toggleSelectAll"
                                        class="rounded cursor-pointer" style="accent-color: var(--color-primary);"
                                        @checked($allSelected)>
                                </th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('No. Form') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">{{ __('Teknisi') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden md:table-cell">{{ __('Pengguna') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">{{ __('Perangkat') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">Site</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Kondisi Akhir') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden xl:table-cell">{{ __('Tanggal') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($forms as $form)
                            <tr class="transition-colors duration-150" style="hover: background: var(--color-glass-bg);">
                                @if(auth()->user()->hasRole('admin'))
                                    <td class="px-4 py-3 w-10">
                                        <input type="checkbox" value="{{ $form->id }}" wire:model.live="selected"
                                            class="rounded cursor-pointer" style="accent-color: var(--color-primary);">
                                    </td>
                                @endif
                                <td class="px-4 py-3 font-mono text-secondary text-xs">{{ $form->nomor_form }}</td>
                                <td class="px-4 py-3 text-primary hidden sm:table-cell">{{ $form->teknisi->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-primary hidden md:table-cell">{{ $form->pengguna->name ?? '-' }}</td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div class="font-medium text-primary text-xs">{{ $form->asset->nama_perangkat ?? '-' }}</div>
                                    @if($form->asset)
                                        <div class="text-xs text-muted mt-0.5 font-mono">{{ $form->asset->no_asset }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    @if($form->site)
                                        <button wire:click="$set('site', '{{ $form->site->site }}')" class="text-xs text-purple-400 hover:text-purple-300 font-medium transition-colors cursor-pointer" title="{{ __('Filter by') }} {{ $form->site->site }}">
                                            {{ $form->site->site }}
                                        </button>
                                    @else
                                        <span class="text-secondary text-xs">{{ $form->site_location ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $kondisiColors = [
                                            'good' => 'background: rgba(16,185,129,0.15); color: #10b981;',
                                            'good_normal' => 'background: rgba(16,185,129,0.15); color: #10b981;',
                                            'fair' => 'background: rgba(59,130,246,0.15); color: #3b82f6;',
                                            'critical' => 'background: rgba(245,158,11,0.15); color: #f59e0b;',
                                            'poor' => 'background: rgba(239,68,68,0.15); color: #ef4444;',
                                            'caution_poor' => 'background: rgba(245,158,11,0.15); color: #f59e0b;',
                                        ];
                                        $kondisiLabels = [
                                            'good_normal' => 'Good/Normal',
                                            'caution_poor' => 'Caution/Poor',
                                        ];
                                    @endphp
                                    @if($form->kondisi_akhir)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold" style="{{ $kondisiColors[$form->kondisi_akhir] ?? '' }}">
                                            {{ $kondisiLabels[$form->kondisi_akhir] ?? ucfirst($form->kondisi_akhir) }}
                                        </span>
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
                                        <a href="{{ route('perawatan.export-pdf', $form->id) }}" target="_blank"
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

        @if(auth()->user()->hasRole('admin') && count($selected) > 0)
            <div class="glass-card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                style="border-color: rgba(245, 158, 11, 0.4);">
                <p class="text-sm text-primary">{{ count($selected) }} {{ __('form terpilih') }}</p>
                <div class="flex items-center gap-2">
                    <button wire:click="downloadBulkPdf" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('Download PDF') }}
                    </button>
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
            <p class="mt-3 text-muted">{{ __('Tidak ada form perawatan ditemukan') }}</p>
        </div>
    @endif

    {{-- Detail Modal (Document Layout) --}}
    @if($viewingForm)
        @php
            $_form = $viewingForm;
            $_items = $_form['items'] ?? [];
            $_hardware = array_values(array_filter($_items, fn($i) => ($i['category'] ?? '') === 'hardware'));
            $_aplikasi = array_values(array_filter($_items, fn($i) => ($i['category'] ?? '') === 'aplikasi'));
            $_os = array_values(array_filter($_items, fn($i) => ($i['category'] ?? '') === 'operating_system'));
            usort($_hardware, fn($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
            usort($_aplikasi, fn($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
            usort($_os, fn($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
            $_approvals = $_form['approvals'] ?? [];
            $_diperiksa = current(array_filter($_approvals, fn($a) => ($a['approval_level'] ?? '') === 'diperiksa_oleh')) ?: null;
            $_diketahui = current(array_filter($_approvals, fn($a) => ($a['approval_level'] ?? '') === 'diketahui_oleh')) ?: null;
            $_disetujui = current(array_filter($_approvals, fn($a) => ($a['approval_level'] ?? '') === 'disetujui_oleh')) ?: null;
            $_kondisiOpts = [
                'good' => ['label' => 'Good', 'color' => '#10b981'],
                'fair' => ['label' => 'Fair', 'color' => '#3b82f6'],
                'critical' => ['label' => 'Critical', 'color' => '#f59e0b'],
                'poor' => ['label' => 'Poor', 'color' => '#ef4444'],
                'good_normal' => ['label' => 'Good/Normal', 'color' => '#10b981'],
                'caution_poor' => ['label' => 'Caution/Poor', 'color' => '#ef4444'],
            ];
            $_selectedKondisi = $_kondisiOpts[$_form['kondisi_akhir'] ?? ''] ?? null;
        @endphp
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-8 overflow-y-auto"
            style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);" x-data x-on:click.self="$wire.closeView()" x-on:keydown.escape.window="$wire.closeView()">
            <div class="w-full max-w-5xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                x-on:click.self="$wire.closeView()">
                <div class="sticky top-0 z-10 flex justify-end p-2 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <button wire:click="closeView" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none px-1">&times;</button>
                </div>
                <div class="p-6 space-y-5 text-sm text-gray-800 dark:text-gray-200">
                    {{-- HEADER --}}
                    <table class="w-full">
                        <tr>
                            <td class="w-14 align-middle"><img src="{{ asset('images/asri.png') }}" class="w-12 h-12 object-contain" alt="ASRI"></td>
                            <td class="text-center">
                                <h1 class="text-lg font-bold">FORMULIR PERAWATAN PERANGKAT</h1>
                                <p class="text-xs text-gray-500">IT Department &mdash; ASRI</p>
                            </td>
                            <td class="w-14"></td>
                        </tr>
                    </table>

                    {{-- NO. FORM & TANGGAL --}}
                    <table class="w-full">
                        <tr>
                            <td class="font-semibold">No : {{ $_form['nomor_form'] }}</td>
                            <td class="text-right text-gray-500">Tanggal : {{ $_form['submitted_at'] ? \Carbon\Carbon::parse($_form['submitted_at'])->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </table>

                    {{-- INFORMASI PENGGUNA --}}
                    <div>
                        <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">Informasi Pengguna</div>
                        <table class="w-full border border-gray-300 dark:border-gray-600">
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[16%]">Nama - [ NIK ]</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[34%]">{{ $_form['pengguna']['name'] ?? '-' }} - [ {{ $_form['pengguna']['nik'] ?? '-' }} ]</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[16%]">Position</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[34%]">{{ $_form['pengguna']['position']['name'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">Alamat Email</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['pengguna']['email'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">SO</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['pengguna']['divisi']['name'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">No. Telepon</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['pengguna']['no_telepon'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">Unit Site</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['pengguna']['site_name'] ?? '-' }}</td>
                            </tr>
                        </table>
                        <div class="mt-1 text-xs text-gray-500">Location Perawatan: Site: {{ $_form['site_location'] ?? '-' }}, {{ $_form['location_detail'] ?? '-' }}</div>
                    </div>

                    {{-- INFORMASI PERANGKAT --}}
                    <div>
                        <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">Informasi Perangkat</div>
                        <table class="w-full border border-gray-300 dark:border-gray-600" style="text-align: center;">
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">Kategori</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">Brand, Tipe</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">Hostname</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">No. Serial</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">No. Asset</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['asset']['kategori'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ ($_form['asset']['brand'] ?? '') . ', ' . ($_form['asset']['tipe'] ?? '') }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['asset']['nama_perangkat'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['asset']['no_serial'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $_form['asset']['no_asset'] ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- CHECKLIST ITEMS (two-col, like PDF) --}}
                    @if (count($_hardware) || count($_aplikasi) || count($_os))
                        <div>
                            <table class="w-full">
                                <tr class="align-top">
                                    {{-- LEFT: HARDWARE + OS --}}
                                    <td class="w-1/2 pr-2 align-top">
                                        <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500" style="margin-top:0;">Perawatan Hardware</div>
                                        <table class="w-full border border-gray-300 dark:border-gray-600">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-800">
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[36%]">Name</th>
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs font-semibold w-[20%]">Status</th>
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[44%]">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($_hardware as $_item)
                                                    <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">{{ $_item['name'] }}</td>
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-center">
                                                            @if (($_item['status'] ?? '') === 'baik') Baik
                                                            @elseif(($_item['status'] ?? '') === 'tidak_baik') Tidak Baik
                                                            @else - @endif
                                                        </td>
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                            {{ $_item['keterangan'] ?? '' }}
                                                            @if ((($_item['name'] ?? '') === 'Battery' || ($_item['name'] ?? '') === 'Battery Report') && (!empty($_item['full_charge_capacity']) || !empty($_item['design_capacity'])))
                                                                @if (!empty($_item['full_charge_capacity']) && !empty($_item['design_capacity']) && $_item['design_capacity'] > 0)
                                                                    <strong>{{ round(($_item['full_charge_capacity'] / $_item['design_capacity']) * 100) }} %</strong>
                                                                @else <strong>-</strong>
                                                                @endif
                                                                [FCC {{ $_item['full_charge_capacity'] ?? '-' }} / DC {{ $_item['design_capacity'] ?? '-' }}]
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-gray-400 text-center">-</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">Perawatan Operating Sistem</div>
                                        <table class="w-full border border-gray-300 dark:border-gray-600">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-800">
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[36%]">Name</th>
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs font-semibold w-[20%]">Status</th>
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[44%]">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($_os as $_item)
                                                    <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">{{ $_item['name'] }}</td>
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-center">
                                                            @if (($_item['status'] ?? '') === 'baik') Baik
                                                            @elseif(($_item['status'] ?? '') === 'tidak_baik') Tidak Baik
                                                            @else - @endif
                                                        </td>
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">{{ $_item['keterangan'] ?? '' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-gray-400 text-center">-</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                    {{-- RIGHT: APLIKASI --}}
                                    <td class="w-1/2 pl-2 align-top">
                                        <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500" style="margin-top:0;">Perawatan Aplikasi</div>
                                        <table class="w-full border border-gray-300 dark:border-gray-600">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-800">
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[36%]">Name</th>
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs font-semibold w-[20%]">Status</th>
                                                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[44%]">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($_aplikasi as $_item)
                                                    <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">{{ $_item['name'] }}</td>
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-center">
                                                            @if (($_item['status'] ?? '') === 'baik') OK
                                                            @elseif(($_item['status'] ?? '') === 'tidak_baik') NOT
                                                            @else - @endif
                                                        </td>
                                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">{{ $_item['keterangan'] ?? '' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-gray-400 text-center">-</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endif

                    {{-- KONDISI SETELAH PERAWATAN + NOTE --}}
                    <table class="w-full">
                        <tr class="align-top">
                            <td class="w-1/2 pr-2 align-top">
                                <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500" style="margin-top:0;">Kondisi Setelah Perawatan :</div>
                                @if ($_selectedKondisi)
                                    <div class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm font-bold" style="color: {{ $_selectedKondisi['color'] }};">
                                        <span class="inline-block w-3.5 h-3.5 rounded-full align-middle mr-1.5" style="background: {{ $_selectedKondisi['color'] }};"></span>{{ $_selectedKondisi['label'] }}
                                    </div>
                                @else
                                    <div class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm text-gray-400">-</div>
                                @endif
                                @if (!empty($_form['kondisi_akhir_notes']))
                                    <div class="mt-1 text-xs text-gray-500"><strong>Keterangan:</strong> {{ $_form['kondisi_akhir_notes'] }}</div>
                                @endif
                            </td>
                            <td class="w-1/2 align-top">
                                <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500" style="margin-top:0;">Note</div>
                                <div class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs">
                                    <strong>Barcode Fisik :</strong> {{ !empty($_form['barcode_fisik']) ? 'Ada' : 'Tidak Ada' }}
                                </div>
                                <div class="mt-2 px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs">
                                    <strong>></strong> {{ $_form['notes'] ?? '*) : diisi untuk perangkat lama' }}
                                </div>
                            </td>
                        </tr>
                    </table>

                    {{-- SIGNATURES --}}
                    <div class="text-xs text-gray-500">Jakarta, {{ $_form['submitted_at'] ? \Carbon\Carbon::parse($_form['submitted_at'])->format('d F Y') : '_______________' }}</div>
                    <table class="w-full mt-4">
                        <tr class="text-center align-top">
                            <td class="w-1/3 px-1">
                                <div class="text-xs font-bold underline mb-1">Perawatan Oleh</div>
                                <div class="text-[10px] text-gray-500 mb-2">Teknisi IT Operation</div>
                                @if ($_diperiksa && !empty($_diperiksa['signature_path']))
                                    <img src="{{ $_diperiksa['signature_path'] }}" class="w-[90px] h-[35px] mx-auto my-1 object-contain" alt="TTD">
                                @else
                                    <div class="w-[90px] border-b border-gray-400 mx-auto my-5"></div>
                                @endif
                                <div class="text-[10px] mt-1">{{ $_diperiksa['user_name'] ?? '_______________' }}</div>
                                <div class="text-[9px] text-gray-500 mt-0.5">Tanggal : {{ $_diperiksa && !empty($_diperiksa['approved_at']) ? \Carbon\Carbon::parse($_diperiksa['approved_at'])->format('d/m/Y') : '___/___/______' }}</div>
                            </td>
                            <td class="w-1/3 px-1">
                                <div class="text-xs font-bold underline mb-1">Diketahui Oleh</div>
                                <div class="text-[10px] text-gray-500 mb-2">Pengguna Perangkat</div>
                                @if ($_diketahui && !empty($_diketahui['signature_path']))
                                    <img src="{{ $_diketahui['signature_path'] }}" class="w-[90px] h-[35px] mx-auto my-1 object-contain" alt="TTD">
                                @else
                                    <div class="w-[90px] border-b border-gray-400 mx-auto my-5"></div>
                                @endif
                                <div class="text-[10px] mt-1">{{ $_diketahui['user_name'] ?? '_______________' }}</div>
                                <div class="text-[9px] text-gray-500 mt-0.5">Tanggal : {{ $_diketahui && !empty($_diketahui['approved_at']) ? \Carbon\Carbon::parse($_diketahui['approved_at'])->format('d/m/Y') : '___/___/______' }}</div>
                            </td>
                            <td class="w-1/3 px-1">
                                <div class="text-xs font-bold underline mb-1">Disetujui Oleh</div>
                                <div class="text-[10px] text-gray-500 mb-2">Supervisor / Manager IT Operation</div>
                                @if ($_disetujui && !empty($_disetujui['signature_path']))
                                    <img src="{{ $_disetujui['signature_path'] }}" class="w-[90px] h-[35px] mx-auto my-1 object-contain" alt="TTD">
                                @else
                                    <div class="w-[90px] border-b border-gray-400 mx-auto my-5"></div>
                                @endif
                                <div class="text-[10px] mt-1">{{ $_disetujui['user_name'] ?? '_______________' }}</div>
                                <div class="text-[9px] text-gray-500 mt-0.5">Tanggal : {{ $_disetujui && !empty($_disetujui['approved_at']) ? \Carbon\Carbon::parse($_disetujui['approved_at'])->format('d/m/Y') : '___/___/______' }}</div>
                            </td>
                        </tr>
                    </table>

                    {{-- FOOTER --}}
                    <div class="text-center text-[9px] text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
                        FM/ASRI/ITE/09-00 - Form Perawatan Perangkat &mdash; {{ $_form['nomor_form'] }} &mdash; {{ $_form['asset']['nama_perangkat'] ?? '' }}
                    </div>

                    {{-- ACTIONS --}}
                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('perawatan.export-pdf', $_form['id']) }}" target="_blank"
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">Export PDF</a>
                        @if (in_array($_form['status'], ['draft', 'submitted', 'diketahui']) && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teknisi')))
                            <a href="{{ route('perawatan.create') }}?formId={{ $_form['id'] }}" wire:navigate
                                class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">Edit</a>
                        @endif
                    </div>
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
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus') }} <span class="font-semibold text-primary">{{ count($selected) }} {{ __('form perawatan') }}</span> {{ __('yang terpilih? Data item, approval, dan lampiran juga akan dihapus.') }}</p>
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
                        <option value="kondisi_akhir">{{ __('Kondisi Akhir') }}</option>
                    </select>
                    @error('bulkEditField') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted mb-1">{{ __('Nilai Baru') }}</label>
                    <input wire:model.debounce.300ms="bulkEditValue" type="text" placeholder="{{ __('Masukkan nilai baru...') }}"
                        class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
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

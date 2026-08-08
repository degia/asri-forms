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
            <h1 class="text-2xl font-bold text-primary">{{ __('Form Pengembalian Asset') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Daftar seluruh pengembalian asset sebelum resign') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-muted">{{ $forms->total() }} {{ __('form') }}</span>
            <a href="{{ route('admin.pengembalian.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Buat Pengembalian') }}
            </a>
        </div>
    </div>

    {{-- Search --}}
    <div class="glass-card p-4">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="{{ __('Cari no form, teknisi, pengguna...') }}"
                class="w-full pl-10 pr-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"/>
        </div>
    </div>

    {{-- Table --}}
    @if($forms->count() > 0)
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('No. Form') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Teknisi') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden md:table-cell">{{ __('Pengguna') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">{{ __('Kondisi') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">{{ __('Kelengkapan') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden xl:table-cell">{{ __('Tanggal') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($forms as $form)
                            <tr class="transition-colors duration-150">
                                <td class="px-4 py-3 font-mono text-secondary text-xs">{{ $form->nomor_form }}</td>
                                <td class="px-4 py-3 text-primary">{{ $form->teknisi->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-primary hidden md:table-cell">{{ $form->pengguna->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-secondary text-xs hidden lg:table-cell">{{ $this->getKondisiLabel($form->kondisi) }}</td>
                                <td class="px-4 py-3 text-secondary text-xs hidden lg:table-cell">{{ $this->getKelengkapanLabel($form->kelengkapan) }}</td>
                                <td class="px-4 py-3 text-secondary text-xs hidden xl:table-cell">{{ $form->submitted_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button wire:click="viewForm({{ $form->id }})" type="button"
                                        class="text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors duration-200"
                                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                                        {{ __('Detail') }}
                                    </button>
                                    <button wire:click="confirmDelete({{ $form->id }}, '{{ $form->nomor_form }}')" type="button"
                                        class="text-xs font-medium px-2.5 py-1.5 rounded-lg text-red-400 hover:bg-red-500/10 transition-colors duration-200">
                                        {{ __('Hapus') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $forms->links() }}
        </div>
    @else
        <div class="glass-card p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-3 text-muted">{{ __('Tidak ada Form Pengembalian Asset ditemukan') }}</p>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($viewingForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.closeView()">
            <div class="glass-card w-full max-w-2xl max-h-[85vh] overflow-y-auto p-6 space-y-5"
                @click.away="$wire.closeView()">

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-primary">{{ __('Detail Pengembalian Asset') }}</h2>
                        <p class="text-xs text-muted font-mono mt-0.5">{{ $viewingForm['nomor_form'] }}</p>
                    </div>
                    <button wire:click="closeView" class="text-muted hover:text-primary text-xl">&times;</button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                    <div><span class="text-muted text-xs">{{ __('Status') }}</span><p class="text-primary">{{ ucfirst($viewingForm['status']) }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Diajukan') }}</span><p class="text-primary">{{ $viewingForm['submitted_at'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Tanggal Pengembalian') }}</span><p class="text-primary">{{ $viewingForm['tanggal_pengembalian'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Teknisi') }}</span><p class="text-primary">{{ $viewingForm['teknisi']['name'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Pengguna') }}</span><p class="text-primary">{{ $viewingForm['pengguna']['name'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">NIK</span><p class="text-primary">{{ $viewingForm['pengguna']['nik'] ?? '-' }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Kondisi') }}</span><p class="text-primary">{{ $this->getKondisiLabel($viewingForm['kondisi']) }}</p></div>
                    <div><span class="text-muted text-xs">{{ __('Kelengkapan') }}</span><p class="text-primary">{{ $this->getKelengkapanLabel($viewingForm['kelengkapan']) }}</p></div>
                </div>

                {{-- Items --}}
                <div>
                    <h3 class="text-xs font-semibold text-muted uppercase mb-2">{{ __('Asset Dikembalikan') }} ({{ count($viewingForm['items']) }})</h3>
                    <div class="space-y-1">
                        @foreach($viewingForm['items'] as $item)
                            <div class="flex items-center justify-between py-1.5 px-3 rounded text-xs" style="background: var(--color-bg-tertiary);">
                                <span class="text-primary">{{ $item['nama_perangkat'] ?? '-' }}</span>
                                <span class="text-muted font-mono">{{ $item['no_asset'] ?? '-' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Notes --}}
                @if($viewingForm['notes'])
                    <div>
                        <h3 class="text-xs font-semibold text-muted uppercase mb-1">{{ __('Catatan') }}</h3>
                        <p class="text-sm text-primary px-3 py-2 rounded" style="background: var(--color-bg-tertiary);">{{ $viewingForm['notes'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus Form Pengembalian') }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus') }} <span class="font-semibold text-primary font-mono">{{ $deleteNomorForm }}</span>? {{ __('Item terkait juga akan dihapus.') }}</p>
                <div class="flex gap-2">
                    <button wire:click="cancelDelete" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="delete" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Hapus') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>

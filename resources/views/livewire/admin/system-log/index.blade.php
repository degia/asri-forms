<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('System Log') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('File log aplikasi (storage/logs/laravel.log)') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted">
                @if($fileSize > 0)
                    {{ round($fileSize / 1024, 1) }} KB
                @endif
            </span>
            <button wire:click="download"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('Download') }}
            </button>
            <button wire:click="clear" wire:confirm="{{ __('Hapus semua isi file log?') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: rgb(239, 68, 68);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Clear') }}
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3 rounded-lg text-sm" style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.2); color: rgb(52, 211, 153);">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-card p-4">
        <div class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari pesan log...') }}"
                    class="w-full pl-10 pr-4 py-2 rounded-lg text-sm bg-transparent border"
                    style="border-color: var(--color-border); color: var(--color-text-primary);">
            </div>
            <select wire:model.live="filterLevel"
                class="px-3 py-2 rounded-lg text-sm bg-transparent border"
                style="border-color: var(--color-border); color: var(--color-text-primary);">
                <option value="">{{ __('Semua Level') }}</option>
                @foreach($levels as $level)
                    <option value="{{ $level }}">{{ strtoupper($level) }}</option>
                @endforeach
            </select>
            <select wire:model.live="maxLines"
                class="px-3 py-2 rounded-lg text-sm bg-transparent border"
                style="border-color: var(--color-border); color: var(--color-text-primary);">
                <option value="100">{{ __('100 baris') }}</option>
                <option value="500">{{ __('500 baris') }}</option>
                <option value="1000">{{ __('1000 baris') }}</option>
                <option value="5000">{{ __('5000 baris') }}</option>
            </select>
        </div>
    </div>

    @if($loading)
        <div class="glass-card p-8 text-center">
            <div class="inline-block w-5 h-5 border-2 border-t-transparent rounded-full animate-spin" style="border-color: var(--color-primary); border-top-color: transparent;"></div>
            <p class="text-sm text-muted mt-2">{{ __('Memuat log...') }}</p>
        </div>
    @elseif(empty($entries))
        <div class="glass-card p-8 text-center">
            <svg class="w-10 h-10 mx-auto text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-muted">{{ __('Tidak ada entri log') }}</p>
        </div>
    @else
        <div class="glass-card p-4">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Waktu') }}</th>
                            <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Level') }}</th>
                            <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Pesan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($entries as $entry)
                            <tr class="hover:opacity-80 transition-opacity">
                                <td class="px-3 py-2 text-muted whitespace-nowrap font-mono">{{ $entry['datetime'] }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                        style="background: {{ match($entry['level']) { 'error' => 'rgba(239, 68, 68, 0.15)', 'warning' => 'rgba(245, 158, 11, 0.15)', 'info' => 'rgba(59, 130, 246, 0.15)', 'debug' => 'rgba(148, 163, 184, 0.15)', default => 'rgba(148, 163, 184, 0.15)' } }};
                                        color: {{ match($entry['level']) { 'error' => 'rgb(239, 68, 68)', 'warning' => 'rgb(245, 158, 11)', 'info' => 'rgb(59, 130, 246)', 'debug' => 'rgb(148, 163, 184)', default => 'rgb(148, 163, 184)' } }};
                                        ">
                                        {{ strtoupper($entry['level']) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-secondary font-mono break-all">{{ $entry['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-muted mt-3">{{ __('Menampilkan') }} {{ count($entries) }} {{ __('entri') }}</p>
        </div>
    @endif
</div>

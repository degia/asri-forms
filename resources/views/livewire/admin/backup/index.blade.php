<div class="space-y-6" x-data x-on:backup-deleted.window="$wire.$refresh()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Backup Data') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Buat dan unduh cadangan database serta file penyimpanan') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="createBackup" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <svg wire:loading.remove wire:target="createBackup" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <svg wire:loading wire:target="createBackup" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span wire:loading.remove wire:target="createBackup">{{ __('Buat Backup Baru') }}</span>
                <span wire:loading wire:target="createBackup">{{ __('Membuat') }}...</span>
            </button>
        </div>
    </div>

    @if($errorMessage)
        <div class="px-4 py-3 rounded-lg text-sm" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);">
            {{ $errorMessage }}
        </div>
    @endif

    @if($successMessage)
        <div class="px-4 py-3 rounded-lg text-sm" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3);">
            {{ $successMessage }}
        </div>
    @endif

    {{-- Upload & Restore --}}
    <div class="glass-card p-4">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-sm font-bold text-primary">{{ __('Upload & Restore Database') }}</h2>
                <p class="text-xs text-muted mt-0.5">{{ __('Upload file .sql atau .zip untuk mengembalikan database') }}</p>
            </div>
            <form wire:submit="uploadAndRestore" class="flex items-center gap-2 flex-wrap">
                <input type="file" wire:model="uploadedFile" accept=".sql,.zip"
                    class="block text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:cursor-pointer"
                    style="color: var(--color-text-secondary);">
                @error('uploadedFile') <span class="text-xs" style="color: #ef4444;">{{ $message }}</span> @enderror
                <span wire:loading wire:target="uploadedFile" class="inline-flex items-center gap-1.5 text-xs" style="color: var(--color-primary);">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>{{ __('Mengunggah') }}...</span>
                </span>
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                    style="background: #f59e0b; color: white;">
                    <svg wire:loading.remove wire:target="uploadAndRestore" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <svg wire:loading wire:target="uploadAndRestore" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span wire:loading.remove wire:target="uploadAndRestore">{{ __('Restore') }}</span>
                    <span wire:loading wire:target="uploadAndRestore">{{ __('Merestore') }}...</span>
                </button>
            </form>
        </div>
    </div>

    @if($isRestoring || $isCreating)
        <div class="glass-card p-6 text-center">
            <svg class="w-8 h-8 mx-auto mb-3 animate-spin" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <p class="text-sm text-muted">{{ $isRestoring ? __('Sedang merestore database, mohon tunggu') . '...' : __('Sedang membuat backup, mohon tunggu') . '...' }}</p>
        </div>
    @endif

    <div class="glass-card p-4">
        @if(count($this->backups) === 0)
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
                <p class="text-sm text-muted">{{ __('Belum ada backup tersedia') }}</p>
                <p class="text-xs text-muted mt-1">{{ __('Klik tombol "Buat Backup Baru" untuk memulai') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="text-left py-2.5 px-3 text-xs font-medium text-muted">{{ __('Nama File') }}</th>
                            <th class="text-left py-2.5 px-3 text-xs font-medium text-muted">{{ __('Ukuran') }}</th>
                            <th class="text-left py-2.5 px-3 text-xs font-medium text-muted">{{ __('Tanggal Dibuat') }}</th>
                            <th class="text-right py-2.5 px-3 text-xs font-medium text-muted">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: color-mix(in srgb, var(--color-border) 25%, transparent);">
                        @foreach($this->backups as $backup)
                            <tr class="transition-colors" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor='transparent'">
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        <span class="font-mono text-xs text-primary">{{ $backup['filename'] }}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-xs text-muted">{{ $backup['size_formatted'] }}</td>
                                <td class="py-2.5 px-3 text-xs text-muted">{{ $backup['created_at'] }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.backup.download', $backup['filename']) }}"
                                            class="p-1.5 rounded transition-colors hover:opacity-80"
                                            style="color: var(--color-primary);"
                                            title="{{ __('Download') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                        <button wire:click="restoreBackup('{{ $backup['filename'] }}')"
                                            wire:confirm="{{ __('PERHATIAN! Merestore backup akan MENIMPA SELURUH DATA database saat ini. Lanjutkan?') }}"
                                            class="p-1.5 rounded transition-colors hover:opacity-80"
                                            wire:loading.attr="disabled"
                                            style="color: #f59e0b;"
                                            title="{{ __('Restore Database') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                            wire:confirm="{{ __('Yakin ingin menghapus backup') }} {{ $backup['filename'] }}?"
                                            class="p-1.5 rounded transition-colors hover:opacity-80"
                                            style="color: #ef4444;"
                                            title="{{ __('Hapus') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

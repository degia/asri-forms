<div class="space-y-6">
    {{-- Toast Notification --}}
    <div x-data="{ toast: false, message: '', type: 'success' }"
        @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)"
        x-on:livewire-upload-error.window="toast = true; message = '{{ __('Gagal mengunggah file CSV. Periksa ukuran (maks 10MB) dan format file, lalu coba lagi.') }}'; type = 'error'; setTimeout(() => toast = false, 4000)"
        x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'" x-text="message">
    </div>

    {{-- Upload Section --}}
    @if (!$imported)
        <div class="glass-card p-6 space-y-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-primary">{{ __('Upload File CSV') }}</h3>
                    <p class="text-xs text-muted">Format: name, email, password, nik, role, status (Access Login: Enable/Disable)</p>                </div>
            </div>

            <div class="relative border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200"
                style="border-color: var(--color-border);" x-data="{ dragging: false }"
                x-on:dragover.prevent="dragging = true" x-on:dragleave="dragging = false"
                x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                :class="{ 'border-blue-400 bg-blue-500/5': dragging }">

                <svg class="w-10 h-10 mx-auto text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm text-secondary mb-2">{{ __('Seret file ke sini atau klik untuk memilih') }}</p>
                <p class="text-xs text-muted">Maksimal 10MB (.csv)</p>

                <input type="file" wire:model="file" x-ref="fileInput" accept=".csv,.txt"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
            </div>

            <div x-data="{ progress: 0, uploading: false, timedOut: false, timer: null }"
                x-on:livewire-upload-start.window="uploading = true; progress = 0; timedOut = false; clearTimeout(timer)"
                x-on:livewire-upload-progress.window="progress = $event.detail.progress"
                x-on:livewire-upload-finish.window="clearTimeout(timer); uploading = false"
                x-on:livewire-upload-error.window="clearTimeout(timer); uploading = false"
                x-effect="if (progress >= 100 && !timer && uploading) { timer = setTimeout(() => { uploading = false; timedOut = true; }, 20000); }"
                x-show="uploading || timedOut" class="flex flex-col items-center gap-2 text-xs"
                style="color: var(--color-primary);">
                <div class="flex items-center gap-2">
                    <svg x-show="!timedOut" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-show="!timedOut"
                        x-text="progress >= 100 ? '{{ __('Memproses file, mohon tunggu') }}...' : '{{ __('Mengunggah file, mohon tunggu') }}... ' + progress + '%'"></span>
                    <span x-show="timedOut" style="color: var(--color-text-secondary);">{{ __('Pemrosesan memakan waktu terlalu lama.') }}</span>
                </div>
                <div class="h-1.5 w-full max-w-xs rounded-full overflow-hidden"
                    style="background: var(--color-glass-bg);">
                    <div class="h-full rounded-full transition-all duration-150"
                        style="background: var(--color-primary);" :style="'width: ' + progress + '%'"></div>
                </div>
                <template x-if="timedOut">
                    <p class="text-xs" style="color: var(--color-text-secondary);">{{ __('Muat ulang halaman lalu pilih file CSV lagi.') }}</p>
                </template>
            </div>

            @error('file')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Preview Section --}}
        @if (!empty($preview) && empty($importErrors))
            <div class="glass-card p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-primary">{{ __('Preview') }} ({{ min($totalRows, 5) }} {{ __('dari') }} {{ $totalRows }}
                        {{ __('baris') }})</h3>
                    <button wire:click="processData" wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">
                        <span wire:loading.remove wire:target="processData">{{ __('Proses Load') }}</span>
                        <span wire:loading wire:target="processData">{{ __('Memproses') }}...</span>
                    </button>
                </div>

                <div wire:loading wire:target="processData" class="flex items-center gap-2 text-xs"
                    style="color: var(--color-primary);">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>{{ __('Memvalidasi data, mohon tunggu') }}...</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b" style="border-color: var(--color-border);">
                                <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">#</th>
                                <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Nama') }}</th>
                                <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Email') }}</th>
                                <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Password') }}</th>
                                <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">NIK</th>
                                <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Role') }}</th>
                                <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Access Login') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--color-border);">
                            @foreach ($preview as $i => $row)
                                <tr>
                                    <td class="px-3 py-2 text-muted whitespace-nowrap">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 text-primary font-medium whitespace-nowrap">
                                        {{ $row['name'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-secondary whitespace-nowrap">{{ $row['email'] ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-secondary font-mono whitespace-nowrap">
                                        {{ $row['password'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-secondary font-mono whitespace-nowrap">
                                        {{ $row['nik'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-secondary whitespace-nowrap">{{ $row['role'] ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-secondary whitespace-nowrap">
                                        {{ isset($row['status']) ? ucfirst($row['status']) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Processed Confirmation --}}
        @if ($processed)
            <div class="glass-card p-6 space-y-4" style="border-color: rgba(245, 158, 11, 0.4);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="font-semibold text-primary">{{ __('Data Terbaca') }}: {{ $successCount }} {{ __('Berhasil') }},
                        {{ $errorCount }} {{ __('Gagal') }}</h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="confirmCancelImport"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors duration-200 bg-red-500 text-white hover:bg-red-600">
                            Cancelled
                        </button>
                        {{-- <button wire:click="confirmSendImport"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 bg-inherit border border-primary hover:bg-primary hover:text-white"
                            style="background: var(--color-primary); color: var(--color-button-text);">
                            Confirm & Sent ({{ $successCount }})
                        </button> --}}

                        <button wire:click="confirmSendImport"
                            class="group relative px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 bg-inherit border border-primary hover:bg-primary hover:text-white overflow-hidden"
                            style="background: var(--color-primary); color: var(--color-button-text);">

                            {{-- Text default: tampil normal, hilang saat hover --}}
                            <span
                                class="block transition-all duration-200 ease-in-out group-hover:opacity-0 group-hover:-translate-y-3">
                                Confirm & Sent ({{ $successCount }})
                            </span>

                            {{-- Text angka saja: muncul saat hover, posisi absolute menimpa text default --}}
                            <span
                                class="absolute inset-0 flex items-center justify-center transition-all duration-200 ease-in-out opacity-0 translate-y-3 group-hover:opacity-100 group-hover:translate-y-0">
                                {{ $successCount }}
                            </span>
                        </button>
                    </div>
                </div>

                <div wire:loading wire:target="confirmImport" class="flex items-center gap-2 text-xs"
                    style="color: var(--color-primary);">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>{{ __('Menyimpan data ke database, mohon tunggu') }}...</span>
                </div>

                <div class="flex items-center justify-center gap-4 text-sm">
                    <button wire:click="$set('resultTab', 'berhasil')" type="button"
                        class="px-4 py-2 rounded-xl transition-all duration-200 cursor-pointer"
                        style="{{ $resultTab === 'berhasil' ? 'background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.5);' : 'background: var(--color-glass-bg); border: 1px solid var(--color-border);' }}">
                        <span class="text-emerald-400 font-bold text-xl">{{ $successCount }}</span>
                        <p class="text-muted text-xs">{{ __('Berhasil') }}</p>
                    </button>
                    <button wire:click="$set('resultTab', 'gagal')" type="button"
                        class="px-4 py-2 rounded-xl transition-all duration-200 cursor-pointer"
                        style="{{ $resultTab === 'gagal' ? 'background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.5);' : 'background: var(--color-glass-bg); border: 1px solid var(--color-border);' }}">
                        <span class="text-red-400 font-bold text-xl">{{ $errorCount }}</span>
                        <p class="text-muted text-xs">{{ __('Gagal') }}</p>
                    </button>
                </div>

                @if ($resultTab === 'berhasil')
                    @if (count($importSuccess) > 0)
                        <div class="text-left mt-2 p-3 rounded-lg"
                            style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                            <p class="text-xs font-semibold text-emerald-400 mb-2">{{ __('Detail Data Berhasil') }}
                                ({{ count($importSuccess) }} {{ __('baris') }}):</p>
                            <div class="overflow-x-auto max-h-60 overflow-y-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b" style="border-color: var(--color-border);">
                                            <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                                {{ __('Baris') }}</th>
                                            <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                                {{ __('Nama') }}</th>
                                            <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                                {{ __('Email') }}</th>
                                            <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                                NIK</th>
                                            <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                                {{ __('Role') }}</th>
                                            <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                                {{ __('Access Login') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="border-color: var(--color-border);">
                                        @foreach ($importSuccess as $row)
                                            <tr>
                                                <td class="px-2 py-1.5 text-muted whitespace-nowrap">
                                                    {{ $row['row'] }}</td>
                                                <td class="px-2 py-1.5 text-primary font-medium whitespace-nowrap">
                                                    {{ $row['data']['name'] }}</td>
                                                <td class="px-2 py-1.5 text-secondary whitespace-nowrap">
                                                    {{ $row['data']['email'] }}</td>
                                                <td class="px-2 py-1.5 text-secondary font-mono whitespace-nowrap">
                                                    {{ $row['data']['nik'] }}</td>
                                                <td class="px-2 py-1.5 text-secondary whitespace-nowrap">
                                                    {{ $row['data']['role'] }}</td>
                                                <td class="px-2 py-1.5 text-secondary whitespace-nowrap">
                                                    {{ $row['data']['status'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-muted mt-2">{{ __('Tidak ada data berhasil.') }}</p>
                    @endif
                @else
                    @if (!empty($importErrors))
                        <div class="text-left mt-2 p-3 rounded-lg"
                            style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                            <p class="text-xs font-semibold text-red-400 mb-2">{{ __('Detail Error') }} ({{ $errorCount }} {{ __('baris gagal') }}):</p>
                            @php
                                $errorGroups = [];
                                foreach ($importErrors as $error) {
                                    $key = preg_replace('/^Baris \d+: /', '', trim($error));
                                    $errorGroups[$key] = ($errorGroups[$key] ?? 0) + 1;
                                }
                            @endphp
                            <div class="space-y-1 mb-2">
                                @foreach ($errorGroups as $message => $count)
                                    <p class="text-xs text-red-400">• {{ $message }} <span
                                            class="text-muted">({{ $count }}×)</span></p>
                                @endforeach
                            </div>
                            <details>
                                <summary class="text-xs text-secondary cursor-pointer hover:text-primary">{{ __('Lihat detail per baris') }}</summary>
                                <div class="max-h-40 overflow-y-auto mt-2 space-y-1">
                                    @foreach ($importErrors as $error)
                                        <p class="text-xs text-red-400">• {{ $error }}</p>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @else
                        <p class="text-sm text-muted mt-2">{{ __('Tidak ada data gagal.') }}</p>
                    @endif
                @endif
            </div>
        @endif

        {{-- Errors --}}
        @if (!empty($importErrors) && !$processed)
            <div class="glass-card p-6 space-y-3" style="border-color: rgba(248, 113, 113, 0.4);">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-red-400">{{ __('Upload gagal / data tidak valid') }}</h3>
                    <span
                        class="text-xs font-semibold text-red-400 bg-red-500/10 px-2 py-1 rounded-full">{{ count($importErrors) }}
                        {{ __('masalah') }}</span>
                </div>
                <div class="space-y-1 max-h-60 overflow-y-auto">
                    @foreach ($importErrors as $error)
                        <p class="text-xs text-red-400">• {{ $error }}</p>
                    @endforeach
                </div>
                <button wire:click="resetImport"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    {{ __('Pilih File Lain / Coba Lagi') }}
                </button>
            </div>
        @endif
    @endif

    {{-- Import Result --}}
    @if ($imported)
        <div class="glass-card p-8 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center"
                style="background: rgba(52, 211, 153, 0.15);">
                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-primary">{{ __('Import Selesai') }}</h3>
            <div class="flex items-center justify-center gap-4 text-sm">
                <button wire:click="$set('resultTab', 'berhasil')" type="button"
                    class="px-4 py-2 rounded-xl transition-all duration-200 cursor-pointer"
                    style="{{ $resultTab === 'berhasil' ? 'background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.5);' : 'background: var(--color-glass-bg); border: 1px solid var(--color-border);' }}">
                    <span class="text-emerald-400 font-bold text-xl">{{ $successCount }}</span>
                    <p class="text-muted text-xs">{{ __('Berhasil') }}</p>
                </button>
                <button wire:click="$set('resultTab', 'gagal')" type="button"
                    class="px-4 py-2 rounded-xl transition-all duration-200 cursor-pointer"
                    style="{{ $resultTab === 'gagal' ? 'background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.5);' : 'background: var(--color-glass-bg); border: 1px solid var(--color-border);' }}">
                    <span class="text-red-400 font-bold text-xl">{{ $errorCount }}</span>
                    <p class="text-muted text-xs">{{ __('Gagal') }}</p>
                </button>
            </div>

            @if ($resultTab === 'berhasil')
                @if (count($importSuccess) > 0)
                    <div class="text-left mt-4 p-3 rounded-lg"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                        <p class="text-xs font-semibold text-emerald-400 mb-2">{{ __('Detail Data Berhasil') }}
                            ({{ count($importSuccess) }} {{ __('baris') }}):</p>
                        <div class="overflow-x-auto max-h-60 overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b" style="border-color: var(--color-border);">
                                        <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                            {{ __('Baris') }}</th>
                                        <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">{{ __('Nama') }}
                                        </th>
                                        <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">
                                            {{ __('Email') }}</th>
                                        <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">NIK
                                        </th>
                                        <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">{{ __('Role') }}
                                        </th>
                                        <th class="px-2 py-1.5 text-left text-muted font-medium whitespace-nowrap">{{ __('Access Login') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--color-border);">
                                    @foreach ($importSuccess as $row)
                                        <tr>
                                            <td class="px-2 py-1.5 text-muted whitespace-nowrap">{{ $row['row'] }}
                                            </td>
                                            <td class="px-2 py-1.5 text-primary font-medium whitespace-nowrap">
                                                {{ $row['data']['name'] }}</td>
                                            <td class="px-2 py-1.5 text-secondary whitespace-nowrap">
                                                {{ $row['data']['email'] }}</td>
                                            <td class="px-2 py-1.5 text-secondary font-mono whitespace-nowrap">
                                                {{ $row['data']['nik'] }}</td>
                                            <td class="px-2 py-1.5 text-secondary whitespace-nowrap">
                                                {{ $row['data']['role'] }}</td>
                                            <td class="px-2 py-1.5 text-secondary whitespace-nowrap">
                                                {{ $row['data']['status'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-muted mt-4">{{ __('Tidak ada data berhasil.') }}</p>
                @endif
            @else
                @if (!empty($importErrors))
                    <div class="text-left mt-4 p-3 rounded-lg"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                        <p class="text-xs font-semibold text-red-400 mb-2">{{ __('Detail Error') }} ({{ $errorCount }} {{ __('baris gagal') }}):</p>
                        @php
                            $errorGroups = [];
                            foreach ($importErrors as $error) {
                                $key = preg_replace('/^Baris \d+: /', '', trim($error));
                                $errorGroups[$key] = ($errorGroups[$key] ?? 0) + 1;
                            }
                        @endphp
                        <div class="space-y-1 mb-2">
                            @foreach ($errorGroups as $message => $count)
                                <p class="text-xs text-red-400">• {{ $message }} <span
                                        class="text-muted">({{ $count }}×)</span></p>
                            @endforeach
                        </div>
                        <details>
                            <summary class="text-xs text-secondary cursor-pointer hover:text-primary">{{ __('Lihat detail per baris') }}</summary>
                            <div class="max-h-40 overflow-y-auto mt-2 space-y-1">
                                @foreach ($importErrors as $error)
                                    <p class="text-xs text-red-400">• {{ $error }}</p>
                                @endforeach
                            </div>
                        </details>
                    </div>
                @else
                    <p class="text-sm text-muted mt-4">{{ __('Tidak ada data gagal.') }}</p>
                @endif
            @endif

            @if ($errorCount > 0 && $successCount === 0)
                <p class="text-sm font-medium text-red-400">{{ __('Seluruh data gagal diimport. Tidak ada data yang ditambahkan.') }}</p>
            @endif

            <div class="flex items-center justify-center gap-3 pt-2">
                <a href="{{ route('admin.users.index') }}" wire:navigate
                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                    style="background: var(--color-primary); color: var(--color-button-text);">
                    {{ __('Lihat Users') }}
                </a>
                <button wire:click="resetImport"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    {{ __('Import Lagi') }}
                </button>
                <button wire:click="confirmCancelImport"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200 bg-red-500 text-white hover:bg-red-600">
                    {{ __('Batalkan Import') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Cancel Import Confirmation Modal --}}
    @if ($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);" x-data
            x-on:keydown.escape.window="$wire.dismissCancelImport()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.dismissCancelImport()">
                <h3 class="text-lg font-bold text-primary">{{ __('Batalkan Import') }}</h3>
                @if ($imported)
                    <p class="text-sm text-muted">{{ __('Yakin ingin membatalkan import? Seluruh') }} <span
                            class="font-semibold text-primary">{{ $successCount }} {{ __('data user') }}</span> {{ __('yang baru saja diimpor akan dihapus dari database.') }}</p>
                @else
                    <p class="text-sm text-muted">{{ __('Yakin ingin membatalkan import? Data yang sudah terbaca akan dibuang dan tidak akan dikirim ke database.') }}</p>
                @endif
                <div class="flex gap-2">
                    <button wire:click="dismissCancelImport" type="button"
                        class="glass-button-secondary text-sm flex-1">{{ __('Tidak') }}</button>
                    <button wire:click="cancelImport" type="button"
                        class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Ya, Batalkan') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirm Import Confirmation Modal --}}
    @if ($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);" x-data
            x-on:keydown.escape.window="$wire.dismissConfirmImport()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.dismissConfirmImport()">
                <h3 class="text-lg font-bold text-primary">{{ __('Konfirmasi Kirim Data') }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin mengirim') }} <span
                        class="font-semibold text-primary">{{ $successCount }} {{ __('data user') }}</span> {{ __('ke database? Setelah dikirim, Anda akan diarahkan ke halaman Users.') }}</p>
                <div class="flex gap-2">
                    <button wire:click="dismissConfirmImport" type="button"
                        class="glass-button-secondary text-sm flex-1">{{ __('Tidak') }}</button>
                    <button wire:click="confirmImport" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">
                        <span wire:loading.remove wire:target="confirmImport">{{ __('Ya, Kirim') }}</span>
                        <span wire:loading wire:target="confirmImport">{{ __('Mengirim') }}...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

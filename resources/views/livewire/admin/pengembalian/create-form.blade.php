<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold" style="color: var(--color-text-primary);">{{ __('Form Pengembalian Asset') }}</h1>
            <p class="text-sm mt-1" style="color: var(--color-text-secondary);">{{ __('Kembalikan asset yang masih terpasang sebelum user diubah statusnya menjadi Resigned.') }}</p>
        </div>
        <a href="{{ route('admin.pengembalian.index') }}" wire:navigate
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
            {{ __('Kembali') }}
        </a>
    </div>

    @if (session()->has('success'))
        <div class="p-3 rounded-lg text-sm"
            style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif

    @error('submit') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror

    <form wire:submit="submit" class="space-y-5">
        {{-- Teknisi --}}
        <div class="glass-card p-5">
            <h2 class="text-sm font-semibold mb-3" style="color: var(--color-text-primary);">{{ __('Dibuat Oleh (Teknisi)') }}</h2>
            <p class="text-sm" style="color: var(--color-text-secondary);">
                {{ Auth::user()->name }} — {{ Auth::user()->email }}
            </p>
        </div>

        {{-- Pilih User --}}
        <div class="glass-card p-5">
            <h2 class="text-sm font-semibold mb-3" style="color: var(--color-text-primary);">{{ __('User yang Mengembalikan Asset') }}</h2>

            @if ($penggunaId)
                <div class="p-3 rounded-lg"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--color-text-primary);">{{ $penggunaName }}</p>
                            <p class="text-xs mt-0.5" style="color: var(--color-text-secondary);">
                                {{ $penggunaNik ? 'NIK ' . $penggunaNik . ' • ' : '' }}{{ $penggunaEmail }}
                            </p>
                        </div>
                        <button type="button" wire:click="clearPengguna"
                            class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors duration-200"
                            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                            {{ __('Ganti User') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="relative">
                    <input wire:model.live="penggunaSearch" wire:keyup="searchPengguna" type="text" placeholder="{{ __('Cari nama, NIK, atau email...') }}"
                        class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                    @if ($showPenggunaDropdown && count($penggunaResults))
                        <div class="absolute z-20 w-full mt-1 rounded-lg overflow-hidden shadow-lg"
                            style="background: var(--color-bg-secondary, #ffffff); border: 1px solid var(--color-border);">
                            @foreach ($penggunaResults as $result)
                                <button type="button" wire:click="selectPengguna('{{ addslashes($result['nik']) }}')"
                                    class="w-full text-left px-3 py-2 text-sm transition-colors duration-200"
                                    style="color: var(--color-text-primary);">
                                    <span class="font-medium">{{ $result['name'] }}</span>
                                    <span class="block text-xs" style="color: var(--color-text-secondary);">
                                        {{ $result['nik'] ? 'NIK ' . $result['nik'] . ' • ' : '' }}{{ $result['email'] ?? '' }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <p class="text-xs mt-2" style="color: var(--color-text-secondary);">{{ __('Hanya employee berstatus Active yang bisa dipilih.') }}</p>
                @error('penggunaId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            @endif
        </div>

        {{-- Daftar Asset --}}
        @if ($penggunaId)
            <div class="glass-card p-5">
                <h2 class="text-sm font-semibold mb-1" style="color: var(--color-text-primary);">{{ __('Asset Terpasang') }}</h2>
                <p class="text-xs mb-3" style="color: var(--color-text-secondary);">{{ __('Centang asset yang dikembalikan. Asset akan di-unassign dan statusnya menjadi inactive.') }}</p>

                @if (count($availableAssets) === 0)
                    <div class="p-3 rounded-lg text-sm"
                        style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e;">
                        {{ __('User ini tidak memiliki asset terpasang. Siap untuk di-resign.') }}
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($availableAssets as $index => $asset)
                            <label class="flex items-start gap-3 p-3 rounded-lg cursor-pointer"
                                style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                                <input type="checkbox" wire:model.live="selectedAssets" value="{{ $asset['id'] }}"
                                    class="mt-0.5 accent-current" />
                                <div>
                                    <p class="text-sm font-medium" style="color: var(--color-text-primary);">{{ $asset['nama_perangkat'] ?? '-' }}</p>
                                    <p class="text-xs mt-0.5" style="color: var(--color-text-secondary);">
                                        {{ $asset['no_asset'] ?? '-' }}
                                        @if (($asset['brand'] ?? '') || ($asset['tipe'] ?? ''))
                                            • {{ $asset['brand'] ?? '' }} {{ $asset['tipe'] ?? '' }}
                                        @endif
                                        @if ($asset['no_serial'] ?? '')
                                            • SN: {{ $asset['no_serial'] }}
                                        @endif
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedAssets') <p class="text-xs text-red-400 mt-2">{{ $message }}</p> @enderror
                @endif
            </div>
        @endif

        {{-- Detail Pengembalian --}}
        <div class="glass-card p-5">
            <h2 class="text-sm font-semibold mb-3" style="color: var(--color-text-primary);">{{ __('Detail Pengembalian') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs text-muted">{{ __('Tanggal Pengembalian') }}</label>
                    <input wire:model="tanggalPengembalian" type="date"
                        class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                    @error('tanggalPengembalian') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-muted">{{ __('Kondisi Asset') }}</label>
                    <select wire:model="kondisi"
                        class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Kondisi') }}</option>
                        <option value="baik">Baik</option>
                        <option value="rusak">Rusak</option>
                        <option value="hilang">Hilang</option>
                    </select>
                    @error('kondisi') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-muted">{{ __('Kelengkapan') }}</label>
                    <select wire:model="kelengkapan"
                        class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Kelengkapan') }}</option>
                        <option value="lengkap">Lengkap</option>
                        <option value="tidak_lengkap">Tidak Lengkap</option>
                    </select>
                    @error('kelengkapan') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4">
                <label class="text-xs text-muted">{{ __('Catatan') }}</label>
                <textarea wire:model="notes" rows="3"
                    class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                    placeholder="{{ __('Catatan tambahan (opsional)') }}"></textarea>
                @error('notes') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <span wire:loading.remove wire:target="submit">{{ __('Simpan Pengembalian') }}</span>
                <span wire:loading wire:target="submit">{{ __('Menyimpan') }}...</span>
            </button>
            <a href="{{ route('admin.pengembalian.index') }}" wire:navigate
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ __('Batal') }}
            </a>
        </div>
    </form>
</div>

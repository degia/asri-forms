<div
    x-data="{
        openStep: @entangle('currentStep'),
        showUpload: null,
        get isDark() { return document.documentElement.classList.contains('dark') },
    }"
    x-init=""
    @asset-found.window="showToast('Aset ditemukan: ' + $event.detail.asset.nama_perangkat, 'success')"
    @asset-not-found.window="showToast('Aset tidak ditemukan untuk kode: ' + $event.detail.code, 'error')"
    @asset-created.window="showToast('Asset baru berhasil dibuat: ' + $event.detail.name, 'success')"
    @draft-saved.window="showToast('Draft tersimpan', 'success')"
    @form-submitted.window="showToast('Form berhasil disubmit!', 'success')"
    @submit-error.window="showToast('Gagal submit: ' + $event.detail.message, 'error')"
    class="max-w-4xl mx-auto px-4 py-6 space-y-4"
>

    {{-- Toast Notification --}}
    <div x-data="{ toast: false, message: '', type: 'success' }"
        @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 3000)"
        x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message"
    ></div>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary">Form Pemeriksaan Perangkat</h1>
            <p class="text-sm text-muted mt-1">
                No. Form: <span class="font-mono font-semibold text-secondary">{{ $this->getFormNumberPreview() }}</span>
            </p>
        </div>
        <div class="flex gap-2">
            <button wire:click="saveDraft" type="button"
                class="glass-button-secondary text-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Simpan Draft
            </button>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if($currentStep > 0)
    <div class="glass-card p-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-muted">Langkah {{ $currentStep }} dari {{ count($stepTitles) }}</span>
            <span class="text-xs font-medium text-secondary">{{ round(($currentStep / count($stepTitles)) * 100) }}%</span>
        </div>
        <div class="w-full h-2 rounded-full" style="background: var(--color-bg-tertiary);">
            <div class="h-2 rounded-full transition-all duration-500"
                style="background: var(--color-text-primary);"
                :style="'width: {{ round(($currentStep / count($stepTitles)) * 100) }}%'">
            </div>
        </div>
    </div>
    @endif

    {{-- Accordion Steps --}}
    <div class="space-y-3">

        {{-- STEP 1: Info Pengguna --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(1)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 1 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 1 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">1</span>
                    <div>
                        <span class="font-semibold text-primary text-sm">Info Pengguna</span>
                        <p class="text-xs text-muted" x-show="openStep !== 1">
                            {{ $teknisiName }} @if($penggunaName) → {{ $penggunaName }} @endif
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 1 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 1" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 space-y-4 border-t" style="border-color: var(--color-border);">
                    {{-- Teknisi (otomatis dari login) --}}
                    <div class="pt-4">
                        <h4 class="text-xs font-semibold text-muted uppercase tracking-wider mb-3">Teknisi (Pemeriksa)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-muted">Nama</label>
                                <input type="text" wire:model.live="teknisiName"
                                    class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-muted">NIK</label>
                                <input type="text" wire:model.live="teknisiNik"
                                    class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-muted">Site</label>
                                <input type="text" wire:model.live="teknisiSite"
                                    class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-1">
                            </div>
                        </div>
                    </div>

                    {{-- Pengguna --}}
                    <div>
                        <h4 class="text-xs font-semibold text-muted uppercase tracking-wider mb-3">Pengguna Perangkat</h4>
                        @if($penggunaId)
                            <div class="glass-card p-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-primary">{{ $penggunaName }}</p>
                                    <p class="text-xs text-muted">{{ $penggunaNik }} · {{ $penggunaEmail }}</p>
                                </div>
                                <button wire:click="clearPengguna" type="button"
                                    class="text-xs text-red-400 hover:text-red-300">Ganti</button>
                            </div>
                        @else
                            <div class="relative">
                                <input type="text" wire:model.live="penggunaSearch"
                                    wire:input.debounce.300ms="searchPengguna"
                                    placeholder="Cari nama, NIK, atau email..."
                                    class="glass-input w-full rounded-lg px-3 py-2 text-sm">
                                @if($showPenggunaDropdown && count($penggunaResults) > 0)
                                    <div class="absolute z-20 mt-1 w-full rounded-lg shadow-lg max-h-48 overflow-auto"
                                        style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">
                                        @foreach($penggunaResults as $u)
                                            <button wire:click="selectPengguna('{{ addslashes($u['nik']) }}')" type="button"
                                                class="w-full text-left px-3 py-2 text-sm hover:opacity-80 transition"
                                                style="color: var(--color-text-primary);">
                                                <span class="font-medium">{{ $u['name'] }}</span>
                                                <span class="text-xs text-muted ml-2">{{ $u['nik'] ?? '' }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($showPenggunaDropdown && strlen($penggunaSearch) >= 2 && count($penggunaResults) === 0)
                                    <div class="absolute z-20 mt-1 w-full rounded-lg shadow-lg"
                                        style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">
                                        <button wire:click="openCreatePengguna" type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:opacity-80 transition flex items-center gap-2"
                                            style="color: var(--color-text-primary);">
                                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Tambah Pengguna Baru: <strong>{{ $penggunaSearch }}</strong></span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if($showCreatePengguna)
                                <div class="glass-card p-3 mt-2 space-y-2" style="border: 1px solid var(--color-border);">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-semibold text-primary">Tambah Pengguna Baru</p>
                                        <button wire:click="closeCreatePengguna" type="button" class="text-xs text-red-400 hover:text-red-300">Batal</button>
                                    </div>
                                    <div>
                                        <label class="text-xs text-muted">Nama <span class="text-red-400">*</span></label>
                                        <input type="text" wire:model.live="newPenggunaName" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="Nama lengkap">
                                        @error('newPenggunaName') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-muted">Email</label>
                                        <div class="relative">
                                            <input type="email" wire:model.live="newPenggunaEmail"
                                                wire:input.debounce.300ms="searchNewPenggunaEmail"
                                                class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1"
                                                placeholder="Cari atau ketik email...">
                                            @if($showEmailDropdown && count($emailSearchResults) > 0)
                                                <div class="absolute z-20 mt-1 w-full rounded-lg shadow-lg max-h-40 overflow-auto"
                                                    style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">
                                                    @foreach($emailSearchResults as $u)
                                                        <button wire:click="selectNewPenggunaEmail('{{ addslashes($u['email']) }}')" type="button"
                                                            class="w-full text-left px-3 py-2 text-sm hover:opacity-80 transition"
                                                            style="color: var(--color-text-primary);">
                                                            <span class="font-medium">{{ $u['email'] }}</span>
                                                            <span class="text-xs text-muted ml-2">{{ $u['name'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('newPenggunaEmail') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                        @if ($this->newPenggunaEmailUsedByEmployee)
                                            <div class="mt-1.5 text-xs text-red-400">Email sudah terpakai pada employee "{{ $this->newPenggunaEmailUsedByEmployee }}".</div>
                                        @elseif ($this->isNewPenggunaEmailUnregistered)
                                            <div class="flex items-center justify-between gap-2 mt-1.5">
                                                <span class="text-xs text-amber-400">Email belum terdaftar sebagai akun user.</span>
                                                <button wire:click="openAddUserPopup" type="button" class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                                                    + Tambah
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-xs text-muted">NIK</label>
                                            <input type="text" wire:model.live="newPenggunaNik" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="Opsional">
                                        </div>
                                        <div>
                                            <label class="text-xs text-muted">Site</label>
                                            <select wire:model.live="newPenggunaSite" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1">
                                                <option value="">Pilih Site</option>
                                                @foreach($sites as $s)
                                                    <option value="{{ $s['id_site'] }}">{{ $s['site'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button wire:click="createPengguna" type="button"
                                        class="glass-button-primary text-xs w-full py-1.5 mt-1"
                                        @if($this->newPenggunaEmailUsedByEmployee) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                        Simpan & Pilih Pengguna
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="flex justify-end pt-2">
                        <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">
                            Selanjutnya →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2: Info Perangkat --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(2)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 2 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 2 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">2</span>
                    <div>
                        <span class="font-semibold text-primary text-sm">Info Perangkat</span>
                        <p class="text-xs text-muted" x-show="openStep !== 2 && $wire.noAsset">
                            {{ $noAsset }} · {{ $brand }} {{ $tipe }}
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 2 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 2" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 space-y-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4">
                        <h4 class="text-xs font-semibold text-muted uppercase tracking-wider mb-3">Data Perangkat</h4>

                        @if($assetId)
                            <div class="glass-card p-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-primary">{{ $namaPerangkat }}</p>
                                    <p class="text-xs text-muted">{{ $noAsset }} · {{ $brand }} {{ $tipe }}</p>
                                </div>
                                <button wire:click="clearAsset" type="button"
                                    class="text-xs text-red-400 hover:text-red-300">Ganti</button>
                            </div>
                        @else
                            <div class="relative">
                                <input type="text" wire:model.live="assetSearch"
                                    wire:input.debounce.300ms="searchAsset"
                                    placeholder="Cari No. Asset, Nama Perangkat, Brand, atau Tipe..."
                                    class="glass-input w-full rounded-lg px-3 py-2 text-sm">
                                @if($showAssetDropdown && count($assetResults) > 0)
                                    <div class="absolute z-20 mt-1 w-full rounded-lg shadow-lg max-h-48 overflow-auto"
                                        style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">
                                        @foreach($assetResults as $a)
                                            <button wire:click="selectAsset({{ $a['id'] }})" type="button"
                                                class="w-full text-left px-3 py-2 text-sm hover:opacity-80 transition"
                                                style="color: var(--color-text-primary);">
                                                <span class="font-medium">{{ $a['no_asset'] }}</span>
                                                <span class="text-xs text-muted ml-2">{{ $a['nama_perangkat'] }} · {{ $a['brand'] }} {{ $a['tipe'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($showAssetDropdown && strlen($assetSearch) >= 2 && count($assetResults) === 0)
                                    <div class="absolute z-20 mt-1 w-full rounded-lg shadow-lg"
                                        style="background: var(--color-bg-secondary); border: 1px solid var(--color-border);">
                                        <button wire:click="openCreateAsset" type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:opacity-80 transition flex items-center gap-2"
                                            style="color: var(--color-text-primary);">
                                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Tambah Asset Baru: <strong>{{ $assetSearch }}</strong></span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if($showCreateAsset)
                                <div class="glass-card p-3 mt-2 space-y-2" style="border: 1px solid var(--color-border);">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-semibold text-primary">Tambah Asset Baru</p>
                                        <button wire:click="closeCreateAsset" type="button" class="text-xs text-red-400 hover:text-red-300">Batal</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-xs text-muted">No. Asset <span class="text-red-400">*</span></label>
                                            <input type="text" wire:model.live="newAssetNoAsset" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="Contoh: ASR-2024-001">
                                            @error('newAssetNoAsset') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs text-muted">Kategori</label>
                                            <select wire:model.live="newAssetKategori" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1">
                                                <option value="">Pilih Kategori</option>
                                                <option value="PC Desktop">PC Desktop</option>
                                                <option value="Laptop">Laptop</option>
                                                <option value="Notebook">Notebook</option>
                                                <option value="Mini PC">Mini PC</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs text-muted">Brand</label>
                                            <input type="text" wire:model.live="newAssetBrand" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="Lenovo, HP, Dell">
                                        </div>
                                        <div>
                                            <label class="text-xs text-muted">Tipe</label>
                                            <input type="text" wire:model.live="newAssetTipe" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="ThinkPad T480">
                                        </div>
                                        <div>
                                            <label class="text-xs text-muted">Nama Perangkat</label>
                                            <input type="text" wire:model.live="newAssetNamaPerangkat" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="Laptop Kantor">
                                        </div>
                                        <div>
                                            <label class="text-xs text-muted">No. Serial</label>
                                            <input type="text" wire:model.live="newAssetNoSerial" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="SN-12345678">
                                        </div>
                                    </div>
                                    <button wire:click="createAsset" type="button" class="glass-button-primary text-xs w-full py-1.5 mt-1">
                                        Simpan & Pilih Asset
                                    </button>
                                </div>
                            @endif
                        @endif

                        <div class="pt-3 space-y-3">
                            <div>
                                <label class="text-xs text-muted">Site Location Pemeriksaan <span class="text-red-400">*</span></label>
                                <select wire:model.live="siteLocation"
                                    class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-1">
                                    <option value="">Pilih Site Location</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site['id_site'] }}">{{ $site['id_site'] }} - {{ $site['site'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-muted">Location Detail</label>
                                <input type="text" wire:model.live="locationDetail" class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-1" placeholder="Contoh: Ruang Server, Meja No. 12">
                            </div>
                        </div>

                        <div class="flex justify-between pt-4">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">Selanjutnya →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 3: Kondisi --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(3)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 3 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 3 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">3</span>
                    <span class="font-semibold text-primary text-sm">Kondisi Perangkat</span>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 3 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 3" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 space-y-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4">
                        <label class="text-xs font-semibold text-muted uppercase tracking-wider">Kondisi Perangkat</label>
                        <div class="flex gap-3 mt-2">
                            <button wire:click="$set('kondisi', 'baru')" type="button"
                                class="flex-1 p-3 rounded-lg border-2 text-sm font-semibold text-center transition-all"
                                :class="$wire.kondisi === 'baru'
                                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-400'
                                    : 'border-transparent'"
                                style="background: {{ $kondisi !== 'baru' ? 'var(--color-bg-tertiary)' : '' }}; color: {{ $kondisi !== 'baru' ? 'var(--color-text-secondary)' : '' }};">
                                Baru
                            </button>
                            <button wire:click="$set('kondisi', 'lama')" type="button"
                                class="flex-1 p-3 rounded-lg border-2 text-sm font-semibold text-center transition-all"
                                :class="$wire.kondisi === 'lama'
                                    ? 'border-amber-500 bg-amber-500/10 text-amber-400'
                                    : 'border-transparent'"
                                style="background: {{ $kondisi !== 'lama' ? 'var(--color-bg-tertiary)' : '' }}; color: {{ $kondisi !== 'lama' ? 'var(--color-text-secondary)' : '' }};">
                                Lama
                            </button>
                        </div>

                        <div class="mt-3">
                            <label class="text-xs text-muted">Keterangan Kondisi (opsional)</label>
                            <textarea wire:model.live="kondisiKeterangan" rows="2"
                                class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-1 resize-none"
                                placeholder="Deskripsi kondisi perangkat..."></textarea>
                        </div>

                        <div class="flex justify-between pt-4">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">Selanjutnya →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 4: Pemeriksaan Hardware --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(4)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 4 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 4 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">4</span>
                    <div>
                        <span class="font-semibold text-primary text-sm">Pemeriksaan Hardware</span>
                        <p class="text-xs text-muted" x-show="openStep !== 4">{{ count($hardwareItems) }} item</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 4 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 4" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4 space-y-3">
                        @foreach($hardwareItems as $index => $item)
                            <div class="glass-card p-3 space-y-2" style="background: var(--color-bg-tertiary); border: none;">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-primary">{{ $item['name'] }}</span>
                                    <div class="flex gap-1">
                                        <button wire:click="toggleItemStatus('hardwareItems', {{ $index }}, 'baik')"
                                            type="button"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all border-2"
                                            :class="@js($item['status']) === 'baik'
                                                ? 'border-emerald-500 bg-emerald-500/15 text-emerald-400'
                                                : 'border-transparent'"
                                            style="{{ json_encode($item['status']) !== 'baik' ? 'background: var(--color-bg-secondary); color: var(--color-text-secondary);' : '' }}">
                                            ✓ Baik
                                        </button>
                                        <button wire:click="toggleItemStatus('hardwareItems', {{ $index }}, 'tidak_baik')"
                                            type="button"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all border-2"
                                            :class="@js($item['status']) === 'tidak_baik'
                                                ? 'border-red-500 bg-red-500/15 text-red-400'
                                                : 'border-transparent'"
                                            style="{{ json_encode($item['status']) !== 'tidak_baik' ? 'background: var(--color-bg-secondary); color: var(--color-text-secondary);' : '' }}">
                                            ✗ Tidak Baik
                                        </button>
                                    </div>
                                </div>

                                <textarea wire:model.live="hardwareItems.{{ $index }}.keterangan"
                                    rows="1" placeholder="Keterangan (opsional)..."
                                    class="glass-input w-full rounded-lg px-3 py-1.5 text-xs resize-none"></textarea>

                                {{-- Battery Capacity Fields --}}
                                @if($item['name'] === 'Battery')
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-xs" style="color: var(--color-text-muted);">Full Charge Capacity (mWh)</label>
                                            <input type="number" wire:model.live="hardwareItems.{{ $index }}.full_charge_capacity"
                                                placeholder="Contoh: 45000"
                                                class="glass-input w-full rounded-lg px-3 py-1.5 text-xs">
                                        </div>
                                        <div>
                                            <label class="text-xs" style="color: var(--color-text-muted);">Design Capacity (mWh)</label>
                                            <input type="number" wire:model.live="hardwareItems.{{ $index }}.design_capacity"
                                                placeholder="Contoh: 50000"
                                                class="glass-input w-full rounded-lg px-3 py-1.5 text-xs">
                                        </div>
                                    </div>
                                    @if($item['full_charge_capacity'] && $item['design_capacity'] && $item['design_capacity'] > 0)
                                        @php
                                            $batteryPercent = round(($item['full_charge_capacity'] / $item['design_capacity']) * 100);
                                        @endphp
                                        <div class="text-xs font-semibold" style="color: {{ $batteryPercent >= 80 ? 'var(--color-success, #10b981)' : ($batteryPercent >= 50 ? 'var(--color-warning, #f59e0b)' : 'var(--color-danger, #ef4444)') }};">
                                            Battery Health: {{ $batteryPercent }}%
                                        </div>
                                    @endif
                                @endif

                                {{-- Photo Upload --}}
                                <div>
                                    <button type="button" @click="showUpload = showUpload === 'hw-{{ $index }}' ? null : 'hw-{{ $index }}'"
                                        class="text-xs flex items-center gap-1"
                                        style="color: var(--color-text-muted);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Lampirkan Foto
                                    </button>
                                    <div x-show="showUpload === 'hw-{{ $index }}'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2" class="mt-2">
                                        <input type="file" accept="image/*" capture="environment"
                                            wire:model="itemPhotos.hw_{{ $index }}"
                                            class="text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold"
                                            style="color: var(--color-text-secondary);">
                                        @if(isset($itemPhotos["hw_{$index}"]))
                                            <div class="mt-1">
                                                <img src="{{ $itemPhotos["hw_{$index}"] ? $itemPhotos["hw_{$index}"]->temporaryUrl() : '' }}" class="h-16 rounded-lg object-cover">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="flex justify-between pt-2">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">Selanjutnya →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 5: Pemeriksaan Aplikasi --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(5)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 5 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 5 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">5</span>
                    <div>
                        <span class="font-semibold text-primary text-sm">Pemeriksaan Aplikasi</span>
                        <p class="text-xs text-muted" x-show="openStep !== 5">{{ count($aplikasiItems) }} item</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 5 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 5" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4 space-y-3">
                        @foreach($aplikasiItems as $index => $item)
                            <div class="glass-card p-3 space-y-2" style="background: var(--color-bg-tertiary); border: none;">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-primary">{{ $item['name'] }}</span>
                                    <div class="flex gap-1">
                                        <button wire:click="toggleItemStatus('aplikasiItems', {{ $index }}, 'baik')"
                                            type="button"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all border-2"
                                            :class="@js($item['status']) === 'baik'
                                                ? 'border-emerald-500 bg-emerald-500/15 text-emerald-400'
                                                : 'border-transparent'"
                                            style="{{ json_encode($item['status']) !== 'baik' ? 'background: var(--color-bg-secondary); color: var(--color-text-secondary);' : '' }}">
                                            Installed
                                        </button>
                                        <button wire:click="toggleItemStatus('aplikasiItems', {{ $index }}, 'tidak_baik')"
                                            type="button"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all border-2"
                                            :class="@js($item['status']) === 'tidak_baik'
                                                ? 'border-red-500 bg-red-500/15 text-red-400'
                                                : 'border-transparent'"
                                            style="{{ json_encode($item['status']) !== 'tidak_baik' ? 'background: var(--color-bg-secondary); color: var(--color-text-secondary);' : '' }}">
                                            Not Installed
                                        </button>
                                    </div>
                                </div>

                                <textarea wire:model.live="aplikasiItems.{{ $index }}.keterangan"
                                    rows="1" placeholder="Keterangan (opsional)..."
                                    class="glass-input w-full rounded-lg px-3 py-1.5 text-xs resize-none"></textarea>

                                <div>
                                    <button type="button" @click="showUpload = showUpload === 'app-{{ $index }}' ? null : 'app-{{ $index }}'"
                                        class="text-xs flex items-center gap-1"
                                        style="color: var(--color-text-muted);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Lampirkan Foto
                                    </button>
                                    <div x-show="showUpload === 'app-{{ $index }}'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2" class="mt-2">
                                        <input type="file" accept="image/*" capture="environment"
                                            wire:model="itemPhotos.app_{{ $index }}"
                                            class="text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold"
                                            style="color: var(--color-text-secondary);">
                                        @if(isset($itemPhotos["app_{$index}"]))
                                            <div class="mt-1">
                                                <img src="{{ $itemPhotos["app_{$index}"] ? $itemPhotos["app_{$index}"]->temporaryUrl() : '' }}" class="h-16 rounded-lg object-cover">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="flex justify-between pt-2">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">Selanjutnya →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 6: Operating System --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(6)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 6 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 6 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">6</span>
                    <div>
                        <span class="font-semibold text-primary text-sm">Operating System</span>
                        <p class="text-xs text-muted" x-show="openStep !== 6">{{ count($osItems) }} item</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 6 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 6" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4 space-y-3">
                        @foreach($osItems as $index => $item)
                            <div class="glass-card p-3 space-y-2" style="background: var(--color-bg-tertiary); border: none;">
                                <span class="text-sm font-medium text-primary">{{ $item['name'] }}</span>

                                <input type="text" wire:model.live="osItems.{{ $index }}.value"
                                    placeholder="Isi nilai..."
                                    class="glass-input w-full rounded-lg px-3 py-1.5 text-xs">

                                <div class="flex gap-1">
                                    <button wire:click="toggleItemStatus('osItems', {{ $index }}, 'baik')"
                                        type="button"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-all border-2"
                                        :class="@js($item['status']) === 'baik'
                                            ? 'border-emerald-500 bg-emerald-500/15 text-emerald-400'
                                            : 'border-transparent'"
                                        style="{{ json_encode($item['status']) !== 'baik' ? 'background: var(--color-bg-secondary); color: var(--color-text-secondary);' : '' }}">
                                        ✓ Baik
                                    </button>
                                    <button wire:click="toggleItemStatus('osItems', {{ $index }}, 'tidak_baik')"
                                        type="button"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-all border-2"
                                        :class="@js($item['status']) === 'tidak_baik'
                                            ? 'border-red-500 bg-red-500/15 text-red-400'
                                            : 'border-transparent'"
                                        style="{{ json_encode($item['status']) !== 'tidak_baik' ? 'background: var(--color-bg-secondary); color: var(--color-text-secondary);' : '' }}">
                                        ✗ Tidak Baik
                                    </button>
                                </div>

                                <textarea wire:model.live="osItems.{{ $index }}.keterangan"
                                    rows="1" placeholder="Keterangan (opsional)..."
                                    class="glass-input w-full rounded-lg px-3 py-1.5 text-xs resize-none"></textarea>
                            </div>
                        @endforeach

                        <div class="flex justify-between pt-2">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">Selanjutnya →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 7: Tindakan --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(7)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 7 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 7 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">7</span>
                    <div>
                        <span class="font-semibold text-primary text-sm">Tindakan</span>
                        <p class="text-xs text-muted" x-show="openStep !== 7">Kategori tindakan yang dilakukan</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 7 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 7" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4 space-y-4">
                        @foreach($tindakanCategories as $catIndex => $category)
                            <div class="glass-card p-3" style="background: var(--color-bg-tertiary); border: none;">
                                <span class="text-sm font-semibold text-primary mb-2 block">{{ $category['label'] }}</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($category['options'] as $option)
                                        @php
                                            $isSelected = in_array($option, $category['selected']);
                                        @endphp
                                        <button wire:click="toggleTindakanOption({{ $catIndex }}, '{{ $option }}')"
                                            type="button"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all border-2"
                                            :class="{{ $isSelected ? 'true' : 'false' }}
                                                ? 'border-emerald-500 bg-emerald-500/15 text-emerald-400'
                                                : 'border-transparent'"
                                            style="{{ !$isSelected ? 'background: var(--color-bg-secondary); color: var(--color-text-secondary);' : '' }}">
                                            {{ $option }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div>
                            <label class="text-xs font-semibold text-muted uppercase tracking-wider">Solution</label>
                            <textarea wire:model.live="tindakanSolution" rows="3"
                                class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-2 resize-none"
                                placeholder="Contoh: Replacement unit / Upgrade Disk, Ram dll"></textarea>
                        </div>

                        <div class="flex justify-between pt-2">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">Selanjutnya →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 8: Catatan --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(8)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 8 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 8 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">8</span>
                    <span class="font-semibold text-primary text-sm">Catatan</span>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 8 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 8" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4 space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-muted uppercase tracking-wider">Catatan / Tindakan Lanjutan</label>
                            <textarea wire:model.live="notes" rows="4"
                                class="glass-input w-full rounded-lg px-3 py-2 text-sm mt-2 resize-none"
                                placeholder="Tuliskan catatan atau tindakan yang perlu dilakukan..."></textarea>
                        </div>

                        <div class="flex justify-between pt-2">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="nextStep" type="button" class="glass-button-primary text-sm">Selanjutnya →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 9: Review & Submit --}}
        <div class="glass-card overflow-hidden">
            <button wire:click="goToStep(9)" type="button"
                class="w-full flex items-center justify-between p-4 text-left transition-colors"
                :class="openStep === 9 ? '' : 'opacity-70 hover:opacity-100'">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        :class="openStep === 9 ? 'text-primary' : 'text-muted'"
                        style="background: var(--color-bg-tertiary);">9</span>
                    <span class="font-semibold text-primary text-sm">Review & Submit</span>
                </div>
                <svg class="w-5 h-5 text-muted transition-transform duration-200"
                    :class="openStep === 9 ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openStep === 9" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2">
                <div class="px-4 pb-4 border-t" style="border-color: var(--color-border);">
                    <div class="pt-4 space-y-4">

                        {{-- No Form Preview --}}
                        <div class="glass-card p-3" style="background: var(--color-bg-tertiary); border: none;">
                            <span class="text-xs text-muted">Nomor Form</span>
                            <p class="font-mono font-bold text-primary text-lg">{{ $this->getFormNumberPreview() }}</p>
                        </div>

                        {{-- Info Pengguna --}}
                        <div class="glass-card p-3" style="background: var(--color-bg-tertiary); border: none;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-muted uppercase">Info Pengguna</span>
                                <button wire:click="goToStep(1)" type="button" class="text-xs" style="color: var(--color-text-secondary);">Edit</button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="text-xs text-muted">Teknisi</span><p class="text-primary">{{ $teknisiName }}</p></div>
                                <div><span class="text-xs text-muted">Pengguna</span><p class="text-primary">{{ $penggunaName ?: '-' }}</p></div>
                            </div>
                        </div>

                        {{-- Info Perangkat --}}
                        <div class="glass-card p-3" style="background: var(--color-bg-tertiary); border: none;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-muted uppercase">Info Perangkat</span>
                                <button wire:click="goToStep(2)" type="button" class="text-xs" style="color: var(--color-text-secondary);">Edit</button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
                                <div><span class="text-xs text-muted">No. Asset</span><p class="text-primary font-mono">{{ $noAsset ?: '-' }}</p></div>
                                <div><span class="text-xs text-muted">Brand</span><p class="text-primary">{{ $brand ?: '-' }}</p></div>
                                <div><span class="text-xs text-muted">Tipe</span><p class="text-primary">{{ $tipe ?: '-' }}</p></div>
                                <div><span class="text-xs text-muted">Kondisi</span><p class="text-primary">{{ ucfirst($kondisi ?: '-') }}</p></div>
                                <div><span class="text-xs text-muted">Site Location</span><p class="text-primary">{{ $sites[array_search($siteLocation, array_column($sites, 'id_site'))]['site'] ?? '-' }}</p></div>
                                <div><span class="text-xs text-muted">Location Detail</span><p class="text-primary">{{ $locationDetail ?: '-' }}</p></div>
                            </div>
                        </div>

                        {{-- Ringkasan Checklist --}}
                        @php
                            $hwBaik = collect($hardwareItems)->where('status', 'baik')->count();
                            $hwTidakBaik = collect($hardwareItems)->where('status', 'tidak_baik')->count();
                            $appBaik = collect($aplikasiItems)->where('status', 'baik')->count();
                            $appTidakBaik = collect($aplikasiItems)->where('status', 'tidak_baik')->count();
                            $osBaik = collect($osItems)->where('status', 'baik')->count();
                            $osTidakBaik = collect($osItems)->where('status', 'tidak_baik')->count();
                        @endphp
                        <div class="glass-card p-3" style="background: var(--color-bg-tertiary); border: none;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-muted uppercase">Ringkasan Checklist</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center text-sm">
                                <div>
                                    <p class="text-xs text-muted">Hardware</p>
                                    <p class="text-emerald-400 font-bold">{{ $hwBaik }} Baik</p>
                                    <p class="text-red-400 font-bold">{{ $hwTidakBaik }} Tidak Baik</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted">Aplikasi</p>
                                    <p class="text-emerald-400 font-bold">{{ $appBaik }} Baik</p>
                                    <p class="text-red-400 font-bold">{{ $appTidakBaik }} Tidak Baik</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted">OS</p>
                                    <p class="text-emerald-400 font-bold">{{ $osBaik }} Baik</p>
                                    <p class="text-red-400 font-bold">{{ $osTidakBaik }} Tidak Baik</p>
                                </div>
                            </div>
                        </div>

                        @if($notes)
                            <div class="glass-card p-3" style="background: var(--color-bg-tertiary); border: none;">
                                <span class="text-xs font-semibold text-muted uppercase">Catatan</span>
                                <p class="text-sm text-primary mt-1">{{ $notes }}</p>
                            </div>
                        @endif

                        @php
                            $hasTindakan = collect($tindakanCategories)->filter(fn($c) => !empty($c['selected']))->count() > 0;
                        @endphp
                        @if($hasTindakan)
                            <div class="glass-card p-3" style="background: var(--color-bg-tertiary); border: none;">
                                <span class="text-xs font-semibold text-muted uppercase">Tindakan</span>
                                <div class="mt-1 space-y-1">
                                    @foreach($tindakanCategories as $cat)
                                        @if(!empty($cat['selected']))
                                            <div class="text-sm">
                                                <span class="font-semibold text-primary">{{ $cat['label'] }}:</span>
                                                <span class="text-secondary">{{ implode(', ', $cat['selected']) }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if($tindakanSolution)
                                        <div class="text-sm mt-2">
                                            <span class="font-semibold text-primary">Solution:</span>
                                            <span class="text-secondary">{{ $tindakanSolution }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-between pt-2">
                            <button wire:click="prevStep" type="button" class="glass-button-secondary text-sm">← Sebelumnya</button>
                            <button wire:click="submitForm"
                                type="button" class="px-6 py-2 rounded-lg font-semibold text-sm transition-all duration-200 text-white"
                                style="background: linear-gradient(135deg, #059669, #10b981);">
                                Submit & Tanda Tangan →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Popup Tambah Akun User Baru --}}
        @if ($showAddUserPopup)
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="fixed inset-0 opacity-75" style="background-color: var(--color-bg-tertiary);" wire:click="closeAddUserPopup"></div>
                <div class="relative w-full max-w-md glass-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-primary">Tambah Akun User Baru</h3>
                        <button wire:click="closeAddUserPopup" type="button" class="text-xs text-red-400 hover:text-red-300">Tutup</button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-muted">Email</label>
                            <input type="email" wire:model="newPenggunaEmail" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1">
                            @error('newPenggunaEmail') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-muted">Password <span class="text-red-400">*</span></label>
                            <input type="text" wire:model="addUserPassword" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1" placeholder="password">
                            @error('addUserPassword') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-muted">Role</label>
                            <select wire:model="addUserRole" class="glass-input w-full rounded-lg px-3 py-1.5 text-sm mt-1">
                                @foreach($this->getRoleList() as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            @error('addUserRole') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-5">
                        <button wire:click="closeAddUserPopup" type="button" class="glass-button-secondary text-xs">Batal</button>
                        <button wire:click="saveAddUser" type="button" class="glass-button-primary text-xs">Simpan & Pilih</button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

@script
    <script>
    function showToast(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type } }));
    }
    </script>
@endscript

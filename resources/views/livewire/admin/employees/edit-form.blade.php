<div class="space-y-6"
    x-data="{ errors: {} }"
    x-on:validation-error.window="errors = $event.detail.errors[0]">

    <div x-data="{ toast: false, message: '', type: 'success' }"
        @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)"
        x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message">
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Edit Employee') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Kelola data karyawan dan akun login terkait') }}</p>
        </div>
        <a href="{{ route('admin.employees.index') }}" wire:navigate
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
            {{ __('Kembali') }}
        </a>
    </div>

    <div class="glass-card p-6 space-y-5">
        {{-- Nama --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Nama') }} <span class="text-red-400">*</span></label>
            <input wire:model="name" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="{{ __('Nama lengkap') }}" />
            @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Email (otomatis dari akun login) --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Email') }}</label>
            <input type="email" wire:model="email" readonly
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200 cursor-not-allowed"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="{{ __('Tidak ada akun login') }}" />
            <p class="text-xs text-muted mt-1">{{ __('Email otomatis mengikuti akun login yang terhubung.') }}</p>
            @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- NIK --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">NIK</label>
            <input wire:model="nik" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="{{ __('Nomor Induk Karyawan') }}" />
            @error('nik') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Site & No. Telepon --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary mb-1">{{ __('Site') }}</label>
                <select wire:model="site"
                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Pilih Site') }}</option>
                    @foreach($this->getSiteList() as $idSite => $label)
                        <option value="{{ $idSite }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('site') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary mb-1">{{ __('No. Telepon') }}</label>
                <input wire:model="no_telepon" type="text"
                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                    placeholder="08xxxxxxxxxx" />
            </div>
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Status Employee') }}</label>
            <select wire:model="status"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="Active">Active</option>
                <option value="Resigned">Resigned</option>
            </select>
            @error('status') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Struktur Organisasi --}}
        <div class="border-t pt-4" style="border-color: var(--color-border);">
            <h3 class="text-sm font-bold text-primary mb-3">{{ __('Struktur Organisasi') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Directorat') }}</label>
                    <select wire:model.live="directorate_id"
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Directorat') }}</option>
                        @foreach($this->getDirectorateList() as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('directorate_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Divisi') }}</label>
                    <select wire:model.live="divisi_id" @disabled(!$directorate_id)
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Divisi') }}</option>
                        @foreach($this->getDivisiList() as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('divisi_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Departemen') }}</label>
                    <select wire:model.live="departement_id" @disabled(!$divisi_id)
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Departemen') }}</option>
                        @foreach($this->getDepartementList() as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('departement_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Sub Departemen') }}</label>
                    <select wire:model.live="sub_departement_id" @disabled(!$departement_id)
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Sub Departemen') }}</option>
                        @foreach($this->getSubDepartementList() as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('sub_departement_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Position') }}</label>
                    <select wire:model="position_id"
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Position') }}</option>
                        @foreach($this->getPositionList() as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('position_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Akun Login --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Akun Login') }}</label>
            @if($linkedUserId)
                @php $linked = collect($linkableUsers)->firstWhere('id', $linkedUserId); @endphp
                <div class="p-3 rounded-lg flex items-center justify-between gap-3"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                    <div>
                        <p class="text-sm font-medium text-primary">{{ $linked['name'] ?? $this->employee->user?->name ?? '-' }}</p>
                        <p class="text-xs text-muted">{{ $this->employee->user?->email ?? $linked['email'] ?? '' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">{{ __('Terhubung') }}</span>
                        <button type="button" wire:click="unlinkUser"
                            class="text-xs px-2 py-1 rounded-lg transition-colors duration-200 text-red-400 hover:text-red-300"
                            style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                            {{ __('Lepas') }}
                        </button>
                    </div>
                </div>
            @else
                <select wire:model="linkedUserId"
                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">-- {{ __('Tidak ada akun login') }} --</option>
                    @foreach($linkableUsers as $u)
                        <option value="{{ $u['id'] }}">{{ $u['name'] }} ({{ $u['email'] }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-muted mt-1">{{ __('Hubungkan ke akun pengguna sistem agar bisa login dan menyetujui (Diketahui) form.') }}</p>
            @endif
            @error('linkedUserId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Asset Terpasang --}}
        @if(count($assignedAssets) > 0)
            <div>
                <label class="block text-sm font-medium text-secondary mb-1">{{ __('Asset Terpasang') }}</label>
                <div class="space-y-1.5">
                    @foreach($assignedAssets as $asset)
                        <div class="p-2.5 rounded-lg flex items-center justify-between gap-3"
                            style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                            <div>
                                <p class="text-sm font-medium text-primary">{{ $asset['nama_perangkat'] ?? '-' }}</p>
                                <p class="text-xs text-muted">{{ $asset['no_asset'] ?? '-' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($status === 'Resigned')
                <div class="p-3 rounded-lg text-sm"
                    style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: var(--color-text-primary);">
                    <p class="font-semibold" style="color: #ef4444;">{{ __('Employee masih memiliki asset terpasang') }}</p>
                    <p class="mt-1" style="color: var(--color-text-secondary);">
                        {{ __('Sebelum mengubah status menjadi Resigned, kembalikan terlebih dahulu asset berikut melalui Form Pengembalian Asset.') }}
                    </p>
                </div>
            @endif
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2">
            <button wire:click="update" wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <span wire:loading.remove wire:target="update">{{ __('Simpan') }}</span>
                <span wire:loading wire:target="update">{{ __('Menyimpan') }}...</span>
            </button>
            <a href="{{ route('admin.employees.index') }}" wire:navigate
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ __('Batal') }}
            </a>
        </div>
    </div>
</div>

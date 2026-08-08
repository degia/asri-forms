<div class="glass-card p-6 space-y-5"
    x-data="{ errors: {} }"
    x-on:validation-error.window="errors = $event.detail.errors[0]">

    <div x-data="{ toast: false, message: '', type: 'success' }"
        @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)"
        x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message">
    </div>

    {{-- Nama --}}
    <div>
        <label class="block text-sm font-medium text-secondary mb-1">{{ __('Nama') }} <span class="text-red-400">*</span></label>
        <input wire:model="name" type="text"
            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
            placeholder="{{ __('Nama lengkap') }}" />
        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    @if(! $this->modal)
    {{-- Email (opsional) --}}
    <div x-data="{ open: true }" @click.outside="open = false">
        <label class="block text-sm font-medium text-secondary mb-1">{{ __('Email') }}</label>
        <div class="relative">
            <input wire:model.live.debounce.300ms="email" type="email" @focus="open = true"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="email@asri.co.id" />
            @if(count($emailSuggestions) > 0)
                <div x-show="open" x-transition
                    class="absolute z-20 mt-1 w-full rounded-lg shadow-lg overflow-hidden"
                    style="background: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                    @foreach($emailSuggestions as $suggestion)
                        <button type="button" wire:click="selectEmail('{{ $suggestion['email'] }}')"
                            class="w-full text-left px-4 py-2 text-sm transition-colors duration-150 hover:bg-black/5"
                            style="color: var(--color-text-primary);">
                            <span>{{ $suggestion['email'] }}</span>
                            <span class="text-xs text-muted"> · {{ $suggestion['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
        <p class="text-xs text-muted mt-1">{{ __('Opsional. Karyawan tanpa email tetap dapat diproses.') }}</p>

        @if($this->email !== '' && ! $this->emailRegistered)
            <p class="text-xs text-amber-400 mt-1">{{ __('Email belum terdaftar sebagai akun user.') }}</p>
            <button type="button" wire:click="openCreateUserModal"
                class="mt-2 px-4 py-2 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                {{ __('Buat Email Baru') }}
            </button>
        @endif

        @if($this->emailRegistered && $this->emailUsed)
            <p class="text-xs text-red-400 mt-1">{{ __('Email sudah digunakan oleh employee lain dan tidak dapat dipakai.') }}</p>
        @endif

        @if($this->emailRegistered && ! $this->emailUsed)
            <p class="text-xs text-emerald-500 mt-1">{{ __('Email terdaftar dan tersedia.') }}</p>
        @endif

        @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>
    @endif

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

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-2">
        <button wire:click="save" wire:loading.attr="disabled"
            @if($this->email !== '' && $this->emailUsed) disabled title="{{ __('Email sudah digunakan oleh employee lain.') }}" @endif
            class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
            style="background: var(--color-primary); color: var(--color-button-text); @if($this->email !== '' && $this->emailUsed) opacity-50 cursor-not-allowed; @endif">
            <span wire:loading.remove wire:target="save">{{ __('Simpan') }}</span>
            <span wire:loading wire:target="save">{{ __('Menyimpan') }}...</span>
        </button>
        @if($this->modal)
            <button type="button" wire:click="closeModal"
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ __('Batal') }}
            </button>
        @else
            <a href="{{ route('admin.employees.index') }}" wire:navigate
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ __('Batal') }}
            </a>
        @endif
    </div>

    {{-- Modal: Buat Email Baru --}}
    <x-modal name="create-user" :show="$showCreateUserModal" maxWidth="md" focusable>
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-semibold" style="color: var(--color-text-primary);">{{ __('Buat Akun User Baru') }}</h3>
            <p class="text-xs text-muted">{{ __('Akun akan dibuat di daftar users dengan status Active. Setelah dibuat, email otomatis dipakai untuk employee ini.') }}</p>

            <div>
                <label class="block text-sm font-medium text-secondary mb-1">{{ __('Nama') }} <span class="text-red-400">*</span></label>
                <input wire:model="newUserName" type="text"
                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                @error('newUserName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary mb-1">{{ __('Email') }} <span class="text-red-400">*</span></label>
                <input wire:model="newUserEmail" type="email"
                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                @error('newUserEmail') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Password') }} <span class="text-red-400">*</span></label>
                    <input wire:model="newUserPassword" type="password"
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                    @error('newUserPassword') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">{{ __('Role') }}</label>
                    <select wire:model="newUserRole"
                        class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        @foreach($this->getRoleList() as $role)
                            <option value="{{ $role }}">{{ $this->getRoleLabel($role) }}</option>
                        @endforeach
                    </select>
                    @error('newUserRole') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button wire:click="createUser" wire:loading.attr="disabled"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                    style="background: var(--color-primary); color: var(--color-button-text);">
                    <span wire:loading.remove wire:target="createUser">{{ __('Buat Akun') }}</span>
                    <span wire:loading wire:target="createUser">{{ __('Membuat') }}...</span>
                </button>
                <button type="button" wire:click="closeCreateUserModal"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    {{ __('Batal') }}
                </button>
            </div>
        </div>
    </x-modal>
</div>

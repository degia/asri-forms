<div class="glass-card p-6 space-y-5"
    x-data="{ errors: {}, toast: false, message: '', type: 'success' }"
    x-on:validation-error.window="errors = $event.detail.errors[0]"
    @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)">

    <div x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message">
    </div>

    @if($showCredentials)
        {{-- INFO AKSES LOGIN --}}
        <div class="rounded-lg p-5 space-y-3" style="background: var(--color-bg-tertiary); border: 1px solid var(--color-border);">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-semibold text-emerald-400">{{ __('Pengguna berhasil ditambahkan!') }}</span>
            </div>
            <p class="text-xs text-muted">{{ __('Berikut informasi akses login yang dapat diberikan kepada pengguna:') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg p-3" style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                    <span class="text-xs text-muted block mb-1">{{ __('Email') }}</span>
                    <p class="text-sm font-mono font-semibold text-primary">{{ $createdEmail }}</p>
                </div>
                <div class="rounded-lg p-3" style="background: var(--color-glass-bg); border: 1px solid var(--color-border);">
                    <span class="text-xs text-muted block mb-1">{{ __('Password Default') }}</span>
                    <p class="text-sm font-mono font-semibold text-primary">{{ $createdPassword }}</p>
                </div>
            </div>
            <p class="text-xs text-muted" style="color: var(--color-text-secondary);">
                <svg class="w-3.5 h-3.5 inline-block -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('Pengguna dapat mengubah password setelah login pertama kali.') }}
            </p>
            <div class="pt-2">
                <a href="{{ route('admin.users.index') }}" wire:navigate
                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                    style="background: var(--color-primary); color: var(--color-button-text);">
                    {{ __('Selesai') }}
                </a>
            </div>
        </div>
    @else
        {{-- FORM TAMBAH PENGGUNA --}}
        {{-- NIK --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">NIK <span class="text-red-400">*</span></label>
            <input wire:model.live.debounce.500ms="nik" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="{{ __('Nomor Induk Karyawan') }}" />
            @error('nik') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror

            @if($nik !== '' && ! $this->linkedEmployee)
                <div class="mt-2 p-3 rounded-lg"
                    style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35);">
                    <p class="text-xs text-amber-400">{{ __('NIK belum terdaftar pada data employee.') }}</p>
                    <button type="button" wire:click="openAddEmployeeModal"
                        class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                        style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Tambah Employee') }}
                    </button>
                </div>
            @endif
        </div>
        {{-- Nama (otomatis dari Employee berdasarkan NIK) --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Nama') }}</label>
            <div class="w-full px-4 py-2 rounded-lg text-sm"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ $this->linkedEmployee?->name ?? __('Nama otomatis dari data Employee berdasarkan NIK') }}
            </div>
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Email') }} <span class="text-red-400">*</span></label>
            <input wire:model="email" type="email"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="email@asri.co.id" />
            @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Password --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary mb-1">{{ __('Password') }} <span class="text-red-400">*</span></label>
                <input wire:model="password" type="password"
                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                    placeholder="{{ __('Minimal 6 karakter') }}" />
                @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary mb-1">{{ __('Konfirmasi Password') }} <span class="text-red-400">*</span></label>
                <input wire:model="password_confirmation" type="password"
                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                    placeholder="{{ __('Ulangi password') }}" />
            </div>
        </div>

        {{-- Access Login --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Access Login') }}</label>
            <select wire:model="status"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="Enable">Enable</option>
                <option value="Disable">Disable</option>
            </select>
            @error('status') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-muted mt-1">Enable: user dapat mengakses login web. Disable: user tidak dapat mengakses login web.</p>
        </div>

        {{-- Role --}}
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Role') }} <span class="text-red-400">*</span></label>
            <select wire:model="role"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="">{{ __('Pilih Role') }}</option>
                @foreach($this->getRoleList() as $r)
                    <option value="{{ $r }}">{{ $this->getRoleLabel($r) }}</option>
                @endforeach
            </select>
            @error('role') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2">
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <span wire:loading.remove wire:target="save">{{ __('Simpan') }}</span>
                <span wire:loading wire:target="save">{{ __('Menyimpan') }}...</span>
            </button>
            <a href="{{ route('admin.users.index') }}" wire:navigate
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ __('Batal') }}
            </a>
        </div>
    @endif

    {{-- Modal: Tambah Employee (kembali ke form ini setelah selesai) --}}
    @if($showAddEmployeeModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
            style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.closeAddEmployeeModal()">
            <div class="glass-card p-6 w-full max-w-3xl space-y-4 my-8" @click.away="$wire.closeAddEmployeeModal()">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary">{{ __('Tambah Employee') }}</h3>
                    <button type="button" wire:click="closeAddEmployeeModal"
                        class="text-muted hover:opacity-70 text-xl leading-none">&times;</button>
                </div>
                <livewire:admin.employees.create-form :nik="$nik" :name="$name" :modal="true" wire:key="'emp-create-' . $nik" />
            </div>
        </div>
    @endif
</div>

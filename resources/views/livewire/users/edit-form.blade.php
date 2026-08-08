<div class="space-y-6" x-data="{ toast: false, message: '', type: 'success' }" x-on:user-updated.window="window.location = '{{ route('admin.users.index') }}'"
    @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)">

    <div x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message">
    </div>

    @if (session()->has('success'))
        <div class="p-3 rounded-lg text-sm"
            style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="update" class="space-y-5">
        {{-- Name --}}
        <div>
            <label class="text-xs text-muted">{{ __('Nama') }}</label>
            <div class="mt-1 px-3 py-2 rounded-lg text-sm"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ $this->linkedEmployee?->name ?? __('Nama otomatis dari data Employee berdasarkan NIK') }}
            </div>
        </div>

        {{-- Email --}}
        <div>
            <label class="text-xs text-muted">{{ __('Email') }} <span class="text-red-400">*</span></label>
            <input wire:model="email" type="email"
                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                required />
            @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Password (optional) --}}
        <div>
            <label class="text-xs text-muted">{{ __('Password') }} <span class="text-muted">{{ __('(kosongkan jika tidak diubah)') }}</span></label>
            <input wire:model="password" type="password"
                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                autocomplete="new-password" />
            @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Password Confirmation --}}
        <div>
            <label class="text-xs text-muted">{{ __('Konfirmasi Password') }}</label>
            <input wire:model="password_confirmation" type="password"
                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                autocomplete="new-password" />
        </div>

        {{-- NIK --}}
        <div>
            <label class="text-xs text-muted">NIK</label>
            <input wire:model.live.debounce.500ms="nik" type="text"
                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
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

        {{-- Status Employee (read-only, dari data Employee) --}}
        <div>
            <label class="text-xs text-muted">{{ __('Status Employee') }}</label>
            <div class="mt-1 px-3 py-2 rounded-lg text-sm"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                @if($this->linkedEmployee)
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $this->linkedEmployee->status === \App\Models\Employee::STATUS_RESIGNED ? 'bg-gray-500/15 text-gray-400' : 'bg-emerald-500/15 text-emerald-400' }}">
                        {{ $this->linkedEmployee->status }}
                    </span>
                    <span class="ml-2 text-xs text-muted">{{ __('Dikelola di menu Employee') }}</span>
                @else
                    <span class="text-xs text-muted">-</span>
                @endif
            </div>
        </div>

        {{-- Access Login --}}
        <div>
            <label class="text-xs text-muted">{{ __('Access Login') }}</label>
            <select wire:model="status"
                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="Enable">Enable</option>
                <option value="Disable">Disable</option>
            </select>
            @error('status') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-muted mt-1">Enable: user dapat mengakses login web. Disable: user tidak dapat mengakses login web.</p>
        </div>

        {{-- Role --}}
        <div>
            <label class="text-xs text-muted">{{ __('Role') }} <span class="text-red-400">*</span></label>
            <select wire:model="role"
                class="w-full px-3 py-2 rounded-lg text-sm mt-1 transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                required>
                <option value="">{{ __('Pilih Role') }}</option>
                @foreach($this->getRoleList() as $r)
                    <option value="{{ $r }}">{{ $this->getRoleLabel($r) }}</option>
                @endforeach
            </select>
            @error('role') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <span wire:loading.remove wire:target="update">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span wire:loading wire:target="update">{{ __('Menyimpan') }}...</span>
                <span wire:loading.remove wire:target="update">{{ __('Simpan Perubahan') }}</span>
            </button>
            <a href="{{ route('admin.users.index') }}" wire:navigate
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                {{ __('Batal') }}
            </a>
        </div>
    </form>

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

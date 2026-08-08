<div class="glass-card p-6 space-y-5"
    x-data="{ errors: {} }"
    x-on:validation-error.window="errors = $event.detail.errors[0]"
    x-on:asset-updated.window="errors = {}; window.location = '{{ route('admin.assets.index') }}'">

    {{-- No Asset (read-only) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('No Asset') }}</label>
            <input type="text" value="{{ $noAsset }}" disabled
                class="w-full px-4 py-2 rounded-lg text-sm"
                style="background: var(--color-bg-tertiary); border: 1px solid var(--color-border); color: var(--color-text-muted);" />
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Kategori') }} <span class="text-red-400">*</span></label>
            <input wire:model="kategori" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="Laptop, Desktop, Printer..." />
            @error('kategori') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Brand & Tipe --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Brand') }} <span class="text-red-400">*</span></label>
            <input wire:model="brand" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="HP, Lenovo, Dell..." />
            @error('brand') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Tipe') }} <span class="text-red-400">*</span></label>
            <input wire:model="tipe" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="ProBook 440, ThinkPad T14..." />
            @error('tipe') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Nama Perangkat & No Serial --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Nama Perangkat') }} <span class="text-red-400">*</span></label>
            <input wire:model="namaPerangkat" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="Laptop HRD-01" />
            @error('namaPerangkat') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('No Serial') }}</label>
            <input wire:model="noSerial" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="{{ __('Nomor serial perangkat') }}" />
            @error('noSerial') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Status (auto) & Pengguna --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Status') }}</label>
            <div class="w-full px-4 py-2 rounded-lg text-sm"
                style="background: var(--color-bg-tertiary); border: 1px solid var(--color-border); color: var(--color-text-muted);">
                <span x-show="!$wire.assignedEmployeeId" class="text-amber-400">Inactive</span>
                <span x-show="$wire.assignedEmployeeId" class="text-emerald-400">Active</span>
            </div>
            <p class="text-xs text-muted mt-1">{{ __('Otomatis berdasarkan asign pengguna') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Pengguna') }}</label>
            <select wire:model="assignedEmployeeId"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                <option value="">-- {{ __('Tidak Ada Pengguna') }} --</option>
                @foreach($employees as $u)
                    <option value="{{ $u->nik }}">{{ $u->name }} ({{ $u->nik ?? '-' }})</option>
                @endforeach
            </select>
            @error('assignedEmployeeId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Operating Unit --}}
    <div>
        <label class="block text-sm font-medium text-secondary mb-1">Operating Unit</label>
        <select wire:model="operatingUnit"
            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
            <option value="">-- {{ __('Pilih Operating Unit') }} --</option>
            @foreach($sites as $s)
                <option value="{{ $s->id_site }}">{{ $s->site }} ({{ $s->id_site }})</option>
            @endforeach
        </select>
        @error('operatingUnit') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Site Location Asset --}}
    <div>
        <label class="block text-sm font-medium text-secondary mb-1">Site Location Asset</label>
        <select wire:model="siteLocationAsset"
            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
            <option value="">-- {{ __('Pilih Site') }} --</option>
            @foreach($sites as $s)
                <option value="{{ $s->id_site }}">{{ $s->site }} ({{ $s->id_site }})</option>
            @endforeach
        </select>
        @error('siteLocationAsset') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-2">
        <button wire:click="update" wire:loading.attr="disabled"
            class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
            style="background: var(--color-primary); color: var(--color-button-text);">
            <span wire:loading.remove wire:target="update">{{ __('Simpan Perubahan') }}</span>
            <span wire:loading wire:target="update">{{ __('Menyimpan') }}...</span>
        </button>
        <a href="{{ route('admin.assets.index') }}" wire:navigate
            class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
            {{ __('Batal') }}
        </a>
    </div>
</div>

<div class="glass-card p-6 space-y-5"
    x-data="{ errors: {} }"
    x-on:validation-error.window="errors = $event.detail.errors[0]"
    x-on:site-updated.window="errors = {}; window.location = '{{ route('admin.sites.index') }}'">

    {{-- ID Site (read-only) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('ID Site') }}</label>
            <input type="text" value="{{ $idSite }}" disabled
                class="w-full px-4 py-2 rounded-lg text-sm"
                style="background: var(--color-bg-tertiary); border: 1px solid var(--color-border); color: var(--color-text-muted);" />
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Nama Site') }} <span class="text-red-400">*</span></label>
            <input wire:model="site" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="Contoh: Head Office" />
            @error('site') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Buss & ID Corp --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">Buss</label>
            <input wire:model="buss" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="Buss" maxlength="1" />
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('ID Corp') }}</label>
            <input wire:model="idCorp" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="ID Corp" maxlength="3" />
        </div>
    </div>

    {{-- Country --}}
    <div>
        <label class="block text-sm font-medium text-secondary mb-1">{{ __('Negara') }}</label>
        <input wire:model="country" type="text"
            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
            placeholder="Indonesia" />
    </div>

    {{-- Province & City --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Provinsi') }}</label>
            <input wire:model="provincy" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="DKI Jakarta" />
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1">{{ __('Kota') }}</label>
            <input wire:model="city" type="text"
                class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
                placeholder="Jakarta Selatan" />
        </div>
    </div>

    {{-- Address --}}
    <div>
        <label class="block text-sm font-medium text-secondary mb-1">{{ __('Alamat') }}</label>
        <textarea wire:model="address" rows="3"
            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
            placeholder="{{ __('Alamat lengkap site') }}"></textarea>
    </div>

    {{-- URL Maps --}}
    <div>
        <label class="block text-sm font-medium text-secondary mb-1">{{ __('URL Google Maps') }}</label>
        <input wire:model="urlMaps" type="url"
            class="w-full px-4 py-2 rounded-lg text-sm transition-colors duration-200"
            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);"
            placeholder="https://maps.google.com/..." />
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-2">
        <button wire:click="update" wire:loading.attr="disabled"
            class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
            style="background: var(--color-primary); color: var(--color-button-text);">
            <span wire:loading.remove wire:target="update">{{ __('Simpan Perubahan') }}</span>
            <span wire:loading wire:target="update">{{ __('Menyimpan') }}...</span>
        </button>
        <a href="{{ route('admin.sites.index') }}" wire:navigate
            class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
            {{ __('Batal') }}
        </a>
    </div>
</div>

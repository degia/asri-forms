<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Import Assets') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Import data asset dari file CSV') }}</p>
        </div>
        <a href="{{ route('admin.assets.import.template') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ __('Download Template') }}
        </a>
    </div>

    <livewire:admin.assets.import-csv />
</div>

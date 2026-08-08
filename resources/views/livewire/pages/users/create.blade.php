<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component {}; ?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-primary">Tambah User</h1>
        <p class="text-sm text-muted mt-1">Buat akun pengguna baru</p>
    </div>

    <livewire:users.create-form />
</div>

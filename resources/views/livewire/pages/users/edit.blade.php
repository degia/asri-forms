<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component {
    public string $userEmail = '';
}; ?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-primary">Edit User</h1>
        <p class="text-sm text-muted mt-1">Ubah data pengguna</p>
    </div>
    <div class="glass-card p-4 sm:p-8">
        <livewire:users.edit-form :userEmail="$userEmail" />
    </div>
</div>

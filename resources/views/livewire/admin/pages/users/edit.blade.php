<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {
    public string $userEmail = '';
}; ?>

<div class="max-w-2xl">
    <livewire:users.edit-form :email="$userEmail" />
</div>

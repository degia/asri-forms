<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {}; ?>

<div class="max-w-2xl">
    <livewire:users.create-form />
</div>

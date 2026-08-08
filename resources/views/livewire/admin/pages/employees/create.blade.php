<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {}; ?>

<div class="max-w-2xl">
    <livewire:admin.employees.create-form />
</div>

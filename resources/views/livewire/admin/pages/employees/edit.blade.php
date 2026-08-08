<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {
    public string $nik = '';
}; ?>

<div class="max-w-2xl">
    <livewire:admin.employees.edit-form :nik="$nik" />
</div>

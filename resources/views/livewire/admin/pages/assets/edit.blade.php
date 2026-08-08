<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {
    public int $id = 0;
}; ?>

<div>
    <livewire:admin.assets.edit-form :$id />
</div>

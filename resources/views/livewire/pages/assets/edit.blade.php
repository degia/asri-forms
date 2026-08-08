<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component {
    public int $id = 0;
}; ?>

<div>
    <livewire:assets.edit :$id />
</div>

<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {
    public string $idSite = '';
}; ?>

<div>
    <livewire:admin.sites.edit-form :$idSite />
</div>

<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component
{
    public function mount(): void
    {
    }
}; ?>

<div>
    <livewire:forms.search />
</div>

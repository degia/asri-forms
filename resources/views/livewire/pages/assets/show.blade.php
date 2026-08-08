<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component
{
    public string $id = '';

    public function mount(string $id): void
    {
        $this->id = $id;
    }
}; ?>

<div>
    <livewire:assets.detail :id="$id" />
</div>

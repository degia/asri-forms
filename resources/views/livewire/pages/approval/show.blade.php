<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component
{
    public string $type = '';
    public string $id = '';

    public function mount(string $type, string $id): void
    {
        $allowed = ['pemeriksaan', 'perawatan'];
        if (!in_array($type, $allowed)) {
            abort(404);
        }
        $this->type = $type;
        $this->id = $id;
    }
}; ?>

<div>
    <livewire:approval.review-form :type="$type" :id="$id" />
</div>

<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component
{
    public ?int $formId = null;

    public function mount(): void
    {
        if (!auth()->user()->hasAnyRole(['admin', 'teknisi'])) {
            abort(403);
        }
        $this->formId = request('formId') ? (int) request('formId') : null;
    }
}; ?>

<div>
    <livewire:pemeriksaan.create-form :formId="$formId" />
</div>

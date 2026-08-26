<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component
{
    public ?int $formId = null;
    public ?int $assetId = null;

    public function mount(): void
    {
        if (!auth()->user()->hasAnyRole(['admin', 'teknisi'])) {
            abort(403);
        }
        $this->formId = request('formId') ? (int) request('formId') : null;
        $this->assetId = request('assetId') ? (int) request('assetId') : null;
    }
}; ?>

<div>
    <livewire:perawatan.create-form :formId="$formId" :assetId="$assetId" />
</div>

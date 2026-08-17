<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {
    public string $nik = '';
    public ?int $page = null;

    public function mount(string $nik): void
    {
        $this->nik = $nik;
        $this->page = request()->integer('page');
    }
}; ?>

<div class="max-w-2xl">
    <livewire:admin.employees.edit-form :nik="$nik" :page="$page" />
</div>

<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.admin-layout')] class extends Component {}; ?>

<div>
    <livewire:admin.activity-log.index />
</div>

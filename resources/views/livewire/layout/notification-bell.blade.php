<div class="relative" x-data="{ open: false }" @click.away="open = false; $wire.set('showDropdown', false)">
    <button @click="$wire.toggleDropdown(); open = !open"
        class="relative p-2 rounded-lg transition-colors duration-200"
        style="color: var(--color-text-secondary);"
        title="Notifikasi Approval">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($pendingCount > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full"
                wire:poll.5s="loadNotifications">
                {{ $pendingCount > 99 ? '99+' : $pendingCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    @if($showDropdown)
        <div class="absolute right-0 mt-2 w-80 rounded-xl overflow-hidden z-50"
            style="background: var(--color-card-bg); border: 1px solid var(--color-card-border); backdrop-filter: blur(16px); box-shadow: 0 4px 30px var(--color-glass-shadow);">
            <div class="px-4 py-3 border-b" style="border-color: var(--color-border);">
                <h3 class="text-sm font-bold text-primary">Approval Pending</h3>
                <p class="text-xs text-muted">{{ $pendingCount }} menunggu persetujuan Anda</p>
            </div>

            @if(count($notifications) === 0)
                <div class="px-4 py-8 text-center">
                    <svg class="w-8 h-8 mx-auto mb-2" style="color: var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-muted">Tidak ada approval pending</p>
                </div>
            @else
                <div class="max-h-80 overflow-y-auto divide-y" style="border-color: var(--color-border);">
                    @foreach($notifications as $notif)
                        <a href="{{ route('approval.show', ['type' => $notif['type'], 'id' => $notif['id']]) }}"
                            wire:navigate
                            class="block px-4 py-3 transition-colors duration-150"
                            onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'"
                            onmouseout="this.style.backgroundColor=''"
                            @click="open = false; $wire.set('showDropdown', false)">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center shrink-0
                                    {{ $notif['type'] === 'pemeriksaan' ? 'bg-blue-500/15 text-blue-400' : 'bg-purple-500/15 text-purple-400' }}">
                                    @if($notif['type'] === 'pemeriksaan')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-primary font-medium truncate">{{ $notif['nomor_form'] }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ $notif['submitted_by'] }} &middot; {{ $notif['device_name'] }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full
                                            {{ $notif['type'] === 'pemeriksaan' ? 'bg-blue-500/15 text-blue-400' : 'bg-purple-500/15 text-purple-400' }}">
                                            {{ ucfirst($notif['type']) }}
                                        </span>
                                        <span class="text-[10px] text-muted">{{ $notif['level'] }}</span>
                                    </div>
                                    <p class="text-[10px] text-muted mt-0.5">{{ $notif['created_at'] }}</p>
                                </div>
                                <svg class="w-4 h-4 shrink-0 mt-1" style="color: var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

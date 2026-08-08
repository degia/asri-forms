<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Activity Log') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Riwayat aktivitas pengguna') }}</p>
        </div>
        <button wire:click="clearAll" wire:confirm="{{ __('Hapus semua log aktivitas?') }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
            style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: rgb(239, 68, 68);">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            {{ __('Hapus Semua') }}
        </button>
    </div>

    @if (session('success'))
        <div class="p-3 rounded-lg text-sm" style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.2); color: rgb(52, 211, 153);">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-card p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari deskripsi...') }}" 
                    class="w-full pl-10 pr-4 py-2 rounded-lg text-sm bg-transparent border" 
                    style="border-color: var(--color-border); color: var(--color-text-primary);">
            </div>
            <select wire:model.live="filterType" 
                class="px-3 py-2 rounded-lg text-sm bg-transparent border" 
                style="border-color: var(--color-border); color: var(--color-text-primary);">
                <option value="">{{ __('Semua Tipe') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterUserId" 
                class="px-3 py-2 rounded-lg text-sm bg-transparent border" 
                style="border-color: var(--color-border); color: var(--color-text-primary);">
                <option value="">{{ __('Semua User') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->email }}">{{ $user->name }}</option>
                @endforeach
            </select>
            @if($search || $filterType || $filterUserId)
                <button wire:click="resetFilters"
                    class="px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    {{ __('Reset') }}
                </button>
            @endif
        </div>
    </div>

    <div class="glass-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b" style="border-color: var(--color-border);">
                        <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap cursor-pointer" wire:click="sortBy('created_at')">
                            {{ __('Waktu') }}
                            @if($sortField === 'created_at') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </th>
                        <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('User') }}</th>
                        <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Tipe') }}</th>
                        <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap">{{ __('Deskripsi') }}</th>
                        <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap hidden md:table-cell">{{ __('Detail') }}</th>
                        <th class="px-3 py-2 text-left text-muted font-medium whitespace-nowrap hidden lg:table-cell">{{ __('IP') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--color-border);">
                    @forelse($logs as $log)
                        <tr class="hover:opacity-80 transition-opacity">
                            <td class="px-3 py-2 text-muted whitespace-nowrap">{{ $log->created_at->format('d M H:i') }}</td>
                            <td class="px-3 py-2 text-primary whitespace-nowrap">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                    style="background: {{ match($log->type) { 'create' => 'rgba(52, 211, 153, 0.15)', 'update' => 'rgba(59, 130, 246, 0.15)', 'delete' => 'rgba(239, 68, 68, 0.15)', 'import' => 'rgba(168, 85, 247, 0.15)', 'backup' => 'rgba(245, 158, 11, 0.15)', 'restore' => 'rgba(52, 211, 153, 0.15)', 'login' => 'rgba(59, 130, 246, 0.15)', default => 'rgba(148, 163, 184, 0.15)' } }};
                                    color: {{ match($log->type) { 'create' => 'rgb(52, 211, 153)', 'update' => 'rgb(59, 130, 246)', 'delete' => 'rgb(239, 68, 68)', 'import' => 'rgb(168, 85, 247)', 'backup' => 'rgb(245, 158, 11)', 'restore' => 'rgb(52, 211, 153)', 'login' => 'rgb(59, 130, 246)', default => 'rgb(148, 163, 184)' } }};
                                    ">
                                    {{ ucfirst($log->type) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-secondary">{{ $log->description }}</td>
                            <td class="px-3 py-2 text-muted hidden md:table-cell max-w-[200px] truncate">
                                @if($log->model_type)
                                    <span class="text-xs">{{ class_basename($log->model_type) }}#{{ $log->model_id }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-muted font-mono hidden lg:table-cell">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-muted text-sm">
                                {{ __('Belum ada aktivitas') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>

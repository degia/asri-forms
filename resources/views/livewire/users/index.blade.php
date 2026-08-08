<div class="space-y-6"
    x-data x-on:user-deleted.window="$wire.$refresh()" x-on:user-updated.window="$wire.$refresh()">
    {{-- Toast --}}
    <div x-data="{ toast: false, message: '', type: 'success' }"
        @show-toast.window="toast = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => toast = false, 4000)"
        x-show="toast" x-transition
        class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-xs"
        :class="type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
        x-text="message">
    </div>
    @if (session()->has('success'))
        <div class="p-3 rounded-lg text-sm"
            style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-3 rounded-lg text-sm"
            style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;">
            {{ session('error') }}
        </div>
    @endif
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Data Users') }}</h1>
            <p class="text-sm text-muted mt-1">{{ __('Daftar seluruh pengguna sistem') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm text-muted">{{ $users->total() }} {{ __('user') }}</span>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Export') }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                    class="absolute right-0 mt-1 w-44 rounded-lg shadow-lg z-30 py-1"
                    style="background: var(--color-card-bg); border: 1px solid var(--color-card-border);"
                    x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <a href="{{ route('admin.users.export', ['format' => 'pdf']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as PDF') }}</a>
                    <a href="{{ route('admin.users.export', ['format' => 'xlsx']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLSX') }}</a>
                    <a href="{{ route('admin.users.export', ['format' => 'xls']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as XLS') }}</a>
                    <a href="{{ route('admin.users.export', ['format' => 'html']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as HTML') }}</a>
                    <a href="{{ route('admin.users.export', ['format' => 'csv']) }}" class="block px-4 py-2 text-xs text-primary hover:bg-[var(--color-bg-tertiary)]">{{ __('Export as CSV') }}</a>
                </div>
            </div>
            <a href="{{ route('admin.users.import') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import CSV
            </a>
            <a href="{{ route('admin.users.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200"
                style="background: var(--color-primary); color: var(--color-button-text);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Tambah User') }}
            </a>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="glass-card p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-muted uppercase tracking-wider">{{ __('Filter Data') }}</p>
            @if($filterName || $filterEmail || $filterNik || $filterSite || $filterRole || $filterStatusEmployee || $filterAccessLogin)
                <a href="{{ route('admin.users.index') }}" wire:navigate
                    class="inline-flex items-center px-3 py-1 rounded-lg text-xs transition-colors duration-200"
                    style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                    {{ __('Reset') }}
                </a>
            @endif
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Nama') }}</label>
                <input wire:model.live.debounce.300ms="filterName" type="text" placeholder="{{ __('Nama user') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Email') }}</label>
                <input wire:model.live.debounce.300ms="filterEmail" type="text" placeholder="{{ __('Email') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">NIK</label>
                <input wire:model.live.debounce.300ms="filterNik" type="text" placeholder="NIK..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Site') }}</label>
                <input wire:model.live.debounce.300ms="filterSite" type="text" placeholder="{{ __('Site') }}..."
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Role') }}</label>
                <select wire:model.live="filterRole"
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua Role') }}</option>
                    @foreach($this->getRoleList() as $role)
                        <option value="{{ $role }}">{{ $this->getRoleLabel($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Status Employee') }}</label>
                <select wire:model.live="filterStatusEmployee"
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="Active">Active</option>
                    <option value="Resigned">Resigned</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1">{{ __('Access Login') }}</label>
                <select wire:model.live="filterAccessLogin"
                    class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                    style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="Enable">Enable</option>
                    <option value="Disable">Disable</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    @if($users->count() > 0)
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-border);">
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" wire:click="toggleSelectAll"
                                    class="rounded cursor-pointer" style="accent-color: var(--color-primary);"
                                    @checked($allSelected)>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider cursor-pointer hover:text-secondary transition-colors"
                                wire:click="toggleSort('name')">
                                <span class="flex items-center gap-1">
                                    {{ __('Nama') }}
                                    @if($sortBy === 'name')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                        </svg>
                                    @endif
                                </span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden sm:table-cell">NIK</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Role') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Status Employee') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Access Login') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider hidden lg:table-cell">{{ __('Site') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($users as $user)
                            <tr class="transition-colors duration-150" style="hover: background: var(--color-glass-bg);">
                                <td class="px-4 py-3 w-10">
                                    <input type="checkbox" value="{{ $user->email }}" wire:model.live="selected"
                                        class="rounded cursor-pointer" style="accent-color: var(--color-primary);">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold"
                                             style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                            {{ strtoupper(substr($user->employee?->name ?? $user->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-primary truncate">{{ $user->employee?->name ?? $user->name }}</div>
                                            <div class="text-xs text-muted truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-secondary hidden sm:table-cell">{{ $user->nik ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @forelse($user->roles as $role)
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $this->getRoleBadge($role->name) }}">
                                            {{ $this->getRoleLabel($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-muted">-</span>
                                    @endforelse
                                </td>
                                <td class="px-4 py-3">
                                    @if($user->employee)
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $this->getEmployeeStatusBadge($user->employee->status) }}">
                                            {{ $this->getEmployeeStatusLabel($user->employee->status) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-muted">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $this->getAccessLoginBadge($user->status) }}">
                                        {{ $this->getAccessLoginLabel($user->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-secondary hidden lg:table-cell">{{ $user->siteName ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.users.edit', $user->email) }}" wire:navigate
                                            class="p-1.5 rounded-lg transition-colors duration-200"
                                            style="color: var(--color-text-secondary);"
                                            title="{{ __('Edit') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button wire:click="confirmDelete('{{ $user->email }}', '{{ addslashes($user->employee?->name ?? $user->name) }}')"
                                            class="p-1.5 rounded-lg transition-colors duration-200 text-red-400 hover:text-red-300"
                                            title="{{ __('Hapus') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(count($selected) > 0)
            <div class="glass-card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                style="border-color: rgba(245, 158, 11, 0.4);">
                <p class="text-sm text-primary">{{ count($selected) }} {{ __('user terpilih') }}</p>
                <div class="flex items-center gap-2">
                    <button wire:click="openBulkEdit" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('Edit Massal') }}
                    </button>
                    <button wire:click="confirmBulkDelete" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-red-500 text-white hover:bg-red-600 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Hapus Terpilih') }}
                    </button>
                </div>
            </div>
        @endif

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @else
        <div class="glass-card p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-3 text-muted">{{ __('Tidak ada user ditemukan') }}</p>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus User') }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus user') }} <span class="font-semibold text-primary">{{ $deleteUserName }}</span>? {{ __('User akan di-soft-delete dan tidak akan bisa login lagi.') }}</p>
                <div class="flex gap-2">
                    <button wire:click="cancelDelete" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="deleteUser" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Hapus') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelBulkDelete()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelBulkDelete()">
                <h3 class="text-lg font-bold text-primary">{{ __('Hapus User Terpilih') }}</h3>
                <p class="text-sm text-muted">{{ __('Yakin ingin menghapus') }} <span class="font-semibold text-primary">{{ count($selected) }} {{ __('user') }}</span> {{ __('yang terpilih? User akan di-soft-delete.') }}</p>
                <div class="flex gap-2">
                    <button wire:click="cancelBulkDelete" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="bulkDelete" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm bg-red-500 text-white hover:bg-red-600 transition-all duration-200">{{ __('Hapus') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Edit Modal --}}
    @if($showBulkEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.cancelBulkEdit()">
            <div class="glass-card p-6 w-full max-w-md space-y-4" @click.away="$wire.cancelBulkEdit()">
                <h3 class="text-lg font-bold text-primary">{{ __('Edit Massal') }} ({{ count($selected) }} {{ __('user') }})</h3>
                <div>
                    <label class="block text-xs font-medium text-muted mb-1">{{ __('Field') }}</label>
                    <select wire:model="bulkEditField"
                        class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                        style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                        <option value="">{{ __('Pilih Field') }}</option>
                        <option value="role">Role</option>
                        <option value="access_login">{{ __('Access Login') }}</option>
                        <option value="name">{{ __('Nama') }}</option>
                        <option value="email">Email</option>
                        <option value="nik">NIK</option>
                    </select>
                    @error('bulkEditField') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted mb-1">{{ __('Nilai Baru') }}</label>
                    @if($bulkEditField === 'role')
                        <select wire:model="bulkEditValue"
                            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                            <option value="">{{ __('Pilih Role') }}</option>
                            @foreach($this->getRoleList() as $role)
                                <option value="{{ $role }}">{{ $this->getRoleLabel($role) }}</option>
                            @endforeach
                        </select>
                    @elseif($bulkEditField === 'access_login')
                        <select wire:model="bulkEditValue"
                            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                            <option value="">{{ __('Pilih Access Login') }}</option>
                            <option value="Enable">Enable</option>
                            <option value="Disable">Disable</option>
                        </select>
                    @else
                        <input type="text" wire:model="bulkEditValue" placeholder="{{ __('Nilai baru') }}"
                            class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                            style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);" />
                    @endif
                    @error('bulkEditValue') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <button wire:click="cancelBulkEdit" type="button" class="glass-button-secondary text-sm flex-1">{{ __('Batal') }}</button>
                    <button wire:click="bulkEdit" type="button" class="flex-1 px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">{{ __('Simpan') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>

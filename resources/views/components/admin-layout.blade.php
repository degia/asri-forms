@props(['header' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ Auth::user()->theme_preference ?? 'light' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <meta name="theme-color" content="#0a0a0a">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <title>Admin Panel - {{ config('app.name', 'ASRI Form Perangkat') }}</title>

        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <link rel="apple-touch-icon" href="{{ asset('icon-192.svg') }}">
        <link rel="icon" href="{{ asset('icon-192.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const html = document.documentElement;
                html.style.transition = 'background-color 0.3s ease, color 0.3s ease';

                window.addEventListener('theme-changed', (e) => {
                    html.classList.toggle('dark', e.detail.theme === 'dark');
                });
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }
        </script>
    </head>
    <body class="font-sans antialiased transition-colors duration-300" style="background-color: var(--color-bg-primary); color: var(--color-text-primary);">
        <div class="min-h-screen pt-16" style="background-color: var(--color-bg-primary);">
            <livewire:layout.navigation />

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    {{-- Sidebar --}}
                    <aside class="w-full lg:w-56 shrink-0">
                        <div class="glass-card p-2 sticky top-24">
                            <div class="px-3 py-2 mb-1">
                                <h2 class="text-sm font-bold text-primary">{{ __('Admin Panel') }}</h2>
                            </div>
                            <nav class="space-y-0.5">
                                <a href="{{ route('admin.dashboard') }}"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.dashboard') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    {{ __('Dashboard') }}
                                </a>
                                <a href="{{ route('admin.sites.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.sites.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.sites.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Sites') }}
                                </a>
                                <a href="{{ route('admin.users.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.users.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    {{ __('Users') }}
                                </a>
                                <a href="{{ route('admin.employees.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.employees.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.employees.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    {{ __('Employees') }}
                                </a>
                                <a href="{{ route('admin.structure-organization.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.structure-organization.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.structure-organization.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h12M3 18h7"/>
                                    </svg>
                                    {{ __('Structure Organization') }}
                                </a>
                                <a href="{{ route('admin.assets.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.assets.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.assets.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    {{ __('Assets') }}
                                </a>
                                <div class="pt-2 pb-1 px-3">
                                    <h3 class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--color-text-muted);">{{ __('Forms') }}</h3>
                                </div>
                                <a href="{{ route('admin.pemeriksaan.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.pemeriksaan.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.pemeriksaan.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    {{ __('Form PMR') }}
                                </a>
                                <a href="{{ route('admin.perawatan.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.perawatan.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.perawatan.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Form PWT') }}
                                </a>
                                <a href="{{ route('admin.pengembalian.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.pengembalian.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.pengembalian.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h2m-9 5h10a2 2 0 002-2V8a2 2 0 00-2-2h-3l-2-3H9L7 6H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('Form Pengembalian') }}
                                </a>
                                <div class="pt-2 pb-1 px-3">
                                    <h3 class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--color-text-muted);">{{ __('System') }}</h3>
                                </div>
                                <a href="{{ route('admin.backup.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.backup.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.backup.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                    {{ __('Backup') }}
                                </a>
                                <a href="{{ route('admin.activity-log.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.activity-log.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.activity-log.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    {{ __('Activity Log') }}
                                </a>
                                <a href="{{ route('admin.system-log.index') }}" wire:navigate
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.system-log.*') ? 'admin-nav-active' : '' }}"
                                    style="{{ request()->routeIs('admin.system-log.*') ? '' : 'color: var(--color-text-secondary);' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('System Log') }}
                                </a>
                            </nav>
                        </div>
                    </aside>

                    {{-- Main Content --}}
                    <div class="flex-1 min-w-0">
                        @if (isset($header) && $header !== null)
                            <div class="mb-6">
                                {{ $header }}
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

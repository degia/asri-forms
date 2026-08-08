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

        <title>{{ config('app.name', 'ASRI Form Perangkat') }}</title>

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
        <div class="min-h-screen pb-16 sm:pb-0 pt-16" style="background-color: var(--color-bg-primary);">
            <livewire:layout.navigation />

            @if (isset($header) && $header !== null)
                <header style="background-color: var(--color-bg-secondary); border-bottom: 1px solid var(--color-border);">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>

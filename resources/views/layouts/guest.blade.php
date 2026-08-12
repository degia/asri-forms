<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ASRI Form Perangkat') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: var(--color-bg-primary); color: var(--color-text-primary);">
        <div class="min-h-screen flex items-center justify-center px-4" style="background-color: var(--color-bg-secondary);">
            <div class="w-full max-w-md">
                <div class="flex flex-col items-center mb-6">
                    <img src="{{ asset('images/asri.png') }}" alt="ASRI"
                        class="w-20 h-20 object-contain rounded-lg"
                        style="background: var(--color-card-bg); border: 1px solid var(--color-card-border); padding: 0.5rem;">
                    <h1 class="mt-4 text-2xl font-bold text-primary">{{ config('app.name', 'ASRI Form Perangkat') }}</h1>
                    <p class="mt-1 text-sm text-muted">IT Department &mdash; ASRI</p>
                </div>
                {{ $slot }}
            </div>
            <p class="fixed bottom-4 left-0 right-0 text-center text-xs text-muted">&copy; 2026 Nuvista</p>
        </div>
        </div>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ASRI Form Perangkat') }} - Gangguan Koneksi</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: var(--color-bg-primary); color: var(--color-text-primary);">
        <div class="min-h-screen flex items-center justify-center px-6" style="background-color: var(--color-bg-secondary);">
            <div class="glass-card w-full max-w-md p-8 text-center">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full" style="background-color: #fef3c7;">
                    <svg class="h-8 w-8" style="color: #b45309;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>

                <h1 class="text-xl font-semibold mb-2 text-primary">Koneksi Database Bermasalah</h1>
                <p class="text-secondary text-sm mb-6 leading-relaxed">
                    Tidak dapat terhubung ke server database saat ini.
                    Silakan coba beberapa saat lagi atau hubungi administrator.
                </p>

                <a href="{{ url('/') }}" class="glass-button-primary inline-block no-underline">Coba Lagi</a>
            </div>
        </div>
    </body>
</html>

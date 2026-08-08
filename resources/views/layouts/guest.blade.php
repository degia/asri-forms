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
        <div class="min-h-screen flex items-center justify-center" style="background-color: var(--color-bg-secondary);">
            <div class="w-full max-w-md px-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

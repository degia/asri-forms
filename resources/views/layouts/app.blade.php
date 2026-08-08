@props(['header' => null])

<x-app-layout :header="$header">
    {{ $slot }}
</x-app-layout>

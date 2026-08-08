@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}
    @if($active)
        style="border-color: var(--color-border-strong); color: var(--color-text-primary);"
    @else
        style="color: var(--color-text-secondary);"
    @endif
    @if(!$active)
        onmouseover="this.style.color='var(--color-text-primary)'; this.style.borderColor='var(--color-border)'"
        onmouseout="this.style.color='var(--color-text-secondary)'; this.style.borderColor='transparent'"
    @endif
>
    {{ $slot }}
</a>

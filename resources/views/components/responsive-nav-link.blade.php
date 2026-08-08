@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}
    @if($active)
        style="border-color: var(--color-border-strong); color: var(--color-text-primary); background-color: var(--color-bg-tertiary);"
    @else
        style="color: var(--color-text-secondary);"
        onmouseover="this.style.color='var(--color-text-primary)'; this.style.backgroundColor='var(--color-bg-tertiary)'; this.style.borderColor='var(--color-border)'"
        onmouseout="this.style.color='var(--color-text-secondary)'; this.style.backgroundColor=''; this.style.borderColor='transparent'"
    @endif
>
    {{ $slot }}
</a>

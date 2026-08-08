@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-emerald-500']) }}>
        {{ $status }}
    </div>
@endif

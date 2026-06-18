@props(['variant' => 'brand'])

@php
    $colors = [
        'brand' => 'bg-brand-500 text-white',
        'success' => 'bg-success-500 text-white',
        'warning' => 'bg-warning-400 text-gray-900',
        'error' => 'bg-error-500 text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-r-full px-3 py-1 text-xs font-semibold '.($colors[$variant] ?? $colors['brand'])]) }}>
    {{ $slot }}
</span>

@props(['value' => 0, 'label' => null, 'variant' => 'brand'])

@php
    $safeValue = max(0, min(100, (int) $value));
    $colors = [
        'brand' => 'bg-brand-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'error' => 'bg-error-500',
    ];
@endphp

<div {{ $attributes }}>
    @if ($label)
        <div class="mb-2 flex justify-between text-xs font-medium text-gray-600 dark:text-gray-300">
            <span>{{ $label }}</span>
            <span>{{ $safeValue }}%</span>
        </div>
    @endif
    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800" role="progressbar" aria-valuenow="{{ $safeValue }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full transition-all duration-300 {{ $colors[$variant] ?? $colors['brand'] }}" style="width: {{ $safeValue }}%"></div>
    </div>
</div>

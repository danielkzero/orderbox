@props([
    'label',
    'icon',
    'href' => null,
    'variant' => 'neutral',
    'type' => 'submit',
])

@php
    $variants = [
        'neutral' => 'border-gray-200 text-gray-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:text-gray-400 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-400',
        'primary' => 'border-brand-200 text-brand-600 hover:bg-brand-50 dark:border-brand-500/30 dark:text-brand-400 dark:hover:bg-brand-500/10',
        'success' => 'border-success-200 text-success-600 hover:bg-success-50 dark:border-success-500/30 dark:text-success-400 dark:hover:bg-success-500/10',
        'warning' => 'border-warning-200 text-warning-600 hover:bg-warning-50 dark:border-warning-500/30 dark:text-warning-400 dark:hover:bg-warning-500/10',
        'danger' => 'border-error-200 text-error-600 hover:bg-error-50 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10',
    ];

    $classes = 'inline-flex size-9 items-center justify-center rounded-lg border bg-white shadow-theme-xs transition focus:outline-hidden focus:ring-3 focus:ring-brand-500/15 dark:bg-gray-900 '.$variants[$variant];
@endphp

<x-tooltip :text="$label">
    @if ($href)
        <a href="{{ $href }}" aria-label="{{ $label }}" {{ $attributes->class($classes) }}>
            <x-icon :name="$icon" class="size-4" />
        </a>
    @else
        <button type="{{ $type }}" aria-label="{{ $label }}" {{ $attributes->class($classes) }}>
            <x-icon :name="$icon" class="size-4" />
        </button>
    @endif
</x-tooltip>

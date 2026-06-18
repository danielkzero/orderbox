@props(['text', 'position' => 'top'])

@php
    $positions = [
        'top' => 'bottom-full left-1/2 mb-2 -translate-x-1/2',
        'bottom' => 'left-1/2 top-full mt-2 -translate-x-1/2',
        'left' => 'right-full top-1/2 mr-2 -translate-y-1/2',
        'right' => 'left-full top-1/2 ml-2 -translate-y-1/2',
    ];
@endphp

<span class="group/tooltip relative inline-flex">
    {{ $slot }}
    <span
        class="pointer-events-none absolute z-[100] hidden whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-tooltip transition group-hover/tooltip:block group-hover/tooltip:opacity-100 group-focus-within/tooltip:block group-focus-within/tooltip:opacity-100 dark:bg-gray-700 {{ $positions[$position] ?? $positions['top'] }}"
        role="tooltip"
    >
        {{ $text }}
    </span>
</span>

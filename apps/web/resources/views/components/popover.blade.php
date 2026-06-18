@props(['title', 'trigger', 'placement' => 'bottom'])

@php
    $positions = [
        'bottom' => 'left-1/2 top-full mt-2 -translate-x-1/2',
        'top' => 'bottom-full left-1/2 mb-2 -translate-x-1/2',
        'left' => 'right-full top-1/2 mr-2 -translate-y-1/2',
        'right' => 'left-full top-1/2 ml-2 -translate-y-1/2',
    ];
@endphp

<span class="relative inline-flex" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button
        type="button"
        @click="open = ! open"
        @click.outside="open = false"
        class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
        :aria-expanded="open"
    >
        {{ $trigger }}
    </button>
    <span
        x-show="open"
        x-cloak
        x-transition
        class="absolute z-50 w-72 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 {{ $positions[$placement] ?? $positions['bottom'] }}"
        role="dialog"
    >
        <strong class="block text-sm text-gray-800 dark:text-white/90">{{ $title }}</strong>
        <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">{{ $slot }}</span>
    </span>
</span>

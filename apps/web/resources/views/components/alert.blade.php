@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $styles = [
        'success' => [
            'wrapper' => 'border-success-200 bg-success-50 text-success-800 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-200',
            'icon' => 'bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-300',
            'path' => '<path d="m8 12 2.5 2.5L16 9"/><circle cx="12" cy="12" r="9"/>',
        ],
        'warning' => [
            'wrapper' => 'border-warning-200 bg-warning-50 text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-200',
            'icon' => 'bg-warning-100 text-warning-600 dark:bg-warning-500/20 dark:text-warning-300',
            'path' => '<path d="M12 8v5M12 17h.01"/><path d="M10.3 3.7 2.5 17.2A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.8L13.7 3.7a2 2 0 0 0-3.4 0Z"/>',
        ],
        'error' => [
            'wrapper' => 'border-error-200 bg-error-50 text-error-800 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-200',
            'icon' => 'bg-error-100 text-error-600 dark:bg-error-500/20 dark:text-error-300',
            'path' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>',
        ],
        'info' => [
            'wrapper' => 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-200',
            'icon' => 'bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300',
            'path' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        ],
    ];
    $style = $styles[$variant] ?? $styles['info'];
@endphp

<div
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border p-4 text-sm '.$style['wrapper']]) }}
    @if ($dismissible) x-data="{ visible: true }" x-show="visible" x-transition.opacity @endif
    role="{{ $variant === 'error' ? 'alert' : 'status' }}"
>
    <span class="flex size-9 shrink-0 items-center justify-center rounded-full {{ $style['icon'] }}">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            {!! $style['path'] !!}
        </svg>
    </span>
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @class(['mt-1' => $title])>{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" @click="visible = false" class="shrink-0 opacity-60 transition hover:opacity-100" aria-label="Fechar mensagem">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    @endif
</div>

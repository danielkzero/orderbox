@props(['title', 'description' => null])

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
        @endif
    </div>
    <div class="flex flex-col gap-3 sm:items-end">
        <div class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-500">Home</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
        </div>
        <div>{{ $actions ?? '' }}</div>
    </div>
</div>

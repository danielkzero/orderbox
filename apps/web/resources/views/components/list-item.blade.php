@props(['title', 'description' => null])

<li {{ $attributes->merge(['class' => 'flex items-start gap-3 px-4 py-3']) }}>
    @isset($leading)
        <span class="shrink-0">{{ $leading }}</span>
    @endisset
    <span class="min-w-0 flex-1">
        <strong class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ $title }}</strong>
        @if ($description)
            <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ $description }}</span>
        @endif
    </span>
    @isset($actions)
        <span class="shrink-0">{{ $actions }}</span>
    @endisset
</li>

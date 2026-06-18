@props(['divided' => true])

<ul {{ $attributes->class([
    'overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900',
    'divide-y divide-gray-100 dark:divide-gray-800' => $divided,
]) }}>
    {{ $slot }}
</ul>

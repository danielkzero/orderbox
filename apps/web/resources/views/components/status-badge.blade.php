@props(['active' => true, 'label' => null])

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $active ? 'bg-success-50 text-success-700 dark:bg-success-950 dark:text-success-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
    {{ $label ?? ($active ? 'Ativo' : 'Inativo') }}
</span>

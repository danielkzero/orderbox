@props(['active' => true, 'label' => null])

<span class="inline-flex rounded-full px-2.5 py-0.5 text-theme-xs font-medium {{ $active ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' }}">
    {{ $label ?? ($active ? 'Ativo' : 'Inativo') }}
</span>

@props(['size' => 'md'])

@php
    $sizes = ['sm' => 'size-4 border-2', 'md' => 'size-5 border-2', 'lg' => 'size-8 border-[3px]'];
@endphp

<span
    {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']).' inline-block animate-spin rounded-full border-current border-r-transparent align-[-0.125em]']) }}
    role="status"
    aria-label="Carregando"
></span>

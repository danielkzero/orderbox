@props(['status'])

@if ($status)
    <x-alert variant="success" title="Operação concluída" {{ $attributes }}>
        {{ $status }}
    </x-alert>
@endif

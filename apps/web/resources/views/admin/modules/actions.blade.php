<div class="flex items-center gap-3">
    <a href="{{ route('crud.edit', [$resource, $item->id]) }}" class="font-medium text-brand-600">Editar</a>
    @if ($item->active ?? false)
        <form method="POST" action="{{ route('crud.deactivate', [$resource, $item->id]) }}">
            @csrf
            <button class="font-medium text-error-600">Inativar</button>
        </form>
    @endif
</div>

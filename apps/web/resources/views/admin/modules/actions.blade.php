<div class="flex items-center gap-3">
    @php
        $canManage = auth()->user()->isAdministrative()
            || in_array($resource, ['customers', 'orders'], true);
    @endphp
    @if ($canManage && ($resource !== 'orders' || $item->status === 'Draft'))
        <a href="{{ route('crud.edit', [$resource, $item->id]) }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Editar</a>
    @endif

    @if ($canManage && $resource === 'orders' && $item->status === 'Draft')
        <form method="POST" action="{{ route('orders.send', $item) }}" onsubmit="return confirm('Enviar este pedido? Após o envio ele não poderá ser editado.')">
            @csrf
            <button class="font-medium text-success-600 hover:text-success-700">Enviar</button>
        </form>
        <form method="POST" action="{{ route('crud.deactivate', [$resource, $item->id]) }}" onsubmit="return confirm('Excluir este rascunho?')">
            @csrf
            <button class="font-medium text-error-600 hover:text-error-700">Excluir</button>
        </form>
    @elseif ($resource === 'orders' && $item->status === 'Sent' && auth()->user()->isAdministrative())
        <form method="POST" action="{{ route('orders.cancel', $item) }}" onsubmit="return confirm('Cancelar este pedido enviado?')">
            @csrf
            <button class="font-medium text-error-600 hover:text-error-700">Cancelar</button>
        </form>
    @elseif ($canManage && $resource !== 'orders' && ($item->active ?? false))
        <form method="POST" action="{{ route('crud.deactivate', [$resource, $item->id]) }}" onsubmit="return confirm('Inativar este registro?')">
            @csrf
            <button class="font-medium text-error-600 hover:text-error-700">Inativar</button>
        </form>
    @endif
</div>

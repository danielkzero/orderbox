<div class="flex items-center gap-3">
    @php
        $canManage = auth()->user()->isAdministrative()
            || in_array($resource, ['customers', 'orders'], true);
    @endphp
    @if ($canManage && ($resource !== 'orders' || $item->status === 'Draft'))
        <a href="{{ route('crud.edit', [$resource, $item->id]) }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Editar</a>
    @endif

    @if ($canManage && $resource === 'orders' && $item->status === 'Draft')
        <form
            method="POST"
            action="{{ route('orders.send', $item) }}"
            data-confirm-title="Enviar pedido?"
            data-confirm-message="Após o envio, o pedido ficará bloqueado para edição."
            data-confirm-label="Enviar pedido"
        >
            @csrf
            <button class="font-medium text-success-600 hover:text-success-700">Enviar</button>
        </form>
        <form
            method="POST"
            action="{{ route('crud.deactivate', [$resource, $item->id]) }}"
            data-confirm-title="Excluir rascunho?"
            data-confirm-message="O pedido e seus itens serão removidos definitivamente."
            data-confirm-label="Continuar"
            data-confirm-level="double"
            data-confirm-variant="danger"
            data-confirm-final-title="Excluir este rascunho definitivamente?"
            data-confirm-final-message="Não será possível recuperar o pedido após a exclusão."
            data-confirm-final-label="Sim, excluir"
        >
            @csrf
            <button class="font-medium text-error-600 hover:text-error-700">Excluir</button>
        </form>
    @elseif ($resource === 'orders' && $item->status === 'Sent' && auth()->user()->isAdministrative())
        <form
            method="POST"
            action="{{ route('orders.cancel', $item) }}"
            data-confirm-title="Cancelar pedido enviado?"
            data-confirm-message="O cancelamento será registrado na auditoria e o pedido não poderá voltar ao estado enviado."
            data-confirm-label="Continuar"
            data-confirm-level="double"
            data-confirm-variant="danger"
            data-confirm-final-title="Confirmar cancelamento do pedido?"
            data-confirm-final-message="Esta é a confirmação final. O pedido será marcado como cancelado imediatamente."
            data-confirm-final-label="Sim, cancelar"
        >
            @csrf
            <button class="font-medium text-error-600 hover:text-error-700">Cancelar</button>
        </form>
    @elseif ($canManage && $resource !== 'orders' && ($item->active ?? false))
        <form
            method="POST"
            action="{{ route('crud.deactivate', [$resource, $item->id]) }}"
            data-confirm-title="Inativar registro?"
            data-confirm-message="O registro deixará de estar disponível para novas operações, mas seu histórico será preservado."
            data-confirm-label="Inativar"
            data-confirm-variant="danger"
        >
            @csrf
            <button class="font-medium text-error-600 hover:text-error-700">Inativar</button>
        </form>
    @endif
</div>

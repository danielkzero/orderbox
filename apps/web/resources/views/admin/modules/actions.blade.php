<div class="flex items-center gap-3" x-data="{ moreOpen: false }">
    @php
        $canManage = auth()->user()->isAdministrative()
            || in_array($resource, ['customers', 'orders'], true);
    @endphp

    @if ($canManage && ($resource !== 'orders' || $item->status === 'Draft'))
        <a href="{{ route('crud.edit', [$resource, $item->id]) }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Editar</a>
    @endif

    @if ($resource === 'orders')
        <a href="{{ route('orders.show', $item) }}" class="font-medium text-gray-700 hover:text-brand-600 dark:text-gray-300">Visualizar</a>
        <form method="POST" action="{{ route('orders.email', $item) }}">
            @csrf
            <button class="font-medium text-gray-700 hover:text-brand-600 dark:text-gray-300">E-mail</button>
        </form>
        <form method="POST" action="{{ route('orders.whatsapp', $item) }}" target="_blank">
            @csrf
            <button class="font-medium text-success-600 hover:text-success-700">WhatsApp</button>
        </form>
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

    @if ($resource === 'orders')
        <div class="relative">
            <button type="button" @click="moreOpen = ! moreOpen" class="font-medium text-gray-600 hover:text-brand-600 dark:text-gray-400">Outros</button>
            <div x-show="moreOpen" x-cloak @click.outside="moreOpen = false" class="absolute right-0 z-40 mt-2 w-52 rounded-xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                <a href="{{ route('orders.history', $item) }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Histórico de envios</a>
                <form method="POST" action="{{ route('orders.duplicate', $item) }}">
                    @csrf
                    <button class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Duplicar pedido</button>
                </form>
                @if ($item->status === 'Draft')
                    <form
                        method="POST"
                        action="{{ route('orders.cancel', $item) }}"
                        data-confirm-title="Cancelar pedido?"
                        data-confirm-message="O pedido ainda não foi enviado e será marcado como cancelado."
                        data-confirm-label="Continuar"
                        data-confirm-level="double"
                        data-confirm-variant="danger"
                        data-confirm-final-title="Confirmar cancelamento?"
                        data-confirm-final-message="O pedido permanecerá no histórico, sem possibilidade de edição ou envio."
                        data-confirm-final-label="Sim, cancelar"
                    >
                        @csrf
                        <button class="block w-full rounded-lg px-3 py-2 text-left text-sm text-error-600 hover:bg-error-50 dark:hover:bg-error-500/10">Cancelar pedido</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>

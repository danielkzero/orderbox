<div class="flex items-center justify-end gap-2" x-data="{ moreOpen: false }">
    @php
        $canManage = auth()->user()->isAdministrative()
            || in_array($resource, ['customers', 'orders'], true);
    @endphp

    @if ($canManage && ($resource !== 'orders' || $item->status === 'Draft'))
        <x-table-action
            :href="route('crud.edit', [$resource, $item->id])"
            icon="pencil"
            label="Editar"
            variant="primary"
        />
    @endif

    @if ($resource === 'orders')
        <x-table-action :href="route('orders.show', $item)" icon="eye" label="Visualizar pedido" />
        <form method="POST" action="{{ route('orders.email', $item) }}">
            @csrf
            <x-table-action icon="mail" label="Enviar por e-mail" />
        </form>
        <form method="POST" action="{{ route('orders.whatsapp', $item) }}" target="_blank">
            @csrf
            <x-table-action icon="message-circle" label="Enviar por WhatsApp" variant="success" />
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
            <x-table-action icon="send" label="Enviar pedido" variant="success" />
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
            <x-table-action icon="ban" label="Inativar" variant="danger" />
        </form>
    @endif

    @if ($resource === 'orders')
        <div class="relative">
            <x-table-action
                type="button"
                icon="more-vertical"
                label="Outras ações"
                @click="moreOpen = ! moreOpen"
                aria-haspopup="menu"
                x-bind:aria-expanded="moreOpen"
            />
            <div x-show="moreOpen" x-cloak @click.outside="moreOpen = false" class="absolute right-0 z-40 mt-2 w-52 rounded-xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                <a href="{{ route('orders.history', $item) }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <x-icon name="history" class="size-4" />
                    Histórico de envios
                </a>
                <form method="POST" action="{{ route('orders.duplicate', $item) }}">
                    @csrf
                    <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <x-icon name="copy" class="size-4" />
                        Duplicar pedido
                    </button>
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
                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-error-600 hover:bg-error-50 dark:hover:bg-error-500/10">
                            <x-icon name="x-circle" class="size-4" />
                            Cancelar pedido
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>

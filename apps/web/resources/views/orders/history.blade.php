<x-app-layout>
    <x-page-header title="Histórico de envios" :description="'Pedido '.$order->order_number">
        <x-slot name="actions">
            <a href="{{ route('orders.show', $order) }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Visualizar pedido</a>
        </x-slot>
    </x-page-header>

    <x-panel>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-4">Data</th>
                        <th class="px-5 py-4">Canal</th>
                        <th class="px-5 py-4">Destinatário</th>
                        <th class="px-5 py-4">Responsável</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Detalhes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($deliveries as $delivery)
                        <tr>
                            <td class="px-5 py-4">{{ $delivery->sent_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="px-5 py-4">{{ $delivery->channel }}</td>
                            <td class="px-5 py-4">{{ $delivery->recipient ?: 'Compartilhamento genérico' }}</td>
                            <td class="px-5 py-4">{{ $delivery->user->name }}</td>
                            <td class="px-5 py-4">{{ ['Sent' => 'Enviado', 'Opened' => 'Compartilhamento aberto', 'Failed' => 'Falhou'][$delivery->status] ?? $delivery->status }}</td>
                            <td class="px-5 py-4">{{ $delivery->details ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500">Nenhum envio registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>
</x-app-layout>

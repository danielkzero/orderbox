<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $company->trade_name }}</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'Clientes ativos', 'value' => $indicators['customers']],
                    ['label' => 'Produtos ativos', 'value' => $indicators['products']],
                    ['label' => 'Representantes', 'value' => $indicators['representatives']],
                    ['label' => 'Total de pedidos', 'value' => $indicators['orders']],
                ] as $indicator)
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">{{ $indicator['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $indicator['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-indigo-600 p-6 text-white shadow-sm">
                    <p class="text-sm font-medium text-indigo-100">Pedidos hoje</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $indicators['orders_today'] }}</p>
                </div>
                <div class="rounded-lg bg-slate-800 p-6 text-white shadow-sm">
                    <p class="text-sm font-medium text-slate-300">Pedidos neste mês</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $indicators['orders_month'] }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="font-semibold text-gray-900">Pedidos recentes</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Pedido</th>
                                <th class="px-6 py-3">Cliente</th>
                                <th class="px-6 py-3">Representante</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">{{ $order->order_number }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ $order->customer->trade_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ $order->salesRepresentative->user->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $order->status }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-medium text-gray-900">
                                        R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">Nenhum pedido cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

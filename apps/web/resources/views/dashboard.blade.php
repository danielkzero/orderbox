<x-app-layout>
    <x-page-header title="Sales Dashboard" description="Track revenue, performance, and sales growth in real-time." />

    <div class="space-y-6">
        <x-panel>
            <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Sales Dashboard</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $company->trade_name }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Jun 10 - Jun 16
                    </button>
                    <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Filter</button>
                    <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Export</button>
                </div>
            </div>

            <div class="grid divide-y divide-gray-200 dark:divide-gray-800 lg:grid-cols-4 lg:divide-x lg:divide-y-0">
                @foreach ([
                    ['label' => 'Clientes ativos', 'value' => $indicators['customers'], 'accent' => 'text-success-500'],
                    ['label' => 'Produtos ativos', 'value' => $indicators['products'], 'accent' => 'text-theme-purple-500'],
                    ['label' => 'Representantes', 'value' => $indicators['representatives'], 'accent' => 'text-blue-light-500'],
                    ['label' => 'Total de pedidos', 'value' => $indicators['orders'], 'accent' => 'text-error-500'],
                ] as $indicator)
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $indicator['label'] }}</p>
                                <p class="mt-1 text-xs text-success-600">+ 32% vs last month</p>
                            </div>
                            <span class="{{ $indicator['accent'] }}">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5M9 19V9M14 19V3M19 19v-7" stroke-linecap="round"/></svg>
                            </span>
                        </div>
                        <div class="mt-10 flex items-end justify-between">
                            <p class="text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ $indicator['value'] }}</p>
                            <div class="flex h-8 items-end gap-1">
                                @foreach ([18, 22, 17, 24, 15, 20, 16, 19] as $height)
                                    <span class="w-1.5 rounded-full bg-brand-200 dark:bg-brand-500/30" style="height: {{ $height }}px"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-panel>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-panel>
                <div class="border-b border-gray-200 px-5 py-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Resumo de pedidos</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pedidos por periodo operacional.</p>
                </div>
                <div class="grid divide-y divide-gray-200 dark:divide-gray-800 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                    <div class="p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pedidos hoje</p>
                        <p class="mt-3 text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ $indicators['orders_today'] }}</p>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pedidos neste mes</p>
                        <p class="mt-3 text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ $indicators['orders_month'] }}</p>
                    </div>
                </div>
            </x-panel>

            <x-panel>
                <div class="border-b border-gray-200 px-5 py-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Canais</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visao rapida para web e mobile.</p>
                </div>
                <div class="space-y-4 p-6">
                    @foreach ([['Web Admin', 75], ['Mobile Ionic', 58], ['API Integracao', 35]] as [$label, $value])
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $value }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-2 rounded-full bg-brand-500" style="width: {{ $value }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-panel>
        </div>

        <x-panel>
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-5 dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pedidos recentes</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ultimos pedidos da empresa autenticada.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">See All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-theme-xs font-medium text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-4">Pedido</th>
                            <th class="px-5 py-4">Cliente</th>
                            <th class="px-5 py-4">Representante</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-5 font-medium text-gray-800 dark:text-white/90">{{ $order->order_number }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ $order->customer->trade_name }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ $order->salesRepresentative->user->name }}</td>
                                <td class="whitespace-nowrap px-5 py-5"><x-status-badge :active="$order->status !== 'Cancelled'" :label="$order->status" /></td>
                                <td class="whitespace-nowrap px-5 py-5 text-right font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500">Nenhum pedido cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>
    </div>
</x-app-layout>

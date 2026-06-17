@php
    $query = request()->query();
    $periodLabel = $period['start']->format('d/m/Y').' - '.$period['end']->format('d/m/Y');
    $exportQuery = array_merge($query, [
        'start_date' => $period['start']->format('Y-m-d'),
        'end_date' => $period['end']->format('Y-m-d'),
        'preset' => 'custom',
    ]);
@endphp

<x-app-layout>
    <x-page-header title="Dashboard de vendas" description="Acompanhe receita, desempenho e crescimento comercial em tempo real." />

    <div class="space-y-6">
        <x-panel class="overflow-visible" x-data="{
            periodOpen: false,
            selectedStart: '{{ $period['start']->format('Y-m-d') }}',
            selectedEnd: '{{ $period['end']->format('Y-m-d') }}',
            currentMonth: new Date('{{ $period['start']->format('Y-m') }}-01T00:00:00'),
            monthNames: ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'],
            label() {
                return `${this.formatDate(this.selectedStart)} - ${this.formatDate(this.selectedEnd || this.selectedStart)}`;
            },
            monthLabel() {
                return `${this.monthNames[this.currentMonth.getMonth()]} ${this.currentMonth.getFullYear()}`;
            },
            previousMonth() {
                this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() - 1, 1);
            },
            nextMonth() {
                this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1, 1);
            },
            dateKey(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            },
            formatDate(value) {
                if (! value) {
                    return '';
                }

                const [year, month, day] = value.split('-');

                return `${day}/${month}/${year}`;
            },
            days() {
                const first = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1);
                const start = new Date(first);
                start.setDate(first.getDate() - first.getDay());
                const items = [];

                for (let index = 0; index < 42; index++) {
                    const date = new Date(start);
                    date.setDate(start.getDate() + index);
                    const key = this.dateKey(date);
                    const startKey = this.selectedStart;
                    const endKey = this.selectedEnd || this.selectedStart;
                    const min = startKey && endKey && startKey <= endKey ? startKey : endKey;
                    const max = startKey && endKey && startKey <= endKey ? endKey : startKey;

                    items.push({
                        key,
                        label: date.getDate(),
                        current: date.getMonth() === this.currentMonth.getMonth(),
                        isStart: key === startKey,
                        isEnd: key === endKey,
                        inRange: min && max && key >= min && key <= max,
                    });
                }

                return items;
            },
            selectDate(day) {
                if (! this.selectedStart || this.selectedEnd) {
                    this.selectedStart = day.key;
                    this.selectedEnd = '';

                    return;
                }

                if (day.key < this.selectedStart) {
                    this.selectedEnd = this.selectedStart;
                    this.selectedStart = day.key;

                    return;
                }

                this.selectedEnd = day.key;
            },
        }">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Dashboard de vendas</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $company->trade_name }} · {{ $periodLabel }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                        @foreach ($periodShortcuts as $shortcut => $label)
                            <a href="{{ route('dashboard', array_merge($query, ['preset' => $shortcut, 'start_date' => null, 'end_date' => null])) }}" class="rounded-md px-4 py-2 text-sm font-medium {{ $preset === $shortcut ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-900 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3">
                        <input type="hidden" name="preset" value="custom">
                        <input type="hidden" name="start_date" x-model="selectedStart">
                        <input type="hidden" name="end_date" x-model="selectedEnd">
                        <input type="hidden" name="group_by" value="{{ $groupBy }}">

                        <div class="relative">
                            <button type="button" x-on:click="periodOpen = ! periodOpen" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]" :class="periodOpen ? 'border-brand-500 ring-3 ring-brand-500/10' : ''">
                            <svg class="size-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span x-text="label()">{{ $periodLabel }}</span>
                        </button>

                            <div x-show="periodOpen" x-cloak x-on:click.outside="periodOpen = false" class="absolute right-0 z-50 mt-3 w-[348px] rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                <div class="mb-4 flex items-center justify-between">
                                    <button type="button" x-on:click="previousMonth()" class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </button>
                                    <p class="text-base font-semibold capitalize text-gray-800 dark:text-white/90" x-text="monthLabel()"></p>
                                    <button type="button" x-on:click="nextMonth()" class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-7 gap-y-1 text-center">
                                    @foreach (['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $weekday)
                                        <div class="py-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $weekday }}</div>
                                    @endforeach

                                    <template x-for="day in days()" :key="day.key">
                                        <button type="button" x-on:click="selectDate(day)" class="relative h-10 text-sm font-medium transition" :class="{
                                            'text-gray-400 dark:text-gray-600': ! day.current,
                                            'text-gray-800 dark:text-gray-200': day.current,
                                            'bg-brand-50 dark:bg-brand-500/10': day.inRange && ! day.isStart && ! day.isEnd,
                                            'rounded-l-full': day.isStart && selectedEnd,
                                            'rounded-r-full': day.isEnd && selectedStart !== selectedEnd,
                                        }">
                                            <span class="mx-auto flex size-10 items-center justify-center" :class="(day.isStart || day.isEnd) ? 'rounded-full bg-brand-500 text-white' : ''" x-text="day.label"></span>
                                        </button>
                                    </template>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label for="start_date" value="Data inicial" />
                                        <div class="mt-1 h-11 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" x-text="formatDate(selectedStart)"></div>
                                    </div>
                                    <div>
                                        <x-input-label for="end_date" value="Data final" />
                                        <div class="mt-1 h-11 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" x-text="formatDate(selectedEnd || selectedStart)"></div>
                                    </div>
                                </div>

                                <button type="button" x-on:click="periodOpen = false" class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-lg bg-brand-500 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                    Confirmar período
                                </button>
                            </div>
                        </div>

                        <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]" x-bind:disabled="! selectedStart">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" />
                            </svg>
                            Filtrar
                        </button>
                    </form>

                    <a href="{{ route('dashboard.export', $exportQuery) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 4v10M8 10l4 4 4-4M5 20h14" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Exportar
                    </a>
                </div>
            </div>

            <div class="grid divide-y divide-gray-200 dark:divide-gray-800 lg:grid-cols-4 lg:divide-x lg:divide-y-0">
                @foreach ([
                    ['label' => 'Receita total', 'value' => 'R$ '.number_format((float) $indicators['revenue'], 2, ',', '.'), 'change' => $indicators['revenue_change'], 'accent' => 'text-success-500'],
                    ['label' => 'Total de pedidos', 'value' => number_format((int) $indicators['orders'], 0, ',', '.'), 'change' => $indicators['orders_change'], 'accent' => 'text-theme-purple-500'],
                    ['label' => 'Ticket médio', 'value' => 'R$ '.number_format((float) $indicators['average_ticket'], 2, ',', '.'), 'change' => null, 'accent' => 'text-blue-light-500'],
                    ['label' => 'Taxa de cancelamento', 'value' => number_format((float) $indicators['cancel_rate'], 2, ',', '.').'%', 'change' => null, 'accent' => 'text-error-500'],
                ] as $indicator)
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $indicator['label'] }}</p>
                                @if ($indicator['change'] !== null)
                                    <p class="mt-1 text-xs {{ $indicator['change'] >= 0 ? 'text-success-600' : 'text-error-500' }}">
                                        {{ $indicator['change'] >= 0 ? '+' : '' }}{{ number_format((float) $indicator['change'], 1, ',', '.') }}% vs período anterior
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Calculado pelo período filtrado</p>
                                @endif
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

        <x-panel>
            <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Estatísticas de receita e pedidos</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize o desempenho dentro do período selecionado.</p>
                </div>
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                    @foreach (['daily' => 'Diário', 'weekly' => 'Semanal', 'monthly' => 'Mensal'] as $key => $label)
                        <a href="{{ route('dashboard', array_merge($query, ['group_by' => $key, 'preset' => 'custom', 'start_date' => $period['start']->format('Y-m-d'), 'end_date' => $period['end']->format('Y-m-d')])) }}" class="rounded-md px-4 py-2 text-sm font-medium {{ $groupBy === $key ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-900 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="p-6">
                @if (count($revenueStats) > 0)
                    <div class="flex min-h-[190px] items-end gap-3 overflow-x-auto border-b border-gray-100 pb-4 dark:border-gray-800">
                        @foreach ($revenueStats as $stat)
                            <div class="flex min-w-16 flex-col items-center gap-2">
                                <div class="flex h-36 items-end">
                                    <div class="w-8 rounded-t-lg bg-brand-500/80" style="height: {{ $stat['height'] }}px"></div>
                                </div>
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $stat['total'], 2, ',', '.') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Nenhum pedido encontrado para o período selecionado.
                    </div>
                @endif
            </div>
        </x-panel>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-panel>
                <div class="border-b border-gray-200 px-5 py-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Base comercial</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastros ativos disponíveis para operação.</p>
                </div>
                <div class="grid divide-y divide-gray-200 dark:divide-gray-800 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    @foreach ([['Clientes', $indicators['customers']], ['Produtos', $indicators['products']], ['Representantes', $indicators['representatives']]] as [$label, $value])
                        <div class="p-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                            <p class="mt-3 text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format((int) $value, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </x-panel>

            <x-panel>
                <div class="border-b border-gray-200 px-5 py-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Canais</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visão rápida para web, APP e API.</p>
                </div>
                <div class="space-y-4 p-6">
                    @foreach ($channelStats as $channel)
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $channel['label'] }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $channel['count'] }} pedidos · {{ $channel['percentage'] }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-2 rounded-full bg-brand-500" style="width: {{ $channel['percentage'] }}%"></div>
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
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Últimos pedidos dentro do período selecionado.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Ver todos</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-theme-xs font-medium text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-4">Pedido</th>
                            <th class="px-5 py-4">Cliente</th>
                            <th class="px-5 py-4">Representante</th>
                            <th class="px-5 py-4">Origem</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-5 font-medium text-gray-800 dark:text-white/90">{{ $order->order_number }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ $order->customer->trade_name ?: $order->customer->corporate_name }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ $order->salesRepresentative->user->name }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ in_array($order->source, ['Mobile', 'App'], true) ? 'APP' : 'Web' }}</td>
                                <td class="whitespace-nowrap px-5 py-5"><x-status-badge :active="$order->status !== 'Cancelled'" :label="$order->status" /></td>
                                <td class="whitespace-nowrap px-5 py-5 text-right font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500">Nenhum pedido encontrado para o período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>
    </div>
</x-app-layout>

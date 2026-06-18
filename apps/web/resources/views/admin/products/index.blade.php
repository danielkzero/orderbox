<x-app-layout>
    <x-page-header title="Produtos" description="Acompanhe o catálogo comercial e mantenha os produtos prontos para pedidos.">
        <x-slot name="actions">
        </x-slot>
    </x-page-header>

    <div x-data="{ createPriceTableOpen: false }">
        <x-panel>
        <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Lista de produtos</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Controle disponibilidade, preço e informações comerciais do catálogo.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if (auth()->user()->isAdministrative())
                    <a href="{{ route('crud.create', 'products') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Adicionar produto
                    </a>
                @endif
            </div>
        </div>

        <form method="GET" class="grid gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800 lg:grid-cols-[1fr_220px_220px_190px_auto]">
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3-3" stroke-linecap="round" />
                    </svg>
                </span>
                <input name="search" value="{{ $filters['search'] }}" placeholder="Pesquisar produto, SKU ou código de barras..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>

            <select name="category_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Categoria</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) $filters['category_id'] === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="brand_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Marca</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((int) $filters['brand_id'] === $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>

            <select name="stock_status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Disponibilidade</option>
                <option value="InStock" @selected($filters['stock_status'] === 'InStock')>Em estoque</option>
                <option value="LowStock" @selected($filters['stock_status'] === 'LowStock')>Estoque baixo</option>
                <option value="OutOfStock" @selected($filters['stock_status'] === 'OutOfStock')>Sem estoque</option>
            </select>

            <button class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Filtrar
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-theme-xs font-medium text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-4">Produto</th>
                        <th class="px-5 py-4">Categoria</th>
                        <th class="px-5 py-4">Marca</th>
                        <th class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span>Preço</span>
                                @if (auth()->user()->isAdministrative())
                                    <button
                                        type="button"
                                        @click="createPriceTableOpen = true"
                                        class="inline-flex size-7 items-center justify-center rounded-lg border border-brand-200 bg-brand-50 text-base font-semibold text-brand-600 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400"
                                        aria-label="Adicionar tabela de preço"
                                        title="Adicionar tabela de preço"
                                    >
                                        +
                                    </button>
                                @endif
                            </div>
                        </th>
                        @foreach ($priceTables as $priceTable)
                            <th class="min-w-[180px] px-5 py-4" x-data="{ editing: false }">
                                @if (auth()->user()->isAdministrative())
                                    <button
                                        type="button"
                                        x-show="! editing"
                                        @click="editing = true; $nextTick(() => $refs.name.focus())"
                                        class="group flex items-center gap-2 text-left font-semibold text-gray-700 hover:text-brand-600 dark:text-gray-200 dark:hover:text-brand-400"
                                        title="Editar nome da tabela"
                                    >
                                        <span>{{ $priceTable->name }}</span>
                                        <svg class="size-4 opacity-0 transition group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <form
                                        x-show="editing"
                                        x-cloak
                                        method="POST"
                                        action="{{ route('products.price-tables.update', $priceTable) }}"
                                        class="flex items-center gap-2"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            x-ref="name"
                                            name="name"
                                            value="{{ $priceTable->name }}"
                                            maxlength="255"
                                            required
                                            class="h-9 min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            @keydown.escape.prevent="editing = false"
                                        >
                                        <button type="submit" class="text-xs font-semibold text-success-600 hover:text-success-700">Salvar</button>
                                        <button type="button" @click="editing = false" class="text-xs font-medium text-gray-500 hover:text-gray-700">Cancelar</button>
                                    </form>
                                @else
                                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ $priceTable->name }}</p>
                                @endif
                            </th>
                        @endforeach
                        <th class="px-5 py-4">Estoque</th>
                        <th class="px-5 py-4">Criado em</th>
                        <th class="px-5 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="min-w-[280px] px-5 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                        @if ($product->imageSrc())
                                            <img src="{{ $product->imageSrc() }}" alt="{{ $product->name }}" class="size-full object-cover">
                                        @else
                                            <span class="text-sm font-semibold text-brand-500">{{ Str::substr($product->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $product->sku }}{{ $product->barcode ? ' · '.$product->barcode : '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ $product->category->name }}</td>
                            <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ $product->brand?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-5 font-medium text-gray-800 dark:text-white/90">R$ {{ number_format($product->displayPrice(), 2, ',', '.') }}</td>
                            @foreach ($priceTables as $priceTable)
                                @php $tablePrice = $product->prices->firstWhere('price_table_id', $priceTable->id); @endphp
                                <td class="whitespace-nowrap px-5 py-5 text-gray-700 dark:text-gray-300">
                                    @if ($tablePrice)
                                        <div class="space-y-1">
                                            <p class="font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $tablePrice->price, 2, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Mín. {{ number_format((float) $tablePrice->minimum_quantity, 3, ',', '.') }}</p>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="whitespace-nowrap px-5 py-5">
                                <div class="space-y-1">
                                    <x-status-badge :active="$product->stockStatusIsAvailable()" :label="$product->stockStatusLabel()" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format((float) $product->available_stock, 3, ',', '.') }} {{ $product->unit->code }}</p>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-5 text-gray-600 dark:text-gray-300">{{ $product->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-5">
                                <div class="flex justify-end">
                                    @include('admin.modules.actions', ['resource' => 'products', 'item' => $product])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 7 + $priceTables->count() }}" class="px-5 py-12 text-center text-gray-500">Nenhum produto encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            <div class="border-t border-gray-200 p-5 dark:border-gray-800">{{ $products->links() }}</div>
        </x-panel>

        @if (auth()->user()->isAdministrative())
            <div
                x-show="createPriceTableOpen"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-950/60 p-4"
                @keydown.escape.window="createPriceTableOpen = false"
            >
                <div @click.outside="createPriceTableOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Nova tabela de preço</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">A tabela será adicionada como uma nova coluna após Preço.</p>
                    </div>
                    <form method="POST" action="{{ route('products.price-tables.store') }}" class="space-y-5">
                        @csrf
                        <div>
                            <x-input-label for="price_table_name" value="Nome da tabela" />
                            <input
                                id="price_table_name"
                                name="name"
                                maxlength="255"
                                required
                                class="mt-1 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                placeholder="Ex.: Atacado"
                            >
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="createPriceTableOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                                Criar tabela
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

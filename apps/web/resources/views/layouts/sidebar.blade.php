@php
    $groups = [
        'Operação' => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
            ['route' => 'customers.index', 'label' => 'Clientes', 'icon' => 'users'],
            ['route' => 'products.index', 'label' => 'Produtos', 'icon' => 'box'],
            ['route' => 'price-tables.index', 'label' => 'Tabelas de preço', 'icon' => 'tag'],
            ['route' => 'representatives.index', 'label' => 'Representantes', 'icon' => 'briefcase'],
            ['route' => 'orders.index', 'label' => 'Pedidos', 'icon' => 'cart'],
        ],
        'Cadastros' => [
            ['route' => 'categories.index', 'label' => 'Categorias', 'icon' => 'folder'],
            ['route' => 'brands.index', 'label' => 'Marcas', 'icon' => 'badge'],
            ['route' => 'units.index', 'label' => 'Unidades', 'icon' => 'ruler'],
        ],
        'Administração' => [
            ['route' => 'users.index', 'label' => 'Usuários', 'icon' => 'shield', 'roles' => ['Admin']],
            ['route' => 'api-clients.index', 'label' => 'Liberação API', 'icon' => 'key', 'roles' => ['Admin']],
            ['route' => 'security.index', 'label' => 'Segurança e 2FA', 'icon' => 'lock'],
            ['route' => 'audit-logs.index', 'label' => 'Auditoria', 'icon' => 'history', 'roles' => ['Admin', 'Manager']],
            ['route' => 'profile.edit', 'label' => 'Meu perfil', 'icon' => 'user'],
        ],
        'Ajuda' => [
            ['route' => 'manual.index', 'label' => 'Manual de uso', 'icon' => 'book'],
            ['route' => 'api-guide.index', 'label' => 'Guia da API', 'icon' => 'code'],
        ],
    ];
@endphp

<aside class="fixed inset-y-0 left-0 z-50 flex w-[290px] -translate-x-full flex-col border-r border-gray-200 bg-white px-5 transition-transform duration-300 dark:border-gray-800 dark:bg-gray-900 xl:translate-x-0"
       :class="{ 'translate-x-0': sidebarOpen }">
    <div class="flex h-20 items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-500 text-lg font-bold text-white">O</span>
            <span>
                <strong class="block text-lg text-gray-900 dark:text-white">OrderBox</strong>
                <small class="text-gray-500 dark:text-gray-400">{{ auth()->user()->company->trade_name }}</small>
            </span>
        </a>
        <button @click="sidebarOpen = false" class="text-gray-500 xl:hidden">✕</button>
    </div>

    <nav class="no-scrollbar flex-1 space-y-7 overflow-y-auto pb-6">
        @foreach ($groups as $group => $items)
            <div>
                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $group }}</p>
                <ul class="space-y-1">
                    @foreach ($items as $item)
                        @continue(isset($item['roles']) && !in_array(auth()->user()->role, $item['roles'], true))
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="menu-item {{ request()->routeIs($item['route']) || request()->routeIs(Str::before($item['route'], '.').'.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <span class="flex size-6 items-center justify-center"><x-icon :name="$item['icon']" /></span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>

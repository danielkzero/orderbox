@php
    $groups = [
        'MENU' => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
            ['route' => 'customers.index', 'label' => 'Clientes', 'icon' => 'users'],
            ['route' => 'products.index', 'label' => 'Produtos', 'icon' => 'box'],
            ['route' => 'price-tables.index', 'label' => 'Tabelas de preço', 'icon' => 'tag'],
            ['route' => 'representatives.index', 'label' => 'Representantes', 'icon' => 'briefcase'],
            ['route' => 'orders.index', 'label' => 'Pedidos', 'icon' => 'cart'],
        ],
        'CADASTROS' => [
            ['route' => 'categories.index', 'label' => 'Categorias', 'icon' => 'folder'],
            ['route' => 'brands.index', 'label' => 'Marcas', 'icon' => 'badge'],
            ['route' => 'units.index', 'label' => 'Unidades', 'icon' => 'ruler'],
            ['route' => 'regions.index', 'label' => 'Regiões', 'icon' => 'map'],
        ],
        'ADMINISTRACAO' => [
            ['route' => 'users.index', 'label' => 'Usuários', 'icon' => 'shield', 'roles' => ['Admin']],
            ['route' => 'api-clients.index', 'label' => 'Liberação API', 'icon' => 'key', 'roles' => ['Admin']],
            ['route' => 'security.index', 'label' => 'Segurança e 2FA', 'icon' => 'lock'],
            ['route' => 'audit-logs.index', 'label' => 'Auditoria', 'icon' => 'history', 'roles' => ['Admin', 'Manager']],
            ['route' => 'profile.edit', 'label' => 'Meu perfil', 'icon' => 'user'],
        ],
        'SUPPORT' => [
            ['route' => 'manual.index', 'label' => 'Manual de uso', 'icon' => 'book'],
            ['route' => 'api-guide.index', 'label' => 'Guia da API', 'icon' => 'code'],
        ],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex -translate-x-full flex-col border-r border-gray-200 bg-white transition-all duration-300 dark:border-gray-800 dark:bg-gray-900 xl:translate-x-0"
    :class="{
        'translate-x-0': sidebarOpen,
        'w-[290px] px-5 shadow-theme-lg xl:shadow-none': ! sidebarCollapsed || sidebarExpandedOnHover,
        'w-[92px] px-4': sidebarCollapsed && ! sidebarExpandedOnHover,
    }"
    @mouseenter="if (sidebarCollapsed) sidebarExpandedOnHover = true"
    @mouseleave="sidebarExpandedOnHover = false"
>
    <div class="flex h-[76px] items-center" :class="sidebarCollapsed && ! sidebarExpandedOnHover ? 'justify-center' : 'justify-between'">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-lg bg-brand-500 text-white">
                <svg class="size-5" viewBox="0 0 24 24" fill="none">
                    <rect x="4" y="5" width="4" height="14" rx="2" fill="currentColor" />
                    <rect x="10" y="9" width="4" height="10" rx="2" fill="currentColor" opacity=".75" />
                    <rect x="16" y="3" width="4" height="16" rx="2" fill="currentColor" opacity=".55" />
                </svg>
            </span>
            <strong x-show="! sidebarCollapsed || sidebarExpandedOnHover" x-cloak class="text-title-sm font-semibold text-gray-900 dark:text-white">OrderBox</strong>
        </a>
        <button x-show="! sidebarCollapsed || sidebarExpandedOnHover" x-cloak @click="sidebarOpen = false" class="text-gray-500 xl:hidden">x</button>
    </div>

    <nav class="no-scrollbar flex-1 space-y-7 overflow-y-auto pb-6">
        @foreach ($groups as $group => $items)
            <div>
                <p x-show="! sidebarCollapsed || sidebarExpandedOnHover" x-cloak class="mb-3 px-3 text-theme-xs font-medium uppercase tracking-wide text-gray-400">{{ $group }}</p>
                <p x-show="sidebarCollapsed && ! sidebarExpandedOnHover" x-cloak class="mb-3 text-center text-theme-xs font-semibold tracking-widest text-gray-400">•••</p>
                <ul class="space-y-1">
                    @foreach ($items as $item)
                        @continue(isset($item['roles']) && ! in_array(auth()->user()->role, $item['roles'], true))
                        @php
                            $active = request()->routeIs($item['route']) || request()->routeIs(Str::before($item['route'], '.').'.*');
                        @endphp
                        <li>
                            <a href="{{ route($item['route']) }}" class="group menu-item {{ $active ? 'menu-item-active' : 'menu-item-inactive' }}" :class="sidebarCollapsed && ! sidebarExpandedOnHover ? 'justify-center px-0' : 'justify-start px-3'">
                                <span class="{{ $active ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"><x-icon :name="$item['icon']" /></span>
                                <span x-show="! sidebarCollapsed || sidebarExpandedOnHover" x-cloak class="menu-item-text">{{ $item['label'] }}</span>
                                @if (in_array($item['route'], ['products.index', 'orders.index', 'api-clients.index'], true))
                                    <span x-show="! sidebarCollapsed || sidebarExpandedOnHover" x-cloak class="menu-dropdown-badge {{ $active ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive' }}">NEW</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>

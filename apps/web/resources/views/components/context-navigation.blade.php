@php
    $user = auth()->user();
    $crudResource = request()->routeIs('crud.*') ? request()->route('resource') : null;
    $context = match (true) {
        request()->routeIs('customers.*', 'representatives.*', 'regions.*')
            || in_array($crudResource, ['customers', 'representatives', 'regions'], true) => 'commercial',
        request()->routeIs('products.*', 'categories.*', 'brands.*', 'units.*')
            || in_array($crudResource, ['products', 'categories', 'brands', 'units'], true) => 'catalog',
        request()->routeIs('profile.*', 'security.*', 'users.*', 'api-clients.*', 'audit-logs.*') => 'settings',
        request()->routeIs('manual.*', 'api-guide.*') => 'help',
        default => null,
    };

    $items = match ($context) {
        'commercial' => [
            ['route' => 'customers.index', 'label' => 'Clientes', 'icon' => 'users'],
            ['route' => 'representatives.index', 'label' => 'Representantes', 'icon' => 'briefcase', 'roles' => ['Admin', 'Manager']],
            ['route' => 'regions.index', 'label' => 'Regiões', 'icon' => 'map', 'roles' => ['Admin', 'Manager']],
        ],
        'catalog' => [
            ['route' => 'products.index', 'label' => 'Produtos', 'icon' => 'box'],
            ['route' => 'categories.index', 'label' => 'Categorias', 'icon' => 'folder', 'roles' => ['Admin', 'Manager']],
            ['route' => 'brands.index', 'label' => 'Marcas', 'icon' => 'badge', 'roles' => ['Admin', 'Manager']],
            ['route' => 'units.index', 'label' => 'Unidades', 'icon' => 'ruler', 'roles' => ['Admin', 'Manager']],
        ],
        'settings' => [
            ['route' => 'profile.edit', 'label' => 'Meu perfil', 'icon' => 'user'],
            ['route' => 'security.index', 'label' => 'Segurança', 'icon' => 'lock'],
            ['route' => 'users.index', 'label' => 'Usuários', 'icon' => 'users', 'roles' => ['Admin']],
            ['route' => 'api-clients.index', 'label' => 'Integrações', 'icon' => 'key', 'roles' => ['Admin']],
            ['route' => 'audit-logs.index', 'label' => 'Auditoria', 'icon' => 'history', 'roles' => ['Admin', 'Manager']],
        ],
        'help' => [
            ['route' => 'manual.index', 'label' => 'Manual de uso', 'icon' => 'book'],
            ['route' => 'api-guide.index', 'label' => 'Guia da API', 'icon' => 'code'],
        ],
        default => [],
    };
@endphp

@if ($context && $items !== [])
    <nav class="mb-6 overflow-x-auto border-b border-gray-200 dark:border-gray-800" aria-label="Navegação do módulo">
        <div class="flex min-w-max gap-1">
            @foreach ($items as $item)
                @continue(isset($item['roles']) && ! in_array($user->role, $item['roles'], true))
                @php
                    $routeGroup = Str::before($item['route'], '.');
                    $active = request()->routeIs($item['route'])
                        || request()->routeIs($routeGroup.'.*')
                        || $crudResource === $routeGroup;
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'group relative inline-flex items-center gap-2.5 px-4 pb-3 pt-1 text-sm font-medium transition',
                        'text-gray-900 dark:text-white' => $active,
                        'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' => ! $active,
                    ])
                    @if ($active) aria-current="page" @endif
                >
                    <x-icon :name="$item['icon']" @class([
                        'size-[18px]',
                        'text-brand-500' => $active,
                        'text-gray-400 transition group-hover:text-gray-600 dark:group-hover:text-gray-300' => ! $active,
                    ]) />
                    <span>{{ $item['label'] }}</span>
                    @if ($active)
                        <span class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-brand-500"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>
@endif

<x-app-layout :title="$title">
    <x-page-header :title="$title" :description="$description" />

    <x-panel>
        <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Encontre, acompanhe e atualize as informações desta área.</p>
            </div>

            @if ($resource && (auth()->user()->isAdministrative() || in_array($resource, ['customers', 'orders'], true)))
                <a href="{{ route('crud.create', $resource) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    <span class="text-lg leading-none">+</span>
                    Novo registro
                </a>
            @endif
        </div>

        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <form method="GET" class="grid gap-3 md:grid-cols-[minmax(240px,1fr)_180px_180px_150px_auto]">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3-3" stroke-linecap="round" />
                    </svg>
                    </span>
                    <input name="search" value="{{ $search }}" placeholder="Pesquisar..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                </div>
                <select name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">Todos os status</option>
                    @if ($filters['is_orders'])
                        @foreach (['Draft' => 'Rascunho', 'Sent' => 'Enviado', 'Cancelled' => 'Cancelado'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    @elseif ($filters['has_active'])
                        <option value="active" @selected($filters['status'] === 'active')>Ativos</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inativos</option>
                    @endif
                </select>
                <select name="sort" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">Ordenação padrão</option>
                    <option value="name" @selected($filters['sort'] === 'name')>Nome</option>
                    <option value="created_at" @selected($filters['sort'] === 'created_at')>Criação</option>
                    <option value="updated_at" @selected($filters['sort'] === 'updated_at')>Atualização</option>
                    @if ($filters['is_orders'])
                        <option value="order_date" @selected($filters['sort'] === 'order_date')>Data do pedido</option>
                    @endif
                </select>
                <select name="direction" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="desc" @selected($filters['direction'] === 'desc')>Decrescente</option>
                    <option value="asc" @selected($filters['direction'] === 'asc')>Crescente</option>
                </select>
                <div class="flex gap-2">
                    <button class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Aplicar</button>
                    @if ($search !== '' || $filters['status'] !== '' || $filters['sort'] !== '')
                        <a href="{{ url()->current() }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium dark:border-gray-700">Limpar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-theme-xs font-medium text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                    <tr>
                        @foreach (array_keys($columns) as $column)
                            <th class="whitespace-nowrap px-5 py-4">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            @foreach ($columns as $resolver)
                                <td class="whitespace-nowrap px-5 py-5 text-gray-700 dark:text-gray-300">
                                    @php $value = $resolver instanceof \Closure ? $resolver($item) : data_get($item, $resolver); @endphp
                                    {!! $value instanceof \Illuminate\Contracts\View\View ? $value->render() : e($value ?? '-') !!}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) }}" class="px-5 py-12 text-center text-gray-500">Nenhum registro encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 p-5 dark:border-gray-800">{{ $items->links() }}</div>
    </x-panel>
</x-app-layout>

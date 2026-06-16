<x-app-layout>
    <x-page-header :title="$title" :description="$description" />

    <x-panel>
        <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie os registros da empresa autenticada.</p>
            </div>

            @if ($resource)
                <a href="{{ route('crud.create', $resource) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    <span class="text-lg leading-none">+</span>
                    Novo registro
                </a>
            @endif
        </div>

        <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="relative w-full sm:max-w-[300px]">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3-3" stroke-linecap="round" />
                    </svg>
                </span>
                <input name="search" value="{{ $search }}" placeholder="Pesquisar..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </form>

            <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h10M18 7h2M6 17h14M4 17h2M4 12h2M10 12h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="12" r="2"/><circle cx="8" cy="17" r="2"/></svg>
                Filtrar
            </button>
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

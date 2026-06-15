<x-app-layout>
    <x-page-header :title="$title" :description="$description" />

    <x-panel>
        <div class="border-b border-gray-200 p-5 dark:border-gray-800">
            <form method="GET" class="flex gap-3">
                <input name="search" value="{{ $search }}" placeholder="Buscar..." class="h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900">
                <button class="rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">Buscar</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                    <tr>@foreach (array_keys($columns) as $column)<th class="whitespace-nowrap px-5 py-3">{{ $column }}</th>@endforeach</tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            @foreach ($columns as $resolver)
                                <td class="whitespace-nowrap px-5 py-4 text-gray-700 dark:text-gray-300">
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

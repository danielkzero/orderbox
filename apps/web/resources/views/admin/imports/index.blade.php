<x-app-layout title="Importação de dados">
    <x-page-header title="Importação de dados" description="Carregue os cadastros essenciais da empresa a partir dos modelos oficiais do OrderBox." />

    <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <div class="space-y-6">
            <x-panel>
                <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Nova importação</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">A operação é transacional: qualquer erro cancela todo o arquivo.</p>
                </div>
                <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="space-y-5 p-5">
                    @csrf
                    <div>
                        <x-input-label for="type" value="Tipo de importação" />
                        <select id="type" name="type" class="mt-1 block h-11 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" required>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="file" value="Planilha" />
                        <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full rounded-lg border border-gray-300 p-3 text-sm dark:border-gray-700" required>
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        <p class="mt-2 text-xs text-gray-500">XLSX, XLS ou CSV, até 10 MB e 5.000 linhas. A carga completa exige arquivo Excel.</p>
                    </div>
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600">
                        <x-icon name="upload" class="size-5" />
                        Validar e importar
                    </button>
                </form>
            </x-panel>

            <x-panel>
                <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Modelos oficiais</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Baixe, preencha sem renomear cabeçalhos e envie nesta tela.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($types as $value => $label)
                        <div class="flex items-center justify-between gap-4 p-4">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            <x-table-action :href="route('imports.template', $value)" icon="download" label="Baixar modelo de {{ Str::lower($label) }}" variant="primary" />
                        </div>
                    @endforeach
                </div>
            </x-panel>
        </div>

        <x-panel>
            <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">Histórico de importações</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Resultados e erros ficam registrados por empresa.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800/50">
                        <tr><th class="px-5 py-3">Data</th><th class="px-5 py-3">Arquivo</th><th class="px-5 py-3">Tipo</th><th class="px-5 py-3">Resultado</th><th class="px-5 py-3">Responsável</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($imports as $import)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4">{{ $import->created_at->format('d/m/Y H:i') }}</td>
                                <td class="max-w-[220px] px-5 py-4"><p class="truncate font-medium text-gray-800 dark:text-white">{{ $import->original_filename }}</p>@if ($import->errors)<p class="mt-1 text-xs text-error-600">{{ collect($import->errors)->first() }}</p>@endif</td>
                                <td class="px-5 py-4">{{ $types[$import->type] ?? $import->type }}</td>
                                <td class="px-5 py-4"><x-status-badge :active="$import->status === 'completed'" :label="$import->status === 'completed' ? 'Concluída' : 'Falhou'" /><p class="mt-1 text-xs text-gray-500">{{ $import->created_rows }} criados · {{ $import->updated_rows }} atualizados</p></td>
                                <td class="px-5 py-4">{{ $import->user->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500">Nenhuma importação realizada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 p-5 dark:border-gray-800">{{ $imports->links() }}</div>
        </x-panel>
    </div>
</x-app-layout>

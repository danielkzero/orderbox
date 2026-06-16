<x-app-layout>
    <x-page-header title="Liberacao da API" description="Autorize quais aplicativos podem chamar a API antes do login do usuario.">
        <x-slot name="actions">
            <a href="{{ route('api-guide.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Ver guia da API
            </a>
        </x-slot>
    </x-page-header>

    @if ($plainSecret)
        <div class="mb-6 rounded-2xl border border-warning-200 bg-warning-50 p-5 text-sm text-warning-800 dark:border-warning-900 dark:bg-warning-950 dark:text-warning-200">
            <p class="font-semibold">Copie o segredo agora. Ele nao sera exibido novamente.</p>
            <code class="mt-3 block break-all rounded-lg bg-white p-3 text-gray-900 dark:bg-gray-900 dark:text-white">{{ $plainSecret }}</code>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[380px_1fr]">
        <x-panel>
            <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">Novo cliente de API</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use um cliente por aplicativo ou integracao.</p>
            </div>
            <form method="POST" action="{{ route('api-clients.store') }}" class="space-y-5 p-5">
                @csrf
                <div>
                    <x-input-label for="name" value="Nome" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" placeholder="Ionic Mobile" :value="old('name')" required />
                </div>
                <div>
                    <x-input-label for="channel" value="Canal" />
                    <select id="channel" name="channel" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="Mobile" @selected(old('channel') === 'Mobile')>Mobile</option>
                        <option value="Integration" @selected(old('channel') === 'Integration')>Integracao</option>
                    </select>
                </div>
                <button class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Gerar credenciais</button>
            </form>
        </x-panel>

        <x-panel>
            <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">Clientes liberados</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">A chave identifica o app. O segredo fica salvo somente como hash.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Nome</th>
                            <th class="px-5 py-3">Client key</th>
                            <th class="px-5 py-3">Canal</th>
                            <th class="px-5 py-3">Uso</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($clients as $client)
                            <tr>
                                <td class="px-5 py-4 font-medium text-gray-900 dark:text-white">{{ $client->name }}</td>
                                <td class="px-5 py-4"><code class="break-all text-xs text-gray-600 dark:text-gray-300">{{ $client->client_key }}</code></td>
                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $client->channel }}</td>
                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $client->last_used_at?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
                                <td class="px-5 py-4"><x-status-badge :active="$client->active" :label="$client->active ? 'Ativo' : 'Bloqueado'" /></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <form method="POST" action="{{ route('api-clients.regenerate', $client) }}">
                                            @csrf
                                            <button class="text-sm font-medium text-brand-600 dark:text-brand-400">Regenerar</button>
                                        </form>
                                        @if ($client->active)
                                            <form method="POST" action="{{ route('api-clients.deactivate', $client) }}">
                                                @csrf
                                                <button class="text-sm font-medium text-error-600">Bloquear</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Nenhum cliente de API criado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-5">{{ $clients->links() }}</div>
        </x-panel>
    </div>
</x-app-layout>

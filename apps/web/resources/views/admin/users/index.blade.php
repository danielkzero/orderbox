<x-app-layout>
    <x-page-header title="Usuários" description="Gerencie acessos, perfis e status dos usuários.">
        <x-slot name="actions"><a href="{{ route('users.create') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white">Novo usuário</a></x-slot>
    </x-page-header>
    <x-panel>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800/50"><tr><th class="px-5 py-3">Nome</th><th class="px-5 py-3">Perfil</th><th class="px-5 py-3">2FA</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Ações</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($users as $user)
                        <tr><td class="px-5 py-4"><strong class="block">{{ $user->name }}</strong><span class="text-gray-500">{{ $user->email }}</span></td><td class="px-5 py-4">{{ $user->role }}</td><td class="px-5 py-4"><x-status-badge :active="$user->two_factor_enabled" :label="$user->two_factor_enabled ? 'Ativo' : 'Desativado'" /></td><td class="px-5 py-4"><x-status-badge :active="$user->active" /></td><td class="px-5 py-4"><div class="flex gap-2"><a href="{{ route('users.edit', $user) }}" class="text-brand-600">Editar</a>@if ($user->active && !$user->is(auth()->user()))<form method="POST" action="{{ route('users.deactivate', $user) }}">@csrf<button class="text-error-600">Inativar</button></form>@endif</div></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-5">{{ $users->links() }}</div>
    </x-panel>
</x-app-layout>

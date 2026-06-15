<x-app-layout>
    <x-page-header title="Segurança e 2FA" description="Proteja a conta e gerencie sessões autenticadas." />
    <div class="grid gap-6 xl:grid-cols-2">
        <x-panel>
            <div class="border-b border-gray-200 p-5 dark:border-gray-800"><h2 class="font-semibold">Autenticação em dois fatores</h2></div>
            <div class="space-y-4 p-5">
                <x-status-badge :active="auth()->user()->two_factor_enabled" :label="auth()->user()->two_factor_enabled ? '2FA ativo' : '2FA desativado'" />
                @if (!auth()->user()->two_factor_enabled)
                    <p class="text-sm text-gray-500">Adicione a chave abaixo em um aplicativo autenticador e informe o código gerado.</p>
                    <code class="block break-all rounded-lg bg-gray-100 p-4 text-sm dark:bg-gray-800">{{ $secret }}</code>
                    <form method="POST" action="{{ route('security.2fa.enable') }}" class="flex gap-3">@csrf<input name="code" maxlength="6" placeholder="Código de 6 dígitos" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"><button class="rounded-lg bg-brand-500 px-4 text-sm font-medium text-white">Ativar 2FA</button></form>
                @else
                    <form method="POST" action="{{ route('security.2fa.disable') }}" class="space-y-3">@csrf @method('DELETE')<input name="password" type="password" placeholder="Confirme sua senha" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"><button class="rounded-lg bg-error-600 px-4 py-2.5 text-sm font-medium text-white">Desativar 2FA</button></form>
                @endif
            </div>
        </x-panel>
        <x-panel>
            <div class="border-b border-gray-200 p-5 dark:border-gray-800"><h2 class="font-semibold">Sessões recentes</h2></div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($sessions as $session)
                    <div class="flex items-center justify-between gap-4 p-5"><div><p class="font-medium">{{ $session->channel }} · {{ $session->ip_address ?: 'IP não informado' }}</p><p class="text-xs text-gray-500">{{ $session->last_activity_at->format('d/m/Y H:i') }} · {{ $session->active_slot ? 'Ativa' : 'Revogada' }}</p></div>@if($session->active_slot)<form method="POST" action="{{ route('security.sessions.revoke', $session) }}">@csrf @method('DELETE')<button class="text-sm font-medium text-error-600">Revogar</button></form>@endif</div>
                @endforeach
            </div>
        </x-panel>
    </div>
</x-app-layout>

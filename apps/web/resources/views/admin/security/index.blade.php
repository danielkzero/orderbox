<x-app-layout>
    <x-page-header title="Seguranca e 2FA" description="Proteja a conta, configure autenticacao em dois fatores e gerencie sessoes autenticadas." />

    <div class="grid gap-6 xl:grid-cols-2">
        <x-panel>
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Autenticacao em dois fatores</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escaneie o QR Code no Google Authenticator, Microsoft Authenticator ou app compativel.</p>
            </div>

            <div class="space-y-5 p-6">
                <x-status-badge :active="auth()->user()->two_factor_enabled" :label="auth()->user()->two_factor_enabled ? '2FA ativo' : '2FA desativado'" />

                @if (! auth()->user()->two_factor_enabled)
                    <div class="grid gap-5 lg:grid-cols-[260px_1fr]">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                            <div class="mx-auto flex size-[230px] items-center justify-center rounded-xl bg-white p-2">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Chave manual</p>
                                <code class="mt-2 block break-all rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">{{ $secret }}</code>
                            </div>

                            <form method="POST" action="{{ route('security.2fa.enable') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="code" value="Codigo de 6 digitos" />
                                    <x-text-input id="code" name="code" maxlength="6" inputmode="numeric" placeholder="123456" class="block w-full" required />
                                </div>
                                <button class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                                    Ativar 2FA
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('security.2fa.disable') }}" class="space-y-4">
                        @csrf
                        @method('DELETE')
                        <div>
                            <x-input-label for="password" value="Confirme sua senha" />
                            <x-text-input id="password" name="password" type="password" placeholder="Digite sua senha" class="block w-full" required />
                        </div>
                        <button class="rounded-lg bg-error-600 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-error-700">
                            Desativar 2FA
                        </button>
                    </form>
                @endif
            </div>
        </x-panel>

        <x-panel>
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Sessoes recentes</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revogue acessos que nao devem continuar ativos.</p>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($sessions as $session)
                    <div class="flex items-center justify-between gap-4 p-5">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $session->channel }} - {{ $session->ip_address ?: 'IP nao informado' }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $session->last_activity_at->format('d/m/Y H:i') }} - {{ $session->active_slot ? 'Ativa' : 'Revogada' }}</p>
                        </div>
                        @if ($session->active_slot)
                            <form method="POST" action="{{ route('security.sessions.revoke', $session) }}">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 hover:bg-error-50 dark:border-error-500/30 dark:hover:bg-error-500/10">Revogar</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500">Nenhuma sessao registrada.</div>
                @endforelse
            </div>
        </x-panel>
    </div>
</x-app-layout>

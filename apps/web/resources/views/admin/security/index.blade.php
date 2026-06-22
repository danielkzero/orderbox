<x-app-layout title="Segurança e 2FA">
    <x-page-header title="Segurança e 2FA" description="Proteja a conta, configure autenticação em dois fatores e gerencie sessões autenticadas." />

    <div class="grid gap-6 xl:grid-cols-2">
        <x-panel>
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Autenticação em dois fatores</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escaneie o QR Code no Google Authenticator, Microsoft Authenticator ou APP compatível.</p>
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
                                    <x-input-label for="code" value="Código de 6 dígitos" />
                                    <x-text-input id="code" name="code" maxlength="6" inputmode="numeric" placeholder="123456" class="block w-full" required />
                                </div>
                                <button class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                                    Ativar 2FA
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('security.2fa.disable') }}" class="space-y-4" data-confirm-title="Desativar autenticação em dois fatores?" data-confirm-message="Sua conta ficará protegida somente pela senha." data-confirm-label="Continuar" data-confirm-level="double" data-confirm-variant="danger" data-confirm-final-title="Remover a proteção adicional?" data-confirm-final-message="Esta é a confirmação final para desativar o 2FA desta conta." data-confirm-final-label="Sim, desativar">
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
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Sessões recentes</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revogue acessos que não devem continuar ativos. Exibimos 10 sessões por página.</p>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($sessions as $session)
                    <div class="flex items-center justify-between gap-4 p-5">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $session->channel === 'Mobile' ? 'APP' : $session->channel }} - {{ $session->ip_address ?: 'IP não informado' }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $session->last_activity_at->format('d/m/Y H:i') }} - {{ $session->active_slot ? 'Ativa' : 'Revogada' }}</p>
                        </div>
                        @if ($session->active_slot)
                            <form method="POST" action="{{ route('security.sessions.revoke', $session) }}" data-confirm-title="Revogar sessão?" data-confirm-message="O dispositivo precisará autenticar novamente para acessar o OrderBox." data-confirm-label="Revogar" data-confirm-variant="danger">
                                @csrf
                                @method('DELETE')
                                <x-table-action icon="log-out" label="Revogar sessão" variant="danger" />
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500">Nenhuma sessão registrada.</div>
                @endforelse
            </div>

            @if ($sessions->hasPages())
                <div class="border-t border-gray-200 p-5 dark:border-gray-800">
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                        Exibindo {{ $sessions->firstItem() }} a {{ $sessions->lastItem() }} de {{ $sessions->total() }} sessões
                    </p>
                    {{ $sessions->links() }}
                </div>
            @endif
        </x-panel>
    </div>
</x-app-layout>

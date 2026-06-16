<x-app-layout>
    <x-page-header title="Guia da API" description="Como liberar APPs, autenticar usuários e consumir endpoints do OrderBox." />

    <div class="space-y-6">
        <x-panel>
            <div class="space-y-4 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Base e segurança</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">A API pública fica em <code>/api/v1</code>. O login exige duas camadas: cliente de API liberado e credenciais do usuário.</p>
                <div class="rounded-xl bg-gray-950 p-4 text-sm text-gray-100">
                    <pre class="whitespace-pre-wrap">X-OrderBox-Client-Key: obx_sua_chave
X-OrderBox-Client-Secret: seu_segredo
Accept: application/json</pre>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">O segredo aparece apenas uma vez ao criar ou regenerar o cliente. Depois disso, somente o hash fica armazenado.</p>
            </div>
        </x-panel>

        <x-panel>
            <div class="space-y-4 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Login do APP</h2>
                <div class="rounded-xl bg-gray-950 p-4 text-sm text-gray-100">
                    <pre class="whitespace-pre-wrap">curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "X-OrderBox-Client-Key: obx_sua_chave" \
  -H "X-OrderBox-Client-Secret: seu_segredo" \
  -d "email=usuario@empresa.test" \
  -d "password=sua_senha"</pre>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">Sem 2FA, a resposta retorna <code>access_token</code>. Com 2FA, a resposta vem com status 202 e <code>challenge_id</code>.</p>
            </div>
        </x-panel>

        <x-panel>
            <div class="space-y-4 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Confirmação 2FA</h2>
                <div class="rounded-xl bg-gray-950 p-4 text-sm text-gray-100">
                    <pre class="whitespace-pre-wrap">curl -X POST http://127.0.0.1:8000/api/v1/auth/2fa/confirm \
  -H "Accept: application/json" \
  -H "X-OrderBox-Client-Key: obx_sua_chave" \
  -H "X-OrderBox-Client-Secret: seu_segredo" \
  -d "challenge_id=uuid_do_desafio" \
  -d "code=123456"</pre>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">A sessão APP anterior só é revogada depois que o código 2FA é validado.</p>
            </div>
        </x-panel>

        <x-panel>
            <div class="space-y-4 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Usando o token</h2>
                <div class="rounded-xl bg-gray-950 p-4 text-sm text-gray-100">
                    <pre class="whitespace-pre-wrap">curl http://127.0.0.1:8000/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer seu_access_token"</pre>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">O token Bearer identifica o usuário autenticado. Se outro login do APP acontecer com a mesma credencial, o token anterior perde validade.</p>
            </div>
        </x-panel>

        <x-panel>
            <div class="space-y-4 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Erros comuns</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                            <tr><th class="px-5 py-3">Status</th><th class="px-5 py-3">Código</th><th class="px-5 py-3">Quando ocorre</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr><td class="px-5 py-4">403</td><td class="px-5 py-4"><code>api_client_not_allowed</code></td><td class="px-5 py-4">Chave ausente, segredo inválido ou cliente bloqueado.</td></tr>
                            <tr><td class="px-5 py-4">422</td><td class="px-5 py-4"><code>validation_error</code></td><td class="px-5 py-4">Credencial, desafio ou código 2FA inválidos.</td></tr>
                            <tr><td class="px-5 py-4">401</td><td class="px-5 py-4"><code>unauthenticated</code></td><td class="px-5 py-4">Token Bearer ausente, expirado ou revogado.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-panel>
    </div>
</x-app-layout>

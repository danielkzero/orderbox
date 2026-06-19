<x-app-layout>
    <x-page-header title="Manual de uso" description="Fluxo básico para operar o OrderBox no painel web e no APP." />

    <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
        <x-panel>
            <div class="space-y-2 p-5 text-sm">
                @foreach (['primeiro-acesso' => 'Primeiro acesso', 'cadastros' => 'Cadastros', 'pedidos' => 'Pedidos', 'usuarios' => 'Usuários e 2FA', 'api' => 'API'] as $id => $label)
                    <a href="#{{ $id }}" class="block rounded-lg px-3 py-2 text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">{{ $label }}</a>
                @endforeach
            </div>
        </x-panel>

        <div class="space-y-6">
            <x-panel id="primeiro-acesso">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">1. Primeiro acesso</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Entre pelo painel web, confira sua empresa no dashboard e acompanhe os principais indicadores da operação.</p>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Acesse o login web.</li>
                        <li>Informe o e-mail e senha do usuário autorizado.</li>
                        <li>Se o usuário tiver 2FA ativo, confirme o código do aplicativo autenticador.</li>
                        <li>Revise o dashboard antes de iniciar os cadastros.</li>
                    </ol>
                </div>
            </x-panel>

            <x-panel id="cadastros">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">2. Cadastros operacionais</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Use os menus Clientes, Produtos, Categorias, Marcas e Unidades para manter a base comercial. As tabelas de preço são criadas e renomeadas diretamente no cabeçalho da lista de Produtos.</p>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Clique em "Novo registro" na listagem.</li>
                        <li>Preencha os campos obrigatórios.</li>
                        <li>Salve e confira o item na listagem.</li>
                        <li>Quando não quiser mais usar um item, clique em "Inativar". Isso preserva histórico e auditoria.</li>
                    </ol>
                </div>
            </x-panel>

            <x-panel id="pedidos">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">3. Pedidos e consulta</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Crie pedidos pelo painel selecionando cliente, representante, tabela de preço e todos os produtos necessários.</p>
                    <ul class="list-disc space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Pedidos vindos do APP entram com origem "APP".</li>
                        <li>Pedidos feitos pelo painel entram com origem "Web".</li>
                        <li>Use "Cancelar" para encerrar um pedido preservando histórico e auditoria.</li>
                    </ul>
                </div>
            </x-panel>

            <x-panel id="usuarios">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">4. Usuários, sessões e 2FA</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Administradores gerenciam usuários. Cada credencial só pode manter uma sessão web e uma sessão APP ativa ao mesmo tempo.</p>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Crie o usuário em Configurações, Usuários.</li>
                        <li>Ative 2FA em Segurança quando a conta exigir confirmação dupla.</li>
                        <li>Ao ocorrer novo login no mesmo canal, a sessão anterior é invalidada.</li>
                        <li>Com 2FA ativo, a sessão anterior só cai depois da confirmação do código.</li>
                    </ol>
                </div>
            </x-panel>

            <x-panel id="api">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">5. Liberação da API</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Antes de chamar login do APP ou integrações, crie um cliente em Liberação API. O APP deve enviar a chave e o segredo em todas as chamadas públicas de autenticação.</p>
                    <a href="{{ route('api-guide.index') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Abrir guia da API</a>
                </div>
            </x-panel>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-page-header title="Manual de uso" description="Fluxo basico para operar o OrderBox no painel web e no aplicativo mobile." />

    <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
        <x-panel>
            <div class="space-y-2 p-5 text-sm">
                @foreach (['primeiro-acesso' => 'Primeiro acesso', 'cadastros' => 'Cadastros', 'pedidos' => 'Pedidos', 'usuarios' => 'Usuarios e 2FA', 'api' => 'API'] as $id => $label)
                    <a href="#{{ $id }}" class="block rounded-lg px-3 py-2 text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">{{ $label }}</a>
                @endforeach
            </div>
        </x-panel>

        <div class="space-y-6">
            <x-panel id="primeiro-acesso">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">1. Primeiro acesso</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Entre pelo painel web, confira a empresa no dashboard e valide se os indicadores aparecem com os dados da hydradigital.</p>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Acesse o login web.</li>
                        <li>Informe o e-mail e senha do usuario autorizado.</li>
                        <li>Se o usuario tiver 2FA ativo, confirme o codigo do aplicativo autenticador.</li>
                        <li>Revise o dashboard antes de iniciar os cadastros.</li>
                    </ol>
                </div>
            </x-panel>

            <x-panel id="cadastros">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">2. Cadastros operacionais</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Use os menus Clientes, Produtos, Tabelas de Preco, Categorias, Marcas e Unidades para manter a base comercial.</p>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Clique em "Novo registro" na listagem.</li>
                        <li>Preencha os campos obrigatorios.</li>
                        <li>Salve e confira o item na listagem.</li>
                        <li>Quando nao quiser mais usar um item, clique em "Inativar". Isso preserva historico e auditoria.</li>
                    </ol>
                </div>
            </x-panel>

            <x-panel id="pedidos">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">3. Pedidos e consulta</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Os pedidos atuais aparecem em modo consulta. O proximo passo funcional e completar criacao, itens e envio de pedidos.</p>
                    <ul class="list-disc space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Pedidos vindos do mobile entram com origem "Mobile".</li>
                        <li>Pedidos feitos pelo painel entram com origem "Admin".</li>
                        <li>A auditoria registra as principais alteracoes feitas por usuario.</li>
                    </ul>
                </div>
            </x-panel>

            <x-panel id="usuarios">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">4. Usuarios, sessoes e 2FA</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Administradores gerenciam usuarios. Cada credencial so pode manter uma sessao web e uma sessao mobile ativa ao mesmo tempo.</p>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
                        <li>Crie o usuario em Administracao, Usuarios.</li>
                        <li>Ative 2FA em Seguranca quando a conta exigir confirmacao dupla.</li>
                        <li>Ao ocorrer novo login no mesmo canal, a sessao anterior e invalidada.</li>
                        <li>Com 2FA ativo, a sessao anterior so cai depois da confirmacao do codigo.</li>
                    </ol>
                </div>
            </x-panel>

            <x-panel id="api">
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">5. Liberacao da API</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Antes de chamar login mobile ou integracoes, crie um cliente em Liberacao API. O app deve enviar a chave e o segredo em todas as chamadas publicas de autenticacao.</p>
                    <a href="{{ route('api-guide.index') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Abrir guia da API</a>
                </div>
            </x-panel>
        </div>
    </div>
</x-app-layout>

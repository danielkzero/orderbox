<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orderbox | Força de vendas para distribuidores</title>
    <meta name="description" content="Orderbox organiza representantes, clientes, produtos, tabelas de preço, pedidos e API em uma plataforma comercial para web e APP.">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased dark:bg-gray-950 dark:text-white">
    <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-950/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <x-application-logo class="size-11 shrink-0 shadow-theme-xs" />
                <span>
                    <strong class="block text-xl font-semibold text-gray-900 dark:text-white">Orderbox</strong>
                    <small class="block text-xs text-gray-500 dark:text-gray-400">Sales force platform</small>
                </span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-gray-600 dark:text-gray-300 md:flex">
                <a href="#gestao" class="hover:text-brand-500">Gestão</a>
                <a href="#vendedor" class="hover:text-brand-500">Vendedores</a>
                <a href="#recursos" class="hover:text-brand-500">Recursos</a>
                <a href="#seguranca" class="hover:text-brand-500">Segurança</a>
            </nav>

            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Abrir painel</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]">Entrar</a>
            @endauth
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-[560px] bg-gradient-to-b from-brand-50 via-white to-gray-50 dark:from-brand-500/10 dark:via-gray-950 dark:to-gray-950"></div>
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8 lg:py-24">
                <div>
                    <span class="inline-flex rounded-full bg-brand-50 px-4 py-2 text-xs font-medium text-brand-500 ring-1 ring-brand-500/10 dark:bg-brand-500/15 dark:text-brand-300">
                        Plataforma comercial web + APP
                    </span>
                    <h1 class="mt-6 max-w-3xl text-4xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                        Controle sua força de vendas do cliente ao pedido.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-gray-600 dark:text-gray-400">
                        Orderbox centraliza representantes, carteiras de clientes, catálogo, tabelas de preço, regiões, pedidos e liberação de API em uma operação simples para distribuidores.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-6 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            Acessar Orderbox
                        </a>
                        <a href="#recursos" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            Ver recursos
                        </a>
                    </div>

                    <div class="mt-10 grid max-w-xl grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                            <strong class="block text-2xl text-gray-900 dark:text-white">Web</strong>
                            <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">gestão administrativa</span>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                            <strong class="block text-2xl text-gray-900 dark:text-white">APP</strong>
                            <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">vendas em campo</span>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                            <strong class="block text-2xl text-gray-900 dark:text-white">API</strong>
                            <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">integração liberada</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-gray-200 bg-white p-4 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 px-2 pb-4 dark:border-gray-800">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Dashboard comercial</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Hydradigital em tempo real</p>
                        </div>
                        <span class="rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">Online</span>
                    </div>

                    <div class="grid gap-4 py-5 sm:grid-cols-4">
                        @foreach ([['R$ 92k', 'Volume'], ['48', 'Pedidos'], ['126', 'Clientes'], ['3', 'Regiões']] as [$value, $label])
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                                <strong class="block text-xl text-gray-900 dark:text-white">{{ $value }}</strong>
                                <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[1fr_260px]">
                        <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-800">
                            <div class="mb-6 flex items-center justify-between">
                                <div>
                                    <h2 class="font-semibold text-gray-900 dark:text-white">Pedidos por semana</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Ritmo de vendas por representante</p>
                                </div>
                                <span class="text-sm font-medium text-success-600">+18%</span>
                            </div>
                            <div class="flex h-56 items-end gap-2">
                                @foreach ([34, 48, 42, 66, 58, 76, 72, 88, 82, 96, 90, 100] as $height)
                                    <span class="flex-1 rounded-t-lg bg-brand-500/80" style="height: {{ $height }}%"></span>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach ([['Pedido enviado', 'PED-00048', 'success'], ['Tabela revisada', 'Atacado SP', 'brand'], ['Sessão 2FA', 'confirmada', 'warning']] as [$title, $detail, $tone])
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $title }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $detail }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="gestao" class="border-y border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="text-sm font-medium text-brand-500">Para gestores</span>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Uma central de gestão comercial completa.</h2>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Acompanhe resultado, carteira, representantes, regiões e pedidos em um painel único.</p>
                </div>

                <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Mais previsibilidade', 'Indicadores de pedidos, volume e desempenho por período.'],
                        ['Mais controle', 'Regras de sessão, usuários, auditoria e permissão de API.'],
                        ['Mais assertividade', 'Clientes ligados a representantes, regiões e tabelas de preço.'],
                        ['Mais organização', 'Catálogo, marcas, categorias, unidades e produtos em cadastros consistentes.'],
                    ] as [$title, $description])
                        <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-950">
                            <div class="mb-5 flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/15">✓</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="vendedor" class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <span class="text-sm font-medium text-brand-500">Para vendedores</span>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">O apoio que o representante precisa para vender em campo.</h2>
                <p class="mt-5 text-gray-600 dark:text-gray-400">
                    A operação no APP foi pensada para consulta de carteira, catálogo, tabelas e criação de pedidos com sincronização controlada.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['Carteira de clientes', 'Acesse os clientes vinculados ao representante e sua região.'],
                    ['Pedido com vários itens', 'Monte pedidos com produtos, quantidades, descontos e totais calculados.'],
                    ['Catálogo comercial', 'Consulte produtos, marcas, unidades, estoque e tabelas ativas.'],
                    ['Sincronização', 'Estrutura preparada para operação online e evolução offline.'],
                ] as [$title, $description])
                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $description }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="recursos" class="bg-gray-100 py-20 dark:bg-gray-900/60">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <span class="text-sm font-medium text-brand-500">Recursos do Orderbox</span>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Tudo que sustenta o ciclo comercial.</h2>
                    </div>
                    <p class="max-w-xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        A home foi pensada como vitrine do que já existe no painel e do que está documentado como base da operação.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Clientes', 'Cadastro, documento, limite, região e representantes.'],
                        ['Produtos', 'SKU, categoria, marca, unidade e disponibilidade.'],
                        ['Tabelas de preço', 'Preços por produto e quantidade mínima.'],
                        ['Pedidos', 'Status, origem, representante, itens e totais.'],
                        ['Regiões', 'Organização comercial por UF, cidade e carteira.'],
                        ['API', 'Clientes autorizados, segredo e rotação controlada.'],
                        ['Manual', 'Passo a passo operacional dentro do painel.'],
                        ['Auditoria', 'Registro das ações administrativas importantes.'],
                    ] as [$title, $description])
                        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-950">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="seguranca" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-gray-200 bg-white p-8 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 lg:p-10">
                <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div>
                        <span class="text-sm font-medium text-brand-500">Segurança e integração</span>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Autenticação única por canal e API liberada por cliente.</h2>
                        <p class="mt-5 text-gray-600 dark:text-gray-400">
                            A mesma credencial pode ter uma sessão web e uma sessão APP, mas nunca duas sessões simultâneas no mesmo tipo de acesso. Com 2FA, a invalidação anterior exige confirmação.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ([['Web', 'Sessão administrativa'], ['APP', 'Sessão de campo'], ['2FA', 'Confirmação dupla']] as [$title, $description])
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                                <strong class="block text-xl text-gray-900 dark:text-white">{{ $title }}</strong>
                                <span class="mt-2 block text-sm text-gray-500 dark:text-gray-400">{{ $description }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="px-4 pb-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl rounded-[2rem] bg-brand-500 p-8 text-white shadow-theme-xl lg:p-12">
                <div class="flex flex-col justify-between gap-8 lg:flex-row lg:items-center">
                    <div>
                        <h2 class="text-3xl font-semibold tracking-tight">Pronto para operar a Hydradigital no Orderbox?</h2>
                        <p class="mt-3 max-w-2xl text-brand-50">Entre no painel, revise cadastros, valide pedidos e siga pelo manual interno para evoluir o uso da plataforma.</p>
                    </div>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-sm font-medium text-brand-600 shadow-theme-xs hover:bg-gray-50">
                        {{ auth()->check() ? 'Abrir dashboard' : 'Entrar no sistema' }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-8 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <span>Orderbox | Força de vendas conectada</span>
            <span>Design simples baseado no padrão TailAdmin.</span>
        </div>
    </footer>
</body>
</html>

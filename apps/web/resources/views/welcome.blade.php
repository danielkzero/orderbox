<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OrderBox · Força de vendas conectada</title>
    <meta name="description" content="Pedidos, clientes, catálogo e representantes em uma operação comercial conectada.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-[#080b12] text-white">
    <div class="pointer-events-none fixed inset-0 opacity-70">
        <div class="absolute -left-32 top-20 size-96 rounded-full bg-brand-500/20 blur-[120px]"></div>
        <div class="absolute -right-20 top-1/3 size-[30rem] rounded-full bg-cyan-400/10 blur-[150px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.025)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.025)_1px,transparent_1px)] bg-[size:72px_72px]"></div>
    </div>

    <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-6 py-7 lg:px-8">
        <a href="/" class="flex items-center gap-3">
            <span class="flex size-11 items-center justify-center rounded-2xl bg-brand-500 text-xl font-bold shadow-[0_0_40px_rgba(70,95,255,.45)]">O</span>
            <span>
                <strong class="block text-lg leading-none">OrderBox</strong>
                <small class="mt-1 block text-[10px] uppercase tracking-[.28em] text-gray-400">Sales command</small>
            </span>
        </a>
        <nav class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-gray-950">Abrir painel</a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10">Entrar</a>
            @endauth
        </nav>
    </header>

    <main class="relative z-10">
        <section class="mx-auto grid min-h-[calc(100vh-100px)] max-w-7xl items-center gap-16 px-6 py-16 lg:grid-cols-[1fr_1.08fr] lg:px-8">
            <div>
                <div class="mb-7 inline-flex items-center gap-3 rounded-full border border-brand-400/25 bg-brand-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-[.2em] text-brand-200">
                    <span class="size-2 rounded-full bg-emerald-400 shadow-[0_0_16px_rgba(52,211,153,.8)]"></span>
                    Operação comercial em movimento
                </div>
                <h1 class="max-w-3xl text-5xl font-semibold leading-[1.02] tracking-[-.055em] sm:text-6xl lg:text-[5.5rem]">
                    Venda em campo.
                    <span class="block bg-gradient-to-r from-brand-300 via-cyan-300 to-emerald-300 bg-clip-text text-transparent">Controle no escritório.</span>
                </h1>
                <p class="mt-8 max-w-xl text-lg leading-8 text-gray-400">
                    Uma central de força de vendas para transformar clientes, catálogo, preços e pedidos em uma operação comercial simples, rastreável e pronta para trabalhar online ou offline.
                </p>
                <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="group inline-flex items-center justify-center gap-3 rounded-2xl bg-brand-500 px-7 py-4 text-sm font-semibold text-white shadow-[0_18px_60px_rgba(70,95,255,.3)] transition hover:-translate-y-1 hover:bg-brand-400">
                        Acessar operação
                        <span class="transition group-hover:translate-x-1">→</span>
                    </a>
                    <a href="#estrutura" class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-7 py-4 text-sm font-semibold text-gray-200 backdrop-blur transition hover:bg-white/10">
                        Conhecer estrutura
                    </a>
                </div>
                <div class="mt-14 grid max-w-xl grid-cols-3 gap-6 border-t border-white/10 pt-7">
                    <div><strong class="block text-2xl">1 + 1</strong><span class="mt-1 block text-xs text-gray-500">sessão Web e Mobile</span></div>
                    <div><strong class="block text-2xl">2FA</strong><span class="mt-1 block text-xs text-gray-500">proteção inteligente</span></div>
                    <div><strong class="block text-2xl">Offline</strong><span class="mt-1 block text-xs text-gray-500">vendas sem pausa</span></div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-8 rounded-[3rem] bg-gradient-to-br from-brand-500/25 via-transparent to-cyan-400/15 blur-2xl"></div>
                <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[#101520]/90 p-3 shadow-2xl backdrop-blur">
                    <div class="flex items-center justify-between border-b border-white/8 px-4 py-3">
                        <div class="flex gap-2"><span class="size-2.5 rounded-full bg-rose-400"></span><span class="size-2.5 rounded-full bg-amber-300"></span><span class="size-2.5 rounded-full bg-emerald-400"></span></div>
                        <span class="text-[10px] uppercase tracking-[.25em] text-gray-500">hydradigital · live desk</span>
                        <span class="rounded-full bg-emerald-400/10 px-2.5 py-1 text-[10px] text-emerald-300">Online</span>
                    </div>
                    <div class="grid gap-3 p-3 sm:grid-cols-[.38fr_1fr]">
                        <aside class="hidden rounded-2xl border border-white/8 bg-white/[.025] p-4 sm:block">
                            <div class="mb-6 h-2 w-20 rounded bg-white/15"></div>
                            @foreach (['Dashboard','Clientes','Produtos','Pedidos','Segurança'] as $index => $item)
                                <div class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs {{ $index === 0 ? 'bg-brand-500 text-white' : 'text-gray-500' }}">
                                    <span class="size-2 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-gray-700' }}"></span>{{ $item }}
                                </div>
                            @endforeach
                        </aside>
                        <div class="space-y-3">
                            <div class="grid grid-cols-3 gap-3">
                                @foreach ([['48','Pedidos'],['126','Clientes'],['R$ 92k','Volume']] as [$value,$label])
                                    <div class="rounded-2xl border border-white/8 bg-white/[.035] p-4">
                                        <span class="block text-lg font-semibold">{{ $value }}</span>
                                        <span class="mt-1 block text-[10px] uppercase tracking-wider text-gray-500">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="rounded-2xl border border-white/8 bg-white/[.035] p-5">
                                <div class="mb-7 flex items-center justify-between"><span class="text-xs font-medium">Ritmo comercial</span><span class="text-[10px] text-emerald-300">+18,4%</span></div>
                                <div class="flex h-36 items-end gap-2">
                                    @foreach ([28,42,36,58,48,72,62,88,74,96,82,100] as $height)
                                        <span class="flex-1 rounded-t bg-gradient-to-t from-brand-600 to-cyan-300/80" style="height: {{ $height }}%"></span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-white/8 bg-white/[.035] p-4"><span class="text-[10px] uppercase text-gray-500">Último pedido</span><strong class="mt-2 block text-sm">PED-00048 · Sent</strong></div>
                                <div class="rounded-2xl border border-white/8 bg-white/[.035] p-4"><span class="text-[10px] uppercase text-gray-500">Sincronização</span><strong class="mt-2 block text-sm text-emerald-300">Tudo atualizado</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="estrutura" class="border-y border-white/8 bg-white/[.025]">
            <div class="mx-auto max-w-7xl px-6 py-24 lg:px-8">
                <div class="mb-14 max-w-2xl">
                    <span class="text-xs font-semibold uppercase tracking-[.25em] text-brand-300">Uma operação, duas frentes</span>
                    <h2 class="mt-4 text-4xl font-semibold tracking-tight">O escritório organiza. O representante vende.</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ([
                        ['01','Comando comercial','Dashboard, clientes, catálogo, preços e pedidos reunidos em uma visão objetiva.'],
                        ['02','Campo conectado','O Mobile Ionic leva carteira e catálogo para o representante, inclusive offline.'],
                        ['03','Segurança por canal','Sessão única por Web e Mobile, proteção 2FA e auditoria das ações críticas.'],
                    ] as [$number,$title,$description])
                        <article class="rounded-[1.75rem] border border-white/8 bg-white/[.035] p-7 transition hover:-translate-y-1 hover:border-brand-400/30 hover:bg-white/[.055]">
                            <span class="text-xs font-semibold text-brand-300">{{ $number }}</span>
                            <h3 class="mt-12 text-xl font-semibold">{{ $title }}</h3>
                            <p class="mt-3 text-sm leading-6 text-gray-400">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <footer class="relative z-10 mx-auto flex max-w-7xl flex-col gap-3 px-6 py-10 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">
        <span>OrderBox · força de vendas</span>
        <span>Painel baseado em TailAdmin Laravel · MIT</span>
    </footer>
</body>
</html>

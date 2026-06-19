@props([
    'title' => 'Acesse sua conta',
    'subtitle' => 'Entre com suas credenciais para continuar no painel Orderbox.',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Orderbox') }}</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-900 antialiased dark:bg-gray-900">
        <main class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(520px,44vw)]">
            <section class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="w-full max-w-md">
                    <a href="{{ url('/') }}" class="mb-10 inline-flex items-center gap-3 text-sm font-medium text-gray-600 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
                        <x-application-logo class="size-10 shrink-0 shadow-theme-xs" />
                        <span>Voltar para o início</span>
                    </a>

                    <div class="mb-8">
                        <span class="mb-3 inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-500 dark:bg-brand-500/15 dark:text-brand-400">
                            Orderbox Admin
                        </span>
                        <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">{{ $title }}</h1>
                        <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
                    </div>

                    {{ $slot }}
                </div>
            </section>

            <aside class="relative hidden overflow-hidden bg-gray-950 px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(70,95,255,0.45),transparent_30%),radial-gradient(circle_at_80%_0%,rgba(34,197,94,0.18),transparent_26%),linear-gradient(135deg,#111827_0%,#030712_60%,#0f172a_100%)]"></div>
                <div class="absolute inset-x-10 top-16 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
                <div class="absolute bottom-20 right-12 grid grid-cols-8 gap-3 opacity-30">
                    @for ($i = 0; $i < 64; $i++)
                        <span class="size-1.5 rounded-full bg-white"></span>
                    @endfor
                </div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-3">
                        <x-application-logo class="size-12 shrink-0 shadow-theme-lg" />
                        <span class="text-2xl font-semibold">Orderbox</span>
                    </div>

                    <div class="mt-16 max-w-xl">
                        <p class="text-sm font-medium uppercase tracking-[0.28em] text-brand-200">Força de vendas</p>
                        <h2 class="mt-5 text-4xl font-semibold leading-tight">Autenticação segura para web, APP e API.</h2>
                        <p class="mt-5 text-base leading-7 text-gray-300">
                            Controle sessões, segurança e operação comercial em uma experiência única para toda a empresa.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 grid gap-4">
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-300">Sessão web</span>
                            <span class="rounded-full bg-success-500/20 px-3 py-1 text-xs font-medium text-success-300">Protegida</span>
                        </div>
                        <div class="mt-5 h-2 rounded-full bg-white/10">
                            <div class="h-2 w-3/4 rounded-full bg-brand-400"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <p class="text-2xl font-semibold">2FA</p>
                            <p class="mt-1 text-sm text-gray-300">Dupla confirmação</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <p class="text-2xl font-semibold">API</p>
                            <p class="mt-1 text-sm text-gray-300">Clientes liberados</p>
                        </div>
                    </div>
                </div>
            </aside>
        </main>
    </body>
</html>

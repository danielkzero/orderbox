<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') | OrderBox</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased dark:bg-gray-950 dark:text-white">
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <section class="w-full max-w-xl text-center">
            <a href="{{ url('/') }}" class="mx-auto inline-flex items-center gap-3">
                <x-application-logo class="size-12 shadow-theme-xs" />
                <span class="text-xl font-semibold">OrderBox</span>
            </a>

            <p class="mt-12 text-sm font-semibold uppercase tracking-[0.3em] text-brand-500">@yield('code')</p>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">@yield('title')</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-gray-500 dark:text-gray-400">@yield('message')</p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">Voltar ao painel</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">Entrar novamente</a>
                @endauth
                <button type="button" onclick="history.back()" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">Voltar</button>
            </div>
        </section>
    </main>
</body>
</html>

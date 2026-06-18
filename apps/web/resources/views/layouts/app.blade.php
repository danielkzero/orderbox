<!DOCTYPE html>
<html lang="pt-BR" x-data="{ dark: localStorage.theme === 'dark', sidebarOpen: false, sidebarCollapsed: false, sidebarExpandedOnHover: false }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'OrderBox' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    <x-feedback-center />
    <x-confirmation-dialog />

    <div class="min-h-screen xl:flex">
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-gray-900/50 xl:hidden"></div>
        @include('layouts.sidebar')

        <div class="min-w-0 flex-1 transition-all duration-300" :class="sidebarCollapsed ? 'xl:ml-[92px]' : 'xl:ml-[290px]'">
            @include('layouts.header')

            <main class="mx-auto max-w-screen-2xl p-4 md:p-6">
                <x-context-navigation />

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

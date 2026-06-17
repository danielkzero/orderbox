<!DOCTYPE html>
<html lang="pt-BR" x-data="{ dark: localStorage.theme === 'dark', sidebarOpen: false, sidebarCollapsed: false, sidebarExpandedOnHover: false }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'OrderBox' }}</title>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    <div class="min-h-screen xl:flex">
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-gray-900/50 xl:hidden"></div>
        @include('layouts.sidebar')

        <div class="min-w-0 flex-1 transition-all duration-300" :class="sidebarCollapsed ? 'xl:ml-[92px]' : 'xl:ml-[290px]'">
            @include('layouts.header')

            <main class="mx-auto max-w-screen-2xl p-4 md:p-6">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-900 dark:bg-success-950 dark:text-success-300">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-900 dark:bg-error-950 dark:text-error-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

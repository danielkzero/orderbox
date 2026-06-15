<header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
    <div class="flex h-20 items-center justify-between px-4 md:px-6">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="rounded-lg border border-gray-200 p-2 text-gray-600 dark:border-gray-700 dark:text-gray-300 xl:hidden">
                <span class="block text-xl">☰</span>
            </button>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Painel administrativo</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ auth()->user()->company->trade_name }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button @click="dark = !dark; localStorage.theme = dark ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', dark)"
                    class="flex size-10 items-center justify-center rounded-full border border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                <span x-text="dark ? '☀' : '☾'"></span>
            </button>

            <div class="hidden text-right sm:block">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->role }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Sair
                </button>
            </form>
        </div>
    </div>
</header>

<header class="sticky top-0 z-30 border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex h-[76px] items-center justify-between gap-4 px-4 md:px-6">
        <div class="flex flex-1 items-center gap-4">
            <button @click="sidebarOpen = true" class="flex size-11 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] xl:hidden">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                </svg>
            </button>

            <form class="hidden w-full max-w-[430px] items-center rounded-xl border border-gray-200 bg-white px-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 md:flex">
                <svg class="mr-3 size-5 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3-3" stroke-linecap="round" />
                </svg>
                <input type="search" placeholder="Pesquisar ou digitar comando..." class="h-11 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-0 dark:text-white/90 dark:placeholder:text-white/30">
                <span class="rounded-lg border border-gray-200 px-2 py-1 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">Ctrl K</span>
            </form>
        </div>

        <div class="flex items-center gap-3">
            <button @click="dark = !dark; localStorage.theme = dark ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', dark)"
                    class="flex size-11 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                <svg x-show="!dark" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z" />
                </svg>
                <svg x-show="dark" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                </svg>
            </button>

            <div class="relative">
                <button class="flex size-11 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                </button>
                <span class="absolute right-2 top-2 size-2 rounded-full bg-orange-400"></span>
            </div>

            <div class="hidden items-center gap-3 pl-1 sm:flex">
                <span class="flex size-11 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ strtoupper(Str::substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->role }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="hidden rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] lg:inline-flex">
                    Sair
                </button>
            </form>
        </div>
    </div>
</header>

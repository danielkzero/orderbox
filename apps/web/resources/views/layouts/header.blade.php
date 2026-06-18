@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-40 overflow-visible border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex h-[76px] items-center justify-between gap-4 px-4 md:px-6">
        <div class="flex flex-1 items-center gap-4">
            <button
                type="button"
                @click="window.innerWidth >= 1280 ? sidebarCollapsed = ! sidebarCollapsed : sidebarOpen = true"
                class="flex size-11 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                :class="sidebarCollapsed ? 'border-brand-500 ring-3 ring-brand-500/10 text-brand-500' : ''"
                aria-label="Recolher ou abrir menu"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                </svg>
            </button>

            <span class="hidden text-sm text-gray-500 dark:text-gray-400 md:block">{{ $user->company->trade_name }}</span>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="dark = !dark; localStorage.theme = dark ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', dark)"
                class="flex size-11 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                aria-label="Alternar tema"
            >
                <svg x-show="!dark" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z" />
                </svg>
                <svg x-show="dark" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                </svg>
            </button>

            <div class="relative" x-data="{ open: false }">
                <button
                    type="button"
                    @click="open = ! open"
                    class="flex items-center gap-3 rounded-lg border border-transparent py-1 pl-1 pr-2 hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                    :class="open ? 'border-brand-500 ring-3 ring-brand-500/10' : ''"
                    aria-label="Abrir opções do usuário"
                >
                    <span class="flex size-11 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        {{ strtoupper(Str::substr($user->name, 0, 1)) }}
                    </span>
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->name }}</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $user->role }}</span>
                    </span>
                    <svg class="hidden size-4 text-gray-500 transition sm:block" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-on:click.outside="open = false" class="absolute right-0 z-50 mt-3 w-[260px] rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-4 py-4 dark:border-gray-800">
                        <p class="font-semibold text-gray-800 dark:text-white/90">{{ $user->name }}</p>
                        <p class="mt-1 break-all text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>

                    <div class="space-y-1 p-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            <x-icon name="user" class="size-5 text-gray-500" />
                            Editar perfil
                        </a>
                        <a href="{{ route('security.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            <x-icon name="lock" class="size-5 text-gray-500" />
                            Segurança e 2FA
                        </a>
                        <a href="{{ route('manual.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            <x-icon name="book" class="size-5 text-gray-500" />
                            Suporte
                        </a>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 p-2 dark:border-gray-800">
                        @csrf
                        <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            <svg class="size-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

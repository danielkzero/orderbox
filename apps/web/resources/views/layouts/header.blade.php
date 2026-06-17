@php
    $user = auth()->user();
    $notifications = [
        ['title' => 'Pedido aguardando aprovação', 'detail' => 'Revise os pedidos enviados pelo APP.', 'time' => '5 min atrás', 'tone' => 'bg-success-500'],
        ['title' => 'Tabela de preço atualizada', 'detail' => 'Confira os produtos vinculados.', 'time' => '18 min atrás', 'tone' => 'bg-brand-500'],
        ['title' => 'Nova sessão ativa', 'detail' => 'Autenticação web registrada com sucesso.', 'time' => '1 h atrás', 'tone' => 'bg-warning-500'],
    ];
    $notificationsUrl = in_array($user->role, ['Admin', 'Manager'], true) ? route('audit-logs.index') : route('manual.index');
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
                    class="relative flex size-11 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                    :class="open ? 'border-brand-500 ring-3 ring-brand-500/10' : ''"
                    aria-label="Abrir notificações"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <span class="absolute right-2 top-2 size-2 rounded-full bg-orange-400"></span>
                </button>

                <div x-show="open" x-cloak x-on:click.outside="open = false" class="absolute right-0 z-50 mt-3 w-[360px] rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-4 dark:border-gray-800">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Notificações</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Atualizações recentes do sistema.</p>
                        </div>
                        <button type="button" @click="open = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.05] dark:hover:text-gray-300">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="max-h-[360px] space-y-2 overflow-y-auto p-3">
                        @foreach ($notifications as $notification)
                            <a href="{{ $notificationsUrl }}" class="flex gap-3 rounded-xl border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.03]">
                                <span class="relative flex size-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    {{ Str::substr($notification['title'], 0, 1) }}
                                    <span class="absolute bottom-0 right-0 size-2.5 rounded-full border-2 border-white {{ $notification['tone'] }} dark:border-gray-900"></span>
                                </span>
                                <span>
                                    <strong class="block text-sm font-semibold text-gray-800 dark:text-white/90">{{ $notification['title'] }}</strong>
                                    <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">{{ $notification['detail'] }}</span>
                                    <span class="mt-2 block text-xs text-gray-400">{{ $notification['time'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-100 p-3 dark:border-gray-800">
                        <a href="{{ $notificationsUrl }}" class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            Ver todas as notificações
                        </a>
                    </div>
                </div>
            </div>

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

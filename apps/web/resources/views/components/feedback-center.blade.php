@php
    $notifications = [];

    $statusMessages = [
        'profile-updated' => 'Dados do perfil atualizados.',
        'password-updated' => 'Senha atualizada com sucesso.',
        'verification-link-sent' => 'Um novo link de verificação foi enviado.',
    ];

    if (session('status')) {
        $notifications[] = [
            'id' => 'status',
            'type' => 'success',
            'title' => 'Operação concluída',
            'message' => $statusMessages[session('status')] ?? session('status'),
            'timeout' => 5000,
        ];
    }

    if ($errors->any()) {
        $notifications[] = [
            'id' => 'validation',
            'type' => 'error',
            'title' => 'Não foi possível concluir',
            'message' => $errors->first(),
            'timeout' => 0,
        ];
    }
@endphp

<div
    class="pointer-events-none fixed right-4 top-20 z-[100000] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3"
    x-data="notificationCenter(@js($notifications))"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="notification.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-6 opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-6 opacity-0"
            class="pointer-events-auto overflow-hidden rounded-xl border bg-white shadow-theme-lg dark:bg-gray-900"
            :class="notification.type === 'error' ? 'border-error-200 dark:border-error-500/30' : 'border-success-200 dark:border-success-500/30'"
            :role="notification.type === 'error' ? 'alert' : 'status'"
        >
            <div class="flex items-start gap-3 p-4">
                <span
                    class="flex size-9 shrink-0 items-center justify-center rounded-full"
                    :class="notification.type === 'error' ? 'bg-error-50 text-error-600 dark:bg-error-500/15' : 'bg-success-50 text-success-600 dark:bg-success-500/15'"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <template x-if="notification.type === 'error'"><path d="m8 8 8 8M16 8l-8 8"/></template>
                        <template x-if="notification.type !== 'error'"><path d="m7 12 3 3 7-7"/></template>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="notification.title"></p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="notification.message"></p>
                </div>
                <button type="button" @click="dismiss(notification.id)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="Fechar notificação">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <div x-show="notification.timeout > 0" class="h-1 bg-gray-100 dark:bg-gray-800">
                <div class="h-full origin-left bg-brand-500" :style="`animation: notification-progress ${notification.timeout}ms linear forwards`"></div>
            </div>
        </div>
    </template>
</div>

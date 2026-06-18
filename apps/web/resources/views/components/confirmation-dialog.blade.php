<div
    x-data="confirmationDialog()"
    x-on:open-confirmation.window="openDialog($event.detail)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100001] flex items-center justify-center p-4"
    @keydown.escape.window="closeDialog()"
    role="dialog"
    aria-modal="true"
    :aria-labelledby="'confirmation-title'"
>
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-gray-950/60 backdrop-blur-[1px]" @click="closeDialog()"></div>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-4 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
    >
        <div class="flex items-start gap-4">
            <span
                class="flex size-12 shrink-0 items-center justify-center rounded-full"
                :class="variant === 'danger' ? 'bg-error-50 text-error-600 dark:bg-error-500/15' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15'"
            >
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 8v5M12 17h.01"/><path d="M10.3 3.7 2.5 17.2A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.8L13.7 3.7a2 2 0 0 0-3.4 0Z"/>
                </svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide" :class="variant === 'danger' ? 'text-error-600' : 'text-warning-600'" x-text="step === 2 ? 'Confirmação final' : eyebrow"></p>
                <h2 id="confirmation-title" class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90" x-text="step === 2 ? finalTitle : title"></h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400" x-text="step === 2 ? finalMessage : message"></p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeDialog()" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Voltar
            </button>
            <button
                type="button"
                @click="confirm()"
                class="inline-flex min-w-28 items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                :class="variant === 'danger' ? 'bg-error-600 hover:bg-error-700' : 'bg-warning-500 hover:bg-warning-600'"
                x-text="step === 2 ? finalLabel : confirmLabel"
            ></button>
        </div>
    </div>
</div>

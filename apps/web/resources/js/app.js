import './bootstrap';
import flatpickr from 'flatpickr';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('notificationCenter', (initialNotifications = []) => ({
    notifications: initialNotifications.map((notification) => ({ ...notification, visible: true })),
    init() {
        this.notifications.forEach((notification) => {
            if (notification.timeout > 0) {
                window.setTimeout(() => this.dismiss(notification.id), notification.timeout);
            }
        });

        window.addEventListener('notify', (event) => this.push(event.detail));
    },
    push(notification) {
        const item = {
            id: notification.id || `${Date.now()}-${Math.random()}`,
            type: notification.type || 'info',
            title: notification.title || 'Notificação',
            message: notification.message || '',
            timeout: notification.timeout ?? 5000,
            visible: true,
        };

        this.notifications.push(item);

        if (item.timeout > 0) {
            window.setTimeout(() => this.dismiss(item.id), item.timeout);
        }
    },
    dismiss(id) {
        const notification = this.notifications.find((item) => item.id === id);

        if (notification) {
            notification.visible = false;
            window.setTimeout(() => {
                this.notifications = this.notifications.filter((item) => item.id !== id);
            }, 250);
        }
    },
}));

Alpine.data('confirmationDialog', () => ({
    open: false,
    form: null,
    step: 1,
    title: '',
    message: '',
    eyebrow: 'Confirme a ação',
    confirmLabel: 'Confirmar',
    finalTitle: 'Deseja realmente continuar?',
    finalMessage: 'Esta ação terá efeito imediato e poderá exigir intervenção administrativa para ser desfeita.',
    finalLabel: 'Sim, continuar',
    variant: 'warning',
    doubleConfirmation: false,
    openDialog(detail) {
        this.form = detail.form;
        this.title = detail.title;
        this.message = detail.message;
        this.eyebrow = detail.eyebrow;
        this.confirmLabel = detail.confirmLabel;
        this.finalTitle = detail.finalTitle;
        this.finalMessage = detail.finalMessage;
        this.finalLabel = detail.finalLabel;
        this.variant = detail.variant;
        this.doubleConfirmation = detail.doubleConfirmation;
        this.step = 1;
        this.open = true;
        document.body.classList.add('overflow-hidden');
    },
    closeDialog() {
        this.open = false;
        this.form = null;
        this.step = 1;
        document.body.classList.remove('overflow-hidden');
    },
    confirm() {
        if (this.doubleConfirmation && this.step === 1) {
            this.step = 2;

            return;
        }

        const form = this.form;
        this.closeDialog();

        if (form) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    },
}));

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    flatpickr('[data-datepicker]', {
        altInput: true,
        altFormat: 'd/m/Y',
        dateFormat: 'Y-m-d',
        allowInput: true,
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmTitle && form.dataset.confirmed !== 'true') {
                event.preventDefault();
                window.dispatchEvent(new CustomEvent('open-confirmation', {
                    detail: {
                        form,
                        title: form.dataset.confirmTitle,
                        message: form.dataset.confirmMessage || 'Revise os dados antes de continuar.',
                        eyebrow: form.dataset.confirmEyebrow || 'Confirme a ação',
                        confirmLabel: form.dataset.confirmLabel || 'Confirmar',
                        finalTitle: form.dataset.confirmFinalTitle || 'Deseja realmente continuar?',
                        finalMessage: form.dataset.confirmFinalMessage || 'Esta ação terá efeito imediato e poderá exigir intervenção administrativa para ser desfeita.',
                        finalLabel: form.dataset.confirmFinalLabel || 'Sim, continuar',
                        variant: form.dataset.confirmVariant || 'warning',
                        doubleConfirmation: form.dataset.confirmLevel === 'double',
                    },
                }));

                return;
            }

            if (form.dataset.submitting === 'true') {
                event.preventDefault();

                return;
            }

            form.dataset.submitting = 'true';
            form.setAttribute('aria-busy', 'true');
            const submitButtons = form.querySelectorAll('button[type="submit"], button:not([type])');
            submitButtons.forEach((button) => {
                button.classList.add('pointer-events-none', 'opacity-60');
                button.setAttribute('aria-disabled', 'true');
            });

            const submitter = event.submitter || submitButtons[0];
            if (submitter && submitter.dataset.loadingApplied !== 'true') {
                const spinner = document.createElement('span');
                spinner.className = 'mr-2 inline-block size-4 animate-spin rounded-full border-2 border-current border-r-transparent align-[-0.125em]';
                spinner.setAttribute('role', 'status');
                spinner.setAttribute('aria-label', 'Processando');
                submitter.prepend(spinner);
                submitter.dataset.loadingApplied = 'true';
            }
        });
    });
});

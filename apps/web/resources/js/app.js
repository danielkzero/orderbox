import './bootstrap';
import flatpickr from 'flatpickr';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
            if (form.dataset.submitting === 'true') {
                event.preventDefault();

                return;
            }

            form.dataset.submitting = 'true';
            form.setAttribute('aria-busy', 'true');
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
                button.classList.add('pointer-events-none', 'opacity-60');
                button.setAttribute('aria-disabled', 'true');
            });
        });
    });
});

<x-guest-layout
    title="Verificação em duas etapas"
    subtitle="Informe o código de segurança para confirmar esta autenticação e invalidar a sessão anterior neste mesmo canal."
>
    <form method="POST" action="{{ route('auth.2fa.store') }}" class="space-y-6" x-data="{
        code: '',
        digits: ['', '', '', '', '', ''],
        sync() { this.code = this.digits.join('') },
        paste(value) {
            const numbers = value.replace(/\D/g, '').slice(0, 6).split('');
            this.digits = ['', '', '', '', '', ''].map((_, index) => numbers[index] || '');
            this.sync();
        }
    }">
        @csrf

        <input type="hidden" name="code" x-model="code">

        <div>
            <x-input-label value="Digite os 6 dígitos do código" />
            <div class="mt-3 grid grid-cols-6 gap-3">
                @for ($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        x-model="digits[{{ $i }}]"
                        @input="digits[{{ $i }}] = digits[{{ $i }}].replace(/\D/g, '').slice(0, 1); sync(); if (digits[{{ $i }}] && $event.target.nextElementSibling) $event.target.nextElementSibling.focus();"
                        @paste.prevent="paste($event.clipboardData.getData('text'))"
                        class="h-14 rounded-xl border border-gray-300 bg-transparent text-center text-xl font-semibold text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                        {{ $i === 0 ? 'autofocus' : '' }}
                    >
                @endfor
            </div>
            <x-input-error :messages="$errors->get('code')" class="mt-3" />
        </div>

        <div class="rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
            Como o usuário tem 2FA ativo, a sessão anterior só será invalidada depois desta confirmação.
        </div>

        <x-primary-button class="w-full justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm normal-case tracking-normal hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-700">
            Verificar minha conta
        </x-primary-button>
    </form>
</x-guest-layout>

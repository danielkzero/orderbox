<x-guest-layout
    title="Verifique seu e-mail"
    subtitle="Antes de continuar, confirme o endereco de e-mail cadastrado para liberar o acesso."
>
    @if (session('status') === 'verification-link-sent')
        <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            Um novo link de verificacao foi enviado para o e-mail cadastrado.
        </div>
    @endif

    <div class="space-y-5">
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm leading-6 text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
            Enviamos um link de verificacao para sua caixa de entrada. Caso nao tenha recebido, solicite um novo envio abaixo.
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm normal-case tracking-normal hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-700">
                Reenviar e-mail de verificacao
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Sair da conta
            </button>
        </form>
    </div>
</x-guest-layout>

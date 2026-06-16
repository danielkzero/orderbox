<x-guest-layout
    title="Recuperar senha"
    subtitle="Informe o e-mail vinculado a sua conta. Enviaremos um link para criar uma nova senha."
>
    <x-auth-session-status class="mb-5 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="seu@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm normal-case tracking-normal hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-700">
            Enviar link de redefinição
        </x-primary-button>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            Lembrou sua senha?
            <a href="{{ route('login') }}" class="font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Voltar para o login</a>
        </p>
    </form>
</x-guest-layout>

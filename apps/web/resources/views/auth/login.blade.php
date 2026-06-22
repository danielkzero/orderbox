
<x-guest-layout
    title="Entrar no Orderbox"
    subtitle="Informe seu e-mail e senha para acessar o painel administrativo."
>
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="danikzero@hotmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Senha" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400" href="{{ route('password.request') }}">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" placeholder="Digite sua senha" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-3">
                <input id="remember_me" type="checkbox" class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900" name="remember">
                <span class="text-sm text-gray-600 dark:text-gray-400">Manter conectado</span>
            </label>
        </div>

        <x-primary-button class="w-full justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm normal-case tracking-normal hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-700">
            Entrar
        </x-primary-button>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            O acesso é liberado pela administração da empresa. Solicite seu usuário ao gestor.
        </p>
    </form>
</x-guest-layout>

<x-guest-layout
    title="Confirmar senha"
    subtitle="Esta area exige uma confirmacao adicional antes de continuar."
>
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Senha atual" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" placeholder="Digite sua senha" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm normal-case tracking-normal hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-700">
            Confirmar acesso
        </x-primary-button>
    </form>
</x-guest-layout>

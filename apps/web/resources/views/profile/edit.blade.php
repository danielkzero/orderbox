<x-app-layout>
    <x-page-header title="Perfil" description="Atualize seus dados de acesso e senha." />

    <div class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-2">
            <x-panel class="p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-panel>

            <x-panel class="p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-panel>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-page-header :title="$user->exists ? 'Editar usuário' : 'Novo usuário'" description="Defina os dados de acesso e o perfil do usuário." />
    <x-panel class="max-w-3xl">
        <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="space-y-5 p-6">
            @csrf @if($user->exists) @method('PUT') @endif
            <div><x-input-label for="name" value="Nome" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $user->name)" required /></div>
            <div><x-input-label for="email" value="E-mail" /><x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required /></div>
            <div><x-input-label for="role" value="Perfil" /><select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">@foreach(['Admin','Manager','SalesRepresentative'] as $role)<option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>@endforeach</select></div>
            @if($user->exists)<label class="flex items-center gap-2"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked(old('active', $user->active))> Usuário ativo</label>@endif
            <div><x-input-label for="password" :value="$user->exists ? 'Nova senha (opcional)' : 'Senha'" /><x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="!$user->exists" /></div>
            <div><x-input-label for="password_confirmation" value="Confirmar senha" /><x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" /></div>
            <div class="flex gap-3"><button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white">Salvar</button><a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm">Cancelar</a></div>
        </form>
    </x-panel>
</x-app-layout>

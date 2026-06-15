<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Confirm the authentication code to replace the previous session on this channel.') }}
    </div>

    <form method="POST" action="{{ route('auth.2fa.store') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('Confirm') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>

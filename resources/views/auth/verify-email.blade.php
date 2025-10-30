<div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
    {{ __('Enviamos um código de 6 dígitos para o seu e-mail. Por favor, insira-o abaixo para verificar sua conta.') }}
</div>

<x-auth-session-status class="mb-4" :status="session('status')" />

<form method="POST" action="{{ route('verification.verify_code') }}">
    @csrf

    <div>
        <x-input-label for="code" :value="__('Código de Verificação')" />
        <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" />

        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    <div class="flex items-center justify-between mt-4">
        <x-primary-button>
            {{ __('Verificar Código') }}
        </x-primary-button>
    </div>
</form>

<hr class="my-6">

<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <div>
        <x-primary-button>
            {{ __('Reenviar Código de Verificaçãoo') }}
        </x-primary-button>
    </div>
</form>
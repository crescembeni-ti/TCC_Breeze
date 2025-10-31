<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificação de E-mail - Mapa de Árvores</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/auth.css')
</head>

<body class="font-sans antialiased bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="sm:max-w-md w-full bg-white shadow-lg rounded-lg p-8">
        <h1 class="text-2xl font-bold text-center text-green-700 mb-6">
            Verifique seu E-mail
        </h1>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Enviamos um código de 6 dígitos para o seu e-mail. Por favor, insira-o abaixo para verificar sua conta.') }}
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        {{-- Formulário principal: Verificar Código --}}
        <form method="POST" action="{{ route('verification.verify_code') }}" class="space-y-6">
            @csrf

            <div>
                <x-input-label for="code" :value="__('Código de Verificação')" />
                <x-text-input 
                    id="code" 
                    type="text" 
                    name="code" 
                    required 
                    autofocus 
                    maxlength="6"
                    class="block mt-1 w-full text-center tracking-widest text-lg"
                    placeholder="Ex: 123456"
                    autocomplete="one-time-code" 
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-center mt-6">
                <x-primary-button class="w-full justify-center">
                    {{ __('Verificar Código') }}
                </x-primary-button>
            </div>
        </form>

        <hr class="my-6 border-gray-300">

        {{-- Formulário secundário: Reenviar código --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div class="flex justify-center">
                <x-primary-button>
                    {{ __('Reenviar Código de Verificação') }}
                </x-primary-button>
            </div>
        </form>
    </div>

</body>
</html>

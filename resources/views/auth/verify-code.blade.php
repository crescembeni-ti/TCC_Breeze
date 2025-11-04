<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificação de E-mail - Mapa de Árvores</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- @vite('resources/css/auth.css') --}} {{-- Removido se já estiver no app.css --}}
</head>

<body class="font-sans antialiased bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="sm:max-w-md w-full bg-white shadow-lg rounded-lg p-8">
        <h1 class="text-2xl font-bold text-center text-green-700 mb-6">
            Verifique seu E-mail
        </h1>

        <!-- MUDANÇA 1: Mostra o e-mail para o qual o código foi enviado -->
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Enviamos um código de 6 dígitos para o seu e-mail:') }} 
            <strong>{{ request()->query('email', old('email')) }}</strong>.
            {{ __('Por favor, insira-o abaixo para verificar sua conta.') }}
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        {{-- Formulário principal: Verificar Código --}}
        <!-- MUDANÇA 2: Corrigido o nome da rota -->
        <form method="POST" action="{{ route('verification.code.verify') }}" class="space-y-6">
            @csrf

            <!-- MUDANÇA 3: Campo de e-mail oculto (CRÍTICO) -->
            <!-- Precisamos enviar o e-mail de volta para o controller saber quem verificar -->
            <input type="hidden" name="email" value="{{ request()->query('email', old('email')) }}">

            <div>
                <!-- MUDANÇA 4: 'for' e 'name' alterados de 'code' para 'verification_code' -->
                <x-input-label for="verification_code" :value="__('Código de Verificação')" />
                <x-text-input 
                    id="verification_code" 
                    type="text" 
                    name="verification_code" {{-- Nome corrigido --}}
                    required 
                    autofocus 
                    maxlength="6"
                    class="block mt-1 w-full text-center tracking-widest text-lg"
                    placeholder="Ex: 123456"
                    autocomplete="one-time-code"
                    inputmode="numeric" {{-- Melhora UX em celulares --}}
                />
                <!-- MUDANÇA 5: Corrigido o nome do erro -->
                <x-input-error :messages="$errors->get('verification_code')" class="mt-2" />
                <!-- MUDANÇA 6: Adicionado erro de e-mail (caso o usuário mexa na URL) -->
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-center mt-6">
                <x-primary-button class="w-full justify-center">
                    {{ __('Verificar Código') }}
                </x-primary-button>
            </div>
        </form>

        <hr class="my-6 border-gray-300">

        {{-- Formulário secundário: Reenviar código --}}
        <!-- MUDANÇA 7: Corrigido o nome da rota de reenvio -->
        <form method="POST" action="{{ route('verification.code.resend') }}">
            @csrf
            
            <!-- MUDANÇA 8: Campo de e-mail oculto (CRÍTICO) -->
            <input type="hidden" name="email" value="{{ request()->query('email', old('email')) }}">

            <div class="flex justify-center flex-col items-center">
                 <p class="text-sm text-gray-600 mb-2">Não recebeu o código?</p>
                 <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Reenviar Código de Verificação') }}
                </button>
            </div>
        </form>
    </div>

</body>
</html>
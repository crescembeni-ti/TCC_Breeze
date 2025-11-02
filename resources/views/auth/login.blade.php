<x-guest-layout>
    <div class="text-center mb-6">
        <div class="flex items-center justify-center gap-8">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-24 w-24 object-contain">
            <h1 class="text-3xl font-bold">
            <span class="text-[#358054]">Bem-vindo</span><br>
            <span class="text-[#a0c520]">de volta</span>
            </h1>
        </div> 
        <p class="text-gray-600 text-sm">Entre para continuar ajudando a mapear o verde de Paracambi</p>
    </div>

    <div class="flex justify-end mb-4">
    <a href="{{ route('home') }}"
       class="text-sm text-green-700 hover:text-green-800 font-medium underline">
        ← Voltar ao início
    </a>
    </div>


    <!-- Mensagem de status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Senha -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Lembrar-me e Esqueci senha -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center text-sm text-gray-600">
                <input id="remember_me"
                       type="checkbox"
                       class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                       name="remember">
                <span class="ml-2">Lembrar-me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-green-700 hover:text-green-800 underline">
                    Esqueceu sua senha?
                </a>
            @endif
        </div>

        <!-- Botão -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center bg-green-600 hover:bg-green-700">
                Entrar
            </x-primary-button>
        </div>

        <!-- Link de registro -->
        <p class="text-center text-sm text-gray-600 mt-6">
            Ainda não tem conta?
            <a href="{{ route('register') }}" class="text-green-700 hover:text-green-800 underline font-medium">
                Crie uma agora
            </a>
        </p>
    </form>
</x-guest-layout>

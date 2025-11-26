<x-guest-layout>
    <div class="text-center mb-6">
        <div class="flex items-center justify-center gap-8">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-24 w-24 object-contain">
            <h1 class="text-3xl font-bold leading-tight">
                <span class="text-[#358054]">Bem-vindo</span><br>
                <span class="text-[#a0c520]">de volta</span>
            </h1>
        </div>
        <p class="text-gray-600 text-sm mt-2">Entre para continuar ajudando a mapear o verde de Paracambi</p>
    </div>

    <div class="flex justify-end mb-4">
        <a href="{{ route('home') }}" class="text-sm text-green-700 hover:text-green-800 font-medium underline">
            ← Voltar ao início
        </a>
    </div>

    <!-- Mensagem de status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
         <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative">
                <input type="email"
                id="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                class="block mt-1 w-full rounded-md border border-gray-300 bg-[#f9fafb] text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 pr-10" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
         </div>

        <!-- Senha com olhinho -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" :value="__('Senha')" />

            <div class="relative">
                <input :type="show ? 'text' : 'password'"
                       id="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       class="block mt-1 w-full rounded-md border border-gray-300 bg-[#f9fafb] text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 pr-10" />

                <!-- Botão olho -->
                <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-green-700"
                        tabindex="-1"
                        aria-label="Mostrar ou ocultar senha">
                    <!-- Ícone olho fechado -->
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5
                                 c4.477 0 8.268 2.943 9.542 7
                                 -1.274 4.057-5.065 7-9.542 7
                                 -4.477 0-8.268-2.943-9.542-7z" />
                    </svg>

                    <!-- Ícone olho aberto -->
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19
                                 c-4.477 0-8.268-2.943-9.542-7
                                 a9.964 9.964 0 012.53-4.568
                                 m3.18-2.302A9.956 9.956 0 0112 5
                                 c4.477 0 8.268 2.943 9.542 7
                                 a9.969 9.969 0 01-4.188 5.063
                                 M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3l18 18" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Lembrar-me e Esqueceu senha -->
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

        <!-- Registro -->
        <p class="text-center text-sm text-gray-600 mt-6">
            Ainda não tem conta?
            <a href="{{ route('register') }}" class="text-green-700 hover:text-green-800 underline font-medium">
                Crie uma agora
            </a>
        </p>
    </form>
</x-guest-layout>

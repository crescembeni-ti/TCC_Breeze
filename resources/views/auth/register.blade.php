<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Crie sua conta 🌱</h2>
        <p class="text-gray-600 text-sm">Junte-se a nós e ajude a preservar o verde de Paracambi</p>
    </div>

    <div class="flex justify-end mb-4">
        <a href="{{ route('home') }}" class="text-sm text-green-700 hover:text-green-800 font-medium underline">
            ← Voltar ao início
        </a>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nome -->
        <div>
            <x-input-label for="name" :value="__('Nome completo')" />
            <x-text-input id="name" class="block mt-1 w-full"
                type="text"
                name="name"
                :value="old('name')"
                required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Senha -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" :value="__('Senha')" />
            <div class="relative">
                <input :type="show ? 'text' : 'password'"
                       id="password"
                       name="password"
                       required
                       autocomplete="new-password"
                       class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" />
                <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-green-700">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.964 9.964 0 012.53-4.568m3.18-2.302A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-4.188 5.063M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Senha -->
        <div class="mt-4" x-data="{ showConfirm: false }">
            <x-input-label for="password_confirmation" :value="__('Confirmar senha')" />
            <div class="relative">
                <input :type="showConfirm ? 'text' : 'password'"
                       id="password_confirmation"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" />
                <button type="button"
                        @click="showConfirm = !showConfirm"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-green-700">
                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.964 9.964 0 012.53-4.568m3.18-2.302A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-4.188 5.063M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Botão -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center bg-green-600 hover:bg-green-700">
                Registrar
            </x-primary-button>
        </div>

        <!-- Link login -->
        <p class="text-center text-sm text-gray-600 mt-6">
            Já possui conta?
            <a href="{{ route('login') }}" class="text-green-700 hover:text-green-800 underline font-medium">
                Fazer login
            </a>
        </p>
    </form>
</x-guest-layout>

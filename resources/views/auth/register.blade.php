<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Crie sua conta 🌱</h2>
        <p class="text-gray-600 text-sm">Junte-se a nós e ajude a preservar o verde de Paracambi</p>
    </div>

    <div class="flex justify-end mb-4">
    <a href="{{ route('home') }}"
       class="text-sm text-green-700 hover:text-green-800 font-medium underline">
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
        <div class="mt-4">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar senha -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar senha')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Botão -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center bg-green-600 hover:bg-green-700">
                Registrar
            </x-primary-button>
        </div>

        <!-- Link para login -->
        <p class="text-center text-sm text-gray-600 mt-6">
            Já possui conta?
            <a href="{{ route('login') }}" class="text-green-700 hover:text-green-800 underline font-medium">
                Fazer login
            </a>
        </p>
    </form>
</x-guest-layout>

<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Esqueceu sua senha? Sem problemas. Basta nos informar seu endereço de e-mail e enviaremos um link de redefinição de senha que permitirá que você escolha um novo.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
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

        <div class="flex items-center justify-end mt-4">
            <button type="submit"
            class="
            bg-green-600 text-white font-semibold
            rounded-md shadow-md
            hover:bg-green-700 hover:shadow-lg
            active:bg-[#38c224]
            transition duration-200
            w-full
            px-4
            py-2          
            ">
            {{ __('Enviar link de redefinição de senha por e-mail') }}
            </button>
        </div>
    </form>
</x-guest-layout>

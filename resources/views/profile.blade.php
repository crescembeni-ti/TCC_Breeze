<x-app-layout>
    @vite([
        'resources/css/app.css',
        'resources/css/perfil.css',
        'resources/js/app.js'
    ])


    <div class="perfil-container max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-green-700 mb-6">Perfil do Usuário 🌿</h1>

        {{-- CARD PRINCIPAL --}}
        <div class="perfil-box">
            <p><strong>Nome:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>

            <p class="mt-4 text-sm text-gray-500">
                Membro desde: {{ Auth::user()->created_at->format('d/m/Y') }}
            </p>

            <div class="mt-8 flex justify-between items-center">
                <a href="{{ route('home') }}" class="link-back">
                    ← Voltar ao mapa
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">
                        Sair
                    </button>
                </form>
            </div>
        </div>

        {{-- CARD DE EXCLUSÃO DE CONTA --}}
        <div class="perfil-box">
            <h2 class="text-lg font-semibold text-red-600 mb-3">⚠️ Zona de Perigo</h2>
            <p class="text-gray-600 mb-4">
                Excluir sua conta é uma ação permanente. Todos os seus dados serão apagados.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <label for="password" class="block mb-2 text-gray-700">
                    Confirme sua senha para excluir a conta:
                </label>
                <input id="password" name="password" type="password" required>

                <div class="mt-4">
                    <button type="submit" class="btn-danger">
                        Excluir Conta
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

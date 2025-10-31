<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-6">
        <h1 class="text-3xl font-bold text-green-700 mb-6">Perfil do Usuário 🌿</h1>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <p><strong>Nome:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>

            <p class="mt-4 text-sm text-gray-500">
                Membro desde: {{ Auth::user()->created_at->format('d/m/Y') }}
            </p>

            <div class="mt-8 flex justify-between items-center">
                <a href="{{ route('home') }}"
                   class="text-sm text-green-700 hover:text-green-800 underline font-medium">
                    ← Voltar ao mapa
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cadastrar Nova Notícia') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('noticias.store') }}">
                        @csrf

                        <div>
                            <label for="titulo">Título</label>
                            <input id="titulo" class="block mt-1 w-full" type="text" name="titulo" required />
                        </div>

                        <div class="mt-4">
                            <label for="conteudo">Conteúdo</label>
                            <textarea id="conteudo" name="conteudo" class="block mt-1 w-full" rows="5"></textarea>
                        </div>

                        <div class="mt-4">
                            <label for="imagem_url">URL da Imagem (Opcional)</label>
                            <input id="imagem_url" class="block mt-1 w-full" type="text" name="imagem_url" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="ms-3">
                                Salvar Notícia
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

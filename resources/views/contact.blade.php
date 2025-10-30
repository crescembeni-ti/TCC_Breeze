<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contato - Mapa de Árvores</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Contato</h1>
                    <div class="flex gap-4">
                        <a href="{{ route('home') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Voltar ao Mapa</a>
                        <a href="{{ route('about') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Sobre</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Sucesso!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Informações de Contato</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Endereço</h3>
                            <p class="text-gray-600">
                                Prefeitura Municipal de Paracambi<br>
                                Centro, Paracambi - RJ<br>
                                CEP: 26600-000
                            </p>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Telefone</h3>
                            <p class="text-gray-600">(21) XXXX-XXXX</p>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">E-mail</h3>
                            <p class="text-gray-600">contato@paracambi.rj.gov.br</p>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Horário de Atendimento</h3>
                            <p class="text-gray-600">
                                Segunda a Sexta: 8h às 17h<br>
                                Sábados, Domingos e Feriados: Fechado
                            </p>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mt-6">
                            <p class="text-blue-800">
                                <strong>Importante:</strong> Este formulário é público e não requer login. Para denúncias que exigem acompanhamento, utilize o <a href="{{ route('contact') }}" class="underline hover:text-blue-900">formulário de denúncias</a> (requer login).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Formulário de Contato</h2>
                    
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf
                        

                        <div>
                            <label for="bairro" class="block text-sm font-semibold text-gray-700 mb-2">Bairro *</label>
                            <input 
                                type="text" 
                                id="bairro" 
                                name="bairro" 
                                required
                                maxlength="255"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('address') border-red-500 @enderror"
                                placeholder="Ex: Centro"
                                value="{{ old('bairro') }}"
                            >
                            @error('bairro')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                         <div>
                            <label for="rua" class="block text-sm font-semibold text-gray-700 mb-2">Rua *</label>
                            <input 
                                type="text" 
                                id="rua" 
                                name="rua" 
                                required
                                maxlength="255"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('address') border-red-500 @enderror"
                                placeholder="Ex: Rua das Flores"
                                value="{{ old('rua') }}"
                            >
                            @error('rua')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="numero" class="block text-sm font-semibold text-gray-700 mb-2">Número *</label>
                            <input 
                                type="text" 
                                id="numero" 
                                name="numero" 
                                required
                                maxlength="255"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('address') border-red-500 @enderror"
                                placeholder="Ex: 14"
                                value="{{ old('rua') }}"
                            >
                            @error('numero')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="descricao" class="block text-sm font-semibold text-gray-700 mb-2">Descrição do Ocorrido *</label>
                            <textarea 
                                id="descricao" 
                                name="descricao" 
                                rows="6"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('description') border-red-500 @enderror"
                                placeholder="Descreva detalhadamente o ocorrido..."
                            >{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                            <p class="text-yellow-800 text-sm">
                                <strong>Atenção:</strong> Todos os campos marcados com * são obrigatórios. Suas informações serão utilizadas apenas para responder ao seu contato.
                            </p>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-200 shadow-md hover:shadow-lg"
                        >
                            Enviar Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-600">© {{ date('Y') }} Mapa de Árvores de Paracambi-RJ. Desenvolvido com Laravel e Leaflet.</p>
            </div>
        </footer>
    </div>

</body>
</html>


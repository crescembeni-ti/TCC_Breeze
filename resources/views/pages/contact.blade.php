<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contato - Árvores de Paracambi</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/contact.css')
<link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <header class="site-header bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-12">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-24 w-24 object-contain">
                <h1 class="text-5xl font-bold">
                    <span class="text-[#358054]">Árvores de</span>
                    <span class="text-[#a0c520]"> Paracambi</span>
                </h1>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('home') }}" class="btn bg-green-700 text-white hover:bg-green-800 transition-colors">
                    Voltar ao Mapa
                </a>
                <a href="{{ route('contact.myrequests') }}" 
                class="btn bg-gray-700 text-white hover:bg-gray-800 transition-colors">
                Minhas Solicitações
            </a>
            </div>
        </div>
    </div>
</header>


        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
        <p class="font-bold">✅ Sucesso!</p>
        <p>{{ session('success') }}</p>
        <a href="{{ route('contact.myrequests') }}" 
            class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
            Ver Minhas Solicitações
        </a>
    </div>
            @endif


            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
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
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Formulário de Contato</h2>
                    
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        
                        <div>
                            <label for="topico" class="block text-sm font-semibold text-gray-700 mb-2">Solicitações Frequentes *</label>
                            <select 
                                id="topico" 
                                name="topico" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('topico') border-red-500 @enderror"
                            >
                                <option value="" disabled {{ old('topico') ? '' : 'selected' }}>Escolha um tópico...</option>
                                
                                @foreach ($topicos as $topico)
                                    <option 
                                        value="{{ $topico->nome }}" 
                                        {{ old('topico') == $topico->nome ? 'selected' : '' }}
                                    >
                                        {{ $topico->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('topico')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="bairro" class="block text-sm font-semibold text-gray-700 mb-2">Bairro *</label>
                            <select 
                                id="bairro" 
                                name="bairro" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('bairro') border-red-500 @enderror"
                            >
                                <option value="" disabled {{ old('bairro') ? '' : 'selected' }}>Escolha um bairro...</option>
                                
                                @foreach ($bairros as $bairro)
                                    <option 
                                        value="{{ $bairro->nome }}" 
                                        {{ old('bairro') == $bairro->nome ? 'selected' : '' }}
                                    >
                                        {{ $bairro->nome }}
                                    </option>
                                @endforeach
                            </select>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('rua') border-red-500 @enderror" placeholder="Ex: Rua das Flores"
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('numero') border-red-500 @enderror" placeholder="Ex: 14"
                                value="{{ old('numero') }}" >
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('descricao') border-red-500 @enderror" placeholder="Descreva detalhadamente o ocorrido..."
                            >{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="foto" class="block text-sm font-semibold text-gray-700 mb-2">Anexar Foto (Opcional)</label>
                            <input 
                                type="file" 
                                id="foto" 
                                name="foto"
                                accept="image/*" 
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none @error('foto') border-red-500 @enderror"
                            >
                            <p class="mt-1 text-sm text-gray-500" id="file_input_help">Permitido: JPG, PNG, JPEG (Máx: 2MB).</p>
                            @error('foto')
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

        <footer class="bg-white shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-600">© {{ date('Y') }} Mapa de Árvores de Paracambi-RJ.</p>
            </div>
        </footer>
    </div>
</body>
</html>
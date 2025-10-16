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
                    <h1 class="text-3xl font-bold text-gray-900">Contato e Denúncias</h1>
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
                                <strong>Importante:</strong> Para enviar uma denúncia através do formulário ao lado, você precisa estar logado no sistema. Se ainda não tem uma conta, <a href="{{ route('register') }}" class="underline hover:text-blue-900">cadastre-se aqui</a>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Report Form -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Formulário de Denúncia</h2>
                    
                    @auth
                        <form action="{{ route('report.store') }}" method="POST" class="space-y-6">
                            @csrf
                            
                            <div>
                                <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Assunto</label>
                                <input 
                                    type="text" 
                                    id="subject" 
                                    name="subject" 
                                    required
                                    maxlength="255"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('subject') border-red-500 @enderror"
                                    placeholder="Ex: Árvore danificada na Rua Principal"
                                    value="{{ old('subject') }}"
                                >
                                @error('subject')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Mensagem</label>
                                <textarea 
                                    id="message" 
                                    name="message" 
                                    rows="8"
                                    required
                                    maxlength="5000"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('message') border-red-500 @enderror"
                                    placeholder="Descreva detalhadamente a sua denúncia, incluindo localização e outros detalhes relevantes..."
                                >{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                                <p class="text-yellow-800 text-sm">
                                    <strong>Atenção:</strong> Sua denúncia será registrada com seu nome de usuário ({{ auth()->user()->name }}) para que possamos entrar em contato caso necessário.
                                </p>
                            </div>

                            <button 
                                type="submit" 
                                class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-200 shadow-md hover:shadow-lg"
                            >
                                Enviar Denúncia
                            </button>
                        </form>
                    @else
                        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Login Necessário</h3>
                            <p class="text-gray-600 mb-6">
                                Para enviar uma denúncia, você precisa estar logado no sistema. Isso nos ajuda a manter um registro de todas as denúncias e entrar em contato caso necessário.
                            </p>
                            <div class="flex gap-4 justify-center">
                                <a href="{{ route('login') }}" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                                    Fazer Login
                                </a>
                                <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition">
                                    Criar Conta
                                </a>
                            </div>
                        </div>
                    @endauth
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


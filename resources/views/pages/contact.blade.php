@extends('layouts.dashboard')

@section('content')
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Fazer Solicitação - Árvores de Paracambi</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite('resources/css/contact.css')
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    </head>

    <body class="font-sans antialiased">
        <div class="min-h-screen">

            <!-- MAIN -->
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                        <p class="font-bold">Sucesso!</p>
                        <p>{{ session('success') }}</p>
                        <a href="{{ route('contact.myrequests') }}"
                            class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                            Ver Minhas Solicitações
                        </a>
                    </div>
                @endif

                <!-- GRID PRINCIPAL -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mr-16">
                        <div class="p-8">
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
                                    <p class="text-gray-600">(21) 2683-1897</p>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">E-mail</h3>
                                    <p class="text-gray-600">meioambiente@paracambi.rj.gov.br</p>
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

                        <!-- FOTO DENTRO DO CARD COM LEGENDA -->
                        <div class="px-6 pb-6">
                            <img src="{{ asset('images/secretaria.jpg') }}"
                                alt="Secretaria de Meio Ambiente e Clima de Paracambi"
                                class="w-full max-h-[360px] object-cover rounded-xl border border-gray-300 shadow-sm">
                            <p class="text-center text-sm text-gray-600 mt-2 italic">
                                Secretaria Municipal de Meio Ambiente e Clima
                            </p>
                        </div>
                    </div>




                    <!-- COLUNA DIREITA (FORMULÁRIO) -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Formulário de Contato</h2>

                        <form action="{{ route('contact.store') }}" method="POST" class="space-y-6"
                            enctype="multipart/form-data">
                            @csrf

                            <div x-data="{ open: false, selected: '{{ old('topico') ?? '' }}' }" class="relative w-full">
                                <label for="topico" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Solicitações Frequentes *
                                </label>

                                <!-- Botão principal -->
                                <button @click="open = !open" type="button"
                                    class="relative w-full px-4 py-2 border border-gray-300 rounded-lg text-left bg-white
                 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <span x-text="selected || 'Escolha um tópico...'"></span>

                                    <!-- Setinha -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Lista de opções -->
                                <ul x-show="open" @click.outside="open=false"
                                    class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg max-h-60 overflow-auto z-10 shadow-md">
                                    @foreach ($topicos as $topico)
                                        <li @click="selected='{{ $topico->nome }}'; open=false"
                                            class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white"
                                            :class="selected === '{{ $topico->nome }}' ? 'bg-[#358054] text-white' : ''">
                                            {{ $topico->nome }}
                                        </li>
                                    @endforeach
                                </ul>

                                <!-- Input escondido -->
                                <input type="hidden" name="topico" :value="selected" required>

                                @error('topico')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>


                            <div x-data="{ open: false, selected: '{{ old('bairro') ?? '' }}' }" class="relative w-full">
                                <label for="bairro" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Bairro *
                                </label>

                                <!-- Botão principal -->
                                <button @click="open = !open" type="button"
                                    class="relative w-full px-4 py-2 border border-gray-300 rounded-lg text-left bg-white
                             focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <span x-text="selected || 'Escolha um bairro...'"></span>

                                    <!-- Setinha -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Lista de opções -->
                                <ul x-show="open" @click.outside="open=false"
                                    class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg max-h-60 overflow-auto z-10 shadow-md">
                                    @foreach ($bairros as $bairro)
                                        <li @click="selected='{{ $bairro->nome }}'; open=false"
                                            class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white"
                                            :class="selected === '{{ $bairro->nome }}' ? 'bg-[#358054] text-white' : ''">
                                            {{ $bairro->nome }}
                                        </li>
                                    @endforeach
                                </ul>

                                <!-- Input escondido -->
                                <input type="hidden" name="bairro" :value="selected" required>
                                @error('bairro')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="rua" class="block text-sm font-semibold text-gray-700 mb-2">Rua *</label>
                                <input type="text" id="rua" name="rua" required maxlength="255"
                                    placeholder="Ex: Rua das Flores" value="{{ old('rua') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('rua') border-red-500 @enderror">
                                @error('rua')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="numero" class="block text-sm font-semibold text-gray-700 mb-2">Número
                                    *</label>
                                <input type="text" id="numero" name="numero" required maxlength="255"
                                    placeholder="Ex: 14" value="{{ old('numero') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('numero') border-red-500 @enderror">
                                @error('numero')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="descricao" class="block text-sm font-semibold text-gray-700 mb-2">Descrição do
                                    Ocorrido *</label>
                                <textarea id="descricao" name="descricao" rows="6" required
                                    placeholder="Descreva detalhadamente o ocorrido..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('descricao') border-red-500 @enderror">{{ old('descricao') }}</textarea>
                                @error('descricao')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="foto" class="block text-sm font-semibold text-gray-700 mb-2">Anexar Foto
                                    (Opcional)</label>
                                <input type="file" id="foto" name="foto" accept="image/*"
                                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none @error('foto') border-red-500 @enderror">
                                <p class="mt-1 text-sm text-gray-500">Permitido: JPG, PNG, JPEG (Máx: 2MB).</p>
                                @error('foto')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                                <p class="text-yellow-800 text-sm">
                                    <strong>Atenção:</strong> Todos os campos marcados com * são obrigatórios. Suas
                                    informações serão utilizadas apenas para responder ao seu contato.
                                </p>
                            </div>

                            <!-- Botão -->
                            <div class="mt-6">
                                <button type="submit"
                                 class=" bg-green-600 text-white font-semibold
                                 rounded-md shadow-md
                                 hover:bg-green-700 hover:shadow-lg
                                 active:bg-[#38c224]
                                 transition duration-200
                                 w-full
                                 px-4
                                 py-2          
                                 ">
                                 Enviar Mensagem 
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>

        </div>
    </body>

    </html>
@endsection

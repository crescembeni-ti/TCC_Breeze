<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @vite('resources/css/about.css')

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">

        <header class="site-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">

                <div class="flex items-center gap-4 flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-4">
                        <img src="{{ asset('images/Brasao_Verde.png') }}" alt="Logo Brasão de Paracambi"
                            class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi"
                            class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <h1 class="text-3xl sm:text-4xl font-bold">
                            <span class="text-[#358054]">Sobre o</span>
                            <span class="text-[#a0c520]">Projeto</span>
                        </h1>
                    </a>
                </div>

                <div class="flex gap-4">

                    {{-- 1. VERIFICA SE É ADMIN (E MOSTRA BOTÃO DE EDIÇÃO) --}}
                    @if (auth('admin')->check())
                        <a href="{{ route('admin.about.edit') }}" class="btn bg-red-600 text-white hover:bg-red-700 font-semibold">
                            EDITAR ESTA PÁGINA ✏️
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn bg-white text-green-700 hover:bg-gray-100">
                            Voltar ao Painel
                        </a>

                    {{-- 2. VERIFICA SE É USUÁRIO COMUM (Usando o guard padrão) --}}
                    @elseif (auth()->check())
                        <a href="{{ route('dashboard') }}" class="btn bg-white text-green-700 hover:bg-gray-100">
                            Voltar ao Menu
                        </a>

                    {{-- 3. VISITANTE (Nem admin, nem usuário) --}}
                    @else
                        <a href="{{ route('home') }}" class="btn bg-white text-green-700 hover:bg-gray-100">
                            Voltar ao Mapa
                        </a>
                    @endif

                </div>

            </div>
        </header>


        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-lg p-8 info-column">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">{{ $pageContent->title }}</h2>

                <div class="prose max-w-none">
                    
                    {!! $pageContent->content !!}
                    
                    @if(!empty($pageContent->mission_content))
                        <h3>Nossa Missão</h3>
                        {!! $pageContent->mission_content !!}
                    @endif

                    @if(!empty($pageContent->how_it_works_content))
                        <h3>Como Funciona</h3>
                        {!! $pageContent->how_it_works_content !!}
                    @endif

                    @if(!empty($pageContent->benefits_content))
                        <h3>Benefícios das Árvores Urbanas</h3>
                        {!! $pageContent->benefits_content !!}
                    @endif

                    <div class="bg-green-50 border-l-4 border-green-500 p-6 mt-8">
                        <p class="text-green-800 font-semibold">
                            Para mais informações ou para reportar problemas, entre em contato conosco através da <a
                                href="{{ route('contact') }}" class="underline hover:text-[#38c224]">página de
                                solicitações</a>.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-gray-800 shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
            </div>
        </footer>
    </div>
</body>

</html>
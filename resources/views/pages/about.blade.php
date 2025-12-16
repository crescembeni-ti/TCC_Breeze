<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageContent->title ?? 'Sobre o Projeto' }} - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/about.css')
    
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-800">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-auto group-hover:scale-105 transition">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold leading-tight">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]">Paracambi</span>
                    </h1>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @if (auth('admin')->check())
                    <a href="{{ route('admin.about.edit') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Editar Página
                    </a>
                    
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-green-700 font-medium text-sm">
                        Painel Admin
                    </a>
                @else
                    <a href="{{ route('home') }}" class="text-[#358054] hover:text-[#2a6642] font-semibold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Voltar ao Mapa
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-[#358054] to-[#4caf50] p-8 sm:p-12 text-center text-white">
                <h1 class="text-4xl sm:text-5xl font-extrabold mb-4 tracking-tight text-white">
                    {{ $pageContent->title ?? 'Sobre o Projeto' }}
                </h1>
                <p class="text-green-100 text-lg max-w-2xl mx-auto">
                    Conheça a iniciativa que está transformando a gestão ambiental da nossa cidade.
                </p>
            </div>

            <div class="p-8 sm:p-12 space-y-12">
                
                @if(!empty($pageContent->content))
                <section class="prose prose-lg max-w-none text-gray-600">
                    {!! $pageContent->content !!}
                </section>
                @endif

                <hr class="border-gray-100">

                @if(!empty($pageContent->mission_content))
                <section class="bg-green-50 rounded-xl p-8 border border-green-100">
                    <h2 class="text-2xl font-bold text-[#358054] mb-4 flex items-center gap-2">
                        🎯 Nossa Missão
                    </h2>
                    <div class="prose prose-green max-w-none">
                        {!! $pageContent->mission_content !!}
                    </div>
                </section>
                @endif

                @if(!empty($pageContent->how_it_works_content))
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        ⚙️ Como Funciona
                    </h2>
                    <div class="prose max-w-none text-gray-600">
                        {!! $pageContent->how_it_works_content !!}
                    </div>
                </section>
                @endif

                @if(!empty($pageContent->benefits_content))
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        🌳 Benefícios das Árvores
                    </h2>
                    <div class="prose max-w-none text-gray-600">
                        {!! $pageContent->benefits_content !!}
                    </div>
                </section>
                @endif

            </div>
            
            <div class="bg-gray-50 p-6 text-center border-t border-gray-100">
                <p class="text-gray-500 text-sm">
                    © {{ date('Y') }} Árvores de Paracambi. Todos os direitos reservados.
                </p>
            </div>
        </div>

    </main>

</body>
</html>
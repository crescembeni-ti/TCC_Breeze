<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageContent->title ?? 'Sobre o Projeto' }} - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    
    <style>
        /* Imagem de fundo fixa */
        body {
            background-image: url('/images/arvore.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }
        .prose p { margin-bottom: 1rem; line-height: 1.6; color: #4b5563; }
        .prose ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose strong { color: #358054; font-weight: 700; }
        .prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 10px 0; }
    </style>
</head>

<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">

    {{-- HEADER VERDE (MANTIDO ORIGINAL) --}}
    <header class="site-header bg-[#beffb4] border-b-2 border-[#358054] shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center flex-wrap gap-4">
            
            {{-- LADO ESQUERDO: TÍTULO --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 sm:h-14 sm:w-14 object-contain transition-transform group-hover:scale-105">
                    <h1 class="text-xl sm:text-2xl font-bold leading-tight">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]">Paracambi</span>
                    </h1>
                </a>
            </div>

            {{-- LADO DIREITO: BOTÕES --}}
            <div class="flex items-center gap-3 sm:gap-4 flex-wrap justify-end">
                <img src="{{ asset('images/nova_logo.png') }}" alt="Logo Prefeitura" class="header-logo-right hover:opacity-90 transition-opacity hidden sm:block" style="height: 3.5rem; width: auto;">
                
                {{-- 1. BOTÃO VOLTAR AO MAPA --}}
                <a href="{{ route('home') }}" class="btn bg-[#358054] text-white hover:bg-[#2d6e4b] px-4 py-2 rounded-lg font-bold shadow-sm transition text-sm sm:text-base flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar ao Mapa
                </a>
                
                {{-- 2. ÁREA ADMINISTRATIVA --}}
                @if(auth('admin')->check())
                    <div class="flex gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn bg-[#358054] text-white hover:bg-[#2d6e4b] px-4 py-2 rounded-lg font-bold shadow-sm transition text-sm sm:text-base flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="hidden sm:inline">Painel</span>
                        </a>
                        <a href="{{ route('admin.about.edit') }}" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-bold shadow hover:bg-blue-700 transition flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span class="hidden sm:inline">Editar</span>
                        </a>
                    </div>
                @elseif(auth()->check())
                    <div class="flex gap-2">
                        <a href="{{ route('dashboard') }}" class="btn bg-[#358054] text-white hover:bg-[#2d6e4b] px-4 py-2 rounded-lg font-bold shadow-sm transition text-sm sm:text-base flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="hidden sm:inline">Painel</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">
        
        {{-- CARD PRINCIPAL (Título Creme + Sombra) --}}
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden mb-10 border border-gray-100">
            <div class="bg-gradient-to-r from-[#358054] to-[#4caf50] p-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#fefce8] drop-shadow-md">
                    {{ $pageContent->title ?? 'Sobre o Projeto' }}
                </h1>
            </div>
            
            @if(!empty($pageContent->content))
            <div class="p-8 sm:p-10 text-lg leading-relaxed text-gray-700">
                <div class="prose max-w-none">
                    {!! $pageContent->content !!}
                </div>
            </div>
            @endif
        </div>

        {{-- SEÇÕES DINÂMICAS (SEM ÍCONE DE VISTO) --}}
        <div class="grid grid-cols-1 gap-8">
            @if(!empty($pageContent->sections) && is_array($pageContent->sections))
                @foreach($pageContent->sections as $section)
                    <section class="bg-white/95 backdrop-blur-sm rounded-xl shadow-md border-l-4 border-[#358054] overflow-hidden transition-all hover:shadow-lg">
                        <div class="p-6 sm:p-8">
                            {{-- ALTERAÇÃO AQUI: Removido SVG e classes de flex --}}
                            <h2 class="text-2xl font-bold text-[#358054] mb-4 border-b border-gray-100 pb-3">
                                {{ $section['title'] }}
                            </h2>
                            <div class="prose max-w-none text-gray-600">
                                {!! $section['content'] !!}
                            </div>
                        </div>
                    </section>
                @endforeach
            @else
                <div class="text-center py-12 px-6 bg-white/90 rounded-lg border border-dashed border-gray-300">
                    <h3 class="text-lg font-medium text-gray-900">Carregando conteúdo...</h3>
                    <p class="text-gray-500 mt-1">Atualize a página para restaurar as seções padrão.</p>
                </div>
            @endif
        </div>

    </main>

    <footer class="bg-gray-800 text-white shadow mt-auto py-4 text-center">
        © {{ date('Y') }} Árvores de Paracambi.
    </footer>

</body>
</html>
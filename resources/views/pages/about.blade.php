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
        /* Imagem de fundo fixa (Parallax) */
        body {
            background-image: url('/images/arvore.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }
        /* Ajustes de tipografia */
        .prose p { margin-bottom: 1rem; line-height: 1.6; color: #4b5563; }
        .prose ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose strong { color: #358054; font-weight: 700; }
        .prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 10px 0; }
    </style>
</head>

<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">

    {{-- HEADER --}}
    <header class="site-header bg-[#beffb4] border-b-2 border-[#358054] shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center flex-wrap gap-4">
            
            {{-- LADO ESQUERDO --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 sm:h-14 sm:w-14 object-contain transition-transform group-hover:scale-105">
                    <h1 class="text-xl sm:text-2xl font-bold leading-tight">
                        {{-- CORRIGIDO: Cores originais da marca para melhor contraste --}}
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]">Paracambi</span>
                    </h1>
                </a>
            </div>

            {{-- LADO DIREITO --}}
            <div class="flex items-center gap-3 sm:gap-6">
                <img src="{{ asset('images/nova_logo.png') }}" alt="Logo Prefeitura" class="header-logo-right hover:opacity-90 transition-opacity" style="height: 3.5rem; width: auto;">
                
                <a href="{{ route('home') }}" class="btn bg-[#358054] text-white hover:bg-[#2d6e4b] px-4 py-2 rounded-lg font-bold shadow-sm transition">
                    Voltar ao Mapa
                </a>
                
                @if(auth('admin')->check())
                    <a href="{{ route('admin.about.edit') }}" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-bold shadow hover:bg-blue-700 transition flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Editar
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">
        
        {{-- BLOCO 1: Título e Introdução --}}
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden mb-10 border border-gray-100">
            <div class="bg-gradient-to-r from-[#358054] to-[#4caf50] p-8 text-center text-white">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
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

        {{-- BLOCO 2: Seções Dinâmicas (Cards) --}}
        <div class="grid grid-cols-1 gap-8">
            @if(!empty($pageContent->sections) && is_array($pageContent->sections))
                @foreach($pageContent->sections as $section)
                    <section class="bg-white/95 backdrop-blur-sm rounded-xl shadow-md border-l-4 border-[#358054] overflow-hidden transition-all hover:shadow-lg">
                        <div class="p-6 sm:p-8">
                            {{-- Título da Seção --}}
                            <h2 class="text-2xl font-bold text-[#358054] mb-4 flex items-center gap-3 border-b border-gray-100 pb-3">
                                {{-- Ícone opcional, se quiser remover basta apagar o SVG --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $section['title'] }}
                            </h2>

                            {{-- Conteúdo da Seção --}}
                            <div class="prose max-w-none text-gray-600">
                                {!! $section['content'] !!}
                            </div>
                        </div>
                    </section>
                @endforeach
            @else
                {{-- Fallback: Se ainda não carregou, recarregue a página --}}
                <div class="text-center py-12 px-6 bg-white/90 rounded-lg border border-dashed border-gray-300">
                    <h3 class="text-lg font-medium text-gray-900">Carregando conteúdo...</h3>
                    <p class="text-gray-500 mt-1">Se os textos padrão não aparecerem, atualize a página.</p>
                </div>
            @endif
        </div>

    </main>

    <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            © {{ date('Y') }} Árvores de Paracambi. Todos os direitos reservados.
        </div>
    </footer>

</body>
</html>
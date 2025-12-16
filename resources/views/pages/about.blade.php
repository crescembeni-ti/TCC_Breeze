<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageContent->title ?? 'Sobre o Projeto' }} - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
@vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/about.css') <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>
    
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-800">

    {{-- INÍCIO DO NOVO HEADER --}}
    <header class="site-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-4 flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-4">
                    {{-- Certifique-se que a imagem Brasao_Verde.png existe na pasta public/images --}}
                    <img src="{{ asset('images/Brasao_Verde.png') }} " alt="Logo Brasão de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <h1 class="text-3xl sm:text-4xl font-bold">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]"> Paracambi</span>
                    </h1>
                </a>
            </div>

            {{-- ========================================================= --}}
            {{-- MENU SUPERIOR --}}
            {{-- ========================================================= --}}
            <div class="flex items-center gap-3 sm:gap-4 relative" x-data="{ open: false }">

                {{-- ADMIN LOGADO --}}
                @if (auth('admin')->check())
                    <a href="{{ route('admin.about.edit') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span class="hidden md:inline">Editar</span>
                    </a>
                    {{-- Botão específico de edição para Admin nesta página --}}
                    <a href="{{ route('admin.dashboard') }}"
                        class="btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg hidden sm:block">
                        Painel Administrativo
                    </a>


                    {{-- USUÁRIO LOGADO --}}
                @elseif(auth()->check())
                    <div class="relative group flex items-center">
                        <a href="{{ route('dashboard') }}"
                            class="btn bg-green-600 hover:bg-green-700 text-white hidden sm:block px-6 py-3 text-lg rounded-lg">
                            Menu
                        </a>

                        <div
                            class="absolute bottom-[-55px] left-1/2 transform -translate-x-1/2
                    bg-gradient-to-r from-[#358054] to-[#a0c520] text-white text-xs font-semibold
                    py-1.5 px-3 rounded-lg shadow-xl opacity-0 group-hover:opacity-100
                    pointer-events-none transition-all duration-200 whitespace-nowrap">
                            Acesse seu painel e opções da conta
                            <span class="absolute top-[-6px] left-1/2 transform -translate-x-1/2 w-0 h-0
                        border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent
                        border-b-[6px] border-b-[#358054]"></span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                    </form>

                    {{-- VISITANTE (NÃO LOGADO) --}}
                @else
                    <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg hidden sm:block">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg hidden sm:block">
                        Cadastrar
                    </a>

                    {{-- MENU HAMBÚRGUER (MOBILE) --}}
                    <div class="relative inline-block">
                        <button id="guestMenuBtn"
                            class="ml-3 btn bg-[#358054] text-white hover:bg-[#2d6e4b] px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200">
                            Menu
                            <svg id="iconMenu" class="w-6 h-6 transition-all duration-200" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h16" />
                                <path d="M4 12h16" />
                                <path d="M4 18h16" />
                            </svg>
                        </button>

                        <div id="guestMenu" class="hidden absolute right-0 mt-2 w-56 bg-[#e8ffe6] rounded-xl shadow-lg z-50 overflow-hidden border border-green-100">

                            <a href="{{ route('contact') }}"
                            class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">
                                Fazer Solicitação
                            </a>

                            <a href="{{ route('contact.myrequests') }}"
                            class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">
                                Minhas Solicitações
                            </a>

                            <a href="{{ route('about') }}"
                            class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">
                                Sobre o Site
                            </a>

                        </div>
                    </div>

                    <script>
                        (function() {
                            const btn = document.getElementById('guestMenuBtn');
                            const menu = document.getElementById('guestMenu');
                            const icon = document.getElementById('iconMenu');
                            let aberto = false;

                            if (!btn || !menu) return;

                            btn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                menu.classList.toggle('hidden');
                                aberto = !aberto;

                                if (aberto) {
                                    icon.innerHTML = `<path d="M6 6l12 12" /><path d="M6 18L18 6" />`;
                                } else {
                                    icon.innerHTML = `<path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />`;
                                }
                            });

                            window.addEventListener('click', () => {
                                if (!menu.classList.contains('hidden')) {
                                    menu.classList.add('hidden');
                                    icon.innerHTML = `<path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />`;
                                    aberto = false;
                                }
                            });
                        })();
                    </script>
                @endif
            </div>
        </div>
    </header>
    {{-- FIM DO NOVO HEADER --}}

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
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Painel') - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/dashboard.css',
        'resources/css/perfil.css'
    ])

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="font-sans antialiased flex flex-col min-h-screen">

    {{-- HEADER ATUALIZADO --}}
    <header class="site-header bg-[#beffb4] border-b-2 border-[#358054] shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center flex-wrap gap-4">
            
            {{-- LADO ESQUERDO: Logo Site + Texto --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 sm:h-14 sm:w-14 object-contain">
                    <h1 class="text-xl sm:text-2xl font-bold leading-tight">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]">Paracambi</span>
                    </h1>
                </a>
            </div>

            {{-- LADO DIREITO: Botão Menu + Nova Logo (Ordem Invertida) --}}
            <div class="flex items-center gap-3 sm:gap-6">
                
                {{-- 1. Botão Menu Mobile (Agora vem antes) --}}
                <button @click="open = !open"
                    class="md:hidden bg-[#358054] text-white px-3 py-1.5 rounded-lg shadow font-medium text-sm flex items-center gap-2 hover:bg-[#2d6e4b] transition">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                {{-- 2. Nova Logo (Agora fica na extrema direita) --}}
                <img src="{{ asset('images/nova_logo.png') }}" 
                     alt="Logo Prefeitura" 
                     class="header-logo-right hover:opacity-90 transition-opacity"
                     style="height: 3.5rem; width: auto;">
            </div>

        </div>
    </header>

    <div x-data="{ open: false }" class="flex flex-1">

        <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
            class="sidebar bg-[#358054] text-white flex flex-col py-8 px-4 transform transition-transform duration-300 md:translate-x-0 rounded-br-2xl md:rounded-none flex-shrink-0">

            <nav class="space-y-4">
                {{-- ==================== MENU ADMIN ==================== --}}
                @if (auth('admin')->check())
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link"><i data-lucide="layout-dashboard" class="icon"></i> Painel Admin</a>
                    <a href="{{ route('admin.map') }}" class="sidebar-link"><i data-lucide="map-pin" class="icon"></i> Cadastrar Árvores</a>
                    <a href="{{ route('admin.trees.index') }}" class="sidebar-link"><i data-lucide="edit-3" class="icon"></i> Editar Árvores</a>

                    {{-- Link de Aprovações --}}
                    <a href="{{ route('admin.trees.pending') }}" class="sidebar-link relative flex items-center justify-between pr-4">
                        <div class="flex items-center gap-3">
                            <i data-lucide="check-circle" class="icon"></i> 
                            <span>Aprovações</span>
                        </div>
                        @php
                            $pendingCount = \App\Models\Tree::where('aprovado', false)->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('admin.contato.index') }}" class="sidebar-link"><i data-lucide="inbox" class="icon"></i> Solicitações</a>
                    <a href="{{ route('admin.os.index') }}" class="sidebar-link"><i data-lucide="file-text" class="icon"></i> Ordens de Serviço</a>
                    <a href="{{ route('admin.profile.edit') }}" class="sidebar-link"><i data-lucide="user" class="icon"></i> Meu Perfil</a>
                    <a href="{{ route('admin.accounts.index') }}" class="sidebar-link"><i data-lucide="users" class="icon"></i> Gerenciar Contas</a>
                    <a href="{{ route('about') }}" class="sidebar-link"><i data-lucide="info" class="icon"></i> Sobre o Site</a>

                {{-- ==================== MENU ANALISTA ==================== --}}
                @elseif (auth('analyst')->check())
                    <a href="{{ route('analyst.dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i> Painel Analista
                    </a>
                    <a href="{{ route('analyst.map') }}" class="sidebar-link"><i data-lucide="map-pin" class="icon"></i> Cadastrar Árvores</a>
                    <a href="{{ route('analyst.vistorias.pendentes') }}" class="sidebar-link">
                        <i data-lucide="clipboard-check" class="icon"></i> Vistorias Pendentes
                    </a>
                    <a href="{{ url('/pbi-analista/ordens-enviadas') }}" class="sidebar-link">
                        <i data-lucide="file-text" class="icon"></i> OS Enviadas
                    </a>

               {{-- ==================== MENU SERVIÇO ==================== --}}
                @elseif (auth('service')->check())
                    <a href="{{ route('service.dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i> Painel Serviço
                    </a>

                    <a href="{{ route('service.tasks.recebidas') }}" class="sidebar-link">
                        <i data-lucide="inbox" class="icon"></i> Tarefas Recebidas
                    </a>
                    <a href="{{ route('service.tasks.em_andamento') }}" class="sidebar-link">
                        <i data-lucide="play-circle" class="icon"></i> Tarefas Em Andamento
                    </a>
                    <a href="{{ route('service.tasks.concluidas') }}" class="sidebar-link">
                        <i data-lucide="check-circle" class="icon"></i> Tarefas Concluídas
                    </a>

                {{-- ==================== MENU USUÁRIO ==================== --}}
                @elseif (auth('web')->check())
                    <a href="{{ route('dashboard') }}" class="sidebar-link"><i data-lucide="layout-dashboard" class="icon"></i> Menu</a>
                    <a href="{{ route('contact') }}" class="sidebar-link"><i data-lucide="send" class="icon"></i> Nova Solicitação</a>
                    <a href="{{ route('contact.myrequests') }}" class="sidebar-link"><i data-lucide="clipboard-list" class="icon"></i> Minhas Solicitações</a>
                    <a href="{{ route('profile.edit') }}" class="sidebar-link"><i data-lucide="user" class="icon"></i> Meu Perfil</a>
                    <a href="{{ route('about') }}" class="sidebar-link"><i data-lucide="info" class="icon"></i> Sobre o Site</a>
                @endif
            </nav>

            <hr class="border-t-2 border-green-400 my-6 opacity-80">

            @if(auth('admin')->check() || auth('web')->check())
                <a href="{{ route('home') }}" class="sidebar-link">
                    <i data-lucide="arrow-left-circle" class="icon"></i> Voltar ao Mapa
                </a>
            @endif

            {{-- LOGOUT MULTI-GUARD --}}
            @if (auth('admin')->check())
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                    @csrf
                    <a href="#" class="sidebar-link logout-btn">
                        <i data-lucide="log-out" class="icon"></i> Sair
                    </a>
                </form>

            @elseif (auth('analyst')->check())
                <form method="POST" action="{{ route('analyst.logout') }}" class="mt-2">
                    @csrf
                    <a href="#" class="sidebar-link logout-btn">
                        <i data-lucide="log-out" class="icon"></i> Sair
                    </a>
                </form>

            @elseif (auth('service')->check())
                <form method="POST" action="{{ route('service.logout') }}" class="mt-2">
                    @csrf
                    <a href="#" class="sidebar-link logout-btn">
                        <i data-lucide="log-out" class="icon"></i> Sair
                    </a>
                </form>

            @elseif (auth('web')->check())
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <a href="#" class="sidebar-link logout-btn">
                        <i data-lucide="log-out" class="icon"></i> Sair
                    </a>
                </form>
            @endif

        </aside>

        <main class="flex-1 p-10 overflow-y-auto">
            @yield('content')
        </main>

    </div>

    <footer class="bg-gray-800 text-white shadow mt-auto py-4 text-center">
        © {{ date('Y') }} Árvores de Paracambi.
    </footer>

    <script>lucide.createIcons();</script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".logout-btn").forEach(btn => {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: "Deseja realmente sair?",
                        text: "Você precisará fazer login novamente.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#358054",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Sim, sair",
                        cancelButtonText: "Cancelar",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.closest("form").submit();
                        }
                    });
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
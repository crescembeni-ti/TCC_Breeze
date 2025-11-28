<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Painel') - Árvores de Paracambi</title>

    <!-- FONTES -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- CSS & JS -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/dashboard.css',
        'resources/css/perfil.css'
    ])

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen flex-container">

    <!-- ======================== HEADER ========================== -->
    <header class="site-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">

            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-4">
                    <img src="{{ asset('images/Brasao_Verde.png') }}"
                         class="h-16 w-16 sm:h-20 sm:w-20 object-contain">

                    <img src="{{ asset('images/logo.png') }}"
                         class="h-16 w-16 sm:h-20 sm:w-20 object-contain">

                    <h1 class="text-3xl sm:text-4xl font-bold">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]">Paracambi</span>
                    </h1>
                </a>
            </div>

            <!-- BOTÃO DO MENU MOBILE -->
            <button
                @click="open = !open"
                class="md:hidden bg-[#358054] text-white px-4 py-2 rounded-lg shadow font-medium">
                Menu
            </button>

        </div>
    </header>

    <!-- ======================== LAYOUT =========================== -->
    <div x-data="{ open: false }" class="flex flex-1 w-full relative">

        <!-- ====================== SIDEBAR ========================= -->
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'" 
        class="sidebar fixed md:static top-0 left-0 w-60 min-h-screen bg-[#358054] 
        text-white flex flex-col py-8 px-4 transform transition-transform duration-300 
        md:translate-x-0 rounded-br-2xl md:rounded-none z-40 md:z-auto">


            <nav class="space-y-4">

                {{-- ===================== ADMIN ===================== --}}
                @if (auth('admin')->check())

                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i> Painel Admin
                    </a>

                    <a href="{{ route('admin.map') }}" class="sidebar-link">
                        <i data-lucide="map-pin" class="icon"></i> Cadastrar Árvores
                    </a>

                    <a href="{{ route('admin.trees.index') }}" class="sidebar-link">
                        <i data-lucide="edit-3" class="icon"></i> Editar Árvores
                    </a>

                    <a href="{{ route('admin.contato.index') }}" class="sidebar-link">
                        <i data-lucide="inbox" class="icon"></i> Solicitações
                    </a>

                    <a href="{{ route('admin.profile.edit') }}" class="sidebar-link">
                        <i data-lucide="user" class="icon"></i> Meu Perfil
                    </a>

                    <!-- NOVO BOTÃO ADICIONADO (VISÍVEL APENAS PARA ADMIN) -->
                    <a href="{{ route('admin.accounts.index') }}" class="sidebar-link">
                        <i data-lucide="users" class="icon"></i> Gerenciar Contas
                    </a>

                {{-- ==================== ANALISTA ==================== --}}
                @elseif (auth('analyst')->check())

                    <a href="{{ route('analyst.dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i> Painel Analista
                    </a>

                    <a href="{{ route('analyst.vistorias.pendentes') }}" class="sidebar-link">
                        <i data-lucide="clipboard-check" class="icon"></i> Vistorias Pendentes
                    </a>


                {{-- ===================== SERVIÇO ===================== --}}
                @elseif (auth('service')->check())

                    <a href="{{ route('service.dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i> Painel Serviço
                    </a>

                    <a href="{{ route('service.tasks.index') }}" class="sidebar-link">
                        <i data-lucide="tool" class="icon"></i> Minhas Tarefas
                    </a>


                {{-- ===================== USER ======================== --}}
                @elseif (auth('web')->check())

                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i> Menu
                    </a>

                    <a href="{{ route('contact') }}" class="sidebar-link">
                        <i data-lucide="send" class="icon"></i> Nova Solicitação
                    </a>

                    <a href="{{ route('contact.myrequests') }}" class="sidebar-link">
                        <i data-lucide="clipboard-list" class="icon"></i> Minhas Solicitações
                    </a>

                    <a href="{{ route('profile.edit') }}" class="sidebar-link">
                        <i data-lucide="user" class="icon"></i> Meu Perfil
                    </a>

                @endif
                
                <a href="{{ route('about') }}" class="sidebar-link">
                    <i data-lucide="info" class="icon"></i> Sobre o Site
                </a>
            </nav>
        
            <!-- ================== LOGOUT ======================= -->
             <hr class="border-t-2 border-green-400 my-6 opacity-80">
             
                <a href="{{ route('home') }}" class="sidebar-link">
                    <i data-lucide="arrow-left-circle" class="icon"></i> Voltar ao Mapa
                </a>

                {{-- Logout multi-guard --}}
                @if (auth('admin')->check())
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link logout-btn">
                            <i data-lucide="log-out" class="icon"></i> Sair
                        </a>
                    </form>

                @elseif(auth('analyst')->check())
                    <form method="POST" action="{{ route('analyst.logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link logout-btn">
                            <i data-lucide="log-out" class="icon"></i> Sair
                        </a>
                    </form>

                @elseif(auth('service')->check())
                    <form method="POST" action="{{ route('service.logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link logout-btn">
                            <i data-lucide="log-out" class="icon"></i> Sair
                        </a>
                    </form>

                @elseif(auth('web')->check())
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link logout-btn">
                            <i data-lucide="log-out" class="icon"></i> Sair
                        </a>
                    </form>
                @endif

            
        </aside>

        <!-- ================= CONTEÚDO ===================== -->
        <main class="flex-1 p-10 bg-transparent overflow-y-auto md:ml-50">
            @yield('content')
        </main>

    </div>

    <!-- ================== FOOTER ======================== -->
    <footer class="bg-gray-800 shadow mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
        </div>
    </footer>

    <script> lucide.createIcons(); </script>

    <!-- SweetAlert Logout -->
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

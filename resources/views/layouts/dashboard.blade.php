<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Painel') - Árvores de Paracambi</title>

    <!-- FONTES E ESTILOS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/dashboard.css', 'resources/css/perfil.css'])

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>

    <!--  SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">

    <!-- HEADER SUPERIOR -->
    <header class="site-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">

            <!-- LOGOS E TÍTULO -->
            <div class="flex items-center gap-4 flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-4">
                    <img src="{{ asset('images/Brasao_Verde.png') }}" alt="Logo Brasão de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <h1 class="text-3xl sm:text-4xl font-bold">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]">Paracambi</span>
                    </h1>
                </a>
            </div>
        </div>
    </header>

    <!-- ====== CONTEÚDO COM SIDEBAR ====== -->
    <div class="flex flex-1 w-full items-start">
        <!-- SIDEBAR -->
        <aside
            class="sidebar w-60 bg-[#358054] text-white flex flex-col py-8 px-4 sticky top-0 h-fit self-start rounded-br-2xl">
            <nav class="space-y-4">

                @if (auth('admin')->check())
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i>
                        <span>Painel</span>
                    </a>

                    <a href="{{ route('admin.map') }}" class="sidebar-link">
                        <i data-lucide="map-pin" class="icon"></i>
                        <span>Cadastrar Árvores</span>
                    </a>

                    <a href="{{ route('admin.trees.index') }}" class="sidebar-link">
                        <i data-lucide="edit-3" class="icon"></i>
                        <span>Editar Árvores</span>
                    </a>

                    <a href="{{ route('admin.contato.index') }}" class="sidebar-link">
                        <i data-lucide="inbox" class="icon"></i>
                        <span>Solicitações</span>
                    </a>

                    <a href="{{ route('admin.profile.edit') }}" class="sidebar-link">
                        <i data-lucide="user" class="icon"></i>
                        <span>Meu Perfil</span>
                    </a>

                    <a href="{{ route('about') }}" class="sidebar-link">
                        <i data-lucide="info" class="icon"></i>
                        <span>Sobre o Site</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i data-lucide="layout-dashboard" class="icon"></i>
                        <span>Menu</span>
                    </a>

                    <a href="{{ route('contact') }}" class="sidebar-link">
                        <i data-lucide="send" class="icon"></i>
                        <span>Nova Solicitação</span>
                    </a>

                    <a href="{{ route('contact.myrequests') }}" class="sidebar-link">
                        <i data-lucide="clipboard-list" class="icon"></i>
                        <span>Minhas Solicitações</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" class="sidebar-link">
                        <i data-lucide="user" class="icon"></i>
                        <span>Meu Perfil</span>
                    </a>

                    <a href="{{ route('about') }}" class="sidebar-link">
                        <i data-lucide="info" class="icon"></i>
                        <span>Sobre o Site</span>
                    </a>
                @endif
            </nav>

            <div class="mt-auto border-t-2 border-green-400 pt-8">

                <a href="{{ route('home') }}" class="sidebar-link text-base font-medium hover:opacity-100">
                    <i data-lucide="arrow-left-circle" class="icon"></i>
                    Voltar ao Mapa
                </a>

                @if (auth('admin')->check())
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link text-base font-medium hover:opacity-100 logout-btn">
                            <i data-lucide="log-out" class="icon"></i>
                            Sair
                        </a>
                    </form>
                @else
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link text-base font-medium hover:opacity-100 logout-btn">
                            <i data-lucide="log-out" class="icon"></i>
                            Sair
                        </a>
                    </form>
                @endif
            </div>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="flex-1 p-10 bg-transparent overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <!-- RODAPÉ -->
    <footer class="bg-gray-800 shadow mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>

    <!--  Modal de confirmação SWEETALERT -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".logout-btn").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: "Tem certeza que quer sair?",
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

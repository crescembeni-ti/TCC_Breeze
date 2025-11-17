<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Painel') - Árvores de Paracambi</title>

    <!-- FONTES E ESTILOS -->
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

    <!-- 🔥 SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">

    <!-- HEADER SUPERIOR -->
    <header class="site-header flex items-center justify-between px-8 py-4 shadow-md bg-[#baffb4] border-b-2 border-[#358054]">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-16 w-16 object-contain">
            <h1 class="text-3xl font-bold">
                <span class="text-[#358054]">Árvores de</span>
                <span class="text-[#a0c520]"> Paracambi</span>
            </h1>
        </div>
    </header>

    <div class="flex flex-1">

        <!-- SIDEBAR LATERAL -->
        <aside class="sidebar w-64 bg-[#358054] text-white flex flex-col py-8 px-4">
            <nav class="space-y-4">

                @if(auth('admin')->check())

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
                        <span>Painel</span>
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

            <div class="mt-auto border-t border-green-400 pt-6">

                <a href="{{ route('home') }}" class="sidebar-link text-sm opacity-80 hover:opacity-100">
                    <i data-lucide="arrow-left-circle" class="icon"></i>
                    Voltar ao Mapa
                </a>

                @if(auth('admin')->check())
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link text-sm opacity-80 hover:opacity-100 logout-btn">
                            <i data-lucide="log-out" class="icon"></i>
                            Sair
                        </a>
                    </form>
                @else
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <a href="#" class="sidebar-link text-sm opacity-80 hover:opacity-100 logout-btn">
                            <i data-lucide="log-out" class="icon"></i>
                            Sair
                        </a>
                    </form>
                @endif
            </div>
        </aside>

        <main class="flex-1 p-10 bg-white overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <footer class="bg-gray-800 text-gray-300 text-center py-4 text-sm border-t border-[#358054]">
        © {{ date('Y') }} Mapa de Árvores de Paracambi-RJ.
    </footer>

    <script>
        lucide.createIcons();
    </script>

    <style>
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            transition: background 0.3s ease, transform 0.2s;
        }
        .sidebar-link:hover {
            background-color: #2f6f47;
            transform: translateX(4px);
        }
        .icon {
            width: 18px;
            height: 18px;
        }
    </style>

    <!-- 🔥 Modal de confirmação SWEETALERT -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".logout-btn").forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.preventDefault();

                Swal.fire({
                    title: "Tem certeza que quer sair?",
                    text: "Você precisará fazer login novamente🌳.",
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

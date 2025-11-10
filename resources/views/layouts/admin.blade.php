<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel - Árvores de Paracambi</title>

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- CSS e JS (agrupados em um único Vite) -->
    @vite([
        'resources/css/app.css',
        'resources/css/dashboard.css',
        'resources/css/perfil.css',
        'resources/css/trees.css',
        'resources/js/app.js'
    ])

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <!-- Ícones -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">

    <!-- CABEÇALHO -->
    <header class="site-header flex items-center justify-between px-8 py-4 shadow-md">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-20 w-20 object-contain">
            <h1 class="text-4xl font-bold">
                <span class="text-[#358054]">Árvores de</span>
                <span class="text-[#a0c520]"> Paracambi</span>
            </h1>
        </div>
    </header>

    <!-- LAYOUT PRINCIPAL -->
    <div class="flex flex-1">

        <!-- SIDEBAR -->
        <aside class="sidebar w-64 bg-[#358054] text-white flex flex-col py-8 px-4">

            <nav class="space-y-4">

                <!-- Link comum -->
                <a href="{{ route('admin.map') }}" class="sidebar-link">
                    <i data-lucide="map" class="icon"></i>
                    <span>Mapa</span>
                </a>

                <!-- ADMIN -->
                @if(auth()->user()->is_admin)

                    <a href="{{ route('admin.trees.index') }}" class="sidebar-link">
                        <i data-lucide="tree-pine" class="icon"></i>
                        <span>Árvores</span>
                    </a>

                    <a href="{{ route('admin.contato.index') }}" class="sidebar-link">
                        <i data-lucide="inbox" class="icon"></i>
                        <span>Mensagens</span>
                    </a>

                @else
                <!-- USUÁRIO COMUM -->

                    <a href="{{ route('contato') }}" class="sidebar-link">
                        <i data-lucide="plus-circle" class="icon"></i>
                        <span>Fazer Solicitação</span>
                    </a>

                    <a href="{{ route('solicitacoes.index') }}" class="sidebar-link">
                        <i data-lucide="list" class="icon"></i>
                        <span>Minhas Solicitações</span>
                    </a>

                @endif

                <!-- PERFIL -->
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i data-lucide="user" class="icon"></i>
                    <span>Meu Perfil</span>
                </a>

            </nav>

            <!-- VOLTAR AO MAPA -->
            <div class="mt-auto border-t border-green-400 pt-6">
                <a href="{{ route('home') }}" class="sidebar-link text-sm opacity-80 hover:opacity-100">
                    <i data-lucide="arrow-left-circle" class="icon"></i>
                    Voltar ao Mapa
                </a>
            </div>

        </aside>

        <!-- CONTEÚDO -->
        <main class="flex-1 p-10">
            <div class="bg-white shadow-sm rounded-lg p-8">
                @yield('content')
            </div>
        </main>

    </div>

    <!-- RODAPÉ -->
    <footer class="bg-gray-800 text-gray-300 text-center py-4 text-sm border-t border-[#358054]">
        © {{ date('Y') }} Mapa de Árvores de Paracambi-RJ.
    </footer>

    <script>
        lucide.createIcons();
    </script>

    @stack('scripts')

</body>
</html>

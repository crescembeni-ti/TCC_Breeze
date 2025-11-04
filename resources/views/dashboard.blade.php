<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/dashboard.css')

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script> {{-- Ícones leves --}}
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">

    <!-- HEADER -->
    <header class="site-header flex items-center justify-between px-8 py-4 shadow-md">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-20 w-20 object-contain">
            <h1 class="text-4xl font-bold">
                <span class="text-[#358054]">Árvores de</span>
                <span class="text-[#a0c520]"> Paracambi</span>
            </h1>
        </div>
    </header>

    <div class="flex flex-1">
        <!-- SIDEBAR -->
        <aside class="sidebar w-64 bg-[#358054] text-white flex flex-col py-8 px-4">
            <nav class="space-y-4">
                <a href="{{ route('admin.map') }}" class="sidebar-link">
                    <i data-lucide="map" class="icon"></i>
                    <span>Mapa</span>
                </a>
                <a href="{{ route('admin.trees.index') }}" class="sidebar-link">
                    <i data-lucide="tree-pine" class="icon"></i>
                    <span>Árvores</span>
                </a>
                <a href="{{ route('admin.contacts.index') }}" class="sidebar-link">
                    <i data-lucide="inbox" class="icon"></i>
                    <span>Mensagens</span>
                </a>
                    <a href="{{ route('profile.edit') }}" class="sidebar-link">
                    <i data-lucide="user" class="icon"></i>
                    <span>Meu Perfil</span>
                </a>
            </nav>

            <div class="mt-auto border-t border-green-400 pt-6">
                <a href="{{ route('home') }}" class="sidebar-link text-sm opacity-80 hover:opacity-100">
                    <i data-lucide="arrow-left-circle" class="icon"></i>
                    Voltar ao Mapa
                </a>
            </div>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="flex-1 p-10">
            <div class="bg-white shadow-sm rounded-lg p-8">
                <h2 class="text-3xl font-bold text-[#358054] mb-4">Painel Administrativo</h2>
                <p class="text-gray-700 text-lg">
                    Bem-vindo ao painel de controle do sistema 🌳  
                    Use o menu à esquerda para navegar pelas seções.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <a href="{{ route('admin.trees.index') }}" class="card">
                        <h3>🌿 Árvores Cadastradas</h3>
                        <p>Gerencie as árvores exibidas no mapa.</p>
                    </a>

                    <a href="{{ route('admin.contacts.index') }}" class="card">
                        <h3>💬 Mensagens</h3>
                        <p>Veja e responda denúncias e contatos.</p>
                    </a>

                    <a href="{{ route('admin.map') }}" class="card">
                        <h3>🗺️ Mapa Interativo</h3>
                        <p>Cadastre árvores diretamente no mapa.</p>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <footer class="bg-gray-800 text-gray-300 text-center py-4 text-sm border-t border-[#358054]">
        © {{ date('Y') }} Mapa de Árvores de Paracambi-RJ.
    </footer>

    <script>
        lucide.createIcons(); // Ativa os ícones
    </script>
</body>
</html>

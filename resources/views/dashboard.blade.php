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
    <script src="https://unpkg.com/lucide@latest"></script>
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
                
                <!-- Link comum a todos -->
                <a href="{{ route('admin.map') }}" class="sidebar-link">
                    <i data-lucide="map" class="icon"></i>
                    <span>Mapa</span>
                </a>

                <!-- Se for ADMIN -->
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.trees.index') }}" class="sidebar-link">
                        <i data-lucide="tree-pine" class="icon"></i>
                        <span>Árvores</span>
                    </a>

                    <a href="{{ route('admin.contato.index') }}" class="sidebar-link">
                        <i data-lucide="inbox" class="icon"></i>
                        <span>Mensagens</span>
                    </a>

                    @can('manage-roles')
                        <div class="link-secao-admin mt-6 p-3 bg-green-700 rounded">
                            <h3 class="text-lg font-semibold">Administração Avançada</h3>
                            <p class="text-sm text-green-100">Gerencie cargos e permissões.</p>
                            <a href="{{ route('admin.roles.index') }}" class="underline text-green-200 text-sm">Ir para Gerenciamento</a>
                        </div>
                    @endcan

                <!-- Se for USUÁRIO COMUM -->
                @else
                    <a href="{{ route('contact') }}" class="sidebar-link">
                        <i data-lucide="plus-circle" class="icon"></i>
                        <span>Fazer Solicitação</span>
                    </a>

                    <a href="{{ route('contact.myrequests') }}" class="sidebar-link">
                        <i data-lucide="list" class="icon"></i>
                        <span>Minhas Solicitações</span>
                    </a>
                @endif

                <!-- Perfil (comum a todos) -->
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

                {{-- TÍTULO --}}
                @if(auth()->user()->is_admin)
                    <h2 class="text-3xl font-bold text-[#358054] mb-4">Painel Administrativo</h2>
                    <p class="text-gray-700 text-lg mb-4">
                        Bem-vindo, {{ auth()->user()->name }} 🌳  
                        Use o menu à esquerda para gerenciar árvores, mensagens e usuários.
                    </p>

                    {{-- Estatísticas do admin --}}
                    @if(isset($stats))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
                            <div class="p-4 bg-green-100 rounded shadow">
                                <h4 class="text-lg font-semibold text-green-800">🌳 Árvores</h4>
                                <p class="text-2xl font-bold text-green-900">{{ $stats['total_trees'] }}</p>
                            </div>
                            <div class="p-4 bg-green-100 rounded shadow">
                                <h4 class="text-lg font-semibold text-green-800">📋 Atividades</h4>
                                <p class="text-2xl font-bold text-green-900">{{ $stats['total_activities'] }}</p>
                            </div>
                            <div class="p-4 bg-green-100 rounded shadow">
                                <h4 class="text-lg font-semibold text-green-800">🌱 Espécies</h4>
                                <p class="text-2xl font-bold text-green-900">{{ $stats['total_species'] }}</p>
                            </div>
                        </div>
                    @endif

                @else
                    <h2 class="text-3xl font-bold text-[#a0c520] mb-4">Painel do Usuário</h2>
                    <p class="text-gray-700 text-lg">
                        Bem-vindo, {{ auth()->user()->name }} 🌱  
                        Aqui você pode visualizar o mapa, enviar e acompanhar solicitações.
                    </p>
                @endif

                {{-- CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">

                    <a href="{{ route('admin.map') }}" class="card">
                        <h3>🗺️ Mapa Interativo</h3>
                        <p>Veja as árvores cadastradas em Paracambi.</p>
                    </a>

                    {{-- Cards diferentes por tipo --}}
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.trees.index') }}" class="card">
                            <h3>🌿 Árvores Cadastradas</h3>
                            <p>Gerencie as árvores exibidas no mapa.</p>
                        </a>

                        <a href="{{ route('admin.contato.index') }}" class="card">
                            <h3>💬 Mensagens</h3>
                            <p>Veja e responda contatos e denúncias.</p>
                        </a>
                    @else
                        <a href="{{ route('contact') }}" class="card">
                            <h3>📤 Nova Solicitação</h3>
                            <p>Solicite o plantio de uma nova árvore.</p>
                        </a>

                        <a href="{{ route('contact.myrequests') }}" class="card">
                            <h3>📋 Minhas Solicitações</h3>
                            <p>Acompanhe o status das suas solicitações.</p>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Logs de admin --}}
            @if(auth()->user()->is_admin)
                <div class="bg-white rounded-lg shadow p-6 mt-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Atividade Recente do Painel</h2>

                    <div class="space-y-4">
                        @forelse($adminLogs as $log)
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <p class="text-sm text-gray-600">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-gray-900">
                                    {{ $log->description }}
                                </p>
                            </div>
                        @empty
                            <p class="text-gray-600">Nenhuma atividade registrada ainda.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </main>
    </div>

    <footer class="bg-gray-800 text-gray-300 text-center py-4 text-sm border-t border-[#358054]">
        © {{ date('Y') }} Mapa de Árvores de Paracambi-RJ.
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

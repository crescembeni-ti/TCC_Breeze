<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Árvores de Paracambi</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/welcome.css')
     <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>
{{-- ADAPTAÇÃO AQUI: Removido 'bg-gray-100' e adicionado 'welcome-page' --}}
<body class="font-sans antialiased welcome-page">
    <div class="min-h-screen">
        <header class="site-header relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
        
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-20 w-20 object-contain">
            <h1 class="text-4xl font-bold">
                <span class="text-[#358054]">Árvores de</span>
                <span class="text-[#a0c520]"> Paracambi</span>
            </h1>
        </div>

        <div class="flex items-center gap-4" x-data="{ open: false }">
            
            @auth
                {{-- Se o usuário está LOGADO, mostra o link para o Painel --}}
                <a href="{{ route('dashboard') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">Painel</a>
            @else
                {{-- Se o usuário é VISITANTE, mostra Entrar/Cadastrar --}}
                <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">Entrar</a>
                {{-- TRADUÇÃO AQUI: De 'Register' para 'Cadastrar' --}}
                <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block">Cadastrar</a>
            @endauth

            <button 
                @click="open = !open"
                class="menu-button focus:outline-none"
                aria-label="Abrir menu"
            >
                <svg xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" 
                    stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div 
                x-show="open"
                @click.away="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="menu-dropdown absolute right-0 top-[6rem] z-50"
                style="display: none;" 
            >
                <a href="{{ route('about') }}">Sobre</a>

                @auth
                    {{-- Link para a página de fazer a solicitação --}}
                    <a href="{{ route('contact') }}">Fazer Solicitação</a>
                    
                    {{-- Link para a página de acompanhar as solicitações --}}
                    <a href="{{ route('contact.myrequests') }}">Minhas Solicitações</a>

                    {{-- Link para o perfil do usuário (mudar nome/senha) --}}
                    <a href="{{ route('profile.edit') }}">Meu Perfil</a>
                    
                    {{-- Divisor --}}
                    <div class="menu-dropdown-divider"></div> 

                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <a href="{{ route('logout') }}"
                           class="menu-dropdown-logout-link" 
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            Sair
                        </a>
                    </form>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    <a href="{{ route('register') }}">Cadastrar</a>
                @endauth
            </div>
            
        </div>
    </div>
</header>


        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                <div class="bg-white rounded-lg shadow p-6 stats-card">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Total de Árvores</h3>
                    <p class="text-4xl font-bold text-green-600">{{ $stats['total_trees'] }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 stats-card">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Atividades Registradas</h3>
                    <p class="text-4xl font-bold text-blue-600">{{ $stats['total_activities'] }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 stats-card">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Espécies no Mapa</h3>
                    <p class="text-4xl font-bold text-purple-600">{{ $stats['total_species'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Mapa Interativo</h2>
                <div id="map"></div> </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Atividades Recentes</h2>
                <div class="space-y-4">
                    @forelse($recentActivities as $activity)
                        <div class="border-l-4 border-green-500 pl-4 py-2 activity-item">
                            <p class="text-sm text-gray-600">{{ $activity->activity_date->format('d/m/Y H:i') }}</p>
                            <p class="text-gray-900">
                                A árvore <strong>{{ $activity->tree->species->name }}</strong> 
                                em <strong>{{ $activity->tree->address }}</strong> 
                                foi <strong>{{ $activity->type }}</strong> 
                                por {{ $activity->user->name }}.
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-600">Nenhuma atividade registrada ainda.</p>
                    @endforelse
                </div>
            </div>
        </main>

        <footer class="bg-gray-800 shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Mapa de Árvores. Desenvolvido com Laravel e Leaflet.</p>
            </div>
        </footer>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
    // Criação do mapa e camadas (mantém igual ao teu atual)
    const map = L.map('map').setView([-22.6111, -43.7089], 14);

    const satelliteLayer = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 
        { attribution: 'Tiles © Esri' }
    ).addTo(map);

    const labelsLayer = L.tileLayer(
        'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', 
        { subdomains: 'abcd', maxZoom: 20 }
    ).addTo(map);

    const bounds = [
        [-22.71, -43.85],
        [-22.51, -43.58]
    ];
    map.setMaxBounds(bounds);
    map.on('drag', () => map.panInsideBounds(bounds, { animate: false }));
    map.setMinZoom(13);
    map.setMaxZoom(17);

    let allTrees = [];
    let markersLayer = L.layerGroup().addTo(map);

    // === BOTÃO FLUTUANTE ===
    const toggleBtn = L.DomUtil.create('button', 'map-filter-toggle');
    toggleBtn.innerHTML = '<i>▼</i> 🌿 Filtros';
    map.getContainer().appendChild(toggleBtn);

    // === PAINEL DE FILTRO ===
    const panel = L.DomUtil.create('div', 'map-filter-panel');
    panel.innerHTML = `
        <label for="bairro">Bairro</label>
        <select id="bairro"><option value="">Todos</option></select>

        <label for="especie">Espécie</label>
        <select id="especie"><option value="">Todas</option></select>

        <button id="filtrar">Aplicar Filtro</button>
    `;
    map.getContainer().appendChild(panel);

    // Evita conflito de clique
    L.DomEvent.disableClickPropagation(panel);
    L.DomEvent.disableScrollPropagation(panel);

    // Animação do botão + painel
    toggleBtn.addEventListener('click', () => {
        panel.classList.toggle('open');
        toggleBtn.classList.toggle('open');
    });

    // === CARREGA AS ÁRVORES ===
    fetch('{{ route('trees.data') }}')
        .then(response => response.json())
        .then(trees => {
            allTrees = trees;
            popularFiltros(trees);
            exibirArvores(trees);
        })
        .catch(error => console.error('Erro ao carregar árvores:', error));

    function popularFiltros(trees) {
        const bairros = [...new Set(trees.map(t => t.neighborhood).filter(Boolean))].sort();
        const especies = [...new Set(trees.map(t => t.species_name).filter(Boolean))].sort();

        const bairroSelect = document.getElementById('bairro');
        const especieSelect = document.getElementById('especie');

        bairros.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b;
            opt.textContent = b;
            bairroSelect.appendChild(opt);
        });

        especies.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e;
            opt.textContent = e;
            especieSelect.appendChild(opt);
        });
    }

    function exibirArvores(trees) {
        markersLayer.clearLayers();

        trees.forEach(tree => {
            const radius = Math.max(5, tree.trunk_diameter / 5);
            const marker = L.circleMarker([tree.latitude, tree.longitude], {
                radius,
                fillColor: tree.color_code,
                color: '#FFF',
                weight: 2,
                opacity: 0.9,
                fillOpacity: 0.8
            });

            marker.bindPopup(`
                <div style="padding: 0.5rem;">
                    <h3 style="font-weight:700; font-size:1.125rem;">${tree.species_name}</h3>
                    <p><strong>Bairro:</strong> ${tree.neighborhood}</p>
                    <p><strong>Diâmetro:</strong> ${tree.trunk_diameter} cm</p>
                    <a href="/trees/${tree.id}" style="color:#16a34a;text-decoration:underline;">Ver detalhes</a>
                </div>
            `);

            markersLayer.addLayer(marker);
        });
    }

    // === FILTRAR ===
    document.addEventListener('click', e => {
        if (e.target.id === 'filtrar') {
            const bairro = document.getElementById('bairro').value;
            const especie = document.getElementById('especie').value;

            const filtradas = allTrees.filter(tree => {
                const matchBairro = bairro ? tree.neighborhood === bairro : true;
                const matchEspecie = especie ? tree.species_name === especie : true;
                return matchBairro && matchEspecie;
            });

            exibirArvores(filtradas);
            panel.classList.remove('open');
            toggleBtn.classList.remove('open');
        }
    });
</script>


</body>
</html>
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">

        <div class="flex items-center gap-4 flex-shrink-0">
            <a href="{{ route('home') }}" class="flex items-center gap-4">
                <img src="{{ asset('images/Brasao_Verde.png') }}" alt="Logo Brasão de Paracambi" class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                <h1 class="text-3xl sm:text-4xl font-bold leading-tight text-white drop-shadow-md">
                    <span class="text-[#358054]">Árvores de</span>
                    <span class="text-[#a0c520]"> Paracambi</span>
                </h1>
            </a>
        </div>

        <div class="flex items-center gap-3 sm:gap-4 relative" x-data="{ open: false }">

            @auth
                <a href="{{ route('dashboard') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">Painel</a>
                <a href="{{ route('about') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">Sobre</a>
            @else
                <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">Entrar</a>
                <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block">Cadastrar</a>
            @endauth

            <button 
                @click="open = !open"
                class="menu-button focus:outline-none sm:ml-2"
                aria-label="Abrir menu"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor"
                     class="icon-hamburger absolute inset-0 m-auto transition-all duration-300"
                     :class="{ 'opacity-0 rotate-90 scale-75': open }">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>

                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor"
                     class="icon-close absolute inset-0 m-auto transition-all duration-300 opacity-0 scale-75 rotate-90"
                     :class="{ 'opacity-100 rotate-0 scale-100': open }">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div 
    x-show="open"
    @click.away="open = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    class="menu-dropdown absolute right-0 top-[5rem] bg-white border border-gray-200 rounded-xl shadow-xl sm:w-48 w-[90vw] py-2 text-center sm:text-left z-50"
    style="display: none;"
>
    @guest
        <a href="{{ route('contact') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-green-700 transition">Fazer Solicitação</a>
        <a href="{{ route('contact.myrequests') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-green-700 transition">Minhas Solicitações</a>
        <a href="{{ route('about') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-green-700 transition">Sobre</a>
    @endguest

    @auth
        <a href="{{ route('contact') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-green-700 transition">Fazer Solicitação</a>
        <a href="{{ route('contact.myrequests') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-green-700 transition">Minhas Solicitações</a>
        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-green-700 transition">Meu Perfil</a>

        <div class="border-t border-gray-200 my-2"></div>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <a href="{{ route('logout') }}"
               class="block px-4 py-2 text-red-600 hover:bg-red-100 hover:text-red-800 font-medium transition"
               onclick="event.preventDefault(); this.closest('form').submit();">
               Sair
            </a>
        </form>
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
                <p class="text-center text-gray-300">© {{ date('Y') }} Mapa de Árvores.</p>
            </div>
        </footer>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
    // 1. Injeta os bairros carregados pelo TreeController
    const allBairros = @json($bairros);

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
    let filteredTrees = [];
    let markersLayer = L.layerGroup().addTo(map);
    let treeMarkers = {};

    // === Botão e painel (Sem alteração) ===
    const toggleBtn = L.DomUtil.create('button', 'map-filter-toggle');
    toggleBtn.innerHTML = '🌿 Filtros';
    map.getContainer().appendChild(toggleBtn);

    const panel = L.DomUtil.create('div', 'map-filter-panel');
    panel.innerHTML = `
    <label for="search">🔎 Pesquisar árvore</label>
    <div style="position: relative;">
        <input type="text" id="search" placeholder="Ex: ipê, pau-brasil...">
        <div id="autocomplete" class="autocomplete-list"></div>
    </div>

    <label for="bairro">Bairro</label>
    <select id="bairro"><option value="">Todos</option></select>

    <label for="especie">Espécie</label>
    <select id="especie"><option value="">Todas</option></select>

    <div class="flex gap-2 mt-2">
        <button id="aplicarFiltro" class="w-1/2">Filtrar</button>
        <button id="limparFiltro" class="w-1/2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">🧹 Limpar</button>
    </div>
`;

    map.getContainer().appendChild(panel);

    L.DomEvent.disableClickPropagation(panel);
    L.DomEvent.disableScrollPropagation(panel);

    toggleBtn.addEventListener('click', () => {
        panel.classList.toggle('open');
    });

    // 2. Removido o 'fetch' para 'bairros.data' que estava quebrado.

    // 3. Removida a função 'popularBairros' que causava o erro [Object Object].

    // === Carrega árvores ===
    fetch('{{ route('trees.data') }}')
    .then(response => response.json())
    .then(data => {
      allTrees = data;
      exibirArvores(allTrees);
      // 4. Chama a função 'popularFiltros' e passa as árvores E os bairros
      popularFiltros(allTrees, allBairros); 
    })
    .catch(error => console.error('Erro ao carregar árvores:', error));


    // === 5. Popula filtros (ATUALIZADO) ===
    function popularFiltros(trees, bairros) {
        // Pega as espécies da lista de árvores
        const especies = [...new Set(trees.map(t => t.species_name).filter(Boolean))].sort();

        const bairroSelect = document.getElementById('bairro');
        const especieSelect = document.getElementById('especie');
        
        // --- CORREÇÃO DO [Object Object] ---
        // Popula o select de Bairros usando a lista 'allBairros'
        bairros.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.nome; // Usa o NOME (ex: "Centro")
            opt.textContent = b.nome; // E mostra o NOME
            bairroSelect.appendChild(opt);
        });
        // --- FIM DA CORREÇÃO ---

        especies.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e;
            opt.textContent = e;
            especieSelect.appendChild(opt);
        });
    }

    // === Exibir árvores (Sem alteração) ===
    function exibirArvores(trees) {
        markersLayer.clearLayers();
        treeMarkers = {};
        filteredTrees = trees;

        trees.forEach((tree, index) => {
            const radius = Math.max(5, tree.trunk_diameter / 5);
            const marker = L.circleMarker([tree.latitude, tree.longitude], {
                radius,
                fillColor: tree.color_code,
                color: '#FFF',
                weight: 2,
                opacity: 0.9,
                fillOpacity: 0.8
            }).addTo(markersLayer);

            marker.bindPopup(criarPopup(tree, index));
            treeMarkers[tree.id] = marker;
        });
    }

// === Popup (ATUALIZADO) ===
function criarPopup(tree, index) {
    const anterior = index > 0
        ? `<button onclick="mudarArvore(${index - 1})" class="popup-nav-btn" title="Árvore anterior">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
             </svg>
           </button>`
        : '<span></span>';

    const proximo = index < filteredTrees.length - 1
        ? `<button onclick="mudarArvore(${index + 1})" class="popup-nav-btn" title="Próxima árvore">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
             </svg>
           </button>`
        : '<span></span>';

    return `
        <div style="padding: 0.5rem; text-align:center;">
            <h3 style="font-weight:700; font-size:1.125rem;">${tree.species_name}</h3>
            
            <p><strong>Endereço:</strong> ${tree.address}</p>
            
            <p><strong>Diâmetro:</strong> ${tree.trunk_diameter} cm</p>
            <div class="popup-nav">
                ${anterior}
                <a href="/trees/${tree.id}" class="popup-link">Ver detalhes</a>
                ${proximo}
            </div>
        </div>
    `;
}

// === Navegação entre árvores (Sem alteração) ===
window.mudarArvore = function (index) {
    const tree = filteredTrees[index];
    const marker = treeMarkers[tree.id];
    
    map.flyTo([tree.latitude, tree.longitude], 17, { duration: 1.0 });

    setTimeout(() => {
        marker.bindPopup(criarPopup(tree, index)).openPopup();
    }, 600);
};

    // === Filtro dinâmico (ATUALIZADO) ===
    const bairroSelect = document.getElementById('bairro');
    const especieSelect = document.getElementById('especie');
    const searchInput = document.getElementById('search');
    const autocompleteBox = document.getElementById('autocomplete');
    const aplicarBtn = document.getElementById('aplicarFiltro');
    const limparBtn = document.getElementById('limparFiltro');


        function aplicarFiltro(foco = false) {
        const bairro = bairroSelect.value; // Pega o nome, ex: "Centro"
        const especie = especieSelect.value;
        const busca = searchInput.value.toLowerCase();

        const filtradas = allTrees.filter(tree => {
            // 7. CORREÇÃO DA LÓGICA DO FILTRO:
            // Compara se o 'address' da árvore CONTÉM o nome do bairro selecionado
            const matchBairro = bairro ? tree.address?.toLowerCase().includes(bairro.toLowerCase()) : true;
            const matchEspecie = especie ? tree.species_name?.toLowerCase() === especie.toLowerCase() : true;
            const matchBusca = busca ? tree.species_name?.toLowerCase().includes(busca) : true;
            return matchBairro && matchEspecie && matchBusca;
        });

        exibirArvores(filtradas);

        // ... (o resto da sua função de mensagem está correta) ...
        const mensagemAntiga = document.querySelector('.map-filter-message');
        if (mensagemAntiga) mensagemAntiga.remove();

        const msg = document.createElement('div');
        msg.classList.add('map-filter-message');

        if (filtradas.length > 0) {
            msg.innerHTML = `✅ <strong>${filtradas.length}</strong> árvore(s) encontrada(s)!`;
            msg.classList.add('success');

            if (foco) {
                const alvo = filtradas[0];
                map.setView([alvo.latitude, alvo.longitude], 17);
                treeMarkers[alvo.id].openPopup();
            }
        } else {
            msg.innerHTML = `⚠️ Nenhuma árvore encontrada com esses filtros.`;
            msg.classList.add('warning');
        }
        panel.appendChild(msg);
        setTimeout(() => {
            msg.classList.add('fade-out');
            setTimeout(() => msg.remove(), 800);
        }, 4000);
    }

    bairroSelect.addEventListener('change', () => aplicarFiltro());
    especieSelect.addEventListener('change', () => aplicarFiltro());

    aplicarBtn.addEventListener('click', () => {
        aplicarBtn.classList.add('loading');
        aplicarBtn.textContent = 'Filtrando...';
        setTimeout(() => {
            aplicarFiltro(true);
            aplicarBtn.classList.remove('loading');
            aplicarBtn.textContent = 'Filtrar';
        }, 600);
    });

    limparBtn.addEventListener('click', () => {
    bairroSelect.value = '';
    especieSelect.value = '';
    searchInput.value = '';
    exibirArvores(allTrees);

    const msg = document.createElement('div');
    msg.classList.add('map-filter-message', 'success');
    msg.innerHTML = '🧹 Filtros limpos! Todas as árvores foram exibidas.';
    panel.appendChild(msg);

    setTimeout(() => {
        msg.classList.add('fade-out');
        setTimeout(() => msg.remove(), 800);
    }, 3000);
});
</script>
</body>
</html>
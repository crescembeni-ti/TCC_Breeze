<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Árvores de Paracambi</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/welcome.css'])
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>

<body class="font-sans antialiased welcome-page">
<div class="min-h-screen">

    {{-- ========================================================= --}}
    {{-- HEADER INTELIGENTE --}}
    {{-- ========================================================= --}}
    <header class="site-header bg-[#baffb4] border-b-2 border-[#358054] shadow-md">
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

            {{-- ========================================================= --}}
            {{-- MENU SUPERIOR --}}
            {{-- ========================================================= --}}
           <div class="flex items-center gap-3 sm:gap-4 relative" x-data="{ open: false }">

                {{-- ADMIN LOGADO --}}
                @if(auth('admin')->check())
                    <a href="{{ route('admin.dashboard') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">
                        Painel Administrativo
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf

                {{-- USUÁRIO LOGADO --}}
                @elseif(auth()->check())
                    <a href="{{ route('dashboard') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">
                        Painel
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        
                {{-- VISITANTE (NÃO LOGADO) --}}
                @else
                    <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block">
                        Cadastrar
                    </a>

                    {{-- MENU HAMBÚRGUER (ATUALIZADO E ANIMADO) --}}
<div class="relative inline-block">

    <!-- Botão animado -->
    <button id="guestMenuBtn"
        class="ml-3 btn bg-[#358054] text-white hover:bg-[#2d6e4b] rounded-lg flex items-center gap-2 transition-all duration-200">

        <!-- Ícone animado -->
        <svg id="iconMenu" class="w-6 h-6 transition-all duration-200"
            fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 6h16" />
            <path d="M4 12h16" />
            <path d="M4 18h16" />
        </svg>

        <!-- Texto mantém igual -->
        Menu
    </button>

    <!-- DROPDOWN novo no tom do seu site -->
    <div id="guestMenu"
         class="hidden absolute right-0 mt-2 w-56 bg-[#e8ffe6] border border-[#358054]/40 rounded-xl shadow-lg z-50">

        <a href="{{ route('contact') }}"
   class="block px-4 py-2 font-semibold text-black !text-black opacity-100 !opacity-100 hover:bg-[#d9f5d6]">
    Fazer Solicitação
</a>

<a href="{{ route('contact.myrequests') }}"
   class="block px-4 py-2 font-semibold text-black !text-black opacity-100 !opacity-100 hover:bg-[#d9f5d6]">
    Minhas Solicitações
</a>

<a href="{{ route('about') }}"
   class="block px-4 py-2 font-semibold text-black !text-black opacity-100 !opacity-100 hover:bg-[#d9f5d6]">
    Sobre o Site
</a>


    </div>
</div>


<script>
                 (function(){
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
                            // ÍCONE VIRA X
                            icon.innerHTML = `
                            <path d="M6 6l12 12" />
                            <path d="M6 18L18 6" />
                            `;
                            } else {
                            // VOLTA HAMBÚRGUER
                            icon.innerHTML = `
                            <path d="M4 6h16" />
                            <path d="M4 12h16" />
                            <path d="M4 18h16" />
                            `;
                            }
                            });

    // Fecha ao clicar fora
    window.addEventListener('click', () => {
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            icon.innerHTML = `
                <path d="M4 6h16" />
                <path d="M4 12h16" />
                <path d="M4 18h16" />
            `;
            aberto = false;
            }
            });
            })();
            </script>

                @endif
            </div>
        </div>
    </header>

    {{-- ========================================================= --}}
    {{-- CONTEÚDO PRINCIPAL --}}
    {{-- ========================================================= --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Mapa --}}
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Mapa Interativo</h2>
            <div id="map"></div>
        </div>

        {{-- Atividades recentes --}}
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
            <p class="text-center text-gray-300">© {{ date('Y') }} Mapa de Árvores de Paracambi.</p>
        </div>
    </footer>
</div>

{{-- ========================================================= --}}
{{-- MAPA (Leaflet) --}}
{{-- ========================================================= --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
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

    toggleBtn.addEventListener('click', () => panel.classList.toggle('open'));

    fetch('/api/trees')
    .then(response => response.json())
    .then(data => {
      allTrees = data;
      exibirArvores(allTrees);
      // 4. Chama a função 'popularFiltros' e passa as árvores E os bairros
      popularFiltros(allTrees, allBairros); 
    })
    .catch(error => console.error('Erro ao carregar árvores:', error));

    function popularFiltros(trees, bairros) {
        const especies = [...new Set(trees.map(t => t.species_name).filter(Boolean))].sort();
        const bairroSelect = document.getElementById('bairro');
        const especieSelect = document.getElementById('especie');

        bairros.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.nome;
            opt.textContent = b.nome;
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
        treeMarkers = {};
        filteredTrees = trees;

        trees.forEach((tree, index) => {
           const trunk = parseFloat(tree.trunk_diameter) || 5;
           const radius = Math.max(5, trunk / 5);

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

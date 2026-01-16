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

    {{-- ESTILOS --}}
   <style>
        /* ... (Mantenha os estilos de tooltip/popup anteriores) ... */
        .bairro-tooltip { background: rgba(0, 0, 0, 0.65); color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; border: none; }
        .leaflet-popup-content-wrapper { padding: 0; overflow: hidden; border-radius: 12px; }
        .leaflet-popup-content { margin: 0; width: 280px !important; }

        .map-filter-toggle {
            position: absolute; top: 10px; right: 10px; z-index: 2000; 
            background: #358054; color: white; padding: 10px 16px; border: none; border-radius: 8px; 
            cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); 
            display: flex; align-items: center; gap: 8px; transition: background 0.2s, transform 0.1s;
        }
        .map-filter-toggle:hover { background: #2d6e4b; }
        .map-filter-toggle:active { transform: scale(0.98); }

        /* PAINEL DE FILTROS CORRIGIDO */
        .map-filter-panel {
            position: absolute; 
            top: 70px; 
            right: 10px; 
            width: 280px; 
            z-index: 2000; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); 
            display: none; 
            flex-direction: column;
            font-family: 'Instrument Sans', sans-serif;
            max-height: calc(100% - 80px); /* Garante que caiba dentro do container do mapa */
            overflow: hidden;
        }
        .map-filter-panel.open { display: flex; animation: slideIn 0.2s ease-out; }
        
        .filter-header {
            padding: 10px 14px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0; /* Impede que o cabeçalho diminua */
        }
        .header-title-box { display: flex; gap: 8px; align-items: center; }
        .header-icon { color: #358054; }
        .header-text h3 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
        .header-text p { margin: 0; font-size: 11px; color: #6b7280; }

        .filter-content {
            padding: 12px 16px;
            overflow-y: auto;
            flex: 1;
            overscroll-behavior: contain;
        }

        .filter-footer {
            padding: 10px 14px;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            border-radius: 0 0 12px 12px;
            flex-shrink: 0; /* Impede que o rodapé diminua ou suma */
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .filter-group { margin-bottom: 10px; }
        .filter-label { font-size: 10px; font-weight: 700; color: #6b7280; margin-bottom: 3px; display: block; text-transform: uppercase; letter-spacing: 0.05em; }
        
        .map-filter-panel input, .map-filter-panel select {
            width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid #d1d5db; 
            font-size: 13px; outline: none; transition: all 0.2s; background-color: #f9fafb; color: #1f2937;
        }
        .map-filter-panel input:focus, .map-filter-panel select:focus {
            border-color: #358054; background-color: #fff; box-shadow: 0 0 0 3px rgba(53, 128, 84, 0.1);
        }

        .admin-divider {
            border-top: 1px dashed #d1d5db; margin: 15px 0; padding-top: 10px; 
            text-align: center; font-size: 10px; font-weight: bold; color: #358054; text-transform: uppercase;
        }

        .btn-actions { display: flex; gap: 10px; }
        .btn-filter { flex: 1; padding: 12px; border-radius: 8px; border: none; background: #358054; color: white; font-weight: 700; cursor: pointer; transition: background 0.2s; font-size: 14px; }
        .btn-filter:hover { background: #2d6e4b; }
        .btn-clear { flex: 1; padding: 12px; border-radius: 8px; border: none; background: #358054; color: white; font-weight: 700; cursor: pointer; transition: background 0.2s; font-size: 14px; }
        .btn-clear:hover { background: #2d6e4b; }

        .filter-status { margin-top: 8px; padding: 5px; font-size: 11px; text-align: center; color: #6b7280; transition: all 0.2s; }
        .filter-status.vazio { background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; color: #991b1b; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 4px; margin-top: 15px; }
    </style>
</head>

<body class="font-sans antialiased welcome-page">
    <div class="min-h-screen">

        {{-- HEADER --}}
        <header class="site-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">
                <div class="flex items-center gap-4 flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-4">
                        <img src="{{ asset('images/Brasao_Verde.png') }}" class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <img src="{{ asset('images/logo.png') }}" class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <h1 class="text-3xl sm:text-4xl font-bold">
                            <span class="text-[#358054]">Árvores de</span>
                            <span class="text-[#a0c520]"> Paracambi</span>
                        </h1>
                    </a>
                </div>

                {{-- MENU --}}
                <div class="flex items-center gap-3 sm:gap-4 relative" x-data="{ open: false }">
                    @if (auth('admin')->check())
                        <a href="{{ route('admin.dashboard') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">Painel Administrativo</a>
                        <form method="POST" action="{{ route('admin.logout') }}">@csrf</form>
                    @elseif(auth()->check())
                        <div class="relative group flex items-center">
                            <a href="{{ route('dashboard') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block px-6 py-3 text-lg rounded-lg">Menu</a>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">@csrf</form>
                    @else
                        <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block">Entrar</a>
                        <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block">Cadastrar</a>

                        {{-- MOBILE MENU --}}
                        <div class="relative inline-block">
                            <button id="guestMenuBtn" class="ml-3 btn bg-[#358054] text-white hover:bg-[#2d6e4b] rounded-lg flex items-center gap-2 transition-all duration-200">
                                Menu
                                <svg id="iconMenu" class="w-6 h-6 transition-all duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />
                                </svg>
                            </button>
                            <div id="guestMenu" class="hidden absolute right-0 mt-2 w-56 bg-[#e8ffe6] rounded-xl shadow-lg z-50 overflow-hidden border border-green-100">
                                <a href="{{ route('contact') }}" class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">Fazer Solicitação</a>
                                <a href="{{ route('contact.myrequests') }}" class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">Minhas Solicitações</a>
                                <a href="{{ route('about') }}" class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">Sobre o Site</a>
                            </div>
                        </div>
                        <script>
                            (function() {
                                const btn = document.getElementById('guestMenuBtn');
                                const menu = document.getElementById('guestMenu');
                                const icon = document.getElementById('iconMenu');
                                let aberto = false;
                                if (!btn || !menu) return;
                                btn.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    menu.classList.toggle('hidden');
                                    aberto = !aberto;
                                    icon.innerHTML = aberto ? `<path d="M6 6l12 12" /><path d="M6 18L18 6" />` : `<path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />`;
                                });
                                window.addEventListener('click', () => {
                                    if (!menu.classList.contains('hidden')) {
                                        menu.classList.add('hidden');
                                        icon.innerHTML = `<path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />`;
                                        aberto = false;
                                    }
                                });
                            })();
                        </script>
                    @endif
                </div>
            </div>
        </header>

        {{-- CONTEÚDO PRINCIPAL --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow p-6 mb-8 relative">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Mapa Interativo</h2>
                <div id="map" class="z-0"></div>
            </div>
        </main>

        <footer class="bg-gray-800 shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
            </div>
        </footer>
    </div>

    {{-- SCRIPTS DO MAPA --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /* CONFIGURAÇÃO */
        const allBairros = @json($bairros);
        const INITIAL_VIEW = [-22.6111, -43.7089];
        const INITIAL_ZOOM = 14;
        const PARACAMBI_BOUNDS = [[-22.7000, -43.8500], [-22.5000, -43.5500]];

        // --- VERIFICAÇÃO DE ADMIN ---
        const isAdmin = @json(auth('admin')->check());
        const editRouteTemplate = "{{ route('admin.trees.edit', 'ID_PLACEHOLDER') }}";

        // Configuração dos Campos Extras do Admin
        const adminFieldsConfig = [
            { id: 'health_status', label: 'Estado da Árvore', key: 'health_status' },
            { id: 'bifurcation_type', label: 'Tipo de Bifurcação', key: 'bifurcation_type' },
            { id: 'stem_balance', label: 'Equilíbrio Fuste', key: 'stem_balance' },
            { id: 'crown_balance', label: 'Equilíbrio Copa', key: 'crown_balance' },
            { id: 'organisms', label: 'Organismos', key: 'organisms' },
            { id: 'target', label: 'Alvo', key: 'target' },
            { id: 'injuries', label: 'Injúrias', key: 'injuries' },
            { id: 'wiring_status', label: 'Estado da Fiação', key: 'wiring_status' },
        ];

        const map = L.map('map', {
            center: INITIAL_VIEW, zoom: INITIAL_ZOOM, minZoom: 13, maxBounds: PARACAMBI_BOUNDS, maxBoundsViscosity: 1.0
        });

        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 22, attribution: 'Google' }).addTo(map);
        L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png", { subdomains: "abcd", maxZoom: 20 }).addTo(map);

        /* UI DO FILTRO */
        const toggleBtn = L.DomUtil.create('button', 'map-filter-toggle');
        toggleBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg> Filtros`;
        map.getContainer().appendChild(toggleBtn);

        // --- CRIAÇÃO DO PAINEL COM CORREÇÃO DE SCROLL ---
        const panel = L.DomUtil.create("div", "map-filter-panel");
        
        // ESTA LINHA CORRIGE O PROBLEMA DO SCROLL NO MAPA
        L.DomEvent.disableClickPropagation(panel);
        L.DomEvent.disableScrollPropagation(panel); 

        // --- MONTAGEM DO HTML ---
        let extraAdminHtml = '';
        if (isAdmin) {
            extraAdminHtml += `<div class="admin-divider">Filtros Avançados (Admin)</div>`;
            adminFieldsConfig.forEach(field => {
                extraAdminHtml += `
                    <div class="filter-group">
                        <label class="filter-label">${field.label}</label>
                        <select id="${field.id}">
                            <option value="">Todos</option>
                        </select>
                    </div>
                `;
            });
        }

        // HTML Completo garantindo que os botões estejam no final
        panel.innerHTML = `
            <div class="filter-header">
                <div class="header-title-box">
                    <div class="header-icon" style="background: #e8f5e9; padding: 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#358054" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                    </div>
                    <div class="header-text">
                        <h3>Explorar Mapa</h3>
                        <p>Filtros</p>
                    </div>
                </div>
                <button id="closePanelBtn" style="background:none; border:none; color:#9ca3af; cursor:pointer; font-size:18px; padding: 4px; line-height: 1;">✕</button>
            </div>

            <div class="filter-content">
                <div class="filter-group">
                    <label class="filter-label">PESQUISAR</label>
                    <input type="text" id="search" placeholder="Nome ou endereço..." autocomplete="off"/>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">BAIRRO</label>
                    <select id="bairro"><option value="">Todos os bairros</option></select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">ESPECIE</label>
                    <select id="especie">
                        <option value="">Todas as espécies</option>
                    </select>
                </div>

                ${extraAdminHtml}
            </div>

            <div class="filter-footer">
                <div class="btn-actions">
                    <button id="limparFiltro" class="btn-clear">Limpar</button>
                    <button id="aplicarFiltro" class="btn-filter">Filtrar</button>
                </div>
                <div id="filterStatus" class="filter-status">Carregando...</div>
            </div>
        `;
        
        map.getContainer().appendChild(panel);

        // Lógica de abrir/fechar
        toggleBtn.addEventListener("click", (e) => { 
            L.DomEvent.stop(e); 
            panel.classList.toggle("open"); 
        });
        
        panel.querySelector('#closePanelBtn').addEventListener("click", (e) => {
            L.DomEvent.stop(e);
            panel.classList.remove("open");
        });

        /* VARIÁVEIS GLOBAIS */
        let currentTrees = [], allTrees = [], filteredTrees = [], treeIndexGlobal = 0, treeMarkers = {};
        let markersLayer = L.layerGroup().addTo(map);
        let bairrosGeoLayer = null;

        /* FETCH DADOS */
        const bairrosIndex = {};
        allBairros.forEach(b => bairrosIndex[b.nome.toUpperCase()] = b.id);

        fetch("/bairros.json").then(r => r.json()).then(geo => {
            const cleanedGeo = { type: "FeatureCollection", features: geo.features.filter(f => f.geometry.type === "Polygon" || f.geometry.type === "MultiPolygon") };
            cleanedGeo.features.forEach(f => { f.properties.id_bairro = bairrosIndex[(f.properties.BAIRRO || "").toUpperCase()] ?? null; });
            bairrosGeoLayer = L.geoJSON(cleanedGeo, {
                style: { color: "#00000020", weight: 1, fillOpacity: 0.02, interactive: false },
                onEachFeature: (feature, layer) => { layer.unbindTooltip(); layer.unbindPopup(); }
            }).addTo(map);
            bairrosGeoLayer.bringToBack();
        });

        fetch("{{ route('trees.data') }}").then(r => r.json()).then(data => {
            allTrees = data;
            popularSelects(allTrees);
            exibirArvores(allTrees);
        });

        /* FUNÇÕES */
        function getColorBySpecies(speciesName, vulgarName) {
            const name = (speciesName || "").toLowerCase();
            const vulgar = (vulgarName || "").toLowerCase();
            const fullName = `${name} ${vulgar}`;

            if (fullName.includes("não identificada")) return "#064e3b";

            // 1. Mapeamento de Cores Base por Palavras-Chave
            let baseHue = 120; // Padrão: Verde
            let saturation = 60;
            let lightness = 45;

            const colorMap = [
                { keys: ['flamboyant', 'vermelho', 'pau-brasil'], hue: 0 },      // Vermelho
                { keys: ['amarelo', 'ipe-amarelo', 'acacia'], hue: 50 },        // Amarelo
                { keys: ['roxo', 'quaresmeira', 'ipe-roxo', 'manaca'], hue: 280 }, // Roxo/Lilás
                { keys: ['rosa', 'ipe-rosa', 'paineira'], hue: 330 },           // Rosa
                { keys: ['branco', 'ipe-branco'], hue: 0, sat: 0, light: 85 },  // Branco (ajuste especial)
                { keys: ['laranja', 'tulipeira'], hue: 30 },                    // Laranja
                { keys: ['azul', 'jacaranda'], hue: 210 },                      // Azul
            ];

            const match = colorMap.find(item => item.keys.some(key => fullName.includes(key)));
            
            if (match) {
                baseHue = match.hue;
                if (match.sat !== undefined) saturation = match.sat;
                if (match.light !== undefined) lightness = match.light;
            }

            // 2. Gerar variação de tonalidade baseada no nome (Hash)
            // Isso garante que Ipê Roxo e Quaresmeira tenham tons de roxo diferentes
            let hash = 0;
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            
            // Ajusta levemente o matiz (hue) em +/- 15 graus e a luminosidade em +/- 10%
            const hueVariation = (hash % 30) - 15;
            const lightVariation = (hash % 20) - 10;

            const finalHue = (baseHue + hueVariation + 360) % 360;
            const finalLight = Math.min(Math.max(lightness + lightVariation, 20), 80);

            return `hsl(${finalHue}, ${saturation}%, ${finalLight}%)`;
        }

        function popularSelects(trees) {
            const especieSelect = document.getElementById("especie");
            const bairroSelect = document.getElementById("bairro");
            
            // 1. Popula Bairros
            allBairros.forEach(b => {
                const opt = document.createElement("option"); opt.value = b.id; opt.textContent = b.nome; bairroSelect.appendChild(opt);
            });

            // 2. Popula Espécies (Nome Vulgar)
            const nomesSet = new Set();
            trees.forEach(t => {
                let nome = t.vulgar_name;
                if (nome && nome.trim() !== "" && nome.toLowerCase() !== "não identificada") {
                    let nomeFormatado = nome.trim();
                    nomeFormatado = nomeFormatado.charAt(0).toUpperCase() + nomeFormatado.slice(1);
                    nomesSet.add(nomeFormatado);
                }
            });
            Array.from(nomesSet).sort().forEach(nome => {
                const opt = document.createElement("option"); opt.value = nome; opt.textContent = nome; especieSelect.appendChild(opt);
            });

            // 3. Popula Filtros de Admin (Dinamicamente)
            if (isAdmin) {
                adminFieldsConfig.forEach(field => {
                    const select = document.getElementById(field.id);
                    if(select) {
                        const valoresUnicos = [...new Set(trees.map(t => t[field.key]).filter(v => v))].sort();
                        valoresUnicos.forEach(valor => {
                            const opt = document.createElement("option");
                            opt.value = valor;
                            opt.textContent = valor;
                            select.appendChild(opt);
                        });
                    }
                });
            }
        }

        function atualizarStatus(count, total) {
            const statusDiv = document.getElementById("filterStatus");
            if (!statusDiv) return;
            statusDiv.className = "filter-status"; 
            if (count === 0) {
                statusDiv.classList.add("vazio");
                statusDiv.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:6px; opacity:0.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg><span>Nenhuma árvore encontrada.</span>`;
            } else if (count === total) {
                statusDiv.innerHTML = `Exibindo todas as <b>${total}</b> árvores.`;
            } else {
                statusDiv.innerHTML = `Encontradas <b>${count}</b> de ${total} árvores.`;
                statusDiv.style.color = "#358054";
            }
        }

        function exibirArvores(trees) {
            markersLayer.clearLayers();
            treeMarkers = {};
            filteredTrees = trees;
            currentTrees = trees;
            treeIndexGlobal = 0;
            atualizarStatus(trees.length, allTrees.length);

            if (trees.length === 0) return;

            const diameters = trees.map(t => parseFloat(t.trunk_diameter) || 0);
            const minDiameter = Math.min(...diameters);
            const maxDiameter = Math.max(...diameters);
            
            function scaleDiameter(d) {
                if (maxDiameter === minDiameter) return 7;
                return 7 + (d - minDiameter) / (maxDiameter - minDiameter) * (15 - 7);
            }

            trees.forEach((tree) => {
                if (!tree.latitude || !tree.longitude) return;
                
                const treeColor = getColorBySpecies(tree.species_name, tree.vulgar_name);
                
                const marker = L.circleMarker([tree.latitude, tree.longitude], {
                    radius: scaleDiameter(parseFloat(tree.trunk_diameter) || 0),
                    color: "#fff", weight: 2, fillColor: treeColor, fillOpacity: 0.85, interactive: true
                }).addTo(markersLayer);

                marker.on('click', () => {
                    currentTrees = filteredTrees;
                    treeIndexGlobal = trees.indexOf(tree);
                    const targetPoint = map.latLngToContainerPoint([tree.latitude, tree.longitude]).add([0, -100]);
                    map.setView(map.containerPointToLatLng(targetPoint), map.getZoom(), { animate: true });
                    marker.bindPopup(criarConteudoPopup(currentTrees, treeIndexGlobal)).openPopup();
                });
                treeMarkers[tree.id] = marker;
            });
        }

        function destacarBairro(bairroId, darZoom = true) {
            if (!bairrosGeoLayer) return;
            bairrosGeoLayer.eachLayer(layer => {
                if (layer.feature.properties.id_bairro == bairroId) {
                    layer.setStyle({ color: "#0084ff", weight: 3, fillOpacity: 0.1 });
                    if (darZoom) map.fitBounds(layer.getBounds(), { padding: [20, 20] });
                } else {
                    layer.setStyle({ color: "#00000020", weight: 1, fillOpacity: 0.02 });
                }
            });
        }

        function aplicarFiltro() {
            // 1. Filtros Padrão
            const bairroVal = document.getElementById("bairro").value;
            const especieVal = document.getElementById("especie").value;
            const buscaVal = document.getElementById("search").value.toLowerCase().trim();

            // 2. Filtros Admin
            const adminFilters = {};
            if (isAdmin) {
                adminFieldsConfig.forEach(field => {
                    const el = document.getElementById(field.id);
                    if (el && el.value) {
                        adminFilters[field.key] = el.value;
                    }
                });
            }

            const filtradas = allTrees.filter(tree => {
                const okBairro = bairroVal ? tree.bairro_id == bairroVal : true;
                const nomeVulgarArvore = (tree.vulgar_name || "").toLowerCase().trim();
                const okEspecie = especieVal ? nomeVulgarArvore === especieVal.toLowerCase().trim() : true;
                
                let okBusca = true;
                if (buscaVal.length > 0) {
                    const nomeGeral = (tree.species_name || "").toLowerCase();
                    const end = (tree.address || "").toLowerCase();
                    okBusca = nomeGeral.includes(buscaVal) || end.includes(buscaVal);
                }

                let okAdmin = true;
                if (isAdmin) {
                    for (const [key, val] of Object.entries(adminFilters)) {
                        if ((tree[key] || "") != val) {
                            okAdmin = false;
                            break;
                        }
                    }
                }

                return okBairro && okEspecie && okBusca && okAdmin;
            });

            exibirArvores(filtradas);
            if (filtradas.length > 0) {
                if (filtradas.length === 1) { map.setView([filtradas[0].latitude, filtradas[0].longitude], 18); }
                else { map.fitBounds(L.latLngBounds(filtradas.map(t => [t.latitude, t.longitude])), { padding: [50, 50], maxZoom: 17 }); }
            }

            if (bairroVal && filtradas.length > 0) destacarBairro(bairroVal, false);
            else {
                if (bairrosGeoLayer) bairrosGeoLayer.eachLayer(l => l.setStyle({ color: "#00000020", weight: 1, fillOpacity: 0.02 }));
                const adminEmpty = isAdmin ? Object.keys(adminFilters).length === 0 : true;
                if (!especieVal && !buscaVal && !bairroVal && adminEmpty) map.setView(INITIAL_VIEW, INITIAL_ZOOM);
            }
        }

        function criarConteudoPopup(listaArvores, indexInicial) {
            const container = document.createElement('div');
            container.className = 'p-4 font-sans relative bg-white';
            let indexAtual = indexInicial;

            function mostrarArvoreAtual() {
                const tree = listaArvores[indexAtual];
                const targetPoint = map.latLngToContainerPoint([tree.latitude, tree.longitude]).add([0, -100]);
                map.setView(map.containerPointToLatLng(targetPoint), map.getZoom(), { animate: true });
                const marker = treeMarkers[tree.id];
                if (marker) marker.bindPopup(container).openPopup();
            }

            function render() {
                const tree = listaArvores[indexAtual];
                const total = listaArvores.length;
                
                // --- LÓGICA INTELIGENTE DO NOME ---
                let nomeExibicao = tree.vulgar_name || 'Não Identificada';
                
                // Normaliza para minúsculas para facilitar a comparação
                let nomeCheck = nomeExibicao.toLowerCase().trim();

                // Verifica se é "não identificada" (com ou sem acento)
                if (nomeCheck.includes('não identificada') || nomeCheck.includes('nao identificada')) {
                    // Se tiver algo escrito no campo 'no_species_case', usa ele
                    if (tree.no_species_case && tree.no_species_case.trim() !== "") {
                        nomeExibicao = tree.no_species_case;
                    }
                }

                // --- BOTÃO EDITAR ---
                let adminButton = '';
                if (isAdmin) {
                    const editUrl = editRouteTemplate.replace('ID_PLACEHOLDER', tree.id);
                    adminButton = `
                        <a href="${editUrl}" 
                           style="color: white !important;"
                           class="flex-1 ml-2 group flex items-center justify-center bg-blue-600 hover:bg-blue-700 
                                  !text-white border border-blue-600 rounded-lg px-3 py-2 transition-all duration-200 decoration-0">
                            <span class="text-xs font-bold text-white">Editar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                    `;
                }

                container.innerHTML = `
                <div class="flex items-center justify-between mb-3 bg-gray-100 rounded-lg p-1 select-none">
                    <button id="btn-prev" class="p-1 text-gray-600 hover:text-green-700 hover:bg-white rounded transition cursor-pointer">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">${indexAtual + 1} de ${total}</span>
                    <button id="btn-next" class="p-1 text-gray-600 hover:text-green-700 hover:bg-white rounded transition cursor-pointer">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>

                <div class="mb-3">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Espécie/Nome Popular:</p>
                    
                    {{-- NOME DA ÁRVORE (CORRIGIDO) --}}
                    <h3 class="font-bold text-[#358054] text-sm leading-tight mb-2">
                        ${nomeExibicao}
                    </h3>

                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Localização:</p>
                    <div class="text-xs text-gray-600 pb-2 border-b border-gray-100 leading-snug">
                        <p class="mb-1">${tree.address},  <strong>${tree.bairro_nome  || 'Rua não informada'}</p>
                        <p class="font-semibold text-gray-500">
                        </p>
                    </div>
                </div>
                
                <div class="flex w-full">
                    <a href="/trees/${tree.id}" class="flex-1 group flex items-center justify-between bg-[#f0fdf4] hover:bg-[#dcfce7] border border-[#bbf7d0] rounded-lg px-3 py-2 transition-all duration-200 decoration-0">
                        <span class="text-xs font-bold text-[#166534]">Ver detalhes</span>
                        <svg class="w-4 h-4 text-[#166534] opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    ${adminButton}
                </div>`;
                
                container.querySelector('#btn-prev').onclick = (e) => { e.stopPropagation(); indexAtual = (indexAtual - 1 + total) % total; render(); mostrarArvoreAtual(); };
                container.querySelector('#btn-next').onclick = (e) => { e.stopPropagation(); indexAtual = (indexAtual + 1) % total; render(); mostrarArvoreAtual(); };
            }
            render();
            return container;
        }

        // Binds dos botões de ação (agora garantidos que existem)
        // OBS: Usamos delegation ou garantimos que os elementos existam após o innerHTML
        // Como o innerHTML é setado logo no início, os getElementById vão funcionar.
        setTimeout(() => {
            document.getElementById("aplicarFiltro").addEventListener("click", aplicarFiltro);
            document.getElementById("search").addEventListener("keyup", (e) => { if (e.key === 'Enter') aplicarFiltro(); });
            document.getElementById("limparFiltro").addEventListener("click", () => {
                document.getElementById("bairro").value = "";
                document.getElementById("especie").value = "";
                document.getElementById("search").value = "";
                if (isAdmin) {
                    adminFieldsConfig.forEach(f => {
                        const el = document.getElementById(f.id);
                        if(el) el.value = "";
                    });
                }
                exibirArvores(allTrees);
                if (bairrosGeoLayer) bairrosGeoLayer.eachLayer(l => l.setStyle({ color: "#00000020", weight: 1, fillOpacity: 0.02 }));
                map.setView(INITIAL_VIEW, INITIAL_ZOOM);
            });
        }, 100);

    </script>
</body>
</html>
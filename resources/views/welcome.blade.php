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
        .bairro-tooltip { background: rgba(0, 0, 0, 0.65); color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; border: none; }
        .leaflet-popup-content-wrapper { padding: 0; overflow: hidden; border-radius: 12px; }
        .leaflet-popup-content { margin: 0; width: 280px !important; }
        
        .map-filter-toggle {
            position: absolute; top: 10px; right: 10px; z-index: 1000; background: #358054; color: white; padding: 10px 16px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); display: flex; align-items: center; gap: 8px; transition: background 0.2s, transform 0.1s;
        }
        .map-filter-toggle:hover { background: #2d6e4b; }
        .map-filter-toggle:active { transform: scale(0.98); }

        .map-filter-panel {
            position: absolute; top: 60px; right: 10px; width: 280px; z-index: 1000; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); display: none; animation: slideIn 0.2s ease-out; font-family: 'Instrument Sans', sans-serif;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .map-filter-panel.open { display: block; }

        .filter-group { margin-bottom: 14px; }
        .filter-label { font-size: 11px; font-weight: 700; color: #6b7280; margin-bottom: 4px; display: block; text-transform: uppercase; letter-spacing: 0.05em; }
        .map-filter-panel input, .map-filter-panel select {
            width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 14px; outline: none; transition: all 0.2s; background-color: #f9fafb; color: #1f2937;
        }
        .map-filter-panel input:focus, .map-filter-panel select:focus {
            border-color: #358054; background-color: #fff; box-shadow: 0 0 0 3px rgba(53, 128, 84, 0.1);
        }

        .btn-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-filter { flex: 1; padding: 10px; border-radius: 6px; border: none; background: #358054; color: white; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-filter:hover { background: #2d6e4b; }
        .btn-clear { flex: 1; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; background: #f3f4f6; color: #374151; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-clear:hover { background: #e5e7eb; }

        .filter-status { margin-top: 15px; padding: 10px; border-top: 1px solid #e5e7eb; font-size: 12px; text-align: center; color: #6b7280; transition: all 0.2s; }
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

        // --- VERIFICAÇÃO DE ADMIN (PARA O BOTÃO EDITAR) ---
        const isAdmin = @json(auth('admin')->check());
        const editRouteTemplate = "{{ route('admin.trees.edit', 'ID_PLACEHOLDER') }}";

        const map = L.map('map', {
            center: INITIAL_VIEW, zoom: INITIAL_ZOOM, minZoom: 13, maxBounds: PARACAMBI_BOUNDS, maxBoundsViscosity: 1.0
        });

        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 22, attribution: 'Google' }).addTo(map);
        L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png", { subdomains: "abcd", maxZoom: 20 }).addTo(map);

        /* UI DO FILTRO */
        const toggleBtn = L.DomUtil.create('button', 'map-filter-toggle');
        toggleBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg> Filtros`;
        map.getContainer().appendChild(toggleBtn);

        const panel = L.DomUtil.create("div", "map-filter-panel");
        L.DomEvent.disableClickPropagation(panel);

        // --- PAINEL DE FILTROS (TEXTOS CORRIGIDOS) ---
        panel.innerHTML = `
            <div class="filter-header">
                <div class="header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg></div>
                <div class="header-text"><h3>Explorar Mapa</h3><p>Encontre Árvores em Paracambi</p></div>
            </div>
            <div class="filter-group"><label class="filter-label">Pesquisar</label><input type="text" id="search" placeholder="Nome ou endereço..." autocomplete="off"/></div>
            
            <div class="filter-group">
                <label class="filter-label">Bairro</label>
                <select id="bairro"><option value="">Todos os bairros</option></select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Nome Comum / Espécie</label>
                <select id="especie">
                    <option value="">Todas as espécies</option>
                </select>
            </div>

            <div class="btn-actions"><button id="limparFiltro" class="btn-clear">Limpar</button><button id="aplicarFiltro" class="btn-filter">Filtrar</button></div>
            <div id="filterStatus" class="filter-status">Carregando...</div>
        `;
        map.getContainer().appendChild(panel);

        toggleBtn.addEventListener("click", (e) => { L.DomEvent.stop(e); panel.classList.toggle("open"); });

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
        function popularSelects(trees) {
            const especieSelect = document.getElementById("especie");
            const bairroSelect = document.getElementById("bairro");
            
            allBairros.forEach(b => {
                const opt = document.createElement("option"); opt.value = b.id; opt.textContent = b.nome; bairroSelect.appendChild(opt);
            });

            // LÓGICA FILTRO: NOME VULGAR
            const nomesSet = new Set();
            trees.forEach(t => {
                let nome = t.vulgar_name;
                // Filtra apenas nomes vulgares válidos
                if (nome && nome.trim() !== "" && nome.toLowerCase() !== "não identificada") {
                    let nomeFormatado = nome.trim();
                    nomeFormatado = nomeFormatado.charAt(0).toUpperCase() + nomeFormatado.slice(1);
                    nomesSet.add(nomeFormatado);
                }
            });

            Array.from(nomesSet).sort().forEach(nome => {
                const opt = document.createElement("option"); 
                opt.value = nome; 
                opt.textContent = nome; 
                especieSelect.appendChild(opt);
            });
        }

        function atualizarStatus(count, total) {
            const statusDiv = document.getElementById("filterStatus");
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
                
                const marker = L.circleMarker([tree.latitude, tree.longitude], {
                    radius: scaleDiameter(parseFloat(tree.trunk_diameter) || 0),
                    color: "#fff", weight: 2, fillColor: tree.color_code || "#358054", fillOpacity: 0.85, interactive: true
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
            const bairroVal = document.getElementById("bairro").value;
            const especieVal = document.getElementById("especie").value;
            const buscaVal = document.getElementById("search").value.toLowerCase().trim();

            const filtradas = allTrees.filter(tree => {
                const okBairro = bairroVal ? tree.bairro_id == bairroVal : true;
                
                // Filtro atualizado para usar 'vulgar_name'
                const nomeVulgarArvore = (tree.vulgar_name || "").toLowerCase().trim();
                const okEspecie = especieVal ? nomeVulgarArvore === especieVal.toLowerCase().trim() : true;
                
                let okBusca = true;
                if (buscaVal.length > 0) {
                    const nomeGeral = (tree.species_name || "").toLowerCase();
                    const end = (tree.address || "").toLowerCase();
                    okBusca = nomeGeral.includes(buscaVal) || end.includes(buscaVal);
                }
                return okBairro && okEspecie && okBusca;
            });

            exibirArvores(filtradas);
            if (filtradas.length > 0) {
                if (filtradas.length === 1) { map.setView([filtradas[0].latitude, filtradas[0].longitude], 18); }
                else { map.fitBounds(L.latLngBounds(filtradas.map(t => [t.latitude, t.longitude])), { padding: [50, 50], maxZoom: 17 }); }
            }

            if (bairroVal && filtradas.length > 0) destacarBairro(bairroVal, false);
            else {
                if (bairrosGeoLayer) bairrosGeoLayer.eachLayer(l => l.setStyle({ color: "#00000020", weight: 1, fillOpacity: 0.02 }));
                if (!especieVal && !buscaVal && !bairroVal) map.setView(INITIAL_VIEW, INITIAL_ZOOM);
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

                // --- LÓGICA DO BOTÃO EDITAR PARA ADMIN ---
                let adminButton = '';
                if (isAdmin) {
                    // Substitui o placeholder pelo ID real da árvore
                    const editUrl = editRouteTemplate.replace('ID_PLACEHOLDER', tree.id);
                    
                    adminButton = `
                        <a href="${editUrl}" 
                           class="flex-1 ml-2 group flex items-center justify-center bg-blue-600 hover:bg-blue-700 
                                  text-white border border-blue-600 rounded-lg px-3 py-2 transition-all duration-200 decoration-0">
                            <span class="text-xs font-bold">Editar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Espécie / Nome Comum:</p>
                    <h3 class="font-bold text-[#358054] text-sm leading-tight mb-2">${tree.species_name}</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Endereço:</p>
                    <p class="text-xs text-gray-600 pb-2 border-b border-gray-100 leading-snug">${tree.address || 'Localização não informada'}</p>
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

        document.getElementById("aplicarFiltro").addEventListener("click", aplicarFiltro);
        document.getElementById("search").addEventListener("keyup", (e) => { if (e.key === 'Enter') aplicarFiltro(); });
        document.getElementById("limparFiltro").addEventListener("click", () => {
            document.getElementById("bairro").value = "";
            document.getElementById("especie").value = "";
            document.getElementById("search").value = "";
            exibirArvores(allTrees);
            if (bairrosGeoLayer) bairrosGeoLayer.eachLayer(l => l.setStyle({ color: "#00000020", weight: 1, fillOpacity: 0.02 }));
            map.setView(INITIAL_VIEW, INITIAL_ZOOM);
        });
    </script>
</body>
</html>
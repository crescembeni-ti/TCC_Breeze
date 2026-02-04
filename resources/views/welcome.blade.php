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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- ESTILOS --}}
    <style>
        /* Estilos Gerais */
        html, body {
            overflow-x: hidden;
            width: 100%;
        }
        .welcome-page {
            max-width: 100vw;
        }
        .bairro-tooltip { background: rgba(0, 0, 0, 0.65); color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; border: none; }
        .leaflet-popup-content-wrapper { padding: 0; overflow: hidden; border-radius: 12px; }
        .leaflet-popup-content { margin: 0; width: 280px !important; }

        /* Botão de Filtros */
        .map-filter-toggle {
            position: absolute; top: 10px; right: 10px; z-index: 2000; 
            background: #358054; color: white; padding: 8px 12px; border: none; border-radius: 8px; 
            cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); 
            display: flex; align-items: center; gap: 6px; transition: background 0.2s, transform 0.1s;
            font-size: 13px;
        }
        .map-filter-toggle:hover { background: #2d6e4b; }
        .map-filter-toggle:active { transform: scale(0.98); }

        /* Painel de Filtros */
        .map-filter-panel {
            position: absolute; top: 70px; right: 10px; width: 320px; 
            z-index: 2000; background: white; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); display: none; 
            flex-direction: column; font-family: 'Instrument Sans', sans-serif;
            max-height: calc(100% - 60px); 
            overflow-y: auto; 
        }
        .map-filter-panel.open { display: flex; animation: slideIn 0.2s ease-out; }
        
        .filter-header { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .header-title-box { display: flex; gap: 8px; align-items: center; }
        .header-icon { color: #358054; }
        .header-text h3 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
        .header-text p { margin: 0; font-size: 11px; color: #6b7280; }

        .filter-content { padding: 12px 16px; flex-shrink: 0; }
        .filter-footer { padding: 10px 14px 20px 14px; background: #f9fafb; border-top: 1px solid #f3f4f6; border-radius: 0 0 12px 12px; flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .filter-group { margin-bottom: 10px; }
        .filter-label { font-size: 10px; font-weight: 700; color: #6b7280; margin-bottom: 3px; display: block; text-transform: uppercase; letter-spacing: 0.05em; }
        .map-filter-panel input, .map-filter-panel select { width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 13px; outline: none; transition: all 0.2s; background-color: #f9fafb; color: #1f2937; }
        .map-filter-panel input:focus, .map-filter-panel select:focus { border-color: #358054; background-color: #fff; box-shadow: 0 0 0 3px rgba(53, 128, 84, 0.1); }

        .admin-divider { border-top: 1px dashed #d1d5db; margin: 15px 0; padding-top: 10px; text-align: center; font-size: 10px; font-weight: bold; color: #358054; text-transform: uppercase; }
        .btn-actions { display: flex; gap: 8px; margin-bottom: 4px; }
        .btn-filter { flex: 1.5; padding: 8px; border-radius: 8px; border: none; background: #358054; color: white; font-weight: 700; cursor: pointer; transition: background 0.2s; font-size: 13px; }
        .btn-filter:hover { background: #2d6e4b; }
        .btn-clear { flex: 1; padding: 8px; border-radius: 8px; border: none; background: #9ca3af; color: white; font-weight: 700; cursor: pointer; transition: background 0.2s; font-size: 13px; }
        .btn-clear:hover { background: #6b7280; }
        .btn-download { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #358054; background: white; color: #358054; font-weight: 700; cursor: pointer; transition: all 0.2s; font-size: 13px; display: flex; justify-content: center; align-items: center; gap: 6px; }
        .btn-download:hover { background: #f0fdf4; }

        .filter-status { margin-top: 4px; padding: 4px; font-size: 12px; font-weight: 600; text-align: center; color: #358054; transition: all 0.2s; }
        .filter-status.vazio { background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; color: #991b1b; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 4px; margin-top: 15px; }

        /* LEGENDA FLUTUANTE (DADOS DO MAPA) */
        .map-legend {
            position: absolute; top: 80px; left: 10px; z-index: 1000;
            background: rgba(255, 255, 255, 0.95); padding: 10px 12px;
            border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            font-family: 'Instrument Sans', sans-serif; font-size: 11px; color: #374151;
            display: none; min-width: 140px; border: 1px solid #e5e7eb; animation: fadeIn 0.3s ease;
        }
        .legend-title { font-weight: 700; margin-bottom: 6px; color: #358054; text-transform: uppercase; font-size: 10px; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px; }
        .legend-item { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
        .legend-color { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.1); flex-shrink: 0; }
        @keyframes fadeIn { from { opacity: 0; transform: translateX(-5px); } to { opacity: 1; transform: translateX(0); } }

    /* CAIXA DE ESTATÍSTICAS DE SOMBREAMENTO */
    .shading-stats-box-external {
        background: linear-gradient(135deg, #358054 0%, #2d6e4b 100%);
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        font-family: 'Instrument Sans', sans-serif;
        color: white;
        width: 220px;
        min-width: 220px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: slideInLeft 0.4s ease-out;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .shading-stats-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        gap: 6px;
        color: rgba(255, 255, 255, 0.95);
    }

    .shading-stat-item {
        margin-bottom: 10px;
        padding: 8px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        border-left: 3px solid #a0c520;
    }

    .shading-stat-item:last-child {
        margin-bottom: 0;
    }

    .shading-stat-label {
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 3px;
    }

    .shading-stat-value {
        font-size: 20px;
        font-weight: 700;
        color: white;
        line-height: 1;
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .shading-stat-unit {
        font-size: 11px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.85);
    }

    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* LEGENDA DE MARGEM DE ERRO */
    .map-margin-note {
        position: relative;
        margin-top: 10px;
        background: rgba(255, 255, 255, 0.95);
        padding: 8px 34px 8px 16px; 
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        color: #b45309;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        border: 1px solid #fcd34d;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        width: 100%;
    }

    .note-close-btn {
        position: absolute;
        right: 8px;
        border: none;
        background: transparent;
        color: #b45309; 
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    @media (max-width: 640px) {
        .map-filter-toggle {
            top: 5px !important;
            right: 5px !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
        }
        .map-filter-panel {
            width: 94% !important; 
            right: 3% !important;  
            max-height: 70% !important;
            top: 45px !important;
        }
        .leaflet-popup-content {
            width: 240px !important; 
        }
        .map-legend {
            top: auto !important;
            bottom: 10px !important;
            left: 5px !important;
            max-width: 130px;
            padding: 6px !important;
            font-size: 10px !important;
            display: block !important;
        }
        .shading-stats-box-external {
            width: 100% !important;
            min-width: unset !important;
            padding: 12px !important;
            margin-bottom: 10px;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            justify-content: space-between !important;
            order: 2; 
        }
        .shading-stats-title {
            width: 100% !important;
            margin-bottom: 6px !important;
            padding-bottom: 4px !important;
            font-size: 10px !important;
        }
        .shading-stat-item {
            flex: 1 !important;
            min-width: 120px !important;
            margin-bottom: 0 !important;
            padding: 6px 10px !important;
        }
        .map-margin-note {
            font-size: 11px !important;
            padding: 8px 30px 8px 12px !important;
            margin-top: 10px !important;
            order: 3;
        }
        #map {
            height: 50vh !important;
            order: 1;
        }
    }
    </style>
</head>

<body class="font-sans antialiased welcome-page">
    <div class="min-h-screen flex flex-col"> 

        {{-- HEADER COMPACTO --}}
        <header class="site-header flex-shrink-0"> 
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center flex-wrap gap-4">
                <div class="flex items-center gap-3 flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 sm:h-14 sm:w-14 object-contain">
                        <h1 class="text-xl sm:text-2xl font-bold leading-tight">
                            <span class="text-[#358054]">Árvores de</span>
                            <span class="text-[#a0c520]"> Paracambi</span>
                        </h1>
                    </a>
                </div>

                <div class="flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-6">
                    <div class="flex items-center gap-2 sm:gap-4 relative order-2 sm:order-1">
                        @if (auth('admin')->check())
                            <a href="{{ route('admin.dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Painel</a>
                        @elseif (auth('analyst')->check())
                            <a href="{{ route('analyst.dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Painel</a>
                        @elseif(auth()->check())
                            <a href="{{ route('dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Menu</a>
                        @else
                            <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block text-sm py-1.5 px-3">Entrar</a>
                            <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block text-sm py-1.5 px-3">Cadastrar</a>

                            <div class="relative inline-block sm:hidden" x-data="{ open: false }">
                                <button @click="open = !open" class="btn bg-[#358054] text-white hover:bg-[#2d6e4b] rounded-lg flex items-center gap-1.5 transition-all duration-200 py-1.5 px-3 text-xs">
                                    Menu
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                        <path x-show="!open" d="M4 6h16M4 12h16M4 18h16" />
                                        <path x-show="open" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl z-[5000] overflow-hidden border border-gray-200">
                                    <div class="p-2 flex flex-col gap-1">
                                        <a href="{{ route('login') }}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold text-white bg-[#358054] hover:bg-[#2d6e4b] rounded-lg transition-colors">Entrar</a>
                                        <a href="{{ route('register') }}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold text-gray-800 bg-[#a0c520] hover:bg-[#8eb01c] rounded-lg transition-colors">Cadastrar</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <img src="{{ asset('images/nova_logo.png') }}" alt="Logo Prefeitura" class="header-logo-right hover:opacity-90 transition-opacity order-1 sm:order-2" style="height: 3.5rem; width: auto;"> 
                </div>
            </div>
        </header>

        <main class="flex-grow max-w-[98%] mx-auto px-2 sm:px-4 lg:px-6 py-4 sm:py-8 w-full flex flex-col">
            
            @if (session('success'))
                <div id="success-alert" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex items-center justify-between shadow-md" role="alert">
                    <div class="flex items-center"><strong class="font-bold mr-2">Sucesso!</strong><span class="block sm:inline">{{ session('success') }}</span></div>
                    @if (session('new_tree_id'))
                        <button onclick="focarNovaArvore({{ session('new_tree_id') }})" class="bg-[#358054] hover:bg-[#2d6e4b] text-white font-bold py-1 px-4 rounded text-xs transition-colors shadow-sm ml-4">Ver no Mapa</button>
                    @endif
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 sm:p-4 mb-6 relative w-full">
                
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 pl-1">Mapa Interativo</h2>
                
                <div class="flex flex-col md:flex-row gap-4">
                    
                    {{-- MAPA --}}
                    <div id="map" class="z-0 flex-1 rounded-xl h-[50vh] md:h-[75vh] relative border border-gray-200 shadow-inner"></div>

                    {{-- ESTATÍSTICAS --}}
                    <div class="flex flex-col gap-3">
                        <div class="shading-stats-box-external">
                            <div class="shading-stats-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="5"></circle>
                                    <line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                    <line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                                </svg>
                                Sombreamento
                            </div>
                            
                            <div class="shading-stat-item">
                                <div class="shading-stat-label">Árvores</div>
                                <div class="shading-stat-value"><span id="stats-tree-count">0</span></div>
                            </div>
                            
                            <div class="shading-stat-item">
                                <div class="shading-stat-label">Área Total</div>
                                <div class="shading-stat-value"><span id="stats-shading-area">0</span><span class="shading-stat-unit">m²</span></div>
                            </div>
                        </div>

                        {{-- AVISO --}}
                        <div x-data="{ showNote: true }" x-show="showNote" x-transition.opacity class="map-margin-note">
                            <span class="pr-2">⚠️ Localização aproximada devido à margem de erro das coordenadas.</span>
                            <button @click="showNote = false" class="note-close-btn" title="Fechar">×</button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </main>

        <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p class="text-xs text-gray-500">© {{ date('Y') }} Árvores de Paracambi - Secretaria de Meio Ambiente</p>
            </div>
        </footer>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const INITIAL_VIEW = [-22.6080, -43.7120];
        const INITIAL_ZOOM = 15;
        const map = L.map('map', { zoomControl: false, attributionControl: true }).setView(INITIAL_VIEW, INITIAL_ZOOM);

        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 22, attribution: 'Google' }).addTo(map);
        L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png", { subdomains: "abcd", maxZoom: 20 }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        const toggleBtn = L.DomUtil.create('button', 'map-filter-toggle');
        toggleBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg> Filtros';
        map.getContainer().appendChild(toggleBtn);

        const panel = L.DomUtil.create("div", "map-filter-panel");
        panel.innerHTML = `
            <div class="filter-header">
                <div class="header-title-box"><div class="header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg></div><div class="header-text"><h3>Filtros</h3></div></div>
                <button onclick="document.querySelector('.map-filter-panel').classList.remove('open')"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="filter-content">
                <div class="filter-group"><label class="filter-label">Buscar</label><input type="text" id="filter-search" placeholder="Espécie..."></div>
                <div class="filter-group"><label class="filter-label">Bairro</label><select id="filter-bairro"><option value="">Todos</option></select></div>
                <div class="filter-group"><label class="filter-label">Estado</label><select id="filter-health"><option value="">Todos</option><option value="Ótimo">Ótimo</option><option value="Bom">Bom</option><option value="Regular">Regular</option><option value="Ruim">Ruim</option><option value="Morto">Morto</option></select></div>
            </div>
            <div class="filter-footer"><div class="btn-actions"><button class="btn-filter" id="apply-filters">Filtrar</button><button class="btn-clear" id="clear-filters">Limpar</button></div><div id="filter-status" class="filter-status"></div></div>
        `;
        map.getContainer().appendChild(panel);
        toggleBtn.onclick = () => panel.classList.toggle('open');

        const legendDiv = L.DomUtil.create("div", "map-legend");
        legendDiv.innerHTML = `<div class="legend-title">Sombreamento</div><div class="legend-item"><div class="legend-color" style="background: rgba(160, 197, 32, 0.4)"></div><span>Área Projetada</span></div>`;
        map.getContainer().appendChild(legendDiv);

        let allTrees = [];
        let markersLayer = L.layerGroup().addTo(map);
        let polygonsLayer = L.layerGroup().addTo(map);

        function updateStats(trees) {
            const countEl = document.getElementById('stats-tree-count');
            const areaEl = document.getElementById('stats-shading-area');
            if (!countEl || !areaEl) return;
            let totalArea = 0;
            trees.forEach(tree => {
                const d = parseFloat(tree.canopy_diameter) || 0;
                if (d > 0) totalArea += Math.PI * Math.pow(d / 2, 2);
            });
            countEl.innerText = trees.length.toLocaleString('pt-BR');
            areaEl.innerText = totalArea.toLocaleString('pt-BR', { maximumFractionDigits: 1 });
        }

        async function loadTrees() {
            try {
                const response = await fetch("{{ route('trees.data') }}");
                allTrees = await response.json();
                const bairros = [...new Set(allTrees.map(t => t.bairro).filter(b => b))].sort();
                const bSelect = document.getElementById('filter-bairro');
                bairros.forEach(b => bSelect.add(new Option(b, b)));
                renderMarkers(allTrees);
                updateStats(allTrees);
            } catch (e) { console.error(e); }
        }

        function renderMarkers(trees) {
            markersLayer.clearLayers();
            polygonsLayer.clearLayers();
            trees.forEach(tree => {
                const d = parseFloat(tree.canopy_diameter) || 0;
                if (d > 0) L.circle([tree.latitude, tree.longitude], { radius: d / 2, color: '#a0c520', weight: 1, opacity: 0.6, fillColor: '#a0c520', fillOpacity: 0.3, interactive: false }).addTo(polygonsLayer);
                L.circleMarker([tree.latitude, tree.longitude], { radius: 6, fillColor: "#358054", color: "#fff", weight: 2, opacity: 1, fillOpacity: 0.9 })
                .bindPopup(`<div class="p-2"><h4 class="font-bold border-b mb-1">${tree.species_name || 'N/I'}</h4><p class="text-xs">Bairro: ${tree.bairro || '-'}</p><a href="/trees/${tree.id}" class="mt-2 block text-center bg-[#358054] text-white py-1 rounded font-bold text-xs">Ver</a></div>`)
                .addTo(markersLayer);
            });
        }

        document.getElementById('apply-filters').onclick = () => {
            const s = document.getElementById('filter-search').value.toLowerCase();
            const b = document.getElementById('filter-bairro').value;
            const h = document.getElementById('filter-health').value;
            const f = allTrees.filter(t => (!s || (t.species_name && t.species_name.toLowerCase().includes(s))) && (!b || t.bairro === b) && (!h || t.health_condition === h));
            renderMarkers(f);
            updateStats(f);
            document.getElementById('filter-status').innerText = `${f.length} encontradas`;
            if (window.innerWidth < 640) panel.classList.remove('open');
        };

        document.getElementById('clear-filters').onclick = () => {
            ['filter-search', 'filter-bairro', 'filter-health'].forEach(id => document.getElementById(id).value = '');
            renderMarkers(allTrees);
            updateStats(allTrees);
            document.getElementById('filter-status').innerText = '';
            map.setView(INITIAL_VIEW, INITIAL_ZOOM);
        };

        loadTrees();
        map.on('zoomend', () => { legendDiv.style.display = map.getZoom() >= 16 ? 'block' : 'none'; });
    </script>
</body>
</html>

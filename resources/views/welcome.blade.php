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
        .bairro-tooltip { background: rgba(0, 0, 0, 0.65); color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; border: none; }
        .leaflet-popup-content-wrapper { padding: 0; overflow: hidden; border-radius: 12px; }
        .leaflet-popup-content { margin: 0; width: 280px !important; }

        /* Botão de Filtros */
        .map-filter-toggle {
            position: absolute; top: 10px; right: 10px; z-index: 2000; 
            background: #358054; color: white; padding: 10px 16px; border: none; border-radius: 8px; 
            cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); 
            display: flex; align-items: center; gap: 8px; transition: background 0.2s, transform 0.1s;
        }
        .map-filter-toggle:hover { background: #2d6e4b; }
        .map-filter-toggle:active { transform: scale(0.98); }

        /* Painel de Filtros */
        .map-filter-panel {
            position: absolute; top: 70px; right: 10px; width: 280px; 
            z-index: 2000; background: white; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); display: none; 
            flex-direction: column; font-family: 'Instrument Sans', sans-serif;
            max-height: calc(100% - 80px); overflow: hidden;
        }
        .map-filter-panel.open { display: flex; animation: slideIn 0.2s ease-out; }
        
        .filter-header { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .header-title-box { display: flex; gap: 8px; align-items: center; }
        .header-icon { color: #358054; }
        .header-text h3 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
        .header-text p { margin: 0; font-size: 11px; color: #6b7280; }

        .filter-content { padding: 12px 16px; overflow-y: auto; flex: 1; overscroll-behavior: contain; }
        .filter-footer { padding: 10px 14px; background: #f9fafb; border-top: 1px solid #f3f4f6; border-radius: 0 0 12px 12px; flex-shrink: 0; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .filter-group { margin-bottom: 10px; }
        .filter-label { font-size: 10px; font-weight: 700; color: #6b7280; margin-bottom: 3px; display: block; text-transform: uppercase; letter-spacing: 0.05em; }
        .map-filter-panel input, .map-filter-panel select { width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 13px; outline: none; transition: all 0.2s; background-color: #f9fafb; color: #1f2937; }
        .map-filter-panel input:focus, .map-filter-panel select:focus { border-color: #358054; background-color: #fff; box-shadow: 0 0 0 3px rgba(53, 128, 84, 0.1); }

        .admin-divider { border-top: 1px dashed #d1d5db; margin: 15px 0; padding-top: 10px; text-align: center; font-size: 10px; font-weight: bold; color: #358054; text-transform: uppercase; }
        .btn-actions { display: flex; gap: 10px; margin-bottom: 8px; }
        .btn-filter { flex: 1; padding: 10px; border-radius: 8px; border: none; background: #358054; color: white; font-weight: 700; cursor: pointer; transition: background 0.2s; font-size: 13px; }
        .btn-filter:hover { background: #2d6e4b; }
        .btn-clear { flex: 1; padding: 10px; border-radius: 8px; border: none; background: #9ca3af; color: white; font-weight: 700; cursor: pointer; transition: background 0.2s; font-size: 13px; }
        .btn-clear:hover { background: #6b7280; }
        .btn-download { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #358054; background: white; color: #358054; font-weight: 700; cursor: pointer; transition: all 0.2s; font-size: 13px; display: flex; justify-content: center; align-items: center; gap: 6px; }
        .btn-download:hover { background: #f0fdf4; }

        .filter-status { margin-top: 8px; padding: 5px; font-size: 11px; text-align: center; color: #6b7280; transition: all 0.2s; }
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

    /* LEGENDA DE MARGEM DE ERRO COM BOTÃO FECHAR */
    .map-margin-note {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        padding: 8px 34px 8px 16px; 
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        color: #b45309;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        border: 1px solid #fcd34d;
        white-space: nowrap;
        pointer-events: auto; 
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
    }

    .note-close-btn {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #b45309; 
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
        line-height: 1;
    }

    .note-close-btn:hover {
        background-color: rgba(180, 83, 9, 0.1);
    }
    
    @media (max-width: 640px) {
        .map-filter-panel {
            width: 90% !important; 
            right: 5% !important;  
            max-height: 70vh;      
        }
        .leaflet-popup-content {
            width: 220px !important; 
        }
        .map-legend {
            top: auto !important;
            bottom: 80px !important;
            left: 10px !important;
            max-width: 150px;
        }
        .map-margin-note {
            font-size: 10px !important;
            padding: 6px 30px 6px 12px !important;
            bottom: 10px !important;
            width: 90%;
            white-space: normal !important;
            text-align: center;
        }
    }
    </style>
</head>

<body class="font-sans antialiased welcome-page">
    <div class="min-h-screen flex flex-col"> 

        {{-- HEADER COMPACTO --}}
        <header class="site-header flex-shrink-0"> 
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center flex-wrap gap-4">
                
                {{-- LADO ESQUERDO: Logo Site Menor --}}
                <div class="flex items-center gap-3 flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 sm:h-14 sm:w-14 object-contain">
                        <h1 class="text-xl sm:text-2xl font-bold leading-tight">
                            <span class="text-[#358054]">Árvores de</span>
                            <span class="text-[#a0c520]"> Paracambi</span>
                        </h1>
                    </a>
                </div>

                {{-- LADO DIREITO: Menu + Nova Logo --}}
                <div class="flex items-center gap-2 sm:gap-6">
                    
                    {{-- 1. MENU --}}
                    <div class="flex items-center gap-2 sm:gap-4 relative">
                        @if (auth('admin')->check())
                            <a href="{{ route('admin.dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Painel</a>
                        @elseif(auth()->check())
                            <a href="{{ route('dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Menu</a>
                        @else
                            <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block text-sm py-1.5 px-3">Entrar</a>
                            <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block text-sm py-1.5 px-3">Cadastrar</a>

                            {{-- MOBILE MENU PARA VISITANTES --}}
                            <div class="relative inline-block sm:hidden" x-data="{ open: false }">
                                <button @click="open = !open" class="btn bg-[#358054] text-white hover:bg-[#2d6e4b] rounded-lg flex items-center gap-1.5 transition-all duration-200 py-1.5 px-3 text-xs">
                                    Entrar
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                        <path x-show="!open" d="M4 6h16M4 12h16M4 18h16" />
                                        <path x-show="open" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-[#e8ffe6] rounded-xl shadow-lg z-50 overflow-hidden border border-green-100">
                                    <a href="{{ route('login') }}" class="block px-4 py-3 text-sm font-semibold text-gray-800 hover:text-green-700 hover:bg-[#d9f5d6]">Entrar</a>
                                    <a href="{{ route('register') }}" class="block px-4 py-3 text-sm font-semibold text-gray-800 hover:text-green-700 hover:bg-[#d9f5d6]">Cadastrar</a>
                                    <a href="{{ route('contact') }}" class="block px-4 py-3 text-sm font-semibold text-gray-800 hover:text-green-700 hover:bg-[#d9f5d6]">Fazer Solicitação</a>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 2. NOVA LOGO --}}
                    <img src="{{ asset('images/nova_logo.png') }}" 
                         alt="Logo Prefeitura" 
                         class="header-logo-right hover:opacity-90 transition-opacity"
                         style="height: 3.5rem; width: auto;"> 
                </div>
            </div>
        </header>

        {{-- CONTEÚDO PRINCIPAL MODIFICADO --}}
        {{-- Mudei py-2 para py-8 sm:py-12 (mais espaçamento do topo e fundo) --}}
        {{-- Adicionei flex flex-col justify-center para tentar centralizar verticalmente se sobrar espaço --}}
        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 w-full flex flex-col justify-center">
            
            @if (session('success'))
                <div id="success-alert" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex items-center justify-between shadow-md" role="alert">
                    <div class="flex items-center"><strong class="font-bold mr-2">Sucesso!</strong><span class="block sm:inline">{{ session('success') }}</span></div>
                    @if (session('new_tree_id'))
                        <button onclick="focarNovaArvore({{ session('new_tree_id') }})" class="bg-[#358054] hover:bg-[#2d6e4b] text-white font-bold py-1 px-4 rounded text-xs transition-colors shadow-sm ml-4">Ver no Mapa</button>
                    @endif
                </div>
            @endif

            {{-- CARD DO MAPA --}}
            <div class="bg-white rounded-lg shadow p-1 mb-8 relative w-full">
                
                <h2 class="text-xl font-bold text-gray-900 mb-2 mt-1 pl-2">Mapa Interativo</h2>
                
                <div id="map" class="z-0 w-full rounded-lg h-[60vh] md:h-[80vh]"></div>

                {{-- LEGENDA COM FECHAR (AlpineJS) --}}
                <div x-data="{ showNote: true }" 
                     x-show="showNote" 
                     x-transition.opacity.duration.300ms
                     class="map-margin-note">
                    
                    ⚠️ Pode conter um leve desvio de localização das árvores devido a margem de erro das coordenadas.
                    
                    <button @click="showNote = false" class="note-close-btn" title="Fechar aviso">
                        &times;
                    </button>
                </div>

            </div>
        </main>

        {{-- FOOTER --}}
        <footer class="bg-gray-800 shadow mt-auto flex-shrink-0">
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
        const exportRoute = "{{ route('admin.trees.export') }}";

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

        const panel = L.DomUtil.create("div", "map-filter-panel");
        L.DomEvent.disableClickPropagation(panel);
        L.DomEvent.disableScrollPropagation(panel); 

        /* --- LEGENDA FLUTUANTE (ELEMENTO) --- */
        const legendDiv = L.DomUtil.create("div", "map-legend");
        map.getContainer().appendChild(legendDiv);

        // CONFIGURAÇÃO DA LEGENDA E CORES
        const legendConfig = {
            'injuries': {
                title: 'Injúrias',
                items: [
                    { label: 'Grave', color: '#dc2626' }, // Vermelho
                    { label: 'Moderada', color: '#f97316' }, // Laranja
                    { label: 'Leves ou Ausentes', color: '#22c55e' } // Verde
                ]
            },
            'target': {
                title: 'Alvo (Fluxo)',
                items: [
                    { label: 'Fluxo Intenso', color: '#dc2626' }, // Vermelho
                    { label: 'Fluxo Moderado', color: '#f97316' }, // Laranja
                    { label: 'Fluxo Leve', color: '#22c55e' } // Verde
                ]
            },
            'wiring_status': {
                title: 'Fiação',
                items: [
                    { label: 'Interfere', color: '#dc2626' }, // Vermelho
                    { label: 'Pode Interferir', color: '#f97316' }, // Laranja
                    { label: 'Não Interfere', color: '#22c55e' }, // Verde
                    { label: 'Ausente', color: '#3b82f6' } // Azul
                ]
            },
            'organisms': {
                title: 'Organismos',
                items: [
                    { label: 'Infestação Avançada', color: '#dc2626' }, // Vermelho
                    { label: 'Infestação Média', color: '#f97316' }, // Laranja
                    { label: 'Infestação Inicial', color: '#22c55e' }, // Verde
                    { label: 'Ausente', color: '#3b82f6' } // Azul
                ]
            },
            'crown_balance': {
                title: 'Equilíbrio Copa',
                items: [
                    { label: 'Muito Desequilibrada', color: '#991b1b' }, // Vermelho Escuro
                    { label: 'Desequilibrada', color: '#dc2626' }, // Vermelho
                    { label: 'Mediamente Equil.', color: '#f97316' }, // Laranja
                    { label: 'Equilibrada', color: '#22c55e' } // Verde
                ]
            },
            'stem_balance': {
                title: 'Equilíbrio Fuste',
                items: [
                    { label: 'Acidental', color: '#000000' }, // Preto
                    { label: 'Maior que 45°', color: '#f87171' }, // Vermelho Fraco
                    { label: 'Menor que 45°', color: '#f97316' }, // Laranja
                    { label: 'Ausente (Reto)', color: '#22c55e' } // Verde
                ]
            },
            'health_status': {
                title: 'Saúde',
                items: [
                    { label: 'Ruim', color: '#dc2626' },
                    { label: 'Regular', color: '#f97316' },
                    { label: 'Boa', color: '#22c55e' }
                ]
            }
        };

        // --- MONTAGEM DO HTML ---
        let extraAdminHtml = '';
        let downloadBtnHtml = '';

        if (isAdmin) {
            extraAdminHtml += `
                <div class="admin-divider">Visualização (Admin)</div>
                <div class="filter-group">
                    <label class="filter-label" style="color:#358054;">🎨 Colorir Mapa Por:</label>
                    <select id="colorMode" style="border-color:#358054; font-weight:600; color:#358054;">
                        <option value="species">Espécie (Padrão)</option>
                        <option value="injuries">Injúrias</option>
                        <option value="target">Alvo (Fluxo)</option>
                        <option value="wiring_status">Conflito com Fiação</option>
                        <option value="organisms">Organismos</option>
                        <option value="crown_balance">Equilíbrio de Copa</option>
                        <option value="stem_balance">Equilíbrio de Fuste</option>
                        <option value="health_status">Estado de Saúde</option>
                    </select>
                </div>
                <div class="admin-divider">Filtros Avançados</div>
            `;

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

            downloadBtnHtml = `
                <button id="downloadCsv" class="btn-download">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Baixar Relatório (Excel)
                </button>
            `;
        }

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
                    <select id="especie"><option value="">Todas as espécies</option></select>
                </div>
                ${extraAdminHtml}
            </div>

            <div class="filter-footer">
                <div class="btn-actions">
                    <button id="limparFiltro" class="btn-clear">Limpar</button>
                    <button id="aplicarFiltro" class="btn-filter">Filtrar</button>
                </div>
                ${downloadBtnHtml}
                <div id="filterStatus" class="filter-status">Carregando...</div>
            </div>
        `;
        
        map.getContainer().appendChild(panel);

        toggleBtn.addEventListener("click", (e) => { L.DomEvent.stop(e); panel.classList.toggle("open"); });
        panel.querySelector('#closePanelBtn').addEventListener("click", (e) => { L.DomEvent.stop(e); panel.classList.remove("open"); });

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

        /* --- FUNÇÃO PARA ATUALIZAR A LEGENDA --- */
        function updateLegend(mode) {
            if (mode === 'species' || !legendConfig[mode]) {
                legendDiv.style.display = 'none';
                return;
            }
            const config = legendConfig[mode];
            let html = `<div class="legend-title">${config.title}</div>`;
            config.items.forEach(item => {
                html += `
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: ${item.color};"></div>
                        <span>${item.label}</span>
                    </div>
                `;
            });
            legendDiv.innerHTML = html;
            legendDiv.style.display = 'block';
        }

        /* --- FUNÇÃO PRINCIPAL DE COR DO MARCADOR --- */
        function getMarkerColor(tree) {
            const modeSelect = document.getElementById('colorMode');
            const mode = modeSelect ? modeSelect.value : 'species';
            const val = (tree[mode] || '').toLowerCase(); 

            // Lógica Padrão: Cor baseada na Espécie
            if (mode === 'species') {
                return getColorBySpecies(tree.species_name, tree.vulgar_name);
            }

            // Lógica Admin (Outros Modos)
            if (mode === 'injuries') {
                if (val.includes('grave') || val.includes('extensa')) return '#dc2626';
                if (val.includes('moderada')) return '#f97316';
                return '#22c55e';
            }
            if (mode === 'target') {
                if (val.includes('intenso')) return '#dc2626'; 
                if (val.includes('moderado')) return '#f97316'; 
                return '#22c55e'; 
            }
            if (mode === 'wiring_status') {
                if (val.includes('interfere') && !val.includes('nao') && !val.includes('pode')) return '#dc2626'; 
                if (val.includes('pode')) return '#f97316';
                if (val.includes('ausente')) return '#3b82f6';
                return '#22c55e';
            }
            if (mode === 'organisms') {
                if (val.includes('avançada') || val.includes('avancada')) return '#dc2626'; 
                if (val.includes('media') || val.includes('média')) return '#f97316'; 
                if (val.includes('inicial')) return '#22c55e'; 
                return '#3b82f6';
            }
            if (mode === 'crown_balance') {
                if (val.includes('muito')) return '#991b1b'; 
                if (val.includes('desequilibrada')) return '#dc2626'; 
                if (val.includes('medianamente') || val.includes('mediamente')) return '#f97316'; 
                return '#22c55e'; 
            }
            if (mode === 'stem_balance') {
                if (val.includes('acidental')) return '#000000'; 
                if (val.includes('maior')) return '#f87171';
                if (val.includes('menor')) return '#f97316'; 
                return '#22c55e'; 
            }
            if (mode === 'health_status') {
                if (val === 'poor' || val === 'ruim') return '#dc2626'; 
                if (val === 'fair' || val === 'regular') return '#f97316'; 
                return '#22c55e'; 
            }

            return '#358054'; // Fallback Verde
        }

        /* --- LÓGICA DE COR POR ESPÉCIE --- */
        function getColorBySpecies(speciesName, vulgarName) {
            const nameCheck = (vulgarName || "").toLowerCase();
            const fullName = nameCheck + " " + (speciesName || "").toLowerCase();

            // 1. Árvore não identificada = Verde Escuro
            if (nameCheck.includes('não identificada') || nameCheck.includes('nao identificada')) {
                return '#064e3b'; 
            }

            // 2. Cores Base (Palavras-Chave)
            let baseHue = 120; // Verde padrão (Espécies novas sem regra caem aqui)
            let saturation = 60;
            let lightness = 45;

            if (fullName.includes('flamboyant') || fullName.includes('vermelho') || fullName.includes('pau-brasil')) {
                baseHue = 0; // Vermelho
            } else if (fullName.includes('amarelo') || fullName.includes('acacia') || fullName.includes('sibipiruna') || fullName.includes('canafistula')) {
                baseHue = 50; // Amarelo/Ouro
            } else if (fullName.includes('roxo') || fullName.includes('quaresmeira') || fullName.includes('jacaranda') || fullName.includes('manaca')) {
                baseHue = 270; // Roxo
            } else if (fullName.includes('rosa') || fullName.includes('paineira') || fullName.includes('jambo')) {
                baseHue = 330; // Rosa
            } else if (fullName.includes('branco')) {
                baseHue = 200; saturation = 10; lightness = 85; // Branco/Cinza claro
            } else if (fullName.includes('laranja') || fullName.includes('espatodea')) {
                baseHue = 25; // Laranja
            } else if (fullName.includes('azul')) {
                baseHue = 210; // Azul
            }

            // 3. Variação de Tom (Hash do nome)
            // Isso garante que Ipê Roxo e Quaresmeira (ambos roxos) tenham tons levemente diferentes
            let hash = 0;
            for (let i = 0; i < fullName.length; i++) {
                hash = fullName.charCodeAt(i) + ((hash << 5) - hash);
            }
            
            // Pequena variação para não descaracterizar a cor base
            const hueVariation = (hash % 20) - 10;   // +/- 10 graus no disco de cor
            const lightVariation = (hash % 15) - 7;  // +/- 7% na luminosidade

            const finalHue = (baseHue + hueVariation + 360) % 360;
            const finalLight = Math.max(25, Math.min(75, lightness + lightVariation));

            return `hsl(${finalHue}, ${saturation}%, ${finalLight}%)`;
        }

        function popularSelects(trees) {
            const especieSelect = document.getElementById("especie");
            const bairroSelect = document.getElementById("bairro");
            
            // Popula Bairros
            allBairros.forEach(b => {
                const opt = document.createElement("option"); opt.value = b.id; opt.textContent = b.nome; bairroSelect.appendChild(opt);
            });

            // Popula Espécies
            const nomesSet = new Set();
            trees.forEach(t => {
                let nome = t.vulgar_name;
                if (nome && nome.trim() !== "" && !nome.toLowerCase().includes("não identificada")) {
                    let nomeFormatado = nome.trim();
                    nomeFormatado = nomeFormatado.charAt(0).toUpperCase() + nomeFormatado.slice(1);
                    nomesSet.add(nomeFormatado);
                }
            });
            Array.from(nomesSet).sort().forEach(nome => {
                const opt = document.createElement("option"); opt.value = nome; opt.textContent = nome; especieSelect.appendChild(opt);
            });

            // Popula Filtros de Admin
            if (isAdmin) {
                adminFieldsConfig.forEach(field => {
                    const select = document.getElementById(field.id);
                    if(select) {
                        // Limpa opções anteriores (exceto "Todos")
                        while (select.options.length > 1) { select.remove(1); }

                        // LÓGICA ESPECIAL: Opções Fixas Obrigatórias
                        let opcoesFixas = [];

                        if (field.id === 'health_status') {
                            opcoesFixas = ['Boa', 'Regular', 'Ruim'];
                        } 
                        else if (field.id === 'crown_balance') {
                            // Adicionei "Desequilibrada" aqui para garantir que apareça
                            opcoesFixas = ['Equilibrada', 'Mediamente Equilibrada', 'Desequilibrada', 'Muito Desequilibrada'];
                        }
                        else if (field.id === 'organisms') {
                            // Adicionei "Ausente" aqui para garantir que apareça separado
                            opcoesFixas = ['Ausente', 'Infestação Inicial', 'Infestação Média', 'Infestação Avançada'];
                        }

                        // Se tiver opções fixas, usa elas. Se não, pega do banco.
                        if (opcoesFixas.length > 0) {
                            opcoesFixas.forEach(valor => {
                                const opt = document.createElement("option"); 
                                opt.value = valor; 
                                opt.textContent = valor; 
                                select.appendChild(opt);
                            });
                        } else {
                            // Padrão dinâmico para os outros campos
                            const valoresUnicos = [...new Set(trees.map(t => t[field.key]).filter(v => v))].sort();
                            valoresUnicos.forEach(valor => {
                                const opt = document.createElement("option"); 
                                opt.value = valor; 
                                opt.textContent = valor; 
                                select.appendChild(opt);
                            });
                        }
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
                statusDiv.innerHTML = `<span>Nenhuma árvore encontrada.</span>`;
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
                
                // CHAMA A FUNÇÃO DE COR CORRETA
                const treeColor = getMarkerColor(tree);
                
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
            const bairroVal = document.getElementById("bairro").value;
            const especieVal = document.getElementById("especie").value;
            const buscaVal = document.getElementById("search").value.toLowerCase().trim();

            const adminFilters = {};
            if (isAdmin) {
                adminFieldsConfig.forEach(field => {
                    const el = document.getElementById(field.id);
                    if (el && el.value) adminFilters[field.key] = el.value;
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
                        if ((tree[key] || "") != val) { okAdmin = false; break; }
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

        function downloadCSV() {
            const params = new URLSearchParams();
            const bairroVal = document.getElementById("bairro").value;
            const especieVal = document.getElementById("especie").value;
            const buscaVal = document.getElementById("search").value;

            if (bairroVal) params.append('bairro_id', bairroVal);
            if (especieVal) params.append('vulgar_name', especieVal);
            if (buscaVal) params.append('search', buscaVal);

            if (isAdmin) {
                adminFieldsConfig.forEach(field => {
                    const el = document.getElementById(field.id);
                    if (el && el.value) params.append(field.key, el.value);
                });
            }
            window.open(`${exportRoute}?${params.toString()}`, '_blank');
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
                
                let nomeExibicao = tree.vulgar_name || 'Não Identificada';
                let nomeCheck = nomeExibicao.toLowerCase().trim();

                if (nomeCheck.includes('não identificada') || nomeCheck.includes('nao identificada')) {
                    if (tree.no_species_case && tree.no_species_case.trim() !== "") {
                        nomeExibicao = tree.no_species_case;
                    }
                }

                let adminButton = '';
                if (isAdmin) {
                    const editUrl = editRouteTemplate.replace('ID_PLACEHOLDER', tree.id);
                    adminButton = `
                        <a href="${editUrl}" style="color: white !important;" class="flex-1 ml-2 group flex items-center justify-center bg-blue-600 hover:bg-blue-700 !text-white border border-blue-600 rounded-lg px-3 py-2 transition-all duration-200 decoration-0">
                            <span class="text-xs font-bold text-white">Editar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>`;
                }

                container.innerHTML = `
                <div class="flex items-center justify-between mb-3 bg-gray-100 rounded-lg p-1 select-none">
                    <button id="btn-prev" class="p-1 text-gray-600 hover:text-green-700 hover:bg-white rounded transition cursor-pointer"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg></button>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">${indexAtual + 1} de ${total}</span>
                    <button id="btn-next" class="p-1 text-gray-600 hover:text-green-700 hover:bg-white rounded transition cursor-pointer"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></button>
                </div>
                <div class="mb-3">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Nome Popular:</p>
                    <h3 class="font-bold text-[#358054] text-sm leading-tight mb-2">${nomeExibicao}</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Localização:</p>
                    <div class="text-xs text-gray-600 pb-2 border-b border-gray-100 leading-snug">
                        <p class="mb-1">${tree.address} - <strong>${tree.bairro_nome || 'Rua não informada'}</p>
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

        setTimeout(() => {
            const btnAplicar = document.getElementById("aplicarFiltro");
            const btnLimpar = document.getElementById("limparFiltro");
            const inputSearch = document.getElementById("search");
            const btnDown = document.getElementById("downloadCsv");
            
            if (btnAplicar) btnAplicar.addEventListener("click", aplicarFiltro);
            if (inputSearch) inputSearch.addEventListener("keyup", (e) => { if (e.key === 'Enter') aplicarFiltro(); });
            if (btnDown) btnDown.addEventListener("click", downloadCSV);

            const colorModeSelect = document.getElementById("colorMode");
            if(colorModeSelect) {
                colorModeSelect.addEventListener("change", () => {
                    const newMode = colorModeSelect.value;
                    updateLegend(newMode);
                    exibirArvores(filteredTrees.length > 0 ? filteredTrees : allTrees);
                });
            }

            if (btnLimpar) {
                btnLimpar.addEventListener("click", () => {
                    document.getElementById("bairro").value = "";
                    document.getElementById("especie").value = "";
                    document.getElementById("search").value = "";
                    if (isAdmin) {
                        adminFieldsConfig.forEach(f => { const el = document.getElementById(f.id); if(el) el.value = ""; });
                        const cm = document.getElementById("colorMode");
                        if(cm) { cm.value = "species"; updateLegend('species'); }
                    }
                    exibirArvores(allTrees);
                    if (bairrosGeoLayer) bairrosGeoLayer.eachLayer(l => l.setStyle({ color: "#00000020", weight: 1, fillOpacity: 0.02 }));
                    map.setView(INITIAL_VIEW, INITIAL_ZOOM);
                });
            }
        }, 300);

    </script>
</body>
</html>
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
            position: absolute; top: 70px; right: 10px; width: 320px; 
            z-index: 2000; background: white; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); display: none; 
            flex-direction: column; font-family: 'Instrument Sans', sans-serif;
            max-height: calc(100% - 60px); /* Reduzi um pouco para dar folga no PC */
            overflow-y: auto; /* Permite rolar o painel inteiro se necessário */
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
            max-height: 80% !important;
            top: 60px !important;
            position: absolute !important;
            border-radius: 12px !important;
            z-index: 2000 !important;
        }
        .filter-content {
            padding: 12px 16px !important;
        }
        .filter-footer {
            padding: 10px 14px !important;
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
                <div class="flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-6">
                    
                    {{-- 1. MENU (Aparece em cima no mobile) --}}
                    <div class="flex items-center gap-2 sm:gap-4 relative order-1 sm:order-2">
                        @if (auth('admin')->check())
                            <a href="{{ route('admin.dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Painel</a>
                        @elseif (auth('analyst')->check())
                            <a href="{{ route('analyst.dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Painel</a>
                        @elseif(auth()->check())
                            <a href="{{ route('dashboard') }}" class="btn bg-green-600 hover:bg-green-700 text-xs sm:text-sm py-1.5 px-3">Menu</a>
                        @else
                            <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block text-sm py-1.5 px-3">Entrar</a>
                            <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block text-sm py-1.5 px-3">Cadastrar</a>

                            {{-- MOBILE MENU PARA VISITANTES --}}
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
                                        <a href="{{ route('login') }}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold text-white bg-[#358054] hover:bg-[#2d6e4b] rounded-lg transition-colors">
                                            Entrar
                                        </a>
                                        <a href="{{ route('register') }}" class="flex items-center justify-center px-4 py-2.5 text-sm font-bold text-gray-800 bg-[#a0c520] hover:bg-[#8eb01c] rounded-lg transition-colors">
                                            Cadastrar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 2. NOVA LOGO (Aparece embaixo no mobile) --}}
                    <img src="{{ asset('images/nova_logo.png') }}" 
                         alt="Logo Prefeitura" 
                         class="header-logo-right hover:opacity-90 transition-opacity order-2 sm:order-1"
                         style="height: 3.5rem; width: auto;"> 
                </div>
            </div>
        </header>

        {{-- CONTEÚDO PRINCIPAL MODIFICADO --}}
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

    {{-- VLibras --}}
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        window.vlibrasWidget = new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>

    {{-- SCRIPTS DO MAPA --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = [];
        let bairrosLayer;

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializa o mapa
            map = L.map('map').setView([-22.6086, -43.7128], 13);

            // Camada de satélite (Google)
            L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '© Google Maps'
            }).addTo(map);

            // Carrega as árvores
            carregarArvores();
        });

        function carregarArvores(filtros = {}) {
            // Limpa marcadores existentes
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            // Busca dados das árvores via API
            let url = '/api/trees';
            let params = new URLSearchParams(filtros);
            if (params.toString()) url += '?' + params.toString();

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    data.forEach(tree => {
                        let color = getStatusColor(tree.status);
                        let marker = L.circleMarker([tree.latitude, tree.longitude], {
                            radius: 8,
                            fillColor: color,
                            color: "#fff",
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.8
                        }).addTo(map);

                        marker.bindPopup(`
                            <div class="p-3">
                                <h3 class="font-bold text-lg mb-1">${tree.especie_comum || 'Espécie não identificada'}</h3>
                                <p class="text-sm text-gray-600 mb-2"><strong>Status:</strong> ${tree.status}</p>
                                <a href="/trees/${tree.id}" class="inline-block bg-[#358054] text-white px-3 py-1 rounded text-sm font-semibold hover:bg-[#2d6e4b] transition-colors">Ver Detalhes</a>
                            </div>
                        `);
                        markers.push(marker);
                    });
                });
        }

        function getStatusColor(status) {
            switch (status) {
                case 'Saudável': return '#a0c520';
                case 'Doente': return '#f59e0b';
                case 'Morta': return '#ef4444';
                default: return '#358054';
            }
        }

        function focarNovaArvore(id) {
            fetch(`/api/trees/${id}`)
                .then(response => response.json())
                .then(tree => {
                    map.setView([tree.latitude, tree.longitude], 18);
                    // Encontra o marcador e abre o popup
                    // (Simplificado: recarrega e foca)
                    carregarArvores();
                });
        }
    </script>
</body>
</html>

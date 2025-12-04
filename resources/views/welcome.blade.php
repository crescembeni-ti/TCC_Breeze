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
        <header class="site-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">
                <div class="flex items-center gap-4 flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-4">
                        <img src="{{ asset('images/Brasao_Verde.png') }} " alt="Logo Brasão de Paracambi"
                            class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi"
                            class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <h1 class="text-3xl sm:text-4xl font-bold">
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
                    @if (auth('admin')->check())
                        <a href="{{ route('admin.dashboard') }}"
                            class="btn bg-green-600 hover:bg-green-700 hidden sm:block">
                            Painel Administrativo
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf

                            {{-- USUÁRIO LOGADO --}}
                        @elseif(auth()->check())
                            <div class="relative group flex items-center">
                            <a href="{{ route('dashboard') }}"
                        class="btn bg-green-600 hover:bg-green-700 hidden sm:block px-6 py-3 text-lg rounded-lg">
                            Menu
                        </a>

                        <!-- Tooltip verde no estilo do layout -->
                    <div class="
                        absolute 
                        bottom-[-55px]
                        left-1/2
                        transform -translate-x-1/2
                        bg-gradient-to-r from-[#358054] to-[#a0c520]
                        text-white text-xs font-semibold
                        py-1.5 px-3 rounded-lg shadow-xl
                        opacity-0 group-hover:opacity-100
                        pointer-events-none
                        transition-all duration-200
                        whitespace-nowrap
                ">
                    Acesse seu painel e opções da conta

                    <!-- Setinha combinando -->
                    <span class="
                        absolute 
                        top-[-6px] left-1/2 transform -translate-x-1/2
                        w-0 h-0
                        border-l-[6px] border-l-transparent
                        border-r-[6px] border-r-transparent
                        border-b-[6px] border-b-[#358054]
                        "></span>
                </div>
            </div>



                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                {{-- VISITANTE (NÃO LOGADO) --}}
                            @else
                                <a href="{{ route('login') }}"
                                    class="btn bg-green-600 hover:bg-green-700 hidden sm:block">
                                    Entrar
                                </a>
                                <a href="{{ route('register') }}"
                                    class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block">
                                    Cadastrar
                                </a>

                                {{-- MENU HAMBÚRGUER (ATUALIZADO E ANIMADO) --}}
                                <div class="relative inline-block">

                                    <!-- Botão animado -->
                                    <button id="guestMenuBtn"
                                        class="ml-3 btn bg-[#358054] text-white hover:bg-[#2d6e4b] rounded-lg flex items-center gap-2 transition-all duration-200">

                                        <!-- Texto mantém igual -->
                                        Menu

                                        <!-- Ícone animado -->
                                        <svg id="iconMenu" class="w-6 h-6 transition-all duration-200" fill="none"
                                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 6h16" />
                                            <path d="M4 12h16" />
                                            <path d="M4 18h16" />
                                        </svg>

                                    </button>

                                    <!-- DROPDOWN novo no tom do seu site -->
                                    <div id="guestMenu"
                                        class="hidden absolute right-0 mt-2 w-56 bg-[#e8ffe6] rounded-xl shadow-lg z-50">

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
                        <div class="pl-4 py-2 activity-item">
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

        <!-- RODAPÉ -->
        <footer class="bg-gray-800 shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
            </div>
        </footer>
    </div>

    {{-- ========================================================= --}}
    {{-- MAPA (Leaflet) --}}
    {{-- ========================================================= --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

   <script>
/* ============================================================
   CONFIGURAÇÃO INICIAL DO MAPA
   ============================================================ */

const allBairros = @json($bairros);
const INITIAL_VIEW = [-22.6111, -43.7089];
const INITIAL_ZOOM = 14;

// Definindo os limites (caixa envolvente) de Paracambi com uma margem de segurança
const PARACAMBI_BOUNDS = [
    [-22.7000, -43.8500], // Canto Sudoeste (embaixo, esquerda)
    [-22.5000, -43.5500]  // Canto Nordeste (cima, direita)
];

// Inicializa o mapa com as restrições
const map = L.map('map', {
    center: INITIAL_VIEW,
    zoom: INITIAL_ZOOM,
    minZoom: 12,              // Impede de afastar muito (ex: ver o estado inteiro)
    maxBounds: PARACAMBI_BOUNDS, // Trava a área de navegação
    maxBoundsViscosity: 1.0   // 1.0 torna a borda sólida (0.0 faria o mapa "quicar" de volta)
});

// Layer satélite
L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
    { attribution: "Tiles © Esri" }
).addTo(map);

// Layer labels
L.tileLayer(
    "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png",
    { subdomains: "abcd", maxZoom: 20 }
).addTo(map);

// *** Removido: aquelas linhas pretas eram "maxBounds", tirei completamente ***


/* ============================================================
   LAYERS DE ÁRVORES
   ============================================================ */

let allTrees = [];
let filteredTrees = [];
let markersLayer = L.layerGroup().addTo(map);
let treeMarkers = {};


/* ============================================================
   ÍNDICE DE BAIRROS
   ============================================================ */

const bairrosIndex = {};
allBairros.forEach(b => bairrosIndex[b.nome.toUpperCase()] = b.id);


/* ============================================================
   CARREGAR GEOJSON DOS BAIRROS — sem linhas, sem tooltip, sem popup
   ============================================================ */

let bairrosGeoLayer = null;

fetch("/bairros.json")
    .then(r => r.json())
    .then(geo => {

        // Filtro para remover linhas ("LineString") e manter só polígonos
        const cleanedGeo = {
            type: "FeatureCollection",
            features: geo.features.filter(f =>
                f.geometry.type === "Polygon" ||
                f.geometry.type === "MultiPolygon"
            )
        };

        // Vincula o id do bairro ao polígono
        cleanedGeo.features.forEach(f => {
            const nome = (f.properties.BAIRRO || "").toUpperCase();
            f.properties.id_bairro = bairrosIndex[nome] ?? null;
        });

        // Camada final — super leve, sem nomes, sem tooltip
        bairrosGeoLayer = L.geoJSON(cleanedGeo, {
            style: {
                color: "#00000020",   // contorno leve e discreto
                weight: 1,
                fillOpacity: 0.02     // quase invisível
            },
            onEachFeature: (feature, layer) => {
                // Não exibir tooltip ou popup
                layer.unbindTooltip();
                layer.unbindPopup();

                // Evita qualquer comportamento ao clicar
                layer.on("click", e => e.originalEvent.preventDefault());
            }
        }).addTo(map);
    });


/* ============================================================
   ESTILO DA TOOLTIP DOS BAIRROS (MENOR / BONITA)
   ============================================================ */
const tooltipStyle = document.createElement("style");
tooltipStyle.innerHTML = `
    .bairro-tooltip {
        background: rgba(0,0,0,0.65);
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: none;
    }
`;
document.head.appendChild(tooltipStyle);


/* ============================================================
   PAINEL DE FILTROS (VISUAL AJUSTADO)
   ============================================================ */

const toggleBtn = L.DomUtil.create('button', 'map-filter-toggle');
toggleBtn.innerHTML = "Filtros";
map.getContainer().appendChild(toggleBtn);

const panel = L.DomUtil.create("div", "map-filter-panel");
panel.innerHTML = `
    <label>Pesquisar espécie</label>
    <input type="text" id="search" placeholder="Ex: ipê, pau brasil..." />

    <label>Bairro</label>
    <select id="bairro"><option value="">Todos</option></select>

    <label>Espécie</label>
    <select id="especie"><option value="">Todas</option></select>

    <button id="aplicarFiltro" class="btn-filter">Filtrar</button>
    <button id="limparFiltro" class="btn-clear">Limpar</button>
`;
map.getContainer().appendChild(panel);

// Estilos internos
const css = document.createElement("style");
css.innerHTML = `
    .map-filter-toggle {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #2d7a44;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .map-filter-panel {
        position: absolute;
        top: 55px;
        right: 10px;
        width: 220px;
        background: white;
        padding: 14px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        display: none;
    }

    .map-filter-panel.open { display: block; }

    .map-filter-panel label {
        font-size: 12px;
        font-weight: 600;
        margin-top: 6px;
        display: block;
    }

    .map-filter-panel input,
    .map-filter-panel select {
        width: 100%;
        margin-top: 2px;
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .btn-filter {
        width: 100%;
        margin-top: 10px;
        padding: 8px;
        border-radius: 8px;
        background: #2d7a44;
        color: white;
        font-weight: 600;
    }

    .btn-clear {
        width: 100%;
        margin-top: 6px;
        padding: 8px;
        border-radius: 8px;
        background: #777;
        color: white;
        font-weight: 600;
    }
`;
document.head.appendChild(css);

toggleBtn.addEventListener("click", () => {
    panel.classList.toggle("open");
});


/* ============================================================
   CARREGAR ÁRVORES DA API
   ============================================================ */

fetch("/api/trees")
    .then(r => r.json())
    .then(data => {
        allTrees = data;
        popularFiltros(allTrees);
        exibirArvores(allTrees);
    });


/* ============================================================
   POPULAR SELECTS
   ============================================================ */

function popularFiltros(trees) {
    const especieSelect = document.getElementById("especie");
    const bairroSelect = document.getElementById("bairro");

    // Preencher bairros
    allBairros.forEach(b => {
        const opt = document.createElement("option");
        opt.value = b.id;
        opt.textContent = b.nome;
        bairroSelect.appendChild(opt);
    });

    // Preencher espécies
    const especies = [...new Set(trees.map(t => t.species_name))].sort();
    especies.forEach(e => {
        const opt = document.createElement("option");
        opt.value = e;
        opt.textContent = e;
        especieSelect.appendChild(opt);
    });
}


/* ============================================================
   EXIBIR ÁRVORES (bolinha proporcional ao diâmetro)
   ============================================================ */

function exibirArvores(trees) {
    markersLayer.clearLayers();
    treeMarkers = {};
    filteredTrees = trees;

    trees.forEach(tree => {
        const size = Math.max(6, (tree.trunk_diameter ?? 10) / 4);

        const marker = L.circleMarker([tree.latitude, tree.longitude], {
            radius: size,
            color: "#fff",
            weight: 2,
            fillColor: tree.color_code,
            fillOpacity: 0.85
        }).addTo(markersLayer);

        marker.bindPopup(`
            <h3 style="font-weight:700">${tree.species_name}</h3>
            <p>${tree.address}</p>
        `);

        treeMarkers[tree.id] = marker;
    });
}


/* ============================================================
   DESTACAR BAIRRO SELECIONADO
   ============================================================ */

function destacarBairro(bairroId) {
    if (!bairrosGeoLayer) return;

    bairrosGeoLayer.eachLayer(layer => {
        const props = layer.feature.properties;

        if (props.id_bairro == bairroId) {
            layer.setStyle({
                color: "#0084ff",
                weight: 3,
                fillOpacity: 0.08    // mais transparente
            });
            map.fitBounds(layer.getBounds());
        } else {
            layer.setStyle({
                color: "#00000040",
                weight: 1,
                fillOpacity: 0.02
            });
        }
    });
}


/* ============================================================
   APLICAR FILTRO
   ============================================================ */

function aplicarFiltro() {
    const bairro = document.getElementById("bairro").value;
    const especie = document.getElementById("especie").value;
    const busca = document.getElementById("search").value.toLowerCase();

    const filtradas = allTrees.filter(tree => {
        const okBairro = bairro ? tree.bairro_id == bairro : true;
        const okEspecie = especie ? tree.species_name === especie : true;
        const okBusca = busca ? tree.species_name.toLowerCase().includes(busca) : true;
        return okBairro && okEspecie && okBusca;
    });

    exibirArvores(filtradas);

    if (bairro) destacarBairro(bairro);
}

document.getElementById("aplicarFiltro").addEventListener("click", aplicarFiltro);


/* ============================================================
   LIMPAR FILTROS → volta estado original
   ============================================================ */

document.getElementById("limparFiltro").addEventListener("click", () => {
    document.getElementById("bairro").value = "";
    document.getElementById("especie").value = "";
    document.getElementById("search").value = "";

    exibirArvores(allTrees);

    // Resetar bairros
    if (bairrosGeoLayer) {
        bairrosGeoLayer.eachLayer(layer => {
            layer.setStyle({
                color: "#00000040",
                weight: 1,
                fillOpacity: 0.02
            });
        });
    }

    // Resetar zoom
    map.setView(INITIAL_VIEW, INITIAL_ZOOM);
});
</script>



</body>

</html>

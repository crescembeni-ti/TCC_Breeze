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

const map = L.map('map').setView(INITIAL_VIEW, INITIAL_ZOOM);

// Camada satélite
L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
    { attribution: "Tiles © Esri" }
).addTo(map);

// Camada labels
L.tileLayer(
    "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png",
    { subdomains: "abcd", maxZoom: 20 }
).addTo(map);


/* ============================================================
   LAYERS DE ÁRVORES
   ============================================================ */

let allTrees = [];
let filteredTrees = [];
let markersLayer = L.layerGroup().addTo(map);
let treeMarkers = {};


/* ============================================================
   INDEXAÇÃO DOS BAIRROS PELO NOME
   ============================================================ */

const bairrosIndex = {};
allBairros.forEach(b => bairrosIndex[b.nome.toUpperCase()] = b.id);


/* ============================================================
   GEOJSON DOS BAIRROS
   ============================================================ */

let bairrosGeoLayer = null;

fetch("/bairros.json")
    .then(r => r.json())
    .then(geo => {

        // removendo linhas e mantendo só polígonos
        const cleanedGeo = {
            type: "FeatureCollection",
            features: geo.features.filter(f =>
                f.geometry.type === "Polygon" ||
                f.geometry.type === "MultiPolygon"
            )
        };

        cleanedGeo.features.forEach(f => {
            const nome = (f.properties.BAIRRO || "").toUpperCase();
            f.properties.id_bairro = bairrosIndex[nome] ?? null;
        });

        bairrosGeoLayer = L.geoJSON(cleanedGeo, {
            style: {
                color: "#00000020",
                weight: 1,
                fillOpacity: 0.02
            },
            onEachFeature: layer => {
                layer.unbindTooltip();
                layer.unbindPopup();
            }
        }).addTo(map);
    });


/* ============================================================
   PAINEL DE FILTROS
   ============================================================ */

const toggleBtn = L.DomUtil.create('button', 'map-filter-toggle');
toggleBtn.innerHTML = "Filtros";
map.getContainer().appendChild(toggleBtn);

const panel = L.DomUtil.create("div", "map-filter-panel");
panel.innerHTML = `
    <label>Pesquisar espécie</label>
    <input type="text" id="search">

    <label>Bairro</label>
    <select id="bairro"><option value="">Todos</option></select>

    <label>Espécie</label>
    <select id="especie"><option value="">Todas</option></select>

    <div style="margin-top:12px;display:flex;gap:8px;">
        <button id="aplicarFiltro" class="btn-filter" style="flex:1;">Filtrar</button>
        <button id="limparFiltro" class="btn-clear" style="flex:1;">Limpar</button>
    </div>
`;
map.getContainer().appendChild(panel);

toggleBtn.addEventListener("click", () => {
    panel.classList.toggle("open");
});


/* ============================================================
   CARREGAR ÁRVORES
   ============================================================ */

fetch("/api/trees")
    .then(r => r.json())
    .then(data => {
        allTrees = data;
        popularFiltros(allTrees);
        exibirArvores(allTrees); // garante popups funcionando ao clicar
    });


/* ============================================================
   POPULAR SELECTS
   ============================================================ */

function popularFiltros(trees) {
    const espSel = document.getElementById("especie");
    const baiSel = document.getElementById("bairro");

    // bairros
    allBairros.forEach(b => {
        const opt = document.createElement("option");
        opt.value = b.id;
        opt.textContent = b.nome;
        baiSel.appendChild(opt);
    });

    // espécies
    const especies = [...new Set(trees.map(t => t.species_name))].sort();
    especies.forEach(e => {
        const opt = document.createElement("option");
        opt.value = e;
        opt.textContent = e;
        espSel.appendChild(opt);
    });
}


/* ============================================================
   EXIBIR ÁRVORES — tamanho correto + popup original restaurado
   ============================================================ */

function exibirArvores(trees) {
    markersLayer.clearLayers();
    treeMarkers = {};
    filteredTrees = trees;

    trees.forEach(tree => {
        const size = Math.max(6, (parseFloat(tree.trunk_diameter) || 10) / 4);

        const marker = L.circleMarker([tree.latitude, tree.longitude], {
            radius: size,
            color: "#ffffff",
            weight: 2,
            fillColor: tree.color_code,
            fillOpacity: 0.85
        }).addTo(markersLayer);

        // popup RESTAURADO como antes
        marker.bindPopup(`
            <h3 style="font-weight:700;font-size:15px;margin-bottom:4px">${tree.species_name}</h3>
            <p style="margin:0 0 6px 0;">${tree.address}</p>

            <button onclick="window.location.href='/trees/${tree.id}'"
                style="
                    background:#2d7a44;color:#fff;
                    padding:6px 12px;border:none;border-radius:6px;
                    font-weight:600;cursor:pointer;
                ">
                Ver detalhes
            </button>
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
                fillOpacity: 0.05 // mais transparente
            });
            map.fitBounds(layer.getBounds());
        } else {
            layer.setStyle({
                color: "#00000020",
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

    const oldMsg = document.querySelector(".filter-msg");
    if (oldMsg) oldMsg.remove();

    // se não encontrou → mensagem e não dá zoom
    if (filtradas.length === 0) {
        const div = document.createElement("div");
        div.className = "filter-msg";
        div.style = "margin-top:10px;padding:8px;background:#ffd8d8;color:#900;border-radius:8px;font-size:12px;font-weight:600;";
        div.innerHTML = "Nenhuma árvore encontrada neste filtro.";
        panel.appendChild(div);

        exibirArvores([]); // esvazia o mapa
        return;
    }

    exibirArvores(filtradas);

    if (bairro) destacarBairro(bairro);
}

document.getElementById("aplicarFiltro").addEventListener("click", aplicarFiltro);


/* ============================================================
   LIMPAR FILTROS
   ============================================================ */

document.getElementById("limparFiltro").addEventListener("click", () => {
    document.getElementById("bairro").value = "";
    document.getElementById("especie").value = "";
    document.getElementById("search").value = "";

    const oldMsg = document.querySelector(".filter-msg");
    if (oldMsg) oldMsg.remove();

    exibirArvores(allTrees);

    if (bairrosGeoLayer) {
        bairrosGeoLayer.eachLayer(layer => {
            layer.setStyle({
                color: "#00000020",
                weight: 1,
                fillOpacity: 0.02
            });
        });
    }

    map.setView(INITIAL_VIEW, INITIAL_ZOOM);
});
</script>





</body>

</html>

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
                                <div
                                    class="
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
                                    <span
                                        class="
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
    {{-- SCRIPTS DO MAPA --}}
    {{-- ========================================================= --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /* ============================================================
           CONFIGURAÇÃO INICIAL DO MAPA
           ============================================================ */

        const allBairros = @json($bairros);
        const INITIAL_VIEW = [-22.6111, -43.7089];
        const INITIAL_ZOOM = 14;

        const PARACAMBI_BOUNDS = [
            [-22.7000, -43.8500],
            [-22.5000, -43.5500]
        ];

        const map = L.map('map', {
            center: INITIAL_VIEW,
            zoom: INITIAL_ZOOM,
            minZoom: 13,
            maxBounds: PARACAMBI_BOUNDS,
            maxBoundsViscosity: 1.0
        });

        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 22,
            attribution: 'Google'
        }).addTo(map);


        L.tileLayer(
            "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png", {
                subdomains: "abcd",
                maxZoom: 20
            }
        ).addTo(map);


        /* ============================================================
           LAYERS E VARIÁVEIS
           ============================================================ */
        let currentTrees = []; // lista de árvores atualmente exibidas
        let treeIndexGlobal = 0; // índice da árvore selecionada
        let allTrees = [];
        let filteredTrees = [];
        let markersLayer = L.layerGroup().addTo(map);
        let treeMarkers = {};


        /* ============================================================
           BAIRROS (FUNDO)
           ============================================================ */
        const bairrosIndex = {};
        allBairros.forEach(b => bairrosIndex[b.nome.toUpperCase()] = b.id);

        let bairrosGeoLayer = null;

        fetch("/bairros.json")
            .then(r => r.json())
            .then(geo => {
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
                        fillOpacity: 0.02,
                        interactive: false // Importante: deixa clicar através do bairro
                    },
                    onEachFeature: (feature, layer) => {
                        layer.unbindTooltip();
                        layer.unbindPopup();
                    }
                }).addTo(map);

                bairrosGeoLayer.bringToBack();
            });


        /* ============================================================
           UI: PAINEL DE FILTROS E ESTILOS
           ============================================================ */

        // Estilos extras para o Popup ficar bonito
        const customStyles = document.createElement("style");
        customStyles.innerHTML = `
        .bairro-tooltip {
            background: rgba(0,0,0,0.65); color: white; padding: 2px 6px;
            border-radius: 4px; font-size: 11px; font-weight: 600; border: none;
        }
        /* Remove margens padrão do popup do leaflet para usarmos Tailwind dentro */
        .leaflet-popup-content-wrapper { padding: 0; overflow: hidden; }
        .leaflet-popup-content { margin: 0; width: 260px !important; }
    `;
        document.head.appendChild(customStyles);

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

        const css = document.createElement("style");
        css.innerHTML = `
        .map-filter-toggle {
            position: absolute; top: 10px; right: 10px;
            background: #2d7a44; color: white; padding: 8px 16px;
            border: none; border-radius: 8px; cursor: pointer; font-weight: 600;
        }
        .map-filter-panel {
            position: absolute; top: 55px; right: 10px; width: 220px;
            background: white; padding: 14px; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25); display: none;
        }
        .map-filter-panel.open { display: block; }
        .map-filter-panel label { font-size: 12px; font-weight: 600; margin-top: 6px; display: block; }
        .map-filter-panel input, .map-filter-panel select {
            width: 100%; margin-top: 2px; padding: 6px;
            border-radius: 6px; border: 1px solid #ccc;
        }
        .btn-filter {
            width: 100%; margin-top: 10px; padding: 8px; border-radius: 8px;
            background: #2d7a44; color: white; font-weight: 600;
        }
        .btn-clear {
            width: 100%; margin-top: 6px; padding: 8px; border-radius: 8px;
            background: #777; color: white; font-weight: 600;
        }
    `;
        document.head.appendChild(css);

        toggleBtn.addEventListener("click", () => panel.classList.toggle("open"));


        /* ============================================================
           LÓGICA DAS ÁRVORES E POPUP
           ============================================================ */

        fetch("/api/trees")
            .then(r => r.json())
            .then(data => {
                allTrees = data;
                popularFiltros(allTrees);
                exibirArvores(allTrees);
            });

        function popularFiltros(trees) {
            const especieSelect = document.getElementById("especie");
            const bairroSelect = document.getElementById("bairro");

            allBairros.forEach(b => {
                const opt = document.createElement("option");
                opt.value = b.id;
                opt.textContent = b.nome;
                bairroSelect.appendChild(opt);
            });

            const especies = [...new Set(trees.map(t => t.species_name))].sort();
            especies.forEach(e => {
                const opt = document.createElement("option");
                opt.value = e;
                opt.textContent = e;
                especieSelect.appendChild(opt);
            });
        }

        /* ============================================================
           EXIBIR ÁRVORES (COM CLIQUE AGRUPADO)
           ============================================================ */

        function exibirArvores(trees) {
    // Limpa todos os markers antigos
    markersLayer.clearLayers();
    treeMarkers = {};
    filteredTrees = trees;
    currentTrees = trees;
    treeIndexGlobal = 0;

    // Define o tamanho mínimo e máximo da bolinha
    const MIN_RADIUS = 8;  // Antes era 3
    const MAX_RADIUS = 15; // Antes era 5

    // Pega todos os diâmetros existentes para calcular escala
    const diameters = trees.map(t => parseFloat(t.trunk_diameter) || 0);
    const minDiameter = Math.min(...diameters);
    const maxDiameter = Math.max(...diameters);

    // Função para mapear diâmetro para radius
    function scaleDiameter(d) {
    if (maxDiameter === minDiameter) return MIN_RADIUS; 
    // Cálculo de escala linear
    return MIN_RADIUS + (d - minDiameter) / (maxDiameter - minDiameter) * (MAX_RADIUS - MIN_RADIUS);
}

    // Dentro do loop que cria os markers
    trees.forEach((tree, index) => {
        if (!tree.latitude || !tree.longitude) return;

        const diametro = parseFloat(tree.trunk_diameter) || 0;
        const size = scaleDiameter(diametro);

        const marker = L.circleMarker([tree.latitude, tree.longitude], {
            radius: size,
            color: "#fff",
            weight: 2,
            fillColor: tree.color_code || "#358054",
            fillOpacity: 0.85,
            interactive: true
        }).addTo(markersLayer);

        // CLIQUE INTELIGENTE - mostra popup com setas
        marker.on('click', () => {
            currentTrees = filteredTrees;
            treeIndexGlobal = trees.indexOf(tree);

            const offsetY = -100;
            const targetPoint = map.latLngToContainerPoint([tree.latitude, tree.longitude]).add([0, offsetY]);
            const targetLatLng = map.containerPointToLatLng(targetPoint);
            map.setView(targetLatLng, map.getZoom(), { animate: false });

            marker.bindPopup(criarConteudoPopup(currentTrees, treeIndexGlobal)).openPopup();
        });

        treeMarkers[tree.id] = marker;
    });
}





        /* ============================================================
           GERADOR DE POPUP DINÂMICO (COM LABELS E NAVEGAÇÃO)
           ============================================================ */

        function criarConteudoPopup(listaArvores, indexInicial) {
            const container = document.createElement('div');
            container.className = 'p-4 font-sans relative';
            let indexAtual = indexInicial;

            // Centraliza o mapa e abre o popup na árvore correta
            function mostrarArvoreAtual() {
                const tree = listaArvores[indexAtual];
                const offsetY = -100; // desloca o popup um pouco pra cima
                const targetPoint = map.latLngToContainerPoint([tree.latitude, tree.longitude]).add([0, offsetY]);
                const targetLatLng = map.containerPointToLatLng(targetPoint);

                map.setView(targetLatLng, map.getZoom(), {
                    animate: false
                });

                const marker = treeMarkers[tree.id];
                if (marker) {
                    // reabre o popup atualizado
                    marker.bindPopup(container).openPopup();
                }
            }

            function render() {
                const tree = listaArvores[indexAtual];
                const total = listaArvores.length;

                container.innerHTML = `
            <div class="flex items-center justify-between mb-3 bg-gray-100 rounded-lg p-1 select-none">
                <button id="btn-prev" class="p-1 text-gray-600 hover:text-green-700 hover:bg-white rounded transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">
                    ${indexAtual + 1} de ${total} árvores
                </span>

                <button id="btn-next" class="p-1 text-gray-600 hover:text-green-700 hover:bg-white rounded transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <div class="mb-3">
                <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Espécie:</p>
                <h3 class="font-bold text-[#358054] text-sm leading-tight mb-2">${tree.species_name}</h3>

                <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Endereço:</p>
                <p class="text-xs text-gray-600 pb-2 border-b border-gray-100 leading-snug">
                    ${tree.address || 'Localização não informada'}
                </p>
            </div>

            <a href="/trees/${tree.id}" 
               class="group flex items-center justify-between w-full bg-[#f0fdf4] hover:bg-[#dcfce7] 
                      border border-[#bbf7d0] rounded-lg px-3 py-2 transition-all duration-200">
                <span class="text-xs font-bold text-[#166534]">Ver detalhes</span>
            </a>
        `;

                // Liga os botões e, ao mudar, reabre o popup da nova árvore
                const btnPrev = container.querySelector('#btn-prev');
                const btnNext = container.querySelector('#btn-next');

                btnPrev.onclick = (e) => {
                    e.stopPropagation();
                    indexAtual = (indexAtual - 1 + total) % total;
                    render();
                    mostrarArvoreAtual(); // centraliza e abre novo popup
                };

                btnNext.onclick = (e) => {
                    e.stopPropagation();
                    indexAtual = (indexAtual + 1) % total;
                    render();
                    mostrarArvoreAtual(); // idem
                };
            }

            render();
            return container;
        }







        /* ============================================================
           FILTRAGEM
           ============================================================ */

        function destacarBairro(bairroId) {
            if (!bairrosGeoLayer) return;
            bairrosGeoLayer.eachLayer(layer => {
                const props = layer.feature.properties;
                if (props.id_bairro == bairroId) {
                    layer.setStyle({
                        color: "#0084ff",
                        weight: 3,
                        fillOpacity: 0.08
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

        document.getElementById("limparFiltro").addEventListener("click", () => {
            document.getElementById("bairro").value = "";
            document.getElementById("especie").value = "";
            document.getElementById("search").value = "";
            exibirArvores(allTrees);
            if (bairrosGeoLayer) {
                bairrosGeoLayer.eachLayer(layer => {
                    layer.setStyle({
                        color: "#00000040",
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

@extends('layouts.dashboard')

@section('content')
<div class="perfil-box inline-block">
<h2 class="text-3xl font-bold text-[#358054] mb-0">
        Painel de Administração – Mapa de Árvores
    </h2>
</div>
    

    {{-- ALERTAS --}}
    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            <strong>Sucesso!</strong> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <strong>Erro!</strong> {{ session('error') }}
        </div>
    @endif

    {{-- CARD: ADICIONAR ARVORE --}}
    <div class="bg-white border border-gray-200 shadow rounded-xl mb-10 p-8">

        <h3 class="text-2xl font-bold mb-6 text-gray-800">Adicionar Nova Árvore</h3>

        <form method="POST" action="{{ route('admin.map.store') }}" class="space-y-10">
            @csrf

            {{-- SEÇÃO 1: IDENTIFICAÇÃO --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Identificação</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Nome --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="name"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Endereço --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Endereço *</label>
                        <input type="text" name="address"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Bairro --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro *</label>
                        <select name="bairro_id" id="bairro_id"
                            class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500" required>
                            <option value="">Selecione um bairro</option>

                            {{-- Lista carregada do backend --}}
                            @foreach($bairros as $bairro)
                                <option value="{{ $bairro->id }}">{{ $bairro->nome }}</option>
                            @endforeach
                        </select>

                        <p class="text-xs text-gray-500 mt-1">Será preenchido automaticamente ao clicar no mapa, mas você pode alterar.</p>
                    </div>


                    {{-- Espécie --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Espécie *</label>
                        <input type="text" name="species_name"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Nome vulgar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome vulgar *</label>
                        <input type="text" name="vulgar_name"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Nome científico --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome científico *</label>
                        <input type="text" name="scientific_name"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Caso não tenha espécie --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Caso não tenha espécie</label>
                        <input type="text" name="no_species_case"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500">
                    </div>

                </div>
            </div>

            {{-- SEÇÃO 2: COORDENADAS --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Coordenadas</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Latitude --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude *</label>
                        <input type="number" step="0.0000001" id="latitude" name="latitude"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                    </div>

                    {{-- Longitude --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude *</label>
                        <input type="number" step="0.0000001" id="longitude" name="longitude"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                    </div>

                </div>
            </div>

            {{-- SEÇÃO 3: DADOS GERAIS --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Dados Gerais</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Estado de saúde --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Saúde *</label>
                        <select name="health_status"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                                required>
                            <option value="good">Boa</option>
                            <option value="fair">Regular</option>
                            <option value="poor">Ruim</option>
                        </select>
                    </div>

                    {{-- Data plantio --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Plantio *</label>
                        <input type="date" name="planted_at" max="{{ now()->format('Y-m-d') }}"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Diâmetro tronco --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diâmetro do Tronco (cm) *</label>
                        <input type="number" step="0.01" name="trunk_diameter"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                </div>
            </div>

            {{-- SEÇÃO 4: DIMENSÕES --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Dimensões</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CAP (cm) *</label>
                        <input type="number" step="0.01" name="cap"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Altura (m) *</label>
                        <input type="number" step="0.01" name="height"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Altura da copa (m) *</label>
                        <input type="number" step="0.01" name="crown_height"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diâmetro longitudinal (m) *</label>
                        <input type="number" step="0.01" name="crown_diameter_longitudinal"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diâmetro perpendicular (m) *</label>
                        <input type="number" step="0.01" name="crown_diameter_perpendicular"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                </div>
            </div>

            {{-- SEÇÃO 5: CARACTERÍSTICAS --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Características</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Bifurcação --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Bifurcação *</label>
                        <select name="bifurcation_type"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                                required>
                            <option value="ausente">Ausente</option>
                            <option value="U">U</option>
                            <option value="V">V</option>
                        </select>
                    </div>

                    {{-- Equilíbrio fuste --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio Fuste *</label>
                        <select name="stem_balance"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                                required>
                            <option value="ausente">Ausente</option>
                            <option value="maior_45">Maior que 45°</option>
                            <option value="menor_45">Menor que 45°</option>
                        </select>
                    </div>

                    {{-- Equilíbrio copa --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio da copa *</label>
                        <select name="crown_balance"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                                required>
                            <option value="equilibrada">Equilibrada</option>
                            <option value="medianamente_desequilibrada">Medianamente Desequilibrada</option>
                            <option value="desequilibrada">Desequilibrada</option>
                            <option value="muito_desequilibrada">Muito Desequilibrada</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- SEÇÃO 6: AMBIENTE --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Ambiente</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Organismos --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organismos *</label>
                        <select name="organisms"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                                required>
                            <option value="ausente">Ausente</option>
                            <option value="infestacao_inicial">Infestação Inicial</option>
                        </select>
                    </div>

                    {{-- Alvo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alvo *</label>
                        <input type="text" name="target"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Injúrias --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Injúrias mecânicas *</label>
                        <input type="text" name="injuries"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Fiação --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado da fiação *</label>
                        <select name="wiring_status"
                                class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                                required>
                            <option value="pode_interferir">Pode interferir</option>
                            <option value="interfere">Interfere</option>
                            <option value="nao_interfere">Não interfere</option>
                        </select>
                    </div>

                    {{-- Larguras --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura total (m) *</label>
                        <input type="number" step="0.01" name="total_width"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura da rua (m) *</label>
                        <input type="number" step="0.01" name="street_width"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    {{-- Gola --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Altura da gola (m) *</label>
                        <input type="number" step="0.01" name="gutter_height"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura da gola (m) *</label>
                        <input type="number" step="0.01" name="gutter_width"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comprimento da gola (m) *</label>
                        <input type="number" step="0.01" name="gutter_length"
                               class="w-full border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500"
                               required>
                    </div>

                </div>
            </div>

            {{-- BOTÃO --}}
            <button
                class="px-6 py-3 bg-[#358054] hover:bg-[#2e6f48] text-white rounded-lg text-lg shadow-md active:scale-95 transition">
                Adicionar Árvore
            </button>
        </form>
    </div>

    {{-- MAPA --}}
    <div class="bg-white border border-gray-200 shadow rounded-xl p-8">
        <h3 class="text-2xl font-bold mb-4 text-gray-800">Mapa de Árvores</h3>
        <p class="text-sm text-gray-600 mb-4">Clique no mapa para definir coordenadas.</p>

        <div id="map" class="rounded-xl overflow-hidden" style="height: 500px;"></div>
    </div>
@endsection


@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

   <script>
document.addEventListener("DOMContentLoaded", async function () {

    /**
     * ==============================================================
     * 1. CONFIGURAÇÃO DO MAPA
     * ==============================================================
     */
    const map = L.map('map').setView([-22.6091, -43.7089], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let tempMarker = null;

    // Campos do formulário
    const latInput = document.getElementById("latitude");
    const lngInput = document.getElementById("longitude");
    const addressInput = document.querySelector("input[name='address']");
    const bairroSelect = document.getElementById("bairro_id");


    /**
     * ==============================================================
     * 2. CARREGAR GEOJSON DOS BAIRROS (PARA DETECTAR AUTOMÁTICO)
     * ==============================================================
     */
    let bairrosPoligonos = [];

    try {
        const geojsonResponse = await fetch("/bairros.json");
        const geojsonData = await geojsonResponse.json();
        bairrosPoligonos = geojsonData.features;

        console.log("GeoJSON carregado:", bairrosPoligonos.length, "bairros.");
    } catch (err) {
        console.warn("Erro ao carregar bairros.json:", err);
    }


    /**
     * ==============================================================
     * 3. FUNÇÃO: Verifica se uma coordenada está dentro de um polígono
     * ==============================================================
     */
    function pointInPolygon(lat, lng, polygon) {
        let inside = false;
        const x = lng, y = lat;

        for (let ring of polygon.coordinates) {
            for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                const xi = ring[i][0], yi = ring[i][1];
                const xj = ring[j][0], yj = ring[j][1];

                const intersect = ((yi > y) !== (yj > y)) &&
                    (x < (xj - xi) * (y - yi) / (yj - yi) + xi);

                if (intersect) inside = !inside;
            }
        }
        return inside;
    }


    /**
     * ==============================================================
     * 4. FUNÇÃO: Detectar Bairro baseado no polígono
     * ==============================================================
     */
    function detectarBairro(lat, lng) {
        for (let f of bairrosPoligonos) {
            if (f.geometry && f.geometry.type === "Polygon") {
                if (pointInPolygon(lat, lng, f.geometry)) {
                    const id = f.properties.id_bairro;
                    const nome = f.properties.nome;
                    console.log("Bairro identificado via polígono:", nome);
                    return id;
                }
            }
        }
        return null;
    }


    /**
     * ==============================================================
     * 5. FUNÇÃO: Reverse Geocoding (pegar nome da rua automaticamente)
     * ==============================================================
     */
    async function buscarEndereco(lat, lng) {
        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
            const response = await fetch(url, {
                headers: { "User-Agent": "Arvores-Paracambi-System" }
            });

            const data = await response.json();

            // Rua
            const rua = data.address?.road || "";
            console.log("Rua detectada:", rua);

            // Retorna objeto
            return {
                rua,
                sugeridoBairro: data.address?.suburb || null
            };

        } catch (e) {
            console.warn("Erro ao consultar Nominatim:", e);
            return { rua: "", sugeridoBairro: null };
        }
    }


    /**
     * ==============================================================
     * 6. EVENTO: Clique no mapa → preenche tudo automaticamente
     * ==============================================================
     */
    map.on("click", async e => {

        const lat = e.latlng.lat.toFixed(7);
        const lng = e.latlng.lng.toFixed(7);

        // Preenche coordenadas
        latInput.value = lat;
        lngInput.value = lng;

        // Remove marcador anterior
        if (tempMarker) map.removeLayer(tempMarker);

        tempMarker = L.marker([lat, lng]).addTo(map)
            .bindPopup("Coordenada selecionada").openPopup();

        /**
         * 6.1 — BUSCAR ENDEREÇO AUTOMÁTICO
         */
        const info = await buscarEndereco(lat, lng);
        addressInput.value = info.rua || "";


        /**
         * 6.2 — DETECTAR BAIRRO VIA POLÍGONO
         */
        const bairroId = detectarBairro(parseFloat(lat), parseFloat(lng));

        if (bairroId) {
            bairroSelect.value = bairroId; // Preenche automaticamente
        } else {
            console.log("Ponto fora de qualquer bairro no GeoJSON.");
        }
    });


    /**
     * ==============================================================
     * 7. EXIBIR ÁRVORES EXISTENTES NO MAPA
     * ==============================================================
     */
    const trees = @json($trees);

    trees.forEach(tree => {
        L.marker([tree.latitude, tree.longitude]).addTo(map)
            .bindPopup(`
                <div style='font-weight:600; margin-bottom:4px;'>${tree.species.name}</div>
                <div style='color:#555;'>${tree.address || 'Sem endereço'}</div>
            `);
    });

});
</script>

@endpush

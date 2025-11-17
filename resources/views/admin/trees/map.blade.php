@extends('layouts.dashboard')

@section('content')

<h2 class="text-3xl font-bold text-[#358054] mb-6">
    Painel de Administração – Mapa de Árvores
</h2>

{{-- MENSAGENS --}}
@if (session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        <strong>Sucesso!</strong> {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <strong>Erro!</strong> {{ session('error') }}
    </div>
@endif


{{-- CARD: ADICIONAR ÁRVORE --}}
<div class="bg-white shadow-sm rounded-lg mb-8 p-6 tree-card">

    <h3 class="text-xl font-semibold mb-4">Adicionar Nova Árvore</h3>

    <form method="POST" action="{{ route('admin.map.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="form-label">Nome *</label>
                <input type="text" name="name" class="input" required>
            </div>

            <div>
                <label class="form-label">Endereço *</label>
                <input type="text" name="address" class="input" required>
            </div>

            <div>
                <label class="form-label">Espécie *</label>
                <input type="text" name="species_name" class="input" required>
            </div>

            <div>
                <label class="form-label">Latitude *</label>
                <input type="number" step="0.0000001" id="latitude" name="latitude" class="input" required>
                <p class="hint">Clique no mapa para preencher</p>
            </div>

            <div>
                <label class="form-label">Longitude *</label>
                <input type="number" step="0.0000001" id="longitude" name="longitude" class="input" required>
                <p class="hint">Clique no mapa para preencher</p>
            </div>

            <div>
                <label class="form-label">Estado de Saúde *</label>
                <select name="health_status" class="input" required>
                    <option value="good">Boa</option>
                    <option value="fair">Regular</option>
                    <option value="poor">Ruim</option>
                </select>
            </div>

            <div>
                <label class="form-label">Data de Plantio *</label>
                <input type="date" name="planted_at"
                    class="input"
                    max="{{ now()->format('Y-m-d') }}"
                    required>
            </div>

            <div>
                <label class="form-label">Diâmetro do Tronco (cm) *</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input" required>
            </div>

            <div>
                <label class="form-label">Nome vulgar *</label>
                <input type="text" name="vulgar_name" class="input" required>
            </div>

            <div>
                <label class="form-label">Nome científico *</label>
                <input type="text" name="scientific_name" class="input" required>
            </div>

            <div>
                <label class="form-label">CAP (cm) *</label>
                <input type="number" step="0.01" name="cap" class="input" required>
            </div>

            <div>
                <label class="form-label">Altura (m) *</label>
                <input type="number" step="0.01" name="height" class="input" required>
            </div>

            <div>
                <label class="form-label">Altura de copa (m) *</label>
                <input type="number" step="0.01" name="crown_height" class="input" required>
            </div>

            <div>
                <label class="form-label">Diâmetro de copa longitudinal (m) *</label>
                <input type="number" step="0.01" name="crown_diameter_longitudinal" class="input" required>
            </div>

            <div>
                <label class="form-label">Diâmetro de copa perpendicular (m) *</label>
                <input type="number" step="0.01" name="crown_diameter_perpendicular" class="input" required>
            </div>

            <div>
                <label class="form-label">Tipo de Bifurcação *</label>
                <select name="bifurcation_type" class="input" required>
                    <option value="">Selecione</option>
                    <option value="ausente">Ausente</option>
                    <option value="U">U</option>
                    <option value="V">V</option>
                </select>
            </div>

            <div>
                <label class="form-label">Equilíbrio Fuste *</label>
                <select name="stem_balance" class="input" required>
                    <option value="">Selecione</option>
                    <option value="ausente">Ausente</option>
                    <option value="maior_45">Maior que 45°</option>
                    <option value="menor_45">Menor que 45°</option>
                </select>
            </div>

            <div>
                <label class="form-label">Equilíbrio da copa *</label>
                <select name="crown_balance" class="input" required>
                    <option value="">Selecione</option>
                    <option value="equilibrada">Equilibrada</option>
                    <option value="medianamente_desequilibrada">Medianamente Desequilibrada</option>
                    <option value="desequilibrada">Desequilibrada</option>
                    <option value="muito_desequilibrada">Muito Desequilibrada</option>
                </select>
            </div>

            <div>
                <label class="form-label">Organismos *</label>
                <select name="organisms" class="input" required>
                    <option value="">Selecione</option>
                    <option value="ausente">Ausente</option>
                    <option value="infestacao_inicial">Infestação Inicial</option>
                </select>
            </div>

            <div>
                <label class="form-label">Alvo *</label>
                <input type="text" name="target" class="input" required>
            </div>

            <div>
                <label class="form-label">Injúrias mecânicas *</label>
                <input type="text" name="injuries" class="input" required>
            </div>

            <div>
                <label class="form-label">Estado da fiação *</label>
                <select name="wiring_status" class="input" required>
                    <option value="">Selecione</option>
                    <option value="pode_interferir">Pode interferir</option>
                    <option value="interfere">Interfere</option>
                    <option value="nao_interfere">Não interfere</option>
                </select>
            </div>

            <div>
                <label class="form-label">Largura total (m) *</label>
                <input type="number" step="0.01" name="total_width" class="input" required>
            </div>

            <div>
                <label class="form-label">Largura da rua (m) *</label>
                <input type="number" step="0.01" name="street_width" class="input" required>
            </div>

            <div>
                <label class="form-label">Altura da gola (m) *</label>
                <input type="number" step="0.01" name="gutter_height" class="input" required>
            </div>

            <div>
                <label class="form-label">Largura da gola (m) *</label>
                <input type="number" step="0.01" name="gutter_width" class="input" required>
            </div>

            <div>
                <label class="form-label">Comprimento da gola (m) *</label>
                <input type="number" step="0.01" name="gutter_length" class="input" required>
            </div>

            <div>
                <label class="form-label">Caso não tenha espécie</label>
                <input type="text" name="no_species_case" class="input">
            </div>

        </div>


        <button class="btn bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow">
            Adicionar Árvore
        </button>

    </form>
</div>


{{-- MAPA --}}
<div class="bg-white shadow-sm rounded-lg p-6 tree-card">

    <h3 class="text-xl font-semibold mb-4">Mapa de Árvores</h3>
    <p class="text-sm text-gray-600 mb-3">Clique no mapa para definir coordenadas.</p>

    <style>
        #map {
            height: 500px !important;
            width: 100% !important;
            min-height: 500px !important;
            z-index: 1;
        }
    </style>

    <div id="map"></div>
</div>

@endsection


@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const map = L.map('map').setView([-22.6091, -43.7089], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let tempMarker = null;

    map.on('click', e => {
        const lat = e.latlng.lat.toFixed(7);
        const lng = e.latlng.lng.toFixed(7);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (tempMarker) map.removeLayer(tempMarker);

        tempMarker = L.marker([lat, lng]).addTo(map);
    });

    const trees = @json($trees);

    trees.forEach(tree => {
        L.marker([tree.latitude, tree.longitude]).addTo(map)
            .bindPopup(`<b>${tree.species.name}</b><br>${tree.address || 'Sem endereço'}`);
    });

});
</script>
@endpush

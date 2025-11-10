@extends('layouts.admin')

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

            {{-- Nome --}}
            <div class="flex flex-col">
                <label class="form-label">Nome *</label>
                <input type="text" name="name" class="input" required>
            </div>

            {{-- Espécie --}}
            <div class="flex flex-col">
                <label class="form-label">Espécie *</label>
                <input type="text" name="species_name" class="input" required>
            </div>

            {{-- Latitude --}}
            <div class="flex flex-col">
                <label class="form-label">Latitude *</label>
                <input type="number" step="0.0000001" id="latitude" name="latitude" class="input" required>
                <p class="hint">Clique no mapa para preencher</p>
            </div>

            {{-- Longitude --}}
            <div class="flex flex-col">
                <label class="form-label">Longitude *</label>
                <input type="number" step="0.0000001" id="longitude" name="longitude" class="input" required>
                <p class="hint">Clique no mapa para preencher</p>
            </div>

            {{-- Estado de Saúde --}}
            <div class="flex flex-col">
                <label class="form-label">Estado de Saúde *</label>
                <select name="health_status" class="input" required>
                    <option value="good">Boa</option>
                    <option value="fair">Regular</option>
                    <option value="poor">Ruim</option>
                </select>
            </div>

            {{-- Data --}}
            <div class="flex flex-col">
                <label class="form-label">Data de Plantio *</label>
                <input type="date" name="planted_at" class="input" required>
            </div>

            {{-- Tronco --}}
            <div class="flex flex-col">
                <label class="form-label">Diâmetro (cm)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

            {{-- Endereço --}}
            <div class="flex flex-col">
                <label class="form-label">Endereço</label>
                <input type="text" name="address" class="input">
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

    <div id="map" class="w-full rounded-lg shadow-md" style="height: 500px;"></div>

</div>

@endsection


@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
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
</script>
@endpush

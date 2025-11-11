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

            {{-- Endereço --}}
            <div class="flex flex-col">
                <label class="form-label">Endereço</label>
                <input type="text" name="address" class="input">
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

            
            {{-- Nome vulgar/gola --}}
            <div class="flex flex-col">
                <label class="form-label">Vulgar/Gola</label>
                <input type="text" name="address" class="input">
            </div>

            {{-- Nome cientifico --}}
            <div class="flex flex-col">
                <label class="form-label">Nome científico</label>
                <input type="text" name="address" class="input">
            </div>
            
            
            {{-- Circunferencia na altura do peito --}}
            <div class="flex flex-col">
                <label class="form-label">CAP(cm)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>
    
            {{-- Altura--}}
            <div class="flex flex-col">
                <label class="form-label">Altura(m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>
    
            {{-- Altura de copa --}}
            <div class="flex flex-col">
                <label class="form-label">Altura de copa(m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

               {{-- Diâmetro da Copa Longitudinal (ou da Gola) (m) --}}
            <div class="flex flex-col">
                <label class="form-label">Diâmetro de copa Longitudinal(m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

             {{--Diâmetro da Copa Perpendicular (ou da Gola) (m)--}}
            <div class="flex flex-col">
                <label class="form-label">Diâmetro de copa Perpendicular(m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

             {{-- Tipo de Bifurcação --}}
            <div class="flex flex-col">
                <label class="form-label">Tipo de Bifurcação *</label>
                <select name="health_status" class="input" required>
                    <option value="good">Ausente</option>
                    <option value="fair">U</option>
                    <option value="poor">V</option>
                </select>
            </div>

                 {{-- Equilíbrio Fuste (Inclinação) --}}
            <div class="flex flex-col">
                <label class="form-label">Equilíbrio Fuste (Inclinação) *</label>
                <select name="health_status" class="input" required>
                    <option value="good">Ausente</option>
                    <option value="fair">Maior que 45°</option>
                    <option value="poor">Menor que 45°</option>
                </select>
            </div>

      {{-- Equilibrio da copa --}}
            <div class="flex flex-col">
                <label class="form-label">Equilibrio da copa *</label>
                <select name="health_status" class="input" required>
                    <option value="good">Equilibrada </option>
                    <option value="fair">Muito desequilibrada </option>
                    <option value="poor">Medianamente desequilibrada</option>
                     <option value="poor">Desequilibrada</option>
                </select>
            </div>

              {{--Organismos xilófagos e/ou patogênicos --}}
            <div class="flex flex-col">
                <label class="form-label">Organismos xilófagos e/ou patogênicos*</label>
                <select name="health_status" class="input" required>
                    <option value="good">Infertação Inicial </option>
                    <option value="fair">Ausente</option>
                    
                </select>
            </div>

            {{-- Alvo--}}
            <div class="flex flex-col">
                <label class="form-label">Alvo</label>
                <input type="text" name="address" class="input">
            </div>

               {{-- Injúrias mecânicas e cavidades --}}
            <div class="flex flex-col">
                <label class="form-label">Injúrias mecânicas e cavidades</label>
                <input type="text" name="address" class="input">
            </div>

      {{--Estado de Fiação --}}
            <div class="flex flex-col">
                <label class="form-label">Estado da fiação*</label>
                <select name="health_status" class="input" required>
                    <option value="good">Pode Interferir </option>
                    <option value="fair">Interfere</option>
                     <option value="fair">Não Interfere</option>
                    
                </select>
            </div>

                     {{-- Largura total (Muro a Muro) (m) --}}
            <div class="flex flex-col">
                <label class="form-label">Largura total (Muro a Muro) (m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

            
                     {{-- Largura rua (Sarjeta a Sarjeta) (m) --}}
            <div class="flex flex-col">
                <label class="form-label">Largura rua (Sarjeta a Sarjeta) (m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

                 
                     {{-- Altura da gola (m)--}}
            <div class="flex flex-col">
                <label class="form-label">Altura da gola (m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

                     
                     {{-- Largura da gola (m)--}}
            <div class="flex flex-col">
                <label class="form-label">Largura da gola (m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

       {{-- Comprimento da gola (m)--}}
            <div class="flex flex-col">
                <label class="form-label">Comprimento da gola (m)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

            {{-- Caso não tenha Espécie --}}
            <div class="flex flex-col">
                <label class="form-label">Caso não tenha espécie </label>
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

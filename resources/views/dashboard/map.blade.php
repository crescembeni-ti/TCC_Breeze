<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Painel de Administração - Mapa de Árvores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensagens de Sucesso/Erro -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Sucesso!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Erro!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Formulário de Adição de Árvore -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Adicionar Nova Árvore ao Mapa</h3>
                    
                    <form method="POST" action="{{ route('admin.map.store') }}" class="space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nome da Árvore ou Local -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome da Árvore ou Local *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Espécie -->
                            <div>
                                <label for="species_name" class="block text-sm font-medium text-gray-700">Espécie *</label>
                                <input type="text" name="species_name" id="species_name" value="{{ old('species_name') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ex: Ipê Amarelo, Pau-Brasil, etc.">
                                @error('species_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Latitude -->
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude *</label>
                                <input type="number" step="0.0000001" name="latitude" id="latitude" value="{{ old('latitude', '-22.6091') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="-22.6091">
                                @error('latitude')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Clique no mapa abaixo para preencher automaticamente</p>
                            </div>

                            <!-- Longitude -->
                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude *</label>
                                <input type="number" step="0.0000001" name="longitude" id="longitude" value="{{ old('longitude', '-43.7089') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="-43.7089">
                                @error('longitude')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Clique no mapa abaixo para preencher automaticamente</p>
                            </div>

                            <!-- Estado de Saúde -->
                            <div>
                                <label for="health_status" class="block text-sm font-medium text-gray-700">Estado de Saúde *</label>
                                <select name="health_status" id="health_status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="good" {{ old('health_status') == 'good' ? 'selected' : '' }}>Boa</option>
                                    <option value="fair" {{ old('health_status') == 'fair' ? 'selected' : '' }}>Regular</option>
                                    <option value="poor" {{ old('health_status') == 'poor' ? 'selected' : '' }}>Ruim</option>
                                </select>
                                @error('health_status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data de Plantio -->
                            <div>
                                <label for="planted_at" class="block text-sm font-medium text-gray-700">Data de Plantio *</label>
                                <input type="date" name="planted_at" id="planted_at" value="{{ old('planted_at') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('planted_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Diâmetro do Tronco (Opcional) -->
                            <div>
                                <label for="trunk_diameter" class="block text-sm font-medium text-gray-700">Diâmetro do Tronco (cm)</label>
                                <input type="number" step="0.01" name="trunk_diameter" id="trunk_diameter" value="{{ old('trunk_diameter') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ex: 45.5">
                                @error('trunk_diameter')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Endereço (Opcional) -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Endereço</label>
                                <input type="text" name="address" id="address" value="{{ old('address') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ex: Rua Principal, 123 - Centro">
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Adicionar Árvore
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mapa Interativo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Mapa de Árvores Cadastradas</h3>
                    <p class="text-sm text-gray-600 mb-4">Clique no mapa para selecionar a localização da nova árvore. As coordenadas serão preenchidas automaticamente no formulário acima.</p>
                    
                    <div id="map" style="height: 500px; width: 100%;" class="rounded-lg shadow-md"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

    <script>
        // Inicializar o mapa centrado em Paracambi-RJ
        const map = L.map('map').setView([-22.6091, -43.7089], 14);

        // Adicionar tiles do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Marcador temporário para seleção de localização
        let tempMarker = null;

        // Adicionar evento de clique no mapa para selecionar localização
        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(7);
            const lng = e.latlng.lng.toFixed(7);
            
            // Atualizar os campos de latitude e longitude
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            // Remover marcador temporário anterior
            if (tempMarker) {
                map.removeLayer(tempMarker);
            }
            
            // Adicionar novo marcador temporário
            tempMarker = L.marker([lat, lng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            
            tempMarker.bindPopup(`<b>Nova Localização</b><br>Lat: ${lat}<br>Lng: ${lng}`).openPopup();
        });

        // Adicionar marcadores das árvores existentes
        const trees = @json($trees);
        
        trees.forEach(tree => {
            const marker = L.marker([tree.latitude, tree.longitude], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            
            marker.bindPopup(`
                <b>${tree.species.name}</b><br>
                ${tree.address || 'Endereço não informado'}<br>
                <small>Status: ${tree.health_status === 'good' ? 'Boa' : tree.health_status === 'fair' ? 'Regular' : 'Ruim'}</small>
            `);
        });
    </script>
</x-app-layout>


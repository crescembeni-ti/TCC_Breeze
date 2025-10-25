<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa de Árvores</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Mapa de Árvores - Paracambi-RJ</h1>
                    <div class="flex gap-4">
                        <a href="{{ route('about') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Sobre</a>
                        <a href="{{ route('contact') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Contato</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Login</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">Registrar</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Total de Árvores</h3>
                    <p class="text-4xl font-bold text-green-600">{{ $stats['total_trees'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Atividades Registradas</h3>
                    <p class="text-4xl font-bold text-blue-600">{{ $stats['total_activities'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Espécies no Mapa</h3>
                    <p class="text-4xl font-bold text-purple-600">{{ $stats['total_species'] }}</p>
                </div>
            </div>

            <!-- Map -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Mapa Interativo</h2>
                <div id="map" class="rounded-lg"></div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Atividades Recentes</h2>
                <div class="space-y-4">
                    @forelse($recentActivities as $activity)
                        <div class="border-l-4 border-green-500 pl-4 py-2">
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

        <!-- Footer -->
        <footer class="bg-white shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-600">© {{ date('Y') }} Mapa de Árvores. Desenvolvido com Laravel e Leaflet.</p>
            </div>
        </footer>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>

        // Coordenadas de Paracambi/RJ
        const PARACAMBI_CENTER = [-22.6063, -43.7086];
        
        // Inicializar o mapa (Paracambi-RJ, Brasil)
        const map = L.map('map').setView([-22.6111, -43.7089], 14);

        // Adicionar camada de tiles do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Definir limites (caixa que envolve Paracambi)
        var bounds = [
            [-22.71, -43.85], // sudoeste (lat, lng)
            [-22.51, -43.58]  // nordeste (lat, lng)
        ];

        // Aplica os limites no mapa
        map.setMaxBounds(bounds);

        // Impede de "escapar" dos limites ao arrastar
        map.on('drag', function () {
            map.panInsideBounds(bounds, { animate: false });
        });

        // Define zoom mínimo e máximo
        map.setMinZoom(13);
        map.setMaxZoom(17);

        // Buscar dados das árvores via API
        fetch('{{ route('trees.data') }}')
            .then(response => response.json())
            .then(trees => {
                trees.forEach(tree => {
                    // Calcular o tamanho do marcador baseado no diâmetro do tronco
                    const radius = Math.max(5, tree.trunk_diameter / 5);
                    
                    // Criar marcador circular colorido
                    const marker = L.circleMarker([tree.latitude, tree.longitude], {
                        radius: radius,
                        fillColor: tree.color_code,
                        color: '#000',
                        weight: 1,
                        opacity: 1,
                        fillOpacity: 0.7
                    }).addTo(map);

                    // Adicionar popup com informações da árvore
                    marker.bindPopup(`
                        <div class="p-2">
                            <h3 class="font-bold text-lg mb-2">${tree.species_name}</h3>
                            <p><strong>Endereço:</strong> ${tree.address}</p>
                            <p><strong>Diâmetro do Tronco:</strong> ${tree.trunk_diameter} cm</p>
                            <p><strong>Status:</strong> ${tree.health_status}</p>
                            <a href="/trees/${tree.id}" class="text-green-600 hover:underline mt-2 inline-block">Ver detalhes</a>
                        </div>
                    `);
                });
            })
            .catch(error => console.error('Erro ao carregar árvores:', error));
    </script>
</body>
</html>


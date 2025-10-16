<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalhes da Árvore - {{ $tree->species->name }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        #tree-map {
            height: 400px;
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
                    <h1 class="text-3xl font-bold text-gray-900">Detalhes da Árvore</h1>
                    <a href="{{ route('home') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Voltar ao Mapa</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Tree Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Informações da Árvore</h2>
                    
                    @if($tree->photo)
                    <div class="mb-6">
                        <img src="{{ $tree->photo }}" alt="Foto de {{ $tree->species->name }}" class="w-full h-64 object-cover rounded-lg shadow-md" onerror="this.style.display='none'">
                    </div>
                    @endif
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600">Espécie</h3>
                            <p class="text-lg text-gray-900">{{ $tree->species->name }}</p>
                            @if($tree->species->scientific_name)
                                <p class="text-sm italic text-gray-600">{{ $tree->species->scientific_name }}</p>
                            @endif
                        </div>

                        @if($tree->species->description)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600">Descrição</h3>
                            <p class="text-gray-900">{{ $tree->species->description }}</p>
                        </div>
                        @endif

                        <div>
                            <h3 class="text-sm font-semibold text-gray-600">Endereço</h3>
                            <p class="text-gray-900">{{ $tree->address }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600">Diâmetro do Tronco</h3>
                                <p class="text-lg text-gray-900">{{ $tree->trunk_diameter }} cm</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600">Status de Saúde</h3>
                                <p class="text-lg text-gray-900 capitalize">{{ $tree->health_status }}</p>
                            </div>
                        </div>

                        @if($tree->planted_at)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600">Data de Plantio</h3>
                            <p class="text-gray-900">{{ $tree->planted_at->format('d/m/Y') }}</p>
                        </div>
                        @endif

                        @if($tree->user)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600">Registrado por</h3>
                            <p class="text-gray-900">{{ $tree->user->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Map -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Localização</h2>
                    <div id="tree-map" class="rounded-lg"></div>
                </div>
            </div>

            <!-- Activities -->
            <div class="bg-white rounded-lg shadow p-6 mt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Histórico de Atividades</h2>
                
                @if($tree->activities->count() > 0)
                    <div class="space-y-4">
                        @foreach($tree->activities as $activity)
                            <div class="border-l-4 border-green-500 pl-4 py-2">
                                <p class="text-sm text-gray-600">{{ $activity->activity_date->format('d/m/Y H:i') }}</p>
                                <p class="text-gray-900">
                                    <strong>{{ ucfirst($activity->type) }}</strong> 
                                    por {{ $activity->user->name }}
                                </p>
                                @if($activity->description)
                                    <p class="text-gray-700 mt-1">{{ $activity->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">Nenhuma atividade registrada para esta árvore.</p>
                @endif
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
        // Inicializar o mapa centrado na árvore
        const map = L.map('tree-map').setView([{{ $tree->latitude }}, {{ $tree->longitude }}], 16);

        // Adicionar camada de tiles do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Adicionar marcador da árvore
        const radius = Math.max(8, {{ $tree->trunk_diameter }} / 5);
        L.circleMarker([{{ $tree->latitude }}, {{ $tree->longitude }}], {
            radius: radius,
            fillColor: '{{ $tree->species->color_code }}',
            color: '#000',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map).bindPopup('<strong>{{ $tree->species->name }}</strong>').openPopup();
    </script>
</body>
</html>


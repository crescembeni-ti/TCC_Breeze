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

    <!-- Estilos globais e da página -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/trees.css'])

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>

<body class="font-sans antialiased tree-page">
    <div class="min-h-screen">
        <!-- HEADER -->
       <header class="site-header relative" style="background-color: #beffb4;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
        
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-20 w-20 object-contain">
            <h1 class="text-4xl font-bold">
                <span class="text-[#358054]">Detalhes</span>
                <span class="text-[#a0c520]"> das Arvores</span>
            </h1>
        </div>

        <a href="{{ route('home') }}" 
           class="btn bg-green-600 hover:bg-green-700 transition-all duration-300 transform hover:scale-105">
            ← Voltar ao Mapa
        </a>
    </div>
</header>


        <!-- CONTEÚDO PRINCIPAL -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- INFORMAÇÕES DA ÁRVORE -->
                <div class="bg-white p-6 tree-card">
                    <h2 class="text-2xl font-bold text-[#358054] mb-4">🌳 Informações da Árvore</h2>
                    
                    @if($tree->photo)
                    <div class="mb-6">
                        <img src="{{ $tree->photo }}" alt="Foto de {{ $tree->species->name }}" 
                             class="w-full h-64 object-cover rounded-lg shadow-md" 
                             onerror="this.style.display='none'">
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

                <!-- MAPA -->
                <div class="bg-white p-6 tree-card">
                    <h2 class="text-2xl font-bold text-[#358054] mb-4">📍 Localização</h2>
                    <div id="tree-map" class="rounded-lg"></div>
                </div>
            </div>

            <!-- HISTÓRICO DE ATIVIDADES -->
            <div class="bg-white p-6 mt-10 tree-card">
                <h2 class="text-2xl font-bold text-[#358054] mb-4">🪵 Histórico de Atividades</h2>
                
                @if($tree->activities->count() > 0)
                    <div class="space-y-4">
                        @foreach($tree->activities as $activity)
                            <div class="activity-item">
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

        <!-- FOOTER -->
        <footer class="shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-700 font-medium">© {{ date('Y') }} Mapa de Árvores de Paracambi 🌿</p>
            </div>
        </footer>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('tree-map').setView([{{ $tree->latitude }}, {{ $tree->longitude }}], 16);

        const satellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            { attribution: 'Tiles © Esri' }
        ).addTo(map);

        const labels = L.tileLayer(
            'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png',
            { subdomains: 'abcd', maxZoom: 20 }
        ).addTo(map);

        const radius = Math.max(8, {{ $tree->trunk_diameter }} / 5);
        L.circleMarker([{{ $tree->latitude }}, {{ $tree->longitude }}], {
            radius,
            fillColor: '{{ $tree->species->color_code }}',
            color: '#fff',
            weight: 2,
            opacity: 0.9,
            fillOpacity: 0.85
        }).addTo(map).bindPopup('<strong>{{ $tree->species->name }}</strong>').openPopup();
    </script>
</body>
</html>

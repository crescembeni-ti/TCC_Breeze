<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalhes da Árvore - {{ $tree->species->name ?? 'Detalhes' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/trees.css']) {{-- Onde devem estar os estilos 'tree-page', etc. --}}

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>

<body class="font-sans antialiased tree-page">
    <div class="min-h-screen">

        <header class="site-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">

                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-4">
                        <img src="{{ asset('images/Brasao_Verde.png') }}"
                             class="h-16 w-16 sm:h-20 sm:w-20 object-contain">

                        <img src="{{ asset('images/logo.png') }}"
                             class="h-16 w-16 sm:h-20 sm:w-20 object-contain">

                        <h1 class="text-3xl sm:text-4xl font-bold">
                            <span class="text-[#358054]">Árvores de</span>
                            <span class="text-[#a0c520]">Paracambi</span>
                        </h1>
                    </a>
                </div>

                <a href="{{ route('home') }}"
                class="btn bg-green-600 hover:bg-[#38c224] transition-all duration-300 transform hover:scale-105">
                Voltar ao Mapa
                </a>

            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- COLUNA 1: INFORMAÇÕES DA ÁRVORE --}}
                <div class="bg-white p-6 tree-card rounded-2xl shadow-lg flex flex-col justify-start">
                    <h2 class="text-2xl font-extrabold text-[#358054] mb-4 pb-2">
                        Informações da Árvore
                    </h2>

                    {{-- CONTAINER DOS CARDS COM ESPAÇAMENTO AJUSTADO --}}
                    <div class="space-y-6"> {{-- 👈 AJUSTE 1: Espaçamento aumentado para space-y-6 --}}

                        {{-- ESPÉCIE --}}
                        <div class="bg-[#f0fdf4] p-4 rounded-xl shadow-sm border border-[#bbf7d0]">
                            <h3 class="text-sm font-bold text-[#166534] uppercase tracking-wide mb-1">Espécie</h3>
                            <p class="text-lg text-gray-900">{{ $tree->species->name ?? 'N/A' }}</p>
                            @if ($tree->species->scientific_name)
                                <p class="text-sm italic text-gray-600">{{ $tree->species->scientific_name }}</p>
                            @endif
                        </div>

                        {{-- DESCRIÇÃO / OBSERVAÇÕES --}}
                        {{-- Prioriza a descrição da árvore ($tree->description). 👈 AJUSTE 3: Prioridade confirmada --}}
                        @if ($tree->description || ($tree->species && $tree->species->description))
                            <div class="bg-[#f0fdf4] p-5 rounded-xl shadow-sm border border-[#bbf7d0] min-h-[10rem] flex flex-col">
                                <h3 class="text-sm font-bold text-[#166534] uppercase tracking-wide mb-3 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    Descrição / Observações
                                </h3>

                                <div class="text-gray-800 leading-relaxed text-base whitespace-pre-line flex-grow">
                                    {{ $tree->description ?? ($tree->species->description ?? 'Sem descrição detalhada.') }}
                                </div>
                            </div>
                        @endif

                        {{-- ENDEREÇO --}}
                        <div class="bg-[#f0fdf4] p-4 rounded-xl shadow-sm border border-[#bbf7d0]">
                            <h3 class="text-sm font-bold text-[#166534] uppercase tracking-wide mb-1">Endereço</h3>
                            <p class="text-gray-900">{{ $tree->address ?? 'Endereço não informado' }}</p>
                        </div>


                        {{-- DATA DE PLANTIO --}}
                        @if ($tree->planted_at)
                            <div class="bg-[#f0fdf4] p-4 rounded-xl shadow-sm border border-[#bbf7d0]">
                                <h3 class="text-sm font-bold text-[#166534] uppercase tracking-wide mb-1">Data de Plantio</h3>
                                <p class="text-gray-900">{{ $tree->planted_at->format('d/m/Y') }}</p>
                            </div>
                        @endif

                        {{-- DETALHES (TRONCO) --}}
                        <div class="grid grid-cols-1 gap-4"> {{-- Mudei para grid-cols-1 pois o outro item foi removido --}}
                            <div class="bg-[#f0fdf4] p-4 rounded-xl shadow-sm border border-[#bbf7d0] text-center">
                                <p class="text-xs uppercase text-[#358054] font-bold tracking-wide">Diâmetro do Tronco</p>
                                <p class="text-lg text-gray-900">{{ $tree->trunk_diameter ?? 0 }} cm</p>
                            </div>

                            {{-- AJUSTE 2: REMOVIDO: Bloco "Status de Saúde" foi completamente removido daqui. --}}
                        </div>
                    </div>
                </div>

                {{-- COLUNA 2: MAPA --}}
                <div class="bg-white p-6 pb-0 tree-card rounded-2xl shadow-lg">
                    <h2 class="text-2xl font-bold text-[#358054] mb-4">Localização</h2>
                    <div id="tree-map" class="rounded-lg w-full h-[500px] lg:h-full min-h-[500px]"></div>
                </div>

            </div>

        </main>

        <footer class="bg-gray-800 shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
            </div>
        </footer>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('tree-map').setView([{{ $tree->latitude }}, {{ $tree->longitude }}], 16);

        const satellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles © Esri'
            }).addTo(map);

        const labels = L.tileLayer(
            'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

        const radius = Math.max(8, {{ $tree->trunk_diameter ?? 4 }} / 5);

        L.circleMarker([{{ $tree->latitude }}, {{ $tree->longitude }}], {
            radius,
            fillColor: '{{ $tree->species->color_code ?? '#358054' }}',
            color: '#fff',
            weight: 2,
            opacity: 0.9,
            fillOpacity: 0.85
        }).addTo(map).bindPopup('<strong>{{ $tree->species->name ?? 'Árvore' }}</strong>').openPopup();
    </script>
</body>

</html>
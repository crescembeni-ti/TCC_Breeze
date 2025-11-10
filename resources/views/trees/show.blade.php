@extends('layouts.admin')

@section('content')

<h2 class="text-3xl font-bold text-[#358054] mb-6">
    Detalhes da Árvore — {{ $tree->species->name }}
</h2>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

    {{-- INFORMAÇÕES DA ÁRVORE --}}
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-2xl font-bold text-[#358054] mb-4">🌳 Informações da Árvore</h3>

        @if($tree->photo)
        <div class="mb-6">
            <img src="{{ $tree->photo }}" 
                 alt="Foto de {{ $tree->species->name }}" 
                 class="w-full h-64 object-cover rounded-lg shadow-md"
                 onerror="this.style.display='none'">
        </div>
        @endif

        <div class="space-y-4">

            <div>
                <h4 class="text-sm font-semibold text-gray-600">Espécie</h4>
                <p class="text-lg text-gray-900">{{ $tree->species->name }}</p>
                @if($tree->species->scientific_name)
                    <p class="text-sm italic text-gray-600">{{ $tree->species->scientific_name }}</p>
                @endif
            </div>

            @if($tree->species->description)
            <div>
                <h4 class="text-sm font-semibold text-gray-600">Descrição</h4>
                <p class="text-gray-900">{{ $tree->species->description }}</p>
            </div>
            @endif

            <div>
                <h4 class="text-sm font-semibold text-gray-600">Endereço</h4>
                <p class="text-gray-900">{{ $tree->address }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-600">Diâmetro do Tronco</h4>
                    <p class="text-lg text-gray-900">{{ $tree->trunk_diameter }} cm</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-600">Status de Saúde</h4>
                    <p class="text-lg text-gray-900 capitalize">{{ $tree->health_status }}</p>
                </div>
            </div>

            @if($tree->planted_at)
            <div>
                <h4 class="text-sm font-semibold text-gray-600">Data de Plantio</h4>
                <p class="text-gray-900">{{ $tree->planted_at->format('d/m/Y') }}</p>
            </div>
            @endif

            @if($tree->user)
            <div>
                <h4 class="text-sm font-semibold text-gray-600">Registrado por</h4>
                <p class="text-gray-900">{{ $tree->user->name }}</p>
            </div>
            @endif

        </div>
    </div>

    {{-- MAPA --}}
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-2xl font-bold text-[#358054] mb-4">📍 Localização</h3>
        <div id="tree-map" class="rounded-lg" style="height: 380px;"></div>
    </div>

</div>

{{-- HISTÓRICO --}}
<div class="bg-white p-6 rounded-lg shadow-sm mt-10">
    <h3 class="text-2xl font-bold text-[#358054] mb-4">🪵 Histórico de Atividades</h3>

    @if($tree->activities->count() > 0)
        <div class="space-y-4">
            @foreach($tree->activities as $activity)
                <div class="border-l-4 border-green-500 pl-4 py-2">
                    <p class="text-sm text-gray-600">
                        {{ $activity->activity_date->format('d/m/Y H:i') }}
                    </p>
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

@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('tree-map').setView([{{ $tree->latitude }}, {{ $tree->longitude }}], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
    }).addTo(map);

    const radius = Math.max(8, {{ $tree->trunk_diameter }} / 5);

    L.circleMarker([{{ $tree->latitude }}, {{ $tree->longitude }}], {
        radius,
        fillColor: '{{ $tree->species->color_code }}',
        color: '#fff',
        weight: 2,
        opacity: 0.9,
        fillOpacity: 0.85
    }).addTo(map)
     .bindPopup('<strong>{{ $tree->species->name }}</strong>');
</script>
@endpush

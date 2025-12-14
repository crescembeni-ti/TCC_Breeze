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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nome <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" required maxlength="255"
                            value="{{ old('name') }}"
                            class="block w-full rounded-md border border-gray-300 bg-gray-50 text-[#358054] shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" />
                    </div>

                    {{-- Endereço --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Endereço <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="address" name="address" required maxlength="255"
                            value="{{ old('address') }}"
                            class="block w-full rounded-md border border-gray-300 bg-gray-50 text-[#358054] shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" />
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher automaticamente</p>
                    </div>

                    {{-- Bairro --}}
                    <div x-data="{ open: false, selected: '{{ old('bairro_id') ?? '' }}', selectedName: '{{ old('bairro_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bairro <span class="text-red-500">*</span>
                        </label>

                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione um bairro'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <ul x-show="open" @click.outside="open = false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            @foreach ($bairros as $bairro)
                                <li @click="selected='{{ $bairro->id }}'; selectedName='{{ $bairro->nome }}'; open=false"
                                    class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                    :class="selected == '{{ $bairro->id }}' ? 'bg-[#358054] text-white' : ''">
                                    {{ $bairro->nome }}
                                </li>
                            @endforeach
                        </ul>

                        <input type="hidden" name="bairro_id" :value="selected" required>

                        <p class="text-xs text-gray-500 mt-1">Será preenchido automaticamente ao clicar no mapa, mas você
                            pode alterar.</p>
                    </div>


                    {{-- Espécie --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Espécie <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="species_name" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>


                    {{-- Nome vulgar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nome vulgar <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="vulgar_name" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Nome científico --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nome científico <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="scientific_name" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>


                    {{-- Caso não tenha espécie --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Caso não tenha espécie
                        </label>
                        <input type="text" name="no_species_case"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 2: COORDENADAS --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Coordenadas</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Latitude --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Latitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.0000001" id="latitude" name="latitude" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                    </div>

                    {{-- Longitude --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Longitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.0000001" id="longitude" name="longitude" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 3: DADOS GERAIS --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Dados Gerais</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Estado de saúde --}}
                    <div x-data="{ open: false, selected: '{{ old('health_status') ?? '' }}', selectedName: '{{ old('health_status_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Estado de Saúde <span class="text-red-500">*</span>
                        </label>

                        <button @click="open = !open" type="button"
                            class="w-full border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between px-3 py-2 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione um estado'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <ul x-show="open" @click.outside="open = false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='good'; selectedName='Boa'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white"
                                :class="selected === 'good' ? 'bg-[#358054] text-white' : ''">Boa</li>
                            <li @click="selected='fair'; selectedName='Regular'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white"
                                :class="selected === 'fair' ? 'bg-[#358054] text-white' : ''">Regular</li>
                            <li @click="selected='poor'; selectedName='Ruim'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white"
                                :class="selected === 'poor' ? 'bg-[#358054] text-white' : ''">Ruim</li>
                        </ul>

                        <input type="hidden" name="health_status" :value="selected" required>
                    </div>

                    {{-- Data de Plantio --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Plantio <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="planted_at" max="{{ now()->format('Y-m-d') }}" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Diâmetro do Tronco --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Diâmetro do Tronco (cm) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="trunk_diameter" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                </div>
            </div>

            {{-- SEÇÃO 4: DIMENSÕES --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Dimensões</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- CAP --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            CAP (cm) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="cap" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Altura --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Altura (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="height" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Altura da copa --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Altura da copa (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="crown_height" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Diâmetro longitudinal --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Diâmetro longitudinal (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="crown_diameter_longitudinal" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Diâmetro perpendicular --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Diâmetro perpendicular (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="crown_diameter_perpendicular" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 5: CARACTERÍSTICAS --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Características</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Tipo de Bifurcação --}}
                    <div x-data="{ open: false, selected: '{{ old('bifurcation_type') ?? '' }}', selectedName: '{{ old('bifurcation_type_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de Bifurcação <span class="text-red-500">*</span>
                        </label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='ausente'; selectedName='Ausente'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li>
                            <li @click="selected='U'; selectedName='U'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'U' ? 'bg-[#358054] text-white' : ''">U</li>
                            <li @click="selected='V'; selectedName='V'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'V' ? 'bg-[#358054] text-white' : ''">V</li>
                        </ul>
                        <input type="hidden" name="bifurcation_type" :value="selected" required>
                    </div>

                    {{-- Equilíbrio Fuste --}}
                    <div x-data="{ open: false, selected: '{{ old('stem_balance') ?? '' }}', selectedName: '{{ old('stem_balance_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Equilíbrio Fuste <span class="text-red-500">*</span>
                        </label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='ausente'; selectedName='Ausente'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li>
                            <li @click="selected='maior_45'; selectedName='Maior que 45°'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'maior_45' ? 'bg-[#358054] text-white' : ''">Maior que 45°</li>
                            <li @click="selected='menor_45'; selectedName='Menor que 45°'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'menor_45' ? 'bg-[#358054] text-white' : ''">Menor que 45°</li>
                        </ul>
                        <input type="hidden" name="stem_balance" :value="selected" required>
                    </div>

                    {{-- Equilíbrio da Copa --}}
                    <div x-data="{ open: false, selected: '{{ old('crown_balance') ?? '' }}', selectedName: '{{ old('crown_balance_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Equilíbrio da copa <span class="text-red-500">*</span>
                        </label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='equilibrada'; selectedName='Equilibrada'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'equilibrada' ? 'bg-[#358054] text-white' : ''">Equilibrada</li>
                            <li @click="selected='medianamente_desequilibrada'; selectedName='Medianamente Desequilibrada'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'medianamente_desequilibrada' ? 'bg-[#358054] text-white' : ''">
                                Medianamente Desequilibrada</li>
                            <li @click="selected='desequilibrada'; selectedName='Desequilibrada'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'desequilibrada' ? 'bg-[#358054] text-white' : ''">Desequilibrada
                            </li>
                            <li @click="selected='muito_desequilibrada'; selectedName='Muito Desequilibrada'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'muito_desequilibrada' ? 'bg-[#358054] text-white' : ''">Muito
                                Desequilibrada</li>
                        </ul>
                        <input type="hidden" name="crown_balance" :value="selected" required>
                    </div>


                </div>
            </div>

            {{-- SEÇÃO 6: AMBIENTE --}}
            <div>
                <h4 class="text-lg font-semibold text-[#358054] mb-3">Ambiente</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Organismos --}}
                    <div x-data="{ open: false, selected: '{{ old('organisms') ?? '' }}', selectedName: '{{ old('organisms_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Organismos <span class="text-red-500">*</span>
                        </label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='ausente'; selectedName='Ausente'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li>
                            <li @click="selected='infestacao_inicial'; selectedName='Infestação Inicial'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'infestacao_inicial' ? 'bg-[#358054] text-white' : ''">Infestação
                                Inicial</li>
                        </ul>
                        <input type="hidden" name="organisms" :value="selected" required>
                    </div>

                    {{-- Alvo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alvo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="target" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Injúrias mecânicas --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Injúrias mecânicas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="injuries" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Estado da fiação --}}
                    <div x-data="{ open: false, selected: '{{ old('wiring_status') ?? '' }}', selectedName: '{{ old('wiring_status_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Estado da fiação <span class="text-red-500">*</span>
                        </label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='pode_interferir'; selectedName='Pode interferir'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'pode_interferir' ? 'bg-[#358054] text-white' : ''">Pode interferir
                            </li>
                            <li @click="selected='interfere'; selectedName='Interfere'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'interfere' ? 'bg-[#358054] text-white' : ''">Interfere</li>
                            <li @click="selected='nao_interfere'; selectedName='Não interfere'; open=false"
                                class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                :class="selected === 'nao_interfere' ? 'bg-[#358054] text-white' : ''">Não interfere</li>
                        </ul>
                        <input type="hidden" name="wiring_status" :value="selected" required>
                    </div>

                    {{-- Largura total --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Largura total (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="total_width" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Largura da rua --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Largura da rua (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="street_width" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Altura da gola --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Altura da gola (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="gutter_height" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Largura da gola --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Largura da gola (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="gutter_width" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Comprimento da gola --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Comprimento da gola (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="gutter_length" required
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- BOTÃO --}}
            <button type="submit"
                class="bg-green-600 text-white  text-lg rounded-md shadow-md hover:bg-green-700 hover:shadow-lg active:bg-[#38c224] active:scale-95 transition px-6 py-3">
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
        document.addEventListener("DOMContentLoaded", async function() {

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
                const x = lng,
                    y = lat;

                for (let ring of polygon.coordinates) {
                    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                        const xi = ring[i][0],
                            yi = ring[i][1];
                        const xj = ring[j][0],
                            yj = ring[j][1];

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
                    const url =
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
                    const response = await fetch(url, {
                        headers: {
                            "User-Agent": "Arvores-Paracambi-System"
                        }
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
                    return {
                        rua: "",
                        sugeridoBairro: null
                    };
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

@extends('layouts.dashboard')

@section('content')
    <div class="perfil-box inline-block">
        <h2 class="text-3xl font-bold text-[#358054] mb-0">
            Painel de Administração – Mapa de Árvores
        </h2>
    </div>

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

        @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            <p class="font-bold">Ops! Corrija os erros abaixo:</p>
            <ul class="list-disc list-inside text-sm mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.map.store') }}" class="space-y-10">
            @csrf

            {{-- SEÇÃO 1: IDENTIFICAÇÃO --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Identificação</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Endereço --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Endereço</label>
                        <input type="text" id="address" name="address" maxlength="255" value="{{ old('address') }}"
                            class="block w-full rounded-md border border-gray-300 bg-gray-50 text-gray-800 shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" />
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher automaticamente</p>
                    </div>

                    {{-- Bairro --}}
                    <div x-data="{ open: false, selected: '{{ old('bairro_id') ?? '' }}', selectedName: '{{ old('bairro_name') ?? '' }}' }" 
                         @set-bairro-map.window="selected = $event.detail.id; selectedName = $event.detail.nome"
                         class="relative w-full">
                        
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>

                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione um bairro'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                        <input type="hidden" name="bairro_id" :value="selected">
                        <p class="text-xs text-gray-500 mt-1">Será preenchido automaticamente ao clicar no mapa.</p>
                    </div>

                    {{-- NOME CIENTÍFICO --}}
                    <div x-data="{
                            query: '{{ old('scientific_name') }}',
                            open: false,
                            list: [],
                            filtered: [],
                            initList() {
                                this.list = {{ json_encode($scientificNames) }};
                            },
                            filter() {
                                if (this.query === '') {
                                    this.filtered = [];
                                } else {
                                    this.filtered = this.list.filter(item => 
                                        item.toLowerCase().includes(this.query.toLowerCase())
                                    );
                                }
                                this.open = true;
                            },
                            select(name) {
                                this.query = name;
                                this.open = false;
                            }
                        }"
                        x-init="initList()"
                        class="relative">
                        
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Científico</label>
                        
                        <div class="relative">
                            <input type="text" 
                                   name="scientific_name" 
                                   x-model="query"
                                   @input="filter()"
                                   @click="open = true; filter()"
                                   @click.outside="open = false"
                                   autocomplete="off"
                                   class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Selecione ou digite um novo...">
                            
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        {{-- Lista Suspensa --}}
                        <ul x-show="open && filtered.length > 0" 
                            x-transition
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                            style="display: none;">
                            
                            <template x-for="name in filtered" :key="name">
                                <li @click="select(name)"
                                    class="cursor-pointer select-none py-2 px-3 hover:bg-[#358054] hover:text-white text-gray-700 text-sm">
                                    <span x-text="name"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Nome Vulgar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Vulgar</label>
                        <input type="text" name="vulgar_name" value="{{ old('vulgar_name') }}"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Caso não tenha espécie --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Caso não tenha espécie</label>
                        <div class="flex flex-col justify-start">
                            <input type="text" name="no_species_case" value="{{ old('no_species_case') }}"
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500"
                                placeholder="Informe se não identificada">
                            <p class="text-xs text-gray-500 mt-1">Utilize este campo apenas se a espécie não for encontrada ou definida acima.</p>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Árvore</label>
                        <textarea name="description" rows="4"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500 placeholder-gray-400"
                            placeholder="Detalhes sobre a saúde, poda, entorno ou observações...">{{ old('description') }}</textarea>
                    </div>

                </div>
            </div>

            {{-- SEÇÃO 2: COORDENADAS --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Localização</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                        <input type="number" step="0.0000001" id="latitude" name="latitude" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="number" step="0.0000001" id="longitude" name="longitude" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 3: DADOS GERAIS --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Status da Árvore</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div x-data="{ open: false, selected: '{{ old('health_status') ?? '' }}', selectedName: '{{ old('health_status_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Saúde</label>
                        <button @click="open = !open" type="button"
                            class="w-full border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between px-3 py-2 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione um estado'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open = false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='good'; selectedName='Boa'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === 'good' ? 'bg-[#358054] text-white' : ''">Boa</li>
                            <li @click="selected='fair'; selectedName='Regular'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === 'fair' ? 'bg-[#358054] text-white' : ''">Regular</li>
                            <li @click="selected='poor'; selectedName='Ruim'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === 'poor' ? 'bg-[#358054] text-white' : ''">Ruim</li>
                        </ul>
                        <input type="hidden" name="health_status" :value="selected">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Plantio</label>
                        <input type="date" name="planted_at" max="{{ now()->format('Y-m-d') }}" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diâmetro do Tronco (cm)</label>
                        <input type="number" step="0.01" name="trunk_diameter" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 4: DIMENSÕES --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Dimensões da Árvore</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CAP (cm)</label>
                        <input type="number" step="0.01" name="cap" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Altura (m)</label>
                        <input type="number" step="0.01" name="height" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Altura da copa (m)</label>
                        <input type="number" step="0.01" name="crown_height"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Copa Longitudinal (m)</label>
                        <input type="number" step="0.01" name="crown_diameter_longitudinal" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Copa Perpendicular (m)</label>
                        <input type="number" step="0.01" name="crown_diameter_perpendicular" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 5: CARACTERÍSTICAS --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Condições Biológicas</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div x-data="{ open: false, selected: '{{ old('bifurcation_type') ?? '' }}', selectedName: '{{ old('bifurcation_type_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Bifurcação</label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li>
                            <li @click="selected='U'; selectedName='U'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'U' ? 'bg-[#358054] text-white' : ''">U</li>
                            <li @click="selected='V'; selectedName='V'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'V' ? 'bg-[#358054] text-white' : ''">V</li>
                        </ul>
                        <input type="hidden" name="bifurcation_type" :value="selected">
                    </div>

                    <div x-data="{ open: false, selected: '{{ old('stem_balance') ?? '' }}', selectedName: '{{ old('stem_balance_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio Fuste (Inclinação)</label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li>
                            <li @click="selected='maior_45'; selectedName='Maior que 45°'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'maior_45' ? 'bg-[#358054] text-white' : ''">Maior que 45°</li>
                            <li @click="selected='menor_45'; selectedName='Menor que 45°'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'menor_45' ? 'bg-[#358054] text-white' : ''">Menor que 45°</li>
                        </ul>
                        <input type="hidden" name="stem_balance" :value="selected">
                    </div>

                    <div x-data="{ open: false, selected: '{{ old('crown_balance') ?? '' }}', selectedName: '{{ old('crown_balance_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio da copa</label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='equilibrada'; selectedName='Equilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'equilibrada' ? 'bg-[#358054] text-white' : ''">Equilibrada</li>
                            <li @click="selected='medianamente_desequilibrada'; selectedName='Medianamente Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'medianamente_desequilibrada' ? 'bg-[#358054] text-white' : ''">Medianamente Desequilibrada</li>
                            <li @click="selected='desequilibrada'; selectedName='Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'desequilibrada' ? 'bg-[#358054] text-white' : ''">Desequilibrada</li>
                            <li @click="selected='muito_desequilibrada'; selectedName='Muito Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'muito_desequilibrada' ? 'bg-[#358054] text-white' : ''">Muito Desequilibrada</li>
                        </ul>
                        <input type="hidden" name="crown_balance" :value="selected">
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 6: AMBIENTE --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Ambiente e Entorno</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div x-data="{ open: false, selected: '{{ old('organisms') ?? '' }}', selectedName: '{{ old('organisms_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organismos</label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li>
                            <li @click="selected='infestacao_inicial'; selectedName='Infestação Inicial'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'infestacao_inicial' ? 'bg-[#358054] text-white' : ''">Infestação Inicial</li>
                        </ul>
                        <input type="hidden" name="organisms" :value="selected">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alvo</label>
                        <input type="text" name="target" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Injúrias mecânicas e cavidades</label>
                        <input type="text" name="injuries" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div x-data="{ open: false, selected: '{{ old('wiring_status') ?? '' }}', selectedName: '{{ old('wiring_status_name') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado da fiação</label>
                        <button @click="open = !open" type="button"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName || 'Selecione...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='pode_interferir'; selectedName='Pode interferir'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'pode_interferir' ? 'bg-[#358054] text-white' : ''">Pode interferir</li>
                            <li @click="selected='interfere'; selectedName='Interfere'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'interfere' ? 'bg-[#358054] text-white' : ''">Interfere</li>
                            <li @click="selected='nao_interfere'; selectedName='Não interfere'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'nao_interfere' ? 'bg-[#358054] text-white' : ''">Não interfere</li>
                        </ul>
                        <input type="hidden" name="wiring_status" :value="selected">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura total (m)</label>
                        <input type="number" step="0.01" name="total_width" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura da rua (m)</label>
                        <input type="number" step="0.01" name="street_width" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Altura da gola (m)</label>
                        <input type="number" step="0.01" name="gutter_height"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura da gola (m)</label>
                        <input type="number" step="0.01" name="gutter_width" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comprimento da gola (m)</label>
                        <input type="number" step="0.01" name="gutter_length" 
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <button type="submit"
                class="bg-green-600 text-white text-lg rounded-md shadow-md hover:bg-green-700 hover:shadow-lg active:bg-[#38c224] active:scale-95 transition px-6 py-3 mt-4">
                Adicionar Árvore
            </button>

        </form>
    </div>

    {{-- MAPA --}}
    <div class="bg-white border border-gray-200 shadow rounded-xl p-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Mapa de Árvores</h3>
                <p class="text-sm text-gray-600">Visualize, filtre e exporte os dados.</p>
            </div>
        </div>

        <div class="relative w-full h-[600px] rounded-xl overflow-hidden border border-gray-300">
            
            {{-- PAINEL FLUTUANTE DE FILTROS E EXPORTAÇÃO --}}
            <div class="absolute top-4 right-4 z-[999] bg-white p-4 rounded-lg shadow-xl w-72 border border-gray-200 bg-opacity-95">
                <h4 class="font-bold text-gray-800 mb-3 text-sm flex items-center gap-2 border-b pb-2">
                    <svg class="w-4 h-4 text-[#358054]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filtros & Relatórios
                </h4>

                {{-- Filtro de Espécie --}}
                <div class="mb-3">
                    <label class="text-xs font-semibold text-gray-500 uppercase">Espécie</label>
                    <select id="filter-species" onchange="updateMapFilters()" class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 py-1">
                        <option value="">Todas as Espécies</option>
                        @foreach($scientificNames as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro de Bairro --}}
                <div class="mb-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase">Bairro</label>
                    <select id="filter-bairro" onchange="updateMapFilters()" class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 py-1">
                        <option value="">Todos os Bairros</option>
                        @foreach($bairros as $bairro)
                            <option value="{{ $bairro->id }}">{{ $bairro->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-3 border-gray-100">

                {{-- Botão de Exportar --}}
                <a id="btn-export" href="{{ route('admin.trees.export') }}" target="_blank" 
                   class="flex items-center justify-center w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs transition shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Baixar Planilha (CSV)
                </a>
                <p id="counter-display" class="text-[10px] text-center text-gray-400 mt-2">Carregando dados...</p>
            </div>

            {{-- O MAPA --}}
            <div id="map" class="w-full h-full z-0"></div>

        </div>
    </div>
@endsection


@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", async function() {

            // 1. Configuração do Mapa
            const map = L.map('map').setView([-22.6091, -43.7089], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let tempMarker = null; // Marcador Vermelho (Novo cadastro)
            let treesLayer = L.layerGroup().addTo(map); // Camada de Árvores (Verdes)

            // Elementos do DOM
            const latInput = document.getElementById("latitude");
            const lngInput = document.getElementById("longitude");
            const addressInput = document.getElementById("address");
            const exportBtn = document.getElementById('btn-export');
            const counterDisplay = document.getElementById('counter-display');

            // 2. Carregar Bairros
            let bairrosPoligonos = [];
            try {
                const geojsonResponse = await fetch("/bairros.json");
                const geojsonData = await geojsonResponse.json();
                bairrosPoligonos = geojsonData.features;
            } catch (err) {
                console.warn("Erro ao carregar bairros.json:", err);
            }

            // 3. Função Point in Polygon
            function pointInPolygon(lat, lng, polygon) {
                let inside = false;
                const x = lng, y = lat;
                for (let ring of polygon.coordinates) {
                    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                        const xi = ring[i][0], yi = ring[i][1];
                        const xj = ring[j][0], yj = ring[j][1];
                        const intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                        if (intersect) inside = !inside;
                    }
                }
                return inside;
            }

            // 4. Detectar Bairro
            function detectarBairro(lat, lng) {
                for (let f of bairrosPoligonos) {
                    if (f.geometry && f.geometry.type === "Polygon") {
                        if (pointInPolygon(lat, lng, f.geometry)) {
                            return { id: f.properties.id_bairro, nome: f.properties.nome };
                        }
                    }
                }
                return null;
            }

            // 5. Reverse Geocoding
            async function buscarEndereco(lat, lng) {
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
                    const response = await fetch(url, { headers: { "User-Agent": "Arvores-Paracambi-System" } });
                    const data = await response.json();
                    return { rua: data.address?.road || "", sugeridoBairro: data.address?.suburb || null };
                } catch (e) {
                    return { rua: "", sugeridoBairro: null };
                }
            }

            // 6. Lógica de Filtragem e Carregamento de Árvores
            window.updateMapFilters = async function() {
                const speciesName = document.getElementById('filter-species').value;
                const bairroId = document.getElementById('filter-bairro').value;

                // Atualiza Link de Exportação
                const params = new URLSearchParams();
                if(speciesName) params.append('scientific_name', speciesName);
                if(bairroId) params.append('bairro_id', bairroId);
                
                // Define o href do botão para baixar o CSV filtrado
                exportBtn.href = "{{ route('admin.trees.export') }}?" + params.toString();

                counterDisplay.innerText = "Carregando...";

                try {
                    // Busca dados filtrados
                    const response = await fetch("{{ route('trees.data') }}?" + params.toString());
                    const trees = await response.json();

                    // Limpa marcadores antigos
                    treesLayer.clearLayers();

                    // Adiciona novos marcadores
                    trees.forEach(tree => {
                        const color = tree.color_code || '#358054';
                        
                        // Ícone circular simples
                        const markerHtml = `
                            <div style="
                                background-color: ${color};
                                width: 12px; height: 12px;
                                border-radius: 50%;
                                border: 1.5px solid white;
                                box-shadow: 0 1px 3px rgba(0,0,0,0.3);
                            "></div>
                        `;

                        const customIcon = L.divIcon({
                            className: 'custom-tree-marker',
                            html: markerHtml,
                            iconSize: [12, 12],
                            iconAnchor: [6, 6]
                        });

                        const popupContent = `
                            <div class="text-xs font-sans">
                                <strong class="text-sm text-[#358054]">${tree.species_name}</strong><br>
                                <span class="text-gray-500">ID: ${tree.id}</span><br>
                                <div class="mt-1 text-gray-700">
                                    <b>Bairro:</b> ${tree.bairro_nome}<br>
                                    <b>Endereço:</b> ${tree.address}<br>
                                    <b>Saúde:</b> ${tree.health_status}
                                </div>
                                <a href="/pbi-admin/trees/${tree.id}/edit" target="_blank" 
                                   class="mt-2 inline-block bg-[#358054] text-white px-2 py-1 rounded hover:bg-green-700">
                                   Editar
                                </a>
                            </div>
                        `;

                        L.marker([tree.latitude, tree.longitude], { icon: customIcon })
                         .bindPopup(popupContent)
                         .addTo(treesLayer);
                    });

                    counterDisplay.innerText = `${trees.length} árvores no mapa.`;

                } catch (error) {
                    console.error('Erro ao carregar árvores:', error);
                    counterDisplay.innerText = "Erro na busca.";
                }
            };

            // 7. Evento de Clique no Mapa (Cadastro de Nova Árvore)
            map.on("click", async e => {
                const lat = e.latlng.lat.toFixed(7);
                const lng = e.latlng.lng.toFixed(7);

                latInput.value = lat;
                lngInput.value = lng;

                if (tempMarker) map.removeLayer(tempMarker);
                
                // Marcador Vermelho (Novo)
                tempMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'new-tree-marker',
                        html: '<div style="background-color:red; width:14px; height:14px; border-radius:50%; border:2px solid white; box-shadow:0 0 5px rgba(0,0,0,0.5);"></div>',
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    })
                }).addTo(map).bindPopup("<b>Novo Local Selecionado</b><br>Preenchendo formulário...").openPopup();

                // Busca Endereço
                const info = await buscarEndereco(lat, lng);
                if(addressInput) addressInput.value = info.rua || "";

                // Detecta Bairro
                const bairroData = detectarBairro(parseFloat(lat), parseFloat(lng));

                if (bairroData) {
                    window.dispatchEvent(new CustomEvent('set-bairro-map', { 
                        detail: { id: bairroData.id, nome: bairroData.nome } 
                    }));
                }
            });

            // Carrega as árvores ao iniciar
            updateMapFilters();
        });
    </script>
@endpush
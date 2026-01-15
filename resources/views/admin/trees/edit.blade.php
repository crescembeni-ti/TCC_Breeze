@extends('layouts.dashboard')

@section('content')
    <div class="perfil-box inline-block">
        <h2 class="text-3xl font-bold text-[#358054] mb-0">
            Painel de Administração – Editar Árvore
        </h2>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            <strong>Sucesso!</strong> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <strong>Erro!</strong> Verifique os campos abaixo.
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-delete" action="{{ route('admin.trees.destroy', $tree->id) }}" method="POST"
          onsubmit="return confirm('ATENÇÃO: Tem certeza que deseja excluir esta árvore permanentemente?');">
        @csrf
        @method('DELETE')
    </form>

    <div class="bg-white border border-gray-200 shadow rounded-xl mb-10 p-8">

        <h3 class="text-2xl font-bold mb-6 text-gray-800">
            Editando: <span class="text-[#358054]">
                {{ $tree->scientific_name ?: $tree->vulgar_name ?: 'Árvore sem nome' }}
            </span>
        </h3>

        <form id="form-edit" method="POST" action="{{ route('admin.trees.update', $tree->id) }}" class="space-y-10">
            @csrf
            @method('PATCH')

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
                        <input type="text" id="address" name="address" maxlength="255" 
                            value="{{ old('address', $tree->address) }}"
                            class="block w-full rounded-md border border-gray-300 bg-gray-50 text-gray-800 shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" />
                        <p class="text-xs text-gray-500 mt-1">Clique no mapa para atualizar</p>
                    </div>

                    {{-- Bairro --}}
                    <div x-data="{ 
                            open: false, 
                            selected: '{{ old('bairro_id', $tree->bairro_id) }}', 
                            selectedName: '{{ optional($tree->bairro)->nome ?? 'Selecione um bairro' }}' 
                        }" 
                        @set-bairro-map.window="selected = $event.detail.id; selectedName = $event.detail.nome"
                        class="relative w-full">
                        
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        
                        <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <ul x-show="open" @click.outside="open = false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-50">
                            @foreach ($bairros as $bairro)
                                <li @click="selected='{{ $bairro->id }}'; selectedName='{{ $bairro->nome }}'; open=false"
                                    class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm"
                                    :class="selected == '{{ $bairro->id }}' ? 'bg-[#358054] text-white' : ''">
                                    {{ $bairro->nome }}
                                </li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="bairro_id" :value="selected">
                    </div>

                    {{-- 
                        NOME CIENTÍFICO (CORRIGIDO)
                        Usamos x-init para carregar a lista de forma segura
                    --}}
                    <div x-data="{
                            query: '{{ old('scientific_name', $tree->scientific_name) }}',
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
                            
                            {{-- Ícone de seta --}}
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
                        <input type="text" name="vulgar_name" 
                            value="{{ old('vulgar_name', $tree->vulgar_name) }}"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- Caso não tenha espécie --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Caso não tenha espécie</label>
                        <div class="flex flex-col h-full justify-start">
                            <input type="text" name="no_species_case"
                                value="{{ old('no_species_case', $tree->no_species_case) }}"
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500"
                                placeholder="Informe se não identificada">
                            <p class="text-xs text-gray-500 mt-2">Utilize este campo apenas se a espécie não for encontrada.</p>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Árvore</label>
                        <textarea name="description" rows="5"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500 placeholder-gray-400"
                            placeholder="Detalhes sobre a saúde, poda, entorno ou observações...">{{ old('description', $tree->description) }}</textarea>
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
                        <input type="number" step="0.0000001" id="latitude" name="latitude" required value="{{ old('latitude', $tree->latitude) }}"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="number" step="0.0000001" id="longitude" name="longitude" required value="{{ old('longitude', $tree->longitude) }}"
                            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
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
                    <div x-data="{ open: false, selected: '{{ old('health_status', $tree->health_status) }}', selectedName: '{{ old('health_status', $tree->health_status) == 'good' ? 'Boa' : (old('health_status', $tree->health_status) == 'fair' ? 'Regular' : (old('health_status', $tree->health_status) == 'poor' ? 'Ruim' : 'Selecione...')) }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Saúde</label>
                        <button @click="open = !open" type="button" class="w-full border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between px-3 py-2 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <span x-text="selectedName"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <ul x-show="open" @click.outside="open = false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            <li @click="selected='good'; selectedName='Boa'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === 'good' ? 'bg-[#358054] text-white' : ''">Boa</li>
                            <li @click="selected='fair'; selectedName='Regular'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === 'fair' ? 'bg-[#358054] text-white' : ''">Regular</li>
                            <li @click="selected='poor'; selectedName='Ruim'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === 'poor' ? 'bg-[#358054] text-white' : ''">Ruim</li>
                        </ul>
                        <input type="hidden" name="health_status" :value="selected">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Plantio</label>
                        <input type="date" name="planted_at" max="{{ now()->format('Y-m-d') }}" value="{{ old('planted_at', optional($tree->planted_at)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diâmetro do Tronco (cm)</label>
                        <input type="number" step="0.01" name="trunk_diameter" value="{{ old('trunk_diameter', $tree->trunk_diameter) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 4: DIMENSÕES --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Dimensões</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach(['cap' => 'CAP (cm)', 'height' => 'Altura (m)', 'crown_height' => 'Altura da Copa (m)', 'crown_diameter_longitudinal' => 'Copa Longitudinal (m)', 'crown_diameter_perpendicular' => 'Copa Perpendicular (m)'] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                        <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $tree->$field) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    @endforeach
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
                    {{-- Bifurcação --}}
                    <div x-data="{ open: false, selected: '{{ old('bifurcation_type', $tree->bifurcation_type) }}', selectedName: '{{ old('bifurcation_type', $tree->bifurcation_type) == 'ausente' ? 'Ausente' : (old('bifurcation_type', $tree->bifurcation_type) == 'U' ? 'U' : (old('bifurcation_type', $tree->bifurcation_type) == 'V' ? 'V' : 'Selecione...')) }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Bifurcação</label>
                        <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                        <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10"><li @click="selected='ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li><li @click="selected='U'; selectedName='U'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'U' ? 'bg-[#358054] text-white' : ''">U</li><li @click="selected='V'; selectedName='V'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'V' ? 'bg-[#358054] text-white' : ''">V</li></ul>
                        <input type="hidden" name="bifurcation_type" :value="selected">
                    </div>
                    {{-- Fuste --}}
                    <div x-data="{ open: false, selected: '{{ old('stem_balance', $tree->stem_balance) }}', selectedName: '{{ old('stem_balance', $tree->stem_balance) == 'ausente' ? 'Ausente' : (old('stem_balance', $tree->stem_balance) == 'maior_45' ? 'Maior que 45°' : (old('stem_balance', $tree->stem_balance) == 'menor_45' ? 'Menor que 45°' : 'Selecione...')) }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio Fuste (Inclinação)</label>
                        <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                        <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10"><li @click="selected='ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li><li @click="selected='maior_45'; selectedName='Maior que 45°'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'maior_45' ? 'bg-[#358054] text-white' : ''">Maior que 45°</li><li @click="selected='menor_45'; selectedName='Menor que 45°'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'menor_45' ? 'bg-[#358054] text-white' : ''">Menor que 45°</li></ul>
                        <input type="hidden" name="stem_balance" :value="selected">
                    </div>
                    {{-- Copa --}}
                    <div x-data="{ open: false, selected: '{{ old('crown_balance', $tree->crown_balance) }}', selectedName: '{{ old('crown_balance', $tree->crown_balance) == 'equilibrada' ? 'Equilibrada' : (old('crown_balance', $tree->crown_balance) == 'medianamente_desequilibrada' ? 'Medianamente Desequilibrada' : (old('crown_balance', $tree->crown_balance) == 'desequilibrada' ? 'Desequilibrada' : (old('crown_balance', $tree->crown_balance) == 'muito_desequilibrada' ? 'Muito Desequilibrada' : 'Selecione...'))) }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio da copa</label>
                        <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                        <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10"><li @click="selected='equilibrada'; selectedName='Equilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'equilibrada' ? 'bg-[#358054] text-white' : ''">Equilibrada</li><li @click="selected='medianamente_desequilibrada'; selectedName='Medianamente Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'medianamente_desequilibrada' ? 'bg-[#358054] text-white' : ''">Medianamente Desequilibrada</li><li @click="selected='desequilibrada'; selectedName='Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'desequilibrada' ? 'bg-[#358054] text-white' : ''">Desequilibrada</li><li @click="selected='muito_desequilibrada'; selectedName='Muito Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'muito_desequilibrada' ? 'bg-[#358054] text-white' : ''">Muito Desequilibrada</li></ul>
                        <input type="hidden" name="crown_balance" :value="selected">
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 6: AMBIENTE --}}
            <div>
                <div class="flex items-center gap-2 mb-4 border-b pb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0a8.1 8.1 0 001-8c0-4.42-3.58-8-8-8a8.1 8.1 0 00-1 8m6 8a2 2 0 11-4 0M6 8a2 2 0 11-4 0" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14c-4 0-6-4-6-4m6 4c4 0 6-4 6-4" />
                    </svg>
                    <h4 class="text-xl font-bold text-gray-700">Ambiente e Entorno</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div x-data="{ open: false, selected: '{{ old('organisms', $tree->organisms) }}', selectedName: '{{ old('organisms', $tree->organisms) == 'ausente' ? 'Ausente' : (old('organisms', $tree->organisms) == 'infestacao_inicial' ? 'Infestação Inicial' : 'Selecione...') }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organismos</label>
                        <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                        <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10"><li @click="selected='ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'ausente' ? 'bg-[#358054] text-white' : ''">Ausente</li><li @click="selected='infestacao_inicial'; selectedName='Infestação Inicial'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'infestacao_inicial' ? 'bg-[#358054] text-white' : ''">Infestação Inicial</li></ul>
                        <input type="hidden" name="organisms" :value="selected">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alvo</label>
                        <input type="text" name="target" value="{{ old('target', $tree->target) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Injúrias mecânicas e cavidades</label>
                        <input type="text" name="injuries" value="{{ old('injuries', $tree->injuries) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div x-data="{ open: false, selected: '{{ old('wiring_status', $tree->wiring_status) }}', selectedName: '{{ old('wiring_status', $tree->wiring_status) == 'pode_interferir' ? 'Pode interferir' : (old('wiring_status', $tree->wiring_status) == 'interfere' ? 'Interfere' : (old('wiring_status', $tree->wiring_status) == 'nao_interfere' ? 'Não interfere' : 'Selecione...')) }}' }" class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado da fiação</label>
                        <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                        <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10"><li @click="selected='pode_interferir'; selectedName='Pode interferir'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'pode_interferir' ? 'bg-[#358054] text-white' : ''">Pode interferir</li><li @click="selected='interfere'; selectedName='Interfere'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'interfere' ? 'bg-[#358054] text-white' : ''">Interfere</li><li @click="selected='nao_interfere'; selectedName='Não interfere'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected === 'nao_interfere' ? 'bg-[#358054] text-white' : ''">Não interfere</li></ul>
                        <input type="hidden" name="wiring_status" :value="selected">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura total (m)</label>
                        <input type="number" step="0.01" name="total_width" value="{{ old('total_width', $tree->total_width) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura da rua (m)</label>
                        <input type="number" step="0.01" name="street_width" value="{{ old('street_width', $tree->street_width) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Altura da gola (m)</label>
                        <input type="number" step="0.01" name="gutter_height" value="{{ old('gutter_height', $tree->gutter_height) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largura da gola (m)</label>
                        <input type="number" step="0.01" name="gutter_width" value="{{ old('gutter_width', $tree->gutter_width) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comprimento da gola (m)</label>
                        <input type="number" step="0.01" name="gutter_length" value="{{ old('gutter_length', $tree->gutter_length) }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- RODAPÉ COM BOTÕES --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-10 border-t border-gray-100 mt-8">
                
                <button type="submit" form="form-delete"
                    class="w-full sm:w-auto px-6 py-2.5 bg-red-100 text-red-700 border border-red-200 rounded-lg font-semibold hover:bg-red-200 transition flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Excluir Árvore
                </button>

                <div class="flex gap-4 w-full sm:w-auto">
                    <a href="{{ route('admin.trees.index') }}" 
                       class="flex-1 sm:flex-none text-center px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Cancelar
                    </a>

                    <button type="submit" form="form-edit"
                        class="flex-1 sm:flex-none px-8 py-2.5 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 shadow-md transition transform active:scale-95">
                        Salvar Alterações
                    </button>
                </div>
            </div>

        </form>
    </div>

    {{-- MAPA --}}
    <div class="bg-white border border-gray-200 shadow rounded-xl p-8">
        <h3 class="text-2xl font-bold mb-4 text-gray-800">Localização no Mapa</h3>
        <p class="text-sm text-gray-600 mb-4">Arraste o marcador ou clique em outro local para alterar a posição.</p>
        <div id="map" class="rounded-xl overflow-hidden" style="height: 500px;"></div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const initialLat = {{ $tree->latitude ?? -22.6091 }};
            const initialLng = {{ $tree->longitude ?? -43.7089 }};
            const map = L.map('map').setView([initialLat, initialLng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
            let tempMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map).bindPopup("Localização Atual").openPopup();
            
            const latInput = document.getElementById("latitude");
            const lngInput = document.getElementById("longitude");
            
            async function buscarEndereco(lat, lng) {
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
                    const response = await fetch(url, { headers: { "User-Agent": "Arvores-Paracambi-System" } });
                    const data = await response.json();
                    return { rua: data.address?.road || "" };
                } catch (e) { return { rua: "" }; }
            }
            
            map.on("click", async e => {
                const lat = e.latlng.lat.toFixed(7);
                const lng = e.latlng.lng.toFixed(7);
                latInput.value = lat;
                lngInput.value = lng;
                tempMarker.setLatLng(e.latlng).bindPopup("Nova Localização").openPopup();
                const addressInput = document.getElementById("address");
                const info = await buscarEndereco(lat, lng);
                if(addressInput && info.rua) addressInput.value = info.rua;
            });
            
            tempMarker.on('dragend', async e => {
                const pos = tempMarker.getLatLng();
                latInput.value = pos.lat.toFixed(7);
                lngInput.value = pos.lng.toFixed(7);
                const addressInput = document.getElementById("address");
                const info = await buscarEndereco(pos.lat, pos.lng);
                if(addressInput && info.rua) addressInput.value = info.rua;
            });
        });
    </script>
@endpush
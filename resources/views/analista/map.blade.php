@extends('layouts.dashboard')

@section('content')
    {{-- X-DATA PARA CONTROLAR O MODAL DE CADASTRO --}}
    <div x-data="{ 
        showModal: false, 
        
        openModal() {
            // Validação simples do HTML antes de abrir o modal
            const form = document.getElementById('form-create');
            if (form.checkValidity()) {
                this.showModal = true;
            } else {
                form.reportValidity();
            }
        },

        confirmAction() {
            document.getElementById('form-create').submit();
        }
    }" class="relative">

        <div class="perfil-box inline-block">
            <h2 class="text-3xl font-bold text-[#358054] mb-0">
                Painel de Cadastro – Mapa de Árvores
            </h2>
        </div>

        {{-- ALERTAS --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex justify-between items-center shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span><strong>Sucesso!</strong> {{ session('success') }}</span>
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

            <form id="form-create" method="POST" action="{{ route('analyst.map.store') }}" class="space-y-10">
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
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName || 'Selecione um bairro'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open = false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-50">
                                @foreach ($bairros as $bairro)
                                    <li @click="selected='{{ $bairro->id }}'; selectedName='{{ $bairro->nome }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white text-sm" :class="selected == '{{ $bairro->id }}' ? 'bg-[#358054] text-white' : ''">
                                        {{ $bairro->nome }}
                                    </li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="bairro_id" :value="selected">
                            <p class="text-xs text-gray-500 mt-1">Será preenchido automaticamente ao clicar no mapa.</p>
                        </div>

                        {{-- NOME CIENTÍFICO (Autocomplete) --}}
                        <div x-data="{
                                query: '{{ old('scientific_name') }}',
                                open: false,
                                list: [],
                                filtered: [],
                                initList() { this.list = {{ json_encode($scientificNames) }}; },
                                filter() {
                                    if (this.query === '') { this.filtered = this.list; } 
                                    else { this.filtered = this.list.filter(item => item.toLowerCase().includes(this.query.toLowerCase())); }
                                    this.open = true;
                                },
                                select(name) { 
                                    this.query = name; 
                                    this.open = false; 
                                    // Atualiza input e dispara evento
                                    setTimeout(() => {
                                        const el = document.getElementById('scientific_name_input');
                                        el.value = name;
                                        el.dispatchEvent(new Event('change'));
                                    }, 50);
                                }
                            }" x-init="initList()" class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Científico</label>
                            <div class="relative">
                                <input type="text" id="scientific_name_input" name="scientific_name" x-model="query" @input="filter()" @click="filter()" @click.outside="open = false" autocomplete="off" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500" placeholder="Selecione ou digite...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                            </div>
                            <ul x-show="open && filtered.length > 0" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto" style="display: none;">
                                <template x-for="name in filtered" :key="name">
                                    <li @click="select(name)" class="cursor-pointer select-none py-2 px-3 hover:bg-[#358054] hover:text-white text-gray-700 text-sm"><span x-text="name"></span></li>
                                </template>
                            </ul>
                        </div>

                        {{-- NOME VULGAR (Autocomplete Bidirecional) --}}
                        <div x-data="{
                                query: '{{ old('vulgar_name') }}',
                                open: false,
                                list: [],
                                filtered: [],
                                initList() { this.list = {{ json_encode($vulgarNames ?? []) }}; },
                                filter() {
                                    if (this.query === '') { this.filtered = this.list; } 
                                    else { this.filtered = this.list.filter(item => item.toLowerCase().includes(this.query.toLowerCase())); }
                                    this.open = true;
                                },
                                select(name) { 
                                    this.query = name; 
                                    this.open = false; 
                                    // Atualiza input e dispara evento
                                    setTimeout(() => {
                                        const el = document.getElementById('vulgar_name_input');
                                        el.value = name;
                                        el.dispatchEvent(new Event('change'));
                                    }, 50);
                                }
                            }" x-init="initList()" class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Vulgar</label>
                            <div class="relative">
                                <input type="text" id="vulgar_name_input" name="vulgar_name" x-model="query" @input="filter()" @click="filter()" @click.outside="open = false" autocomplete="off" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500" placeholder="Selecione ou digite...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                            </div>
                            <ul x-show="open && filtered.length > 0" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto" style="display: none;">
                                <template x-for="name in filtered" :key="name">
                                    <li @click="select(name)" class="cursor-pointer select-none py-2 px-3 hover:bg-[#358054] hover:text-white text-gray-700 text-sm"><span x-text="name"></span></li>
                                </template>
                            </ul>
                        </div>

                        {{-- Caso não tenha espécie --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Caso não tenha espécie</label>
                            <div class="flex flex-col justify-start">
                                <input type="text" name="not_identified_species" value="{{ old('not_identified_species') }}"
                                    class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500"
                                    placeholder="Informe se não identificada">
                                <p class="text-xs text-gray-500 mt-1">Utilize este campo apenas se a espécie não for encontrada.</p>
                            </div>
                        </div>

                        {{-- Descrição --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Árvore</label>
                            <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500 placeholder-gray-400" placeholder="Detalhes sobre a saúde, poda, entorno ou observações...">{{ old('description') }}</textarea>
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
                            {{-- REQUIRED E ASTERISCO ADICIONADOS --}}
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude <span class="text-red-500">*</span></label>
                            <input type="number" step="0.0000001" id="latitude" name="latitude" required class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                            <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                        </div>
                        <div>
                            {{-- REQUIRED E ASTERISCO ADICIONADOS --}}
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude <span class="text-red-500">*</span></label>
                            <input type="number" step="0.0000001" id="longitude" name="longitude" required class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                            <p class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: DADOS GERAIS (DIÂMETRO REMOVIDO) --}}
                <div>
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <h4 class="text-xl font-bold text-gray-700">Status da Árvore</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div x-data="{ open: false, selected: '{{ old('health_status') }}', selectedName: 'Selecione...' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Saúde</label>
                            <button @click="open = !open" type="button" class="w-full border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between px-3 py-2 shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open = false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Boa'; selectedName='Boa'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Boa</li>
                                <li @click="selected='Regular'; selectedName='Regular'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Regular</li>
                                <li @click="selected='Ruim'; selectedName='Ruim'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Ruim</li>
                            </ul>
                            <input type="hidden" name="health_status" :value="selected">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Plantio</label>
                            <input type="date" name="planted_at" max="{{ now()->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 4: DIMENSÕES --}}
                <div>
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <h4 class="text-xl font-bold text-gray-700">Dimensões da Árvore</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach(['cap' => 'CAP (cm)', 'height' => 'Altura (m)', 'crown_height' => 'Altura da Copa (m)', 'crown_diameter_longitudinal' => 'Copa Longitudinal (m)', 'crown_diameter_perpendicular' => 'Copa Perpendicular (m)', 'total_width' => 'Largura Total (m)', 'street_width' => 'Largura da Rua (m)', 'gutter_height' => 'Altura da Gola (m)', 'gutter_width' => 'Largura da Gola (m)', 'gutter_length' => 'Comprimento da Gola (m)'] as $field => $label)
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label><input type="number" step="0.01" name="{{ $field }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-gray-800 focus:ring-green-500 focus:border-green-500"></div>
                        @endforeach
                    </div>
                </div>

                {{-- SEÇÃO 5: CARACTERÍSTICAS BIOLÓGICAS --}}
                <div>
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <h4 class="text-xl font-bold text-gray-700">Condições Biológicas</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div x-data="{ open: false, selected: '{{ old('bifurcation_type') }}', selectedName: 'Selecione...' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Bifurcação</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Ausente</li>
                                <li @click="selected='U'; selectedName='U'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">U</li>
                                <li @click="selected='V'; selectedName='V'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">V</li>
                            </ul>
                            <input type="hidden" name="bifurcation_type" :value="selected">
                        </div>
                        <div x-data="{ open: false, selected: '{{ old('stem_balance') }}', selectedName: 'Selecione...' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio Fuste</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Ausente</li>
                                <li @click="selected='Maior que 45°'; selectedName='Maior que 45°'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Maior que 45°</li>
                                <li @click="selected='Menor que 45°'; selectedName='Menor que 45°'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Menor que 45°</li>
                                <li @click="selected='Acidental...'; selectedName='Acidental ou associada...'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Acidental...</li>
                            </ul>
                            <input type="hidden" name="stem_balance" :value="selected">
                        </div>
                        <div x-data="{ open: false, selected: '{{ old('crown_balance') }}', selectedName: 'Selecione...' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio Copa</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Equilibrada'; selectedName='Equilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Equilibrada</li>
                                <li @click="selected='Medianamente Desequilibrada'; selectedName='Medianamente Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Medianamente Desequilibrada</li>
                                <li @click="selected='Desequilibrada'; selectedName='Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Desequilibrada</li>
                                <li @click="selected='Muito Desequilibrada'; selectedName='Muito Desequilibrada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Muito Desequilibrada</li>
                            </ul>
                            <input type="hidden" name="crown_balance" :value="selected">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 6: AMBIENTE (CORRIGIDA E MESCLADA) --}}
                <div>
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0a8.1 8.1 0 001-8c0-4.42-3.58-8-8-8a8.1 8.1 0 00-1 8m6 8a2 2 0 11-4 0M6 8a2 2 0 11-4 0" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14c-4 0-6-4-6-4m6 4c4 0 6-4 6-4" />
                        </svg>
                        <h4 class="text-xl font-bold text-gray-700">Ambiente e Entorno</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        {{-- Organismos --}}
                        <div x-data="{ open: false, selected: '{{ old('organisms') }}', selectedName: '{{ old('organisms') ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organismos</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Ausente</li>
                                <li @click="selected='Infestação Inicial'; selectedName='Infestação Inicial'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Infestação Inicial</li>
                                <li @click="selected='Infestação Média'; selectedName='Infestação Média'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Infestação Média</li>
                                <li @click="selected='Infestação Avançada'; selectedName='Infestação Avançada'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Infestação Avançada</li>
                            </ul>
                            <input type="hidden" name="organisms" :value="selected">
                        </div>

                        {{-- Alvo --}}
                        <div x-data="{ open: false, selected: '{{ old('target') }}', selectedName: '{{ old('target') ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alvo</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Ruas secundárias estritamente residenciais com pouca circulação de veículos e pessoas'; selectedName='Ruas secundárias estritamente residenciais com pouca circulação de veículos e pessoas'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Ruas secundárias estritamente residenciais com pouca circulação de veículos e pessoas</li>
                                <li @click="selected='Ruas principais ou secundárias com fluxo intermediário de veículos e pessoas'; selectedName='Ruas principais ou secundárias com fluxo intermediário de veículos e pessoas'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Ruas principais ou secundárias com fluxo intermediário de veículos e pessoas</li>
                                <li @click="selected='Avenidas ou ruas principais com fluxo intenso de veículos e pessoas'; selectedName='Avenidas ou ruas principais com fluxo intenso de veículos e pessoas'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Avenidas ou ruas principais com fluxo intenso de veículos e pessoas</li>
                            </ul>
                            <input type="hidden" name="target" :value="selected">
                        </div>

                        {{-- Injúrias --}}
                        <div x-data="{ open: false, selected: '{{ old('injuries') }}', selectedName: '{{ old('injuries') ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Injúrias Mecânicas</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Leves ou Ausentes'; selectedName='Leves ou Ausentes'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Leves ou Ausentes</li>
                                <li @click="selected='Moderadas'; selectedName='Moderadas'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Moderadas</li>
                                <li @click="selected='Graves'; selectedName='Graves'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Graves</li>
                            </ul>
                            <input type="hidden" name="injuries" :value="selected">
                        </div>

                        {{-- Fiação --}}
                        <div x-data="{ open: false, selected: '{{ old('wiring_status') }}', selectedName: '{{ old('wiring_status') ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado da fiação</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                <li @click="selected='Não interfere'; selectedName='Não interfere'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Não interfere</li>
                                <li @click="selected='Pode interferir'; selectedName='Pode interferir'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Pode interferir</li>
                                <li @click="selected='Interfere'; selectedName='Interfere'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Interfere</li>
                                <li @click="selected='Ausente'; selectedName='Ausente'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">Ausente</li>
                            </ul>
                            <input type="hidden" name="wiring_status" :value="selected">
                        </div>
                    </div>
                </div>

                {{-- BOTÃO CADASTRAR --}}
                <button type="button" @click="openModal()" class="bg-green-600 text-white text-lg rounded-md shadow-md hover:bg-green-700 hover:shadow-lg active:bg-[#38c224] active:scale-95 transition px-6 py-3 mt-4">Enviar para Aprovação</button>

            </form>
        </div>

        {{-- MAPA --}}
        <div id="map-section" class="bg-white border border-gray-200 shadow rounded-xl p-8 scroll-mt-24">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Mapa de Árvores</h3>
            <p class="text-sm text-gray-600 mb-4">Clique no mapa para definir coordenadas.</p>
            <div id="map" class="rounded-xl overflow-hidden" style="height: 500px;"></div>
        </div>

        {{-- MODAL DE CONFIRMAÇÃO --}}
        <div x-show="showModal" style="display: none;" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-[9999] flex items-center justify-center backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.stop>
            <div class="bg-white rounded-lg shadow-xl p-8 max-w-sm w-full text-center relative" @click.outside="showModal = false">
                <div class="mb-4 flex justify-center"><div class="rounded-full bg-green-100 bg-opacity-50 p-3"><svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></div></div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Cadastrar Árvore?</h3>
                <p class="text-gray-500 mb-6">Confirma o cadastro desta nova árvore no sistema?</p>
                <div class="flex justify-center gap-4">
                    <button @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">Cancelar</button>
                    <button @click="confirmAction()" class="px-4 py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg font-semibold transition shadow-md">Sim, Cadastrar</button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- SCRIPT DO AUTOCOMPLETE BIDIRECIONAL --}}
    <script>
        const speciesMap = @json($speciesMap ?? []);
        const vulgarToScientific = @json($vulgarToScientific ?? []);

        document.addEventListener('DOMContentLoaded', function() {
            const scientificInput = document.getElementById('scientific_name_input'); 
            const vulgarInput = document.getElementById('vulgar_name_input');

            if (scientificInput && vulgarInput) {
                // Científico -> Vulgar
                scientificInput.addEventListener('change', function() {
                    const selected = this.value;
                    if (speciesMap[selected]) {
                        vulgarInput.value = speciesMap[selected];
                        vulgarInput.dispatchEvent(new Event('input')); 
                    }
                });

                // Vulgar -> Científico (REVERSO)
                vulgarInput.addEventListener('change', function() {
                    const selected = this.value;
                    if (vulgarToScientific[selected]) {
                        scientificInput.value = vulgarToScientific[selected];
                        scientificInput.dispatchEvent(new Event('input')); 
                    }
                });
            }
        });
    </script>

    {{-- SCRIPT DO MAPA --}}
    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const map = L.map('map').setView([-22.6091, -43.7089], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
            let tempMarker = null;
            const latInput = document.getElementById("latitude");
            const lngInput = document.getElementById("longitude");
            const addressInput = document.getElementById("address");

            let bairrosPoligonos = [];
            try {
                const geojsonResponse = await fetch("/bairros.json");
                const geojsonData = await geojsonResponse.json();
                bairrosPoligonos = geojsonData.features;
            } catch (err) {}

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

            function detectarBairro(lat, lng) {
                for (let f of bairrosPoligonos) {
                    if (f.geometry && f.geometry.type === "Polygon") {
                        if (pointInPolygon(lat, lng, f.geometry)) return { id: f.properties.id_bairro, nome: f.properties.nome };
                    }
                }
                return null;
            }

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
                if (tempMarker) map.removeLayer(tempMarker);
                tempMarker = L.marker([lat, lng]).addTo(map).bindPopup("Coordenada selecionada").openPopup();
                const info = await buscarEndereco(lat, lng);
                if(addressInput) addressInput.value = info.rua || "";
                const bairroData = detectarBairro(parseFloat(lat), parseFloat(lng));
                if (bairroData) {
                    window.dispatchEvent(new CustomEvent('set-bairro-map', { detail: { id: bairroData.id, nome: bairroData.nome } }));
                }
            });
        });
    </script>
@endpush
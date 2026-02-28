@extends('layouts.dashboard')

@section('content')
    {{-- X-DATA: Controla o Modal e Permissões --}}
    <div x-data="{ 
        showModal: false, 
        modalType: '', 
        isAnalista: {{ auth('analyst')->check() ? 'true' : 'false' }},
        
        openModal(type) {
            this.modalType = type;
            const form = document.getElementById(type === 'delete' ? 'form-delete' : 'form-edit');
            
            if (type === 'save') {
                if (form.checkValidity()) {
                    this.showModal = true;
                } else {
                    form.reportValidity();
                }
            } else {
                this.showModal = true;
            }
        },

        confirmAction() {
            if (this.modalType === 'delete') {
                document.getElementById('form-delete').submit();
            } else if (this.modalType === 'save') {
                document.getElementById('form-edit').submit();
            }
        },
        
        get title() { return this.modalType === 'delete' ? 'Excluir Árvore?' : 'Salvar Alterações?'; },
        get description() { return this.modalType === 'delete' ? 'Esta ação é irreversível. Deseja realmente excluir esta árvore?' : 'Deseja atualizar os dados desta árvore no sistema?'; },
        get confirmColor() { return this.modalType === 'delete' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'; },
        get iconColor() { return this.modalType === 'delete' ? 'text-red-500' : 'text-green-500'; }
    }" class="relative">

        {{-- Cabeçalho --}}
        <div class="perfil-box inline-block">
            <h2 class="text-3xl font-bold text-[#358054] mb-0">
                @if(auth('admin')->check())
                    Painel de Administração – Editar Árvore
                @else
                    Painel do Analista – Editar Árvore
                @endif
            </h2>
        </div>

        {{-- Alertas de Sucesso --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <strong>Sucesso!</strong> {{ session('success') }}
            </div>
        @endif

        {{-- FORMULÁRIO DE EXCLUSÃO (Invisível) --}}
        <form id="form-delete" action="{{ route('admin.trees.destroy', $tree->id) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>

        {{-- CARTÃO PRINCIPAL DE EDIÇÃO --}}
        <div class="bg-white border border-gray-200 shadow rounded-xl mb-10 p-8">

            <h3 class="text-2xl font-bold mb-6 text-gray-800">
                Editando: <span class="text-[#358054]">
                    {{ $tree->scientific_name ?: $tree->vulgar_name ?: 'Árvore sem nome' }}
                </span> - ID: {{ $tree->id }}
            </h3>

            {{-- FORMULÁRIO DE EDIÇÃO --}}
            <form id="form-edit" method="POST" action="{{ route('admin.trees.update', $tree->id) }}" class="space-y-10" enctype="multipart/form-data">
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
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="block w-full rounded-md border border-gray-300 text-gray-800 shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" />
                            <p x-show="!isAnalista" class="text-xs text-gray-500 mt-1">Clique no mapa para atualizar</p>
                        </div>

                        {{-- Bairro --}}
                        <div x-data="{ 
                                open: false, 
                                selected: '{{ old('bairro_id', $tree->bairro_id) }}', 
                                selectedName: '{{ optional($tree->bairro)->nome ?? 'Selecione um bairro' }}' 
                            }" 
                            @set-bairro-map.window="if(!isAnalista) { selected = $event.detail.id; selectedName = $event.detail.nome }"
                            class="relative w-full">
                            
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                            
                            <button @click="if(!isAnalista) open = !open" type="button" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg x-show="!isAnalista" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                        {{-- NOME CIENTÍFICO (Autocomplete Bidirecional) --}}
                        <div x-data="{
                                query: '{{ old('scientific_name', $tree->scientific_name) }}',
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
                                    setTimeout(() => {
                                        const el = document.getElementById('scientific_name_input');
                                        el.value = name;
                                        el.dispatchEvent(new Event('change'));
                                    }, 50);
                                }
                            }" x-init="initList()" class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Científico</label>
                            <div class="relative">
                                <input type="text" id="scientific_name_input" name="scientific_name" x-model="query" @input="if(!isAnalista) filter()" @click="if(!isAnalista) filter()" @click.outside="open = false" autocomplete="off" 
                                    :disabled="isAnalista"
                                    :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                    class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500" placeholder="Selecione ou digite...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                            </div>
                            <ul x-show="open && filtered.length > 0" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto" style="display: none;">
                                <template x-for="name in filtered" :key="name">
                                    <li @click="select(name)" class="cursor-pointer select-none py-2 px-3 hover:bg-[#358054] hover:text-white text-gray-700 text-sm"><span x-text="name"></span></li>
                                </template>
                            </ul>
                        </div>

                        {{-- NOME POPULAR (Autocomplete Bidirecional) --}}
                        <div x-data="{
                                query: '{{ old('vulgar_name', $tree->vulgar_name) }}',
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
                                    setTimeout(() => {
                                        const el = document.getElementById('vulgar_name_input');
                                        el.value = name;
                                        el.dispatchEvent(new Event('change'));
                                    }, 50);
                                }
                            }" x-init="initList()" class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Popular</label>
                            <div class="relative">
                                <input type="text" id="vulgar_name_input" name="vulgar_name" x-model="query" @input="if(!isAnalista) filter()" @click="if(!isAnalista) filter()" @click.outside="open = false" autocomplete="off" 
                                    :disabled="isAnalista"
                                    :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                    class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500" placeholder="Selecione ou digite...">
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
                                <input type="text" name="no_species_case" value="{{ old('no_species_case', $tree->no_species_case) }}"
                                    :disabled="isAnalista"
                                    :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                    class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500"
                                    placeholder="Informe se não identificada">
                                <p x-show="!isAnalista" class="text-xs text-gray-500 mt-1">Utilize este campo apenas se a espécie não for encontrada.</p>
                            </div>
                        </div>

                        {{-- CAMPO: FOTO DA ÁRVORE (MODIFICADO) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Foto da Árvore</label>
                            
                            {{-- Pré-visualização da imagem atual (Visível para TODOS) --}}
                            @if($tree->photo)
                                <div class="mb-3">
                                    <p class="text-xs text-gray-500 mb-1">Imagem Atual:</p>
                                    <div class="relative w-32 h-32 rounded-lg overflow-hidden border border-gray-200 shadow-sm group">
                                        <img src="{{ asset('storage/' . $tree->photo) }}" alt="Foto da Árvore" class="w-full h-full object-cover">
                                        <a href="{{ asset('storage/' . $tree->photo) }}" target="_blank" class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white opacity-0 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            {{-- Input de Upload (ESCONDIDO PARA ANALISTA) --}}
                            <div x-show="!isAnalista">
                                <input type="file" name="photo" accept="image/*" 
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition shadow-sm border border-gray-200 rounded-lg cursor-pointer" />
                                <p class="text-xs text-gray-500 mt-1">Carregar nova imagem (Substitui a atual). Máx: 10MB.</p>
                            </div>
                            
                            {{-- Mensagem para analista (Opcional) --}}
                            <p x-show="isAnalista" class="text-xs text-gray-400 mt-1 italic">Visualização apenas. Alteração restrita a administradores.</p>
                        </div>

                        {{-- Descrição --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Árvore</label>
                            <textarea name="description" rows="4" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500 placeholder-gray-400" placeholder="Detalhes sobre a saúde, poda, entorno ou observações...">{{ old('description', $tree->description) }}</textarea>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude <span class="text-red-500">*</span></label>
                            <input type="number" step="0.0000001" id="latitude_display" 
                                value="{{ old('latitude', $tree->latitude) }}"
                                disabled
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 bg-gray-200 cursor-not-allowed"
                                :class="!isAnalista ? 'hidden' : ''">
                            <input x-show="!isAnalista" type="number" step="0.0000001" id="latitude" name="latitude" 
                                value="{{ old('latitude', $tree->latitude) }}"
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500"
                                :disabled="isAnalista">
                            <input x-show="isAnalista" type="hidden" name="latitude" value="{{ old('latitude', $tree->latitude) }}">
                            <p x-show="!isAnalista" class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude <span class="text-red-500">*</span></label>
                            <input type="number" step="0.0000001" id="longitude_display"
                                value="{{ old('longitude', $tree->longitude) }}"
                                disabled
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 bg-gray-200 cursor-not-allowed"
                                :class="!isAnalista ? 'hidden' : ''">
                            <input x-show="!isAnalista" type="number" step="0.0000001" id="longitude" name="longitude" 
                                value="{{ old('longitude', $tree->longitude) }}"
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500"
                                :disabled="isAnalista">
                            <input x-show="isAnalista" type="hidden" name="longitude" value="{{ old('longitude', $tree->longitude) }}">
                            <p x-show="!isAnalista" class="text-xs text-gray-500 mt-1">Clique no mapa para preencher</p>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div x-data="{ open: false, selected: '{{ old('health_status', $tree->health_status) }}', selectedName: '{{ old('health_status', $tree->health_status) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Saúde</label>
                            <button @click="if(!isAnalista) open = !open" type="button" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full border border-gray-300 rounded-lg text-left flex items-center justify-between px-3 py-2 shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg x-show="!isAnalista" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open = false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['health_status'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="health_status" :value="selected">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Plantio</label>
                            <input type="date" name="planted_at" max="{{ now()->format('Y-m-d') }}" 
                                value="{{ old('planted_at', $tree->planted_at ? \Carbon\Carbon::parse($tree->planted_at)->format('Y-m-d') : '') }}"
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500">
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
                    
                    {{-- Campos CAP e DAP Dinâmicos --}}
                    <div class="mb-8" x-data="{ 
                        caps: [
                            @php
                                $foundCap = false;
                                $dap1 = $tree->cap ? round($tree->cap / pi(), 2) : null;
                                if($tree->cap) {
                                    echo "{ id: '', cap: '{$tree->cap}', dap: '{$dap1}', label: 'CAP 1' },";
                                    $foundCap = true;
                                }
                                for($i = 2; $i <= 20; $i++) {
                                    $capKey = 'cap' . $i;
                                    if(!empty($tree->$capKey)) {
                                        $dap = round($tree->$capKey / pi(), 2);
                                        echo "{ id: '{$i}', cap: '{$tree->$capKey}', dap: '{$dap}', label: 'CAP {$i}' },";
                                        $foundCap = true;
                                    }
                                }
                                if(!$foundCap) {
                                    echo "{ id: '', cap: '', dap: '', label: 'CAP 1' }";
                                }
                            @endphp
                        ],
                        nextId: 2, 
                        init() {
                            let maxId = 1;
                            this.caps.forEach(c => {
                                if(c.id && parseInt(c.id) > maxId) maxId = parseInt(c.id);
                            });
                            this.nextId = maxId + 1;
                        },
                        addCap() {
                            if (this.caps.length < 20) {
                                this.caps.push({ id: this.nextId, cap: '', dap: '' });
                                this.nextId++;
                            }
                        },
                        removeCap(index) {
                            if (this.caps.length > 1) {
                                this.caps.splice(index, 1);
                            }
                        }
                    }">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-bold text-gray-700 uppercase">Circunferências (CAP) e Diâmetros (DAP)</label>
                            <button type="button" @click="addCap()" x-show="!isAnalista && caps.length < 20" 
                                class="bg-[#358054] text-white px-3 py-1 rounded-md text-sm font-semibold hover:bg-green-700 transition flex items-center gap-1">
                                <i data-lucide="plus" class="w-4 h-4"></i> Adicionar CAP
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <template x-for="(item, index) in caps" :key="index">
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 relative group">
                                    <div class="flex flex-col gap-2">
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 uppercase" x-text="item.label || ('CAP ' + item.id)"></label>
                                            <input type="number" step="0.01" :name="item.id == '' ? 'cap' : 'cap' + item.id" x-model="item.cap"
                                                :disabled="isAnalista"
                                                :class="isAnalista ? 'bg-gray-200' : 'bg-white'"
                                                class="w-full border border-gray-300 rounded-md shadow-sm px-2 py-1 text-sm focus:ring-green-500 focus:border-green-500">
                                        </div>
                                        <div x-show="item.cap">
                                            <label class="text-[10px] font-bold text-gray-500 uppercase">DAP</label>
                                            <div class="bg-gray-100 px-2 py-1 rounded text-sm text-gray-600 font-mono border border-dashed border-gray-300" 
                                                x-text="item.cap ? (item.cap / 3.14159).toFixed(2) + ' m' : '-'">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeCap(index)" x-show="!isAnalista && caps.length > 1"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach(['height' => 'Altura (m)', 'crown_height' => 'Altura da Copa (m)', 'crown_diameter_longitudinal' => 'Copa Longitudinal (m)', 'crown_diameter_perpendicular' => 'Copa Perpendicular (m)', 'total_width' => 'Largura Total (m)', 'street_width' => 'Largura da Rua (m)', 'gutter_height' => 'Altura da Gola (m)', 'gutter_width' => 'Largura da Gola (m)', 'gutter_length' => 'Comprimento da Gola (m)'] as $field => $label)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $tree->$field) }}"
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-800 focus:ring-green-500 focus:border-green-500">
                        </div>
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
                        <div x-data="{ open: false, selected: '{{ old('bifurcation_type', $tree->bifurcation_type) }}', selectedName: '{{ old('bifurcation_type', $tree->bifurcation_type) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Bifurcação</label>
                            <button @click="if(!isAnalista) open = !open" type="button" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg x-show="!isAnalista" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['bifurcation_type'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="bifurcation_type" :value="selected">
                        </div>

                        <div x-data="{ open: false, selected: '{{ old('stem_balance', $tree->stem_balance) }}', selectedName: '{{ old('stem_balance', $tree->stem_balance) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio Fuste</label>
                            <button @click="if(!isAnalista) open = !open" type="button" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg x-show="!isAnalista" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['stem_balance'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="stem_balance" :value="selected">
                        </div>

                        <div x-data="{ open: false, selected: '{{ old('crown_balance', $tree->crown_balance) }}', selectedName: '{{ old('crown_balance', $tree->crown_balance) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Equilíbrio Copa</label>
                            <button @click="if(!isAnalista) open = !open" type="button" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg x-show="!isAnalista" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['crown_balance'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
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
                        <div x-data="{ open: false, selected: '{{ old('organisms', $tree->organisms) }}', selectedName: '{{ old('organisms', $tree->organisms) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organismos</label>
                            <button @click="if(!isAnalista) open = !open" type="button" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg x-show="!isAnalista" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['organisms'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="organisms" :value="selected">
                        </div>

                        {{-- Alvo --}}
                        <div x-data="{ open: false, selected: '{{ old('target', $tree->target) }}', selectedName: '{{ old('target', $tree->target) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alvo</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['target'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="target" :value="selected">
                        </div>

                        {{-- Injúrias --}}
                        <div x-data="{ open: false, selected: '{{ old('injuries', $tree->injuries) }}', selectedName: '{{ old('injuries', $tree->injuries) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Injúrias Mecânicas</label>
                            <button @click="if(!isAnalista) open = !open" type="button" 
                                :disabled="isAnalista"
                                :class="isAnalista ? 'bg-gray-200 cursor-not-allowed' : 'bg-gray-50'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500">
                                <span x-text="selectedName"></span>
                                <svg x-show="!isAnalista" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['injuries'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="injuries" :value="selected">
                        </div>

                        {{-- Fiação --}}
                        <div x-data="{ open: false, selected: '{{ old('wiring_status', $tree->wiring_status) }}', selectedName: '{{ old('wiring_status', $tree->wiring_status) ?: 'Selecione...' }}' }" class="relative w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado da fiação</label>
                            <button @click="open = !open" type="button" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-left flex items-center justify-between shadow-sm focus:ring-green-500 focus:border-green-500"><span x-text="selectedName"></span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>
                            <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-0 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                                @foreach($dynamicOptions['wiring_status'] as $option)
                                    <li @click="selected='{{ $option }}'; selectedName='{{ $option }}'; open=false" class="px-3 py-2 cursor-pointer hover:bg-[#358054] hover:text-white">{{ $option }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="wiring_status" :value="selected">
                        </div>
                    </div>
                </div>

                {{-- BOTÕES DE AÇÃO --}}
                <div class="flex flex-wrap gap-4 pt-6 border-t">
                    <button type="button" @click="openModal('save')" class="bg-green-600 text-white text-lg rounded-md shadow-md hover:bg-green-700 hover:shadow-lg active:bg-[#38c224] active:scale-95 transition px-8 py-3">Salvar Alterações</button>
                    <button x-show="!isAnalista" type="button" @click="openModal('delete')" class="bg-red-600 text-white text-lg rounded-md shadow-md hover:bg-red-700 hover:shadow-lg active:bg-red-800 active:scale-95 transition px-8 py-3">Excluir Árvore</button>
                    <a href="{{ route('admin.trees.index') }}" class="bg-gray-100 text-gray-700 text-lg rounded-md shadow-sm hover:bg-gray-200 transition px-8 py-3 decoration-0 flex items-center justify-center">Voltar para Lista</a>
                </div>

            </form>
        </div>

        {{-- MAPA --}}
        <div id="map-section" class="bg-white border border-gray-200 shadow rounded-xl p-8 scroll-mt-24">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Localização no Mapa</h3>
            <p class="text-sm text-gray-600 mb-4">Clique no mapa para atualizar as coordenadas e o endereço.</p>
            <div id="map" class="rounded-xl overflow-hidden" style="height: 500px;"></div>
        </div>

        {{-- MODAL DE CONFIRMAÇÃO --}}
        <div x-show="showModal" style="display: none;" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-[9999] flex items-center justify-center backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.stop>
            <div class="bg-white rounded-lg shadow-xl p-8 max-w-sm w-full text-center relative" @click.outside="showModal = false">
                <div class="mb-4 flex justify-center">
                    <div class="rounded-full p-3" :class="modalType === 'delete' ? 'bg-red-100' : 'bg-green-100'">
                        <template x-if="modalType === 'delete'"><svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></template>
                        <template x-if="modalType === 'save'"><svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></template>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="title"></h3>
                <p class="text-gray-500 mb-6" x-text="description"></p>
                <div class="flex justify-center gap-4">
                    <button @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">Cancelar</button>
                    <button @click="confirmAction()" class="px-4 py-2 text-white rounded-lg font-semibold transition shadow-md" :class="confirmColor">Confirmar</button>
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
            // Inicializa o mapa centrado na árvore atual
            const initialLat = {{ $tree->latitude }};
            const initialLng = {{ $tree->longitude }};
            
            const map = L.map('map').setView([initialLat, initialLng], 18);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
            
            let tempMarker = L.marker([initialLat, initialLng]).addTo(map).bindPopup("Localização Atual da Árvore").openPopup();
            
            const latInput = document.getElementById("latitude");
            const lngInput = document.getElementById("longitude");
            const latDisplay = document.getElementById("latitude_display");
            const lngDisplay = document.getElementById("longitude_display");
            const addressInput = document.getElementById("address");
            const isAnalista = {{ auth('analyst')->check() ? 'true' : 'false' }};

            // Se for analista, o mapa é apenas visualização, não permite clique
            if (!isAnalista) {
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
                    if(latDisplay) latDisplay.value = lat;
                    if(lngDisplay) lngDisplay.value = lng;

                    if (tempMarker) map.removeLayer(tempMarker);
                    tempMarker = L.marker([lat, lng]).addTo(map).bindPopup("Nova Coordenada").openPopup();
                    
                    const info = await buscarEndereco(lat, lng);
                    if(addressInput) addressInput.value = info.rua || "";
                    
                    const bairroData = detectarBairro(parseFloat(lat), parseFloat(lng));
                    if (bairroData) {
                        window.dispatchEvent(new CustomEvent('set-bairro-map', { detail: { id: bairroData.id, nome: bairroData.nome } }));
                    }
                });
            }
        });
    </script>
@endpush
@extends('layouts.dashboard')

@section('title', 'Editar Ordem de Serviço')

@section('content')

@php
    // Opções para sincronizar com os dados do Analista
    $todosMotivos = [
        'Risco de Queda' => 'Risco de queda',
        'Conflito rede eletrica' => 'Conflito com rede elétrica',
        'Danos infraestrutura' => 'Danos à infraestrutura',
        'Outras' => 'Outras razões',
    ];

    $todosServicos = [
        'Levantamento copa' => 'Poda de levantamento de copa',
        'Desobstrucao' => 'Poda de desobstrução de rede',
        'Limpeza' => 'Poda de limpeza',
        'Adequacao' => 'Poda de adequação',
        'Remocao Total' => 'Remoção total da árvore',
        'Outras' => 'Outras intervenções',
    ];

    $todosEquipamentos = [
        'Motosserra' => 'Motosserra',
        'Motopoda' => 'Motopoda',
        'EPIs' => 'EPIs',
        'Cordas' => 'Cordas',
        'Cones' => 'Cones',
        'Caminhão' => 'Caminhão',
    ];
@endphp

<div class="max-w-4xl mx-auto bg-white p-8 shadow-2xl rounded-lg border-t-8 border-[#358054] relative overflow-hidden print:border-0 print:shadow-none print:p-0 print:max-w-full">
    
    <form action="{{ route('admin.os.update', $os->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Mensagens de Feedback --}}
@if(session('success'))
    <div class="mb-6 flex items-center gap-3 p-4 text-sm text-green-800 border-l-4 border-green-500 bg-green-50 rounded-r-lg shadow-sm animate-fade-in print:hidden">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
        <div>
            <span class="font-bold">Sucesso!</span> {{ session('success') }}
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 flex items-center gap-3 p-4 text-sm text-red-800 border-l-4 border-red-500 bg-red-50 rounded-r-lg shadow-sm print:hidden">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
        <div>
            <span class="font-bold">Ops! Verifique os erros:</span>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

        {{-- MARCA D'ÁGUA --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
            <img src="{{ asset('images/logo.png') }}" alt="Marca D'água" class="w-2/3 object-contain opacity-15">
        </div>

        <div class="relative z-10">
            {{-- CABEÇALHO --}}
            <div class="flex justify-between items-center mb-6 border-b pb-4 print:mb-2 print:pb-2">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto object-contain print:h-12" alt="Logo">
                    <div class="text-xs text-gray-600 leading-tight font-bold uppercase">
                        ESTADO DO RIO DE JANEIRO<br>
                        MUNICÍPIO DE PARACAMBI<br>
                        SECRETARIA MUNICIPAL DE MEIO AMBIENTE
                    </div>
                </div>
                <div class="text-right">
                    <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">ORDEM DE SERVIÇO</h3>
                    <p class="text-sm font-bold mt-1">Poda e Remoção de Árvores</p>
                </div>
            </div>

            <div class="text-sm">
                {{-- DADOS DA SOLICITAÇÃO --}}
                <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2">
                    <div>
                        <label class="font-bold block text-gray-700">Nº Solicitação:</label>
                        <p class="p-1 font-medium">{{ $os->contact->id }}</p>
                    </div>
                    <div>
                        <label class="font-bold block text-gray-700">Endereço:</label>
                        <p class="p-1 font-medium">{{ $os->contact->rua }}, {{ $os->contact->numero }} - {{ $os->contact->bairro }}</p>
                    </div>
                </div>

                {{-- COORDENADAS (Latitude e Longitude) --}}
                <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2">
                    <div>
                        <label class="font-bold block text-gray-700">Latitude:</label>
                        <input type="text" name="latitude" value="{{ old('latitude', $os->latitude) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-[#358054] focus:border-[#358054] print:border-none">
                    </div>
                    <div>
                        <label class="font-bold block text-gray-700">Longitude:</label>
                        <input type="text" name="longitude" value="{{ old('longitude', $os->longitude) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-[#358054] focus:border-[#358054] print:border-none">
                    </div>
                </div>

                {{-- ESPÉCIES E QUANTIDADE --}}
                <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2">
                    <div class="col-span-2">
                        <label class="font-bold block text-gray-700">Espécie(s):</label>
                        <input type="text" name="especies" value="{{ old('especies', is_array($os->especies) ? implode(', ', $os->especies) : $os->especies) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-[#358054] focus:border-[#358054] print:border-none">
                    </div>
                    <div>
                        <label class="font-bold block text-gray-700">Quantidade:</label>
                        <input type="number" name="quantidade" value="{{ old('quantidade', $os->quantidade) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-[#358054] focus:border-[#358054] print:border-none">
                    </div>
                </div>

                {{-- MOTIVOS --}}
                <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded print:bg-transparent">
                    <h4 class="font-bold underline mb-2">Motivo da Intervenção</h4>
                    <div class="grid grid-cols-2 gap-y-2">
                        @foreach ($todosMotivos as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="motivos[]" value="{{ $value }}" 
                                    {{ in_array($value, old('motivos', $os->motivos ?? [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-[#358054] focus:ring-[#358054]">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SERVIÇOS --}}
                <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded print:bg-transparent">
                    <h4 class="font-bold underline mb-2">Serviços a Executar</h4>
                    <div class="grid grid-cols-2 gap-y-2">
                        @foreach ($todosServicos as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="servicos[]" value="{{ $value }}" 
                                    {{ in_array($value, old('servicos', $os->servicos ?? [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-[#358054] focus:ring-[#358054]">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- EQUIPAMENTOS E EPI'S --}}
                <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded print:bg-transparent">
                    <h4 class="font-bold underline mb-2">Equipamentos e EPI's Necessários</h4>
                    <div class="grid grid-cols-3 gap-y-2">
                        @foreach ($todosEquipamentos as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="equipamentos[]" value="{{ $value }}" 
                                    {{ in_array($value, old('equipamentos', $os->equipamentos ?? [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-[#358054] focus:ring-[#358054]">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- DATAS E OBSERVAÇÕES --}}
                <div class="grid grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="font-bold block text-xs uppercase text-gray-600">Data Vistoria:</label>
                        <input type="date" name="data_vistoria" value="{{ old('data_vistoria', $os->data_vistoria ? \Carbon\Carbon::parse($os->data_vistoria)->format('Y-m-d') : '') }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="font-bold block text-xs uppercase text-gray-600">Previsão Execução:</label>
                        <input type="date" name="data_execucao" value="{{ old('data_execucao', $os->data_execucao ? \Carbon\Carbon::parse($os->data_execucao)->format('Y-m-d') : '') }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="font-bold block text-xs uppercase text-gray-600">Observações Técnicas/Admin:</label>
                        <textarea name="observacoes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-[#358054] focus:border-[#358054]">{{ old('observacoes', $os->observacoes) }}</textarea>
                    </div>
                </div>

                {{-- ASSINATURAS (PRINT ONLY) --}}
                <div class="hidden print:grid grid-cols-2 gap-8 mt-12 pt-4">
                    <div class="text-center border-t border-black pt-1">
                        <p class="text-[10px] font-bold">Responsável Técnico</p>
                    </div>
                    <div class="text-center border-t border-black pt-1">
                        <p class="text-[10px] font-bold">Encarregado da Equipe</p>
                    </div>
                </div>

                {{-- BOTÕES --}}
                <div class="mt-8 flex justify-end gap-3 print:hidden">
                    <a href="{{ route('admin.contato.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-semibold">Voltar</a>
                    <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center gap-2 font-semibold">
                        <i data-lucide="printer" class="w-4 h-4"></i> Imprimir
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#358054] text-white font-bold rounded-md hover:bg-[#2a6643] transition flex items-center gap-2 shadow-lg">
                        <i data-lucide="save" class="w-4 h-4"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>lucide.createIcons();</script>
@endsection
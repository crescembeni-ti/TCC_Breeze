@extends('layouts.dashboard')

@section('title', 'Ordem de Serviço #' . $os->id)

@section('content')

{{-- Define as listas completas de opções para MOTIVOS, SERVIÇOS e EQUIPAMENTOS --}}
@php
    // Estas listas devem ser idênticas às usadas no formulário do Analista
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

<div class="max-w-4xl mx-auto bg-white p-8 shadow-2xl rounded-lg border-t-8 border-[#358054]">

    {{-- TÍTULO DO DOCUMENTO --}}
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/Brasao_Verde.png') }}" class="h-16 w-auto" alt="Logo">
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

    {{-- INÍCIO DA REPLICAÇÃO DO FORMULÁRIO --}}
    <div class="text-sm">

        {{-- DADOS DA SOLICITAÇÃO --}}
        <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2">
            <div>
                <label class="font-bold block text-gray-700">Nº Solicitação:</label>
                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1">{{ $os->contact->id }}</p>
            </div>
            <div>
                <label class="font-bold block text-gray-700">Data:</label>
                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1">{{ \Carbon\Carbon::parse($os->contact->created_at)->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- IDENTIFICAÇÃO DA ÁREA --}}
        <div class="mb-4 border-b border-gray-300 pb-2">
            <h4 class="font-bold underline mb-1">Identificação da Área</h4>
            <div class="grid grid-cols-1 gap-2">
                <div>
                    <span class="text-gray-600">Endereço:</span>
                    <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50">
                        {{ $os->contact->rua ?? 'N/A' }}{{ $os->contact->numero ? ', ' . $os->contact->numero : '' }} - {{ $os->contact->bairro ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <span class="font-bold underline mb-1">Coordenadas Geográficas:</span>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600">Latitude:</span>
                            <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50">
                                {{ $os->latitude ?? 'Não registrado' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-600">Longitude:</span>
                            <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50">
                                {{ $os->longitude ?? 'Não registrado' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- IDENTIFICAÇÃO DAS ÁRVORES --}}
        <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2">
            <div class="col-span-2">
                <label class="font-bold block">Espécie(s):</label>
                <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50">{{ $os->especies ?? 'Não informado' }}</p>
            </div>
            <div>
                <label class="font-bold block">Quantidade:</label>
                <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50">{{ $os->quantidade ?? 'Não informado' }}</p>
            </div>
        </div>

        {{-- MOTIVO DA INTERVENÇÃO --}}
        <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded">
            <h4 class="font-bold underline mb-1">Motivo da Intervenção</h4>
            <div class="grid grid-cols-2 gap-y-1">
                @foreach ($todosMotivos as $value => $label)
                    @php $checked = in_array($value, $os->motivos ?? []); @endphp
                    <div class="flex items-center gap-2 text-gray-700">
                        <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }}"></i>
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SERVIÇOS A SEREM EXECUTADOS --}}
        <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded">
            <h4 class="font-bold underline mb-1">Serviços a serem executados</h4>
            <div class="grid grid-cols-2 gap-y-1">
                @foreach ($todosServicos as $value => $label)
                    @php $checked = in_array($value, $os->servicos ?? []); @endphp
                    <div class="flex items-center gap-2 text-gray-700">
                        <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }}"></i>
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- EQUIPAMENTOS --}}
        <div class="mb-4 border-b border-gray-300 pb-2">
            <h4 class="font-bold underline mb-1">Equipamentos Necessários</h4>
            <div class="flex flex-wrap gap-4">
                @foreach ($todosEquipamentos as $value => $label)
                    @php $checked = in_array($value, $os->equipamentos ?? []); @endphp
                    <div class="flex items-center gap-1 text-gray-700">
                        <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }}"></i>
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- RESPONSABILIDADES --}}
        <div class="mb-4 border-b-2 border-gray-400 pb-2 bg-gray-50 p-2 rounded">
            <h4 class="font-bold text-sm mb-2 border-b">Responsabilidades e Procedimentos</h4>
            <div class="flex flex-col gap-2 text-xs">
                <div>✔ Segurança: Utilização obrigatória de EPIs</div>
                <div>✔ Sinalização: Uso de cones e faixas</div>
                <div>✔ Descarte: Destino adequado</div>
                <div>✔ Registro: Fotos antes e depois</div>
                <div>✔ Comunicação: Informar imprevistos</div>
            </div>
        </div>

        {{-- DATAS --}}
        <div class="grid grid-cols-2 gap-6 mt-4">
            <div>
                <label class="font-bold text-xs">DATA VISTORIA:</label>
                <p class="border p-1 bg-gray-50">{{ $os->data_vistoria ? \Carbon\Carbon::parse($os->data_vistoria)->format('d/m/Y') : 'N/A' }}</p>
            </div>
            <div>
                <label class="font-bold text-xs">PREVISÃO EXECUÇÃO:</label>
                <p class="border p-1 bg-gray-50">{{ $os->data_execucao ? \Carbon\Carbon::parse($os->data_execucao)->format('d/m/Y') : 'N/A' }}</p>
            </div>
        </div>

        @if ($os->observacoes)
        <div class="mt-4">
            <label class="font-bold text-xs">Observações:</label>
            <p class="bg-gray-100 p-2 rounded">{{ $os->observacoes }}</p>
        </div>
        @endif

        {{-- BOTÕES --}}
        <div class="mt-6 flex flex-row-reverse gap-2 print:hidden">
            <a href="{{ auth()->guard('analyst')->check() ? route('analyst.os.enviadas') : route('admin.os.index') }}"
               class="bg-gray-700 text-white px-3 py-2 rounded">
                Voltar
            </a>
            <button onclick="window.print()" class="bg-[#beffb4] px-3 py-2 rounded">
                Imprimir OS
            </button>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>

@endsection

@push('scripts')
<style>
@media print {

    body {
        font-size: 12px !important;
        line-height: 1.25 !important;
    }

    .p-8 { padding: 20px !important; }

    .mb-6 { margin-bottom: 12px !important; }
    .mb-4 { margin-bottom: 10px !important; }
    .mt-4 { margin-top: 10px !important; }
    .gap-6 { gap: 12px !important; }
    .gap-4 { gap: 10px !important; }

    .site-header, .sidebar, footer,
    .print\:hidden {
        display: none !important;
    }

    .shadow-2xl,
    .border-t-8 {
        box-shadow: none !important;
        border: none !important;
    }

    main {
        padding: 0 !important;
        margin: 0 !important;
        max-width: none !important;
    }
}
</style>
@endpush

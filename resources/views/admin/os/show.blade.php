@extends('layouts.dashboard')

@section('title', 'Ordem de Serviço')

@section('content')

@php
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

{{-- 
    Container Principal:
    - Na tela: padding p-8, borda colorida, sombra.
    - Na impressão (print): sem borda, sem sombra, padding zero, largura total.
--}}
<div class="max-w-4xl mx-auto bg-white p-8 shadow-2xl rounded-lg border-t-8 border-[#358054] relative overflow-hidden print:border-0 print:shadow-none print:p-0 print:max-w-full">

    {{-- MARCA D'ÁGUA --}}
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
        {{-- Removido 'grayscale' para exibir a cor original (verde) --}}
        <img src="{{ asset('images/logo.png') }}" alt="Marca D'água" class="w-2/3 opacity-15 print:w-1/2 print:opacity-15">
    </div>

    {{-- CONTEÚDO --}}
    <div class="relative z-10">

        {{-- CABEÇALHO --}}
        <div class="flex justify-between items-center mb-6 border-b pb-4 print:mb-2 print:pb-2 print:border-b-2">
            <div class="flex items-center gap-2">
                {{-- Logo menor na impressão para economizar espaço vertical --}}
                <img src="{{ asset('images/Brasao_Verde.png') }}" class="h-16 w-auto print:h-12" alt="Logo">
                <div class="text-xs text-gray-600 leading-tight font-bold uppercase print:text-xs">
                    ESTADO DO RIO DE JANEIRO<br>
                    MUNICÍPIO DE PARACAMBI<br>
                    SECRETARIA MUNICIPAL DE MEIO AMBIENTE
                </div>
            </div>
            <div class="text-right">
                <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black print:text-lg">ORDEM DE SERVIÇO</h3>
                <p class="text-sm font-bold mt-1 print:text-sm">Poda e Remoção de Árvores</p>
            </div>
        </div>

        {{-- CORPO DO FORMULÁRIO --}}
        <div class="text-sm print:text-sm">

            {{-- DADOS DA SOLICITAÇÃO --}}
            <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2 print:mb-1 print:pb-1 print:gap-2">
                <div>
                    <label class="font-bold block text-gray-700">Nº Solicitação:</label>
                    <p class="w-full border-0 border-b border-gray-400 bg-gray-50/80 p-1 print:bg-transparent print:p-0 font-medium">{{ $os->contact->id }}</p>
                </div>
                <div>
                    <label class="font-bold block text-gray-700">Data:</label>
                    <p class="w-full border-0 border-b border-gray-400 bg-gray-50/80 p-1 print:bg-transparent print:p-0 font-medium">{{ \Carbon\Carbon::parse($os->contact->created_at)->format('d/m/Y') }}</p>
                </div>
            </div>

            {{-- IDENTIFICAÇÃO DA ÁREA --}}
            <div class="mb-4 border-b border-gray-300 pb-2 print:mb-1 print:pb-1">
                <h4 class="font-bold underline mb-1 print:mb-0">Identificação da Área</h4>
                <div class="grid grid-cols-1 gap-2 print:gap-1">
                    <div>
                        <span class="text-gray-600 font-semibold">Endereço:</span>
                        <span class="ml-1">{{ $os->contact->rua ?? 'N/A' }}{{ $os->contact->numero ? ', ' . $os->contact->numero : '' }} - {{ $os->contact->bairro ?? 'N/A' }}</span>
                    </div>
                    <div class="print:flex print:gap-4">
                        <div class="print:flex-1">
                            <span class="text-gray-600 font-semibold">Latitude:</span>
                            <span class="ml-1">{{ $os->latitude ?? 'N/R' }}</span>
                        </div>
                        <div class="print:flex-1">
                            <span class="text-gray-600 font-semibold">Longitude:</span>
                            <span class="ml-1">{{ $os->longitude ?? 'N/R' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- IDENTIFICAÇÃO DAS ÁRVORES --}}
            <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2 print:mb-1 print:pb-1 print:gap-2">
                <div class="col-span-2">
                    <label class="font-bold block">Espécie(s):</label>
                    <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50/80 print:bg-transparent print:p-0">
                        @if(is_array($os->especies))
                            {{ implode(', ', $os->especies) }}
                        @else
                            {{ $os->especies ?? 'Não informado' }}
                        @endif
                    </p>
                </div>
                <div>
                    <label class="font-bold block">Quantidade:</label>
                    <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50/80 print:bg-transparent print:p-0">{{ $os->quantidade ?? 'N/I' }}</p>
                </div>
            </div>

            {{-- MOTIVO DA INTERVENÇÃO --}}
            <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded print:bg-transparent print:p-0 print:mb-1 print:border-none">
                <h4 class="font-bold underline mb-1 print:mb-0">Motivo da Intervenção</h4>
                <div class="grid grid-cols-2 gap-y-1 print:gap-y-0 print:text-sm">
                    @foreach ($todosMotivos as $value => $label)
                        @php $checked = in_array($value, $os->motivos ?? []); @endphp
                        <div class="flex items-center gap-2 text-gray-700 print:text-black">
                            <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }} print:w-3.5 print:h-3.5 print:text-black"></i>
                            <span class="{{ $checked ? 'font-bold' : '' }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- SERVIÇOS A SEREM EXECUTADOS --}}
            <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded print:bg-transparent print:p-0 print:mb-1 print:border-none">
                <h4 class="font-bold underline mb-1 print:mb-0">Serviços a Executar</h4>
                <div class="grid grid-cols-2 gap-y-1 print:gap-y-0 print:text-sm">
                    @foreach ($todosServicos as $value => $label)
                        @php $checked = in_array($value, $os->servicos ?? []); @endphp
                        <div class="flex items-center gap-2 text-gray-700 print:text-black">
                            <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }} print:w-3.5 print:h-3.5 print:text-black"></i>
                            <span class="{{ $checked ? 'font-bold' : '' }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- EQUIPAMENTOS E MATERIAIS --}}
            <div class="mb-4 border-b border-gray-300 pb-2 print:mb-1 print:pb-1">
                <h4 class="font-bold underline mb-1 print:mb-0">Equipamentos</h4>
                <div class="flex flex-wrap gap-4 print:gap-3 print:text-sm">
                    @foreach ($todosEquipamentos as $value => $label)
                        @php $checked = in_array($value, $os->equipamentos ?? []); @endphp
                        <div class="flex items-center gap-1 text-gray-700 print:text-black">
                            <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }} print:w-3.5 print:h-3.5 print:text-black"></i>
                            <span class="{{ $checked ? 'font-bold' : '' }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- RESPONSABILIDADES E PROCEDIMENTOS --}}
            <div class="mb-4 border-b-2 border-gray-400 pb-2 bg-gray-50/80 p-2 rounded print:bg-transparent print:p-0 print:mb-2 print:border-none">
                <h4 class="font-bold text-sm text-black mb-2 border-b border-gray-300 print:mb-1 print:text-sm">Procedimentos Obrigatórios</h4>
                <div class="flex flex-col gap-2 text-xs text-black print:gap-0.5 print:text-[11px]">
                    <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300 print:p-0 print:border-none">
                        <span class="mt-0.5 text-[#358054] font-extrabold text-sm print:text-black">&#10003;</span>
                        <div class="flex-1 flex"><span class="font-bold w-24 shrink-0">Segurança:</span><span>Utilização obrigatória de EPIs</span></div>
                    </div>
                    <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300 print:p-0 print:border-none">
                        <span class="mt-0.5 text-[#358054] font-extrabold text-sm print:text-black">&#10003;</span>
                        <div class="flex-1 flex"><span class="font-bold w-24 shrink-0">Sinalização:</span><span>Uso de cones e faixas de segurança</span></div>
                    </div>
                    <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300 print:p-0 print:border-none">
                        <span class="mt-0.5 text-[#358054] font-extrabold text-sm print:text-black">&#10003;</span>
                        <div class="flex-1 flex"><span class="font-bold w-24 shrink-0">Descarte:</span><span>Destino adequado dos resíduos</span></div>
                    </div>
                    <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300 print:p-0 print:border-none">
                        <span class="mt-0.5 text-[#358054] font-extrabold text-sm print:text-black">&#10003;</span>
                        <div class="flex-1 flex"><span class="font-bold w-24 shrink-0">Registro:</span><span>Fotos antes e depois</span></div>
                    </div>
                </div>
            </div>

            {{-- DATAS E OBSERVAÇÕES --}}
            <div class="grid grid-cols-2 gap-6 mt-4 print:mt-1 print:gap-4">
                <div>
                    <label class="font-bold block text-xs uppercase">DATA VISTORIA:</label>
                    <p class="w-full border p-1 rounded bg-gray-50/80 print:bg-transparent print:border-gray-400 print:py-0">{{ $os->data_vistoria ? \Carbon\Carbon::parse($os->data_vistoria)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <label class="font-bold block text-xs uppercase">PREVISÃO EXECUÇÃO:</label>
                    <p class="w-full border p-1 rounded bg-gray-50/80 print:bg-transparent print:border-gray-400 print:py-0">{{ $os->data_execucao ? \Carbon\Carbon::parse($os->data_execucao)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                
                @if ($os->observacoes)
                <div class="mt-4 col-span-2 print:mt-2">
                    <label class="font-bold block text-xs uppercase">Observações:</label>
                    <p class="bg-gray-100/90 p-2 rounded whitespace-pre-wrap print:bg-transparent print:border print:border-gray-300 print:p-1 print:text-xs">{{ $os->observacoes }}</p>
                </div>
                @endif
            </div>

            {{-- ASSINATURAS --}}
            <div class="grid grid-cols-2 gap-8 mt-12 pt-4 print:mt-6 print:flex print:justify-between">
                <div class="text-center border-t border-black pt-2 w-full">
                    <p class="text-xs font-bold">Responsável Técnico</p>
                </div>
                <div class="text-center border-t border-black pt-2 w-full">
                    <p class="text-xs font-bold">Recebido por</p>
                </div>
            </div>

        </div>

        {{-- BOTÕES --}}
        <div class="mt-6 flex flex-row-reverse gap-2 print:hidden">
            @php
                if (auth()->guard('analyst')->check()) {
                    $rotaVoltar = route('analyst.os.enviadas');
                    $textoBotao = 'Voltar';
                } else {
                    $rotaVoltar = route('admin.os.index');
                    $textoBotao = 'Voltar';
                }
            @endphp
            <a href="{{ $rotaVoltar }}" class="inline-flex justify-center rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-600">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> {{ $textoBotao }}
            </a>
            <button onclick="window.print()" class="inline-flex justify-center rounded-md bg-[#beffb4] px-3 py-2 text-sm font-semibold text-black shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-[#a0c520]">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Imprimir
            </button>
        </div>

    </div>
</div>

<script>lucide.createIcons();</script>

@endsection

@push('scripts')
<style>
@media print {
    @page {
        margin: 0.8cm; 
        size: A4;
    }

    body, html {
        background-color: white !important;
        background-image: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: 100% !important;
        color: #000 !important;
    }

    .site-header, .sidebar, footer, .print\:hidden {
        display: none !important;
    }
    
    main {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        background: white !important;
        border: none !important;
        box-shadow: none !important;
    }
    
    .shadow-2xl, .border-t-8, .rounded-lg {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
    }

    .max-w-4xl {
        max-width: 100% !important;
        padding: 0 !important;
    }
    
    .break-inside-avoid {
        page-break-inside: avoid;
    }
}
</style>
@endpush
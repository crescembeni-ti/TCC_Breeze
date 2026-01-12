@extends('layouts.dashboard')

@section('title', 'Ordem de Serviço')

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
                    {{-- Usando observações para lat_long como placeholder, se não houver campos dedicados --}}
                    {{-- ALTERAÇÃO AQUI: Dividir Latitude e Longitude em dois campos visuais --}}
              
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
                {{-- FIM DA ALTERAÇÃO --}}
                    </p>
                </div>
            </div>
        </div>

        {{-- IDENTIFICAÇÃO DAS ÁRVORES --}}
        <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2">
           <div class="col-span-2">
                <label class="font-bold block">Espécie(s):</label>
                <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50">
                    @if(is_array($os->especies))
                        {{-- Se for Array (novo formato), junta com vírgula --}}
                        {{ implode(', ', $os->especies) }}
                    @else
                        {{-- Se for Texto (formato antigo ou vazio), mostra direto --}}
                        {{ $os->especies ?? 'Não informado' }}
                    @endif
                </p>
            </div>
            <div>
                <label class="font-bold block">Quantidade:</label>
                {{-- DADO ESTÁTICO --}}
                <p class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50">{{ $os->quantidade ?? 'Não informado' }}</p>
            </div>
        </div>

        {{-- MOTIVO DA INTERVENÇÃO --}}
        <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded">
            <h4 class="font-bold underline mb-1">Motivo da Intervenção</h4>
            <div class="grid grid-cols-2 gap-y-1">
                @foreach ($todosMotivos as $value => $label)
                    @php
                        // Verifica se o valor está no array de motivos salvos na OS
                        $checked = in_array($value, $os->motivos ?? []);
                    @endphp
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
                    @php
                        $checked = in_array($value, $os->servicos ?? []);
                    @endphp
                    <div class="flex items-center gap-2 text-gray-700">
                        <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }}"></i>
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- EQUIPAMENTOS E MATERIAIS --}}
        <div class="mb-4 border-b border-gray-300 pb-2">
            <h4 class="font-bold underline mb-1">Equipamentos Necessários</h4>
            <div class="flex flex-wrap gap-4">
                @foreach ($todosEquipamentos as $value => $label)
                    @php
                        $checked = in_array($value, $os->equipamentos ?? []);
                    @endphp
                    <div class="flex items-center gap-1 text-gray-700">
                        <i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 text-{{ $checked ? '[#358054]' : 'gray-400' }}"></i>
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- RESPONSABILIDADES E PROCEDIMENTOS (ESTÁTICO) --}}
        <div class="mb-4 border-b-2 border-gray-400 pb-2 bg-gray-50 p-2 rounded">
            <h4 class="font-bold text-sm text-black mb-2 border-b border-gray-300">Responsabilidades e Procedimentos (Confirmação)</h4>
            <div class="flex flex-col gap-2 text-xs text-black">
                <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300">
                    <span class="mt-0.5 text-[#358054] font-extrabold text-sm">&#10003;</span>
                    <div class="flex-1 flex"><span class="font-bold w-32 shrink-0">Segurança:</span><span>Utilização obrigatória de EPIs</span></div>
                </div>
                <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300">
                    <span class="mt-0.5 text-[#358054] font-extrabold text-sm">&#10003;</span>
                    <div class="flex-1 flex"><span class="font-bold w-32 shrink-0">Sinalização:</span><span>Uso de cones e faixas de segurança</span></div>
                </div>
                <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300">
                    <span class="mt-0.5 text-[#358054] font-extrabold text-sm">&#10003;</span>
                    <div class="flex-1 flex"><span class="font-bold w-32 shrink-0">Descarte:</span><span>Destino adequado dos resíduos</span></div>
                </div>
                <div class="flex items-start gap-2 p-1 rounded border-b border-dotted border-gray-300">
                    <span class="mt-0.5 text-[#358054] font-extrabold text-sm">&#10003;</span>
                    <div class="flex-1 flex"><span class="font-bold w-32 shrink-0">Registro:</span><span>Fotos antes e depois</span></div>
                </div>
                <div class="flex items-start gap-2 p-1 rounded">
                    <span class="mt-0.5 text-[#358054] font-extrabold text-sm">&#10003;</span>
                    <div class="flex-1 flex"><span class="font-bold w-32 shrink-0">Comunicação:</span><span>Informar imprevistos</span></div>
                </div>
            </div>
        </div>

        {{-- DATAS E ASSINATURA --}}
                        <div class="grid grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="font-bold block text-xs uppercase">Data Vistoria:</label>
                                {{-- 
                                    max="{{ date('Y-m-d') }}" -> Impede selecionar datas futuras 
                                --}}
                                <input type="date" name="data_vistoria" 
                                       class="w-full border p-1 rounded focus:ring-[#358054] focus:border-[#358054]" 
                                       max="{{ date('Y-m-d') }}" 
                                       required>
                                <p class="text-[10px] text-gray-500 mt-1">* Não pode ser data futura</p>
                            </div>
                            <div>
                                <label class="font-bold block text-xs uppercase">Previsão Execução:</label>
                                {{-- 
                                    min="{{ date('Y-m-d') }}" -> Impede selecionar datas passadas 
                                --}}
                                <input type="date" name="data_execucao" 
                                       class="w-full border p-1 rounded focus:ring-[#358054] focus:border-[#358054]"
                                       min="{{ date('Y-m-d') }}">
                                <p class="text-[10px] text-gray-500 mt-1">* Não pode ser data passada</p>
                            </div>
                        </div>
            
            @if ($os->observacoes)
            <div class="mt-4 col-span-2">
                <label class="font-bold block text-xs uppercase">Observações do Analista:</label>
                <p class="bg-gray-100 p-2 rounded whitespace-pre-wrap">{{ $os->observacoes }}</p>
            </div>
            @endif
        </div>

        {{-- BOTÕES (SOMENTE VOLTAR/IMPRIMIR) --}}
        <div class="mt-6 flex flex-row-reverse gap-2 col-span-2 print:hidden">
            @php
        // Define a rota de volta baseada no guard logado
        if (auth()->guard('analyst')->check()) {
            $rotaVoltar = route('analyst.os.enviadas');
            $textoBotao = 'Voltar para Ordens Enviadas';
        } else {
            $rotaVoltar = route('admin.os.index');
            $textoBotao = 'Voltar para Gestão de OS';
        }
    @endphp
    <a href="{{ $rotaVoltar }}" class="inline-flex w-full justify-center rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-600 sm:w-auto">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> {{ $textoBotao }}
    </a>
           
        
            <button onclick="window.print()" class="inline-flex w-full justify-center rounded-md bg-[#beffb4] px-3 py-2 text-sm font-semibold text-black shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-[#a0c520] sm:w-auto print:hidden">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Imprimir OS
            </button>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>

@endsection

@push('scripts')
<style>
@media print {
    /* Esconde elementos não essenciais na impressão */
    .site-header, .sidebar, footer {
        display: none !important;
    }
    /* Otimiza a visualização */
    main {
        padding: 0 !important;
        margin: 0 !important;
        width: 100%;
        max-width: none !important;
    }
    .shadow-2xl, .border-t-8 {
        box-shadow: none !important;
        border: none !important;
    }
    .print\:hidden {
        display: none !important;
    }
}
</style>
@endpush
@extends('layouts.dashboard')

@section('title', 'Vistorias Pendentes')

@section('content')

{{-- 
    ESTADO GLOBAL DO ALPINE 
    open: controla se o modal aparece
    item: guarda os dados da vistoria selecionada para preencher o formulário
--}}
<div x-data="{ open: false, item: {} }">

    {{-- CABEÇALHO --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">
                Vistorias Pendentes
            </h2>
            <p class="text-gray-600 mt-1">
                Solicitações que aguardam emissão de Ordem de Serviço.
            </p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('analyst.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-[#358054] flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Voltar ao Painel
            </a>
        </div>
    </header>
    
    {{-- TABELA DE LISTAGEM --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg relative z-0">
        <div class="p-6">
            
            @if($vistorias->isEmpty())
                <div class="text-center py-12 flex flex-col items-center justify-center">
                    <div class="bg-green-50 p-4 rounded-full mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-[#358054]">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tudo em dia!</h3>
                    <p class="text-gray-500">Não há vistorias pendentes no momento.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($vistorias as $vistoria)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $vistoria->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $vistoria->bairro }}</div>
                                    <div class="text-sm text-gray-500">{{ $vistoria->rua }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $vistoria->nome_solicitante }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $vistoria->status->name ?? 'Pendente' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{-- BOTÃO QUE ABRE O MODAL --}}
                                    <button 
                                        @click="open = true; item = {{ $vistoria->toJson() }}"
                                        class="text-[#358054] hover:text-green-900 font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50 transition cursor-pointer">
                                        Analisar / Gerar OS
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- 
        =========================================================
        MODAL - ORDEM DE SERVIÇO (Replicando a Imagem)
        =========================================================
    --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;">
        
        {{-- Fundo escuro --}}
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>

        {{-- Container do Modal --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
                
                {{-- Conteúdo do Formulário --}}
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    
                    {{-- HEADER DA OS --}}
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <div class="flex items-center gap-2">
                             {{-- Placeholder para Logo --}}
                             <img src="{{ asset('images/Brasao_Verde.png') }}" class="h-16 w-auto" alt="Logo">
                             <div class="text-xs text-gray-600 leading-tight font-bold uppercase">
                                Estado do Rio de Janeiro<br>
                                Município de Paracambi<br>
                                Secretaria Municipal de Meio Ambiente
                             </div>
                        </div>
                        <div class="text-right">
                             <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">Ordem de Serviço</h3>
                             <p class="text-sm font-bold mt-1">Poda e Remoção de Árvores</p>
                        </div>
                    </div>

                    {{-- FORMULÁRIO --}}
                    <form action="#" method="POST" class="text-sm"> @csrf
                        {{-- DADOS DA SOLICITAÇÃO --}}
                        <div class="grid grid-cols-2 gap-4 mb-2 border-b border-gray-300 pb-2">
                            <div>
                                <label class="font-bold block text-gray-700">Nº Solicitação:</label>
                                <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1" :value="item.id" readonly>
                            </div>
                            <div>
                                <label class="font-bold block text-gray-700">Data:</label>
                                {{-- Formatação de data simples via JS --}}
                                <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1" :value="new Date(item.created_at).toLocaleDateString('pt-BR')" readonly>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DA ÁREA --}}
                        <div class="mb-2 border-b border-gray-300 pb-2">
                            <h4 class="font-bold underline mb-1">Identificação da Área</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <span class="text-gray-600">Endereço:</span>
                                    <input type="text" class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50" :value="(item.rua || '') + ', ' + (item.numero || '') + ' - ' + (item.bairro || '')" readonly>
                                </div>
                                <div>
                                    <span class="text-gray-600">Coordenadas Geográficas:</span>
                                    <input type="text" class="w-full border-0 border-b border-gray-400 p-1" placeholder="Latitude / Longitude (se houver)">
                                    <input type="text" class="w-full border-0 border-b border-gray-400 p-1" placeholder="Longitude (se houver)">
                                </div>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DAS ÁRVORES --}}
                        <div class="grid grid-cols-3 gap-4 mb-2 border-b border-gray-300 pb-2">
                            <div class="col-span-2">
                                <label class="font-bold block">Espécie(s):</label>
                                <input type="text" class="w-full border-0 border-b border-gray-400 p-1" placeholder="Ex: Ipê, Mangueira...">
                            </div>
                            <div>
                                <label class="font-bold block">Quantidade:</label>
                                <input type="number" class="w-full border-0 border-b border-gray-400 p-1" value="1">
                            </div>
                        </div>

                        {{-- MOTIVO DA INTERVENÇÃO (Checkboxes) --}}
                        <div class="mb-2 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded">
                            <h4 class="font-bold underline mb-1">Motivo da Intervenção</h4>
                            <div class="grid grid-cols-2 gap-y-1">
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Risco de Queda"> Risco de queda</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Conflito rede eletrica"> Conflito com rede elétrica</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Danos infraestrutura"> Danos à infraestrutura</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Outras"> Outras razões</label>
                            </div>
                        </div>

                        {{-- SERVIÇOS A SEREM EXECUTADOS --}}
                        <div class="mb-2 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded">
                            <h4 class="font-bold underline mb-1">Serviços a serem executados</h4>
                            <div class="grid grid-cols-2 gap-y-1">
                                <label class="flex items-center gap-2"><input type="checkbox" name="servico[]" value="Levantamento copa"> Poda de levantamento de copa</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="servico[]" value="Desobstrucao"> Poda de desobstrução de rede</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="servico[]" value="Limpeza"> Poda de limpeza</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="servico[]" value="Adequacao"> Poda de adequação</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="servico[]" value="Remocao Total"> Remoção total da árvore</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="servico[]" value="Outras"> Outras intervenções</label>
                            </div>
                        </div>

                        {{-- EQUIPAMENTOS E MATERIAIS --}}
                        <div class="mb-4 border-b border-gray-300 pb-2">
                            <h4 class="font-bold underline mb-1">Equipamentos Necessários</h4>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-1"><input type="checkbox"> Motosserra</label>
                                <label class="flex items-center gap-1"><input type="checkbox"> Motopoda</label>
                                <label class="flex items-center gap-1"><input type="checkbox" checked> EPIs</label>
                                <label class="flex items-center gap-1"><input type="checkbox"> Cordas</label>
                                <label class="flex items-center gap-1"><input type="checkbox"> Cones</label>
                                <label class="flex items-center gap-1"><input type="checkbox"> Caminhão</label>
                            </div>
                        </div>
                       {{--
                        =====================================================
                            6. RESPONSABILIDADES E PROCEDIMENTOS (NOVO)
                            =====================================================
                        --}}
                       <div class="mb-2 border-b-2 border-gray-400 pb-2 bg-gray-50 p-2 rounded">
                            {{-- ALTERADO AQUI PARA text-black --}}
                            <h4 class="font-bold text-sm text-black mb-2 border-b border-gray-300">Responsabilidades e Procedimentos</h4>
                            
                            <div class="space-y-1 text-xs text-gray-800">
                                <div class="flex border-b border-dotted border-gray-300 pb-1">
                                    <span class="font-bold w-32 shrink-0">Segurança:</span>
                                    <span>Utilização obrigatória de EPIs</span>
                                </div>
                                <div class="flex border-b border-dotted border-gray-300 pb-1">
                                    <span class="font-bold w-32 shrink-0">Sinalização:</span>
                                    <span>Uso de cones e faixas de segurança no local</span>
                                </div>
                                <div class="flex border-b border-dotted border-gray-300 pb-1">
                                    <span class="font-bold w-32 shrink-0">Descarte:</span>
                                    <span>Destino adequado dos resíduos (Triturador)</span>
                                </div>
                                <div class="flex border-b border-dotted border-gray-300 pb-1">
                                    <span class="font-bold w-32 shrink-0">Registro Fotográfico:</span>
                                    <span>Fotos antes e depois do serviço para documentação</span>
                                </div>
                                <div class="flex">
                                    <span class="font-bold w-32 shrink-0">Comunicação:</span>
                                    <span>Informar ao responsável qualquer imprevisto antes de prosseguir com a execução</span>
                                </div>
                            </div>
                        </div>

                        {{-- DATAS E ASSINATURA --}}
                        <div class="grid grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="font-bold block text-xs uppercase">Data Vistoria:</label>
                                <input type="date" class="w-full border p-1 rounded">
                            </div>
                            <div>
                                <label class="font-bold block text-xs uppercase">Previsão Execução:</label>
                                <input type="date" class="w-full border p-1 rounded">
                            </div>
                        </div>

                        {{-- BOTÕES DE AÇÃO --}}
                        <div class="mt-6 flex flex-row-reverse gap-2">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-[#358054] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 sm:w-auto">
                                Confirmar e Gerar OS
                            </button>
                            <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" @click="open = false">
                                Cancelar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
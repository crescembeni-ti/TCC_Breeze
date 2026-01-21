@extends('layouts.dashboard')

@section('title', 'Ordens de Serviço Enviadas')

@section('content')

{{-- ESTADO GLOBAL DO ALPINE --}}
<div x-data="{ open: false, item: { contact: {}, motivos: [], servicos: [], equipamentos: [] } }">

    {{-- CABEÇALHO DA LISTAGEM --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">Ordens de Serviço Enviadas</h2>
            <p class="text-gray-600 mt-1">Histórico de todas as OS que você processou e enviou.</p>
        </div>
        <div>
            <a href="{{ route('analyst.vistorias.pendentes') }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-bold py-2 px-4 rounded shadow transition">
                ← Voltar para Pendentes
            </a>
        </div>
    </header>

    {{-- LISTAGEM (TABELA) --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg relative z-0">
        <div class="p-6">
            @if($ordensEnviadas->isEmpty())
                <div class="text-center py-12">
                    <div class="bg-green-50 p-4 rounded-full mb-3 inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Nenhuma ordem enviada ainda</h3>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Vistoria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ordensEnviadas as $os)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">#{{ $os->contact->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $os->contact->bairro }}</div>
                                    <div class="text-sm text-gray-500">{{ $os->contact->rua }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $os->contact->nome_solicitante }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $os->data_vistoria ? \Carbon\Carbon::parse($os->data_vistoria)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Vistoriado
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        @click="open = true; item = {{ $os->toJson() }}"
                                        class="text-[#358054] hover:text-green-900 font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50 transition cursor-pointer">
                                        Ver Detalhes
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

    {{-- MODAL DE VISUALIZAÇÃO (LAYOUT IDÊNTICO À SHOW BLADE COM MARCA D'ÁGUA) --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
                
                {{-- MARCA D'ÁGUA ADICIONADA --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
                    <img src="{{ asset('images/logo.png') }}" class="w-2/3 object-contain opacity-15">
                </div>

                {{-- CONTEÚDO (Sem bg-white para ver a marca d'água) --}}
                <div class="p-8 relative z-10 text-left">
                    
                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-6 border-b pb-4 print:mb-2 print:pb-2">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto object-contain" alt="Logo">
                            <div class="text-xs text-gray-600 leading-tight font-bold uppercase text-left">
                                ESTADO DO RIO DE JANEIRO<br>
                                MUNICÍPIO DE PARACAMBI<br>
                                SECRETARIA MUNICIPAL DE MEIO AMBIENTE<br>
                                SUPER INTENDÊNCIA DE ÁREAS VERDES<br>
                                DIRETORIA DE ARBORIZAÇÃO URBANA
                            </div>
                        </div>
                        <div class="text-right">
                            <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">ORDEM DE SERVIÇO</h3>
                            <p class="text-sm font-bold mt-1 text-black">Poda e Remoção de Árvores</p>
                        </div>
                    </div>

                    {{-- CORPO DA OS (TUDO READONLY) --}}
                    <div class="text-sm text-left">

                        {{-- DADOS DO PROTOCOLO --}}
                        <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2">
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Nº Solicitação:</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.contact ? item.contact.id : ''"></p>
                            </div>
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Data de Envio:</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.created_at ? new Date(item.created_at).toLocaleDateString('pt-BR') : ''"></p>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DA ÁREA --}}
                        <div class="mb-4 border-b border-gray-300 pb-2">
                            <h4 class="font-bold mb-1 uppercase text-black">Identificação da Área</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <div class="mb-2">
                                    <span class="text-gray-600 font-semibold">Endereço:</span>
                                    <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black"
                                       x-text="item.contact ? ((item.contact.rua || '') + ', ' + (item.contact.numero || '') + ' - ' + (item.contact.bairro || '')) : ''"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="font-bold mb-1 uppercase text-black">Latitude:</label>
                                        <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.latitude || 'N/R'"></p>
                                    </div>
                                    <div>
                                        <label class="font-bold mb-1 uppercase text-black">Longitude:</label>
                                        <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.longitude || 'N/R'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ESPÉCIES --}}
                        <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2">
                            <div class="col-span-2">
                                <label class="font-bold mb-1 uppercase text-black">Espécie(s):</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.especies || 'N/A'"></p>
                            </div>
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Quantidade:</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.quantidade || '1'"></p>
                            </div>
                        </div>

                        {{-- MOTIVO (Checkboxes Desabilitados) --}}
                        <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                            <h4 class="font-bold mb-1 uppercase text-black">Motivo da Intervenção</h4>
                            <div class="grid grid-cols-2 gap-y-1">
                                <template x-for="opcao in ['Risco de Queda', 'Conflito rede eletrica', 'Danos infraestrutura', 'Outras']">
                                    <label class="flex items-center gap-2 text-gray-700 opacity-80">
                                        <input type="checkbox" disabled class="rounded border-gray-400 text-[#358054]" 
                                               :checked="item.motivos && item.motivos.includes(opcao)"> 
                                        <span class="text-[14px]" x-text="opcao"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- SERVIÇOS (Checkboxes Desabilitados) --}}
                        <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                            <h4 class="font-bold mb-1 uppercase text-black">Serviços a Executar</h4>
                            <div class="grid grid-cols-2 gap-y-1">
                                <template x-for="opcao in ['Levantamento copa', 'Desobstrucao', 'Limpeza', 'Adequacao', 'Remocao Total', 'Outras']">
                                    <label class="flex items-center gap-2 text-gray-700 opacity-80">
                                        <input type="checkbox" disabled class="rounded border-gray-400 text-[#358054]" 
                                               :checked="item.servicos && item.servicos.includes(opcao)"> 
                                        <span class="text-[14px]" x-text="opcao"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- EQUIPAMENTOS (Checkboxes Desabilitados) --}}
                        <div class="mb-4 border-b border-gray-300 pb-2">
                            <h4 class="font-bold mb-1 uppercase text-black">Equipamentos Necessários</h4>
                            <div class="flex flex-wrap gap-4">
                                <template x-for="opcao in ['Motosserra', 'Motopoda', 'EPIs', 'Cordas', 'Cones', 'Caminhão']">
                                    <label class="flex items-center gap-1 text-gray-700 opacity-80">
                                        <input type="checkbox" disabled class="rounded border-gray-400 text-[#358054]" 
                                               :checked="item.equipamentos && item.equipamentos.includes(opcao)"> 
                                        <span class="text-[14px]" x-text="opcao"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- DATAS E OBSERVAÇÕES --}}
                        <div class="grid grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Data Vistoria:</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" 
                                   x-text="item.data_vistoria ? new Date(item.data_vistoria).toLocaleDateString('pt-BR') : 'N/A'"></p>
                            </div>
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Previsão Execução:</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" 
                                   x-text="item.data_execucao ? new Date(item.data_execucao).toLocaleDateString('pt-BR') : 'A definir'"></p>
                            </div>
                            
                            {{-- OBSERVAÇÕES DO ADMIN --}}
                            <div class="col-span-2">
                                <label class="font-bold mb-1 uppercase text-black">OBSERVAÇÕES:</label>
                                <div class="w-full border p-2 bg-gray-50 rounded italic text-gray-800 text-sm min-h-[40px]" x-text="item.observacoes || 'Sem observações.'"></div>
                            </div>
                        </div>

                        {{-- RODAPÉ (ASSINATURAS) --}}
                        <div class="grid grid-cols-2 gap-8 mt-12 pt-4 print:mt-6 text-center">
                            <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Responsável Técnico</p></div>
                            <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Recebido por</p></div>
                        </div>

                    </div>

                    {{-- BOTÕES --}}
                    <div class="mt-8 flex justify-end gap-3 print:hidden">
                        <button type="button" @click="open = false" class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded text-xs font-bold hover:bg-gray-200 transition">
                            Fechar
                        </button>
                        <button type="button" onclick="window.print()" class="bg-[#358054] text-white px-6 py-2 rounded text-xs font-bold shadow-lg hover:bg-green-700 flex items-center gap-2">
                            <i data-lucide="printer" class="w-4 h-4"></i> Imprimir OS
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => { 
        lucide.createIcons(); 
    });
</script>

@endsection
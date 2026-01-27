@extends('layouts.dashboard')

@section('title', 'Vistorias Pendentes')

@section('content')

{{-- ESTADO GLOBAL DO ALPINE --}}
<div x-data="{ open: false, item: { contact: {} } }">

    {{-- CABEÇALHO DA PÁGINA --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">Vistorias Pendentes</h2>
            <p class="text-gray-600 mt-1">Solicitações encaminhadas para sua análise técnica.</p>
        </div>
    </header>
    
    {{-- TABELA DE LISTAGEM --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg relative z-0">
        <div class="p-6">
            @if($vistorias->isEmpty())
                <div class="text-center py-12 flex flex-col items-center justify-center">
                    <div class="bg-green-50 p-4 rounded-full mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tudo em dia!</h3>
                    <p class="text-gray-500">Não há vistorias pendentes no momento.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($vistorias as $vistoria)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">#{{ $vistoria->contact->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $vistoria->contact->bairro }}</div>
                                    <div class="text-sm text-gray-500">{{ $vistoria->contact->rua }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $vistoria->contact->nome_solicitante }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Aguardando Vistoria
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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

    {{-- MODAL (LAYOUT DA OS COM MARCA D'ÁGUA) --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
                
                {{-- MARCA D'ÁGUA ADICIONADA --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
                    <img src="{{ asset('images/logo.png') }}" class="w-2/3 object-contain opacity-15">
                </div>

                {{-- CONTEÚDO (Sem bg-white para ver a marca d'água no fundo branco do container pai) --}}
                <div class="p-8 relative z-10 text-left">
                    
                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-6 border-b pb-4 print:mb-2 print:pb-2">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto object-contain print:h-12" alt="Logo">
                            <div class="text-xs text-gray-600 leading-tight font-bold uppercase text-left">
                                ESTADO DO RIO DE JANEIRO<br>
                                MUNICÍPIO DE PARACAMBI<br>
                                SECRETARIA MUNICIPAL DE MEIO AMBIENTE<br>
                                SUPER INTENDÊNCIA DE ÁREAS VERDES<br>
                                DIRETORIA DE ARBORIZAÇÃO URBANA
                            </div>
                        </div>
                        <div class="text-right">
                            <h3 class="font-bold mb-1 uppercase text-black uppercase border-b-2 border-black">ORDEM DE SERVIÇO</h3>
                            <p class="text-sm font-bold mt-1 text-black">Poda e Remoção de Árvores</p>
                        </div>
                    </div>

                    {{-- FORMULÁRIO --}}
                    <form action="{{ route('analyst.os.store') }}" method="POST" class="text-sm"> 
                        @csrf
                        <input type="hidden" name="contact_id" :value="item.contact ? item.contact.id : ''">

                        <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2">
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Nº Solicitação:</label>
                                <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50/80 p-1 font-medium text-gray-900" :value="item.contact ? item.contact.id : ''" readonly>
                            </div>
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Data de Envio:</label>
                                <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50/80 p-1 font-medium text-gray-900" :value="item.created_at ? new Date(item.created_at).toLocaleDateString('pt-BR') : ''" readonly>
                            </div>
                        </div>

                        <div class="mb-4 border-b border-gray-300 pb-2">
                            <h4 class="font-bold mb-1 uppercase text-black">Identificação da Área</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <div class="mb-2">
                                    <span class="text-gray-600 font-semibold">Endereço:</span>
                                    <input type="text" class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50/50 font-medium text-gray-900" 
                                           :value="item.contact ? ((item.contact.rua || '') + ', ' + (item.contact.numero || '') + ' - ' + (item.contact.bairro || '')) : ''" readonly>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="font-bold mb-1 uppercase text-black">Latitude:</label>
                                        <input type="text" name="latitude" class="w-full border-0 border-b border-gray-400 p-1 focus:ring-0 text-black" :value="item.latitude || ''">
                                    </div>
                                    <div>
                                        <label class="font-bold mb-1 uppercase text-black">Longitude:</label>
                                        <input type="text" name="longitude" class="w-full border-0 border-b border-gray-400 p-1 focus:ring-0 text-black" :value="item.longitude || ''">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2">
                            <div class="col-span-2">
                                <label class="font-bold mb-1 uppercase text-black">Espécie(s):</label>
                                <input type="text" name="especies" class="w-full border-0 border-b border-gray-400 bg-gray-50/50 p-1 focus:ring-0 text-black" placeholder="Preencher..." :value="item.especies || ''">
                            </div>
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Quantidade:</label>
                                <input type="number" name="quantidade" class="w-full border-0 border-b border-gray-400 bg-gray-50/50 p-1 focus:ring-0 text-black font-semibold" value="1" :value="item.quantidade || 1">
                            </div>
                        </div>

                        <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                            <h4 class="font-bold mb-1 uppercase text-black">Motivo da Intervenção</h4>
                            <div class="grid grid-cols-2 gap-y-1">
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="motivo[]" value="Risco de Queda" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Risco de queda</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="motivo[]" value="Conflito rede eletrica" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Conflito com rede elétrica</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="motivo[]" value="Danos infraestrutura" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Danos à infraestrutura</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="motivo[]" value="Outras" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Outras razões</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                            <h4 class="font-bold mb-1 uppercase text-black">Serviços a Executar</h4>
                            <div class="grid grid-cols-2 gap-y-1">
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="servico[]" value="Levantamento copa" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Poda de levantamento de copa</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="servico[]" value="Desobstrucao" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Poda de desobstrução de rede</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="servico[]" value="Limpeza" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Poda de limpeza</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="servico[]" value="Adequacao" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Poda de adequação</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="servico[]" value="Remocao Total" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Remoção total da árvore</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="servico[]" value="Outras" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Outras intervenções</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4 border-b border-gray-300 pb-2">
                            <h4 class="font-bold mb-1 uppercase text-black">Equipamentos Necessários</h4>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-1 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="equip[]" value="Motosserra" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Motosserra</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="equip[]" value="Motopoda" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Motopoda</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="equip[]" value="EPIs" checked class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">EPIs</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="equip[]" value="Cordas" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Cordas</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="equip[]" value="Cones" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Cones</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer text-gray-700">
                                    <input type="checkbox" name="equip[]" value="Caminhão" class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]"> 
                                    <span class="text-[14px]">Caminhão</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">DATA VISTORIA:</label>
                                <input type="date" name="data_vistoria" 
                                       class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" 
                                       max="{{ date('Y-m-d') }}" required>
                                <p class="text-[10px] text-gray-500 mt-1">* Não pode ser data futura</p>
                            </div>
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">PREVISÃO EXECUÇÃO:</label>
                                <input type="date" name="data_execucao" 
                                       class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black"
                                       min="{{ date('Y-m-d') }}">
                                <p class="text-[10px] text-gray-500 mt-1">* Se souber, defina a data.</p>
                            </div>
                            
                            <div class="col-span-2">
                                <label class="font-bold mb-1 uppercase text-black">OBSERVAÇÕES DO ADMIN:</label>
                                <div class="w-full border p-2 bg-yellow-50 rounded italic text-gray-800 text-sm min-h-[40px]" x-text="item.observacoes || 'Sem observações.'"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8 mt-12 pt-4 print:mt-6 text-center">
                            <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Responsável Técnico</p></div>
                            <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Recebido por</p></div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3">
                            <button type="button" @click="open = false" class="inline-flex items-center justify-center rounded-md bg-gray-100 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200 transition">
                                Cancelar
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#358054] px-6 py-2 text-sm font-bold text-white shadow-lg hover:bg-[#2a6643] transition">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Gerar Ordem de Serviço
                            </button>
                        </div>
                    </form>
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
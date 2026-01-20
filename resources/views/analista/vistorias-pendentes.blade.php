@extends('layouts.dashboard')

@section('title', 'Minhas Tarefas')

@section('content')

{{-- ESTADO GLOBAL DO ALPINE --}}
<div x-data="{ open: false, item: { contact: {} } }">

    {{-- CABEÇALHO --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">Minhas Tarefas (Equipe Técnica)</h2>
            <p class="text-gray-600 mt-1">Ordens de serviço encaminhadas para sua equipe.</p>
        </div>
    </header>

    {{-- TABELA DE LISTAGEM --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg relative z-0">
        <div class="p-6">
            @if($ordensDeServico->isEmpty())
                <div class="text-center py-12 flex flex-col items-center justify-center">
                    <div class="bg-green-50 p-4 rounded-full mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Sem tarefas no momento!</h3>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">OS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Local</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ordensDeServico as $os)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $os->contact->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $os->contact->rua }} - {{ $os->contact->bairro }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $os->contact->status->name == 'Em Execução' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $os->contact->status->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium flex gap-2">
                                    <button @click="open = true; item = {{ json_encode($os->load('contact')) }}" 
                                        class="text-[#358054] hover:text-green-900 font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50 transition cursor-pointer">
                                        Visualizar OS
                                    </button>
                                    {{-- Botões de Confirmar/Falha/Concluir aqui... --}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @extends('layouts.dashboard')

@section('title', 'Vistorias Pendentes')

@section('content')

{{-- ESTADO GLOBAL DO ALPINE --}}
<div x-data="{ open: false, item: { contact: {} } }">

    {{-- CABEÇALHO --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">Vistorias Pendentes</h2>
            <p class="text-gray-600 mt-1">Solicitações que aguardam emissão de Ordem de Serviço.</p>
        </div>
    </header>
    
    {{-- TABELA DE LISTAGEM --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg relative z-0">
        <div class="p-6">
            @if($vistorias->isEmpty())
                <div class="text-center py-12 flex flex-col items-center justify-center">
                    <div class="bg-green-50 p-4 rounded-full mb-3">
                        <i data-lucide="check-circle" class="w-8 h-8 text-[#358054]"></i>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($vistorias as $vistoria)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $vistoria->contact->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $vistoria->contact->bairro }}</div>
                                    <div class="text-sm text-gray-500">{{ $vistoria->contact->rua }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $vistoria->contact->nome_solicitante }}</td>
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

    {{-- MODAL COM ESTÉTICA DE OS OFICIAL --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            {{-- CONTAINER DO "PAPEL" (Borda verde e Sombra forte) --}}
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
                
                {{-- MARCA D'ÁGUA --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
                    <img src="{{ asset('images/logo.png') }}" class="w-1/2 object-contain opacity-10">
                </div>

                <div class="bg-white p-8 relative z-10">
                    {{-- HEADER IDÊNTICO AO DOCUMENTO --}}
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto" alt="Logo">
                            <div class="text-[10px] text-gray-600 font-bold uppercase leading-tight">
                                ESTADO DO RIO DE JANEIRO<br>
                                MUNICÍPIO DE PARACAMBI<br>
                                SECRETARIA MUNICIPAL DE MEIO AMBIENTE<br>
                                DIRETORIA DE ARBORIZAÇÃO URBANA
                            </div>
                        </div>
                        <div class="text-right">
                            <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">ORDEM DE SERVIÇO</h3>
                            <p class="text-xs font-bold mt-1 text-[#358054]">Geração de Documento</p>
                        </div>
                    </div>

                    {{-- FORMULÁRIO --}}
                    <form action="{{ route('analyst.os.store') }}" method="POST" class="text-sm"> 
                        @csrf
                        <input type="hidden" name="contact_id" :value="item.contact ? item.contact.id : ''">

                        <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2">
                            <div>
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Nº Solicitação:</label>
                                <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50/50 p-1 font-bold" :value="item.contact ? '#' + item.contact.id : ''" readonly>
                            </div>
                            <div>
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Data da Vistoria:</label>
                                <input type="date" name="data_vistoria" required max="{{ date('Y-m-d') }}"
                                       class="w-full border-0 border-b border-gray-400 bg-white p-1 focus:ring-0 focus:border-[#358054]">
                            </div>
                        </div>

                        <div class="mb-4 border-b border-gray-300 pb-2">
                            <h4 class="font-bold underline mb-2 uppercase text-xs">Identificação da Área</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <span class="text-gray-500 font-bold uppercase text-[10px]">Endereço:</span>
                                    <p class="p-1 bg-gray-50 border-b border-gray-200" x-text="item.contact ? (item.contact.rua + ', ' + item.contact.numero + ' - ' + item.contact.bairro) : ''"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-gray-500 font-bold uppercase text-[10px]">Latitude:</span>
                                        <input type="text" name="latitude" class="w-full border-0 border-b border-gray-400 p-1 focus:ring-0" placeholder="0.0000" :value="item.latitude || ''">
                                    </div>
                                    <div>
                                        <span class="text-gray-500 font-bold uppercase text-[10px]">Longitude:</span>
                                        <input type="text" name="longitude" class="w-full border-0 border-b border-gray-400 p-1 focus:ring-0" placeholder="0.0000" :value="item.longitude || ''">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2">
                            <div class="col-span-2">
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Espécie(s):</label>
                                <input type="text" name="especies" class="w-full border-0 border-b border-gray-400 p-1 focus:ring-0" placeholder="Informe a espécie..." :value="item.especies || ''">
                            </div>
                            <div>
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Quantidade:</label>
                                <input type="number" name="quantidade" class="w-full border-0 border-b border-gray-400 p-1 focus:ring-0 text-center" value="1">
                            </div>
                        </div>

                        {{-- SEÇÕES DE SELEÇÃO (ESTILO FORMULÁRIO DE PAPEL) --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-gray-50/80 p-2 rounded border border-gray-200">
                                <h4 class="font-bold underline mb-2 uppercase text-[10px]">Motivo da Intervenção</h4>
                                <div class="space-y-1 text-[11px]">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Risco de Queda" class="rounded text-[#358054]"> Risco de queda</label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Conflito rede eletrica" class="rounded text-[#358054]"> Conflito rede elétrica</label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Danos infraestrutura" class="rounded text-[#358054]"> Danos infraestrutura</label>
                                </div>
                            </div>
                            <div class="bg-gray-50/80 p-2 rounded border border-gray-200">
                                <h4 class="font-bold underline mb-2 uppercase text-[10px]">Previsão de Execução</h4>
                                <input type="date" name="data_execucao" min="{{ date('Y-m-d') }}"
                                       class="w-full border-0 border-b border-gray-400 bg-transparent p-1 focus:ring-0">
                                <p class="text-[9px] text-gray-500 mt-1 italic">* Data sugerida para a equipe</p>
                            </div>
                        </div>

                        {{-- BOTÕES DE AÇÃO DO MODAL --}}
                        <div class="mt-8 flex justify-end gap-3 print:hidden">
                            <button type="button" @click="open = false" class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded text-xs font-bold hover:bg-gray-200">
                                Cancelar
                            </button>
                            <button type="submit" class="bg-[#358054] text-white px-6 py-2 rounded text-xs font-bold shadow-lg hover:bg-green-700 transition flex items-center gap-2">
                                <i data-lucide="file-plus-2" class="w-4 h-4"></i> Finalizar e Gerar OS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
</script>

@endsection
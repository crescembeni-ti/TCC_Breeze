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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($vistorias as $vistoria)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $vistoria->contact->id }}</td>
                                <td class="px-6 py-4">
                                    {{-- CORREÇÃO: Acessando via ->contact --}}
                                    <div class="text-sm font-bold text-gray-900">{{ $vistoria->contact->bairro }}</div>
                                    <div class="text-sm text-gray-500">{{ $vistoria->contact->rua }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $vistoria->contact->nome_solicitante }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $vistoria->contact->status->name ?? 'Pendente' }}
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

    {{-- MODAL --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    
                    {{-- HEADER DA OS --}}
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <div class="flex items-center gap-2">
                             <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto" alt="Logo">
                             <div class="text-xs text-gray-600 leading-tight font-bold uppercase">
                                Estado do Rio de Janeiro<br>
                                Município de Paracambi<br>
                                Secretaria Municipal de Meio Ambiente<br>
                                SUPER INTENDÊNCIA DE ÁREAS VERDES<br>
                                DIRETORIA DE ARBORIZAÇÃO URBANA
                             </div>
                        </div>
                        <div class="text-right">
                             <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">Ordem de Serviço</h3>
                             <p class="text-sm font-bold mt-1">Poda e Remoção de Árvores</p>
                        </div>
                    </div>

                    {{-- FORMULÁRIO --}}
                    <form action="{{ route('analyst.os.store') }}" method="POST" class="text-sm"> 
                        @csrf
                        {{-- O ID aqui refere-se ao CONTACT ID que está dentro da OS --}}
                        <input type="hidden" name="contact_id" :value="item.contact ? item.contact.id : ''">

                        <div class="grid grid-cols-2 gap-4 mb-2 border-b border-gray-300 pb-2">
                            <div>
                                <label class="font-bold block text-gray-700">Nº Solicitação:</label>
                                {{-- CORREÇÃO NO JS: item.contact.id --}}
                                <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1" :value="item.contact ? item.contact.id : ''" readonly>
                            </div>
                            <div>
                                <label class="font-bold block text-gray-700">Data:</label>
                                <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1" :value="item.created_at ? new Date(item.created_at).toLocaleDateString('pt-BR') : ''" readonly>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DA ÁREA --}}
                        <div class="mb-2 border-b border-gray-300 pb-2">
                            <h4 class="font-bold underline mb-1">Identificação da Área</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <span class="text-gray-600">Endereço:</span>
                                    {{-- CORREÇÃO NO JS: Acessando item.contact.rua, item.contact.numero, etc --}}
                                    <input type="text" class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50" 
                                        :value="item.contact ? ((item.contact.rua || '') + ', ' + (item.contact.numero || '') + ' - ' + (item.contact.bairro || '')) : ''" readonly>
                                </div>
                                <div>
                                    <span class="text-gray-600">Coordenadas Geográficas:</span>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="latitude" class="w-full border-0 border-b border-gray-400 p-1" placeholder="Latitude" :value="item.latitude || ''">
                                        <input type="text" name="longitude" class="w-full border-0 border-b border-gray-400 p-1" placeholder="Longitude" :value="item.longitude || ''">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DAS ÁRVORES --}}
                        <div class="grid grid-cols-3 gap-4 mb-2 border-b border-gray-300 pb-2">
                            <div class="col-span-2">
                                <label class="font-bold block">Espécie(s):</label>
                                <input type="text" name="especies" class="w-full border-0 border-b border-gray-400 p-1" placeholder="Ex: Ipê, Mangueira..." :value="item.especies || ''">
                            </div>
                            <div>
                                <label class="font-bold block">Quantidade:</label>
                                <input type="number" name="quantidade" class="w-full border-0 border-b border-gray-400 p-1" value="1" :value="item.quantidade || 1">
                            </div>
                        </div>

                        {{-- RESTO DO FORMULÁRIO (Checkboxes e datas) - Mantenha igual --}}
                        <div class="mb-2 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded">
                            <h4 class="font-bold underline mb-1">Motivo da Intervenção</h4>
                            <div class="grid grid-cols-2 gap-y-1">
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Risco de Queda"> Risco de queda</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Conflito rede eletrica"> Conflito com rede elétrica</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Danos infraestrutura"> Danos à infraestrutura</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="motivo[]" value="Outras"> Outras razões</label>
                            </div>
                        </div>

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

                        <div class="mb-4 border-b border-gray-300 pb-2">
                            <h4 class="font-bold underline mb-1">Equipamentos Necessários</h4>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-1"><input type="checkbox" name="equip[]" value="Motosserra"> Motosserra</label>
                                <label class="flex items-center gap-1"><input type="checkbox" name="equip[]" value="Motopoda"> Motopoda</label>
                                <label class="flex items-center gap-1"><input type="checkbox" name="equip[]" value="EPIs" checked> EPIs</label>
                                <label class="flex items-center gap-1"><input type="checkbox" name="equip[]" value="Cordas"> Cordas</label>
                                <label class="flex items-center gap-1"><input type="checkbox" name="equip[]" value="Cones"> Cones</label>
                                <label class="flex items-center gap-1"><input type="checkbox" name="equip[]" value="Caminhão"> Caminhão</label>
                            </div>
                        </div>

                        <div class="mb-2 border-b-2 border-gray-400 pb-2 bg-gray-50 p-2 rounded">
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
                        <div class="mt-6 flex flex-row-reverse gap-2">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-[#358054] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 sm:w-auto">
                                Gerar Ordem de serviço
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
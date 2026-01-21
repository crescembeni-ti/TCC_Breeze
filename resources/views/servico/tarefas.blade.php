@extends('layouts.dashboard')

@section('title', 'Minhas Tarefas')

@section('content')

{{-- ESTADO GLOBAL DO ALPINE --}}
<div x-data="{ open: false, item: { contact: {}, motivos: [], servicos: [], equipamentos: [] } }">

    {{-- CABEÇALHO --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center text-left">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">Minhas Tarefas (Equipe Técnica)</h2>
            <p class="text-gray-600 mt-1">Ordens de serviço encaminhadas para sua execução.</p>
        </div>
    </header>

    {{-- LISTA DE TAREFAS --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg relative z-0">
        <div class="p-6 text-left">
            @if($ordensDeServico->isEmpty())
                <div class="text-center py-12 flex flex-col items-center justify-center">
                    <div class="bg-green-50 p-4 rounded-full mb-3">
                        <i data-lucide="check-circle" class="w-8 h-8 text-[#358054]"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Sem tarefas no momento!</h3>
                    <p class="text-gray-500">Todas as ordens de serviço foram processadas.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ordensDeServico as $os)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">#{{ $os->contact->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="font-bold text-gray-900">{{ $os->contact->bairro }}</div>
                                    <div class="text-xs text-gray-500">{{ $os->contact->rua }}, {{ $os->contact->numero }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $os->contact->status->name == 'Em Execução' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $os->contact->status->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end items-center gap-2">
                                        {{-- BOTÃO DETALHES --}}
                                        <button @click="open = true; item = {{ json_encode($os->load('contact')) }}" 
                                            class="text-[#358054] hover:text-green-900 font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50 transition cursor-pointer">
                                            Detalhes da OS
                                        </button>

                                        {{-- BOTÕES DE AÇÃO DE STATUS --}}
                                        @if($os->contact->status->name != 'Em Execução')
                                            <form method="POST" action="{{ route('service.tasks.confirmar', $os->id) }}">
                                                @csrf
                                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-xs font-bold transition">
                                                    Visto / Iniciar
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('service.tasks.falha', $os->id) }}" class="flex items-center gap-1">
                                                @csrf
                                                <input type="text" name="motivo_falha" required minlength="3" placeholder="Motivo Falha" class="border rounded px-2 py-1 text-xs w-24">
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded shadow text-xs font-bold">
                                                    Falha
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('service.tasks.concluir', $os->id) }}">
                                                @csrf
                                                <button onclick="return confirm('Tem certeza que concluiu este serviço?')" class="bg-[#358054] hover:bg-green-700 text-white px-3 py-1 rounded shadow text-xs font-bold">
                                                    Concluir
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL COM ESTÉTICA DE OS OFICIAL (ESTÁTICO + DATA EXECUÇÃO) --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
                
                {{-- MARCA D'ÁGUA --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
                    <img src="{{ asset('images/logo.png') }}" class="w-1/2 object-contain opacity-10">
                </div>

                <div class="bg-white p-8 relative z-10 text-left">
                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto" alt="Logo">
                            <div class="text-[10px] text-gray-600 font-bold uppercase leading-tight">
                                ESTADO DO RIO DE JANEIRO<br>
                                MUNICÍPIO DE PARACAMBI<br>
                                SECRETARIA MUNICIPAL DE MEIO AMBIENTE
                            </div>
                        </div>
                        <div class="text-right">
                            <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">ORDEM DE SERVIÇO</h3>
                            <p class="text-xs font-bold mt-1 text-[#358054]">Visualização Equipe Técnica</p>
                        </div>
                    </div>

                    {{-- FORMULÁRIO DATA DE EXECUÇÃO --}}
                    {{-- Aqui usamos a rota de UPDATE para salvar apenas a nova data --}}
                    <form :action="'{{ url('/pbi-servico/tarefas') }}/' + item.id + '/update_date'" method="POST" class="text-sm space-y-6"> 
                        @csrf
                        @method('PUT')

                        {{-- DADOS ESTÁTICOS --}}
                        <div class="grid grid-cols-2 gap-4 border-b border-gray-300 pb-2">
                            <div>
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Nº Solicitação:</label>
                                <p class="p-1 bg-gray-50 border-b border-gray-400 font-bold text-gray-900" x-text="item.contact ? '#' + item.contact.id : ''"></p>
                            </div>
                            <div>
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Data da Vistoria (Admin):</label>
                                <p class="p-1 bg-gray-50 border-b border-gray-400 font-bold text-gray-900" x-text="item.data_vistoria ? new Date(item.data_vistoria).toLocaleDateString('pt-BR') : 'N/A'"></p>
                            </div>
                        </div>

                        <div class="border-b border-gray-300 pb-2 text-left">
                            <h4 class="font-bold underline mb-2 uppercase text-xs">Identificação da Área</h4>
                            <p class="mb-2"><strong>Endereço:</strong> <span x-text="item.contact ? (item.contact.rua + ', ' + item.contact.numero + ' - ' + item.contact.bairro) : ''"></span></p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-gray-500 font-bold uppercase text-[10px]">Latitude:</span>
                                    <p class="p-1 border-b border-gray-200 font-mono text-gray-900 font-bold" x-text="item.latitude || 'N/R'"></p>
                                </div>
                                <div>
                                    <span class="text-gray-500 font-bold uppercase text-[10px]">Longitude:</span>
                                    <p class="p-1 border-b border-gray-200 font-mono text-gray-900 font-bold" x-text="item.longitude || 'N/R'"></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 border-b border-gray-300 pb-2 text-left">
                            <div class="col-span-2">
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Espécie(s) Marcada(s):</label>
                                <p class="p-1 border-b border-gray-200 font-bold uppercase text-gray-900" x-text="item.especies || 'N/A'"></p>
                            </div>
                            <div>
                                <label class="font-bold block text-gray-700 uppercase text-[10px]">Quantidade:</label>
                                <p class="p-1 border-b border-gray-200 font-bold text-center text-gray-900" x-text="item.quantidade || '1'"></p>
                            </div>
                        </div>

                        {{-- MOTIVOS E EPI'S (ESTÁTICO) --}}
                        <div class="grid grid-cols-2 gap-4 text-left">
                            <div class="bg-gray-50/80 p-3 rounded border border-gray-200">
                                <h4 class="font-bold underline mb-2 uppercase text-[10px] text-[#358054]">Motivo da Intervenção</h4>
                                <div class="space-y-1">
                                    <template x-for="motivo in (item.motivos || [])">
                                        <div class="flex items-center gap-2 text-[11px] font-bold text-gray-800">
                                            <span class="text-[#358054]">✔</span> <span x-text="motivo"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="bg-gray-50/80 p-3 rounded border border-gray-200">
                                <h4 class="font-bold underline mb-2 uppercase text-[10px] text-blue-700">Equipamentos / EPIs Solicitados</h4>
                                <div class="space-y-1">
                                    <template x-for="eq in (item.equipamentos || [])">
                                        <div class="flex items-center gap-2 text-[11px] font-bold text-gray-800">
                                            <span class="text-blue-700">▪</span> <span x-text="eq"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- AGENDAMENTO (ÚNICO CAMPO EDITÁVEL) --}}
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-left">
                            <label class="font-bold block text-yellow-800 uppercase text-[10px] mb-2">Previsão de Execução (Definir Planejamento):</label>
                            <input type="date" name="data_execucao" required 
                                   min="{{ date('Y-m-d') }}" 
                                   :value="item.data_execucao"
                                   class="w-full border-0 border-b border-yellow-400 bg-transparent p-1 focus:ring-0 text-base font-bold text-gray-900">
                            <p class="text-[9px] text-yellow-700 mt-2 italic">* Preencha a data que sua equipe realizará este serviço.</p>
                        </div>

                        {{-- BOTÕES DO MODAL --}}
                        <div class="mt-8 flex justify-end gap-3 print:hidden">
                            <button type="button" @click="open = false" class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded text-xs font-bold hover:bg-gray-200 transition">
                                Fechar
                            </button>
                            <button type="button" onclick="window.print()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded text-xs font-bold hover:bg-gray-100 flex items-center gap-2">
                                <i data-lucide="printer" class="w-4 h-4 text-blue-600"></i> Imprimir OS
                            </button>
                            <button type="submit" class="bg-[#358054] text-white px-6 py-2 rounded text-xs font-bold shadow-lg hover:bg-green-700 flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i> Salvar Agendamento
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
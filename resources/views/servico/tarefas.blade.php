@extends('layouts.dashboard')

@section('title', 'Minhas Tarefas')

@section('content')

{{-- ESTADO GLOBAL DO ALPINE --}}
<div x-data="{ open: false, item: { contact: {}, servicos: [] } }">

    {{-- CABEÇALHO --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">Minhas Tarefas (Equipe Técnica)</h2>
            <p class="text-gray-600 mt-1">Ordens de serviço encaminhadas para sua equipe.</p>
        </div>
    </header>

    {{-- LISTA DE TAREFAS --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg relative z-0">
        <div class="p-6">
            @if($ordensDeServico->isEmpty())
                <div class="text-center py-12 flex flex-col items-center justify-center">
                    <div class="bg-green-50 p-4 rounded-full mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviços</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ordensDeServico as $os)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $os->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $os->contact->rua }}, {{ $os->contact->numero }} - {{ $os->contact->bairro }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $os->contact->nome_solicitante }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $os->contact->status->name == 'Em Execução' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $os->contact->status->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @foreach($os->servicos ?? [] as $servico)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs border border-green-200">{{ $servico }}</span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium flex gap-2">
                                    {{-- BOTÃO PARA ABRIR MODAL --}}
                                    <button @click="open = true; item = {{ $os->toJson() }}" 
                                        class="text-[#358054] hover:text-green-900 font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50 transition cursor-pointer">
                                        Detalhes
                                    </button>

                                    {{-- BOTÕES DE AÇÃO --}}
                                    @if($os->contact->status->name != 'Em Execução')
                                        <form method="POST" action="{{ route('service.tasks.confirmar', $os->id) }}">
                                            @csrf
                                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-xs font-bold">
                                                Visto / Iniciar
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('service.tasks.falha', $os->id) }}">
                                            @csrf
                                            <input type="text" name="motivo_falha" required minlength="3" placeholder="Motivo" class="border rounded px-2 py-1 text-xs">
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded shadow text-xs font-bold">
                                                Registrar Falha
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('service.tasks.concluir', $os->id) }}">
                                            @csrf
                                            <button onclick="return confirm('Tem certeza que concluiu este serviço?')" class="bg-[#358054] hover:bg-green-700 text-white px-3 py-1 rounded shadow text-xs font-bold">
                                                Concluir
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>


{{-- MODAL DE DETALHES DA OS --}}
{{-- MODAL RÉPLICA DA OS (FORMATO COMPLETO) --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
                <div class="bg-white p-8">

                    {{-- BOTÃO FECHAR --}}
                    <button @click="open = false" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 font-bold print:hidden">✕</button>

                    {{-- HEADER IDÊNTICO AO DO ANALISTA --}}
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/Brasao_Verde.png') }}" class="h-16 w-auto" alt="Logo">
                            <div class="text-[10px] text-gray-600 leading-tight font-bold uppercase">
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

                    {{-- CORPO DA OS --}}
                    <div class="text-sm space-y-4">
                        
                        {{-- DADOS DA SOLICITAÇÃO --}}
                        <div class="grid grid-cols-2 gap-4 border-b border-gray-300 pb-2">
                            <div>
                                <label class="font-bold block text-gray-700">Protocolo / Solicitação:</label>
                                <p class="w-full border-b border-gray-400 bg-gray-50 p-1" x-text="item.contact ? item.contact.id : ''"></p>
                            </div>
                            <div>
                                <label class="font-bold block text-gray-700">Data de Geração:</label>
                                <p class="w-full border-b border-gray-400 bg-gray-50 p-1" x-text="item.created_at ? new Date(item.created_at).toLocaleDateString('pt-BR') : ''"></p>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DA ÁREA --}}
                        <div class="border-b border-gray-300 pb-2">
                            <h4 class="font-bold underline mb-1">Identificação da Área</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <span class="text-gray-600">Endereço Completo:</span>
                                    <p class="w-full border-b border-gray-400 p-1 bg-gray-50 font-medium" 
                                       x-text="item.contact ? (item.contact.rua + ', ' + item.contact.numero + ' - ' + item.contact.bairro) : ''"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-gray-600">Latitude:</span>
                                        <p class="w-full border-b border-gray-400 p-1 bg-gray-50" x-text="item.latitude || 'Não informada'"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Longitude:</span>
                                        <p class="w-full border-b border-gray-400 p-1 bg-gray-50" x-text="item.longitude || 'Não informada'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- IDENTIFICAÇÃO DAS ÁRVORES --}}
                        <div class="grid grid-cols-3 gap-4 border-b border-gray-300 pb-2">
                            <div class="col-span-2">
                                <label class="font-bold block">Espécie(s):</label>
                                <p class="w-full border-b border-gray-400 p-1 bg-gray-50" x-text="Array.isArray(item.especies) ? item.especies.join(', ') : (item.especies || 'N/A')"></p>
                            </div>
                            <div>
                                <label class="font-bold block">Quantidade:</label>
                                <p class="w-full border-b border-gray-400 p-1 bg-gray-50" x-text="item.quantidade || '1'"></p>
                            </div>
                        </div>

                        {{-- MOTIVOS E SERVIÇOS (COM MARCADORES DE CHECK) --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-2 rounded border">
                                <h4 class="font-bold underline mb-1">Motivo da Intervenção</h4>
                                <div class="space-y-1">
                                    <template x-for="motivo in (item.motivos || [])" :key="motivo">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[#358054] font-bold">✔</span>
                                            <span x-text="motivo"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-2 rounded border">
                                <h4 class="font-bold underline mb-1">Serviços Autorizados</h4>
                                <div class="space-y-1">
                                    <template x-for="serv in (item.servicos || [])" :key="serv">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[#358054] font-bold">✔</span>
                                            <span x-text="serv"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- EQUIPAMENTOS --}}
                        <div class="border-b border-gray-300 pb-2">
                            <h4 class="font-bold underline mb-1">Equipamentos Necessários</h4>
                            <div class="flex flex-wrap gap-4 bg-gray-50 p-2 rounded">
                                <template x-for="eq in (item.equipamentos || [])" :key="eq">
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs border border-gray-400 px-2 py-0.5 rounded bg-white" x-text="eq"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- OBSERVAÇÕES DO ANALISTA --}}
                        <div class="bg-yellow-50 p-3 rounded border border-yellow-200">
                            <h4 class="font-bold text-xs uppercase text-yellow-800 mb-1">Laudo / Observações do Analista:</h4>
                            <p class="italic text-gray-700" x-text="item.laudo_tecnico || item.observacoes || 'Nenhuma observação adicional.'"></p>
                        </div>

                        {{-- DATAS E CONFIRMAÇÃO --}}
                        <div class="grid grid-cols-2 gap-4 pt-4">
                            <div class="border-t border-black text-center pt-1 text-[10px] uppercase">
                                Assinatura do Analista Responsável
                            </div>
                            <div class="border-t border-black text-center pt-1 text-[10px] uppercase">
                                Assinatura da Equipe Técnica
                            </div>
                        </div>
                    </div>

                    {{-- AÇÕES DO MODAL --}}
                    <div class="mt-8 flex justify-end gap-3 print:hidden">
                        <button onclick="window.print()" class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded flex items-center gap-2 hover:bg-gray-200">
                             Imprimir OS
                        </button>
                        <button @click="open = false" class="bg-[#358054] text-white px-6 py-2 rounded font-bold hover:bg-green-700">
                            Ok, entendi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
@media print {
    body * { visibility: hidden; }
    .fixed.inset-0.z-50, .fixed.inset-0.z-50 * { visibility: visible; }
    .fixed.inset-0.z-50 { position: absolute; left: 0; top: 0; width: 100%; }
    .print\:hidden { display: none !important; }
    .shadow-xl { box-shadow: none !important; }
}
</style>
@endpush
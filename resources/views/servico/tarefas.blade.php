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
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border-t-8 border-[#358054]">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg font-bold text-gray-800">OS #<span x-text="item.id"></span> | Protocolo #<span x-text="item.contact ? item.contact.id : ''"></span></h3>
                        <button @click="open = false" class="text-gray-500 hover:text-gray-700 font-bold">✕</button>
                    </div>

                    {{-- DETALHES --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <p><strong>Solicitante:</strong> <span x-text="item.contact ? item.contact.nome_solicitante : ''"></span></p>
                            <p><strong>Endereço:</strong> <span x-text="item.contact ? item.contact.rua + ', ' + item.contact.numero + ' - ' + item.contact.bairro : ''"></span></p>
                        </div>
                        <div>
                            <p><strong>Status:</strong> <span x-text="item.contact ? item.contact.status.name : ''"></span></p>
                            <p><strong>Serviços:</strong></p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <template x-for="servico in item.servicos" :key="servico">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs border border-green-200" x-text="servico"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- OBSERVAÇÕES / LAUDO --}}
                    <div class="bg-gray-50 p-3 rounded text-sm">
                        <p><strong>Observações / Laudo:</strong></p>
                        <p x-text="item.laudo_tecnico || item.observacoes || 'Sem observações adicionais.'"></p>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection

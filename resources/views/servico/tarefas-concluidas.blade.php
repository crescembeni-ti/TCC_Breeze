@extends('layouts.dashboard')
@section('title', 'Tarefas Concluídas')

@section('content')
<div x-data="{ open: false, item: { id: '', contact: { status: {} }, motivos: [], servicos: [], equipamentos: [] } }">
    
    {{-- CABEÇALHO DA PÁGINA --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6">
        <h2 class="text-3xl font-semibold text-gray-700">Histórico de Conclusão</h2>
        <p class="text-gray-500 mt-1">Serviços finalizados pela equipe técnica.</p>
    </header>

    {{-- CONTEÚDO PRINCIPAL --}}
    @if($ordens->isEmpty())
        
        {{-- ESTADO VAZIO --}}
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg p-12 text-center flex flex-col items-center justify-center min-h-[300px]">
            <div class="bg-gray-100 p-4 rounded-full mb-4">
                <i data-lucide="archive" class="w-12 h-12 text-gray-400"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Histórico vazio</h3>
            <p class="text-gray-500">Nenhuma tarefa foi concluída até o momento.</p>
        </div>

    @else
        
        {{-- LISTAGEM (TABELA PADRONIZADA) --}}
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                            {{-- NOVA COLUNA: DATA CONCLUSÃO --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Conclusão</th>
                            {{-- NOVA COLUNA: STATUS --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($ordens as $os)
                        <tr class="hover:bg-gray-50 transition-colors opacity-90">
                            {{-- PROTOCOLO --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-600">
                                #{{ $os->contact->id }}
                            </td>
                            
                            {{-- LOCAL --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="font-bold text-gray-900">{{ $os->contact->bairro }}</div>
                                <div class="text-xs text-gray-500">{{ $os->contact->rua }}</div>
                            </td>

                            {{-- DATA CONCLUSÃO (UPDATED_AT) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="calendar-check" class="w-4 h-4 text-green-600 mr-1"></i>
                                    <span>{{ $os->updated_at->format('d/m/Y') }}</span>
                                    <span class="text-gray-500 text-xs">às</span>
                                    <span>{{ $os->updated_at->format('H:i') }}</span>
                                </div>
                            </td>

                            {{-- STATUS (FIXO: CONCLUÍDO) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                    Concluído
                                </span>
                            </td>

                            {{-- AÇÕES (BOTÃO VER OS) --}}
                            <td class="px-6 py-4 text-right">
                                <button @click="open = true; item = {{ json_encode($os->load('contact.status')) }}" 
                                    class="text-green-700 hover:text-blue-900 font-bold border border-green-700 px-4 py-1.5 rounded hover:bg-blue-50 transition flex items-center gap-2 ml-auto">
                                    <i data-lucide="eye" class="w-4 h-4"></i> Ver OS
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- MODAL (APENAS LEITURA) --}}
    @include('servico.partials.modal_os', ['action' => 'none'])
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => { 
        lucide.createIcons(); 
    });
</script>
@endsection
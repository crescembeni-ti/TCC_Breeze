@extends('layouts.dashboard')

@section('title', 'Dashboard do Analista')

@section('content')

    <header class="bg-white shadow mb-8 rounded-lg p-6">
        <h2 class="text-3xl font-semibold text-[#358054] leading-tight">
            Painel do Analista
        </h2>
        <p class="text-gray-600 mt-1">Gerencie e realize as vistorias pendentes.</p>
    </header>
    
    <div class="space-y-8">
        
        {{-- CARD DE VISÃO GERAL --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- CONTADOR PENDENTES --}}
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                <p class="text-sm font-medium text-gray-500">Vistorias Pendentes</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $countPendentes }}</p> 
            </div>
            
            {{-- CONTADOR CONCLUÍDAS --}}
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-sm font-medium text-gray-500">Vistorias Concluídas</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $countConcluidas }}</p>
            </div>
        </div>

        {{-- TABELA PRINCIPAL DE VISTORIAS --}}
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-2xl font-bold text-[#358054] mb-6">
                    Lista de Solicitações de Vistoria (Recentes)
                </h3>
                
                <div class="mt-4 overflow-x-auto">
                    {{-- Verifica se tem dados --}}
                    @if($vistorias->isEmpty())
                        <p class="text-gray-500 text-center py-4">Nenhuma solicitação pendente no momento.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data da Solicitação</th>
                                  
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- Loop real pelos dados --}}
                                @foreach ($vistorias as $vistoria)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700">
                                        #{{ $vistoria->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $vistoria->topico ?? 'Sem Tópico' }}</div>
                                        <div class="text-xs text-gray-500">{{ $vistoria->bairro }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $vistoria->created_at->format('d/m/Y') }}
                                    </td>
                              
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

            </div>
        </div>
    </div>

@endsection
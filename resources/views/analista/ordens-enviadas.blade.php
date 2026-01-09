@extends('layouts.dashboard')

@section('title', 'Ordens de Serviço Enviadas')

@section('content')

{{-- CABEÇALHO --}}
<header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
    <div>
        <h2 class="text-3xl font-semibold text-[#358054] leading-tight">
            Ordens de Serviço Enviadas
        </h2>
        <p class="text-gray-600 mt-1">
            Histórico de todas as OS que você gerou.
        </p>
    </div>
    <div>
        <a href="{{ route('analyst.vistorias.pendentes') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
            ← Voltar para Pendentes
        </a>
    </div>
</header>

{{-- MENSAGEM DE SUCESSO --}}
@if(session('success'))
    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

{{-- TABELA DE LISTAGEM --}}
<div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
    <div class="p-6">
        
        @if($ordensEnviadas->isEmpty())
            <div class="text-center py-12 flex flex-col items-center justify-center">
                <div class="bg-blue-50 p-4 rounded-full mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-blue-600">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Nenhuma ordem enviada ainda</h3>
                <p class="text-gray-500">Quando você gerar ordens de serviço, elas aparecerão aqui.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- REMOVIDO: <th OS Nº> --}}
                            
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Vistoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($ordensEnviadas as $os)
                        <tr class="hover:bg-gray-50 transition-colors">
                            
                            {{-- REMOVIDO: <td OS Nº> --}}

                            {{-- PROTOCOLO (Agora é a primeira coluna e com destaque) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-[#358054]">
                                    #{{ $os->contact->id }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $os->contact->bairro }}</div>
                                <div class="text-sm text-gray-500">{{ $os->contact->rua }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $os->contact->nome_solicitante }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($os->data_vistoria)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($os->contact->status->name == 'Vistoriado') bg-blue-100 text-blue-800
                                    @elseif($os->contact->status->name == 'Em Execução') bg-yellow-100 text-yellow-800
                                    @elseif($os->contact->status->name == 'Concluído') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $os->contact->status->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('analyst.os.show', $os->id) }}" 
                                   class="text-[#358054] hover:text-green-900 font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50 transition">
                                    Ver Detalhes
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ESTATÍSTICAS RÁPIDAS --}}
<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Total de OS Geradas</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $ordensEnviadas->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Em Execução</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $ordensEnviadas->where('contact.status.name', 'Em Execução')->count() }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Concluídas</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $ordensEnviadas->where('contact.status.name', 'Concluído')->count() }}
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
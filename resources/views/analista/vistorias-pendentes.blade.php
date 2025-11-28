@extends('layouts.dashboard')

@section('title', 'Vistorias Pendentes')

@section('content')

    {{-- CABEÇALHO DA PÁGINA --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold text-[#358054] leading-tight">
                Vistorias Pendentes
            </h2>
            <p class="text-gray-600 mt-1">
                Solicitações que aguardam análise técnica ou vistoria in-loco.
            </p>
        </div>
        
        {{-- Botão Voltar (Opcional) --}}
        <div class="mt-4 md:mt-0">
            <a href="{{ route('analyst.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-[#358054] flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Voltar ao Painel
            </a>
        </div>
    </header>
    
    {{-- ÁREA DA TABELA --}}
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
        <div class="p-6">
            
            {{-- CASO 1: NÃO EXISTEM VISTORIAS --}}
            @if($vistorias->isEmpty())
                <div class="text-center py-12 flex flex-col items-center justify-center">
                    <div class="bg-green-50 p-4 rounded-full mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-[#358054]">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tudo em dia!</h3>
                    <p class="text-gray-500">Não há vistorias pendentes no momento.</p>
                </div>

            {{-- CASO 2: EXISTEM VISTORIAS (MOSTRAR TABELA) --}}
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto / Local</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($vistorias as $vistoria)
                            <tr class="hover:bg-gray-50 transition-colors">
                                
                                {{-- ID --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    #{{ $vistoria->id }}
                                </td>

                                {{-- ASSUNTO E BAIRRO --}}
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $vistoria->topico ?? 'Sem Tópico' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $vistoria->bairro ?? 'Bairro N/A' }}
                                        @if($vistoria->rua)
                                         - {{ $vistoria->rua }}, {{ $vistoria->numero }}
                                        @endif
                                    </div>
                                </td>

                                {{-- SOLICITANTE --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $vistoria->nome_solicitante }}</div>
                                    <div class="text-sm text-gray-500 text-xs">{{ $vistoria->email_solicitante }}</div>
                                </td>

                                {{-- STATUS (Com cores dinâmicas) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match($vistoria->status->name ?? '') {
                                            'Em Análise' => 'bg-yellow-100 text-yellow-800',
                                            'Deferido' => 'bg-blue-100 text-blue-800',
                                            'Em Execução' => 'bg-purple-100 text-purple-800',
                                            'Vistoriado' => 'bg-indigo-100 text-indigo-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $vistoria->status->name ?? 'Indefinido' }}
                                    </span>
                                </td>

                                {{-- DATA --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $vistoria->created_at->format('d/m/Y') }}
                                </td>

                                {{-- BOTÃO DE AÇÃO --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{-- Ajuste a rota abaixo para onde você quer que vá ao clicar --}}
                                    <a href="#" class="text-[#358054] hover:text-green-900 font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50 transition">
                                        Analisar
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
@endsection
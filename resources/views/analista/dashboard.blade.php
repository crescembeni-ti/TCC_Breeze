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
            {{-- Substitua 5 por uma variável que carrega o COUNT de vistorias pendentes --}}
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                <p class="text-sm font-medium text-gray-500">Vistorias Pendentes</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">5</p> 
            </div>
            
            {{-- Substitua 12 por uma variável que carrega o COUNT de vistorias concluídas --}}
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-sm font-medium text-gray-500">Vistorias Concluídas</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">12</p>
            </div>

           
        </div>

        {{-- TABELA PRINCIPAL DE VISTORIAS --}}
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-2xl font-bold text-[#358054] mb-6">
                    Lista de Solicitações de Vistoria
                </h3>
                
                {{-- A LÓGICA DE EXIBIÇÃO DE DADOS VEM AQUI --}}
           {{-- Lembrete:** Esta lista precisa ser carregada do seu Controller, passando os dados para a View. -- }}
                
                {{-- Exemplo de como a tabela deve ficar --}}
                <div class="mt-4">
                    {{-- Estrutura da Tabela (necessita de dados do Controller) --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data da Solicitação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- @foreach ($vistorias as $vistoria) --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">#001</td>
                                <td class="px-6 py-4 whitespace-nowrap">Poda de Árvore Risco</td>
                                <td class="px-6 py-4 whitespace-nowrap">2025-11-25</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">Iniciar Vistoria</a>
                                </td>
                            </tr>
                            {{-- @endforeach --}}
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection
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
        {{-- CORREÇÃO AQUI: Alterado de md:grid-cols-3 para md:grid-cols-2 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
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

        {{-- Botões de Ação Rápida --}}
         <div class="grid grid-cols-1 gap-6">
             <a href="{{ route('analyst.vistorias.pendentes') }}" class="block p-8 bg-white border border-gray-200 rounded-lg shadow hover:bg-green-50 hover:border-green-300 transition group">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 group-hover:text-[#358054] flex items-center gap-2">
                <i data-lucide="list" class="w-8 h-8"></i> Minhas Tarefas
                </h5>
                <p class="text-lg font-normal text-gray-700">Visualize a lista completa de ordens de serviço atribuídas a você.</p>
             </a>
        </div>
            </div>
        </div>


    </div>

@endsection
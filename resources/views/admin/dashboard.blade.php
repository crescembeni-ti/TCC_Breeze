@extends('layouts.dashboard')

@section('title', 'Painel Administrativo')

@section('content')
<div class="bg-white shadow-sm rounded-lg p-8">
    <h2 class="text-3xl font-bold text-[#358054] mb-4">Painel Administrativo</h2>
    <p class="text-gray-700 text-lg mb-4">
        Bem-vindo, {{ Auth::guard('admin')->user()->name }} 🌳 
        <br>Use o menu à esquerda para gerenciar árvores, mensagens e solicitações.
    </p>

    @if(isset($stats))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
        <div class="p-4 bg-green-100 rounded shadow">
            <h4 class="text-lg font-semibold text-green-800">🌳 Árvores</h4>
            <p class="text-2xl font-bold text-green-900">{{ $stats['total_trees'] }}</p>
        </div>
        <div class="p-4 bg-green-100 rounded shadow">
            <h4 class="text-lg font-semibold text-green-800">📋 Atividades</h4>
            <p class="text-2xl font-bold text-green-900">{{ $stats['total_activities'] }}</p>
        </div>
        <div class="p-4 bg-green-100 rounded shadow">
            <h4 class="text-lg font-semibold text-green-800">🌱 Espécies</h4>
            <p class="text-2xl font-bold text-green-900">{{ $stats['total_species'] }}</p>
        </div>
    </div>
    @endif
</div>

<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Atividade Recente do Painel</h2>
    <div class="space-y-4">
        @forelse($adminLogs ?? [] as $log)
            <div class="border-l-4 border-blue-500 pl-4 py-2">
                <p class="text-sm text-gray-600">
                    {{ $log->created_at->format('d/m/Y H:i') }}
                </p>
                <p class="text-gray-900">
                    {{ $log->description }}
                </p>
            </div>
        @empty
            <p class="text-gray-600">Nenhuma atividade registrada ainda.</p>
        @endforelse
    </div>
</div>
@endsection

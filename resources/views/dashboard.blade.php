@extends('layouts.dashboard')

@section('title', 'Painel do Usuário')

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg p-8">
    <h2 class="text-3xl font-bold text-[#358054] mb-4">Painel do Usuário</h2>
    <p class="text-gray-700 text-lg">
        Bem-vindo, {{ $user->name }} 🌱 
        <br>
        Aqui você pode visualizar o mapa, enviar e acompanhar solicitações.
    </p>

    {{-- Estatísticas --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
    <div class="card text-center">
        <h3>🌳 Total de Árvores</h3>
        <p class="text-4xl font-bold text-green-700">{{ $stats['total_trees']}}</p>
    </div>

    <div class="card text-center">
        <h3>🪵 Atividades Registradas</h3>
        <p class="text-4xl font-bold text-blue-700">{{ $stats['total_activities']}}</p>
    </div>

    <div class="card text-center">
        <h3>🌱 Espécies no Mapa</h3>
        <p class="text-4xl font-bold text-purple-700">{{ $stats['total_species']}}</p>
    </div>
</div>

</div>
@endsection

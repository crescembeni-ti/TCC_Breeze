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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <a href="{{ route('contact') }}" class="p-6 bg-gray-50 rounded-lg shadow hover:bg-gray-100 transition">
            <h3>📤 Nova Solicitação</h3>
            <p>Solicite o plantio de uma nova árvore.</p>
        </a>

        <a href="{{ route('contact.myrequests') }}" class="p-6 bg-gray-50 rounded-lg shadow hover:bg-gray-100 transition">
            <h3>📋 Minhas Solicitações</h3>
            <p>Acompanhe o status das suas solicitações.</p>
        </a>
        
        <a href="{{ route('profile.edit') }}" class="p-6 bg-gray-50 rounded-lg shadow hover:bg-gray-100 transition">
            <h3>👤 Meu Perfil</h3>
            <p>Atualize seus dados e senha.</p>
        </a>
    </div>
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Ordem de Serviço')

@section('content')
<div class="bg-white shadow rounded-lg p-6">

    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/Brasao_Verde.png') }}" class="h-16 w-auto" alt="Logo">
            <div class="text-xs text-gray-600 leading-tight font-bold uppercase">
                Estado do Rio de Janeiro<br>
                Município de Paracambi<br>
                Secretaria Municipal de Meio Ambiente
            </div>
        </div>
        <div class="text-right">
            <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">Ordem de Serviço</h3>
            <p class="text-sm font-bold mt-1">Poda e Remoção de Árvores</p>
        </div>
    </div>

    {{-- Dados da OS --}}
    <div class="grid grid-cols-2 gap-4 mb-2 border-b border-gray-300 pb-2">
        <div>
            <label class="font-bold block text-gray-700">Nº Solicitação:</label>
            <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1" value="{{ $os->contact->id }}" readonly>
        </div>
        <div>
            <label class="font-bold block text-gray-700">Data:</label>
            <input type="text" class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1" value="{{ \Carbon\Carbon::parse($os->created_at)->format('d/m/Y') }}" readonly>
        </div>
    </div>

    {{-- Identificação da Área --}}
    <div class="mb-2 border-b border-gray-300 pb-2">
        <h4 class="font-bold underline mb-1">Identificação da Área</h4>
        <div class="grid grid-cols-1 gap-2">
            <div>
                <span class="text-gray-600">Endereço:</span>
                <input type="text" class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50" value="{{ $os->contact->rua }}, {{ $os->contact->numero ?? '' }} - {{ $os->contact->bairro }}" readonly>
            </div>
            <div>
                <span class="text-gray-600">Coordenadas:</span>
                <input type="text" class="w-full border-0 border-b border-gray-400 p-1 bg-gray-50" value="{{ $os->contact->lat_long ?? '' }}" readonly>
            </div>
        </div>
    </div>

    {{-- Serviços, Motivos e Equipamentos --}}
    <div class="grid grid-cols-3 gap-4 mb-2 border-b border-gray-300 pb-2">
        <div class="col-span-2">
            <label class="font-bold block">Serviços:</label>
            <input type="text" class="w-full border-0 border-b border-gray-400 p-1" value="{{ implode(', ', $os->servicos ?? []) }}" readonly>
        </div>
        <div>
            <label class="font-bold block">Equipamentos:</label>
            <input type="text" class="w-full border-0 border-b border-gray-400 p-1" value="{{ implode(', ', $os->equipamentos ?? []) }}" readonly>
        </div>
    </div>

    <div class="mb-2 border-b border-gray-300 pb-2 bg-gray-50 p-2 rounded">
        <h4 class="font-bold underline mb-1">Motivos</h4>
        <p>{{ implode(', ', $os->motivos ?? []) }}</p>
    </div>

    <div class="grid grid-cols-2 gap-6 mt-4">
        <div>
            <label class="font-bold block text-xs uppercase">Data Vistoria:</label>
            <input type="text" class="w-full border p-1 rounded" value="{{ \Carbon\Carbon::parse($os->data_vistoria)->format('d/m/Y') }}" readonly>
        </div>
        <div>
            <label class="font-bold block text-xs uppercase">Previsão Execução:</label>
            <input type="text" class="w-full border p-1 rounded" value="{{ $os->data_execucao ? \Carbon\Carbon::parse($os->data_execucao)->format('d/m/Y') : '-' }}" readonly>
        </div>
    </div>

</div>
@endsection

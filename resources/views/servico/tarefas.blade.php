@extends('layouts.dashboard')

@section('title', 'Minhas Tarefas')

@section('content')

<div class="bg-white p-8 rounded-lg shadow">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#358054]">
            Minhas Tarefas (Equipe Técnica)
        </h2>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            {{ session('warning') }}
        </div>
    @endif

    @if($ordensDeServico->isEmpty()) 
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Não há ordens de serviço encaminhadas para sua equipe no momento.</p>
        </div>
    @else

        <div class="grid grid-cols-1 gap-6">

            @foreach($ordensDeServico as $os)
            <div class="border border-l-4 {{ $os->contact->status->name == 'Em Execução' ? 'border-l-blue-500' : 'border-l-[#358054]' }} rounded-lg shadow-sm p-6 hover:shadow-md transition">
                
                <div class="flex justify-between items-start">
                    <h3 class="text-2xl font-semibold text-[#358054] mb-3">
                        OS #{{ $os->id }} <span class="text-sm text-gray-500 font-normal">| Protocolo #{{ $os->contact->id }}</span>
                    </h3>
                    
                    {{-- Badge de Status --}}
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                        {{ $os->contact->status->name == 'Em Execução' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $os->contact->status->name }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-gray-600"><strong>Solicitante:</strong> {{ $os->contact->nome_solicitante }}</p>
                        <p class="text-gray-600"><strong>Local:</strong>
                            {{ $os->contact->rua }}, {{ $os->contact->numero }} - {{ $os->contact->bairro }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 font-bold mb-1">Serviços a Executar:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($os->servicos ?? [] as $servico)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm border border-green-200">
                                    {{ $servico }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded mb-4">
                    <p class="text-sm"><strong>Observações / Laudo:</strong><br>
                    <span class="text-gray-700">{{ $os->laudo_tecnico ?? $os->observacoes ?? 'Sem observações adicionais.' }}</span></p>
                </div>

                <hr class="my-4">

                {{-- LÓGICA DOS BOTÕES --}}
                <div class="flex flex-col md:flex-row gap-4 justify-end items-center">
                    
                    {{-- 
                        CENÁRIO 1: ACABOU DE CHEGAR (Status ainda é 'Vistoriado' ou outro) 
                        Mostra botão "VISTO / CONFIRMAR"
                    --}}
                    @if($os->contact->status->name != 'Em Execução')
                        
                        <div class="flex items-center gap-2 text-yellow-700 bg-yellow-50 px-3 py-2 rounded text-sm mr-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Confirme o recebimento para iniciar.
                        </div>

                        <form method="POST" action="{{ route('service.tasks.confirmar', $os->id) }}">
                            @csrf
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow text-sm font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Visto / Iniciar Execução
                            </button>
                        </form>

                    {{-- 
                        CENÁRIO 2: JÁ ESTÁ EM EXECUÇÃO 
                        Mostra botões CONCLUIR e FALHA
                    --}}
                    @else

                        <form method="POST" action="{{ route('service.tasks.falha', $os->id) }}" class="flex gap-2 w-full md:w-auto">
                            @csrf
                            <input name="motivo_falha" required minlength="3"
                                placeholder="Motivo (caso não execute)"
                                class="border rounded-lg px-3 py-2 text-sm w-full md:w-64 focus:ring-red-500 focus:border-red-500">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow text-sm font-bold whitespace-nowrap">
                                Registrar Falha
                            </button>
                        </form>

                        <form method="POST" action="{{ route('service.tasks.concluir', $os->id) }}">
                            @csrf
                            <button onclick="return confirm('Tem certeza que concluiu este serviço?')" 
                                    class="bg-[#358054] hover:bg-green-700 text-white px-6 py-2 rounded-lg shadow text-sm font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Concluir Serviço
                            </button>
                        </form>

                    @endif

                </div>
            </div>
            @endforeach
        </div>

    @endif
</div>

@endsection
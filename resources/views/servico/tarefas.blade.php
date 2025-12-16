@extends('layouts.dashboard')

@section('title', 'Minhas Tarefas')

@section('content')

<div class="bg-white p-8 rounded-lg shadow">

    <h2 class="text-3xl font-bold text-[#358054] mb-6">
        Minhas Tarefas
    </h2>

    {{-- 🔑 CORREÇÃO 1: Usar a variável $ordensDeServico --}}
    @if($ordensDeServico->isEmpty()) 
        <p class="text-gray-600 text-lg">Não há tarefas atribuídas no momento.</p>
    @else

        <div class="grid grid-cols-1 gap-6">

            {{-- 🔑 CORREÇÃO 2: Iterar como $os --}}
            @foreach($ordensDeServico as $os)
            <div class="border rounded-lg shadow p-6">
                <h3 class="text-2xl font-semibold text-[#358054] mb-3">
                    Ordem de Serviço #OS-{{ $os->id }}
                </h3>

                <p><strong>Solicitante:</strong> {{ $os->contact->nome_solicitante }}</p>

                <p><strong>Endereço:</strong>
                    {{ $os->contact->rua }}, {{ $os->contact->numero }} - {{ $os->contact->bairro }}
                </p>

                <p><strong>Serviços:</strong>
                    @foreach ($os->servicos as $servico)
                        <span class="bg-blue-200 text-blue-800 px-2 py-1 rounded-md">{{ $servico }}</span>
                    @endforeach
                </p>

                <p><strong>Observações:</strong><br>
                    {{ $os->observacoes ?? 'Nenhuma observação.' }}
                </p>

                <div class="mt-6 flex gap-4">
                    {{-- Usar $os->id na rota agora está correto --}}
                    <form method="POST" action="{{ route('service.tasks.concluir', $os->id) }}">
                        @csrf
                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">
                            Concluir Ordem de Serviço
                        </button>
                    </form>

                    <form method="POST" action="{{ route('service.tasks.falha', $os->id) }}">
                        @csrf
                        <input name="motivo_falha" required minlength="5"
                            placeholder="Motivo da falha"
                            class="border rounded-lg px-3 py-2">
                        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">
                            Não Executado
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

    @endif
</div>

@endsection
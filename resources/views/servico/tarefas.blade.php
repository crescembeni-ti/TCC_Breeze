@extends('layouts.dashboard')

@section('title', 'Minhas Tarefas')

@section('content')

<div class="bg-white p-8 rounded-lg shadow">

    <h2 class="text-3xl font-bold text-[#358054] mb-6">
        Minhas Tarefas
    </h2>

    @if($tarefas->isEmpty())
        <p class="text-gray-600 text-lg">Não há tarefas atribuídas no momento.</p>
    @else

        <div class="grid grid-cols-1 gap-6">

            @foreach($tarefas as $tarefa)
            <div class="border rounded-lg shadow p-6">

                <h3 class="text-2xl font-semibold text-[#358054] mb-3">
                    {{ $tarefa->topico }}
                </h3>

                <p><strong>Solicitante:</strong> {{ $tarefa->nome_solicitante }}</p>

                <p><strong>Endereço:</strong>
                    {{ $tarefa->rua }}, {{ $tarefa->numero }} - {{ $tarefa->bairro }}
                </p>

                <p class="mt-2"><strong>Descrição:</strong><br>
                    {{ $tarefa->descricao }}
                </p>

                <p class="mt-2"><strong>Observações do Analista:</strong><br>
                    {{ $tarefa->observacoes ?? '---' }}
                </p>


                <div class="mt-6 flex gap-4">

                    {{-- CONCLUIR --}}
                    <form method="POST" action="{{ route('service.tasks.concluir', $tarefa->id) }}">
                        @csrf

                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">
                            Concluir Serviço
                        </button>
                    </form>

                    {{-- FALHA --}}
                    <form method="POST" action="{{ route('service.tasks.falha', $tarefa->id) }}">
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

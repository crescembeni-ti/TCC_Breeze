@extends('layouts.dashboard')

@section('title', 'Ordens de Serviço')

@section('content')
<div class="bg-white shadow-sm rounded-lg p-6">

    {{-- TÍTULO + FILTROS --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#358054]">
            Ordens de Serviço
        </h2>

        <div class="flex gap-2">
            <a href="{{ route('admin.os.index', ['destino' => 'analista']) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold
               {{ $destino === 'analista'
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Enviadas para Analistas
            </a>

            <a href="{{ route('admin.os.index', ['destino' => 'servico']) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold
               {{ $destino === 'servico'
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Enviadas para Serviço
            </a>
        </div>
    </div>

    @if($oss->isEmpty())
        <p class="text-gray-600">
            Nenhuma ordem de serviço neste filtro.
        </p>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#OS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assunto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destino</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($oss as $os)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold">#{{ $os->id }}</td>

                    <td class="px-6 py-4">
                        {{ $os->contact->nome_solicitante }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $os->contact->topico }}
                    </td>

                    <td class="px-6 py-4 text-sm font-semibold">
                        @if($os->flow === 'analista')
                            <span class="text-blue-600">Analista</span>
                        @elseif($os->flow === 'servico')
                            <span class="text-orange-600">Serviço</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 flex gap-2">

                        {{-- VISUALIZAR --}}
                        <a href="{{ route('admin.os.show', $os->id) }}"
                           class="border border-[#358054] text-[#358054] px-3 py-1 rounded hover:bg-green-50">
                            Visualizar
                        </a>

                        {{-- CANCELAR --}}
                        <form method="POST"
                              action="{{ route('admin.os.cancelar', $os->id) }}"
                              onsubmit="return confirm('Deseja cancelar esta ordem e retorná-la para a etapa anterior?');">
                            @csrf
                            @method('PUT')

                            <button
                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                Cancelar
                            </button>
                        </form>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
@endsection

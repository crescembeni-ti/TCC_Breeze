@extends('layouts.dashboard')

@section('title', 'Ordens de Serviço')

@section('content')
<div class="bg-white shadow-sm rounded-lg p-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#358054]">Ordens de Serviço</h2>

        {{-- FILTROS --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.os.index', ['tipo' => 'recebidas']) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold
               {{ ($tipo ?? 'recebidas') === 'recebidas'
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Recebidas
            </a>

            <a href="{{ route('admin.os.index', ['tipo' => 'enviadas']) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold
               {{ ($tipo ?? 'recebidas') === 'enviadas'
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Enviadas
            </a>
        </div>
    </div>

    @if($oss->isEmpty())
        <p class="text-gray-600">
            Nenhuma ordem de serviço
            {{ ($tipo ?? 'recebidas') === 'recebidas' ? 'recebida' : 'enviada' }}.
        </p>
    @else
        <table class="min-w-full divide-y divide-gray-200 mt-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#OS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assunto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($oss as $os)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold">#{{ $os->id }}</td>
                    <td class="px-6 py-4">{{ $os->contact->nome_solicitante }}</td>
                    <td class="px-6 py-4">{{ $os->contact->topico }}</td>
                    <td class="px-6 py-4">
                        {{ $os->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 flex gap-2">

                        {{-- VISUALIZAR (todos) --}}
                        <a href="{{ route('admin.os.show', $os->id) }}"
                           class="text-[#358054] font-semibold border border-[#358054] px-3 py-1 rounded hover:bg-green-50">
                            Visualizar
                        </a>

                        {{-- ENCAMINHAR (somente admin + recebidas) --}}
                        @if(auth('admin')->check() && ($tipo ?? 'recebidas') === 'recebidas')
                            <form action="{{ route('admin.os.enviar', $os->id) }}" method="POST"
                                  onsubmit="return confirm('Encaminhar esta ordem para a equipe de serviço?');">
                                @csrf
                                <button type="submit"
                                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                    Encaminhar
                                </button>
                            </form>
                        @endif

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
@endsection

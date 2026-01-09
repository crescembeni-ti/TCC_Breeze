@extends('layouts.dashboard')

@section('title', 'Ordens de Serviço')

@section('content')
<div class="bg-white shadow-sm rounded-lg p-6">

    {{-- TÍTULO + FILTROS --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-3xl font-bold text-[#358054]">
            Ordens de Serviço
        </h2>

        <div class="flex gap-2">
            <a href="{{ route('admin.os.index', ['destino' => 'analista']) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition
               {{ $destino === 'analista'
                    ? 'bg-green-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Enviadas para Analistas
            </a>

            <a href="{{ route('admin.os.index', ['destino' => 'servico']) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition
               {{ $destino === 'servico'
                    ? 'bg-green-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Enviadas para Serviço
            </a>
        </div>
    </div>

    @if($oss->isEmpty())
        <div class="text-center py-10 bg-gray-50 rounded-lg">
            <p class="text-gray-500">
                Nenhuma ordem de serviço encontrada neste filtro.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#OS</th>
                        
                        {{-- CABEÇALHO DINÂMICO --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            @if($destino === 'analista')
                                Analista Responsável
                            @elseif($destino === 'servico')
                                Responsável pelo Serviço
                            @else
                                Solicitante (Cidadão)
                            @endif
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destino</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($oss as $os)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-gray-700">#{{ $os->id }}</td>

                        {{-- CÉLULA DINÂMICA COM O NOME DA PESSOA --}}
                        <td class="px-6 py-4">
                            @if($destino === 'analista')
                                {{-- Nome do Analista --}}
                                <span class="font-bold text-[#358054]">
                                    {{ $os->analyst->name ?? 'Aguardando Atribuição' }}
                                </span>

                            @elseif($destino === 'servico')
                                {{-- Nome do Responsável pelo Serviço (CORRIGIDO) --}}
                                <span class="font-bold text-blue-600">
                                    {{-- Usa o relacionamento 'service' do seu Model --}}
                                    {{ $os->service->name ?? 'Aguardando Atribuição' }}
                                </span>

                            @else
                                {{-- Nome do Cidadão (Visão Geral) --}}
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $os->contact->nome_solicitante }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Protocolo: #{{ $os->contact->id }}
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $os->contact->topico }}
                        </td>

                        <td class="px-6 py-4 text-sm font-semibold">
                            @if($os->flow === 'analista')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Analista
                                </span>
                            @elseif($os->flow === 'servico')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    Serviço
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 flex gap-2">
                            {{-- VISUALIZAR --}}
                            <a href="{{ route('admin.os.show', $os->id) }}"
                               class="text-[#358054] hover:text-green-900 border border-[#358054] px-3 py-1 rounded hover:bg-green-50 text-sm font-medium transition">
                                Visualizar
                            </a>

                            {{-- CANCELAR --}}
                            <form method="POST"
                                  action="{{ route('admin.os.cancelar', $os->id) }}"
                                  onsubmit="return confirm('Deseja cancelar esta ordem e retorná-la para a etapa anterior?');">
                                @csrf
                                @method('PUT')

                                <button class="text-red-600 hover:text-red-900 border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-1 rounded text-sm font-medium transition">
                                    Cancelar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
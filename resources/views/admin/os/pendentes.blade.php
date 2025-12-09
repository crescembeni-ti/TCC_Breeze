@extends('layouts.dashboard')

@section('title', 'Ordens de Serviço Pendentes')

@section('content')
<div class="bg-white p-8 rounded-lg shadow">

    <h2 class="text-3xl font-bold text-[#358054] mb-6">
        Ordens de Serviço Pendentes
    </h2>

    @if($os->isEmpty())
        <p class="text-gray-600 text-lg">Nenhuma OS pendente no momento.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded-lg shadow">
                <thead>
                    <tr class="bg-[#358054] text-white">
                        <th class="px-4 py-3">Solicitante</th>
                        <th class="px-4 py-3">Endereço</th>
                        <th class="px-4 py-3">Data da Vistoria</th>
                        <th class="px-4 py-3">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($os as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $item->nome_solicitante }}</td>
                        <td class="px-4 py-3">
                            {{ $item->rua }}, {{ $item->numero }} - {{ $item->bairro }}
                        </td>
                        <td class="px-4 py-3">
                            {{ \Carbon\Carbon::parse($item->data_vistoria)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST"
                                  action="{{ route('admin.os.enviar', $item->id) }}">
                                @csrf

                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                                    Enviar para Equipe de Serviço
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

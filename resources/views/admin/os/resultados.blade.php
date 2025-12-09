@extends('layouts.dashboard')

@section('title', 'Resultados de Execução')

@section('content')
<div class="bg-white p-8 rounded-lg shadow">

    <h2 class="text-3xl font-bold text-[#358054] mb-6">
        Resultados das Ordens de Serviço
    </h2>

    @if($os->isEmpty())
        <p class="text-gray-600 text-lg">Nenhuma OS concluída ou recusada.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded-lg shadow">
                <thead>
                    <tr class="bg-[#358054] text-white">
                        <th class="px-4 py-3">Solicitante</th>
                        <th class="px-4 py-3">Endereço</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Justificativa / Observações</th>
                        <th class="px-4 py-3">Data Execução</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($os as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $item->nome_solicitante }}</td>

                        <td class="px-4 py-3">
                            {{ $item->rua }}, {{ $item->numero }} - {{ $item->bairro }}
                        </td>

                        <td class="px-4 py-3 font-semibold">
                            @if($item->status->name == 'Concluído')
                                <span class="text-green-700">Concluído</span>
                            @else
                                <span class="text-red-600">Indeferido</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            {{ $item->justificativa ?? '---' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $item->data_execucao ? \Carbon\Carbon::parse($item->data_execucao)->format('d/m/Y') : "---" }}
                        </td>

                    </tr>
                    @endforeach
                </tbody>

            </table>

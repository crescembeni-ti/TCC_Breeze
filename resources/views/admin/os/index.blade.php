@extends('layouts.dashboard')

@section('title', 'Ordens de Serviço')

@section('content')
<div class="bg-white shadow-sm rounded-lg p-6">
    <h2 class="text-3xl font-bold text-[#358054] mb-4">Ordens de Serviço</h2>

    @if($oss->isEmpty())
        <p class="text-gray-600">Nenhuma OS gerada ainda.</p>
    @else
        <table class="min-w-full divide-y divide-gray-200 mt-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#OS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Vistoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($oss as $os)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">#{{ $os->id }}</td>
                    <td class="px-6 py-4">{{ $os->contact->nome_solicitante }}</td>
                     <td>{{ $os->contact->topico }}</td>
                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($os->created_at)->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('dashboard.os.show', $os->id) }}" class="text-[#358054] font-bold border border-[#358054] px-3 py-1 rounded hover:bg-green-50">Visualizar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

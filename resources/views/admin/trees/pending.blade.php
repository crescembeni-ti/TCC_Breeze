@extends('layouts.dashboard')

@section('content')
<div class="p-6">
    
    {{-- CABEÇALHO COM FUNDO VERDE --}}
    <div class="bg-[#358054] p-6 rounded-lg shadow-md mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            🌳 Árvores Pendentes de Aprovação
        </h2>
        <a href="{{ route('admin.dashboard') }}" class="bg-white text-[#358054] px-4 py-2 rounded-lg font-bold hover:bg-gray-100 transition shadow-sm text-sm flex items-center gap-2">
            ⬅ Voltar ao Painel
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($pendingTrees->isEmpty())
            <div class="p-10 text-center text-gray-500">
                <div class="mb-4 text-6xl">🎉</div>
                <p class="text-xl font-semibold">Tudo limpo!</p>
                <p class="text-sm">Nenhuma árvore pendente de aprovação no momento.</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastrado Por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espécie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Cadastro</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingTrees as $tree)
                        <tr class="hover:bg-gray-50 transition">
                            {{-- QUEM CADASTROU --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tree->analyst)
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                        👤 {{ $tree->analyst->name }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                        🖥️ Sistema
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $tree->species->name ?? 'Desconhecida' }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $tree->id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 truncate max-w-xs" title="{{ $tree->address }}">
                                    {{ Str::limit($tree->address, 35) }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $tree->bairro->nome ?? 'Bairro não informado' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $tree->created_at->format('d/m/Y') }} <br>
                                <span class="text-xs">{{ $tree->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    {{-- Ver --}}
                                    <a href="{{ route('admin.trees.edit', $tree->id) }}" target="_blank" 
                                       class="text-blue-600 hover:text-blue-900 bg-blue-50 border border-blue-200 px-3 py-1 rounded-md text-xs font-bold transition hover:bg-blue-100">
                                        👁️ Ver
                                    </a>

                                    {{-- Aprovar --}}
                                    <form action="{{ route('admin.trees.approve', $tree->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja aprovar e publicar esta árvore?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="text-white bg-green-600 hover:bg-green-700 border border-green-700 px-3 py-1 rounded-md text-xs font-bold shadow-sm transition transform hover:scale-105">
                                            ✅ Aprovar
                                        </button>
                                    </form>
                                    
                                    {{-- Recusar --}}
                                    <form action="{{ route('admin.trees.destroy', $tree->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Isso excluirá a árvore permanentemente. Confirmar recusa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:bg-red-50 border border-red-200 px-3 py-1 rounded-md text-xs font-bold transition">
                                            ❌ Recusar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
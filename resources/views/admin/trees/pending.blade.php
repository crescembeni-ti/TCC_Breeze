@extends('layouts.dashboard')

@section('content')
<div class="p-6">
    
    {{-- CABEÇALHO COM FUNDO VERDE --}}
    <div class="bg-[#358054] p-4 sm:p-6 rounded-lg shadow-md mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2 text-center sm:text-left">
            🌳 Árvores Pendentes de Aprovação
        </h2>
        <a href="{{ route('admin.dashboard') }}" class="bg-white text-[#358054] px-4 py-2 rounded-lg font-bold hover:bg-gray-100 transition shadow-sm text-sm flex items-center gap-2 w-full sm:w-auto justify-center">
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
            {{-- Adicionado: flex flex-col items-center justify-center --}}
            <div class="p-12 text-center text-gray-500 flex flex-col items-center justify-center">
                
                {{-- Ícone aumentado (w-16 h-16) e com margem inferior (mb-4) --}}
                <div class="bg-green-50 rounded-full p-4 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#358054]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h3 class="text-xl font-bold text-gray-800">Tudo limpo!</h3>
                <p class="text-sm text-gray-500 mt-1">Nenhuma árvore pendente de aprovação no momento.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastrado Por</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espécie</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Localização</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Data</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingTrees as $tree)
                            <tr class="hover:bg-gray-50 transition">
                                {{-- QUEM CADASTROU --}}
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    @if($tree->analyst)
                                        <span class="px-2 py-0.5 inline-flex text-[10px] sm:text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                            👤 {{ Str::limit($tree->analyst->name, 10) }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 inline-flex text-[10px] sm:text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                            🖥️ Sistema
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $tree->vulgar_name ?? 'Desconhecida' }}</div>
                                    <div class="text-[10px] text-gray-500 md:hidden">{{ Str::limit($tree->address, 20) }}</div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                    <div class="text-sm text-gray-900 truncate max-w-xs" title="{{ $tree->address }}">
                                        {{ Str::limit($tree->address, 35) }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $tree->bairro->nome ?? 'Bairro não informado' }}</div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                    {{ $tree->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-1 sm:gap-2">
                                        {{-- Ver --}}
                                        <a href="{{ route('admin.trees.edit', $tree->id) }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-900 bg-blue-50 border border-blue-200 px-2 sm:px-3 py-1 rounded-md text-[10px] sm:text-xs font-bold transition">
                                            👁️Ver
                                        </a>

                                        {{-- Aprovar --}}
                                        <form action="{{ route('admin.trees.approve', $tree->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja aprovar e publicar esta árvore?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="text-white bg-green-600 hover:bg-green-700 border border-green-700 px-2 sm:px-3 py-1 rounded-md text-[10px] sm:text-xs font-bold shadow-sm transition">
                                                ✅Aprovar
                                            </button>
                                        </form>
                                        
                                        {{-- Recusar --}}
                                        <form action="{{ route('admin.trees.destroy', $tree->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Isso excluirá a árvore permanentemente. Confirmar recusa?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:bg-red-50 border border-red-200 px-2 sm:px-3 py-1 rounded-md text-[10px] sm:text-xs font-bold transition">
                                                ❌Reprovar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
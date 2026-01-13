@extends('layouts.dashboard') {{-- Ou layouts.dashboard, verifique qual seu layout de admin usa --}}

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">🌳 Árvores Pendentes de Aprovação</h2>
        <a href="{{ route('admin.map') }}" class="text-sm text-green-600 hover:underline">Voltar ao Mapa</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($pendingTrees->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="text-lg">Nenhuma árvore pendente no momento. 🎉</p>
                <p class="text-sm">Todas as solicitações já foram moderadas.</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espécie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Cadastro</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingTrees as $tree)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $tree->species->name ?? 'Desconhecida' }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $tree->id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ Str::limit($tree->address, 40) }}</div>
                                <div class="text-xs text-gray-500">{{ $tree->bairro->nome ?? 'Bairro não informado' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $tree->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    {{-- Botão Ver/Editar (Abre a edição normal em nova aba) --}}
                                    <a href="{{ route('admin.trees.edit', $tree->id) }}" target="_blank" 
                                       class="text-blue-600 hover:text-blue-900 border border-blue-200 bg-blue-50 px-3 py-1 rounded">
                                        Ver Detalhes
                                    </a>

                                    {{-- Botão APROVAR --}}
                                    <form action="{{ route('admin.trees.approve', $tree->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja aprovar e publicar esta árvore?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded shadow-sm transition">
                                            ✅ Aprovar
                                        </button>
                                    </form>
                                    
                                    {{-- Botão EXCLUIR (Caso seja spam) --}}
                                    <form action="{{ route('admin.trees.destroy', $tree->id) }}" method="POST" onsubmit="return confirm('Recusar e excluir permanentemente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 ml-2 text-xs underline">
                                            Recusar
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
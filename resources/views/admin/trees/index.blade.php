@extends('layouts.dashboard')

@section('content')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gerenciar Árvores - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/dashboard.css')

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">


    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-1 p-4 md:p-10">
        <div class="bg-white shadow-sm rounded-lg p-4 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-[#358054]">Gerenciar Árvores</h2>
                    <p class="text-gray-600 mt-1">Consulte e gerencie o inventário de árvores cadastradas.</p>
                </div>
                

            </div>

            {{-- ÁREA DE FILTROS --}}
            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8">
                <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Filtro por ID --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">ID da Árvore</label>
                        <input type="number" name="id" value="{{ request('id') }}" placeholder="Ex: 123" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#358054] focus:ring-[#358054] text-sm">
                    </div>

                    {{-- Filtro por Bairro --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Bairro</label>
                        <select name="bairro" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#358054] focus:ring-[#358054] text-sm">
                            <option value="">Todos os Bairros</option>
                            @foreach($bairros as $b)
                                <option value="{{ $b->nome }}" {{ request('bairro') == $b->nome ? 'selected' : '' }}>
                                    {{ $b->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Ordenação --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Ordenar por</label>
                        <select name="sort" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#358054] focus:ring-[#358054] text-sm">
                            <option value="id_desc" {{ request('sort') == 'id_desc' ? 'selected' : '' }}>ID (Mais recente)</option>
                            <option value="id_asc" {{ request('sort') == 'id_asc' ? 'selected' : '' }}>ID (Mais antigo)</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nome Científico (A-Z)</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nome Científico (Z-A)</option>
                        </select>
                    </div>

                    {{-- Botões --}}
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-[#358054] text-white px-4 py-2 rounded-lg hover:bg-[#2d6947] transition font-bold text-sm shadow-sm flex items-center justify-center gap-2">
                            <i data-lucide="search" class="w-4 h-4"></i> Filtrar
                        </button>
                        <a href="{{ url()->current() }}" class="p-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 transition shadow-sm" title="Limpar Filtros">
                            <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID</th>
                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nome Científico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Endereço</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($trees as $tree)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tree->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{-- Tenta mostrar o scientific_name da árvore. Se vazio, tenta o da espécie vinculada --}}
                                    {{ $tree->scientific_name ?: optional($tree->species)->scientific_name ?: 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $tree->address ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.trees.edit', $tree) }}"
                                        class="text-[#358054] hover:text-[#a0c520] font-semibold">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center py-12">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="tree-pine" class="w-12 h-12 mb-2 opacity-20"></i>
                                        <p class="text-lg font-medium">Nenhuma árvore encontrada</p>
                                        <p class="text-sm">Tente ajustar seus filtros para encontrar o que procura.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>


    <script>
        lucide.createIcons();
    </script>
</body>

</html>
@endsection

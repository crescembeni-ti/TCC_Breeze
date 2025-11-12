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

    <!-- HEADER -->
    <header class="site-header flex items-center justify-between px-8 py-4 shadow-md bg-white">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-20 w-20 object-contain">
            <h1 class="text-4xl font-bold">
                <span class="text-[#358054]">Árvores de</span>
                <span class="text-[#a0c520]"> Paracambi</span>
            </h1>
        </div>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-1 p-10">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
                <h2 class="text-3xl font-bold text-[#358054]">Gerenciar Árvores 🌳</h2>

                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#358054] text-white rounded-lg text-sm font-semibold hover:bg-[#2d6947] transition">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Voltar ao Painel
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espécie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($trees as $tree)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tree->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $tree->species->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tree->address ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.trees.edit', $tree) }}" class="text-[#358054] hover:text-[#a0c520] font-semibold">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Nenhuma árvore cadastrada ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-gray-800 text-gray-300 text-center py-4 text-sm border-t border-[#358054] mt-auto">
        © {{ date('Y') }} - Árvores de Paracambi
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

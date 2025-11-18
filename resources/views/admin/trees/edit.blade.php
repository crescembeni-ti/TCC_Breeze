<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Árvore - Árvores de Paracambi</title>

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

    <!-- CONTEÚDO -->
    <main class="flex-1 p-10">

        <div class="bg-white shadow-sm rounded-lg p-8">

            <!-- Título + Voltar -->
            <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
                <h2 class="text-3xl font-bold text-[#358054]">Editar Árvore #{{ $tree->id }} 🌳</h2>

                <a href="{{ route('admin.trees.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#358054] text-white rounded-lg text-sm font-semibold hover:bg-[#2d6947] transition">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Voltar
                </a>
            </div>

            {{-- ERROS --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
                    <strong>Ops! Algo deu errado:</strong>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- FORMULÁRIO -->
            <form action="{{ route('admin.trees.update', $tree) }}" method="POST" class="space-y-10">
                @csrf
                @method('PATCH')


                <!-- SESSÃO: ESPÉCIE -->
                <div>
                    <h3 class="text-xl font-semibold text-[#358054] mb-4 flex items-center gap-2">
                        <i data-lucide="leaf" class="w-5 h-5"></i>
                        Informações da Espécie
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="font-medium">Espécie</label>
                            <select name="species_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach ($species as $spec)
                                    <option value="{{ $spec->id }}" {{ $tree->species_id == $spec->id ? 'selected' : '' }}>
                                        {{ $spec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="font-medium">Caso não tenha espécie</label>
                            <input type="text" name="no_species_case"
                                value="{{ old('no_species_case', $tree->no_species_case) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                    </div>
                </div>


                <!-- SESSÃO: DIMENSÕES -->
                <div>
                    <h3 class="text-xl font-semibold text-[#358054] mb-4 flex items-center gap-2">
                        <i data-lucide="ruler" class="w-5 h-5"></i>
                        Dimensões da Árvore
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        @php
                            $dimFields = [
                                'Diâmetro do Tronco (cm)' => 'trunk_diameter',
                                'CAP (cm)' => 'cap',
                                'Altura (m)' => 'height',
                                'Altura da Copa (m)' => 'crown_height',
                                'Copa Longitudinal (m)' => 'crown_diameter_longitudinal',
                                'Copa Perpendicular (m)' => 'crown_diameter_perpendicular',
                                'Largura total (m)' => 'total_width',
                                'Largura da rua (m)' => 'street_width',
                                'Altura da gola (m)' => 'gutter_height',
                                'Largura da gola (m)' => 'gutter_width',
                                'Comprimento da gola (m)' => 'gutter_length',
                            ];
                        @endphp

                        @foreach ($dimFields as $label => $name)
                            <div>
                                <label class="font-medium">{{ $label }}</label>
                                <input type="number" step="0.01" name="{{ $name }}"
                                    value="{{ old($name, $tree->$name) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        @endforeach

                    </div>
                </div>


                <!-- SESSÃO: CONDIÇÕES BIOLÓGICAS -->
                <div>
                    <h3 class="text-xl font-semibold text-[#358054] mb-4 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                        Condições Biológicas
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Tipo de bifurcação --}}
                        <div>
                            <label class="font-medium">Tipo de Bifurcação</label>
                            <select name="bifurcation_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecione</option>
                                <option value="ausente" {{ $tree->bifurcation_type == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                <option value="U" {{ $tree->bifurcation_type == 'U' ? 'selected' : '' }}>U</option>
                                <option value="V" {{ $tree->bifurcation_type == 'V' ? 'selected' : '' }}>V</option>
                            </select>
                        </div>

                        {{-- Equilíbrio fuste --}}
                        <div>
                            <label class="font-medium">Equilíbrio do Fuste (Inclinação)</label>
                            <select name="stem_balance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecione</option>
                                <option value="ausente" {{ $tree->stem_balance == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                <option value="maior_45" {{ $tree->stem_balance == 'maior_45' ? 'selected' : '' }}>Maior que 45°</option>
                                <option value="menor_45" {{ $tree->stem_balance == 'menor_45' ? 'selected' : '' }}>Menor que 45°</option>
                            </select>
                        </div>

                        {{-- Equilíbrio da copa --}}
                        <div>
                            <label class="font-medium">Equilíbrio da copa</label>
                            <select name="crown_balance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecione</option>
                                <option value="equilibrada" {{ $tree->crown_balance == 'equilibrada' ? 'selected' : '' }}>Equilibrada</option>
                                <option value="medianamente_desequilibrada" {{ $tree->crown_balance == 'medianamente_desequilibrada' ? 'selected' : '' }}>
                                    Medianamente desequilibrada
                                </option>
                                <option value="desequilibrada" {{ $tree->crown_balance == 'desequilibrada' ? 'selected' : '' }}>Desequilibrada</option>
                                <option value="muito_desequilibrada" {{ $tree->crown_balance == 'muito_desequilibrada' ? 'selected' : '' }}>
                                    Muito desequilibrada
                                </option>
                            </select>
                        </div>

                        {{-- Organismos --}}
                        <div>
                            <label class="font-medium">Organismos xilófagos/patogênicos</label>
                            <select name="organisms" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecione</option>
                                <option value="ausente" {{ $tree->organisms == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                <option value="infestacao_inicial" {{ $tree->organisms == 'infestacao_inicial' ? 'selected' : '' }}>
                                    Infestação Inicial
                                </option>
                            </select>
                        </div>

                        {{-- Fiação --}}
                        <div>
                            <label class="font-medium">Estado da fiação</label>
                            <select name="wiring_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecione</option>
                                <option value="pode_interferir" {{ $tree->wiring_status == 'pode_interferir' ? 'selected' : '' }}>Pode interferir</option>
                                <option value="interfere" {{ $tree->wiring_status == 'interfere' ? 'selected' : '' }}>Interfere</option>
                                <option value="nao_interfere" {{ $tree->wiring_status == 'nao_interfere' ? 'selected' : '' }}>Não interfere</option>
                            </select>
                        </div>

                    </div>
                </div>


                <!-- SESSÃO: INFORMAÇÕES ADICIONAIS -->
                <div>
                    <h3 class="text-xl font-semibold text-[#358054] mb-4 flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                        Informações Adicionais
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="font-medium">Nome vulgar / Gola</label>
                            <input type="text" name="vulgar_name"
                                value="{{ old('vulgar_name', $tree->vulgar_name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="font-medium">Nome científico</label>
                            <input type="text" name="scientific_name"
                                value="{{ old('scientific_name', $tree->scientific_name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="font-medium">Alvo</label>
                            <input type="text" name="target"
                                value="{{ old('target', $tree->target) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="font-medium">Injúrias mecânicas e cavidades</label>
                            <input type="text" name="injuries"
                                value="{{ old('injuries', $tree->injuries) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                    </div>
                </div>


                <!-- SESSÃO: LOCALIZAÇÃO -->
                <div>
                    <h3 class="text-xl font-semibold text-[#358054] mb-4 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                        Localização
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="md:col-span-2">
                            <label class="font-medium">Endereço</label>
                            <input type="text" name="address"
                                value="{{ old('address', $tree->address) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="font-medium">Latitude</label>
                            <input type="text" name="latitude"
                                value="{{ old('latitude', $tree->latitude) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="font-medium">Longitude</label>
                            <input type="text" name="longitude"
                                value="{{ old('longitude', $tree->longitude) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                    </div>
                </div>


                <!-- SESSÃO: STATUS -->
                <div>
                    <h3 class="text-xl font-semibold text-[#358054] mb-4 flex items-center gap-2">
                        <i data-lucide="heart-pulse" class="w-5 h-5"></i>
                        Status da Árvore
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="font-medium">Status de Saúde</label>
                            <select name="health_status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="good" {{ $tree->health_status == 'good' ? 'selected' : '' }}>Boa</option>
                                <option value="fair" {{ $tree->health_status == 'fair' ? 'selected' : '' }}>Razoável</option>
                                <option value="poor" {{ $tree->health_status == 'poor' ? 'selected' : '' }}>Ruim</option>
                            </select>
                        </div>

                        <div>
                            <label class="font-medium">Data de plantio</label>
                            <input type="date" name="planted_at"
                                value="{{ old('planted_at', $tree->planted_at?->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                    </div>
                </div>


                <!-- BOTÕES -->
                <div class="flex justify-between pt-8">

                    <a href="{{ route('admin.trees.index') }}"
                       class="px-4 py-2 bg-gray-200 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Voltar
                    </a>

                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        Salvar Alterações
                    </button>
                </div>

            </form>


            <!-- EXCLUIR -->
            <form action="{{ route('admin.trees.destroy', $tree) }}"
                method="POST"
                class="mt-6"
                onsubmit="return confirm('Tem certeza que deseja excluir esta árvore?');">
                @csrf
                @method('DELETE')

                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">
                    Excluir Árvore
                </button>
            </form>

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

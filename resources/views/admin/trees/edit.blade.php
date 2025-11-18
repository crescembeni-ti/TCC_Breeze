<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Árvore
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h3 class="text-lg font-semibold mb-4">Editando Árvore #{{ $tree->id }}</h3>

                {{-- ERROS --}}
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        <strong>Ops!</strong> Erros encontrados:
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORMULÁRIO DE EDIÇÃO (PATCH) --}}
                <form action="{{ route('admin.trees.update', $tree) }}" method="POST" class="mb-6">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- ESPÉCIE --}}
                        <div>
                            <label class="block font-medium">Espécie</label>
                            <select name="species_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach ($species as $spec)
                                    <option value="{{ $spec->id }}"
                                        {{ $tree->species_id == $spec->id ? 'selected' : '' }}>
                                        {{ $spec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                          {{-- Diâmetro do Tronco --}}
            <div class="flex flex-col">
                <label class="form-label">Diâmetro do Tronco (cm)</label>
                <input type="number" step="0.01" name="trunk_diameter" class="input">
            </div>

            {{-- Nome vulgar / Gola --}}
            <div class="flex flex-col">
                <label class="form-label">Nome vulgar / Gola</label>
                <input type="text" name="vulgar_name" class="input">
            </div>

            {{-- Nome científico --}}
            <div class="flex flex-col">
                <label class="form-label">Nome científico</label>
                <input type="text" name="scientific_name" class="input">
            </div>

            {{-- Circunferência na altura do peito (CAP) --}}
            <div class="flex flex-col">
                <label class="form-label">CAP (cm)</label>
                <input type="number" step="0.01" name="cap" class="input">
            </div>

            {{-- Altura --}}
            <div class="flex flex-col">
                <label class="form-label">Altura (m)</label>
                <input type="number" step="0.01" name="height" class="input">
            </div>

            {{-- Altura de copa --}}
            <div class="flex flex-col">
                <label class="form-label">Altura de copa (m)</label>
                <input type="number" step="0.01" name="crown_height" class="input">
            </div>

            {{-- Diâmetro de copa longitudinal --}}
            <div class="flex flex-col">
                <label class="form-label">Diâmetro de copa longitudinal (m)</label>
                <input type="number" step="0.01" name="crown_diameter_longitudinal" class="input">
            </div>

            {{-- Diâmetro de copa perpendicular --}}
            <div class="flex flex-col">
                <label class="form-label">Diâmetro de copa perpendicular (m)</label>
                <input type="number" step="0.01" name="crown_diameter_perpendicular" class="input">
            </div>

            {{-- Tipo de Bifurcação --}}
            <div class="flex flex-col">
                <label class="form-label">Tipo de Bifurcação</label>
                <select name="bifurcation_type" class="input">
                    <option value="">Selecione</option>
                    <option value="ausente">Ausente</option>
                    <option value="U">U</option>
                    <option value="V">V</option>
                </select>
            </div>

            {{-- Equilíbrio Fuste (Inclinação) --}}
            <div class="flex flex-col">
                <label class="form-label">Equilíbrio Fuste (Inclinação)</label>
                <select name="stem_balance" class="input">
                    <option value="">Selecione</option>
                    <option value="ausente">Ausente</option>
                    <option value="maior_45">Maior que 45°</option>
                    <option value="menor_45">Menor que 45°</option>
                </select>
            </div>

            {{-- Equilíbrio da copa --}}
            <div class="flex flex-col">
                <label class="form-label">Equilíbrio da copa</label>
                <select name="crown_balance" class="input">
                    <option value="">Selecione</option>
                    <option value="equilibrada">Equilibrada</option>
                    <option value="medianamente_desequilibrada">Medianamente desequilibrada</option>
                    <option value="desequilibrada">Desequilibrada</option>
                    <option value="muito_desequilibrada">Muito desequilibrada</option>
                </select>
            </div>

            {{-- Organismos xilófagos e/ou patogênicos --}}
            <div class="flex flex-col">
                <label class="form-label">Organismos xilófagos e/ou patogênicos</label>
                <select name="organisms" class="input">
                    <option value="">Selecione</option>
                    <option value="ausente">Ausente</option>
                    <option value="infestacao_inicial">Infestação Inicial</option>
                </select>
            </div>

            {{-- Alvo --}}
            <div class="flex flex-col">
                <label class="form-label">Alvo</label>
                <input type="text" name="target" class="input">
            </div>

            {{-- Injúrias mecânicas e cavidades --}}
            <div class="flex flex-col">
                <label class="form-label">Injúrias mecânicas e cavidades</label>
                <input type="text" name="injuries" class="input">
            </div>

            {{-- Estado da fiação --}}
            <div class="flex flex-col">
                <label class="form-label">Estado da fiação</label>
                <select name="wiring_status" class="input">
                    <option value="">Selecione</option>
                    <option value="pode_interferir">Pode interferir</option>
                    <option value="interfere">Interfere</option>
                    <option value="nao_interfere">Não interfere</option>
                </select>
            </div>

            {{-- Largura total (muro a muro) --}}
            <div class="flex flex-col">
                <label class="form-label">Largura total (Muro a Muro) (m)</label>
                <input type="number" step="0.01" name="total_width" class="input">
            </div>

            {{-- Largura da rua (sarjeta a sarjeta) --}}
            <div class="flex flex-col">
                <label class="form-label">Largura da rua (Sarjeta a Sarjeta) (m)</label>
                <input type="number" step="0.01" name="street_width" class="input">
            </div>

            {{-- Altura da gola --}}
            <div class="flex flex-col">
                <label class="form-label">Altura da gola (m)</label>
                <input type="number" step="0.01" name="gutter_height" class="input">
            </div>

            {{-- Largura da gola --}}
            <div class="flex flex-col">
                <label class="form-label">Largura da gola (m)</label>
                <input type="number" step="0.01" name="gutter_width" class="input">
            </div>

            {{-- Comprimento da gola --}}
            <div class="flex flex-col">
                <label class="form-label">Comprimento da gola (m)</label>
                <input type="number" step="0.01" name="gutter_length" class="input">
            </div>

            {{-- Caso não tenha espécie --}}
            <div class="flex flex-col">
                <label class="form-label">Caso não tenha espécie</label>
                <input type="text" name="no_species_case" class="input">
            </div>
          

                        {{-- ENDEREÇO --}}
                        <div>
                            <label class="block font-medium">Endereço</label>
                            <input type="text" name="address"
                                   value="{{ old('address', $tree->address) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        {{-- LAT --}}
                        <div>
                            <label class="block font-medium">Latitude</label>
                            <input type="text" name="latitude"
                                   value="{{ old('latitude', $tree->latitude) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        {{-- LNG --}}
                        <div>
                            <label class="block font-medium">Longitude</label>
                            <input type="text" name="longitude"
                                   value="{{ old('longitude', $tree->longitude) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        {{-- STATUS DE SAÚDE --}}
                        <div>
                            <label class="block font-medium">Status de Saúde</label>
                            <select name="health_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="good" {{ $tree->health_status=='good'?'selected':'' }}>Boa</option>
                                <option value="fair" {{ $tree->health_status=='fair'?'selected':'' }}>Razoável</option>
                                <option value="poor" {{ $tree->health_status=='poor'?'selected':'' }}>Ruim</option>
                            </select>
                        </div>

                        {{-- DIAMETRO --}}
                        <div>
                            <label class="block font-medium">Diâmetro do Tronco (cm)</label>
                            <input type="number" step="0.01" name="trunk_diameter"
                                   value="{{ old('trunk_diameter', $tree->trunk_diameter) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        {{-- DATA --}}
                        <div class="md:col-span-2">
                            <label class="block font-medium">Data de Plantio</label>
                            <input type="date" name="planted_at"
                                   value="{{ old('planted_at', $tree->planted_at->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                    </div>

                    {{-- BOTÕES --}}
                    <div class="mt-6 flex justify-between">

                        <a href="{{ route('admin.trees.index') }}"
                           class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">
                           Voltar
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Salvar Alterações
                        </button>

                    </div>
                </form>


                {{-- FORMULÁRIO DE EXCLUSÃO (DELETE) - SEPARADO --}}
                <form action="{{ route('admin.trees.destroy', $tree) }}"
                      method="POST"
                      onsubmit="return confirm('Tem certeza que deseja excluir esta árvore?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Excluir Árvore
                    </button>
                </form>

            </div>

        </div>
    </div>

</body>
</html>

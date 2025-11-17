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

</x-app-layout>

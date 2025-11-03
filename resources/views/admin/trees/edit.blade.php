<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Árvore') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-semibold mb-4">Editando Árvore #{{ $tree->id }}</h3>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <strong>Ops!</strong> Havia algo errado com os dados:
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.trees.update', $tree) }}" method="POST">
                        @csrf @method('PATCH') <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label for="species_id" class="block text-sm font-medium text-gray-700">Espécie</label>
                                <select id="species_id" name="species_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($species as $spec)
                                        <option value="{{ $spec->id }}" {{ $tree->species_id == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Endereço</label>
                                <input type="text" name="address" id="address" value="{{ old('address', $tree->address) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                                <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $tree->latitude) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                                <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $tree->longitude) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="health_status" class="block text-sm font-medium text-gray-700">Status de Saúde</label>
                                <select id="health_status" name="health_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="good" {{ old('health_status', $tree->health_status) == 'good' ? 'selected' : '' }}>Boa</option>
                                    <option value="fair" {{ old('health_status', $tree->health_status) == 'fair' ? 'selected' : '' }}>Razoável</option>
                                    <option value="poor" {{ old('health_status', $tree->health_status) == 'poor' ? 'selected' : '' }}>Ruim</option>
                                </select>
                            </div>

                            <div>
                                <label for="trunk_diameter" class="block text-sm font-medium text-gray-700">Diâmetro do Tronco (cm)</label>
                                <input type="number" step="0.1" name="trunk_diameter" id="trunk_diameter" value="{{ old('trunk_diameter', $tree->trunk_diameter) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-2">
                                <label for="planted_at" class="block text-sm font-medium text-gray-700">Data de Plantio</Tabel>
                                <input type="date" name="planted_at" id="planted_at" value="{{ old('planted_at', $tree->planted_at->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end space-x-4">
                            <a href="{{ route('admin.trees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700">
                                Salvar Alterações
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
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
                            <strong>Ops!</strong> Algo está errado.
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <form method="POST" action="{{ route('admin.trees.update', $tree) }}">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Espécie --}}
                            <div>
                                <label class="form-label">Espécie *</label>
                                <select name="species_id" class="input" required>
                                    @foreach($species as $spec)
                                        <option value="{{ $spec->id }}"
                                            {{ old('species_id', $tree->species_id) == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Diâmetro --}}
                            <div>
                                <label class="form-label">Diâmetro do Tronco (cm) *</label>
                                <input type="number" step="0.01" name="trunk_diameter"
                                       class="input"
                                       value="{{ old('trunk_diameter', $tree->trunk_diameter) }}" required>
                            </div>

                            {{-- Nome vulgar --}}
                            <div>
                                <label class="form-label">Nome vulgar / Gola *</label>
                                <input type="text" name="vulgar_name" class="input"
                                       value="{{ old('vulgar_name', $tree->vulgar_name) }}" required>
                            </div>

                            {{-- Nome científico --}}
                            <div>
                                <label class="form-label">Nome científico *</label>
                                <input type="text" name="scientific_name" class="input"
                                       value="{{ old('scientific_name', $tree->scientific_name) }}" required>
                            </div>

                            {{-- CAP --}}
                            <div>
                                <label class="form-label">CAP (cm) *</label>
                                <input type="number" step="0.01" name="cap"
                                       class="input"
                                       value="{{ old('cap', $tree->cap) }}" required>
                            </div>

                            {{-- Altura --}}
                            <div>
                                <label class="form-label">Altura (m) *</label>
                                <input type="number" step="0.01" name="height"
                                       class="input"
                                       value="{{ old('height', $tree->height) }}" required>
                            </div>

                            {{-- Altura de copa --}}
                            <div>
                                <label class="form-label">Altura de copa (m) *</label>
                                <input type="number" step="0.01" name="crown_height"
                                       class="input"
                                       value="{{ old('crown_height', $tree->crown_height) }}" required>
                            </div>

                            {{-- Copa Longitudinal --}}
                            <div>
                                <label class="form-label">Diâmetro de copa longitudinal (m) *</label>
                                <input type="number" step="0.01" name="crown_diameter_longitudinal"
                                       class="input"
                                       value="{{ old('crown_diameter_longitudinal', $tree->crown_diameter_longitudinal) }}">
                            </div>

                            {{-- Copa Perp --}}
                            <div>
                                <label class="form-label">Diâmetro de copa perpendicular (m) *</label>
                                <input type="number" step="0.01" name="crown_diameter_perpendicular"
                                       class="input"
                                       value="{{ old('crown_diameter_perpendicular', $tree->crown_diameter_perpendicular) }}">
                            </div>

                            {{-- Tipo de bifurcação --}}
                            <div>
                                <label class="form-label">Tipo de Bifurcação *</label>
                                <select name="bifurcation_type" class="input" required>
                                    <option value="ausente" {{ old('bifurcation_type', $tree->bifurcation_type) == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                    <option value="U" {{ old('bifurcation_type', $tree->bifurcation_type) == 'U' ? 'selected' : '' }}>U</option>
                                    <option value="V" {{ old('bifurcation_type', $tree->bifurcation_type) == 'V' ? 'selected' : '' }}>V</option>
                                </select>
                            </div>

                            {{-- Stem --}}
                            <div>
                                <label class="form-label">Equilíbrio Fuste *</label>
                                <select name="stem_balance" class="input">
                                    <option value="ausente" {{ old('stem_balance', $tree->stem_balance) == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                    <option value="maior_45" {{ old('stem_balance', $tree->stem_balance) == 'maior_45' ? 'selected' : '' }}>Maior que 45°</option>
                                    <option value="menor_45" {{ old('stem_balance', $tree->stem_balance) == 'menor_45' ? 'selected' : '' }}>Menor que 45°</option>
                                </select>
                            </div>

                            {{-- Crown --}}
                            <div>
                                <label class="form-label">Equilíbrio da copa *</label>
                                <select name="crown_balance" class="input" required>
                                    <option value="equilibrada" {{ old('crown_balance', $tree->crown_balance) == 'equilibrada' ? 'selected' : '' }}>Equilibrada</option>
                                    <option value="medianamente_desequilibrada" {{ old('crown_balance', $tree->crown_balance) == 'medianamente_desequilibrada' ? 'selected' : '' }}>Medianamente desequilibrada</option>
                                    <option value="desequilibrada" {{ old('crown_balance', $tree->crown_balance) == 'desequilibrada' ? 'selected' : '' }}>Desequilibrada</option>
                                    <option value="muito_desequilibrada" {{ old('crown_balance', $tree->crown_balance) == 'muito_desequilibrada' ? 'selected' : '' }}>Muito desequilibrada</option>
                                </select>
                            </div>

                            {{-- Organismos --}}
                            <div>
                                <label class="form-label">Organismos *</label>
                                <select name="organisms" class="input">
                                    <option value="ausente" {{ old('organisms', $tree->organisms) == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                    <option value="infestacao_inicial" {{ old('organisms', $tree->organisms) == 'infestacao_inicial' ? 'selected' : '' }}>Infestação Inicial</option>
                                </select>
                            </div>

                            {{-- Alvo --}}
                            <div>
                                <label class="form-label">Alvo *</label>
                                <input type="text" name="target" class="input"
                                       value="{{ old('target', $tree->target) }}" required>
                            </div>

                            {{-- Injúrias --}}
                            <div>
                                <label class="form-label">Injúrias *</label>
                                <input type="text" name="injuries" class="input"
                                       value="{{ old('injuries', $tree->injuries) }}" required>
                            </div>

                            {{-- Fiação --}}
                            <div>
                                <label class="form-label">Estado da fiação *</label>
                                <select name="wiring_status" class="input" required>
                                    <option value="pode_interferir" {{ old('wiring_status', $tree->wiring_status) == 'pode_interferir' ? 'selected' : '' }}>Pode interferir</option>
                                    <option value="interfere" {{ old('wiring_status', $tree->wiring_status) == 'interfere' ? 'selected' : '' }}>Interfere</option>
                                    <option value="nao_interfere" {{ old('wiring_status', $tree->wiring_status) == 'nao_interfere' ? 'selected' : '' }}>Não interfere</option>
                                </select>
                            </div>

                            {{-- Medidas --}}
                            <div>
                                <label class="form-label">Largura total *</label>
                                <input type="number" step="0.01" name="total_width"
                                       class="input"
                                       value="{{ old('total_width', $tree->total_width) }}" required>
                            </div>

                            <div>
                                <label class="form-label">Largura da rua *</label>
                                <input type="number" step="0.01" name="street_width"
                                       class="input"
                                       value="{{ old('street_width', $tree->street_width) }}" required>
                            </div>

                            <div>
                                <label class="form-label">Altura da gola *</label>
                                <input type="number" step="0.01" name="gutter_height"
                                       class="input"
                                       value="{{ old('gutter_height', $tree->gutter_height) }}" required>
                            </div>

                            <div>
                                <label class="form-label">Largura da gola *</label>
                                <input type="number" step="0.01" name="gutter_width"
                                       class="input"
                                       value="{{ old('gutter_width', $tree->gutter_width) }}" required>
                            </div>

                            <div>
                                <label class="form-label">Comprimento da gola *</label>
                                <input type="number" step="0.01" name="gutter_length"
                                       class="input"
                                       value="{{ old('gutter_length', $tree->gutter_length) }}" required>
                            </div>

                            {{-- Endereço --}}
                            <div>
                                <label class="form-label">Endereço *</label>
                                <input type="text" name="address" class="input"
                                       value="{{ old('address', $tree->address) }}" required>
                            </div>

                            {{-- Latitude --}}
                            <div>
                                <label class="form-label">Latitude *</label>
                                <input type="number" step="0.0000001" name="latitude"
                                       class="input"
                                       value="{{ old('latitude', $tree->latitude) }}" required>
                            </div>

                            {{-- Longitude --}}
                            <div>
                                <label class="form-label">Longitude *</label>
                                <input type="number" step="0.0000001" name="longitude"
                                       class="input"
                                       value="{{ old('longitude', $tree->longitude) }}" required>
                            </div>

                            {{-- Data --}}
                            <div>
                                <label class="form-label">Data de Plantio *</label>
                                <input type="date" name="planted_at"
                                       class="input"
                                       max="{{ now()->format('Y-m-d') }}"
                                       value="{{ old('planted_at', $tree->planted_at?->format('Y-m-d')) }}" required>
                            </div>

                            {{-- Caso não tenha espécie --}}
                            <div>
                                <label class="form-label">Caso não tenha espécie</label>
                                <input type="text" name="no_species_case" class="input"
                                       value="{{ old('no_species_case', $tree->no_species_case) }}">
                            </div>

                        </div>


                        <div class="mt-6 flex justify-end space-x-4">

                            <a href="{{ route('admin.trees.index') }}"
                               class="px-4 py-2 bg-gray-200 rounded-md">
                                Cancelar
                            </a>

                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md">
                                Salvar alterações
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

</x-app-layout>

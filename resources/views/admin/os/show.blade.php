@extends('layouts.dashboard')

@section('title', 'Ordem de Serviço')

@section('content')

@php
    // Detecta se é Admin para habilitar edição. Se não for, fica estático.
    $podeEditar = auth()->guard('admin')->check();

    $todosMotivos = [
        'Risco de Queda' => 'Risco de queda',
        'Conflito rede eletrica' => 'Conflito com rede elétrica',
        'Danos infraestrutura' => 'Danos à infraestrutura',
        'Outras' => 'Outras razões',
    ];

    $todosServicos = [
        'Levantamento copa' => 'Poda de levantamento de copa',
        'Desobstrucao' => 'Poda de desobstrução de rede',
        'Limpeza' => 'Poda de limpeza',
        'Adequacao' => 'Poda de adequação',
        'Remocao Total' => 'Remoção total da árvore',
        'Outras' => 'Outras intervenções',
    ];

    $todosEquipamentos = [
        'Motosserra' => 'Motosserra',
        'Motopoda' => 'Motopoda',
        'EPIs' => 'EPIs',
        'Cordas' => 'Cordas',
        'Cones' => 'Cones',
        'Caminhão' => 'Caminhão',
    ];
@endphp

<div class="max-w-4xl mx-auto bg-white p-8 shadow-2xl rounded-lg border-t-8 border-[#358054] relative overflow-hidden print:border-0 print:shadow-none print:p-0 print:max-w-full">

    {{-- MARCA D'ÁGUA --}}
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
        <img src="{{ asset('images/logo.png') }}" alt="Marca D'água" class="w-2/3 object-contain opacity-15 print:w-1/2">
    </div>

    <div class="relative z-10">

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 border-l-4 border-green-500 text-green-700 font-bold print:hidden">
                {{ session('success') }}
            </div>
        @endif

        {{-- Abre o formulário APENAS se for Admin --}}
        @if($podeEditar)
        <form action="{{ route('admin.os.update', $os->id) }}" method="POST">
            @csrf
            @method('PUT')
        @endif

            <div class="flex justify-between items-center mb-6 border-b pb-4 print:mb-2 print:pb-2">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto object-contain print:h-12" alt="Logo">
                    <div class="text-xs text-gray-600 leading-tight font-bold uppercase text-left">
                        ESTADO DO RIO DE JANEIRO<br>
                        MUNICÍPIO DE PARACAMBI<br>
                        SECRETARIA MUNICIPAL DE MEIO AMBIENTE<br>
                        SUPER INTENDÊNCIA DE ÁREAS VERDES<br>
                        DIRETORIA DE ARBORIZAÇÃO URBANA
                    </div>
                </div>
                <div class="text-right">
                    <h3 class="text-lg font-bold text-gray-800 uppercase border-b-2 border-black">ORDEM DE SERVIÇO</h3>
                    <p class="text-sm font-bold mt-1">Poda e Remoção de Árvores</p>
                </div>
            </div>

            <div class="text-sm text-left">
                <div class="grid grid-cols-2 gap-4 mb-4 border-b border-gray-300 pb-2">
                    <div>
                        <label class="font-bold block text-gray-700">Nº Solicitação:</label>
                        <p class="w-full border-b border-gray-400 bg-gray-50/80 p-1 font-medium text-gray-900">{{ $os->contact->id }}</p>
                    </div>
                    <div>
                        <label class="font-bold block text-gray-700">Data:</label>
                        <p class="w-full border-b border-gray-400 bg-gray-50/80 p-1 font-medium text-gray-900">{{ \Carbon\Carbon::parse($os->contact->created_at)->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="mb-4 border-b border-gray-300 pb-2">
                    <h4 class="font-bold underline mb-1 uppercase">Identificação da Área</h4>
                    <div class="grid grid-cols-1 gap-2">
                        <div class="mb-2">
                            <span class="text-gray-600 font-semibold">Endereço:</span>
                            <span class="ml-1 font-medium text-gray-900">{{ $os->contact->rua }}, {{ $os->contact->numero }} - {{ $os->contact->bairro }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-gray-600 font-semibold block">Latitude:</label>
                                @if($podeEditar)
                                    <input type="text" name="latitude" value="{{ old('latitude', $os->latitude) }}" class="w-full border-0 border-b border-gray-400 bg-gray-50/50 p-1 focus:ring-0 focus:border-[#358054]">
                                @else
                                    <p class="p-1 border-b border-gray-200 font-medium text-gray-900">{{ $os->latitude ?? 'N/R' }}</p>
                                @endif
                            </div>
                            <div>
                                <label class="text-gray-600 font-semibold block">Longitude:</label>
                                @if($podeEditar)
                                    <input type="text" name="longitude" value="{{ old('longitude', $os->longitude) }}" class="w-full border-0 border-b border-gray-400 bg-gray-50/50 p-1 focus:ring-0 focus:border-[#358054]">
                                @else
                                    <p class="p-1 border-b border-gray-200 font-medium text-gray-900">{{ $os->longitude ?? 'N/R' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4 border-b border-gray-300 pb-2">
                    <div class="col-span-2">
                        <label class="font-bold block text-gray-700">Espécie(s):</label>
                        @if($podeEditar)
                            <input type="text" name="especies" value="{{ old('especies', is_array($os->especies) ? implode(', ', $os->especies) : $os->especies) }}" class="w-full border-0 border-b border-gray-400 bg-gray-50/50 p-1 focus:ring-0">
                        @else
                            <p class="p-1 border-b border-gray-200 font-medium text-gray-900 uppercase">{{ is_array($os->especies) ? implode(', ', $os->especies) : $os->especies }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="font-bold block text-gray-700">Quantidade:</label>
                        @if($podeEditar)
                            <input type="number" name="quantidade" value="{{ old('quantidade', $os->quantidade) }}" class="w-full border-0 border-b border-gray-400 bg-gray-50/50 p-1 focus:ring-0">
                        @else
                            <p class="p-1 border-b border-gray-200 font-medium text-gray-900">{{ $os->quantidade }}</p>
                        @endif
                    </div>
                </div>

                {{-- Motivo --}}
                <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                    <h4 class="font-bold underline mb-1 uppercase text-gray-800">Motivo da Intervenção</h4>
                    <div class="grid grid-cols-2 gap-y-1">
                        @foreach ($todosMotivos as $value => $label)
                            @php $checked = in_array($value, old('motivos', $os->motivos ?? [])); @endphp
                            <label class="flex items-center gap-2 text-gray-700 {{ $podeEditar ? 'cursor-pointer' : '' }}">
                                <input type="checkbox" name="motivos[]" value="{{ $value }}" {{ $checked ? 'checked' : '' }} 
                                       {{ !$podeEditar ? 'disabled' : '' }} class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]">
                                <span class="{{ $checked ? 'font-bold' : '' }} text-xs">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Serviços --}}
                <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                    <h4 class="font-bold underline mb-1 uppercase text-gray-800">Serviços a Executar</h4>
                    <div class="grid grid-cols-2 gap-y-1">
                        @foreach ($todosServicos as $value => $label)
                            @php $checked = in_array($value, old('servicos', $os->servicos ?? [])); @endphp
                            <label class="flex items-center gap-2 text-gray-700 {{ $podeEditar ? 'cursor-pointer' : '' }}">
                                <input type="checkbox" name="servicos[]" value="{{ $value }}" {{ $checked ? 'checked' : '' }} 
                                       {{ !$podeEditar ? 'disabled' : '' }} class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]">
                                <span class="{{ $checked ? 'font-bold' : '' }} text-xs">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Equipamentos --}}
                <div class="mb-4 border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                    <h4 class="font-bold underline mb-1 uppercase text-gray-800">Equipamentos</h4>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($todosEquipamentos as $value => $label)
                            @php $checked = in_array($value, old('equipamentos', $os->equipamentos ?? [])); @endphp
                            <label class="flex items-center gap-1 text-gray-700 {{ $podeEditar ? 'cursor-pointer' : '' }}">
                                <input type="checkbox" name="equipamentos[]" value="{{ $value }}" {{ $checked ? 'checked' : '' }} 
                                       {{ !$podeEditar ? 'disabled' : '' }} class="rounded border-gray-400 text-[#358054] focus:ring-[#358054]">
                                <span class="{{ $checked ? 'font-bold' : '' }} text-xs">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Datas --}}
                <div class="grid grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="font-bold block text-xs uppercase text-gray-700">DATA VISTORIA:</label>
                        @if($podeEditar)
                            <input type="date" name="data_vistoria" value="{{ old('data_vistoria', $os->data_vistoria ? \Carbon\Carbon::parse($os->data_vistoria)->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}" class="w-full border border-gray-300 p-1.5 rounded bg-white shadow-sm focus:ring-1 focus:ring-[#358054]">
                        @else
                            <p class="w-full border-b border-gray-300 p-1 font-medium text-gray-900">{{ $os->data_vistoria ? \Carbon\Carbon::parse($os->data_vistoria)->format('d/m/Y') : 'N/A' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="font-bold block text-xs uppercase text-gray-700">PREVISÃO EXECUÇÃO:</label>
                        @if($podeEditar)
                            <input type="date" name="data_execucao" value="{{ old('data_execucao', $os->data_execucao ? \Carbon\Carbon::parse($os->data_execucao)->format('Y-m-d') : '') }}" min="{{ date('Y-m-d') }}" class="w-full border border-gray-300 p-1.5 rounded bg-white shadow-sm focus:ring-1 focus:ring-[#358054]">
                        @else
                            <p class="w-full border-b border-gray-300 p-1 font-medium text-gray-900">{{ $os->data_execucao ? \Carbon\Carbon::parse($os->data_execucao)->format('d/m/Y') : 'N/A' }}</p>
                        @endif
                    </div>
                    <div class="mt-4 col-span-2">
                        <label class="font-bold block text-xs uppercase text-gray-700">Observações do Admin:</label>
                        @if($podeEditar)
                            <textarea name="observacoes" rows="3" class="w-full border border-gray-300 p-2 rounded bg-white shadow-sm focus:ring-1 focus:ring-[#358054]">{{ old('observacoes', $os->observacoes) }}</textarea>
                        @else
                            <p class="w-full border p-2 bg-gray-50 rounded italic text-gray-800">{{ $os->observacoes ?? 'Sem observações.' }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8 mt-12 pt-4 print:mt-6 text-center">
                    <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Responsável Técnico</p></div>
                    <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Recebido por</p></div>
                </div>
            </div>

            {{-- BOTÕES --}}
            <div class="mt-8 flex justify-end gap-3 print:hidden">
                <a href="{{ $podeEditar ? route('admin.contato.index') : route('service.tasks.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Voltar
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-black shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
                    <i data-lucide="printer" class="w-4 h-4 mr-2 text-blue-600"></i> Imprimir
                </button>
                @if($podeEditar)
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#358054] px-6 py-2 text-sm font-bold text-white shadow-lg hover:bg-[#2a6643] transition">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Salvar Alterações
                </button>
                @endif
            </div>

        @if($podeEditar)
        </form>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
</script>
@endsection
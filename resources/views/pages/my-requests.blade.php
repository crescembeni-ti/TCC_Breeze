@extends('layouts.dashboard')
@section('content')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minhas Solicitações - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/dashboard.css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

       {{-- CONTEÚDO PRINCIPAL --}}
            <main class="flex-1 p-10">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 text-gray-900">

                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Minhas Solicitações</h2>

                        @if (session('success'))
                            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
                        @endif
                        @if ($errors->has('cancel_error'))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                                {{ $errors->first('cancel_error') }}</div>
                        @endif

                        {{-- LEGENDA DE STATUS --}}
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Legenda de Status</h4>
                            <div class="flex flex-wrap gap-x-6 gap-y-3">
                                {{-- Adicionei a classe 'left-align' aos primeiros itens que podem ser cortados --}}
                                <div class="tooltip left-align flex items-center">
                                    <span class="w-4 h-4 rounded-sm mr-2 bg-gray-400 border border-gray-500"></span>
                                    <span class="text-sm text-gray-700">Em Análise</span>
                                    <span class="tooltiptext">O pedido foi enviado e será analisado pela
                                        secretaria.</span>
                                </div>
                                <div class="tooltip left-align flex items-center">
                                    <span class="w-4 h-4 rounded-sm mr-2 bg-blue-700 border border-blue-800"></span>
                                    <span class="text-sm text-gray-700">Deferido</span>
                                    <span class="tooltiptext">O pedido foi visto e uma vistoria será feita no
                                        local.</span>
                                </div>
                                <div class="tooltip flex items-center">
                                    <span class="w-4 h-4 rounded-sm mr-2 bg-red-600 border border-red-700"></span>
                                    <span class="text-sm text-gray-700">Indeferido</span>
                                    <span class="tooltiptext">O pedido é inviável ou não compete ao meio
                                        ambiente.</span>
                                </div>
                                <div class="tooltip flex items-center">
                                    <span class="w-4 h-4 rounded-sm mr-2 border border-amber-900"
                                        style="background-color: #92400E;"></span>
                                    <span class="text-sm text-gray-700">Vistoriado</span>
                                    <span class="tooltiptext">Já foi feita uma vistoria no local pela
                                        secretaria.</span>
                                </div>
                                <div class="tooltip flex items-center">
                                    <span
                                        class="w-4 h-4 rounded-sm mr-2 bg-yellow-400 border border-yellow-500"></span>
                                    <span class="text-sm text-gray-700">Em Execução</span>
                                    <span class="tooltiptext">Foi constatada a necessidade de serviço e a equipe irá
                                        agir.</span>
                                </div>
                                <div class="tooltip flex items-center">
                                    <span
                                        class="w-4 h-4 rounded-sm mr-2 bg-orange-500 border border-orange-600"></span>
                                    <span class="text-sm text-gray-700">Sem Pendências</span>
                                    <span class="tooltiptext">Foi vistoriado e não foi constatada necessidade de
                                        serviço.</span>
                                </div>
                                <div class="tooltip flex items-center">
                                    <span class="w-4 h-4 rounded-sm mr-2 bg-green-600 border border-green-700"></span>
                                    <span class="text-sm text-gray-700">Concluído</span>
                                    <span class="tooltiptext">O serviço já foi executado.</span>
                                </div>
                            </div>
                        </div>

                        {{-- LISTA DE SOLICITAÇÕES --}}
                        @if ($myRequests->isEmpty())
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-lg font-medium text-gray-900">Nenhuma solicitação encontrada</h3>
                                <p class="mt-1 text-sm text-gray-500">Você ainda não fez nenhuma solicitação de
                                    intervenção.</p>
                                    
                                <!-- Botão -->
                                <div class="mt-6">
                                 <a href="{{ route('contact') }}"
                                  class="
                                  bg-green-700 text-white font-semibold
                                  rounded-md shadow-md
                                  hover:bg-green-600 hover:shadow-lg
                                  active:bg-[#38c224]
                                  transition duration-200
                                  px-4 py-2 inline-block
                                  ">
                                  Fazer minha primeira solicitação
                                 </a>
                                </div>

                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($myRequests as $request)
                                    <div x-data="{ open: false, showCancelModal: false }"
                                        class="border rounded-lg shadow-sm transition-shadow hover:shadow-md bg-white">

                                        <div class="p-4 md:p-6">
                                            <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                                                <div>
                                                    <p
                                                        class="text-lg font-semibold text-gray-900 flex items-center flex-wrap">
                                                        Status da Solicitação:
                                                        <span class="text-sm text-gray-500 font-normal ml-3">
                                                            ({{ $request->created_at->format('d/m/Y') }})
                                                        </span>
                                                    </p>
                                                </div>

                                                <div class="flex-shrink-0 mt-3 sm:mt-0 sm:ml-4">
                                                    @php
                                                        $statusName = $request->status->name ?? 'Indefinido';
                                                        $colorClass = match ($statusName) {
                                                            'Em Análise' => 'bg-gray-200 text-gray-800',
                                                            'Deferido' => 'bg-blue-100 text-blue-800',
                                                            'Indeferido' => 'bg-red-100 text-red-800',
                                                            'Vistoriado' => 'bg-amber-100 text-amber-800',
                                                            'Em Execução' => 'bg-yellow-100 text-yellow-800',
                                                            'Sem Pendências' => 'bg-orange-100 text-orange-800',
                                                            'Concluído' => 'bg-green-100 text-green-800',
                                                            'Cancelado' => 'bg-gray-300 text-gray-600 line-through',
                                                            default => 'bg-gray-100 text-gray-800',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $colorClass }}">
                                                        {{ $statusName }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mt-4 flex items-center gap-6">
                                                <button @click="open = !open"
                                                    class="text-sm font-medium text-green-600 hover:text-green-800 inline-flex items-center">
                                                    <span x-show="!open">Ver Detalhes</span>
                                                    <span x-show="open" style="display: none;">Ocultar
                                                        Detalhes</span>
                                                    <svg class="w-4 h-4 ml-1 transform transition-transform"
                                                        :class="{ 'rotate-180': open }" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>

                                                @if ($request->status && in_array($request->status->name, ['Em Análise', 'Deferido']))
                                                    <button @click="showCancelModal = true"
                                                        class="text-sm font-medium text-red-600 hover:text-red-800">
                                                        Cancelar Solicitação
                                                    </button>
                                                @endif

                                            </div>
                                        </div>

                                        <div x-show="open" x-transition
                                            class="border-t border-gray-200 bg-gray-50 p-4 md:p-6"
                                            style="display: none;">
                                            <h4 class="text-md font-semibold text-gray-700 mb-3">Detalhes da
                                                Solicitação #{{ $request->id }}</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="font-medium text-gray-500">Data e Hora:</p>
                                                    <p class="text-gray-800">
                                                        {{ $request->created_at->format('d/m/Y \à\s H:i') }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-500">Local:</p>
                                                    <p class="text-gray-800">{{ $request->bairro }},
                                                        {{ $request->rua }}, Nº {{ $request->numero }}</p>
                                                </div>
                                                <div class="col-span-1 md:col-span-2">
                                                    <p class="font-medium text-gray-500">Descrição:</p>
                                                    <p class="text-gray-800 whitespace-pre-wrap">
                                                        {{ $request->descricao }}</p>
                                                </div>
                                                @if ($request->foto_path)
                                                    <div class="col-span-1 md:col-span-2">
                                                        <p class="font-medium text-gray-500">Foto Enviada:</p>
                                                        <a href="{{ Storage::url($request->foto_path) }}"
                                                            target="_blank">
                                                            <img src="{{ Storage::url($request->foto_path) }}"
                                                                alt="Foto da solicitação"
                                                                class="mt-2 rounded-lg border shadow-sm max-w-xs max-h-64 object-cover">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            @if ($request->status && $request->status->name == 'Indeferido' && $request->justificativa)
                                                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                                    <p class="text-sm font-semibold text-red-800">Motivo do
                                                        Indeferimento:</p>
                                                    <p class="text-sm text-red-700 mt-1 whitespace-pre-wrap">
                                                        {{ $request->justificativa }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div x-show="showCancelModal" x-transition
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
                                            style="display: none;">
                                            <div @click.away="showCancelModal = false" x-show="showCancelModal"
                                                x-transition class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                                                <form method="POST"
                                                    action="{{ route('contact.cancel', $request) }}">
                                                    @csrf @method('PATCH')
                                                    <h3 class="text-xl font-semibold text-gray-900">Cancelar
                                                        Solicitação</h3>
                                                    <p class="mt-2 text-sm text-gray-600">Tem certeza que deseja
                                                        cancelar a solicitação #{{ $request->id }}? Esta ação não pode
                                                        ser desfeita.</p>
                                                    <div class="mt-4">
                                                        <label for="justificativa-{{ $request->id }}"
                                                            class="block text-sm font-medium text-gray-700">Motivo
                                                            (opcional):</label>
                                                        <textarea name="justificativa_cancelamento" id="justificativa-{{ $request->id }}" rows="3"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                                                    </div>
                                                    <div class="mt-6 flex justify-end space-x-4">
                                                        <button type="button" @click="showCancelModal = false"
                                                            class="btn bg-gray-200 text-gray-700 hover:bg-gray-300">Manter
                                                            Solicitação</button>
                                                        <button type="submit"
                                                            class="btn bg-red-600 hover:bg-red-700 text-white">Sim,
                                                            cancelar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </main>
       

    <!-- LUCIDE ICONS -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

</body>

</html>
@endsection

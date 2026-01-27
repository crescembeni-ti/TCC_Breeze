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

<body class="font-sans antialiased">

    {{-- CONTEÚDO PRINCIPAL --}}
    <main class="flex-1 p-10">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8 text-gray-900">

                <h2 class="text-3xl font-bold text-gray-900 mb-6">Minhas Solicitações</h2>

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border border-green-200">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->has('cancel_error'))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border border-red-200">
                        {{ $errors->first('cancel_error') }}
                    </div>
                @endif

                {{-- LEGENDA DE STATUS --}}
                <div class="mb-8 p-5 bg-gray-50 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Legenda de Status</h4>
                    <div class="flex flex-wrap gap-x-6 gap-y-3">
                        <div class="flex items-center gap-2" title="O pedido foi enviado e será analisado pela secretaria.">
                            <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                            <span class="text-sm text-gray-600">Em Análise</span>
                        </div>
                        <div class="flex items-center gap-2" title="O pedido foi visto e uma vistoria será feita no local.">
                            <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                            <span class="text-sm text-gray-600">Deferido</span>
                        </div>
                        <div class="flex items-center gap-2" title="O pedido é inviável ou não compete ao meio ambiente.">
                            <span class="w-3 h-3 rounded-full bg-red-600"></span>
                            <span class="text-sm text-gray-600">Indeferido</span>
                        </div>
                        <div class="flex items-center gap-2" title="Já foi feita uma vistoria no local pela secretaria.">
                            <span class="w-3 h-3 rounded-full bg-amber-800"></span>
                            <span class="text-sm text-gray-600">Vistoriado</span>
                        </div>
                        <div class="flex items-center gap-2" title="Foi constatada a necessidade de serviço e a equipe irá agir.">
                            <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                            <span class="text-sm text-gray-600">Em Execução</span>
                        </div>
                        <div class="flex items-center gap-2" title="Foi vistoriado e não foi constatada necessidade de serviço.">
                            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                            <span class="text-sm text-gray-600">Sem Pendências</span>
                        </div>
                        <div class="flex items-center gap-2" title="O serviço já foi executado.">
                            <span class="w-3 h-3 rounded-full bg-green-600"></span>
                            <span class="text-sm text-gray-600">Concluído</span>
                        </div>
                    </div>
                </div>

                {{-- LISTA DE SOLICITAÇÕES --}}
                @if ($myRequests->isEmpty())
                    <div class="text-center py-16 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                        <div class="bg-white p-4 rounded-full inline-block shadow-sm mb-4">
                            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Nenhuma solicitação encontrada</h3>
                        <p class="mt-1 text-sm text-gray-500">Você ainda não fez nenhuma solicitação de intervenção.</p>
                        
                        <div class="mt-6">
                            <a href="{{ route('contact') }}" class="bg-[#358054] text-white font-semibold rounded-lg shadow-md hover:bg-[#2a6643] hover:shadow-lg active:scale-95 transition-all duration-200 px-6 py-2.5 inline-flex items-center gap-2">
                                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                                Fazer minha primeira solicitação
                            </a>
                        </div>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($myRequests as $request)
                            {{-- CARD DA SOLICITAÇÃO COM UX MELHORADA --}}
                            <div x-data="{ open: false, showCancelModal: false }"
                                class="border rounded-lg shadow-sm transition-all hover:shadow-md bg-white overflow-hidden
                                {{-- LÓGICA DE COR DA BORDA ESQUERDA --}}
                                @php
                                    $statusColor = match ($request->status->name ?? '') {
                                        'Em Análise' => 'border-l-4 border-l-gray-400',
                                        'Deferido' => 'border-l-4 border-l-blue-700',
                                        'Indeferido' => 'border-l-4 border-l-red-600',
                                        'Vistoriado' => 'border-l-4 border-l-amber-900',
                                        'Em Execução' => 'border-l-4 border-l-yellow-400',
                                        'Sem Pendências' => 'border-l-4 border-l-orange-500',
                                        'Concluído' => 'border-l-4 border-l-green-600',
                                        'Cancelado' => 'border-l-4 border-l-gray-300',
                                        default => 'border-l-4 border-l-gray-200',
                                    };
                                @endphp
                                {{ $statusColor }}">

                                <div class="p-5">
                                    {{-- CABEÇALHO DO CARD --}}
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-4">
                                        <div>
                                            <div class="flex items-center gap-3 mb-1">
                                                <h3 class="text-xl font-bold text-[#358054]">
                                                    Solicitação #{{ $request->id }}
                                                </h3>
                                                <span class="text-xs text-gray-500 font-medium bg-gray-100 border border-gray-200 rounded px-2 py-0.5 flex items-center gap-1">
                                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                                    {{ $request->created_at->format('d/m/Y') }}
                                                </span>
                                            </div>
                                            {{-- Assunto Resumido --}}
                                            <p class="text-gray-700 font-medium">
                                                {{ $request->topico ?? 'Solicitação Geral' }}
                                            </p>
                                        </div>

                                        <div class="flex items-center self-start">
                                            {{-- STATUS BADGE --}}
                                            @php
                                                $statusName = $request->status->name ?? 'Indefinido';
                                                $badgeClass = match ($statusName) {
                                                    'Em Análise' => 'bg-gray-100 text-gray-700 border border-gray-200',
                                                    'Deferido' => 'bg-blue-50 text-blue-700 border border-blue-100',
                                                    'Indeferido' => 'bg-red-50 text-red-700 border border-red-100',
                                                    'Vistoriado' => 'bg-amber-50 text-amber-800 border border-amber-100',
                                                    'Em Execução' => 'bg-yellow-50 text-yellow-700 border border-yellow-100',
                                                    'Sem Pendências' => 'bg-orange-50 text-orange-700 border border-orange-100',
                                                    'Concluído' => 'bg-green-50 text-green-700 border border-green-100',
                                                    'Cancelado' => 'bg-gray-50 text-gray-500 border border-gray-200 line-through',
                                                    default => 'bg-gray-50 text-gray-600',
                                                };
                                            @endphp
                                            <span class="px-3 py-1 text-xs font-bold uppercase tracking-wide rounded-full {{ $badgeClass }}">
                                                {{ $statusName }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- BARRA DE AÇÕES --}}
                                    <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-3">
                                        <button @click="open = !open" 
                                                class="text-sm font-semibold text-gray-500 hover:text-[#358054] flex items-center gap-1.5 transition-colors group">
                                            <span x-text="open ? 'Fechar Detalhes' : 'Ver Detalhes'"></span>
                                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200 group-hover:text-[#358054]" :class="{ 'rotate-180': open }"></i>
                                        </button>

                                        @if ($request->status && $request->status->name === 'Em Análise')
                                            <button @click="showCancelModal = true" 
                                                    class="text-xs font-semibold text-red-500 hover:text-red-700 flex items-center gap-1 border border-transparent hover:border-red-100 hover:bg-red-50 px-2.5 py-1.5 rounded transition-all">
                                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                Cancelar Pedido
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- ÁREA EXPANDIDA (DETALHES) --}}
                                <div x-show="open" x-collapse style="display: none;" class="bg-gray-50 border-t border-gray-100 p-6">
                                    
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Informações Completas</h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 text-sm">
                                        
                                        {{-- Data e Hora --}}
                                        <div class="flex gap-3">
                                            <div class="mt-0.5 text-gray-400 bg-white p-1.5 rounded border border-gray-200 shadow-sm h-fit">
                                                <i data-lucide="clock" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">Data e Hora do Envio</p>
                                                <p class="text-gray-600">{{ $request->created_at->format('d/m/Y \à\s H:i') }}</p>
                                            </div>
                                        </div>

                                        {{-- Localização --}}
                                        <div class="flex gap-3">
                                            <div class="mt-0.5 text-gray-400 bg-white p-1.5 rounded border border-gray-200 shadow-sm h-fit">
                                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">Endereço da Ocorrência</p>
                                                <p class="text-gray-600">{{ $request->bairro }}, {{ $request->rua }}, Nº {{ $request->numero }}</p>
                                            </div>
                                        </div>

                                        {{-- Descrição --}}
                                        <div class="col-span-1 md:col-span-2 flex gap-3">
                                            <div class="mt-0.5 text-gray-400 bg-white p-1.5 rounded border border-gray-200 shadow-sm h-fit">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                            </div>
                                            <div class="w-full">
                                                <p class="font-semibold text-gray-900 mb-1">Relato do Problema</p>
                                                <div class="text-gray-600 bg-white p-3 rounded border border-gray-200 text-sm leading-relaxed shadow-sm w-full">
                                                    {{ $request->descricao }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Foto --}}
                                        @if ($request->foto_path)
                                            <div class="col-span-1 md:col-span-2 flex gap-3">
                                                <div class="mt-0.5 text-gray-400 bg-white p-1.5 rounded border border-gray-200 shadow-sm h-fit">
                                                    <i data-lucide="image" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900 mb-2">Imagem Anexada</p>
                                                    <a href="{{ Storage::url($request->foto_path) }}" target="_blank" class="group relative inline-block">
                                                        <img src="{{ Storage::url($request->foto_path) }}" 
                                                             alt="Foto da solicitação"
                                                             class="h-40 w-auto rounded-lg border border-gray-300 shadow-sm transition-transform group-hover:scale-[1.02]">
                                                        <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/10 rounded-lg transition-colors cursor-zoom-in">
                                                            <i data-lucide="zoom-in" class="text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Justificativa de Indeferimento --}}
                                    @if ($request->status && $request->status->name == 'Indeferido' && $request->justificativa)
                                        <div class="mt-6 flex gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-900 shadow-sm">
                                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-red-600 mt-0.5"></i>
                                            <div>
                                                <p class="font-bold text-red-800">Motivo do Indeferimento</p>
                                                <p class="text-sm mt-1 text-red-700 leading-relaxed">{{ $request->justificativa }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- MODAL DE CANCELAMENTO --}}
                                <div x-show="showCancelModal" x-transition.opacity
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4"
                                    style="display: none;">
                                    
                                    <div @click.away="showCancelModal = false" x-show="showCancelModal"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-90"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 border border-gray-100">
                                        
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="p-2 bg-red-100 rounded-full text-red-600">
                                                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-900">Tem certeza que deseja cancelar?</h3>
                                        </div>

                                        <form method="POST" action="{{ route('contact.cancel', $request) }}">
                                            @csrf @method('DELETE')
                                            
                                            <p class="text-sm text-gray-600 leading-relaxed">
                                                Você está prestes a cancelar a solicitação <strong>#{{ $request->id }}</strong>. 
                                                Esta ação é irreversível e ela será removida da sua lista.
                                            </p>

                                            <div class="mt-6 flex justify-end gap-3">
                                                <button type="button" @click="showCancelModal = false"
                                                    class="px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                                                    Voltar
                                                </button>
                                                <button type="submit"
                                                    class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 shadow-sm transition-colors flex items-center gap-2">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    Confirmar Cancelamento
                                                </button>
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

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
        
        // Garante que novos ícones apareçam se o Alpine alterar o DOM
        document.addEventListener('alpine:initialized', () => {
             lucide.createIcons();
        });
    </script>

</body>
</html>
@endsection
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minhas Solicitações - Árvores de Paracambi</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    {{-- Carrega os CSS principais (app.css e welcome.css) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/welcome.css')
     <!-- Ícone do site -->
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    {{-- Script do Alpine.js (necessário para o "Ver Detalhes" e o Modal) --}}
    {{-- O @vite('resources/js/app.js') já deve incluir isso, mas garantimos --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    {{-- Garante que o rodapé fique no final, mesmo com pouco conteúdo --}}
    <div class="min-h-screen flex flex-col justify-between">
        
        {{-- ====================================================== --}}
        {{-- 1. CABEÇALHO (Layout da "welcome" colado aqui dentro) --}}
        {{-- ====================================================== --}}
        <header class="site-header relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
                
                <!-- LOGO + TÍTULO -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-20 w-20 object-contain">
                        <h1 class="text-4xl font-bold">
                            <span class="text-[#358054]">Árvores de</span>
                            <span class="text-[#a0c520]"> Paracambi</span>
                        </h1>
                    </a>
                </div>

                <!-- LADO DIREITO: LOGIN / REGISTRO + MENU HAMBÚRGUER -->
                <div class="flex items-center gap-4" x-data="{ open: false }">
                    
                    <!-- Botões Desktop (Para telas médias e grandes) -->
                    <div class="hidden sm:flex items-center gap-4">
                        @auth
                            {{-- Se o usuário está LOGADO, mostra o link para o Painel/Home --}}
                            <a href="{{ route('home') }}" class="btn bg-green-600 hover:bg-green-700">Painel</a>
                        @else
                            {{-- Se o usuário é VISITANTE, mostra Entrar/Cadastrar --}}
                            <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700">Entrar</a>
                            <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700">Cadastrar</a>
                        @endauth
                    </div>

                    <!-- Botão do menu (Hamburger) -->
                    <button 
                        @click="open = !open"
                        class="menu-button focus:outline-none"
                        aria-label="Abrir menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" 
                            fill="none" viewBox="0 0 24 24" 
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" 
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- DROPDOWN DO MENU (Mobile e Desktop) -->
                    <div 
                        x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="menu-dropdown absolute right-0 top-[6rem] z-50"
                        style="display: none;"
                    >
                        <!-- Links Públicos (Para todos) -->
                        <a href="{{ route('about') }}">Sobre</a>

                        @auth
                            <!-- ===== Links para Usuários Logados ===== -->
                            <a href="{{ route('contact') }}">Fazer Solicitação</a>
                            <a href="{{ route('profile.edit') }}">Meu Perfil</a>
                            
                            <div class="menu-dropdown-divider"></div> 

                            <!-- Formulário de Sair (Logout) -->
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <a href="{{ route('logout') }}"
                                   class="menu-dropdown-logout-link"
                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                    Sair
                                </a>
                            </form>
                        @else
                            <!-- ===== Links para Visitantes (Mobile) ===== -->
                            <a href="{{ route('login') }}">Entrar</a>
                            <a href="{{ route('register') }}">Cadastrar</a>
                        @endauth
                    </div>
                    
                </div>
            </div>
        </header>

        {{-- ====================================================== --}}
        <!-- 2. Conteúdo principal (O seu design interativo)         -->
        {{-- ====================================================== --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">
                        Minhas Solicitações
                    </h2>

                    {{-- Alerta de Sucesso (para quando cancelar) --}}
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    {{-- Alerta de Erro (para quando tentar cancelar e não puder) --}}
                    @if($errors->has('cancel_error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            {{ $errors->first('cancel_error') }}
                        </div>
                    @endif

                    @if($myRequests->isEmpty())
                        {{-- Se não houver solicitações, mostra uma mensagem --}}
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">Nenhuma solicitação encontrada</h3>
                            <p class="mt-1 text-sm text-gray-500">Você ainda não fez nenhuma solicitação de intervenção.</p>
                            <div class="mt-6">
                                <a href="{{ route('contact') }}" class="btn bg-green-600 hover:bg-green-700">
                                    Fazer minha primeira solicitação
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Se houver solicitações, mostra a lista interativa --}}
                        <div class="space-y-4">
                            @foreach ($myRequests as $request)
                                {{-- x-data controla o detalhe (open) E o modal (showCancelModal) --}}
                                <div x-data="{ open: false, showCancelModal: false }" class="border rounded-lg shadow-sm transition-shadow hover:shadow-md bg-white">
                                    
                                    {{-- PARTE SEMPRE VISÍVEL DO CARD --}}
                                    <div class="p-4 md:p-6">
                                        <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                                            {{-- Detalhes principais --}}
                                            <div>
                                                <p class="text-lg font-semibold text-gray-900">
                                                    Status da Solicitação
                                                </p>
                                            </div>
                                            {{-- Status com caixinha colorida --}}
                                            <div class="flex-shrink-0 mt-3 sm:mt-0 sm:ml-4">
                                                
                                                @php
                                                    $statusName = $request->status->name ?? 'Indefinido';
                                                    $colorClass = '';
                                                    switch ($statusName) {
                                                        case 'Em Análise': $colorClass = 'bg-orange-100 text-orange-800'; break;
                                                        case 'Deferido': $colorClass = 'bg-blue-100 text-blue-800'; break;
                                                        case 'Concluído': $colorClass = 'bg-green-100 text-green-800'; break;
                                                        case 'Indeferido': $colorClass = 'bg-red-100 text-red-800'; break;
                                                        case 'Cancelado': $colorClass = 'bg-gray-200 text-gray-600'; break; // NOVO
                                                        default: $colorClass = 'bg-gray-100 text-gray-800';
                                                    }
                                                @endphp
                                                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $colorClass }}">
                                                    {{ $statusName }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Botões de Ação --}}
                                        <div class="mt-4 flex items-center gap-6">
                                            {{-- Botão "Ver Detalhes" --}}
                                            <button @click="open = !open" class="text-sm font-medium text-green-600 hover:text-green-800 inline-flex items-center">
                                                <span x-show="!open">Ver Detalhes</span>
                                                <span x-show="open" style="display: none;">Ocultar Detalhes</span>
                                                <svg class="w-4 h-4 ml-1 transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            {{-- [NOVO] BOTÃO DE CANCELAR --}}
                                            {{-- Só aparece se o status for "Em Análise" --}}
                                            @if($request->status && $request->status->name == 'Em Análise')
                                                <button @click="showCancelModal = true" class="text-sm font-medium text-red-600 hover:text-red-800">
                                                    Cancelar Solicitação
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- "ABINHA" DE DETALHES (só abre ao clicar) --}}
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-2"
                                         class="border-t border-gray-200 bg-gray-50 p-4 md:p-6"
                                         style="display: none;"
                                    >
                                        <h4 class="text-md font-semibold text-gray-700 mb-3">Detalhes da Solicitação</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="font-medium text-gray-500">Data:</p>
                                                {{-- A correção do fuso horário é aplicada aqui --}}
                                                <p class="text-gray-800">{{ $request->created_at->format('d/m/Y \à\s H:i') }}</p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-500">Local:</p>
                                                <p class="text-gray-800">{{ $request->bairro }}, {{ $request->rua }}, Nº {{ $request->numero }}</p>
                                            </div>
                                            <div class="col-span-1 md:col-span-2">
                                                <p class="font-medium text-gray-500">O que solicitou:</p>
                                                <p class="text-gray-800 whitespace-pre-wrap">{{ $request->descricao }}</p> 
                                            </div>
                                        </div>
                                        
                                        {{-- Mostra a justificativa, se for "Indeferido" --}}
                                        @if($request->status && $request->status->name == 'Indeferido' && $request->justificativa)
                                            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                                <p class="text-sm font-semibold text-red-800">Motivo do Indeferimento:</p>
                                                <p class="text-sm text-red-700 mt-1 whitespace-pre-wrap">
                                                    {{ $request->justificativa }}
                                                </p>
                                            </div>
                                        @endif
                                        
                                        {{-- Mostra a justificativa, se for "Cancelado" --}}
                                        @if($request->status && $request->status->name == 'Cancelado' && $request->justificativa)
                                            <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-md">
                                                <p class="text-sm font-semibold text-gray-800">Motivo do Cancelamento:</p>
                                                <p class="text-sm text-gray-700 mt-1 whitespace-pre-wrap">
                                                    {{ $request->justificativa }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- ====================================================== --}}
                                    {{-- 4. O MODAL DE CANCELAMENTO (A "Caixinha")             --}}
                                    {{-- ====================================================== --}}
                                    <div x-show="showCancelModal"
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
                                         style="display: none;">
                                        
                                        {{-- O Card do Modal --}}
                                        <div @click.away="showCancelModal = false" 
                                             x-show="showCancelModal"
                                             x-transition:enter="ease-out duration-300"
                                             x-transition:enter-start="opacity-0 scale-90"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                                             
                                            {{-- O formulário que envia para a rota de cancelamento --}}
                                            <form method="POST" action="{{ route('contact.cancel', $request) }}">
                                                @csrf
                                                @method('PATCH')
                                                
                                                {{-- REQUISITO 5: Pergunta "Tem Certeza?" --}}
                                                <h3 class="text-xl font-semibold text-gray-900">Cancelar Solicitação</h3>
                                                <p class="mt-2 text-sm text-gray-600">
                                                    Tem certeza que deseja cancelar a solicitação do protocolo #{{ $request->id }}? Esta ação não pode ser desfeita.
                                                </p>
                                                
                                                {{-- REQUISITO 2: Motivo Opcional --}}
                                                <div class="mt-4">
                                                    <label for="justificativa_cancelamento-{{ $request->id }}" class="block text-sm font-medium text-gray-700">
                                                        Motivo (opcional):
                                                    </label>
                                                    <textarea name="justificativa_cancelamento" id="justificativa_cancelamento-{{ $request->id }}" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                                                </div>
                                                
                                                {{-- REQUISITOS 3 e 4: Botões de Ação --}}
                                                <div class="mt-6 flex justify-end space-x-4">
                                                    {{-- Botão "Sair" (não cancela) --}}
                                                    <button type="button" 
                                                            @click="showCancelModal = false" 
                                                            class="btn bg-gray-200 text-gray-700 hover:bg-gray-300">
                                                        Manter Solicitação
                                                    </button>
                                                    
                                                    {{-- Botão "Cancelar" (confirma e envia o form) --}}
                                                    <button type="submit" 
                                                        class="btn bg-red-600 hover:bg-red-700 text-white"
                                                        @click.prevent="
                                                        if (confirm('Tem certeza que deseja cancelar esta solicitação?')) {
                                                       $el.closest('form').submit()
                                                        }
                                                        ">
                                                        Sim, cancelar
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

        {{-- ====================================================== --}}
        {{-- 5. RODAPÉ (Layout da "welcome" colado aqui dentro)   --}}
        {{-- ====================================================== --}}
        <footer class="bg-gray-800 shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Mapa de Árvores. Desenvolvido com Laravel e Leaflet.</p>
            </div>
        </footer>
        
    </div>
</body>
</html>
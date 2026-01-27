@extends('layouts.dashboard')

@section('title', 'Painel Administrativo')

@section('content')
    {{-- Ajustado padding para mobile: p-4 md:p-8 --}}
    <div class="bg-white shadow-sm rounded-lg p-4 md:p-8 mb-6">
        <h2 class="text-2xl md:text-3xl font-bold text-[#358054] mb-4">Painel Administrativo</h2>
        <p class="text-gray-700 text-base md:text-lg mb-6">
            Bem-vindo, <span class="font-semibold">{{ Auth::guard('admin')->user()->name }}</span>!
            <br>Use o menu à esquerda para gerenciar árvores, mensagens e solicitações.
        </p>

        @if (isset($stats))
            {{-- Ajustado grid: sm:grid-cols-2 para tablet --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 border-t pt-6 border-gray-100">
                <div class="bg-green-50 rounded-lg p-6 text-center border border-green-100 hover:shadow-md transition">
                    <h4 class="text-lg font-semibold text-green-800 mb-2">🌳 Árvores</h4>
                    <p class="text-4xl font-bold text-[#38c224]">{{ $stats['total_trees'] }}</p>
                </div>

                {{-- CARD SOLICITAÇÕES --}}
                <div class="bg-blue-50 rounded-lg p-6 text-center border border-blue-100 hover:shadow-md transition">
                    <h4 class="text-lg font-semibold text-blue-800 mb-2">📬 Solicitações</h4>
                    <p class="text-4xl font-bold text-blue-500">{{ $stats['total_requests'] }}</p>
                </div>

                {{-- O card de espécies pode ocupar duas colunas no tablet se desejar, mas padrão é ok --}}
                <div class="bg-yellow-50 rounded-lg p-6 text-center border border-yellow-100 hover:shadow-md transition sm:col-span-2 lg:col-span-1">
                    <h4 class="text-lg font-semibold text-yellow-800 mb-2">🌿 Espécies</h4>
                    <p class="text-4xl font-bold text-yellow-600">{{ $stats['total_species'] }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
        
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4 border-b border-gray-100 pb-4">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2">
                📋 Histórico de Atividades
            </h2>

            {{-- FORMULÁRIO DE FILTROS --}}
            <form method="GET" action="{{ route('admin.dashboard') }}" 
                  x-data="{ period: '{{ request('period') }}' }" 
                  class="w-full xl:w-auto flex flex-col md:flex-row gap-3 items-end">
                
                {{-- Filtro de Ação --}}
                <div class="relative w-full md:w-auto">
                    <select name="filter" onchange="this.form.submit()" 
                            class="appearance-none w-full md:w-48 bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:border-green-500 cursor-pointer shadow-sm">
                        <option value="" {{ request('filter') == '' ? 'selected' : '' }}>Todas as Ações</option>
                        <option value="cadastro" {{ request('filter') == 'cadastro' ? 'selected' : '' }}>✅ Cadastros</option>
                        <option value="atualizacao" {{ request('filter') == 'atualizacao' ? 'selected' : '' }}>✏️ Atualizações</option> 
                        <option value="exclusao" {{ request('filter') == 'exclusao' ? 'selected' : '' }}>🗑️ Exclusões</option>
                        <option value="aprovacao" {{ request('filter') == 'aprovacao' ? 'selected' : '' }}>🛡️ Aprovações</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>

                {{-- Filtro de Período --}}
                <div class="relative w-full md:w-auto">
                    <select name="period" x-model="period" onchange="if(this.value != 'custom') this.form.submit()" 
                            class="appearance-none w-full md:w-48 bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:border-green-500 cursor-pointer shadow-sm">
                        <option value="">Todo o Período</option>
                        <option value="7_days">📅 Últimos 7 dias</option>
                        <option value="30_days">📅 Últimos 30 dias</option>
                        <option value="custom">📆 Personalizado</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>

                {{-- CAMPOS DE DATA --}}
                <div x-show="period === 'custom'" x-transition class="flex flex-col sm:flex-row gap-2 w-full md:w-auto" style="display: none;">
                    <input type="date" name="date_start" value="{{ request('date_start') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500 w-full md:w-auto">
                    <span class="self-center text-gray-500 hidden sm:inline">até</span>
                    <input type="date" name="date_end" value="{{ request('date_end') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500 w-full md:w-auto">
                    <button type="submit" class="bg-[#358054] text-white px-3 py-2 rounded-lg hover:bg-green-700 transition w-full sm:w-auto flex justify-center items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                </div>

            </form>
        </div>

        <div class="space-y-3">
            @forelse($adminLogs as $log)
                @php
                    $isCreate = Str::contains($log->action, 'create');
                    $isUpdate = Str::contains($log->action, 'update');
                    $isDelete = Str::contains($log->action, 'delete');
                    
                    $bgColor = $isCreate ? 'bg-green-50' : ($isUpdate ? 'bg-blue-50' : ($isDelete ? 'bg-red-50' : 'bg-gray-50'));
                    $borderColor = $isCreate ? 'border-green-200' : ($isUpdate ? 'border-blue-200' : ($isDelete ? 'border-red-200' : 'border-gray-200'));
                @endphp

                <div class="flex flex-col sm:flex-row items-start gap-4 p-4 rounded-lg border {{ $borderColor }} {{ $bgColor }} transition hover:shadow-sm">
                    <div class="shrink-0 mt-1 hidden sm:block">
                        @if($isCreate)
                            <div class="p-2 bg-green-200 text-green-700 rounded-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                        @elseif($isUpdate)
                            <div class="p-2 bg-blue-200 text-blue-700 rounded-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                        @elseif($isDelete)
                            <div class="p-2 bg-red-200 text-red-700 rounded-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </div>
                        @else
                            <div class="p-2 bg-gray-200 text-gray-700 rounded-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 w-full">
                        <div class="flex justify-between items-start w-full">
                            <p class="font-medium text-gray-900 text-sm sm:text-base break-words">
                                {{ $log->description }}
                            </p>
                            {{-- Ícone visível apenas no mobile, alinhado à direita --}}
                            <span class="sm:hidden text-xs font-bold uppercase tracking-wider px-2 py-1 rounded bg-white bg-opacity-50">
                                @if($isCreate) Novo @elseif($isUpdate) Edit @elseif($isDelete) Del @endif
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs sm:text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $log->admin->name ?? 'Sistema' }}
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="bg-gray-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-500">Nenhuma atividade encontrada com este filtro.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $adminLogs->links() }}
        </div>
    </div>
@endsection
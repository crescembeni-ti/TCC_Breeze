<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Painel') - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'])
    
    {{-- Alpine.js é essencial para abrir/fechar o menu --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    
    {{-- O x-data aqui controla o estado do menu (aberto/fechado) --}}
    <div class="flex h-screen overflow-hidden bg-gray-100" x-data="{ sidebarOpen: false }">

        {{-- 1. OVERLAY ESCURO (Só aparece no mobile quando o menu está aberto) --}}
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden">
        </div>

        {{-- 2. SIDEBAR (Menu Lateral) --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition-transform duration-300 transform bg-[#1f2937] lg:translate-x-0 lg:static lg:inset-0 shadow-xl">
            
            {{-- Cabeçalho da Sidebar --}}
            <div class="flex items-center justify-center mt-8">
                <div class="flex items-center gap-3 px-4">
                    <img src="{{ asset('images/logo.png') }}" class="h-10 w-auto">
                    <span class="text-white text-xl font-bold">Admin</span>
                </div>
            </div>

            {{-- Links de Navegação --}}
            <nav class="mt-10 px-4 space-y-2">
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-[#358054] text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="mx-4 font-medium">Dashboard</span>
                </a>

                <a href="{{ route('admin.trees.index') }}" 
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.trees.*') ? 'bg-[#358054] text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    <span class="mx-4 font-medium">Árvores</span>
                </a>

                <a href="{{ route('admin.service-orders.index') }}" 
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.service-orders.*') ? 'bg-[#358054] text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span class="mx-4 font-medium">Ordens de Serviço</span>
                </a>

                <a href="{{ route('admin.contacts.index') }}" 
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.contacts.*') ? 'bg-[#358054] text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="mx-4 font-medium">Solicitações</span>
                </a>

                <a href="{{ route('admin.profile.edit') }}" 
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.profile.*') ? 'bg-[#358054] text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="mx-4 font-medium">Perfil Admin</span>
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-8 pt-8 border-t border-gray-700">
                    @csrf
                    <button type="submit" class="flex w-full items-center px-4 py-3 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="mx-4 font-medium">Sair</span>
                    </button>
                </form>
            </nav>
        </aside>

        {{-- 3. CONTEÚDO PRINCIPAL (Main Content) --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            
            {{-- HEADER MOBILE (Só aparece em telas pequenas) --}}
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 lg:hidden">
                <div class="flex items-center gap-3">
                    {{-- BOTÃO HAMBÚRGUER --}}
                    <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none focus:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <span class="text-lg font-bold text-[#358054]">Admin Panel</span>
                </div>
                
                {{-- Avatarzinho ou Nome no Mobile (Opcional) --}}
                <div class="text-sm font-semibold text-gray-600">
                    {{ Auth::guard('admin')->user()->name ?? 'Admin' }}
                </div>
            </header>

            {{-- ÁREA DE ROLAGEM --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 md:p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
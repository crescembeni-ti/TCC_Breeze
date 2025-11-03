<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        
                        <h3 class="text-lg font-semibold text-gray-800">Painel de Administrador</h3>
                        
                        <p class="mt-2 text-gray-600">
                            Você está vendo esta seção porque tem permissões de administrador.
                        </p>

                        <div class="mt-4 space-x-2"> 
                            
                            <!-- Botão 1: Mapa -->
                            <a href="{{ route('admin.map') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Acessar Mapa de Administração
                            </a>
                            
                            <!-- Botão 2: Gerenciar Árvores -->
                            <a href="{{ route('admin.trees.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Gerenciar Árvores Cadastradas
                            </a>

                            <!-- [NOVO] Botão 3: Ver Mensagens -->
                            <a href="{{ route('admin.contacts.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ver Mensagens
                            </a>
                        </div>

                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>
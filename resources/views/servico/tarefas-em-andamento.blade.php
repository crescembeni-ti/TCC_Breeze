@extends('layouts.dashboard')
@section('title', 'Em Andamento')

@section('content'<div x-data="{ 
    open: false, 
    showPhoto: false, 
    showLightbox: false,
    photoUrl: '', 
    item: { id: '', contact: { status: {} }, motivos: [], servicos: [], equipamentos: [] },
    currentPhotos: [],
    openCarousel(photos) {
        this.currentPhotos = photos;
        this.showPhoto = true;
    }
}">  
    <header class="bg-white shadow mb-8 rounded-lg p-6">
        <h2 class="text-3xl font-semibold text-blue-700">Tarefas em Andamento</h2>
        <p class="text-gray-600 mt-1">Serviços iniciados que precisam ser concluídos.</p>
    </header>

    @if($ordens->isEmpty())
        
        {{-- ESTADO VAZIO --}}
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg p-12 text-center flex flex-col items-center justify-center min-h-[300px]">
            <div class="bg-blue-50 p-4 rounded-full mb-4">
                <i data-lucide="play-circle" class="w-12 h-12 text-blue-700"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Nenhuma tarefa em andamento</h3>
            <p class="text-gray-500">Todas as tarefas iniciadas foram concluídas.</p>
        </div>

    @else
        
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                            {{-- NOVA COLUNA: DATA DO VISTO --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data do Visto</th>
                            {{-- NOVA COLUNA: STATUS --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fotos</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($ordens as $os)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- PROTOCOLO --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-500">
                                #{{ $os->contact->id }}
                            </td>
                            
                            {{-- LOCAL --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="font-bold text-gray-900">{{ $os->contact->bairro }}</div>
                                <div class="text-xs text-gray-500">{{ $os->contact->rua }}</div>
                            </td>

                            {{-- DATA DO VISTO (ALTERADO PARA OLHO AZUL) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="flex items-center gap-1">
                                    {{-- ÍCONE DE OLHO AZUL --}}
                                    <i data-lucide="eye" class="w-4 h-4 text-blue-500 mr-1"></i>
                                    <span>{{ $os->updated_at->format('d/m/Y') }}</span>
                                    <span class="text-gray-500 text-xs">às</span>
                                    <span>{{ $os->updated_at->format('H:i') }}</span>
                                </div>
                            </td>

                            {{-- STATUS (FIXO: EM EXECUÇÃO) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Em Execução
                                </span>
                            </td>

                            {{-- FOTOS --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @php
                                    $fotos = $os->contact->fotos;
                                    if (is_string($fotos)) {
                                        $fotos = json_decode($fotos, true);
                                    }
                                @endphp
                                @if(is_array($fotos) && count($fotos) > 0)
                                    <button @click="openCarousel({{ json_encode($fotos) }})" class="flex items-center gap-1 text-[#358054] hover:underline font-bold">
                                        <i data-lucide="images" class="w-4 h-4"></i> Ver Fotos
                                    </button>
                                @elseif($os->contact->foto_path)
                                    <button @click="showPhoto = true; photoUrl = '{{ Storage::url($os->contact->foto_path) }}'; currentPhotos = [];" class="flex items-center gap-1 text-[#358054] hover:underline font-bold">
                                        <i data-lucide="image" class="w-4 h-4"></i> Ver Foto
                                    </button>
                                @else
                                    <span class="text-gray-400 italic">Sem foto</span>
                                @endif
                            </td>

                            {{-- AÇÕES (BOTÃO VER OS) --}}
                            <td class="px-6 py-4 text-right">
                                <button @click="open = true; item = {{ json_encode($os->load('contact.status')) }}" 
                                    class="text-green-700 hover:text-blue-900 font-bold border border-green-700 px-4 py-1.5 rounded hover:bg-blue-50 transition flex items-center gap-2 ml-auto">
                                    <i data-lucide="eye" class="w-4 h-4"></i> Conferir OS
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- MODAL DE FOTOS (ESTILO ADMIN CORRIGIDO) --}}
    <div x-show="showPhoto" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4" 
         style="display: none;" 
         x-cloak>
        <div class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" @click="showPhoto = false"></div>
        
        <div class="relative bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden transform transition-all z-10 mx-auto">
            {{-- Header do Modal --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-xl font-bold text-[#358054] flex-1 text-center">Fotos</h2>
                <button @click="showPhoto = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            {{-- Conteúdo das Fotos --}}
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[65vh] overflow-y-auto custom-scrollbar pr-1">
                    <template x-for="(photo, index) in currentPhotos" :key="index">
                        <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-gray-100 shadow-sm border border-gray-200">
                            <img :src="'/storage/' + photo" 
                                 class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="photoUrl = '/storage/' + photo; showLightbox = true;">
                        </div>
                    </template>
                    <template x-if="currentPhotos.length === 0 && photoUrl">
                        <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-gray-100 shadow-sm border border-gray-200">
                            <img :src="photoUrl" 
                                 class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="showLightbox = true;">
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- LIGHTBOX PARA AMPLIAR (ESTILO ADMIN) --}}
    <div x-show="showLightbox" 
         class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/95 p-4" 
         style="display: none;" 
         x-cloak 
         @click.self="showLightbox = false">
        <button @click="showLightbox = false" class="absolute top-5 right-10 text-white text-5xl cursor-pointer hover:text-gray-300 transition-colors z-[10001]">&times;</button>
        <img :src="photoUrl" 
             class="max-w-full max-h-full sm:max-w-[90vw] sm:max-h-[90vh] object-contain shadow-2xl rounded-sm"
             @click.stop>
    </div>

    {{-- MODAL (COM BOTÕES DE CONCLUSÃO ATIVADOS) --}}
    @include('servico.partials.modal_os', ['action' => 'concluir'])
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => { 
        lucide.createIcons(); 
    });
</script>
@endsection

@extends('layouts.dashboard')
@section('title', 'Em Andamento')

@section('content'<div x-data="{ 
    open: false, 
    showPhoto: false, 
    photoUrl: '', 
    item: { id: '', contact: { status: {} }, motivos: [], servicos: [], equipamentos: [] },
    currentPhotos: [],
    currentIndex: 0,
    openCarousel(photos) {
        this.currentPhotos = photos;
        this.currentIndex = 0;
        this.photoUrl = '/storage/' + this.currentPhotos[0];
        this.showPhoto = true;
    },
    nextPhoto() {
        this.currentIndex = (this.currentIndex + 1) % this.currentPhotos.length;
        this.photoUrl = '/storage/' + this.currentPhotos[this.currentIndex];
    },
    prevPhoto() {
        this.currentIndex = (this.currentIndex - 1 + this.currentPhotos.length) % this.currentPhotos.length;
        this.photoUrl = '/storage/' + this.currentPhotos[this.currentIndex];
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
                                        <i data-lucide="images" class="w-4 h-4"></i> Ver Fotos ({{ count($fotos) }})
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

    {{-- MODAL DE FOTO (LIGHTBOX COM CARROSSEL) --}}
    <div x-show="showPhoto" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-90 p-4" style="display: none;" x-cloak>
        <div class="fixed inset-0" @click="showPhoto = false"></div>
        
        <div class="relative max-w-5xl w-full flex items-center justify-center z-10">
            {{-- Botão Fechar --}}
            <button @click="showPhoto = false" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
                <i data-lucide="x" class="w-10 h-10"></i>
            </button>

            {{-- Navegação Esquerda --}}
            <template x-if="currentPhotos.length > 1">
                <button @click="prevPhoto()" class="absolute left-0 sm:-left-16 text-white hover:text-green-400 transition p-2 bg-black/20 rounded-full">
                    <i data-lucide="chevron-left" class="w-12 h-12"></i>
                </button>
            </template>

            {{-- Imagem --}}
            <div class="flex flex-col items-center gap-4">
                <img :src="photoUrl" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl border-2 border-white/20">
                
                {{-- Indicador de Fotos --}}
                <template x-if="currentPhotos.length > 1">
                    <div class="flex gap-2">
                        <template x-for="(f, i) in currentPhotos" :key="i">
                            <div class="w-2.5 h-2.5 rounded-full transition-all" 
                                 :class="currentIndex === i ? 'bg-green-500 w-6' : 'bg-white/50'"></div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Navegação Direita --}}
            <template x-if="currentPhotos.length > 1">
                <button @click="nextPhoto()" class="absolute right-0 sm:-right-16 text-white hover:text-green-400 transition p-2 bg-black/20 rounded-full">
                    <i data-lucide="chevron-right" class="w-12 h-12"></i>
                </button>
            </template>
        </div>
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

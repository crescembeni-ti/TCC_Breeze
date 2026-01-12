@extends('layouts.dashboard')

{{-- TÍTULO DA PÁGINA NO NAVEGADOR --}}
@section('title', 'Editar Sobre')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<style>
    /* Força o editor a ficar ACIMA do menu lateral e do topo quando em tela cheia */
    .note-fullscreen {
        z-index: 9999 !important; /* Um número bem alto para cobrir tudo */
        background-color: white !important; /* Garante fundo branco */
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        position: fixed !important;
    }

    /* Ajuste para o vídeo ficar responsivo dentro do editor */
    .note-video-clip {
        max-width: 100%;
        height: auto;
    }
    
    /* Garante que o modal de inserir vídeo apareça na frente de tudo */
    .note-modal-backdrop {
        z-index: 1040 !important;
    }
    .note-modal {
        z-index: 1050 !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-pt-BR.min.js"></script>

<div class="p-6 bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        
        <div class="bg-[#358054] p-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">🌿 Editar Página: Sobre o Projeto</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-gray-200 text-sm underline">
                Voltar ao Painel
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 mx-6 mt-6">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-lg font-semibold text-gray-700 mb-2">Título da Página</label>
                <input type="text" name="title" value="{{ old('title', $pageContent->title ?? 'Sobre o Projeto') }}"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>

            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-lg font-bold text-[#358054] mb-3">📖 Introdução / Visão Geral</label>
                <textarea name="content" class="summernote">{{ old('content', $pageContent->content ?? '') }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Este é o texto principal que aparece no topo da página.</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-lg font-bold text-[#358054] mb-3">🎯 Nossa Missão</label>
                <textarea name="mission_content" class="summernote">{{ old('mission_content', $pageContent->mission_content ?? '') }}</textarea>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-lg font-bold text-[#358054] mb-3">⚙️ Como Funciona</label>
                <textarea name="how_it_works_content" class="summernote">{{ old('how_it_works_content', $pageContent->how_it_works_content ?? '') }}</textarea>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-lg font-bold text-[#358054] mb-3">🌳 Benefícios das Árvores</label>
                <textarea name="benefits_content" class="summernote">{{ old('benefits_content', $pageContent->benefits_content ?? '') }}</textarea>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-green-600 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:bg-green-700 transition transform hover:scale-105">
                    💾 Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            placeholder: 'Digite o conteúdo aqui...',
            tabsize: 2,
            height: 200,
            lang: 'pt-BR',
            // CORREÇÃO 1: Configurações para garantir que o vídeo funcione melhor
            dialogsInBody: true, // Isso ajuda a evitar conflitos de layout nos modais
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']], // Certifique-se de usar links do YouTube/Vimeo
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            // Callbacks para depuração se necessário
            callbacks: {
                onInit: function() {
                    console.log('Summernote is launched');
                }
            }
        });
    });
</script>

@endsection
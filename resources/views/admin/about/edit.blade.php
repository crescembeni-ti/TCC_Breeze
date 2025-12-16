@extends('layouts.dashboard')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor .note-toolbar {
        background-color: #f3f4f6; /* Cinza claro */
        border-bottom: 1px solid #e5e7eb;
    }
    .note-editor {
        background: white;
        border-color: #d1d5db !important; /* Borda cinza do tailwind */
    }
</style>

<main class="p-10">
    <div class="bg-white shadow-sm rounded-lg p-8 max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-[#358054] mb-6">Editar Página: Sobre o Projeto</h2>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Título da Página</label>
                <input type="text" name="title" id="title" value="{{ old('title', $pageContent->title ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border">
            </div>

            <div class="mb-8">
                <label for="content" class="block text-sm font-medium text-gray-700 font-bold mb-2">Introdução / Visão Geral</label>
                <textarea name="content" class="summernote">{{ old('content', $pageContent->content ?? '') }}</textarea>
            </div>

            <hr class="my-6 border-gray-200">

            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 font-bold mb-2">Nossa Missão</label>
                <textarea name="mission_content" class="summernote">{{ old('mission_content', $pageContent->mission_content ?? '') }}</textarea>
            </div>
            
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 font-bold mb-2">Como Funciona</label>
                <textarea name="how_it_works_content" class="summernote">{{ old('how_it_works_content', $pageContent->how_it_works_content ?? '') }}</textarea>
            </div>
            
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 font-bold mb-2">Benefícios das Árvores</label>
                <textarea name="benefits_content" class="summernote">{{ old('benefits_content', $pageContent->benefits_content ?? '') }}</textarea>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-green-600 text-white font-bold rounded-md shadow-md hover:bg-green-700 transition mt-6">
                Salvar Alterações
            </button>
        </form>
    </div>
</main>

{{-- ============================================================= --}}
{{-- SCRIPTS: jQuery (Obrigatório pro Summernote) + Summernote JS  --}}
{{-- ============================================================= --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            placeholder: 'Digite o conteúdo aqui...',
            tabsize: 2,
            height: 200, // Altura do editor
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']], // Botões de Mídia
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            // Callback para garantir que imagens coladas funcionem
            callbacks: {
                onImageUpload: function(files) {
                    // O Summernote por padrão converte imagem em Base64 (texto),
                    // o que funciona perfeitamente para o seu banco de dados (longText).
                    // Não precisa mudar nada!
                }
            }
        });
    });
</script>
@endsection
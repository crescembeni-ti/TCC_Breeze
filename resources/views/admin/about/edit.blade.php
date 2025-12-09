@extends('layouts.dashboard')

@section('content')
<main class="p-10">
    <div class="bg-white shadow-sm rounded-lg p-8 max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-[#358054] mb-6">Editar Página: Sobre o Projeto</h2>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.about.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Título da Página</label>
                <input type="text" name="title" id="title" value="{{ old('title', $pageContent->title) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700">Conteúdo Principal (Introdução/Visão Geral)</label>
                <textarea name="content" id="content" class="mt-1 block w-full rounded-md shadow-sm rich-editor">{{ old('content', $pageContent->content) }}</textarea>
            </div>

            <h3 class="text-xl font-semibold mt-8 mb-3 border-b pb-1">Nossa Missão</h3>
            <div class="mb-6">
                <textarea name="mission_content" class="mt-1 block w-full rounded-md shadow-sm rich-editor">{{ old('mission_content', $pageContent->mission_content) }}</textarea>
            </div>
            
            <h3 class="text-xl font-semibold mt-8 mb-3 border-b pb-1">Como Funciona</h3>
            <p class="text-sm text-gray-500 mb-2">Use listas (<ul>) para o passo a passo.</p>
            <div class="mb-6">
                <textarea name="how_it_works_content" class="mt-1 block w-full rounded-md shadow-sm rich-editor">{{ old('how_it_works_content', $pageContent->how_it_works_content) }}</textarea>
            </div>
            
            <h3 class="text-xl font-semibold mt-8 mb-3 border-b pb-1">Benefícios das Árvores Urbanas</h3>
            <div class="mb-6">
                <textarea name="benefits_content" class="mt-1 block w-full rounded-md shadow-sm rich-editor">{{ old('benefits_content', $pageContent->benefits_content) }}</textarea>
            </div>

            <button type="submit" class="w-full py-2 px-4 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 transition mt-6">
                Salvar Alterações
            </button>
        </form>
    </div>
</main>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '.rich-editor', // Classe usada nos Textareas
        plugins: 'advlist autolink lists link image charmap print preview anchor',
        toolbar_mode: 'floating',
        // Configuração mínima para permitir HTML e media
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | image media',
        height: 400
    });
</script>
@endsection
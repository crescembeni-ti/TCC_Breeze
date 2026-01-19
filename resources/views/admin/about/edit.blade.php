@extends('layouts.dashboard')

@section('title', 'Editar Sobre')

@section('content')

{{-- Carrega CSS do Summernote --}}
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-pt-BR.min.js"></script>

<div class="p-6 bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        
        <div class="bg-[#358054] p-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">🌿 Editar Página: Sobre o Projeto</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-gray-200 text-sm underline">Voltar</a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 mx-6 mt-6">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 mx-6 mt-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-lg font-semibold text-gray-700 mb-2">Título da Página</label>
                <input type="text" name="title" value="{{ old('title', $pageContent->title ?? 'Sobre o Projeto') }}"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
            </div>

            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-lg font-bold text-[#358054] mb-3">📖 Introdução / Visão Geral</label>
                <textarea name="content" class="summernote">{{ old('content', $pageContent->content ?? '') }}</textarea>
            </div>

            <hr class="border-t-2 border-gray-200 my-8">

            <h3 class="text-xl font-bold text-gray-800">Seções Adicionais</h3>
            <p class="text-sm text-gray-500 mb-4">Adicione quantas caixas de conteúdo desejar (Ex: Missão, Valores, História).</p>

            <div id="sections-container" class="space-y-6">
                @php
                    // Recupera as seções salvas ou um array vazio
                    $sections = old('sections', $pageContent->sections ?? []);
                @endphp

                @foreach($sections as $index => $section)
                <div class="section-item p-6 bg-white rounded-xl border border-gray-300 shadow-sm relative group hover:border-[#358054] transition-colors">
                    
                    {{-- Botão de Remover --}}
                    <button type="button" onclick="removeSection(this)" class="absolute top-4 right-4 text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition" title="Remover seção">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>

                    <div class="mb-4 pr-12">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Título da Seção</label>
                        <input type="text" name="sections[{{ $index }}][title]" value="{{ $section['title'] ?? '' }}" 
                               class="w-full p-2 border border-gray-300 rounded focus:ring-1 focus:ring-[#358054]" placeholder="Ex: Nossa Missão">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Conteúdo</label>
                        <textarea name="sections[{{ $index }}][content]" class="summernote-dynamic">{{ $section['content'] ?? '' }}</textarea>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" onclick="addSection()" class="w-full py-3 border-2 border-dashed border-[#358054] text-[#358054] font-bold rounded-lg hover:bg-green-50 transition flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Adicionar Nova Seção
            </button>

            <div class="flex justify-end pt-6 border-t mt-8">
                <button type="submit" class="bg-green-600 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:bg-green-700 transition transform hover:scale-105">
                    💾 Salvar Tudo
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Scripts para Manipulação Dinâmica --}}
<script>
    // Contador para gerar índices únicos (evita conflito ao adicionar/remover)
    let sectionCount = {{ count(old('sections', $pageContent->sections ?? [])) }};

    function initSummernote(selector) {
        $(selector).summernote({
            placeholder: 'Escreva o conteúdo...',
            tabsize: 2,
            height: 150,
            lang: 'pt-BR',
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    }

    // Inicia os editores já existentes ao carregar
    $(document).ready(function() {
        initSummernote('.summernote'); // O principal
        initSummernote('.summernote-dynamic'); // Os dinâmicos já carregados do banco
    });

    function addSection() {
        const index = sectionCount++;
        const html = `
            <div class="section-item p-6 bg-white rounded-xl border border-gray-300 shadow-sm relative group hover:border-[#358054] transition-colors mt-6 slide-in">
                <button type="button" onclick="removeSection(this)" class="absolute top-4 right-4 text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition" title="Remover seção">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </button>

                <div class="mb-4 pr-12">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Título da Seção</label>
                    <input type="text" name="sections[${index}][title]" class="w-full p-2 border border-gray-300 rounded focus:ring-1 focus:ring-[#358054]" placeholder="Título da nova seção">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Conteúdo</label>
                    <textarea id="summernote-${index}" name="sections[${index}][content]" class="summernote-dynamic"></textarea>
                </div>
            </div>
        `;

        $('#sections-container').append(html);
        
        // Inicializa o Summernote no novo textarea criado
        initSummernote(`#summernote-${index}`);
    }

    function removeSection(button) {
        if(confirm('Tem certeza que deseja remover esta seção?')) {
            $(button).closest('.section-item').remove();
        }
    }
</script>

<style>
    .slide-in {
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

@endsection
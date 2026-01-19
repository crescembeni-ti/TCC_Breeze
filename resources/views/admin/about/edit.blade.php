@extends('layouts.dashboard')

@section('title', 'Editar Sobre')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-pt-BR.min.js"></script>

<div class="p-6 bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        
        <div class="bg-[#358054] p-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">🌿 Editar Página: Sobre o Projeto</h2>
            <a href="{{ route('about') }}" target="_blank" class="text-white hover:text-green-200 text-sm underline flex items-center gap-1">
                Ver Página <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 mx-6 mt-6 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                <label class="block text-lg font-bold text-gray-800 mb-2">Título Principal da Página</label>
                <input type="text" name="title" value="{{ old('title', $pageContent->title ?? 'Sobre o Projeto') }}"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#358054] outline-none text-lg">
            </div>

            <div>
                <label class="block text-lg font-bold text-[#358054] mb-3 flex items-center gap-2">
                    📖 Introdução / Texto Principal
                </label>
                {{-- AQUI ESTAVA O PROBLEMA: Usar {!! !!} permite carregar HTML salvo --}}
                <textarea name="content" class="summernote">{!! old('content', $pageContent->content ?? '') !!}</textarea>
            </div>

            <div class="border-t-2 border-dashed border-gray-300 my-8"></div>

            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">📦 Seções Adicionais</h3>
                <p class="text-gray-500 mb-6">Aqui você pode adicionar caixas de conteúdo personalizadas (Ex: Missão, Visão, História).</p>

                <div id="sections-container" class="space-y-6">
                    @php
                        $sections = old('sections', $pageContent->sections ?? []);
                        if (!is_array($sections)) $sections = [];
                    @endphp

                    @foreach($sections as $index => $section)
                    <div class="section-item p-6 bg-white rounded-xl border border-gray-300 shadow-sm relative group hover:border-[#358054] transition-all">
                        
                        <button type="button" onclick="removeSection(this)" class="absolute top-4 right-4 text-red-500 hover:text-white hover:bg-red-500 p-2 rounded-lg transition border border-red-200 hover:border-red-500" title="Remover esta seção">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>

                        <div class="mb-4 pr-12">
                            <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wide">Título da Caixa</label>
                            <input type="text" name="sections[{{ $index }}][title]" value="{{ $section['title'] ?? '' }}" 
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#358054] outline-none font-semibold text-gray-800" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wide">Conteúdo</label>
                            {{-- CORREÇÃO AQUI TAMBÉM --}}
                            <textarea name="sections[{{ $index }}][content]" class="summernote-dynamic">{!! $section['content'] ?? '' !!}</textarea>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="addSection()" class="mt-6 w-full py-4 border-2 border-dashed border-[#358054] text-[#358054] bg-green-50 font-bold rounded-xl hover:bg-[#358054] hover:text-white transition flex items-center justify-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Adicionar Nova Caixa de Conteúdo
                </button>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 shadow-lg flex justify-end z-50 md:static md:bg-transparent md:border-0 md:shadow-none md:p-0 md:mt-8">
                <button type="submit" class="bg-[#358054] text-white font-bold py-3 px-8 rounded-lg shadow-md hover:bg-[#2d6e4b] transition transform hover:scale-105 w-full md:w-auto">
                    💾 Salvar Alterações
                </button>
            </div>
        </form>
    </div>
    <div class="h-20 md:hidden"></div>
</div>

<script>
    let sectionCount = {{ count($sections) }};

    function initSummernote(selector) {
        $(selector).summernote({
            placeholder: 'Digite o texto aqui...',
            tabsize: 2,
            height: 200,
            lang: 'pt-BR',
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    }

    $(document).ready(function() {
        initSummernote('.summernote');
        initSummernote('.summernote-dynamic');
    });

    function addSection() {
        const index = sectionCount++;
        const html = `
            <div class="section-item p-6 bg-white rounded-xl border border-gray-300 shadow-sm relative group hover:border-[#358054] transition-all mt-6 animate-fade-in">
                <button type="button" onclick="removeSection(this)" class="absolute top-4 right-4 text-red-500 hover:text-white hover:bg-red-500 p-2 rounded-lg transition border border-red-200 hover:border-red-500" title="Remover seção">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </button>
                <div class="mb-4 pr-12">
                    <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wide">Título da Caixa</label>
                    <input type="text" name="sections[${index}][title]" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#358054] outline-none font-semibold text-gray-800" placeholder="Digite o título" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wide">Conteúdo</label>
                    <textarea id="summernote-${index}" name="sections[${index}][content]" class="summernote-dynamic"></textarea>
                </div>
            </div>
        `;
        $('#sections-container').append(html);
        initSummernote(`#summernote-${index}`);
    }

    function removeSection(button) {
        if(confirm('Tem certeza que deseja apagar esta seção?')) {
            $(button).closest('.section-item').remove();
        }
    }
</script>

<style>
    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

@endsection
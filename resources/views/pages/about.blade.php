@extends('layouts.dashboard')

@section('title', $pageContent->title ?? 'Sobre o Projeto')

@section('content')
<style>
    .prose p { margin-bottom: 1rem; line-height: 1.6; color: #4b5563; }
    .prose ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
    .prose strong { color: #358054; font-weight: 700; }
    .prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 10px 0; }
</style>

<div class="max-w-5xl mx-auto py-4">
    {{-- CARD PRINCIPAL (Título Creme + Sombra) --}}
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden mb-10 border border-gray-100">
        <div class="bg-gradient-to-r from-[#358054] to-[#4caf50] p-8 text-center relative">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#fefce8] drop-shadow-md">
                {{ $pageContent->title ?? 'Sobre o Projeto' }}
            </h1>
            @if(auth('admin')->check())
                <a href="{{ route('admin.about.edit') }}" class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 text-white p-2 rounded-lg transition-colors shadow-sm" title="Editar Conteúdo">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </a>
            @endif
        </div>
        
        @if(!empty($pageContent->content))
        <div class="p-8 sm:p-10 text-lg leading-relaxed text-gray-700">
            <div class="prose max-w-none">
                {!! $pageContent->content !!}
            </div>
        </div>
        @endif
    </div>

    {{-- SEÇÕES DINÂMICAS --}}
    <div class="grid grid-cols-1 gap-8">
        @if(!empty($pageContent->sections) && is_array($pageContent->sections))
            @foreach($pageContent->sections as $section)
                <section class="bg-white/95 backdrop-blur-sm rounded-xl shadow-md border-l-4 border-[#358054] overflow-hidden transition-all hover:shadow-lg">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-2xl font-bold text-[#358054] mb-4 border-b border-gray-100 pb-3">
                            {{ $section['title'] }}
                        </h2>
                        <div class="prose max-w-none text-gray-600">
                            {!! $section['content'] !!}
                        </div>
                    </div>
                </section>
            @endforeach
        @else
            <div class="text-center py-12 px-6 bg-white/90 rounded-lg border border-dashed border-gray-300">
                <h3 class="text-lg font-medium text-gray-900">Carregando conteúdo...</h3>
                <p class="text-gray-500 mt-1">Atualize a página para restaurar as seções padrão.</p>
            </div>
        @endif
    </div>
</div>
@endsection

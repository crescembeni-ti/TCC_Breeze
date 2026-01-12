@extends('layouts.dashboard')

@section('title', 'Editar Sobre')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<style>
    /* Estilo para o vídeo inserido ficar responsivo */
    .note-video-clip, video {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .note-modal-backdrop { z-index: 1040 !important; }
    .note-modal { z-index: 1050 !important; }
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

            <input type="file" id="video-upload-input" accept="video/mp4,video/webm,video/ogg" style="display: none;">

            <div>
                <label class="block text-lg font-semibold text-gray-700 mb-2">Título da Página</label>
                <input type="text" name="title" value="{{ old('title', $pageContent->title ?? 'Sobre o Projeto') }}"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>

            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-lg font-bold text-[#358054] mb-3">📖 Introdução / Visão Geral</label>
                <textarea name="content" class="summernote">{{ old('content', $pageContent->content ?? '') }}</textarea>
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
        
        // Variável para saber qual editor chamou o upload (já que você tem vários textareas)
        var currentEditorContext = null;

        // Função que cria o botão personalizado
        var VideoUploadButton = function (context) {
            var ui = $.summernote.ui;
            var button = ui.button({
                contents: '<i class="note-icon-video"></i> Upload MP4',
                tooltip: 'Fazer upload de vídeo do PC',
                click: function () {
                    // 1. Salva qual editor foi clicado
                    currentEditorContext = context;
                    // 2. Simula o clique no input de arquivo invisível
                    $('#video-upload-input').trigger('click');
                }
            });
            return button.render();
        }

        // Configuração do Summernote
        $('.summernote').summernote({
            placeholder: 'Digite o conteúdo aqui...',
            tabsize: 2,
            height: 200,
            lang: 'pt-BR',
            dialogsInBody: true,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'videoUpload']], // Adicionei 'videoUpload' aqui
            ],
            buttons: {
                videoUpload: VideoUploadButton // Registra o botão
            }
        });

        // Lógica do Upload via AJAX quando o arquivo é selecionado
        $('#video-upload-input').on('change', function() {
            var file = this.files[0];
            var reader = new FileReader();

            if (file) {
                // Prepara os dados para envio
                var formData = new FormData();
                formData.append("video", file);

                // Mostra um texto de "Enviando..." (Opcional, mas bom para UX)
                if(currentEditorContext) {
                    currentEditorContext.invoke('editor.saveRange'); // Salva onde o cursor estava
                    currentEditorContext.invoke('editor.pasteHTML', '<span id="temp-loading">🔄 Enviando vídeo... aguarde...</span>');
                }

                $.ajax({
                    url: "{{ route('admin.upload.video') }}", // Rota que criamos no Laravel
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Remove o texto de carregamento
                        var loadingText = document.getElementById('temp-loading');
                        if(loadingText) loadingText.remove();

                        // Cria a tag de vídeo HTML5
                        var videoNode = document.createElement('video');
                        videoNode.src = response.url;
                        videoNode.controls = true;
                        videoNode.style.maxWidth = "100%";
                        
                        // Insere no editor correto
                        if(currentEditorContext) {
                            currentEditorContext.invoke('editor.restoreRange');
                            currentEditorContext.invoke('editor.insertNode', videoNode);
                            // Adiciona um parágrafo vazio depois para não quebrar layout
                            currentEditorContext.invoke('editor.pasteHTML', '<p><br></p>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Erro ao enviar vídeo: ' + textStatus);
                        var loadingText = document.getElementById('temp-loading');
                        if(loadingText) loadingText.remove();
                    }
                });
            }
            
            // Limpa o input para permitir enviar o mesmo arquivo novamente se quiser
            $(this).val('');
        });
    });
</script>

@endsection
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/about.css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .content-box {
            padding: 3rem;
            margin-top: 30px;
        }

        /* BARRA DE EDIÇÃO FLUTUANTE (Layout da Imagem) */
        .floating-editor-bar {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            background: #e9ecef; /* Cor cinza clara para a base da barra */
            border: 1px solid #c9cdd0;
            border-radius: 8px;
            padding: 5px 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .floating-editor-bar button {
            transition: background 0.2s;
            font-weight: 500;
        }

        /* Botões de Ação na barra flutuante (Salvar / Cancelar) */
        .action-button {
            padding: 6px 12px;
            border-radius: 4px;
        }
        
        /* Botões de Inserção */
        .insert-button {
            background-color: #358054;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
        }

        /* Estilo do bloco em modo edição */
        .edit-mode .block-wrapper {
            position: relative;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px dashed #00b050; /* Borda verde tracejada */
            background-color: #f9fff5;
        }
        
        /* Controles do bloco (mover/remover) */
        .block-controls {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #00b050; 
            color: white;
            padding: 5px;
            border-radius: 4px;
            z-index: 10;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .block-controls button {
            background: transparent;
            border: none;
            color: white;
            font-weight: bold;
            line-height: 1;
            padding: 2px 5px;
            transition: background 0.2s;
        }
        
        /* Estilo para campos contenteditable */
        .edit-mode [contenteditable="true"]:focus {
            outline: 2px solid #358054;
            background-color: #ffffff;
        }
        .edit-mode [contenteditable="true"] {
            outline: 1px solid #ccc;
            padding: 5px;
            min-height: 20px;
            background-color: #ffffff;
            cursor: text;
        }

        /* Estilo para Modal de Seleção de Mídia */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 450px;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col" x-data="cmsEditor()">

        <input type="file" id="fileInput" class="hidden" accept="image/*" @change="handleImageFileUpload($event)">
        <input type="file" id="videoFileInput" class="hidden" accept="video/mp4,video/webm" @change="handleVideoFileUpload($event)">

        <header class="site-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">
                <div class="flex items-center gap-4 flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-4">
                        <img src="{{ asset('images/Brasao_Verde.png') }}" alt="Logo Brasão de Paracambi" class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <h1 class="text-3xl sm:text-4xl font-bold">
                            <span class="text-[#358054]">Sobre o</span>
                            <span class="text-[#a0c520]">Projeto</span>
                        </h1>
                    </a>
                </div>
                <div class="flex gap-4">
                    @if (auth('admin')->check())
                        <button @click="toggleEditMode()" class="action-button font-semibold transition flex items-center gap-2"
                            :class="editing ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700'">
                            <span x-show="!editing" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar Página
                            </span>
                            <span x-show="editing" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Sair da Edição
                            </span>
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="action-button bg-green-600 text-white hover:bg-green-700 font-semibold flex items-center gap-2">
                            Voltar ao Painel
                        </a>
                    @else
                        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="action-button bg-white text-green-700 hover:bg-gray-100 font-semibold">
                            Voltar ao Mapa
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <div x-show="editing" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="fixed top-28 left-1/2 transform -translate-x-1/2 z-50 flex items-center gap-3 px-5 py-3 bg-white/95 backdrop-blur-md shadow-2xl rounded-full border border-gray-200">
            
            <div class="flex items-center gap-2 pr-4 border-r border-gray-300">
                <button @click="saveContent()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-bold rounded-full shadow hover:bg-blue-700 hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Salvar
                </button>
                <button @click="toggleEditMode(true)" class="flex items-center gap-1 px-3 py-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors">
                    Cancelar
                </button>
            </div>
            
            <div class="flex items-center gap-3 pl-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Add:</span>
                
                <button @click="addBlock('text')" class="group flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 hover:scale-105 transition-all border border-gray-200" title="Texto">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                    <span class="text-sm font-semibold">Texto</span>
                </button>
                
                <button @click="openImageSelector()" class="group flex items-center gap-2 px-3 py-2 bg-purple-50 text-purple-700 rounded-full hover:bg-purple-100 hover:scale-105 transition-all border border-purple-200" title="Imagem">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>
                    <span class="text-sm font-semibold">Foto</span>
                </button>
                
                <button @click="openVideoSelectionModal()" class="group flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 rounded-full hover:bg-red-100 hover:scale-105 transition-all border border-red-200" title="Vídeo">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                    </svg>
                    <span class="text-sm font-semibold">Vídeo</span>
                </button>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-1">
            <div class="bg-white rounded-lg shadow-lg content-box relative" :class="{'ring-4 ring-green-100 ring-offset-2': editing}">
                
                <h2 class="text-3xl font-bold text-gray-900 mb-6" 
                    :contenteditable="editing" 
                    x-ref="pageTitle"
                    @blur="editing && (editingTitle = $event.target.innerText)">
                    {{ $pageContent->title }}
                </h2>
                
                <div class="prose max-w-none">
                    
                    <template x-for="(block, index) in contentBlocks" :key="block.id">
                        <div class="block-wrapper transition-all duration-300" :class="{'relative p-4 rounded-xl border-2 border-dashed border-gray-300 hover:border-green-400 hover:bg-gray-50': editing}">
                            
                            <div x-show="editing" class="absolute -top-3 right-4 flex bg-white shadow-md border border-gray-200 rounded-full overflow-hidden z-10 items-center">
                                <button @click.prevent="moveBlock(index, 'up')" class="p-1.5 hover:bg-gray-100 text-gray-600 border-r border-gray-100" title="Subir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                </button>
                                <button @click.prevent="moveBlock(index, 'down')" class="p-1.5 hover:bg-gray-100 text-gray-600 border-r border-gray-100" title="Descer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <button @click.prevent="removeBlock(index)" class="p-1.5 hover:bg-red-50 text-red-500" title="Excluir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>

                            <template x-if="block.type === 'text'">
                                <div class="text-editor-field min-h-[40px]" 
                                    :id="'editor-' + block.id"
                                    :contenteditable="editing"
                                    x-html="block.data.html"
                                    @blur="updateBlockData(index, 'html', $event.target.innerHTML)">
                                </div>
                            </template>
                            
                            <template x-if="block.type === 'image'">
                                <div class="flex flex-col gap-2">
                                    <div class="relative group">
                                        <img :src="'/storage/' + block.data.url" 
                                             @click="editing && openImageSelectorForEdit(index)"
                                             class="w-full h-auto max-h-[500px] object-cover rounded-lg shadow-sm" 
                                             :class="{'cursor-pointer hover:opacity-90': editing}"
                                             alt="Imagem">
                                        <div x-show="editing" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="bg-black/60 text-white px-4 py-2 rounded-full text-sm backdrop-blur flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Trocar Foto
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500 italic text-center" :contenteditable="editing" @blur="updateBlockData(index, 'caption', $event.target.innerHTML)" x-html="block.data.caption"></div>
                                </div>
                            </template>

                            <template x-if="block.type === 'youtube'">
                                <div class="flex flex-col gap-2">
                                    <div class="w-full aspect-video bg-black rounded-lg shadow-lg overflow-hidden relative">
                                        <iframe x-show="block.data.subType === 'yt'" :src="'https://www.youtube.com/embed/' + block.data.url" frameborder="0" allowfullscreen class="w-full h-full"></iframe>
                                        <video x-show="block.data.subType === 'local'" :src="'/storage/' + block.data.url" controls class="w-full h-full object-cover"></video>
                                        
                                        <div x-show="editing" class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow">
                                            VÍDEO
                                        </div>
                                    </div>
                                    <div class="text-lg font-semibold" :contenteditable="editing" @blur="updateBlockData(index, 'title', $event.target.innerHTML)" x-html="block.data.title"></div>
                                </div>
                            </template>
                            
                            <div x-show="editing" class="mt-4 pt-2 border-t border-dashed border-gray-200 flex justify-center items-center gap-3 opacity-50 hover:opacity-100 transition-opacity">
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest">Inserir:</span>
                                
                                <button @click.prevent="addBlock('text', index + 1)" class="p-1.5 bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 hover:scale-110 transition-transform" title="Texto">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                                </button>
                                
                                <button @click.prevent="openImageSelector(index + 1)" class="p-1.5 bg-purple-50 text-purple-600 rounded-full hover:bg-purple-100 hover:scale-110 transition-transform" title="Imagem">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>
                                </button>
                                
                                <button @click.prevent="openVideoSelectionModal(index + 1)" class="p-1.5 bg-red-50 text-red-600 rounded-full hover:bg-red-100 hover:scale-110 transition-transform" title="Vídeo">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </template>
                    
                    <div class="bg-green-50 border-l-4 border-green-500 p-6 mt-8">
                        <p class="text-green-800 font-semibold">
                            Para mais informações ou para reportar problemas, entre em contato conosco através da <a
                                href="{{ route('contact') }}" class="underline hover:text-[#38c224]">página de
                                solicitações</a>.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-gray-800 shadow mt-auto w-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
            </div>
        </footer>

        <div x-show="showVideoModal" class="modal-overlay" @click.self="showVideoModal = false">
            <div class="modal-content">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    Adicionar Vídeo
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button @click="selectYoutubeLink()" class="group flex flex-col items-center justify-center p-6 bg-white border-2 border-red-100 rounded-xl hover:border-red-500 hover:bg-red-50 transition-all shadow-sm hover:shadow-md">
                        <svg class="w-16 h-16 text-red-600 mb-2 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                        </svg>
                        <span class="font-bold text-gray-800 group-hover:text-red-700">YouTube</span>
                        <span class="text-xs text-gray-500">Via Link</span>
                    </button>

                    <button @click="selectLocalVideo()" class="group flex flex-col items-center justify-center p-6 bg-white border-2 border-blue-100 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all shadow-sm hover:shadow-md">
                        <svg class="w-16 h-16 text-blue-500 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="font-bold text-gray-800 group-hover:text-blue-700">Arquivo Local</span>
                        <span class="text-xs text-gray-500">Upload MP4</span>
                    </button>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="showVideoModal = false" class="py-2 px-4 text-gray-500 hover:text-gray-700 font-medium">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const isInitialEditing = @json($isEditing);

        function initializeTinyMCE(selector) {
            tinymce.remove(); 
            document.querySelectorAll(selector).forEach(el => {
                if (!el.id) {
                    el.id = 'editor-' + Date.now() + Math.random().toString(36).substring(2, 9);
                }

                tinymce.init({
                    selector: '#' + el.id,
                    plugins: 'autolink lists link image media table codesample',
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image media',
                    menubar: false,
                    height: 200,
                    inline: true, 
                    setup: function (editor) {
                        editor.on('blur', function () {
                            editor.save();
                            const event = new Event('blur', { bubbles: true });
                            editor.getElement().dispatchEvent(event);
                        });
                    }
                });
            });
        }
        
        function destroyTinyMCE() {
            tinymce.remove();
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('cmsEditor', () => ({
                editing: isInitialEditing,
                contentBlocks: @json($pageContent->content_blocks) || [],
                editingTitle: @json($pageContent->title),
                showVideoModal: false, 
                currentBlockIndexForUpload: null, 

                nextId() {
                    const ids = this.contentBlocks.map(b => b.id);
                    if(ids.length === 0) return 1;
                    return Math.max(...ids) + 1;
                },

                init() {
                    if (this.editing) {
                        this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    }
                },

                toggleEditMode(cancel = false) {
                    if (cancel) {
                        return window.location.reload();
                    }
                    this.editing = !this.editing;
                    if (this.editing) {
                        this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    } else {
                        destroyTinyMCE();
                    }
                },
                
                openImageSelector(targetIndex = this.contentBlocks.length) {
                    this.currentBlockIndexForUpload = targetIndex;
                    document.getElementById('fileInput').click();
                },

                openImageSelectorForEdit(index) {
                     this.currentBlockIndexForUpload = index;
                     document.getElementById('fileInput').click();
                },
                
                openVideoSelectionModal(targetIndex = this.contentBlocks.length) {
                    this.currentBlockIndexForUpload = targetIndex;
                    this.showVideoModal = true;
                },

                selectYoutubeLink() {
                    this.showVideoModal = false;
                    const videoId = prompt("Insira o ID do YouTube (ex: dQw4w9WgXcQ):");
                    if (videoId) {
                         this.addBlock('youtube', this.currentBlockIndexForUpload, videoId, 'yt');
                    }
                },

                selectLocalVideo() {
                    this.showVideoModal = false;
                    document.getElementById('videoFileInput').click();
                },

                handleImageFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    const fakePath = `simulacao/temp-img-${Date.now()}.jpg`; 

                    if (this.currentBlockIndexForUpload < this.contentBlocks.length && this.currentBlockIndexForUpload >= 0) {
                        if (this.contentBlocks[this.currentBlockIndexForUpload].type === 'text') {
                             this.addBlock('image', this.currentBlockIndexForUpload, fakePath);
                        } else {
                             this.contentBlocks[this.currentBlockIndexForUpload].data.url = fakePath;
                             this.contentBlocks[this.currentBlockIndexForUpload].data.caption = 'Nova imagem carregada';
                        }
                    } else {
                        this.addBlock('image', this.currentBlockIndexForUpload, fakePath);
                    }
                    
                    event.target.value = '';
                    this.currentBlockIndexForUpload = null;
                },

                handleVideoFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const fakePath = `simulacao/temp-video-${Date.now()}.mp4`; 
                    this.addBlock('youtube', this.currentBlockIndexForUpload, fakePath, 'local');
                    event.target.value = '';
                },
                
                addBlock(type, targetIndex, dataValue = null, subType = null) {
                    let newBlock = { id: this.nextId(), type: type, data: {} };
                    
                    if (type === 'text') {
                        newBlock.data.html = '<p>Novo bloco de texto editável. Clique aqui para digitar.</p>';
                    } else if (type === 'image') {
                        newBlock.data.url = dataValue || 'caminho/para/placeholder.jpg';
                        newBlock.data.caption = 'Legenda da imagem';
                    } else if (type === 'youtube') {
                        newBlock.data.url = dataValue || 'dQw4w9WgXcQ';
                        newBlock.data.title = 'Título do Vídeo';
                        newBlock.data.subType = subType || 'yt'; 
                    }
                    
                    this.contentBlocks.splice(targetIndex, 0, newBlock);
                    
                    this.$nextTick(() => {
                        initializeTinyMCE('.block-wrapper [contenteditable=true]');
                    });
                },

                removeBlock(index) {
                    if (confirm('Tem certeza que deseja remover este bloco?')) {
                        this.contentBlocks.splice(index, 1);
                        this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    }
                },

                moveBlock(index, direction) {
                    const block = this.contentBlocks[index];
                    let newIndex = index;

                    if (direction === 'up' && index > 0) {
                        newIndex = index - 1;
                    } else if (direction === 'down' && index < this.contentBlocks.length - 1) {
                        newIndex = index + 1;
                    }
                    
                    if (newIndex !== index) {
                         this.contentBlocks.splice(index, 1);
                         this.contentBlocks.splice(newIndex, 0, block);
                         this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    }
                },

                updateBlockData(index, key, value) {
                    if (key === 'html') {
                        this.contentBlocks[index].data.html = value;
                    } else {
                        this.contentBlocks[index].data[key] = value;
                    }
                },

                async saveContent() {
                    if (typeof tinymce !== 'undefined') tinymce.triggerSave();

                    const title = this.$refs.pageTitle.innerText;
                    const contentBlocksJson = JSON.stringify(this.contentBlocks);

                    try {
                        const response = await fetch("{{ route('admin.about.update') }}", {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                            },
                            body: JSON.stringify({
                                title: title,
                                content_blocks_json: contentBlocksJson,
                                _token: '{{ csrf_token() }}'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert(data.message);
                            this.editing = false; 
                        } else {
                            alert("Falha ao salvar: " + (data.message || 'Erro desconhecido.'));
                        }

                    } catch (error) {
                        console.error('Erro ao salvar conteúdo:', error);
                        alert('Erro de conexão ou servidor ao salvar.');
                    }
                },
            }));
        });
    </script>
</body>

</html>
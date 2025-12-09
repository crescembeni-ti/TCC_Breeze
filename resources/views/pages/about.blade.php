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
                        <img src="{{ asset('images/Brasao_Verde.png') }}" alt="Logo Brasão de Paracambi"
                            class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi"
                            class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                        <h1 class="text-3xl sm:text-4xl font-bold">
                            <span class="text-[#358054]">Sobre o</span>
                            <span class="text-[#a0c520]">Projeto</span>
                        </h1>
                    </a>
                </div>

                <div class="flex gap-4">
                    @if (auth('admin')->check())
                        <button @click="toggleEditMode()" class="action-button font-semibold transition"
                            :class="editing ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700'">
                            <span x-text="editing ? 'SAIR DO MODO EDIÇÃO ❌' : 'EDITAR ESTA PÁGINA ✏️'"></span>
                        </button>
                        
                        <a href="{{ route('admin.dashboard') }}" class="action-button bg-green-600 text-white hover:bg-green-700 font-semibold">
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

        <div x-show="editing" class="floating-editor-bar">
            <button @click="saveContent()" class="action-button bg-blue-600 text-white">💾 Salvar Tudo</button>
            <button @click="toggleEditMode(true)" class="action-button bg-gray-400 text-white">❌ Cancelar</button>
            
            <div class="border-l h-6"></div>
            
            <button class="insert-button" @click="openImageSelector()">🖼️ Inserir Imagem</button>
            <button class="insert-button" @click="addBlock('text')">➕ Texto</button>
            <button class="insert-button" @click="openVideoSelectionModal()">▶️ Vídeo</button>
        </div>


        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-1">
            <div class="bg-white rounded-lg shadow-lg content-box" :class="{'edit-mode': editing}">
                
                <h2 class="text-3xl font-bold text-gray-900 mb-6" 
                    :contenteditable="editing" 
                    x-ref="pageTitle"
                    @blur="editing && (editingTitle = $event.target.innerText)">
                    {{ $pageContent->title }}
                </h2>
                
                <div class="prose max-w-none">
                    
                    <template x-for="(block, index) in contentBlocks" :key="block.id">
                        <div class="block-wrapper" :class="{'relative': editing}">
                            
                            <div x-show="editing" class="block-controls">
                                <button @click.prevent="moveBlock(index, 'up')">🔼</button>
                                <button @click.prevent="moveBlock(index, 'down')">🔽</button>
                                <button @click.prevent="removeBlock(index)">❌</button>
                            </div>

                            <template x-if="block.type === 'text'">
                                <div class="text-editor-field" 
                                    :id="'editor-' + block.id"
                                    :contenteditable="editing"
                                    x-html="block.data.html"
                                    @blur="updateBlockData(index, 'html', $event.target.innerHTML)">
                                </div>
                            </template>
                            
                            <template x-if="block.type === 'image'">
                                <div class="flex gap-4 items-start">
                                    <img :src="'/storage/' + block.data.url" 
                                         @click="editing && openImageSelectorForEdit(index)"
                                         class="w-1/3 h-40 object-cover rounded-lg cursor-pointer" alt="Imagem">
                                    
                                    <div :contenteditable="editing" @blur="updateBlockData(index, 'caption', $event.target.innerHTML)" x-html="block.data.caption"></div>
                                </div>
                            </template>

                            <template x-if="block.type === 'youtube'">
                                <div class="flex gap-4 items-start">
                                    <iframe x-show="block.data.subType === 'yt'" :src="'https://www.youtube.com/embed/' + block.data.url" frameborder="0" allowfullscreen class="w-1/3 aspect-video"></iframe>
                                    <video x-show="block.data.subType === 'local'" :src="'/storage/' + block.data.url" controls class="w-1/3 h-40 object-cover rounded-lg"></video>

                                    <div :contenteditable="editing" @blur="updateBlockData(index, 'title', $event.target.innerHTML)" x-html="block.data.title"></div>
                                </div>
                            </template>
                            
                            <div x-show="editing" class="mt-4 flex justify-end gap-2">
                                <button @click.prevent="addBlock('text', index + 1)" class="px-2 py-1 text-sm bg-gray-200 rounded hover:bg-gray-300">➕ Texto</button>
                                <button @click.prevent="openImageSelector(index + 1)" class="px-2 py-1 text-sm bg-gray-200 rounded hover:bg-gray-300">🖼️ Imagem</button>
                                <button @click.prevent="openVideoSelectionModal(index + 1)" class="px-2 py-1 text-sm bg-gray-200 rounded hover:bg-gray-300">▶️ Vídeo</button>
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
                <h3 class="text-xl font-bold mb-4">Inserir Vídeo</h3>

                <div class="space-y-4">
                    <button @click="selectYoutubeLink()" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        🔗 Inserir Link do YouTube
                    </button>

                    <div class="flex items-center justify-center">
                        <span class="text-gray-500">OU</span>
                    </div>

                    <button @click="selectLocalVideo()" class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        📂 Enviar Arquivo de Vídeo
                    </button>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="showVideoModal = false" class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const isInitialEditing = @json($isEditing);

        // --- FUNÇÕES TINYMCE (Adaptadas para inicialização dinâmica por ID) ---
        function initializeTinyMCE(selector) {
            // Remove instâncias antigas antes de inicializar novas
            tinymce.remove(); 
            
            // Inicializa apenas nos elementos que são contenteditable e NÃO estão dentro de um bloco de código (exclui o titulo)
            document.querySelectorAll(selector).forEach(el => {
                if (!el.id) {
                    // 1. Garante que cada editor in-place tenha um ID único
                    el.id = 'editor-' + Date.now() + Math.random().toString(36).substring(2, 9);
                }

                tinymce.init({
                    selector: '#' + el.id,
                    plugins: 'autolink lists link image media table codesample',
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image media',
                    menubar: false,
                    height: 200,
                    inline: true, // Modo de edição in-place
                    setup: function (editor) {
                        editor.on('blur', function () {
                            editor.save();
                            // 2. Garante que o Alpine capture o conteúdo atualizado no DOM
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
                    return Math.max(...ids, 0) + 1;
                },

                init() {
                    // Inicialização inicial se o modo de edição estiver ativo
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
                        // Ativa TinyMCE nos blocos existentes
                        this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    } else {
                        // Desativa TinyMCE
                        destroyTinyMCE();
                    }
                },
                
                // --- INSERÇÃO DE MÍDIA E TEXTO ---

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

                // --- HANDLERS DE UPLOAD (Simulação) ---

                handleImageFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    const fakePath = `simulacao/temp-img-${Date.now()}.jpg`; 

                    if (this.currentBlockIndexForUpload < this.contentBlocks.length && this.currentBlockIndexForUpload >= 0) {
                        // EDITANDO BLOCO EXISTENTE (Se o target for um índice válido)
                        // Se o bloco alvo for um TEXTO, criamos um bloco de IMAGEM no lugar
                        if (this.contentBlocks[this.currentBlockIndexForUpload].type === 'text') {
                             this.addBlock('image', this.currentBlockIndexForUpload, fakePath);
                        } else {
                             this.contentBlocks[this.currentBlockIndexForUpload].data.url = fakePath;
                             this.contentBlocks[this.currentBlockIndexForUpload].data.caption = 'Nova imagem carregada';
                        }
                    } else {
                        // INSERINDO NOVO BLOCO (targetIndex = this.contentBlocks.length)
                        this.addBlock('image', this.currentBlockIndexForUpload, fakePath);
                    }
                    
                    event.target.value = '';
                    this.currentBlockIndexForUpload = null;
                },

                handleVideoFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const fakePath = `simulacao/temp-video-${Date.now()}.mp4`; 
                    
                    // Sempre insere um novo bloco de vídeo local
                    this.addBlock('youtube', this.currentBlockIndexForUpload, fakePath, 'local');
                    
                    event.target.value = '';
                },
                
                // --- FUNÇÕES DE GESTÃO DE BLOCOS (Mover/Adicionar/Remover) ---
                
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
                    
                    // Re-inicializa TinyMCE para o novo elemento funcionar imediatamente
                    this.$nextTick(() => {
                        initializeTinyMCE('.block-wrapper [contenteditable=true]');
                    });
                },

                removeBlock(index) {
                    if (confirm('Tem certeza que deseja remover este bloco?')) {
                        this.contentBlocks.splice(index, 1);
                        // Destroi e recria os editores para remover a instância antiga
                        this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    }
                },

                moveBlock(index, direction) {
                    // (Lógica de mover mantida)
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
                    }
                },

                // Atualiza o dado de um bloco após o @blur (quando sai do foco)
                updateBlockData(index, key, value) {
                    // (Lógica de atualização mantida)
                    if (key === 'html') {
                        this.contentBlocks[index].data.html = value;
                    } else {
                        this.contentBlocks[index].data[key] = value;
                    }
                },

                // Lógica de Salvar via AJAX
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
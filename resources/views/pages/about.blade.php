<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/about.css') <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* CSS do Editor (Mantido) */
        .content-box { min-height: 400px; }
        .floating-editor-bar {
            position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 100;
            background: #e9ecef; border: 1px solid #c9cdd0; border-radius: 8px; padding: 5px 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 10px;
        }
        .edit-mode .block-wrapper {
            position: relative; padding: 10px; margin-bottom: 15px; border: 1px dashed #00b050;
            background-color: #f9fff5; border-radius: 8px;
        }
        .block-controls {
            position: absolute; top: -10px; right: -10px; background: #00b050; color: white;
            padding: 5px; border-radius: 4px; z-index: 10; cursor: pointer; display: flex; align-items: center;
        }
        .edit-mode [contenteditable="true"]:focus { outline: 2px solid #358054; background-color: #ffffff; color: #000; }
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 200;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-content { background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 450px; }
        
        /* Ajuste para garantir que o header fique branco e fixo igual ao mapa */
        .site-header { background-color: white; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); position: sticky; top: 0; z-index: 50; }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-800" x-data="cmsEditor()">

    <input type="file" id="fileInput" class="hidden" accept="image/*" @change="handleImageFileUpload($event)">
    <input type="file" id="videoFileInput" class="hidden" accept="video/mp4,video/webm" @change="handleVideoFileUpload($event)">

    {{-- ========================================================= --}}
    {{-- HEADER INTELIGENTE (CÓPIA DO MAPA) --}}
    {{-- ========================================================= --}}
    <header class="site-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-4 flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-4">
                    <img src="{{ asset('images/Brasao_Verde.png') }} " alt="Logo Brasão de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <h1 class="text-3xl sm:text-4xl font-bold">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]"> Paracambi</span>
                    </h1>
                </a>
            </div>

            {{-- MENU SUPERIOR --}}
            <div class="flex items-center gap-3 sm:gap-4 relative" x-data="{ open: false }">

                {{-- ADMIN LOGADO --}}
                @if (auth('admin')->check())
                    
                    <button @click="toggleEditMode()" class="btn hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded font-semibold transition-colors"
                            :class="editing ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'">
                        <span x-show="!editing">Editar Página</span>
                        <span x-show="editing">Sair da Edição</span>
                    </button>

                    <a href="{{ route('admin.dashboard') }}"
                        class="btn bg-green-600 hover:bg-green-700 hidden sm:block text-white px-4 py-2 rounded">
                        Painel Admin
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                    </form>

                {{-- USUÁRIO LOGADO --}}
                @elseif(auth()->check())
                    <div class="relative group flex items-center">
                        <a href="{{ route('dashboard') }}"
                            class="btn bg-green-600 hover:bg-green-700 hidden sm:block px-6 py-3 text-lg rounded-lg text-white">
                            Menu
                        </a>

                        <div class="absolute bottom-[-55px] left-1/2 transform -translate-x-1/2
                            bg-gradient-to-r from-[#358054] to-[#a0c520] text-white text-xs font-semibold
                            py-1.5 px-3 rounded-lg shadow-xl opacity-0 group-hover:opacity-100
                            pointer-events-none transition-all duration-200 whitespace-nowrap">
                                Acesse seu painel e opções da conta
                                <span class="absolute top-[-6px] left-1/2 transform -translate-x-1/2 w-0 h-0
                                border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent
                                border-b-[6px] border-b-[#358054]"></span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                    </form>

                {{-- VISITANTE (NÃO LOGADO) --}}
                @else
                    <a href="{{ route('login') }}" class="btn bg-green-600 hover:bg-green-700 hidden sm:block text-white px-4 py-2 rounded">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn bg-gray-600 hover:bg-gray-700 hidden sm:block text-white px-4 py-2 rounded">
                        Cadastrar
                    </a>

                    {{-- MENU HAMBÚRGUER (MOBILE) --}}
                    <div class="relative inline-block">
                        <button id="guestMenuBtn"
                            class="ml-3 btn bg-[#358054] text-white hover:bg-[#2d6e4b] rounded-lg flex items-center gap-2 transition-all duration-200 px-3 py-2">
                            Menu
                            <svg id="iconMenu" class="w-6 h-6 transition-all duration-200" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h16" />
                                <path d="M4 12h16" />
                                <path d="M4 18h16" />
                            </svg>
                        </button>

                        <div id="guestMenu" class="hidden absolute right-0 mt-2 w-56 bg-[#e8ffe6] rounded-xl shadow-lg z-50 overflow-hidden border border-green-100">
                            <a href="{{ route('contact') }}"
                            class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">
                                Fazer Solicitação
                            </a>
                            <a href="{{ route('contact.myrequests') }}"
                            class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">
                                Minhas Solicitações
                            </a>
                            <a href="{{ route('about') }}"
                            class="block px-4 py-3 font-semibold !text-gray-800 hover:!text-green-700 hover:bg-[#d9f5d6] transition-colors">
                                Sobre o Site
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </header>

    <div x-show="editing" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed top-32 left-1/2 transform -translate-x-1/2 z-50 flex items-center gap-3 px-5 py-3 bg-white/95 backdrop-blur-md shadow-2xl rounded-full border border-gray-200">
        
        <div class="flex items-center gap-2 pr-4 border-r border-gray-300">
            <button @click="saveContent()" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white font-bold rounded-full shadow hover:bg-green-700 hover:-translate-y-0.5 transition-all">
                Salvar
            </button>
            <button @click="toggleEditMode(true)" class="flex items-center gap-1 px-3 py-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors">
                Cancelar
            </button>
        </div>
        
        <div class="flex items-center gap-3 pl-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Add:</span>
            <button @click="addBlock('text')" class="p-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 border border-gray-200" title="Texto">📝</button>
            <button @click="openImageSelector()" class="p-2 bg-purple-50 text-purple-700 rounded-full hover:bg-purple-100 border border-purple-200" title="Imagem">📷</button>
            <button @click="openVideoSelectionModal()" class="p-2 bg-red-50 text-red-700 rounded-full hover:bg-red-100 border border-red-200" title="Vídeo">🎬</button>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-1">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden" :class="{'ring-4 ring-green-100 ring-offset-2': editing}">
            
            <div class="bg-gradient-to-r from-[#358054] to-[#4caf50] p-8 sm:p-12 text-center text-white relative">
                <h1 class="text-4xl sm:text-5xl font-extrabold mb-4 tracking-tight text-white outline-none"
                    :contenteditable="editing" 
                    x-ref="pageTitle"
                    @blur="editing && (editingTitle = $event.target.innerText)"
                    style="min-height: 1.5em;">
                    {{ $pageContent->title }}
                </h1>
                <p class="text-green-100 text-lg max-w-2xl mx-auto">
                    Conheça a iniciativa que está transformando a gestão ambiental da nossa cidade.
                </p>
                <div x-show="editing" class="absolute top-2 right-2 bg-white/20 backdrop-blur px-2 py-1 rounded text-xs text-white">
                    Modo Edição
                </div>
            </div>

            <div class="p-8 sm:p-12 space-y-8 content-box">
                <div class="prose prose-lg max-w-none text-gray-600">
                    <template x-for="(block, index) in contentBlocks" :key="block.id">
                        <div class="block-wrapper transition-all duration-300" :class="{'p-4': editing}">
                            
                            <div x-show="editing" class="block-controls">
                                <button @click.prevent="moveBlock(index, 'up')" title="Subir">▲</button>
                                <button @click.prevent="moveBlock(index, 'down')" title="Descer">▼</button>
                                <button @click.prevent="removeBlock(index)" title="Excluir" class="text-red-200 hover:text-white">✕</button>
                            </div>

                            <template x-if="block.type === 'text'">
                                <div class="text-editor-field min-h-[40px]" 
                                     :id="'editor-' + block.id" :contenteditable="editing" x-html="block.data.html"
                                     @blur="updateBlockData(index, 'html', $event.target.innerHTML)"></div>
                            </template>
                            
                            <template x-if="block.type === 'image'">
                                <div class="flex flex-col gap-2 my-6">
                                    <div class="relative group">
                                        <img :src="'/storage/' + block.data.url" 
                                             @click="editing && openImageSelectorForEdit(index)"
                                             class="w-full h-auto max-h-[500px] object-cover rounded-xl shadow-md" 
                                             :class="{'cursor-pointer hover:opacity-90': editing}">
                                    </div>
                                    <div class="text-sm text-gray-500 italic text-center" :contenteditable="editing" @blur="updateBlockData(index, 'caption', $event.target.innerHTML)" x-html="block.data.caption"></div>
                                </div>
                            </template>

                            <template x-if="block.type === 'youtube'">
                                <div class="flex flex-col gap-2 my-6">
                                    <div class="w-full aspect-video bg-black rounded-xl shadow-lg overflow-hidden relative">
                                        <iframe x-show="block.data.subType === 'yt'" :src="'https://www.youtube.com/embed/' + block.data.url" frameborder="0" allowfullscreen class="w-full h-full"></iframe>
                                        <video x-show="block.data.subType === 'local'" :src="'/storage/' + block.data.url" controls class="w-full h-full object-cover"></video>
                                    </div>
                                    <div class="text-lg font-semibold text-center" :contenteditable="editing" @blur="updateBlockData(index, 'title', $event.target.innerHTML)" x-html="block.data.title"></div>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <div x-show="contentBlocks.length === 0 && !editing" class="text-center py-10 text-gray-400">
                        <p>Nenhum conteúdo adicionado ainda.</p>
                    </div>
                </div>

                <hr class="border-gray-100 my-8">

                <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                     <p class="text-green-800 font-semibold text-center">
                        Para mais informações ou para reportar problemas, entre em contato conosco através da <a href="{{ route('contact') }}" class="underline hover:text-[#38c224]">página de solicitações</a>.
                    </p>
                </div>
            </div>
            
            <div class="bg-gray-50 p-6 text-center border-t border-gray-100">
                <p class="text-gray-500 text-sm">
                    © {{ date('Y') }} Árvores de Paracambi. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </main>

    <div x-show="showVideoModal" class="modal-overlay" @click.self="showVideoModal = false" style="display: none;">
        <div class="modal-content">
            <h3 class="text-xl font-bold mb-6">Adicionar Vídeo</h3>
            <div class="grid grid-cols-2 gap-4">
                <button @click="selectYoutubeLink()" class="p-6 bg-white border rounded-xl hover:bg-red-50">YouTube</button>
                <button @click="selectLocalVideo()" class="p-6 bg-white border rounded-xl hover:bg-blue-50">Arquivo Local</button>
            </div>
            <div class="mt-6 flex justify-end">
                <button @click="showVideoModal = false" class="py-2 px-4 text-gray-500">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        const isInitialEditing = @json($isEditing ?? false);

        function initializeTinyMCE(selector) {
            if (typeof tinymce === 'undefined') return;
            tinymce.remove(); 
            document.querySelectorAll(selector).forEach(el => {
                if (!el.id) el.id = 'editor-' + Date.now() + Math.random().toString(36).substring(2, 9);
                tinymce.init({
                    selector: '#' + el.id,
                    plugins: 'autolink lists link image media table codesample',
                    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist',
                    menubar: false,
                    inline: true, 
                    setup: function (editor) {
                        editor.on('blur', function () { editor.save(); el.dispatchEvent(new Event('blur', { bubbles: true })); });
                    }
                });
            });
        }
        
        function destroyTinyMCE() { if (typeof tinymce !== 'undefined') tinymce.remove(); }

        document.addEventListener('alpine:init', () => {
            Alpine.data('cmsEditor', () => ({
                editing: isInitialEditing,
                contentBlocks: @json($pageContent->content_blocks ?? []),
                editingTitle: @json($pageContent->title ?? 'Sobre'),
                showVideoModal: false, 
                currentBlockIndexForUpload: null, 

                nextId() {
                    const ids = this.contentBlocks.map(b => b.id);
                    return ids.length === 0 ? 1 : Math.max(...ids) + 1;
                },
                init() { if (this.editing) this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]')); },
                toggleEditMode(cancel = false) {
                    if (cancel) return window.location.reload();
                    this.editing = !this.editing;
                    if (this.editing) this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    else destroyTinyMCE();
                },
                openImageSelector(t=this.contentBlocks.length) { this.currentBlockIndexForUpload = t; document.getElementById('fileInput').click(); },
                openImageSelectorForEdit(i) { this.currentBlockIndexForUpload = i; document.getElementById('fileInput').click(); },
                openVideoSelectionModal(t=this.contentBlocks.length) { this.currentBlockIndexForUpload = t; this.showVideoModal = true; },
                
                selectYoutubeLink() {
                    this.showVideoModal = false;
                    const videoId = prompt("ID do YouTube:");
                    if (videoId) this.addBlock('youtube', this.currentBlockIndexForUpload, videoId, 'yt');
                },
                selectLocalVideo() { this.showVideoModal = false; document.getElementById('videoFileInput').click(); },
                
                handleImageFileUpload(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    const fakePath = `simulacao/temp-img-${Date.now()}.jpg`; 
                    if (this.currentBlockIndexForUpload < this.contentBlocks.length && this.currentBlockIndexForUpload >= 0 && this.contentBlocks[this.currentBlockIndexForUpload].type !== 'text') {
                        this.contentBlocks[this.currentBlockIndexForUpload].data.url = fakePath;
                    } else {
                        this.addBlock('image', this.currentBlockIndexForUpload, fakePath);
                    }
                    e.target.value = ''; this.currentBlockIndexForUpload = null;
                },
                handleVideoFileUpload(e) {
                    const file = e.target.files[0]; if (!file) return;
                    const fakePath = `simulacao/temp-video-${Date.now()}.mp4`; 
                    this.addBlock('youtube', this.currentBlockIndexForUpload, fakePath, 'local'); e.target.value = '';
                },
                addBlock(type, idx, val=null, sub=null) {
                    let newBlock = { id: this.nextId(), type: type, data: {} };
                    if (type === 'text') newBlock.data.html = '<p>Texto...</p>';
                    else if (type === 'image') { newBlock.data.url = val||''; newBlock.data.caption = 'Legenda'; }
                    else if (type === 'youtube') { newBlock.data.url = val; newBlock.data.title = 'Título'; newBlock.data.subType = sub||'yt'; }
                    this.contentBlocks.splice(idx, 0, newBlock);
                    this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                },
                removeBlock(i) { if(confirm('Remover?')) { this.contentBlocks.splice(i, 1); } },
                moveBlock(i, dir) {
                    const newI = dir==='up'?i-1:i+1;
                    if(newI>=0 && newI<this.contentBlocks.length) {
                        const b = this.contentBlocks.splice(i,1)[0]; this.contentBlocks.splice(newI,0,b);
                        this.$nextTick(() => initializeTinyMCE('.block-wrapper [contenteditable=true]'));
                    }
                },
                updateBlockData(i, k, v) { if(k==='html') this.contentBlocks[i].data.html=v; else this.contentBlocks[i].data[k]=v; },
                async saveContent() {
                    if (typeof tinymce !== 'undefined') tinymce.triggerSave();
                    try {
                        const res = await fetch("{{ route('admin.about.update') }}", {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ title: this.$refs.pageTitle.innerText, content_blocks_json: JSON.stringify(this.contentBlocks), _token: '{{ csrf_token() }}' })
                        });
                        const data = await res.json();
                        if (data.success) { alert(data.message); this.editing = false; } else { alert("Erro ao salvar"); }
                    } catch (e) { alert('Erro de conexão'); }
                }
            }));
        });
    </script>

    <script>
        (function() {
            const btn = document.getElementById('guestMenuBtn');
            const menu = document.getElementById('guestMenu');
            const icon = document.getElementById('iconMenu');
            let aberto = false;

            if (!btn || !menu) return;

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
                aberto = !aberto;

                if (aberto) {
                    icon.innerHTML = `<path d="M6 6l12 12" /><path d="M6 18L18 6" />`;
                } else {
                    icon.innerHTML = `<path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />`;
                }
            });

            window.addEventListener('click', () => {
                if (!menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    icon.innerHTML = `<path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" />`;
                    aberto = false;
                }
            });
        })();
    </script>
</body>
</html>
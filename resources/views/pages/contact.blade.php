@extends('layouts.dashboard')

@section('content')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fazer Solicitação - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/contact.css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    {{-- Ícones e Alpine --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
        /* CSS mantido */
        #preview-area { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 12px; }
        .preview-item { position: relative; width: 110px; height: 110px; }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ccc; cursor: pointer; }
        .remove-btn { position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; background: #ff4d4d; border-radius: 50%; color: white; font-size: 14px; text-align: center; line-height: 22px; cursor: pointer; border: 2px solid white; }
    </style>
</head>

<body class="font-sans antialiased">
    
    {{-- CONTEXTO ALPINE --}}
    <div class="min-h-screen" x-data="fileUploader()">

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 md:py-12">
            
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                    <p class="font-bold">Sucesso!</p>
                    <p>{{ session('success') }}</p>
                    <a href="{{ route('contact.myrequests') }}"
                       class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                        Ver Minhas Solicitações
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                {{-- COLUNA DA ESQUERDA: INFOS --}}
                <div class="bg-white rounded-lg shadow-lg overflow-hidden lg:mr-8">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">Informações de Contato</h2>
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Endereço</h3>
                                <p class="text-gray-600">
                                    Prefeitura Municipal de Paracambi<br>
                                    Centro, Paracambi - RJ<br>
                                    CEP: 26600-000
                                </p>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Telefone</h3>
                                <p class="text-gray-600">(21) 2683-1897</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">E-mail</h3>
                                <p class="text-gray-600">meioambiente@paracambi.rj.gov.br</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Horário de Atendimento</h3>
                                <p class="text-gray-600">
                                    Segunda a Sexta: 8h às 17h<br>
                                    Sábados, Domingos e Feriados: Fechado
                                </p>
                            </div>

                            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                                <h3 class="text-lg font-semibold text-red-700 mb-2 flex items-center gap-2">
                                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                                    Emergência / Urgência
                                </h3>
                                <p class="text-gray-700">
                                    <strong>Light:</strong> 0800 021 0196<br>
                                    <strong>Bombeiros:</strong> 193
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <img src="{{ asset('images/secretaria.jpg') }}" class="w-full max-h-[360px] object-cover rounded-xl border border-gray-300 shadow-sm">
                        <p class="text-center text-sm text-gray-600 mt-2 italic">Secretaria Municipal de Meio Ambiente e Clima</p>
                    </div>
                </div>

                {{-- COLUNA DA DIREITA: FORMULÁRIO --}}
                <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">Formulário de Contato</h2>

                    {{-- FORMULÁRIO --}}
                    <form id="contactForm" action="{{ route('contact.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm" class="space-y-6">
                        @csrf

                        {{-- TÓPICO (Dropdown Customizado) --}}
                        <div x-data="{ open: false, selected: '{{ old('topico') ?? '' }}' }" class="relative w-full">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Solicitações Frequentes *</label>
                            <button @click="open = !open" type="button" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-left flex justify-between items-center focus:ring-2 focus:ring-[#358054] focus:border-transparent">
                                <span x-text="selected || 'Escolha um tópico...'" :class="selected ? 'text-gray-900' : 'text-gray-500'"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-500"></i>
                            </button>
                            
                            <ul x-show="open" @click.outside="open=false" x-transition class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-20">
                                @foreach ($topicos as $topico)
                                    <li @click="selected='{{ $topico->nome }}'; open=false" 
                                        class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white transition-colors" 
                                        :class="selected === '{{ $topico->nome }}' ? 'bg-green-50 text-[#358054] font-medium' : ''">
                                        {{ $topico->nome }}
                                    </li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="topico" :value="selected">
                        </div>

                        {{-- TELEFONE --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone de Contato *</label>
                            <input type="text" 
                                   name="telefone" 
                                   id="telefoneInput" 
                                   required 
                                   maxlength="15"
                                   value="{{ old('telefone') }}"
                                   placeholder="(21) 99999-9999"
                                   oninput="mascaraTelefone(this)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#358054] focus:border-[#358054]">
                            <small class="text-gray-500">Informe um número para contato caso a equipe precise.</small>
                        </div>

                        {{-- BAIRRO (Dropdown Customizado) --}}
                        <div x-data="{ open: false, selected: '{{ old('bairro') ?? '' }}' }" class="relative w-full">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Bairro *</label>
                            <button @click="open = !open" type="button" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-left flex justify-between items-center focus:ring-2 focus:ring-[#358054] focus:border-transparent">
                                <span x-text="selected || 'Escolha um bairro...'" :class="selected ? 'text-gray-900' : 'text-gray-500'"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-500"></i>
                            </button>
                            <ul x-show="open" @click.outside="open=false" x-transition class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-20">
                                @foreach ($bairros as $bairro)
                                    <li @click="selected='{{ $bairro->nome }}'; open=false" 
                                        class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white transition-colors" 
                                        :class="selected === '{{ $bairro->nome }}' ? 'bg-green-50 text-[#358054] font-medium' : ''">
                                        {{ $bairro->nome }}
                                    </li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="bairro" :value="selected">
                        </div>

                        {{-- RUA --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rua *</label>
                            <input type="text" name="rua" required maxlength="255" value="{{ old('rua') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#358054] focus:border-[#358054]">
                        </div>

                        {{-- NÚMERO --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                            <input type="text" name="numero" maxlength="10" value="{{ old('numero') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#358054] focus:border-[#358054]">
                        </div>

                        {{-- DESCRIÇÃO --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Descrição *</label>
                            <small class="text-gray-500 mt-1 block mb-1">Descreva a situação da árvore e forneça um ponto de referência.</small>
                            <textarea name="descricao" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#358054] focus:border-[#358054]">{{ old('descricao') }}</textarea>
                        </div>

                        {{-- FOTOS --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Anexar Fotos (Máx. 3)</label>
                            
                            <input type="file" class="hidden" id="inputFotos" name="fotos[]" accept="image/*" multiple @change="addFiles">
                            
                            <div class="flex flex-col gap-3">
                                <button type="button" onclick="document.getElementById('inputFotos').click()" class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg shadow-sm hover:bg-gray-200 transition flex items-center justify-center gap-2">
                                    <i data-lucide="camera" class="w-5 h-5"></i>
                                    Selecionar Fotos
                                </button>
                                <p class="text-gray-500 text-xs">Formatos: JPG, PNG, JPEG. Tamanho máx: 5MB.</p>
                            </div>

                            <div class="grid grid-cols-3 gap-3 mt-3">
                                <template x-for="(foto, index) in fotos" :key="index">
                                    <div class="relative group aspect-square">
                                        <img :src="foto.url" class="w-full h-full object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90" @click="openImage(foto.url)">
                                        <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition" @click="remove(index)">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                            <p class="text-yellow-800 text-sm"><strong>Atenção:</strong> Todos os campos marcados com * são obrigatórios.</p>
                        </div>

                        <button type="submit" class="w-full bg-[#358054] text-white font-bold text-lg rounded-lg shadow-md hover:bg-[#2d6e4b] transition py-3 flex items-center justify-center gap-2">
                            <span>Enviar Mensagem</span>
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>

                    </form>
                </div>
            </div>
        </main>

        {{-- ======================================================= --}}
        {{-- MODAL DE CONFIRMAÇÃO (RESPONSIVO E COM Z-INDEX ALTO) --}}
        {{-- ======================================================= --}}
        <div x-show="showConfirmModal" 
             class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6" 
             style="display: none;" 
             x-cloak>
            
            {{-- Fundo Preto --}}
            <div class="fixed inset-0 bg-black/90 transition-opacity backdrop-blur-sm" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showConfirmModal = false"></div>
            
            {{-- Card do Modal --}}
            <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all z-10"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full mb-6">
                    <i data-lucide="check-circle" class="w-8 h-8 text-[#358054]"></i>
                </div>
                
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Confirmar Envio?</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Você está prestes a enviar sua solicitação para a Secretaria de Meio Ambiente. Verifique se os dados e as fotos estão corretos antes de confirmar.
                    </p>
                </div>

                <div class="mt-8 flex flex-col gap-3">
                    <button @click="confirmSubmit" 
                            type="button" 
                            id="btnConfirmarEnvio"
                            class="w-full inline-flex justify-center items-center px-6 py-3 text-base font-bold text-white bg-[#358054] rounded-xl shadow-sm hover:bg-[#2d6e4b] focus:outline-none transition-all">
                        Sim, Confirmar
                    </button>
                    <button @click="showConfirmModal = false" 
                            type="button" 
                            class="w-full inline-flex justify-center items-center px-6 py-3 text-base font-bold text-gray-700 bg-gray-100 border border-gray-200 rounded-xl shadow-sm hover:bg-gray-200 focus:outline-none transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- SCRIPTS --}}
    <script>
        lucide.createIcons();

        function mascaraTelefone(input) {
            let v = input.value;
            v = v.replace(/\D/g, ""); 
            v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); 
            v = v.replace(/(\d)(\d{4})$/, "$1-$2"); 
            input.value = v;
        }

        let arquivosReais = [];

        function fileUploader() {
            return {
                fotos: [],
                showConfirmModal: false,

                addFiles(event) {
                    let files = event.target.files;
                    for (let file of files) {
                        if (arquivosReais.length >= 3) { 
                            Swal.fire({
                                icon: 'warning',
                                title: 'Limite atingido',
                                text: 'Máximo de 3 fotos permitido.',
                                confirmButtonColor: '#358054'
                            });
                            break; 
                        }
                        let reader = new FileReader();
                        reader.onload = e => { this.fotos.push({ url: e.target.result }); }
                        reader.readAsDataURL(file);
                        arquivosReais.push(file);
                    }
                    event.target.value = ''; 
                    this.syncFileInput();
                },

                remove(index) {
                    this.fotos.splice(index, 1);
                    arquivosReais.splice(index, 1);
                    this.syncFileInput();
                },

                syncFileInput() {
                    const input = document.getElementById('inputFotos');
                    const dataTransfer = new DataTransfer();
                    arquivosReais.forEach(file => dataTransfer.items.add(file));
                    input.files = dataTransfer.files;
                },

                submitForm() {
                    // Validações
                    const inputTopico = document.querySelector('input[name="topico"]');
                    const inputBairro = document.querySelector('input[name="bairro"]');
                    const inputTelefone = document.getElementById('telefoneInput');
                    const inputRua = document.querySelector('input[name="rua"]');
                    const inputDesc = document.querySelector('textarea[name="descricao"]');

                    let erros = [];

                    if (!inputTopico || !inputTopico.value) erros.push('Tópico');
                    if (!inputTelefone || !inputTelefone.value) erros.push('Telefone');
                    if (!inputBairro || !inputBairro.value) erros.push('Bairro');
                    if (!inputRua || !inputRua.value) erros.push('Rua');
                    if (!inputDesc || !inputDesc.value) erros.push('Descrição');

                    if (erros.length > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Campos obrigatórios',
                            text: 'Por favor, preencha: ' + erros.join(', '),
                            confirmButtonColor: '#358054'
                        });
                        return;
                    }

                    this.showConfirmModal = true;
                },

                confirmSubmit() {
                    // Sincroniza arquivos antes de enviar
                    this.syncFileInput();
                    
                    // Feedback visual seguro
                    const btn = document.getElementById('btnConfirmarEnvio');
                    if(btn) {
                        btn.innerHTML = 'Enviando...';
                        btn.disabled = true;
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                    }
                    
                    // Envio direto via formulário HTML (ignora Alpine prevent)
                    const form = document.getElementById('contactForm');
                    if (form) {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                },

                openImage(url) {
                    let img = document.createElement("img");
                    img.src = url;
                    img.style.maxWidth = "90vw";
                    img.style.maxHeight = "90vh";
                    img.style.borderRadius = "8px";
                    img.style.boxShadow = "0 20px 25px -5px rgb(0 0 0 / 0.1)";
                    
                    let box = document.createElement("div");
                    box.style = "position:fixed; inset:0; background:rgba(0,0,0,0.95); display:flex; align-items:center; justify-content:center; z-index:999999; cursor:zoom-out; backdrop-filter:blur(5px);";
                    
                    box.appendChild(img);
                    box.onclick = () => box.remove();
                    document.body.appendChild(box);
                }
            }
        }
    </script>
</body>
</html>
@endsection
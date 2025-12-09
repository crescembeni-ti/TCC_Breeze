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

    <style>
        #preview-area {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 12px;
        }
        .preview-item {
            position: relative;
            width: 110px;
            height: 110px;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        .remove-btn {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 22px;
            height: 22px;
            background: #ff4d4d;
            border-radius: 50%;
            color: white;
            font-size: 14px;
            text-align: center;
            line-height: 22px;
            cursor: pointer;
            border: 2px solid white;
        }
        /* Lightbox */
        #lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999999;
        }
        #lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 10px;
        }
        #lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 40px;
            color: white;
            cursor: pointer;
        }
    </style>
</head>

<body class="font-sans antialiased">
<div class="min-h-screen">

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

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

            <!-- ------------------------------ -->
            <!-- CARD ESQUERDO -->
            <!-- ------------------------------ -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mr-16">
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Informações de Contato</h2>
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
                    </div>
                </div>

                <div class="px-6 pb-6">
                    <img src="{{ asset('images/secretaria.jpg') }}"
                         class="w-full max-h-[360px] object-cover rounded-xl border border-gray-300 shadow-sm">
                    <p class="text-center text-sm text-gray-600 mt-2 italic">
                        Secretaria Municipal de Meio Ambiente e Clima
                    </p>
                </div>
            </div>

            <!-- ------------------------------ -->
            <!-- FORMULÁRIO -->
            <!-- ------------------------------ -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Formulário de Contato</h2>

                <form action="{{ route('contact.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- ----------- SELECT TÓPICO ----------- -->
                    <div x-data="{ open: false, selected: '{{ old('topico') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Solicitações Frequentes *</label>

                        <button @click="open = !open" type="button"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-left">
                            <span x-text="selected || 'Escolha um tópico...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            @foreach ($topicos as $topico)
                                <li @click="selected='{{ $topico->nome }}'; open=false"
                                    class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white"
                                    :class="selected === '{{ $topico->nome }}' ? 'bg-[#358054] text-white' : ''">
                                    {{ $topico->nome }}
                                </li>
                            @endforeach
                        </ul>

                        <input type="hidden" name="topico" :value="selected" required>
                    </div>

                    <!-- ----------- SELECT BAIRRO ----------- -->
                    <div x-data="{ open: false, selected: '{{ old('bairro') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Bairro *</label>

                        <button @click="open = !open" type="button"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-left">
                            <span x-text="selected || 'Escolha um bairro...'"></span>

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <ul x-show="open" @click.outside="open=false"
                            class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            @foreach ($bairros as $bairro)
                                <li @click="selected='{{ $bairro->nome }}'; open=false"
                                    class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white"
                                    :class="selected === '{{ $bairro->nome }}' ? 'bg-[#358054] text-white' : ''">
                                    {{ $bairro->nome }}
                                </li>
                            @endforeach
                        </ul>

                        <input type="hidden" name="bairro" :value="selected" required>
                    </div>

                    <!-- RUA -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rua *</label>
                        <input type="text" name="rua" required maxlength="255"
                               value="{{ old('rua') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <!-- NUMERO -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                        <input type="text" name="numero" maxlength="10"
                               value="{{ old('numero') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <!-- DESCRIÇÃO -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Descrição *</label>
                        <textarea name="descricao" rows="6" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('descricao') }}</textarea>
                    </div>

                    <!-- FOTOS -->
                    <div x-data="fileUploader()" class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Anexar Fotos (Máx. 3)
                        </label>

                        <input 
                            type="file" 
                            class="hidden" 
                            id="inputFotos"
                            accept="image/*"
                            multiple
                            @change="addFiles"
                        >

                        <button type="button"
                            onclick="document.getElementById('inputFotos').click()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">
                            Selecionar Fotos
                        </button>

                        <!-- preview -->
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <template x-for="(foto, index) in fotos" :key="index">
                                <div class="relative">
                                    <img :src="foto.url" 
                                        class="w-full h-24 object-cover rounded-lg border shadow cursor-pointer"
                                        @click="openImage(foto.url)">
                                    
                                    <button type="button"
                                        class="absolute top-1 right-1 bg-red-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center"
                                        @click="remove(index)">
                                        ×
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- input real usado pelo Laravel -->
                        <template x-for="(foto, index) in fotos">
                            <input type="hidden" name="fotos[]" :value="foto.file">
                        </template>

                        <p class="text-gray-500 text-sm">Máximo de 3 fotos (JPG, PNG, JPEG)</p>
                    </div>

                    <script>
                    function fileUploader() {
                        return {
                            fotos: [],

                            addFiles(event) {
                                let files = event.target.files;

                                for (let file of files) {
                                    if (this.fotos.length >= 3) break;

                                    let reader = new FileReader();
                                    reader.onload = e => {
                                        this.fotos.push({
                                            file,
                                            url: e.target.result
                                        });
                                    }
                                    reader.readAsDataURL(file);
                                }
                            },

                            remove(index) {
                                this.fotos.splice(index, 1);
                            },

                            openImage(url) {
                                let img = document.createElement("img");
                                img.src = url;
                                img.style.maxWidth = "90vw";
                                img.style.maxHeight = "90vh";

                                let box = document.createElement("div");
                                box.style = "position:fixed; inset:0; background:rgba(0,0,0,.8); display:flex; align-items:center; justify-content:center; z-index:99999;";
                                box.appendChild(img);

                                box.onclick = () => box.remove();
                                document.body.appendChild(box);
                            }
                        }
                    }
</script>


                    <!-- AVISO -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <p class="text-yellow-800 text-sm"><strong>Atenção:</strong> Todos os campos marcados com * são obrigatórios.</p>
                    </div>

                    <!-- BOTÃO -->
                    <button type="submit"
                        class="bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 transition w-full py-2">
                        Enviar Mensagem
                    </button>

                </form>
            </div>

        </div>
    </main>

</div>

<script>

let arquivosSelecionados = [];
const fileInput = document.getElementById("file-input");
const previewArea = document.getElementById("preview-area");
const hiddenField = document.getElementById("fotos-data");

const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const lightboxClose = document.getElementById("lightbox-close");

fileInput.addEventListener("change", function(event) {

    let novos = [...event.target.files];

    if (arquivosSelecionados.length + novos.length > 3) {
        alert("Máximo de 3 fotos.");
        return fileInput.value = "";
    }

    arquivosSelecionados.push(...novos);

    atualizarPreview();
    fileInput.value = "";
});

function atualizarPreview() {
    previewArea.innerHTML = "";

    arquivosSelecionados.forEach((file, index) => {
        let url = URL.createObjectURL(file);

        let item = document.createElement("div");
        item.classList.add("preview-item");

        item.innerHTML = `
            <span class="remove-btn" onclick="removerFoto(${index})">×</span>
            <img src="${url}" onclick="abrirLightbox('${url}')">
        `;

        previewArea.appendChild(item);
    });

    hiddenField.value = arquivosSelecionados.length;
}

function removerFoto(index) {
    arquivosSelecionados.splice(index, 1);
    atualizarPreview();
}

function abrirLightbox(url) {
    lightboxImg.src = url;
    lightbox.style.display = "flex";
}

lightboxClose.onclick = () => lightbox.style.display = "none";
lightbox.onclick = (e) => { if (e.target === lightbox) lightbox.style.display = "none"; }

</script>

</body>
</html>
@endsection

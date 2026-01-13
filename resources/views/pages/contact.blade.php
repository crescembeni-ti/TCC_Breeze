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
        /* CSS mantido */
        #preview-area { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 12px; }
        .preview-item { position: relative; width: 110px; height: 110px; }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ccc; cursor: pointer; }
        .remove-btn { position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; background: #ff4d4d; border-radius: 50%; color: white; font-size: 14px; text-align: center; line-height: 22px; cursor: pointer; border: 2px solid white; }
        #lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.85); display: none; align-items: center; justify-content: center; z-index: 999999; }
        #lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 10px; }
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

            {{-- COLUNA DA ESQUERDA: INFOS --}}
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

                        <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                            <h3 class="text-lg font-semibold text-red-700 mb-2 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
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
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Formulário de Contato</h2>

                <form x-data="fileUploader()" id="contactForm" action="{{ route('contact.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm" class="space-y-6">
                    @csrf

                    {{-- TÓPICO --}}
                    <div x-data="{ open: false, selected: '{{ old('topico') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Solicitações Frequentes *</label>
                        <button @click="open = !open" type="button" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-left flex justify-between items-center">
                            <span x-text="selected || 'Escolha um tópico...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            @foreach ($topicos as $topico)
                                <li @click="selected='{{ $topico->nome }}'; open=false" class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === '{{ $topico->nome }}' ? 'bg-[#358054] text-white' : ''">{{ $topico->nome }}</li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="topico" :value="selected" required>
                    </div>

                    {{-- NOVO CAMPO: TELEFONE COM MÁSCARA --}}
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

                    {{-- BAIRRO --}}
                    <div x-data="{ open: false, selected: '{{ old('bairro') ?? '' }}' }" class="relative w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Bairro *</label>
                        <button @click="open = !open" type="button" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-left flex justify-between items-center">
                            <span x-text="selected || 'Escolha um bairro...'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false" class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-auto z-10">
                            @foreach ($bairros as $bairro)
                                <li @click="selected='{{ $bairro->nome }}'; open=false" class="px-4 py-2 cursor-pointer hover:bg-[#358054] hover:text-white" :class="selected === '{{ $bairro->nome }}' ? 'bg-[#358054] text-white' : ''">{{ $bairro->nome }}</li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="bairro" :value="selected" required>
                    </div>

                    {{-- RUA --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rua *</label>
                        <input type="text" name="rua" required maxlength="255" value="{{ old('rua') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    {{-- NÚMERO --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                        <input type="text" name="numero" maxlength="10" value="{{ old('numero') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    {{-- DESCRIÇÃO --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Descrição *</label>
                        <small class="text-gray-500 mt-1 block">Descreva a situação da árvore e forneça um ponto de referência.</small>
                        <textarea name="descricao" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('descricao') }}</textarea>
                    </div>

                    {{-- FOTOS (ALPINE) --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Anexar Fotos (Máx. 3)</label>
                        <input type="file" class="hidden" id="inputFotos" name="fotos[]" @accept="image/*" multiple @change="addFiles">
                        <button type="button" onclick="document.getElementById('inputFotos').click()" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">Selecionar Fotos</button>
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <template x-for="(foto, index) in fotos" :key="index">
                                <div class="relative">
                                    <img :src="foto.url" class="w-full h-24 object-cover rounded-lg border shadow cursor-pointer" @click="openImage(foto.url)">
                                    <button type="button" class="absolute top-1 right-1 bg-red-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center" @click="remove(index)">×</button>
                                </div>
                            </template>
                        </div>
                        <p class="text-gray-500 text-sm">Máximo de 3 fotos (JPG, PNG, JPEG)</p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <p class="text-yellow-800 text-sm"><strong>Atenção:</strong> Todos os campos marcados com * são obrigatórios.</p>
                    </div>

                    <button type="submit" class="bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 transition w-full py-2">Enviar Mensagem</button>

                </form>
            </div>
        </div>
    </main>
</div>

<script>
    // --- MÁSCARA DE TELEFONE ---
    function mascaraTelefone(input) {
        let v = input.value;
        v = v.replace(/\D/g, ""); // Remove tudo o que não é dígito
        v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); // Coloca parênteses em volta dos dois primeiros dígitos
        v = v.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca hífen entre o quarto e o quinto dígitos
        input.value = v;
    }

    // --- UPLOAD DE ARQUIVOS (ALPINE LÓGICA) ---
    let arquivosReais = [];
    function fileUploader() {
        return {
            fotos: [],
            addFiles(event) {
                let files = event.target.files;
                for (let file of files) {
                    if (arquivosReais.length >= 3) { alert("Máximo de 3 fotos permitido."); break; }
                    let reader = new FileReader();
                    reader.onload = e => { this.fotos.push({ url: e.target.result }); }
                    reader.readAsDataURL(file);
                    arquivosReais.push(file);
                }
                event.target.value = '';
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
                this.syncFileInput();
                document.getElementById('contactForm').submit();
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
</body>
</html>
@endsection
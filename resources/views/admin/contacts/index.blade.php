@extends('layouts.dashboard')

@section('content')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mensagens de Contato - Árvores de Paracambi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    {{-- Leaflet CSS (Mapa) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/dashboard.css')

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>
    {{-- Importante para o x-data funcionar --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #lightbox-admin { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.95); display: none; align-items: center; justify-content: center; z-index: 99999; }
        #lightbox-admin img { max-width: 90vw; max-height: 90vh; border-radius: 8px; object-fit: contain; }
        #lightbox-close-admin { position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; }
        
        /* Estilo do Mapa */
        #map-contacts {
            height: 600px;
            width: 100%;
            border-radius: 0.75rem; 
            margin-top: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">

    <main class="flex-1 p-10">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <div class="flex items-center justify-center mb-6 flex-wrap gap-3 text-center">
                <h2 class="text-3xl font-bold text-[#358054] text-center">Mensagens de Contato</h2>
            </div>

            @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md shadow-sm">
                {{ session('success') }}
            </div>
            @endif

            {{-- CONTROLE DE FILTROS UNIFICADO --}}
            <div class="flex flex-col xl:flex-row items-center justify-between mb-6 gap-6 border-b border-gray-100 pb-4">
                
                {{-- 1. Botões de Abas (Status) - Preservam o período selecionado --}}
                <div class="flex justify-center gap-4 w-full xl:w-auto">
                    <a href="{{ route('admin.contato.index') }}?filtro=todas&period={{ request('period') }}&date_start={{ request('date_start') }}&date_end={{ request('date_end') }}" 
                       class="px-4 py-2 text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'todas' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">
                       Todas
                    </a>
                    
                    <a href="{{ route('admin.contato.index') }}?filtro=pendentes&period={{ request('period') }}&date_start={{ request('date_start') }}&date_end={{ request('date_end') }}" 
                       class="px-4 py-2 text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'pendentes' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">
                       Pendentes
                    </a>
                    
                    <a href="{{ route('admin.contato.index') }}?filtro=resolvidas&period={{ request('period') }}&date_start={{ request('date_start') }}&date_end={{ request('date_end') }}" 
                       class="px-4 py-2 text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'resolvidas' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">
                       Resolvidas
                    </a>
                </div>

                {{-- 2. Filtro de Data (Novo) - Preserva a aba selecionada --}}
                <form method="GET" action="{{ route('admin.contato.index') }}" 
                      x-data="{ period: '{{ request('period') }}' }" 
                      class="flex flex-col md:flex-row items-end gap-3 w-full xl:w-auto">
                    
                    <input type="hidden" name="filtro" value="{{ $filtro }}">
                    
                    <div class="relative w-full md:w-auto">
                        <select name="period" x-model="period" onchange="if(this.value != 'custom') this.form.submit()" 
                                class="appearance-none w-full md:w-48 bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:border-green-500 cursor-pointer shadow-sm">
                            <option value="" {{ request('period') == '' ? 'selected' : '' }}>Todo o Período</option>
                            <option value="7_days">📅 Últimos 7 dias</option>
                            <option value="30_days">📅 Últimos 30 dias</option>
                            <option value="custom">📆 Personalizado...</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                    {{-- Campos de Data (Aparecem só se "Personalizado") --}}
                    <div x-show="period === 'custom'" x-transition class="flex gap-2 w-full md:w-auto" style="display: none;">
                        <input type="date" name="date_start" value="{{ request('date_start') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500 w-full md:w-auto">
                        <span class="self-center text-gray-500">até</span>
                        <input type="date" name="date_end" value="{{ request('date_end') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500 w-full md:w-auto">
                        <button type="submit" class="bg-[#358054] text-white px-3 py-2 rounded-lg hover:bg-green-700 transition">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                <div class="text-sm text-gray-600 font-medium whitespace-nowrap">Total: {{ $messages->count() }}</div>
            </div>

            @php
            $groupsPendentes = ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'];
            $groupsResolvidas = ['Concluído', 'Indeferido', 'Sem Pendências'];
            $statusColors = [
                'Em Análise' => '#9ea3af', 'Deferido' => '#3850d6', 'Indeferido' => '#d2062a',
                'Vistoriado' => '#8c3c14', 'Em Execução' => '#f4ca29',
                'Sem Pendências' => '#ef6d22', 'Concluído' => '#34a54c',
            ];
            @endphp

            {{-- LISTA DE MENSAGENS --}}
            <div id="mensagens-container"></div>

            {{-- ==================== NOVO MAPA ==================== --}}
            <hr class="my-8 border-gray-200">
            
            <div class="mb-4">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">🗺️ Mapa de Solicitações (Por Bairro)</h3>
                <p class="text-gray-600 text-sm">Visualização das solicitações baseada no bairro informado.</p>
            </div>
            
            <div id="map-contacts"></div>
            <p id="map-status" class="text-xs text-center text-gray-500 mt-2">Iniciando mapa...</p>
            {{-- ==================================================== --}}

        </div>
    </main>

    {{-- MODAIS (Mantidos idênticos ao original) --}}
    <div id="modal-view" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 relative">
            <button onclick="closeViewModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"><i data-lucide="x"></i></button>
            <h2 class="text-2xl font-bold text-[#358054] mb-4 text-center">Detalhes</h2>
            <div class="space-y-3">
                <p><strong>Tipo:</strong> <span id="view-topico" class="font-semibold text-[#358054]"></span></p>
                <p><strong>Nome:</strong> <span id="view-nome"></span></p>
                <p><strong>Email:</strong> <span id="view-email"></span></p>
                <p><strong>Endereço:</strong> <span id="view-endereco"></span></p>
                <p id="view-telefone-container" style="display: none;">
                <strong>Telefone:</strong> <span id="view-telefone"></span>
            </p>
                <div class="bg-gray-100 p-3 rounded"><p id="view-descricao"></p></div>
                <button onclick="openFotosModal(currentViewingId)" class="mt-4 w-full bg-[#358054] text-white py-2 rounded-lg">Ver Fotos</button>
            </div>
        </div>
    </div>
    
    <div id="modal-fotos" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-[9999]">
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl p-6 relative">
            <button onclick="closeFotosModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"><i data-lucide="x"></i></button>
            <h2 class="text-2xl font-bold text-[#358054] mb-4 text-center">Fotos</h2>
            <div id="fotos-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[60vh] overflow-auto p-2"></div>
        </div>
    </div>
    <div id="lightbox-admin" onclick="closeLightbox()" style="display: none;">
        <span id="lightbox-close-admin">×</span>
        <img id="lightbox-img-admin" src="">
    </div>

    <div id="modal-status" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative">
            <button onclick="closeStatusModal()" class="absolute top-3 right-3 text-gray-600"><i data-lucide="x"></i></button>
            <h2 class="text-2xl font-bold text-blue-700 mb-4">Atualizar Status</h2>
            <form id="status-form" onsubmit="return submitStatusForm(event)" class="space-y-3">
                @csrf @method('PATCH')
                <label class="font-semibold">Status</label>
                <select name="status_id" id="status-select" class="w-full rounded-md border-gray-300 shadow-sm">
                    @foreach ($allStatuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                <div id="just-box"><label class="font-semibold">Justificativa</label><textarea name="justificativa" id="status-justificativa" class="w-full rounded-md border-gray-300 shadow-sm" rows="3"></textarea></div>
                <button class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salvar</button>
            </form>
        </div>
    </div>

    <div id="modal-forward" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative">
            <button onclick="closeForwardModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"><i data-lucide="x"></i></button>
            <h2 id="forward-title" class="text-2xl font-bold text-orange-700 mb-4">Encaminhar Solicitação</h2>
            <form id="forward-form" onsubmit="return submitForwardForm(event)" class="space-y-3">
                @csrf @method('PATCH')
                <input type="hidden" name="forward_type" id="forward_type">
                <label id="forward-label" class="font-semibold">Selecione:</label>
                <select id="forward-user-select" class="w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Carregando...</option>
                </select>
                <button id="forward-save-btn" class="w-full px-3 py-2 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700">Confirmar Encaminhamento</button>
            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Dados do PHP
        const messages = @json($messages->keyBy('id'));
        const analistas = @json($analistas ?? []);
        const servicos = @json($servicos ?? []);
        const statusColors = @json($statusColors);

        let currentViewingId = null;
        let currentEditingId = null;
        let currentForwardingId = null;

        // --- MANIPULAÇÃO DE STRINGS (AJUDANTES) ---
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        // Função para normalizar strings (remover acentos e minúsculas)
        // Isso ajuda a encontrar "Centro" se estiver escrito "centro" ou "São José" se for "Sao Jose"
        function normalizeString(str) {
            if (!str) return "";
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
        }

        // --- MAPA (LÓGICA NOVA BASEADA NO JSON LOCAL) ---
        async function initContactMap() {
            const map = L.map('map-contacts').setView([-22.6091, -43.7089], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

            const statusText = document.getElementById('map-status');
            statusText.innerText = "Carregando bairros...";

            try {
                // 1. Carrega o arquivo bairros.json
                const response = await fetch('/bairros.json');
                if (!response.ok) throw new Error("Erro ao carregar bairros.json");
                const geoJsonData = await response.json();

                // 2. Cria um dicionário: Nome do Bairro -> Coordenada Central
                const bairrosCenters = {};

                geoJsonData.features.forEach(feature => {
                    const nome = feature.properties.nome;
                    if (nome && feature.geometry && feature.geometry.type === "Polygon") {
                        // Calcula o centroide aproximado do polígono
                        const coords = feature.geometry.coordinates[0];
                        let latSum = 0, lngSum = 0;
                        coords.forEach(pt => {
                            lngSum += pt[0];
                            latSum += pt[1];
                        });
                        const centerLat = latSum / coords.length;
                        const centerLng = lngSum / coords.length;
                        
                        bairrosCenters[normalizeString(nome)] = { lat: centerLat, lng: centerLng };
                    }
                });

                // 3. Adiciona os Polígonos ao mapa (visualização fraca para contexto)
                L.geoJSON(geoJsonData, {
                    style: { color: "#358054", weight: 1, fillOpacity: 0.05 }
                }).addTo(map);

                // 4. Itera sobre as mensagens e coloca os pinos
                const allMsgs = Object.values(messages);
                let pinCount = 0;

                allMsgs.forEach(msg => {
                    // Tenta encontrar o bairro da mensagem no JSON carregado
                    const bairroMsg = normalizeString(msg.bairro);
                    
                    if (bairrosCenters[bairroMsg]) {
                        let center = bairrosCenters[bairroMsg];
                        
                        // --- JITTER (Dispersão) ---
                        // Adiciona um valor aleatório para espalhar os pontos no bairro
                        // Math.random() - 0.5 gera entre -0.5 e 0.5.
                        // Multiplicamos por 0.004 (aprox 400m) para espalhar bem.
                        const lat = center.lat + (Math.random() - 0.5) * 0.004;
                        const lng = center.lng + (Math.random() - 0.5) * 0.004;

                        const statusName = msg.status ? msg.status.name.trim() : 'Desconhecido';
                        const color = statusColors[statusName] || '#333333';

                        const marker = L.circleMarker([lat, lng], {
                            color: '#fff', // Borda branca para destacar
                            weight: 1,
                            fillColor: color,
                            fillOpacity: 0.8,
                            radius: 7
                        }).addTo(map);

                        marker.bindPopup(`
                            <div style="font-size:13px; text-align:center;">
                                <strong style="color:${color}">${statusName}</strong><br>
                                <b>${msg.topico ?? 'Solicitação'}</b><br>
                                <span style="font-size:11px; color:#555;">${msg.bairro}</span><br>
                                ${msg.rua || ''}, ${msg.numero || ''}<br>
                                <a href="#" onclick="openViewModal(${msg.id}); return false;" style="color:#358054; font-weight:bold; display:block; margin-top:4px;">Ver Detalhes</a>
                            </div>
                        `);
                        pinCount++;
                    }
                });

                statusText.innerText = `${pinCount} solicitações plotadas no mapa.`;

            } catch (error) {
                console.error(error);
                statusText.innerText = "Erro ao carregar mapa de bairros.";
            }
        }

        // --- FUNÇÃO RENDER TABLE (Mantida) ---
        function renderTable(itens) {
            if (!itens || itens.length === 0) return `<p class="text-gray-400 mt-2 ml-2">Nenhuma solicitação neste grupo.</p>`;
            let rows = itens.map(m => {
                const created = new Date(m.created_at).toLocaleString('pt-BR');
                const topico = m.topico ?? '';
                const endereco = [m.bairro, m.rua, m.numero].filter(Boolean).join(', ');
                const descricao = m.descricao ?? '';
                const statusName = m.status ? m.status.name.trim() : '';
                let actionButtons = '';

                if (statusName === 'Em Execução' && m.service_order && m.service_order.viewed_at) {
                    const viewedDate = new Date(m.service_order.viewed_at).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
                    const serviceName = (m.service_order.service && m.service_order.service.name) ? m.service_order.service.name : 'Equipe Técnica';
                    actionButtons += `<div class="flex flex-col items-end mr-4 text-xs text-gray-500 border-r pr-3 border-gray-200"><span class="font-bold text-[#358054]">Visto por: ${serviceName}</span><span>${viewedDate}</span></div>`;
                }

                if (statusName === 'Deferido') {
                    actionButtons += `<button onclick="openForwardModal(${m.id}, 'analista')" class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white rounded text-xs font-semibold hover:bg-orange-700 mr-2"><i data-lucide="user-check" class="w-3 h-3 mr-1"></i> Analista</button>`;
                } else if (statusName.toLowerCase().includes('vistoriado')) {
                    actionButtons += `<button onclick="openForwardModal(${m.id}, 'servico')" class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white rounded text-xs font-semibold hover:bg-orange-700 mr-2"><i data-lucide="hammer" class="w-3 h-3 mr-1"></i> Serviço</button>`;
                }

                let btnVer = '';
                const temOS = m.service_order && m.service_order.id;
                const statusQueAbremOS = ['Vistoriado', 'Em Execução', 'Concluído', 'Indeferido', 'Sem Pendências'];
                if (temOS && statusQueAbremOS.some(s => statusName.includes(s))) {
                    btnVer = `<a href="/pbi-admin/os/${m.service_order.id}" class="inline-flex items-center justify-center px-3 py-1.5 bg-[#358054] text-white rounded text-xs font-semibold hover:bg-[#2d6947] mr-2"><i data-lucide="file-text" class="w-3 h-3 mr-1"></i> Ver OS</a>`;
                } else {
                    btnVer = `<button onclick="openViewModal(${m.id})" class="px-3 py-1.5 bg-[#358054] text-white rounded text-xs font-semibold hover:bg-[#2d6947] mr-2">Ver</button>`;
                }
                actionButtons += btnVer;
                actionButtons += `<button onclick="openStatusModal(${m.id})" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold hover:bg-blue-700">Status</button>`;

                return `<tr class="border-t hover:bg-gray-50 transition"><td class="px-6 py-4 align-top text-sm text-gray-500">${escapeHtml(created)}</td><td class="px-6 py-4 align-top"><div class="text-sm font-medium text-gray-900"><span class="font-semibold text-[#358054]">${escapeHtml(topico)}</span> - ${escapeHtml(endereco)}</div><div class="text-xs text-gray-500 mt-1">${escapeHtml(descricao.substring(0,100))}...</div></td><td class="px-6 py-4 align-top text-right text-sm"><div class="flex justify-end gap-2 items-center flex-wrap">${actionButtons}</div></td></tr>`;
            }).join('');
            return `<div class="overflow-x-auto"><table class="min-w-full table-fixed divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 w-1/4 text-left text-xs text-gray-500 uppercase">Data</th><th class="px-6 py-3 w-2/4 text-left text-xs text-gray-500 uppercase">Solicitação</th><th class="px-6 py-3 w-1/4 text-right text-xs text-gray-500 uppercase">Ações</th></tr></thead><tbody class="bg-white">${rows}</tbody></table></div>`;
        }

        // --- OUTRAS FUNÇÕES MODAIS (Mantidas) ---
        function openForwardModal(id, type) {
            currentForwardingId = id;
            const title = document.getElementById('forward-title');
            const label = document.getElementById('forward-label');
            const select = document.getElementById('forward-user-select');
            const inputType = document.getElementById('forward_type');
            
            inputType.value = type;
            select.innerHTML = '<option value="" disabled selected>Selecione...</option>';
            
            let lista = [];
            if (type === 'analista') {
                title.textContent = 'Encaminhar para Analista';
                label.textContent = 'Selecione o Analista:';
                lista = analistas;
            } else {
                title.textContent = 'Encaminhar para Equipe de Serviço';
                label.textContent = 'Selecione a Equipe:';
                lista = servicos;
            }

            if(lista.length === 0) {
                const opt = document.createElement('option');
                opt.text = "Nenhum cadastrado encontrado";
                select.add(opt);
            } else {
                lista.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    select.appendChild(opt);
                });
            }
            document.getElementById('modal-forward').classList.remove('hidden');
            document.getElementById('modal-forward').classList.add('flex');
        }
        function closeForwardModal() {
            document.getElementById('modal-forward').classList.add('hidden');
            document.getElementById('modal-forward').classList.remove('flex');
            currentForwardingId = null;
        }
        async function submitForwardForm(e) {
            e.preventDefault();
            if (!currentForwardingId) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = `/pbi-admin/contacts/${currentForwardingId}/forward`;
            const type = document.getElementById('forward_type').value;
            const selectedId = document.getElementById('forward-user-select').value;
            if(!selectedId) { alert("Selecione um responsável."); return; }
            const fd = new FormData();
            fd.append('_method', 'PATCH');
            if (type === 'analista') { fd.append('analyst_id', selectedId); } else { fd.append('service_id', selectedId); }
            try {
                const req = await fetch(url, { method: "POST", headers: { "X-CSRF-TOKEN": token, 'Accept': 'application/json' }, body: fd });
                const res = await req.json();
                if (req.ok) { alert('Encaminhado com sucesso!'); location.reload(); } else { alert("Erro ao encaminhar: " + (res.message || 'Verifique o console')); }
            } catch (err) { console.error(err); alert('Erro de conexão.'); }
        }
       function openViewModal(id) {
            currentViewingId = id;
            let m = messages[id]; // Pega os dados da mensagem atual

            // Preenche os dados básicos
            document.getElementById('view-topico').textContent = m.topico ?? '';
            document.getElementById('view-nome').textContent = m.user?.name ?? m.nome_solicitante;
            document.getElementById('view-email').textContent = m.user?.email ?? m.email_solicitante;
            document.getElementById('view-endereco').textContent = `${m.bairro}, ${m.rua}, ${m.numero}`;
            document.getElementById('view-descricao').textContent = m.descricao;

            // --- LÓGICA DO TELEFONE CORRIGIDA ---
            const telefoneContainer = document.getElementById('view-telefone-container');
            const telefoneSpan = document.getElementById('view-telefone');

            // Tenta encontrar o telefone em várias colunas possíveis (do contato ou do usuário)
            // A ordem de prioridade é: campo 'telefone' direto > campo 'celular' > campo 'phone' > dados do usuário vinculado
            let telefoneEncontrado = m.telefone ?? 'Não informado';

            telefoneSpan.textContent = telefoneEncontrado;
            
            // Força o container a aparecer sempre
            telefoneContainer.style.display = 'block'; 
            // -------------------------------------

            document.getElementById('modal-view').classList.remove('hidden');
            document.getElementById('modal-view').classList.add('flex');
        }
        function closeViewModal() { document.getElementById('modal-view').classList.add('hidden'); document.getElementById('modal-view').classList.remove('flex'); }
        function openFotosModal(id) {
            let m = messages[id];
            const container = document.getElementById('fotos-container');
            container.innerHTML = '';
            let fotos = m.fotos;
            if (typeof fotos === 'string') { try { fotos = JSON.parse(fotos); } catch(e){ fotos=[]; } }
            if (!Array.isArray(fotos)) fotos = [];
            if (fotos.length > 0) {
                fotos.forEach(path => {
                    let img = document.createElement('img');
                    img.src = `/storage/${path}`;
                    img.className = "w-full h-64 object-cover rounded-lg shadow cursor-pointer hover:opacity-80";
                    img.onclick = () => { document.getElementById('lightbox-img-admin').src = img.src; document.getElementById('lightbox-admin').style.display = 'flex'; };
                    container.appendChild(img);
                });
            } else { container.innerHTML = `<p class="text-gray-500">Sem fotos.</p>`; }
            document.getElementById('modal-fotos').classList.remove('hidden');
            document.getElementById('modal-fotos').classList.add('flex');
        }
        function closeFotosModal() { document.getElementById('modal-fotos').classList.add('hidden'); document.getElementById('modal-fotos').classList.remove('flex'); }
        function closeLightbox() { document.getElementById('lightbox-admin').style.display = 'none'; }
        function openStatusModal(id) {
            currentEditingId = id;
            const m = messages[id];
            document.getElementById('status-select').value = m.status_id ?? '';
            document.getElementById('status-justificativa').value = m.justificativa ?? '';
            toggleJustificativaVisibility();
            document.getElementById('modal-status').classList.remove('hidden');
            document.getElementById('modal-status').classList.add('flex');
        }
        function closeStatusModal() { document.getElementById('modal-status').classList.add('hidden'); document.getElementById('modal-status').classList.remove('flex'); }
        document.getElementById('status-select').addEventListener('change', toggleJustificativaVisibility);
        function toggleJustificativaVisibility() {
            const select = document.getElementById('status-select');
            const justBox = document.getElementById('just-box');
            const chosen = select.options[select.selectedIndex]?.text || '';
            justBox.style.display = (chosen === 'Indeferido' || chosen === 'Sem Pendências') ? 'block' : 'none';
        }
        async function submitStatusForm(e) {
            e.preventDefault();
            const url = `/pbi-admin/contacts/${currentEditingId}`;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const fd = new FormData(document.getElementById('status-form'));
            fd.append('_method', 'PATCH');
            await fetch(url, { method: 'POST', headers: {'X-CSRF-TOKEN': token}, body: fd }).then(()=>{ location.reload() });
        }

        // --- INICIALIZAÇÃO ---
        document.addEventListener('DOMContentLoaded', function () {
            if(window.lucide) lucide.createIcons();
            const container = document.getElementById('mensagens-container');
            
            @if ($filtro === 'pendentes')
                @php
                foreach($groupsPendentes as $group){
                    $ids = $messages->filter(fn($m) => $m->status && $m->status->name === $group)->pluck('id')->values();
                    $jsonIds = $ids->toJson();
                @endphp
                    (function () {
                        const block = document.createElement('div');
                        block.setAttribute('x-data', '{ open: true }');
                        block.innerHTML = `
                    <h3 @click="open = !open" class="mt-6 text-xl font-semibold cursor-pointer" style="color: {{ $statusColors[$group] ?? '#333' }};">
                        {{ $group }}
                        <svg class="w-4 h-4 ml-1 inline-block transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </h3>
                    <div x-show="open" class="mt-4 lista-placeholder" data-ids='{!! $jsonIds !!}'></div>`;
                        container.appendChild(block);
                    })();
                @php } @endphp
            @elseif($filtro === 'resolvidas')
                @php
                foreach($groupsResolvidas as $group){
                    $ids = $messages->filter(fn($m) => $m->status && $m->status->name === $group)->pluck('id')->values();
                    $jsonIds = $ids->toJson();
                @endphp
                    (function () {
                        const block = document.createElement('div');
                        block.setAttribute('x-data', '{ open: true }');
                        block.innerHTML = `
                    <h3 @click="open = !open" class="mt-6 text-xl font-semibold cursor-pointer" style="color: {{ $statusColors[$group] ?? '#333' }};">
                        {{ $group }}
                        <svg class="w-4 h-4 ml-1 inline-block transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </h3>
                    <div x-show="open" class="mt-4 lista-placeholder" data-ids='{!! $jsonIds !!}'></div>`;
                        container.appendChild(block);
                    })();
                @php } @endphp
            @else
                const allDiv = document.createElement('div');
                allDiv.className = 'lista-placeholder';
                allDiv.setAttribute('data-all', 'true');
                container.appendChild(allDiv);
            @endif

            setTimeout(() => {
                document.querySelectorAll('.lista-placeholder').forEach(div => {
                    const ids = JSON.parse(div.getAttribute('data-ids') || '[]');
                    const itens = ids.map(i => messages[i]).filter(Boolean);
                    div.innerHTML = renderTable(itens);
                });
                const allPlaceholder = document.querySelector('[data-all="true"]');
                if (allPlaceholder) allPlaceholder.innerHTML = renderTable(Object.values(messages));
                if(window.lucide) lucide.createIcons();
            }, 200);

            // Inicia o Mapa com a lógica Local (JSON)
            initContactMap();
        });
    </script>

</body>
</html>
@endsection
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

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/dashboard.css')

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #lightbox-admin { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.95); display: none; align-items: center; justify-content: center; z-index: 99999; }
        #lightbox-admin img { max-width: 90vw; max-height: 90vh; border-radius: 8px; object-fit: contain; }
        #lightbox-close-admin { position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; }
        #map-contacts { height: 600px; width: 100%; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 1; }
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

            {{-- FILTROS --}}
            <div class="flex flex-col xl:flex-row items-center justify-between mb-6 gap-6 border-b border-gray-100 pb-4">
                
                {{-- Abas de Status --}}
                <div class="flex justify-center gap-4 w-full xl:w-auto">
                    <a href="{{ route('admin.contato.index') }}?filtro=todas&period={{ request('period') }}&date_start={{ request('date_start') }}&date_end={{ request('date_end') }}" 
                       class="px-4 py-2 text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'todas' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">Todas</a>
                    
                    <a href="{{ route('admin.contato.index') }}?filtro=pendentes&period={{ request('period') }}&date_start={{ request('date_start') }}&date_end={{ request('date_end') }}" 
                       class="px-4 py-2 text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'pendentes' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">Pendentes</a>
                    
                    <a href="{{ route('admin.contato.index') }}?filtro=resolvidas&period={{ request('period') }}&date_start={{ request('date_start') }}&date_end={{ request('date_end') }}" 
                       class="px-4 py-2 text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'resolvidas' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">Resolvidas</a>
                </div>

                {{-- Filtro de Data --}}
                <form method="GET" action="{{ route('admin.contato.index') }}" x-data="{ period: '{{ request('period') }}' }" class="flex flex-col md:flex-row items-end gap-3 w-full xl:w-auto">
                    <input type="hidden" name="filtro" value="{{ $filtro }}">
                    <div class="relative w-full md:w-auto">
                        <select name="period" x-model="period" onchange="if(this.value != 'custom') this.form.submit()" class="appearance-none w-full md:w-48 bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:border-green-500 cursor-pointer shadow-sm">
                            <option value="" {{ request('period') == '' ? 'selected' : '' }}>Todo o Período</option>
                            <option value="7_days">📅 Últimos 7 dias</option>
                            <option value="30_days">📅 Últimos 30 dias</option>
                            <option value="custom">📆 Personalizado...</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg></div>
                    </div>
                    <div x-show="period === 'custom'" x-transition class="flex gap-2 w-full md:w-auto" style="display: none;">
                        <input type="date" name="date_start" value="{{ request('date_start') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 w-full md:w-auto">
                        <span class="self-center text-gray-500">até</span>
                        <input type="date" name="date_end" value="{{ request('date_end') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 w-full md:w-auto">
                        <button type="submit" class="bg-[#358054] text-white px-3 py-2 rounded-lg hover:bg-green-700 transition"><i data-lucide="search" class="w-4 h-4"></i></button>
                    </div>
                </form>

                <div class="text-sm text-gray-600 font-medium whitespace-nowrap">Total: {{ $messages->count() }}</div>
            </div>

            @php
            $statusColors = [
                'Em Análise' => '#9ea3af', 'Deferido' => '#3850d6', 'Indeferido' => '#d2062a',
                'Vistoriado' => '#8c3c14', 'Em Execução' => '#f4ca29',
                'Sem Pendências' => '#ef6d22', 'Concluído' => '#34a54c',
            ];
            @endphp

            {{-- LISTA DE MENSAGENS (CONTAINER VAZIO - PREENCHIDO VIA JS) --}}
            <div id="mensagens-container"></div>

            {{-- MAPA --}}
            <hr class="my-8 border-gray-200">
            <div class="mb-4">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">🗺️ Mapa de Solicitações (Geral)</h3>
                <p class="text-gray-600 text-sm">Visualizando <strong>todas</strong> as solicitações do período selecionado.</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 mt-4">
                {{-- Mapa --}}
                <div class="lg:w-3/4 relative">
                    <div id="map-contacts"></div>
                    <p id="map-status" class="text-xs text-center text-gray-500 mt-2">Iniciando mapa...</p>
                </div>
                
                {{-- Painel Lateral Mapa --}}
                <div class="lg:w-1/4 space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                        <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2"><i data-lucide="filter" class="w-4 h-4"></i> Filtros do Mapa</h4>
                        
                        <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Filtrar por Status</label>
                        <select id="map-status-filter" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-[#358054] focus:ring-[#358054] mb-3">
                            <option value="">Todos os Status</option>
                            @foreach ($statusColors as $status => $color)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>

                        <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Filtrar por Bairro</label>
                        <select id="map-bairro-filter" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-[#358054] focus:ring-[#358054] mb-3">
                            <option value="">Todos os Bairros</option>
                        </select>

                        <div class="bg-white p-2 rounded border border-gray-200 mb-3 text-center">
                            <span class="text-xs text-gray-500 block">Encontrados</span>
                            <span id="map-counter" class="text-xl font-bold text-[#358054]">0</span>
                        </div>

                        <div class="space-y-2">
                            <button id="btn-clear-map" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded transition"><i data-lucide="x" class="w-3 h-3"></i> Limpar Filtros</button>
                            <button id="btn-export-map" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-[#217346] hover:bg-[#1e6b41] text-white text-sm font-semibold rounded transition shadow-sm"><i data-lucide="file-spreadsheet" class="w-3 h-3"></i> Baixar Excel</button>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <h4 class="font-bold text-gray-700 mb-3">Legenda</h4>
                        <div class="grid grid-cols-1 gap-2 text-sm max-h-48 overflow-y-auto">
                            @foreach ($statusColors as $status => $color)
                                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full inline-block border border-white shadow-sm" style="background-color: {{ $color }};"></span><span class="text-gray-600">{{ $status }}</span></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- MODAIS --}}
    <div id="modal-view" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 relative">
            <button onclick="closeViewModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"><i data-lucide="x"></i></button>
            <h2 class="text-2xl font-bold text-[#358054] mb-4 text-center">Detalhes</h2>
            <div class="space-y-3">
                <p><strong>Tipo:</strong> <span id="view-topico" class="font-semibold text-[#358054]"></span></p>
                <p><strong>Nome:</strong> <span id="view-nome"></span></p>
                <p><strong>Email:</strong> <span id="view-email"></span></p>
                <p><strong>Endereço:</strong> <span id="view-endereco"></span></p>
                <p id="view-telefone-container" style="display: none;"><strong>Telefone:</strong> <span id="view-telefone"></span></p>
                <div class="bg-gray-100 p-3 rounded"><p id="view-descricao"></p></div>
                <button onclick="openFotosModal(currentViewingId)" class="mt-4 w-full bg-[#358054] text-white py-2 rounded-lg">Ver Fotos</button>
            </div>
        </div>
    </div>
    
    {{-- (Outros modais de foto, status e encaminhamento mantidos, apenas omiti para poupar espaço mas devem estar aqui no seu código final) --}}
    <div id="modal-fotos" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-[9999]"><div class="bg-white w-full max-w-3xl rounded-xl shadow-xl p-6 relative"><button onclick="closeFotosModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"><i data-lucide="x"></i></button><h2 class="text-2xl font-bold text-[#358054] mb-4 text-center">Fotos</h2><div id="fotos-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[60vh] overflow-auto p-2"></div></div></div>
    <div id="lightbox-admin" onclick="closeLightbox()" style="display: none;"><span id="lightbox-close-admin">×</span><img id="lightbox-img-admin" src=""></div>
    <div id="modal-status" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50"><div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative"><button onclick="closeStatusModal()" class="absolute top-3 right-3 text-gray-600"><i data-lucide="x"></i></button><h2 class="text-2xl font-bold text-blue-700 mb-4">Atualizar Status</h2><form id="status-form" onsubmit="return submitStatusForm(event)" class="space-y-3">@csrf @method('PATCH')<label class="font-semibold">Status</label><select name="status_id" id="status-select" class="w-full rounded-md border-gray-300 shadow-sm">@foreach ($allStatuses as $status)<option value="{{ $status->id }}">{{ $status->name }}</option>@endforeach</select><div id="just-box"><label class="font-semibold">Justificativa</label><textarea name="justificativa" id="status-justificativa" class="w-full rounded-md border-gray-300 shadow-sm" rows="3"></textarea></div><button class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salvar</button></form></div></div>
    <div id="modal-forward" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50"><div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative"><button onclick="closeForwardModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"><i data-lucide="x"></i></button><h2 id="forward-title" class="text-2xl font-bold text-orange-700 mb-4">Encaminhar Solicitação</h2><form id="forward-form" onsubmit="return submitForwardForm(event)" class="space-y-3">@csrf @method('PATCH')<input type="hidden" name="forward_type" id="forward_type"><label id="forward-label" class="font-semibold">Selecione:</label><select id="forward-user-select" class="w-full rounded-md border-gray-300 shadow-sm" required><option value="">Carregando...</option></select><button id="forward-save-btn" class="w-full px-3 py-2 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700">Confirmar Encaminhamento</button></form></div></div>

    {{-- SCRIPTS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // DADOS DO PHP (Tabela já ordenada e Mapa completo)
        const messagesList = @json($messages); // Array simples (preserva ordem do PHP)
        const messagesById = @json($messages->keyBy('id')); // Objeto para busca rápida (Modais)
        
        // Dados do mapa (usa o mapMessages se existir, senão o fallback)
        const mapDataRaw = @json($mapMessages ?? $messages); 

        const analistas = @json($analistas ?? []);
        const servicos = @json($servicos ?? []);
        const statusColors = @json($statusColors);
        const currentFilter = '{{ $filtro }}';

        // Definição dos grupos
        const groupsPendentes = ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'];
        const groupsResolvidas = ['Concluído', 'Indeferido', 'Sem Pendências'];

        // Variáveis globais
        let currentViewingId = null, currentEditingId = null, currentForwardingId = null;
        let mapInstance = null, markersLayer = null, allMarkersData = [], filteredData = [];

        // --- MANIPULAÇÃO DE STRINGS ---
        function escapeHtml(unsafe) { if (!unsafe) return ''; return String(unsafe).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;"); }
        function normalizeString(str) { if (!str) return ""; return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim(); }

        // --- RENDERIZAÇÃO DA TABELA (LISTA) ---
        function renderMainList() {
            const container = document.getElementById('mensagens-container');
            container.innerHTML = '';

            // Se for "todas", renderiza uma tabela única
            if (currentFilter === 'todas') {
                container.innerHTML = renderTableHTML(messagesList);
            } 
            // Se for pendentes ou resolvidas, agrupa
            else {
                const targetGroups = (currentFilter === 'pendentes') ? groupsPendentes : groupsResolvidas;
                
                targetGroups.forEach(group => {
                    // Filtra via JS para montar os grupos
                    const itensDoGrupo = messagesList.filter(m => m.status && m.status.name === group);
                    
                    const block = document.createElement('div');
                    block.setAttribute('x-data', '{ open: true }');
                    
                    const color = statusColors[group] || '#333';
                    
                    block.innerHTML = `
                        <h3 @click="open = !open" class="mt-6 text-xl font-semibold cursor-pointer" style="color: ${color};">
                            ${group}
                            <svg class="w-4 h-4 ml-1 inline-block transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </h3>
                        <div x-show="open" class="mt-4">
                            ${renderTableHTML(itensDoGrupo)}
                        </div>`;
                    
                    container.appendChild(block);
                });
            }
            if(window.lucide) lucide.createIcons();
        }

        function renderTableHTML(itens) {
            if (!itens || itens.length === 0) return `<p class="text-gray-400 mt-2 ml-2">Nenhuma solicitação encontrada.</p>`;
            
            const rows = itens.map(m => {
                const created = new Date(m.created_at).toLocaleString('pt-BR');
                const topico = m.topico ?? '';
                const endereco = [m.bairro, m.rua, m.numero].filter(Boolean).join(', ');
                const descricao = m.descricao ?? '';
                const statusName = m.status ? m.status.name.trim() : '';
                
                // Botões de ação
                let actionButtons = '';
                if (statusName === 'Em Execução' && m.service_order?.viewed_at) {
                    const viewedDate = new Date(m.service_order.viewed_at).toLocaleString('pt-BR', {day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});
                    const servName = m.service_order.service?.name || 'Equipe Técnica';
                    actionButtons += `<div class="flex flex-col items-end mr-4 text-xs text-gray-500 border-r pr-3 border-gray-200"><span class="font-bold text-[#358054]">Visto por: ${servName}</span><span>${viewedDate}</span></div>`;
                }
                if (statusName === 'Deferido') actionButtons += `<button onclick="openForwardModal(${m.id}, 'analista')" class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white rounded text-xs font-semibold hover:bg-orange-700 mr-2"><i data-lucide="user-check" class="w-3 h-3 mr-1"></i> Analista</button>`;
                else if (statusName.toLowerCase().includes('vistoriado')) actionButtons += `<button onclick="openForwardModal(${m.id}, 'servico')" class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white rounded text-xs font-semibold hover:bg-orange-700 mr-2"><i data-lucide="hammer" class="w-3 h-3 mr-1"></i> Serviço</button>`;

                const temOS = m.service_order && m.service_order.id;
                const statusOS = ['Vistoriado', 'Em Execução', 'Concluído', 'Indeferido', 'Sem Pendências'];
                if (temOS && statusOS.some(s => statusName.includes(s))) {
                    actionButtons += `<a href="/pbi-admin/os/${m.service_order.id}" class="inline-flex items-center justify-center px-3 py-1.5 bg-[#358054] text-white rounded text-xs font-semibold hover:bg-[#2d6947] mr-2"><i data-lucide="file-text" class="w-3 h-3 mr-1"></i> Ver OS</a>`;
                } else {
                    actionButtons += `<button onclick="openViewModal(${m.id})" class="px-3 py-1.5 bg-[#358054] text-white rounded text-xs font-semibold hover:bg-[#2d6947] mr-2">Ver</button>`;
                }
                actionButtons += `<button onclick="openStatusModal(${m.id})" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold hover:bg-blue-700">Status</button>`;

                return `<tr class="border-t hover:bg-gray-50 transition">
                    <td class="px-6 py-4 align-top text-sm text-gray-500">${escapeHtml(created)}</td>
                    <td class="px-6 py-4 align-top">
                        <div class="text-sm font-medium text-gray-900"><span class="font-semibold text-[#358054]">${escapeHtml(topico)}</span> - ${escapeHtml(endereco)}</div>
                        <div class="text-xs text-gray-500 mt-1">${escapeHtml(descricao.substring(0,100))}...</div>
                    </td>
                    <td class="px-6 py-4 align-top text-right text-sm"><div class="flex justify-end gap-2 items-center flex-wrap">${actionButtons}</div></td>
                </tr>`;
            }).join('');

            return `<div class="overflow-x-auto"><table class="min-w-full table-fixed divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 w-1/4 text-left text-xs text-gray-500 uppercase">Data</th><th class="px-6 py-3 w-2/4 text-left text-xs text-gray-500 uppercase">Solicitação</th><th class="px-6 py-3 w-1/4 text-right text-xs text-gray-500 uppercase">Ações</th></tr></thead><tbody class="bg-white">${rows}</tbody></table></div>`;
        }

        // --- MAPA ---
        async function initContactMap() {
            mapInstance = L.map('map-contacts').setView([-22.6091, -43.7089], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapInstance);
            markersLayer = L.layerGroup().addTo(mapInstance);
            const statusText = document.getElementById('map-status');
            statusText.innerText = "Carregando...";

            try {
                const response = await fetch('/bairros.json');
                if (!response.ok) throw new Error("Erro JSON");
                const geoJsonData = await response.json();
                const bairrosCenters = {};
                
                geoJsonData.features.forEach(f => {
                    if (f.properties.nome && f.geometry.type === "Polygon") {
                        const c = f.geometry.coordinates[0];
                        let lat=0, lng=0;
                        c.forEach(pt=>{lng+=pt[0];lat+=pt[1]});
                        bairrosCenters[normalizeString(f.properties.nome)] = {lat: lat/c.length, lng: lng/c.length};
                    }
                });
                L.geoJSON(geoJsonData, { style: {color: "#358054", weight: 1, fillOpacity: 0.05} }).addTo(mapInstance);

                allMarkersData = [];
                let pinCount = 0;
                const bairroCounts = {};
                const dataArray = Array.isArray(mapDataRaw) ? mapDataRaw : Object.values(mapDataRaw);

                dataArray.forEach(msg => {
                    const bNorm = normalizeString(msg.bairro);
                    const bReal = msg.bairro || 'Desconhecido';
                    bairroCounts[bReal] = (bairroCounts[bReal] || 0) + 1;

                    if (bairrosCenters[bNorm]) {
                        const center = bairrosCenters[bNorm];
                        allMarkersData.push({
                            lat: center.lat + (Math.random()-0.5)*0.004,
                            lng: center.lng + (Math.random()-0.5)*0.004,
                            color: statusColors[msg.status?.name.trim()] || '#333',
                            status: msg.status?.name.trim() || 'Desconhecido',
                            bairro: bReal,
                            msg: msg
                        });
                        pinCount++;
                    }
                });

                statusText.innerText = `${pinCount} solicitações no mapa.`;
                
                const bSelect = document.getElementById('map-bairro-filter');
                Object.keys(bairroCounts).sort().forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b; opt.textContent = `${b} (${bairroCounts[b]})`;
                    bSelect.appendChild(opt);
                });

                renderMarkers();

                document.getElementById('map-status-filter').addEventListener('change', () => applyMapFilters());
                document.getElementById('map-bairro-filter').addEventListener('change', () => applyMapFilters());
                document.getElementById('btn-clear-map').addEventListener('click', () => {
                    document.getElementById('map-status-filter').value = "";
                    document.getElementById('map-bairro-filter').value = "";
                    applyMapFilters();
                });
                document.getElementById('btn-export-map').addEventListener('click', exportMapToExcel);

            } catch (e) { console.error(e); statusText.innerText = "Erro ao carregar mapa."; }
        }

        function applyMapFilters() {
            const st = document.getElementById('map-status-filter').value;
            const br = document.getElementById('map-bairro-filter').value;
            renderMarkers(st, br);
        }

        function renderMarkers(fStatus = "", fBairro = "") {
            markersLayer.clearLayers();
            filteredData = [];
            allMarkersData.forEach(d => {
                if ((fStatus === "" || d.status === fStatus) && (fBairro === "" || d.bairro === fBairro)) {
                    filteredData.push(d.msg);
                    const m = L.circleMarker([d.lat, d.lng], {color:'#fff', weight:1, fillColor:d.color, fillOpacity:0.8, radius:7}).addTo(markersLayer);
                    // Garante modal
                    if(!messagesById[d.msg.id]) messagesById[d.msg.id] = d.msg;
                    m.bindPopup(`<div style="text-align:center;font-size:13px"><strong style="color:${d.color}">${d.status}</strong><br><b>${d.msg.topico}</b><br>${d.msg.bairro}<br><a href="#" onclick="openViewModal(${d.msg.id});return false" style="color:#358054;font-weight:bold;display:block;margin-top:4px">Ver Detalhes</a></div>`);
                }
            });
            document.getElementById('map-counter').innerText = filteredData.length;
        }

        function exportMapToExcel() {
            if(!filteredData.length) { alert("Nada para exportar."); return; }
            let csv = "data:text/csv;charset=utf-8,\uFEFFID;Data;Status;Solicitante;Email;Telefone;Bairro;Rua;Descricao\n";
            filteredData.forEach(i => {
                const c = (t) => t ? String(t).replace(/;/g, ",").replace(/[\r\n]+/g, " ") : "";
                const ph = i.telefone || i.user?.phone || "";
                csv += `${i.id};${new Date(i.created_at).toLocaleDateString()};${i.status?.name};${c(i.nome_solicitante)};${c(i.email_solicitante)};${c(ph)};${c(i.bairro)};${c(i.rua)};${c(i.descricao)}\n`;
            });
            const link = document.createElement("a");
            link.href = encodeURI(csv); link.download = "mapa_filtrado.csv";
            link.click();
        }

        // --- START ---
        document.addEventListener('DOMContentLoaded', () => {
            renderMainList(); // Monta a lista via JS (ordenada corretamente)
            initContactMap(); // Monta o mapa via JS (completo)
        });

        // --- Modais ---
        function openViewModal(id) {
            currentViewingId = id;
            let m = messagesById[id] || messagesList.find(x => x.id == id);
            if(!m) return;
            document.getElementById('view-topico').textContent = m.topico ?? '';
            document.getElementById('view-nome').textContent = m.user?.name ?? m.nome_solicitante;
            document.getElementById('view-email').textContent = m.user?.email ?? m.email_solicitante;
            document.getElementById('view-endereco').textContent = `${m.bairro}, ${m.rua}, ${m.numero}`;
            document.getElementById('view-descricao').textContent = m.descricao;
            document.getElementById('view-telefone').textContent = m.telefone || m.celular || m.phone || m.user?.phone || 'Não informado';
            document.getElementById('view-telefone-container').style.display = 'block';
            document.getElementById('modal-view').classList.remove('hidden');
            document.getElementById('modal-view').classList.add('flex');
        }
        // (Outras funções de fechar modal, fotos, encaminhar mantidas...)
        function closeViewModal() { document.getElementById('modal-view').classList.add('hidden'); document.getElementById('modal-view').classList.remove('flex'); }
        function openFotosModal(id) {
            let m = messagesById[id] || messagesList.find(x => x.id == id);
            const container = document.getElementById('fotos-container'); container.innerHTML = '';
            let fotos = m.fotos;
            if (typeof fotos === 'string') { try { fotos = JSON.parse(fotos); } catch(e){ fotos=[]; } }
            if (Array.isArray(fotos) && fotos.length) {
                fotos.forEach(path => {
                    let img = document.createElement('img'); img.src = `/storage/${path}`;
                    img.className = "w-full h-64 object-cover rounded-lg shadow cursor-pointer hover:opacity-80";
                    img.onclick = () => { document.getElementById('lightbox-img-admin').src = img.src; document.getElementById('lightbox-admin').style.display = 'flex'; };
                    container.appendChild(img);
                });
            } else { container.innerHTML = `<p class="text-gray-500">Sem fotos.</p>`; }
            document.getElementById('modal-fotos').classList.remove('hidden'); document.getElementById('modal-fotos').classList.add('flex');
        }
        function closeFotosModal() { document.getElementById('modal-fotos').classList.add('hidden'); document.getElementById('modal-fotos').classList.remove('flex'); }
        function closeLightbox() { document.getElementById('lightbox-admin').style.display = 'none'; }
        function openStatusModal(id) {
            currentEditingId = id; const m = messagesById[id] || messagesList.find(x => x.id == id);
            document.getElementById('status-select').value = m.status_id ?? '';
            document.getElementById('status-justificativa').value = m.justificativa ?? '';
            toggleJustificativaVisibility();
            document.getElementById('modal-status').classList.remove('hidden'); document.getElementById('modal-status').classList.add('flex');
        }
        function closeStatusModal() { document.getElementById('modal-status').classList.add('hidden'); document.getElementById('modal-status').classList.remove('flex'); }
        document.getElementById('status-select').addEventListener('change', toggleJustificativaVisibility);
        function toggleJustificativaVisibility() {
            const select = document.getElementById('status-select'); const justBox = document.getElementById('just-box');
            const chosen = select.options[select.selectedIndex]?.text || '';
            justBox.style.display = (chosen === 'Indeferido' || chosen === 'Sem Pendências') ? 'block' : 'none';
        }
        async function submitStatusForm(e) {
            e.preventDefault(); const url = `/pbi-admin/contacts/${currentEditingId}`;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const fd = new FormData(document.getElementById('status-form')); fd.append('_method', 'PATCH');
            await fetch(url, { method: 'POST', headers: {'X-CSRF-TOKEN': token}, body: fd }).then(()=>{ location.reload() });
        }
        function openForwardModal(id, type) {
            currentForwardingId = id;
            document.getElementById('forward_type').value = type;
            const select = document.getElementById('forward-user-select'); select.innerHTML = '<option value="" disabled selected>Selecione...</option>';
            let lista = (type === 'analista') ? analistas : servicos;
            document.getElementById('forward-title').textContent = (type === 'analista') ? 'Encaminhar para Analista' : 'Encaminhar para Serviço';
            if(!lista.length) select.add(new Option("Nenhum encontrado"));
            else lista.forEach(u => select.appendChild(new Option(u.name, u.id)));
            document.getElementById('modal-forward').classList.remove('hidden'); document.getElementById('modal-forward').classList.add('flex');
        }
        function closeForwardModal() { document.getElementById('modal-forward').classList.add('hidden'); document.getElementById('modal-forward').classList.remove('flex'); }
        async function submitForwardForm(e) {
            e.preventDefault(); if (!currentForwardingId) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = `/pbi-admin/contacts/${currentForwardingId}/forward`;
            const type = document.getElementById('forward_type').value;
            const selectedId = document.getElementById('forward-user-select').value;
            if(!selectedId) { alert("Selecione."); return; }
            const fd = new FormData(); fd.append('_method', 'PATCH');
            if (type === 'analista') fd.append('analyst_id', selectedId); else fd.append('service_id', selectedId);
            try {
                const req = await fetch(url, { method: "POST", headers: { "X-CSRF-TOKEN": token, 'Accept': 'application/json' }, body: fd });
                if (req.ok) { alert('Sucesso!'); location.reload(); } else alert("Erro.");
            } catch (err) { console.error(err); }
        }
    </script>
</body>
</html>
@endsection
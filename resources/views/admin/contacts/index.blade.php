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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite('resources/css/dashboard.css')

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #lightbox-admin { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.95); display: none; align-items: center; justify-content: center; z-index: 99999; }
        #lightbox-admin img { max-width: 90vw; max-height: 90vh; border-radius: 8px; object-fit: contain; }
        #lightbox-close-admin { position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; }
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

            {{-- Botões de Filtro --}}
            <div class="flex items-center justify-center relative mb-6">
                <div class="flex justify-center gap-6">
                    <a href="{{ route('admin.contato.index') }}?filtro=todas" class="px-6 py-3 min-w-[140px] text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'todas' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">Todas</a>
                    <a href="{{ route('admin.contato.index') }}?filtro=pendentes" class="px-6 py-3 min-w-[140px] text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'pendentes' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">Pendentes</a>
                    <a href="{{ route('admin.contato.index') }}?filtro=resolvidas" class="px-6 py-3 min-w-[140px] text-center rounded-lg font-semibold shadow-sm transition-all {{ $filtro === 'resolvidas' ? 'bg-[#358054] text-white' : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">Resolvidas</a>
                </div>
                <div class="absolute right-0 text-sm text-gray-600">Total: {{ $messages->count() }}</div>
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

            <div id="mensagens-container"></div>
        </div>
    </main>

    {{-- MODAIS --}}

    {{-- Modal Detalhes (Visualizar) --}}
    <div id="modal-view" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 relative">
            <button onclick="closeViewModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"><i data-lucide="x"></i></button>
            <h2 class="text-2xl font-bold text-[#358054] mb-4 text-center">Detalhes</h2>
            <div class="space-y-3">
                <p><strong>Tipo:</strong> <span id="view-topico" class="font-semibold text-[#358054]"></span></p>
                <p><strong>Nome:</strong> <span id="view-nome"></span></p>
                <p><strong>Email:</strong> <span id="view-email"></span></p>
                <p><strong>Endereço:</strong> <span id="view-endereco"></span></p>
                <div class="bg-gray-100 p-3 rounded"><p id="view-descricao"></p></div>
                <button onclick="openFotosModal(currentViewingId)" class="mt-4 w-full bg-[#358054] text-white py-2 rounded-lg">Ver Fotos</button>
            </div>
        </div>
    </div>
    
    {{-- Modal Fotos --}}
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

    {{-- Modal Atualizar Status --}}
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

    {{-- MODAL DE ENCAMINHAMENTO --}}
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

                <button id="forward-save-btn" class="w-full px-3 py-2 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700">
                    Confirmar Encaminhamento
                </button>
            </form>
        </div>
    </div>

    {{-- SCRIPTS JS --}}
    <script>
        const messages = @json($messages->keyBy('id'));
        const analistas = @json($analistas ?? []);
        const servicos = @json($servicos ?? []);

        let currentViewingId = null;
        let currentEditingId = null;
        let currentForwardingId = null;

        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        function renderTable(itens) {
            if (!itens || itens.length === 0) return `<p class="text-gray-400 mt-2 ml-2">Nenhuma solicitação neste grupo.</p>`;

            let rows = itens.map(m => {
                const created = new Date(m.created_at).toLocaleString('pt-BR');
                const topico = m.topico ?? '';
                const endereco = [m.bairro, m.rua, m.numero].filter(Boolean).join(', ');
                const descricao = m.descricao ?? '';
                const statusName = m.status ? m.status.name.trim() : '';

                // --- LÓGICA DOS BOTÕES ---
                let actionButtons = '';

                // 1. VISUALIZAÇÃO DE "VISTO POR" (NOVO - Aparece à esquerda dos botões)
                // Só mostra se estiver "Em Execução" E se já tiver a data de visualização (viewed_at)
                if (statusName === 'Em Execução' && m.service_order && m.service_order.viewed_at) {
                    const viewedDate = new Date(m.service_order.viewed_at).toLocaleString('pt-BR', {
                        day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
                    });
                    
                    // Tenta pegar o nome da conta de serviço. Se não vier, usa "Equipe" como padrão.
                    const serviceName = (m.service_order.service && m.service_order.service.name) 
                                        ? m.service_order.service.name 
                                        : 'Equipe Técnica';
                    
                    // Adicionamos esse bloco ANTES dos botões para ficar à esquerda
                    actionButtons += `
                        <div class="flex flex-col items-end mr-4 text-xs text-gray-500 border-r pr-3 border-gray-200">
                            <span class="font-bold text-[#358054]">Visto por: ${serviceName}</span>
                            <span>${viewedDate}</span>
                        </div>
                    `;
                }

                // 2. BOTÕES DE ENCAMINHAMENTO (Se aplicável)
                if (statusName === 'Deferido') {
                    actionButtons += `
                        <button onclick="openForwardModal(${m.id}, 'analista')" 
                            class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white rounded text-xs font-semibold hover:bg-orange-700 mr-2" title="Encaminhar para Analista">
                            <i data-lucide="user-check" class="w-3 h-3 mr-1"></i> Analista
                        </button>`;
                } 
                else if (statusName.toLowerCase().includes('vistoriado')) {
                    actionButtons += `
                        <button onclick="openForwardModal(${m.id}, 'servico')" 
                            class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white rounded text-xs font-semibold hover:bg-orange-700 mr-2" title="Encaminhar para Serviço">
                            <i data-lucide="hammer" class="w-3 h-3 mr-1"></i> Serviço
                        </button>`;
                }

                // 3. BOTÃO VER INTELIGENTE
                let btnVer = '';
                const temOS = m.service_order && m.service_order.id;
                const statusQueAbremOS = ['Vistoriado', 'Em Execução', 'Concluído', 'Indeferido', 'Sem Pendências'];
                const deveAbrirOS = statusQueAbremOS.some(s => statusName.includes(s));

                if (temOS && deveAbrirOS) {
                    const urlOS = `/pbi-admin/os/${m.service_order.id}`;
                    btnVer = `
                        <a href="${urlOS}" 
                           class="inline-flex items-center justify-center px-3 py-1.5 bg-[#358054] text-white rounded text-xs font-semibold hover:bg-[#2d6947] mr-2"
                           title="Visualizar Ordem de Serviço Completa">
                           <i data-lucide="file-text" class="w-3 h-3 mr-1"></i> Ver OS
                        </a>`;
                } else {
                    btnVer = `
                        <button onclick="openViewModal(${m.id})" 
                            class="px-3 py-1.5 bg-[#358054] text-white rounded text-xs font-semibold hover:bg-[#2d6947] mr-2"
                            title="Ver Detalhes da Solicitação">
                            Ver
                        </button>`;
                }
                actionButtons += btnVer;

                // 4. BOTÃO STATUS
                actionButtons += `
                    <button onclick="openStatusModal(${m.id})" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold hover:bg-blue-700">
                        Status
                    </button>`;

                return `
                <tr class="border-t hover:bg-gray-50 transition">
                    <td class="px-6 py-4 align-top text-sm text-gray-500">${escapeHtml(created)}</td>
                    <td class="px-6 py-4 align-top">
                        <div class="text-sm font-medium text-gray-900">
                            <span class="font-semibold text-[#358054]">${escapeHtml(topico)}</span> - ${escapeHtml(endereco)}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">${escapeHtml(descricao.substring(0,100))}...</div>
                    </td>
                    <td class="px-6 py-4 align-top text-right text-sm">
                        <div class="flex justify-end gap-2 items-center flex-wrap">
                            ${actionButtons}
                        </div>
                    </td>
                </tr>`;
            }).join('');

            return `<div class="overflow-x-auto"><table class="min-w-full table-fixed divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-6 py-3 w-1/4 text-left text-xs text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 w-2/4 text-left text-xs text-gray-500 uppercase">Solicitação</th>
                    <th class="px-6 py-3 w-1/4 text-right text-xs text-gray-500 uppercase">Ações</th>
                </tr></thead>
                <tbody class="bg-white">${rows}</tbody>
            </table></div>`;
        }

        // --- FUNÇÕES MODAIS (PADRÃO) ---
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

            if(!selectedId) {
                alert("Selecione um responsável.");
                return;
            }

            const fd = new FormData();
            fd.append('_method', 'PATCH');

            if (type === 'analista') {
                fd.append('analyst_id', selectedId);
            } else {
                fd.append('service_id', selectedId);
            }

            try {
                const req = await fetch(url, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": token, 'Accept': 'application/json' },
                    body: fd
                });

                const res = await req.json();

                if (req.ok) {
                    alert('Encaminhado com sucesso!');
                    location.reload();
                } else {
                    console.error(res);
                    alert("Erro ao encaminhar: " + (res.message || 'Verifique o console'));
                }
            } catch (err) {
                console.error(err);
                alert('Erro de conexão.');
            }
        }

        function openViewModal(id) {
            currentViewingId = id;
            let m = messages[id];
            document.getElementById('view-topico').textContent = m.topico ?? '';
            document.getElementById('view-nome').textContent = m.user?.name ?? m.nome_solicitante;
            document.getElementById('view-email').textContent = m.user?.email ?? m.email_solicitante;
            document.getElementById('view-endereco').textContent = `${m.bairro}, ${m.rua}, ${m.numero}`;
            document.getElementById('view-descricao').textContent = m.descricao;
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
        
        if(window.lucide) lucide.createIcons();

        // --- INICIALIZAÇÃO E GRUPOS (PHP -> JS) ---
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('mensagens-container');
            
            @if ($filtro === 'pendentes')
                @php
                foreach($groupsPendentes as $group){
                    // CORREÇÃO: USANDO SETINHA -> EM VEZ DE PONTO .
                    // Isso evita o erro "Undefined constant status"
                    $ids = $messages->filter(fn($m) => $m->status && $m->status->name === $group)->pluck('id')->values();
                    $jsonIds = $ids->toJson();
                @endphp
                    (function () {
                        const block = document.createElement('div');
                        block.setAttribute('x-data', '{ open: true }');
                        block.innerHTML = `
                    <h3 @click="open = !open" class="mt-6 text-xl font-semibold cursor-pointer" style="color: {{ $statusColors[$group] ?? '#333' }};">
                        {{ $group }}
                        <svg class="w-4 h-4 ml-1 inline-block transform transition-transform"
                             :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </h3>
                    <div x-show="open" class="mt-4 lista-placeholder" data-ids='{!! $jsonIds !!}'></div>`;
                        container.appendChild(block);
                    })();
                @php
                }
                @endphp

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
                        <svg class="w-4 h-4 ml-1 inline-block transform transition-transform"
                             :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </h3>
                    <div x-show="open" class="mt-4 lista-placeholder" data-ids='{!! $jsonIds !!}'></div>`;
                        container.appendChild(block);
                    })();
                @php
                }
                @endphp

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
        });
    </script>

</body>
</html>
@endsection
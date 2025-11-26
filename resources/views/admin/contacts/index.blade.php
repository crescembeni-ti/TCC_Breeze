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
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">

    <!-- HEADER -->
    <header class="site-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center flex-wrap gap-4">

            <!-- LOGOS E TÍTULO -->
            <div class="flex items-center gap-4 flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-4">
                    <img src="{{ asset('images/Brasao_Verde.png') }}" alt="Logo Brasão de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi"
                        class="h-16 w-16 sm:h-20 sm:w-20 object-contain">
                    <h1 class="text-3xl sm:text-4xl font-bold">
                        <span class="text-[#358054]">Árvores de</span>
                        <span class="text-[#a0c520]">Paracambi</span>
                    </h1>
                </a>
            </div>
        </div>
    </header>

    <div class="flex flex-1 w-full items-start">
        <!-- SIDEBAR -->
        <aside
            class="sidebar w-60 bg-[#358054] text-white flex flex-col py-8 px-4 sticky top-0 h-fit self-start rounded-br-2xl">
            <nav class="space-y-4">

                @if (auth('admin')->check())
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                    <i data-lucide="layout-dashboard" class="icon"></i>
                    <span>Painel</span>
                </a>

                <a href="{{ route('admin.map') }}" class="sidebar-link">
                    <i data-lucide="map-pin" class="icon"></i>
                    <span>Cadastrar Árvores</span>
                </a>

                <a href="{{ route('admin.trees.index') }}" class="sidebar-link">
                    <i data-lucide="edit-3" class="icon"></i>
                    <span>Editar Árvores</span>
                </a>

                <a href="{{ route('admin.contato.index') }}" class="sidebar-link">
                    <i data-lucide="inbox" class="icon"></i>
                    <span>Solicitações</span>
                </a>

                <a href="{{ route('admin.profile.edit') }}" class="sidebar-link">
                    <i data-lucide="user" class="icon"></i>
                    <span>Meu Perfil</span>
                </a>

                <a href="{{ route('about') }}" class="sidebar-link">
                    <i data-lucide="info" class="icon"></i>
                    <span>Sobre o Site</span>
                </a>
                @else
                <a href="{{ route('dashboard') }}" class="sidebar-link">
                    <i data-lucide="layout-dashboard" class="icon"></i>
                    <span>Menu</span>
                </a>

                <a href="{{ route('contact') }}" class="sidebar-link">
                    <i data-lucide="send" class="icon"></i>
                    <span>Nova Solicitação</span>
                </a>

                <a href="{{ route('contact.myrequests') }}" class="sidebar-link">
                    <i data-lucide="clipboard-list" class="icon"></i>
                    <span>Minhas Solicitações</span>
                </a>

                <a href="{{ route('profile.edit') }}" class="sidebar-link">
                    <i data-lucide="user" class="icon"></i>
                    <span>Meu Perfil</span>
                </a>

                <a href="{{ route('about') }}" class="sidebar-link">
                    <i data-lucide="info" class="icon"></i>
                    <span>Sobre o Site</span>
                </a>
                @endif
            </nav>

            <div class="mt-auto border-t-2 border-green-400 pt-8">

                <a href="{{ route('home') }}" class="sidebar-link text-base font-medium hover:opacity-100">
                    <i data-lucide="arrow-left-circle" class="icon"></i>
                    Voltar ao Mapa
                </a>

                @if (auth('admin')->check())
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                    @csrf
                    <a href="#" class="sidebar-link text-base font-medium hover:opacity-100 logout-btn">
                        <i data-lucide="log-out" class="icon"></i>
                        Sair
                    </a>
                </form>
                @else
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <a href="#" class="sidebar-link text-base font-medium hover:opacity-100 logout-btn">
                        <i data-lucide="log-out" class="icon"></i>
                        Sair
                    </a>
                </form>
                @endif
            </div>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->

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

                <!-- FILTROS -->
                <div class="flex items-center justify-center relative mb-6">
                    <!-- Botões centralizados -->
                    <div class="flex justify-center gap-6">
                        <a href="{{ route('admin.contato.index') }}?filtro=todas" class="px-6 py-3 min-w-[140px] text-center rounded-lg font-semibold shadow-sm transition-all
                  {{ $filtro === 'todas' 
                     ? 'bg-[#358054] text-white' 
                     : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">
                            Todas
                        </a>

                        <a href="{{ route('admin.contato.index') }}?filtro=pendentes" class="px-6 py-3 min-w-[140px] text-center rounded-lg font-semibold shadow-sm transition-all
                  {{ $filtro === 'pendentes' 
                     ? 'bg-[#358054] text-white' 
                     : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">
                            Pendentes
                        </a>

                        <a href="{{ route('admin.contato.index') }}?filtro=resolvidas" class="px-6 py-3 min-w-[140px] text-center rounded-lg font-semibold shadow-sm transition-all
                  {{ $filtro === 'resolvidas' 
                     ? 'bg-[#358054] text-white' 
                     : 'bg-[#38c224]/10 text-[#358054] hover:bg-[#38c224]/20' }}">
                            Resolvidas
                        </a>
                    </div>

                    <!-- Total alinhado à direita -->
                    <div class="absolute right-0 text-sm text-gray-600">
                        Total: {{ $messages->count() }}
                    </div>
                </div>


                <!-- AGRUPAMENTO -->
                @php
                $groupsPendentes = ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'];
                $groupsResolvidas = ['Concluído', 'Indeferido', 'Sem Pendências'];

                // Apenas cor do texto (sem fundo)
                $statusColors = [
                'Em Análise' => '#9ea3af',
                'Deferido' => '#3850d6',
                'Indeferido' => '#d2062a',
                'Vistoriado' => '#8c3c14',
                'Em Execução' => '#f4ca29',
                'Sem Pendências' => '#ef6d22',
                'Concluído' => '#34a54c',
                ];
                @endphp


                <!-- Local onde o JS vai inserir os blocos -->
                <div id="mensagens-container"></div>

            </div>
        </main>
    </div>

    <!-- RODAPÉ -->
    <footer class="bg-gray-800 shadow mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-300">© {{ date('Y') }} Árvores de Paracambi.</p>
        </div>
    </footer>

    {{-- Partial de lista (inline para facilitar) --}}
    @push('partials')
    @endpush

    {{-- Modal View (igual) --}}
    <div id="modal-view" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative">
            <button onclick="closeViewModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>

            <h2 class="text-2xl font-bold text-[#358054] mb-4 text-center">Detalhes da Mensagem</h2>

            <div class="space-y-3">
                <p><strong>Nome:</strong> <span id="view-nome"></span></p>
                <p><strong>Email:</strong> <span id="view-email"></span></p>
                <p><strong>Endereço:</strong> <span id="view-endereco"></span></p>

                <div>
                    <p class="font-semibold">Mensagem:</p>
                    <p id="view-descricao" class="p-3 bg-gray-100 rounded-md text-sm"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Status (AJAX) --}}
    <div id="modal-status" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative">
            <button onclick="closeStatusModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>

            <h2 class="text-2xl font-bold text-blue-700 mb-4">Atualizar Status</h2>

            <form id="status-form" method="POST" class="space-y-3" onsubmit="return submitStatusForm(event)">
                @csrf
                @method('PATCH')

                <label class="font-semibold">Status</label>
                <select name="status_id" id="status-select" class="w-full rounded-md border-gray-300 shadow-sm">
                    @foreach ($allStatuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>

                <div id="just-box">
                    <label class="font-semibold">Justificativa</label>
                    <textarea name="justificativa" id="status-justificativa"
                        class="w-full rounded-md border-gray-300 shadow-sm" rows="3"></textarea>
                </div>

                <button id="status-save-btn"
                    class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    Salvar
                </button>
            </form>
        </div>
    </div>

    {{-- hidden template partial used inline to render table rows --}}
    @php
    // create a small function to render rows (blade)
    @endphp

    {{-- Lista partial file (render inline) --}}
    @once
    @push('partials')
    @endpush
    @endonce

    {{-- a tabela de cada bloco: partial renderizado inline --}}
    @if(false) {{-- placeholder para evitar erro de blade ao colar --}}
    @include('admin.contacts._lista')
    @endif

    {{-- SCRIPT --}}
    <script>
        // mensagens vindas do backend
        const messages = @json($messages -> keyBy('id'));

        // RENDER partial (JS) - monta uma tabela HTML a partir de uma coleção
        function renderTable(itens) {
            if (!itens || itens.length === 0) {
                return `<p class="text-gray-400 mt-2">Nenhuma solicitação aqui.</p>`;
            }

            let rows = itens.map(m => {
                const nome = (m.user && m.user.name) ? m.user.name : (m.nome_solicitante ?? '');
                const email = (m.user && m.user.email) ? m.user.email : (m.email_solicitante ?? '');
                const statusName = m.status ? m.status.name : '';
                const created = new Date(m.created_at).toLocaleString();

                // garante compatibilidade com diferentes nomes de campo
                const descricao = m.descricao ?? m.content ?? m.mensagem ?? '(sem descrição)';
                const endereco = [m.bairro, m.rua, m.numero].filter(Boolean).join(', ');

                return `
        <tr class="border-t">
            <td class="px-6 py-4 align-top text-sm text-gray-500">${escapeHtml(created)}</td>
            <td class="px-6 py-4 align-top">
                <div class="text-sm font-medium text-gray-900">${escapeHtml(endereco)}</div>
                <div class="text-sm text-gray-500">${escapeHtml(descricao.substring(0, 120))}</div>
            </td>
            <td class="px-6 py-4 align-top text-right text-sm space-x-2">
                <button onclick="openViewModal(${m.id})"
                        class="inline-flex items-center px-3 py-1.5 bg-[#358054] text-white rounded-md text-xs font-semibold hover:bg-[#2d6947] transition">
                    Ver
                </button>
                <button onclick="openStatusModal(${m.id})"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-700 text-white rounded-md text-xs font-semibold hover:bg-blue-600 transition-colors">
                    Atualizar
                </button>
            </td>
        </tr>
        `;
            }).join('');

            return `<div class="overflow-x-auto">
    <table class="min-w-full table-fixed divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 w-1/4 text-left text-xs font-medium text-gray-600 uppercase">Data</th>
                <th class="px-6 py-3 w-2/4 text-left text-xs font-medium text-gray-600 uppercase">Solicitação</th>
                <th class="px-6 py-3 w-1/4 text-right text-xs font-medium text-gray-600 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white">${rows}</tbody>
    </table>
</div>`;
        }

        // utility
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // monta os blocos já renderizados do blade — substitui include
        document.addEventListener('DOMContentLoaded', function () {
            // para cada grupo presente na página (h3 + placeholder), vamos renderizar dinamicamente
            document.querySelectorAll('h3').forEach(function (h) {
                const next = h.nextElementSibling;
                if (next && next.classList.contains('lista-placeholder')) {
                    // já vem do blade com data-group contendo os ids
                    const ids = JSON.parse(next.getAttribute('data-ids') || '[]');
                    const itens = ids.map(i => messages[i]).filter(Boolean);
                    next.innerHTML = renderTable(itens);
                }
            });

            // para o bloco "todas", se existir um placeholder com data-all="true"
            const allPlaceholder = document.querySelector('[data-all="true"]');
            if (allPlaceholder) {
                allPlaceholder.innerHTML = renderTable(Object.values(messages));
            }
        });

        // VIEW MODAL
        function openViewModal(id) {
            let m = messages[id];
            document.getElementById('view-nome').textContent = m.user?.name ?? m.nome_solicitante;
            document.getElementById('view-email').textContent = m.user?.email ?? m.email_solicitante;
            document.getElementById('view-endereco').textContent = `${m.bairro}, ${m.rua}, ${m.numero}`;
            document.getElementById('view-descricao').textContent = m.descricao;

            document.getElementById('modal-view').classList.remove('hidden');
            document.getElementById('modal-view').classList.add('flex');
        }
        function closeViewModal() {
            document.getElementById('modal-view').classList.add('hidden');
            document.getElementById('modal-view').classList.remove('flex');
        }

        // STATUS MODAL: abre e preenche
        let currentEditingId = null;
        function openStatusModal(id) {
            currentEditingId = id;
            const m = messages[id];

            // preenche select e justificativa
            document.getElementById('status-select').value = m.status_id ?? '';
            document.getElementById('status-justificativa').value = m.justificativa ?? '';

            // esconder justificativa por padrão e só mostrar para Indef/ Sem Pendencias
            toggleJustificativaVisibility();

            document.getElementById('modal-status').classList.remove('hidden');
            document.getElementById('modal-status').classList.add('flex');
        }

        function closeStatusModal() {
            document.getElementById('modal-status').classList.add('hidden');
            document.getElementById('modal-status').classList.remove('flex');
            currentEditingId = null;
        }

        // quando change no select
        document.getElementById('status-select').addEventListener('change', toggleJustificativaVisibility);
        function toggleJustificativaVisibility() {
            const select = document.getElementById('status-select');
            const justBox = document.getElementById('just-box');
            const chosen = select.options[select.selectedIndex].text;
            if (chosen === 'Indeferido' || chosen === 'Sem Pendências') {
                justBox.style.display = 'block';
            } else {
                justBox.style.display = 'none';
                document.getElementById('status-justificativa').value = '';
            }
        }

        // submit do form: envia via AJAX PATCH para /pbi-admin/contacts/{id}
        async function submitStatusForm(e) {
            e.preventDefault();
            if (!currentEditingId) return;

            const url = `/pbi-admin/contacts/${currentEditingId}`; // sua rota PATCH
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const form = document.getElementById('status-form');
            const fd = new FormData(form);
            // _method = PATCH (laravel)
            fd.append('_method', 'PATCH');

            try {
                const res = await fetch(url, {
                    method: 'POST', // usamos POST + _method=PATCH para compatibilidade
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: fd
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    alert(err?.message || 'Erro ao atualizar status.');
                    return;
                }

                const data = await res.json();
                // atualiza a mensagem localmente
                messages[data.contact.id] = data.contact;

                // fecha modal e atualiza apenas a linha (melhor opção é recarregar o bloco)
                closeStatusModal();

                // atualiza a interface: aqui simplificamos recarregando a página
                // mas se preferir atualizar sem reload, podemos re-renderizar os blocos.
                // Vou re-renderizar parcialmente: for simplicity, reload page to reflect grouping.
                // Se preferir evitar reload, substitua por lógica para atualizar dom.
                location.reload();

            } catch (err) {
                console.error(err);
                alert('Erro de rede ao atualizar status.');
            }
            return false;
        }

        lucide.createIcons();
    </script>

    {{-- placeholders preenchidos pelo blade com data (facilita render JS) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('mensagens-container');
            @if ($filtro === 'pendentes')
                @php
            foreach($groupsPendentes as $group){
                $ids = $messages -> filter(fn($m)=> $m -> status && $m -> status -> name === $group) -> pluck('id') -> values();
                $jsonIds = $ids -> toJson();
                @endphp
                    (function () {
                        const block = document.createElement('div');
                        block.setAttribute('x-data', '{ open: false }');
                        block.innerHTML = `
                    <h3 @click="open = !open" class="mt-6 text-xl font-semibold cursor-pointer" style="color: {{ $statusColors[$group] ?? '#333' }};">
                        {{ $group }}
                        <svg class="w-4 h-4 ml-1 inline-block transform transition-transform"
                             :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7" />
                        </svg>
                    </h3>
                    <div x-show="open" class="mt-4 lista-placeholder" data-ids='{{ $jsonIds }}'></div>
                `;
                        container.appendChild(block);
                    })();
                @php
            }
            @endphp
            @elseif($filtro === 'resolvidas')
            @php
            foreach($groupsResolvidas as $group){
                $ids = $messages -> filter(fn($m)=> $m -> status && $m -> status -> name === $group) -> pluck('id') -> values();
                $jsonIds = $ids -> toJson();
                @endphp
                    (function () {
                        const block = document.createElement('div');
                        block.setAttribute('x-data', '{ open: false }');
                        block.innerHTML = `
                    <h3 @click="open = !open" class="mt-6 text-xl font-semibold cursor-pointer" style="color: {{ $statusColors[$group] ?? '#333' }};">
                        {{ $group }}
                        <svg class="w-4 h-4 ml-1 inline-block transform transition-transform"
                             :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7" />
                        </svg>
                    </h3>
                    <div x-show="open" class="mt-4 lista-placeholder" data-ids='{{ $jsonIds }}'></div>
                `;
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

            // renderiza as tabelas depois que os blocos estão prontos
            setTimeout(() => {
                document.querySelectorAll('.lista-placeholder').forEach(div => {
                    const ids = JSON.parse(div.getAttribute('data-ids') || '[]');
                    const itens = ids.map(i => messages[i]).filter(Boolean);
                    div.innerHTML = renderTable(itens);
                });

                const allPlaceholder = document.querySelector('[data-all="true"]');
                if (allPlaceholder) {
                    allPlaceholder.innerHTML = renderTable(Object.values(messages));
                }
            }, 200);
        });
    </script>

</body>

</html>
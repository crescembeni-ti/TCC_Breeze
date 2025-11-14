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
</head>

<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">

    <!-- HEADER -->
    <header class="site-header flex items-center justify-between px-8 py-4 shadow-md bg-white">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-20 w-20 object-contain">
            <h1 class="text-4xl font-bold">
                <span class="text-[#358054]">Árvores de</span>
                <span class="text-[#a0c520]"> Paracambi</span>
            </h1>
        </div>
    </header>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-10">
        <div class="bg-white shadow-sm rounded-lg p-8">

            <!-- Título + Voltar -->
            <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
                <h2 class="text-3xl font-bold text-[#358054]">Mensagens de Contato 📬</h2>

                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#358054] text-white rounded-lg text-sm font-semibold hover:bg-[#2d6947] transition">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Voltar ao Painel
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md shadow-sm">
                    {{ session('success') }}
                </div>
            @endif


            <!-- Tabela -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">De</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Solicitação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Data</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">

                        @foreach ($messages as $message)
                        <tr>
                            <!-- Remetente -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $message->user->name ?? $message->nome_solicitante }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $message->user->email ?? $message->email_solicitante }}
                                </div>
                            </td>

                            <!-- Solicitação -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $message->bairro }}, {{ $message->rua }}, {{ $message->numero }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ Str::limit($message->descricao, 100) }}
                                </div>
                            </td>

                            <!-- Data -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </td>

                            <!-- Ações -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">

                                <!-- Ver Mensagem -->
                                <button 
                                    onclick="openViewModal({{ $message->id }})"
                                    class="inline-flex items-center px-3 py-1.5 bg-[#358054] text-white rounded-md text-xs font-semibold hover:bg-[#2d6947] transition">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                    Ver
                                </button>

                                <!-- Editar Status -->
                                <button 
                                    onclick="openStatusModal({{ $message->id }})"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs font-semibold hover:bg-blue-700 transition">
                                    <i data-lucide="pencil" class="w-4 h-4 mr-1"></i>
                                    Atualizar
                                </button>

                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-gray-800 text-gray-300 text-center py-4 text-sm border-t border-[#358054] mt-auto">
        © {{ date('Y') }} - Árvores de Paracambi
    </footer>


    <!-- MODAL VISUALIZAR -->
    <div id="modal-view" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative">
            <button onclick="closeViewModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>

            <h2 class="text-2xl font-bold text-[#358054] mb-4">📬 Detalhes da Mensagem</h2>

            <div class="space-y-3">
                <p><strong>Nome:</strong> <span id="view-nome"></span></p>
                <p><strong>Email:</strong> <span id="view-email"></span></p>
                <p><strong>Endereço:</strong> <span id="view-endereco"></span></p>

                <div>
                    <p class="font-semibold">Mensagem:</p>
                    <p id="view-descricao" class="p-3 bg-gray-100 rounded-md text-sm"></p>
                </div>
            </div>

            <div class="mt-6 text-right">
                <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>


    <!-- MODAL STATUS -->
    <div id="modal-status" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6 relative">
            <button onclick="closeStatusModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>

            <h2 class="text-2xl font-bold text-blue-700 mb-4">✏️ Atualizar Status</h2>

            <form id="status-form" method="POST" class="space-y-3">
                @csrf
                @method('PATCH')

                <label class="font-semibold">Status</label>
                <select name="status_id" id="status-select" class="w-full rounded-md border-gray-300 shadow-sm">
                    @foreach ($allStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>

                <label class="font-semibold">Justificativa</label>
                <textarea name="justificativa" id="status-justificativa"
                          class="w-full rounded-md border-gray-300 shadow-sm" rows="3"></textarea>

                <button class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    Salvar
                </button>
            </form>
        </div>
    </div>


    <!-- SCRIPT -->
    <script>
        const messages = @json($messages->keyBy('id'));

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


        // STATUS MODAL
        function openStatusModal(id) {
            let m = messages[id];

            document.getElementById('status-form').action =
                `/admin/contacts/${id}/update-status`;

            document.getElementById('status-select').value = m.status_id;
            document.getElementById('status-justificativa').value = m.justificativa ?? "";

            document.getElementById('modal-status').classList.remove('hidden');
            document.getElementById('modal-status').classList.add('flex');
        }

        function closeStatusModal() {
            document.getElementById('modal-status').classList.add('hidden');
            document.getElementById('modal-status').classList.remove('flex');
        }

        lucide.createIcons();
    </script>

</body>
</html>

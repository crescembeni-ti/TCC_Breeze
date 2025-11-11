<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Texto já estava em Português --}}
            {{ __('Mensagens de Contato') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-semibold mb-4">Caixa de Entrada</h3>
<div class="mb-6">
    <a href="{{ route('dashboard') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
        <!-- Ícone Heroicon: seta para a esquerda -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Voltar para o Painel
    </a>
</div>


                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    {{-- Textos já estavam em Português --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">De</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitação</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($messages as $message)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{-- Usando a relação 'user' que carregamos no controller --}}
                                            <div class="text-sm font-medium text-gray-900">{{ $message->user->name ?? $message->nome_solicitante }}</div>
                                            <div class="text-sm text-gray-500">{{ $message->user->email ?? $message->email_solicitante }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{-- Mostra o endereço e a descrição --}}
                                            <div class="text-sm font-medium text-gray-900">{{ $message->bairro }}, {{ $message->rua }}, {{ $message->numero }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($message->descricao, 100) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $message->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            
                                            {{-- === FORMULÁRIO DE ATUALIZAÇÃO ADAPTADO === --}}
                                            <form action="{{ route('admin.contacts.updateStatus', $message) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <div>
                                                    @php
                                                        // Define a cor baseada no nome do status
                                                        $statusName = $message->status->name ?? 'Indefinido';
                                                        $colorClass = match($statusName) {
                                                            'Em Análise' => 'bg-yellow-100 text-yellow-800',
                                                            'Deferido' => 'bg-blue-100 text-blue-800',
                                                            'Concluído' => 'bg-green-100 text-green-800',
                                                            'Indeferido' => 'bg-red-100 text-red-800',
                                                            default => 'bg-gray-100 text-gray-800', // Para 'Enviado' (se existir) ou Nulo
                                                        };
                                                    @endphp

                                                    <label for="status-{{ $message->id }}" class="sr-only">Status</label>
                                                    <select 
                                                        name="status_id" {{-- Nome corrigido --}}
                                                        id="status-{{ $message->id }}"
                                                        class="rounded-md border-gray-300 shadow-sm text-sm {{ $colorClass }}"
                                                    >
                                                        {{-- Loop dinâmico com os status do BD --}}
                                                        @foreach ($allStatuses as $status)
                                                            <option value="{{ $status->id }}" @if($message->status_id == $status->id) selected @endif>
                                                                {{ $status->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mt-2">
                                                    <label for="justificativa-{{ $message->id }}" class="sr-only">Justificativa</label>
                                                    <textarea 
                                                        name="justificativa" 
                                                        id="justificativa-{{ $message->id }}" 
                                                        rows="2" 
                                                        class="block w-full rounded-md shadow-sm border-gray-300 text-sm"
                                                        placeholder="Justificativa (se indeferido)"
                                                    >{{ old('justificativa', $message->justificativa) }}</textarea>
                                                    {{-- Mostra erro de validação (ex: justificativa obrigatória) --}}
                                                    @error('justificativa')
                                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <button type="submit" class="mt-2 px-3 py-1 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700">
                                                    Atualizar
                                                </button>
                                            </form>
                                            {{-- === FIM DO FORMULÁRIO === --}}

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            {{-- Texto já estava em Português --}}
                                            Nenhuma mensagem recebida.
                                        </td>
                                    </tr>
                                @endForelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mensagens de Contato') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-semibold mb-4">Caixa de Entrada</h3>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">De</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagem</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($messages as $message)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $message->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $message->email }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ Str::limit($message->message, 100) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $message->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <form action="{{ route('admin.contacts.updateStatus', $message) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" 
                                                        class="rounded-md border-gray-300 shadow-sm text-sm
                                                        @if($message->status == 'novo') bg-red-100 text-red-800
                                                        @elseif($message->status == 'visto') bg-yellow-100 text-yellow-800
                                                        @elseif($message->status == 'resolvendo') bg-blue-100 text-blue-800
                                                        @else bg-green-100 text-green-800 @endif
                                                        "
                                                        onchange="this.form.submit()"> <option value="novo" {{ $message->status == 'novo' ? 'selected' : '' }}>Novo</option>
                                                    <option value="visto" {{ $message->status == 'visto' ? 'selected' : '' }}>Visto</to>
                                                    <option value="resolvendo" {{ $message->status == 'resolvendo' ? 'selected' : '' }}>Resolvendo</option>
                                                    <option value="resolvido" {{ $message->status == 'resolvido' ? 'selected' : '' }}>Resolvido</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Nenhuma mensagem recebida.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
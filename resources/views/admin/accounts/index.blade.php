@extends('layouts.dashboard')

@section('title', 'Gerenciar Contas')

@section('content')

<div class="bg-white shadow-sm rounded-lg p-6">

    {{-- TABS --}}
    <div class="flex space-x-4 border-b mb-4 pb-2">

        <a href="?type=admin"
           class="px-4 py-2 rounded 
           {{ $type === 'admin' ? 'bg-[#358054] text-white' : 'bg-gray-200 text-gray-700' }}">
            Admins
        </a>

        <a href="?type=analyst"
           class="px-4 py-2 rounded
           {{ $type === 'analyst' ? 'bg-[#358054] text-white' : 'bg-gray-200 text-gray-700' }}">
            Analistas
        </a>

        <a href="?type=service"
           class="px-4 py-2 rounded
           {{ $type === 'service' ? 'bg-[#358054] text-white' : 'bg-gray-200 text-gray-700' }}">
            Serviços
        </a>
    </div>

    {{-- BOTÃO ADICIONAR --}}
    <button 
        onclick="openAddModal('{{ $type }}')"
        class="bg-[#358054] text-white px-4 py-2 rounded-lg shadow mb-4">
        + Criar {{ ucfirst($type) }}
    </button>

    {{-- TABELA --}}
    <table class="min-w-full bg-white border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Nome</th>
                <th class="px-4 py-2">Email</th>

                @if ($type !== 'admin')
                    <th class="px-4 py-2">CPF</th>
                @endif

                <th class="px-4 py-2 text-right">Ações</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($data as $item)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $item->id }}</td>
                    <td class="px-4 py-2">{{ $item->name }}</td>
                    <td class="px-4 py-2">{{ $item->email }}</td>

                    @if ($type !== 'admin')
                        <td class="px-4 py-2">{{ $item->cpf }}</td>
                    @endif

                    <td class="px-4 py-2 text-right space-x-2">

                        {{-- EDITAR --}}
                        <button 
                            onclick="openEdit({{ $item->id }}, '{{ $item->name }}', '{{ $item->email }}', '{{ $item->cpf ?? '' }}', '{{ $type }}')"
                            class="px-3 py-1 bg-blue-500 text-white text-xs rounded">
                            Editar
                        </button>

                        {{-- EXCLUIR --}}
                        <form method="POST" 
                              action="{{ route('admin.accounts.destroy', [$type, $item->id]) }}"
                              class="inline-block"
                              onsubmit="return confirm('Excluir esta conta?')">

                            @csrf
                            @method('DELETE')

                            <button class="px-3 py-1 bg-red-500 text-white text-xs rounded">
                                Excluir
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $data->links() }}
    </div>

</div>

{{-- MODAL ADD --}}
<dialog id="modalAdd" class="p-4 rounded-lg shadow-lg">
    <form method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-4">
        @csrf

        <input id="add_type" type="hidden" name="type" value="">

        <h3 class="text-lg font-bold">Criar {{ ucfirst($type) }}</h3>

        <input name="name" class="w-full border rounded p-2" placeholder="Nome" required>
        @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

        <input name="email" class="w-full border rounded p-2" placeholder="Email" required>
        @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

        <div id="cpf_add_container"></div>

        <input name="password" type="password" class="w-full border rounded p-2" placeholder="Senha" required>
        @error('password') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

        <input name="password_confirmation" type="password" class="w-full border rounded p-2" placeholder="Confirmar Senha" required>
        @error('password_confirmation') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

        <button class="bg-[#358054] text-white px-4 py-2 rounded">Salvar</button>
    </form>

    <button onclick="modalAdd.close()" class="mt-2 text-gray-600">Cancelar</button>
</dialog>


{{-- MODAL EDIT --}}
<dialog id="modalEdit" class="p-4 rounded-lg shadow-lg">
    <form id="formEdit" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <input id="edit_type" type="hidden" name="type" value="">

        <h3 class="text-lg font-bold">Editar</h3>

        <input id="edit_name" name="name" class="w-full border rounded p-2">
        <input id="edit_email" name="email" class="w-full border rounded p-2">

        <div id="cpf_container"></div>

        <input name="password" type="password" class="w-full border rounded p-2" placeholder="Nova Senha (opcional)">
        <input name="password_confirmation" type="password" class="w-full border rounded p-2" placeholder="Confirmar Nova Senha">

        <button class="bg-[#358054] text-white px-4 py-2 rounded">Atualizar</button>
    </form>

    <button onclick="modalEdit.close()" class="mt-2 text-gray-600">Cancelar</button>
</dialog>


{{-- REABRIR MODAL SE HOUVER ERROS --}}
@if ($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        modalAdd.showModal();
    });
</script>
@endif


<script>
/* MODAL ADD */
function openAddModal(type) {
    document.getElementById('add_type').value = type;

    const cpfContainer = document.getElementById('cpf_add_container');
    cpfContainer.innerHTML = (type !== 'admin')
        ? '<input name="cpf" class="w-full border rounded p-2" placeholder="CPF" required>'
        : '';

    modalAdd.showModal();
}

/* MODAL EDIT */
function openEdit(id, name, email, cpf = '', type) {
    modalEdit.showModal();

    document.getElementById('edit_type').value = type;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;

    const cpfDiv = document.getElementById('cpf_container');
    cpfDiv.innerHTML = (type !== 'admin')
        ? `<input id="edit_cpf" name="cpf" class="w-full border rounded p-2" value="${cpf}">`
        : '';

    document.getElementById('formEdit').action =
        "/pbi-admin/accounts/update/" + type + "/" + id;
}
</script>

@endsection

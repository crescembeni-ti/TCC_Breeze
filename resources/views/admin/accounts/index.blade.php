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
    class="
    bg-green-700 text-white font-semibold
    rounded-md shadow-md
    hover:bg-green-600 hover:shadow-lg
    active:bg-[#38c224]
    transition duration-200
    px-4 py-2 inline-block
    mb-4
    ">
    + Criar {{ ucfirst($type) }}
    </button>

    {{-- TABELA --}}
    <table class="min-w-full bg-white border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left">ID</th>
                <th class="px-4 py-2 text-left">Nome</th>
                <th class="px-4 py-2 text-left">Email</th>
                @if ($type !== 'admin')
                    <th class="px-4 py-2 text-left">CPF</th>
                @endif
                <th class="px-4 py-2 text-right">Ações</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($data as $item)
                <tr class="border-t">
                    <td class="px-4 py-2 text-left">{{ $item->id }}</td>
                    <td class="px-4 py-2 text-left">{{ $item->name }}</td>
                    <td class="px-4 py-2 text-left">{{ $item->email }}</td>

                    @if ($type !== 'admin')
                        <td class="px-4 py-2 text-left" id="cpf-{{ $item->id }}">{{ $item->cpf }}</td>
                    @endif

                    <td class="px-4 py-2 text-right space-x-2">

                        {{-- EDITAR --}}
                        <button 
                            onclick="openEdit({{ $item->id }}, '{{ $item->name }}', '{{ $item->email }}', '{{ $item->cpf ?? '' }}', '{{ $type }}')"
                            class="bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 hover:shadow-lg active:bg-[#38c224] transition duration-200 px-3 py-1 text-xs">
                            Editar
                        </button>

                        {{-- EXCLUIR --}}
<button onclick="openDeleteModal({{ $item->id }}, '{{ $item->name }}')"
        data-id="{{ $item->id }}" 
        data-name="{{ $item->name }}"
        data-url="{{ route('admin.accounts.destroy', [$type, $item->id]) }}"
        class="bg-red-600 text-white font-semibold rounded-md shadow-md hover:bg-red-700 hover:shadow-lg active:bg-red-500 transition duration-200 px-3 py-1 text-xs">
    Excluir
</button>
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
<dialog id="modalAdd" class="p-6 rounded-lg shadow-lg w-[420px] max-w-full">
    <button onclick="closeModal('modalAdd')" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>
    <form method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-4">
        @csrf
        <input id="add_type" type="hidden" name="type" value="">

        <h3 class="text-lg font-bold">Criar {{ ucfirst($type) }}</h3>

        <input name="name"
               placeholder="Nome"
               required
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        <input name="email"
               placeholder="Email"
               required
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        <div id="cpf_add_container"></div>

        <input name="password" type="password"
               placeholder="Senha" required
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        <input name="password_confirmation" type="password"
               placeholder="Confirmar Senha" required
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        {{-- Botão SALVAR --}}
        <button
            class="bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 hover:shadow-lg active:bg-[#38c224] transition duration-200 w-full px-4 py-2">
            Salvar
        </button>
    </form>
</dialog>

{{-- MODAL EDIT --}}
<dialog id="modalEdit" class="p-6 rounded-lg shadow-lg w-[420px] max-w-full">
    <button onclick="closeModal('modalEdit')" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>
    <form id="formEdit" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <input id="edit_type" type="hidden" name="type" value="">

        <h3 class="text-lg font-bold">Editar</h3>

        <input id="edit_name" name="name"
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        <input id="edit_email" name="email"
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        <div id="cpf_container"></div>

        <input name="password" type="password"
               placeholder="Nova Senha (opcional)"
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        <input name="password_confirmation" type="password"
               placeholder="Confirmar Nova Senha"
               class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                      text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2">

        {{-- Botão ATUALIZAR --}}
        <button
            class="bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 hover:shadow-lg active:bg-[#38c224] transition duration-200 w-full px-4 py-2">
            Atualizar
        </button>
    </form>
</dialog>

{{-- MODAL EXCLUSÃO --}}
<dialog id="modalDelete" class="p-6 rounded-lg shadow-lg w-[420px] max-w-full">
    <button onclick="closeModal('modalDelete')" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>

    <h3 class="text-lg font-bold text-red-600 text-center">Tem certeza que deseja excluir?</h3>
    
    <div id="modalDeleteName" class="bg-gray-100 text-gray-800 font-bold px-4 py-2 rounded-md my-4 w-fit mx-auto"></div>

    <p class="text-gray-700 text-center">Esta ação é irreversível.</p>

    <form id="formDelete" method="POST" action="" class="mt-4">
        @csrf
        @method('DELETE')

        <div class="flex justify-center space-x-4">
            <button type="button" onclick="closeModal('modalDelete')" class="bg-gray-300 text-black font-semibold rounded-md px-4 py-2">
                Cancelar
            </button>

            <button type="submit" class="bg-red-600 text-white font-semibold rounded-md shadow-md hover:bg-red-700 hover:shadow-lg active:bg-red-500 transition duration-200 px-4 py-2">
                Excluir
            </button>
        </div>
    </form>
</dialog>

<script>
/* MODAL ADD */
function openAddModal(type) {
    document.getElementById('add_type').value = type;

    const cpfContainer = document.getElementById('cpf_add_container');
    cpfContainer.innerHTML = (type !== 'admin')
        ? '<input name="cpf" class="block w-full rounded-md border border-gray-300 bg-[#f9fafb] text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2" placeholder="CPF" required>'
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
        ? `<input id="edit_cpf" name="cpf"
                  class="block w-full rounded-md border border-gray-300 bg-[#f9fafb]
                         text-[#358054] shadow-sm focus:ring-green-500 focus:border-green-500 px-4 py-2"
                  value="${cpf}">`
        : '';

    document.getElementById('formEdit').action =
        "/pbi-admin/accounts/update/" + type + "/" + id;
}

function closeModal(modalId) {
    document.getElementById(modalId).close();
}

function formatCPF(cpf) {
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('td[id^="cpf-"]').forEach(function(cpfElement) {
        let cpf = cpfElement.innerText.trim();
        cpfElement.innerText = formatCPF(cpf);
    });
});

function openDeleteModal(id, name) {
    const modalDelete = document.getElementById('modalDelete');
    const formDelete = document.getElementById('formDelete');
    const deleteButton = document.querySelector(`button[data-id='${id}']`);
    const url = deleteButton.getAttribute('data-url');

    formDelete.action = url;

    const modalName = document.getElementById('modalDeleteName');
    modalName.innerText = name;

    modalDelete.showModal();
}

/* VALIDAÇÃO DE SENHAS */

// Modal ADD
document.querySelector('#modalAdd form').addEventListener('submit', function(e) {
    let senha = this.querySelector('input[name="password"]').value;
    let confirmar = this.querySelector('input[name="password_confirmation"]').value;

    if (senha !== confirmar) {
        e.preventDefault();
        alert('As senhas não coincidem.');
    }
});

// Modal EDIT
document.querySelector('#formEdit').addEventListener('submit', function(e) {
    let senha = this.querySelector('input[name="password"]').value;
    let confirmar = this.querySelector('input[name="password_confirmation"]').value;

    if (senha !== '' && senha !== confirmar) {
        e.preventDefault();
        alert('A confirmação da senha não coincide.');
    }
});
</script>

@endsection

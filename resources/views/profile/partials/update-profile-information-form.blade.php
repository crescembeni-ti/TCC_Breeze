@php
    // Detecta se é admin
    $isAdmin = auth('admin')->check();
    $guard = $isAdmin ? 'admin' : 'web';
    $user = $isAdmin ? auth('admin')->user() : auth()->user();

    // Define as rotas dinamicamente
    $updateRoute = $isAdmin ? route('admin.profile.update') : route('profile.update');
@endphp

<form method="POST" action="{{ $updateRoute }}">
    @csrf
    @method('PATCH')

    <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-2">Nome</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}"
               class="w-full border rounded-lg p-2 focus:ring-[#358054] focus:border-[#358054]">
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-2">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}"
               class="w-full border rounded-lg p-2 focus:ring-[#358054] focus:border-[#358054]">
    </div>

    <button type="submit"
            class="bg-[#358054] text-white px-6 py-2 rounded-lg shadow hover:bg-[#2f6f47] transition">
        Salvar Alterações
    </button>
</form>

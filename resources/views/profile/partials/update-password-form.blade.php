@php
    $isAdmin = auth('admin')->check();
    $updateRoute = $isAdmin ? route('admin.profile.update') : route('profile.update');
@endphp

<form method="POST" action="{{ $updateRoute }}">
    @csrf
    @method('PATCH')

    <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-2">Nova Senha</label>
        <input type="password" name="password"
               class="w-full border rounded-lg p-2 focus:ring-[#358054] focus:border-[#358054]">
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-2">Confirmar Senha</label>
        <input type="password" name="password_confirmation"
               class="w-full border rounded-lg p-2 focus:ring-[#358054] focus:border-[#358054]">
    </div>

    <button type="submit"
            class="bg-[#358054] text-white px-6 py-2 rounded-lg shadow hover:bg-[#2f6f47] transition">
        Atualizar Senha
    </button>
</form>

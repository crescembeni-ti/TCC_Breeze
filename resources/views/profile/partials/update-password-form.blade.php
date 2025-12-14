@php
    $isAdmin = auth('admin')->check();
    $updateRoute = $isAdmin ? route('admin.profile.update') : route('profile.update');
@endphp

<form method="POST" action="{{ $updateRoute }}">
    @csrf
    @method('PATCH')

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Nova Senha
        </label>
        <input type="password" name="password" required
            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Confirmar Senha
        </label>
        <input type="password" name="password_confirmation" required
            class="w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 bg-gray-50 text-[#358054] focus:ring-green-500 focus:border-green-500">
    </div>

    <button type="submit"
        class="bg-[#358054] text-white text-lg rounded-md shadow-md hover:bg-[#2f6f47] hover:shadow-lg active:bg-[#38c224] active:scale-95 transition px-6 py-3">
        Atualizar Senha
    </button>

</form>

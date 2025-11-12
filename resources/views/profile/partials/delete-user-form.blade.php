@php
    $isAdmin = auth('admin')->check();
    $destroyRoute = $isAdmin ? route('admin.profile.destroy') : route('profile.destroy');
@endphp

<form method="POST" action="{{ $destroyRoute }}">
    @csrf
    @method('DELETE')

    <p class="text-gray-700 mb-4">
        Tem certeza que deseja excluir permanentemente sua conta?
    </p>

    <button type="submit"
            class="bg-red-600 text-white px-6 py-2 rounded-lg shadow hover:bg-red-700 transition">
        Excluir Conta
    </button>
</form>

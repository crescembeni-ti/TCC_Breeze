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
    class="bg-red-600 text-white text-lg rounded-md shadow-md hover:bg-red-700 hover:shadow-lg active:bg-red-800 active:scale-95 transition px-6 py-3">
    Excluir Conta
</button>

</form>

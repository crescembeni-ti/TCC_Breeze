@php
    $isAdmin = auth('admin')->check();
    $updateRoute = $isAdmin
        ? route('admin.profile.password.update')
        : route('profile.password.update');
@endphp

<form method="POST" action="{{ $updateRoute }}">
    @csrf
    @method('PATCH')

    {{-- Senha atual --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Senha Atual
        </label>
        <input type="password" name="current_password" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
        @error('current_password')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Nova senha --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Nova Senha
        </label>
        <input type="password" name="password" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
        @error('password')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Confirmar senha --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Confirmar Senha
        </label>
        <input type="password" name="password_confirmation" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
    </div>

    <button type="submit"
        class="bg-[#358054] text-white rounded-md px-6 py-3">
        Atualizar Senha
    </button>

    <br>

    @if (session('success'))
    <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
        {{ session('success') }}
    </div>
@endif

</form>

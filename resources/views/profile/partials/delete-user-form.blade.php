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

    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Por favor, digite sua senha para confirmar:
        </label>
        
        <input 
            type="password" 
            name="password" 
            id="password"
            placeholder="Senha atual"
            class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 border"
            required
        >

        @error('password', 'userDeletion')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        
        @error('password')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div class="flex justify-end">
        <button type="submit"
            class="bg-red-600 text-white text-lg rounded-md shadow-md hover:bg-red-700 hover:shadow-lg active:bg-red-800 active:scale-95 transition px-6 py-3">
            Excluir Conta
        </button>
    </div>

</form>
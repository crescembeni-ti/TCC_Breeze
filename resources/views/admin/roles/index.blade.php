{{-- Este arquivo é /resources/views/admin/roles/index.blade.php --}}
@extends('admin.layout') {{-- Use o seu layout de admin --}}

@section('content')
    <h2>Gerenciamento de Cargos</h2>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Cargos Atuais</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        {{-- O Spatie nos dá essa coleção fácil de usar --}}
                        {{ $user->getRoleNames()->join(', ') }}
                    </td>
                    <td>
                        <a href="{{ route('admin.roles.edit', $user) }}">Editar Cargos</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    {{ $users->links() }} {{-- Para paginação --}}
@endsection
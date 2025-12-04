@extends('layouts.dashboard')

@section('title', 'Meu Perfil - Usuário')

@section('content')
<h2 class="text-3xl font-bold text-[#358054] mb-6">Meu Perfil</h2>

<div class="perfil-box mb-6">
    @include('profile.partials.update-password-form')
</div>

<div class="perfil-box">
    @include('profile.partials.delete-user-form')
</div>
@endsection

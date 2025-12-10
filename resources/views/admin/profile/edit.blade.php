@extends('layouts.dashboard')

@section('title', 'Meu Perfil')

@section('content')
    <div class="perfil-box inline-block">
    <h2 class="text-3xl font-bold text-[#358054] mb-0">Meu Perfil</h2>
</div>

    <div class="perfil-box mb-6">
        @include('profile.partials.update-password-form')
    </div>

    <div class="perfil-box">
        @include('profile.partials.delete-user-form')
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Painel de Serviço')

@section('content')
<div class="container mx-auto">
    {{-- Cartão de Boas-vindas --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-[#358054]">
        <h2 class="text-2xl font-bold text-[#358054] flex items-center gap-2">
            <i data-lucide="wrench" class="w-8 h-8"></i>
            Painel da Equipe de Serviço
        </h2>
        <p class="text-gray-600 mt-2">
            Bem-vindo(a)! 
            @if(isset($user)) 
                <strong>{{ $user->name }}</strong>
            @endif
        </p>
    </div>

    {{-- Cards de Estatísticas Rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded shadow hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-700 font-bold text-sm uppercase">Tarefas Pendentes</p>
                    {{-- VARIÁVEL DINÂMICA AQUI --}}
                    <h3 class="text-3xl font-bold text-gray-800">{{ $pendentes ?? 0 }}</h3> 
                </div>
                <i data-lucide="clock" class="w-10 h-10 text-orange-300"></i>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-700 font-bold text-sm uppercase">Em Andamento</p>
                    {{-- VARIÁVEL DINÂMICA AQUI --}}
                    <h3 class="text-3xl font-bold text-gray-800">{{ $emAndamento ?? 0 }}</h3> 
                </div>
                <i data-lucide="play-circle" class="w-10 h-10 text-blue-300"></i>
            </div>
        </div>

        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-700 font-bold text-sm uppercase">Concluídas</p>
                    {{-- VARIÁVEL DINÂMICA AQUI --}}
                    <h3 class="text-3xl font-bold text-gray-800">{{ $concluidas ?? 0 }}</h3> 
                </div>
                <i data-lucide="check-circle" class="w-10 h-10 text-green-300"></i>
            </div>
        </div>
    </div>

    {{-- Botões de Ação Rápida --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('service.tasks.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-green-50 hover:border-green-300 transition group">
            <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900 group-hover:text-[#358054] flex items-center gap-2">
                <i data-lucide="list"></i> Minhas Tarefas
            </h5>
            <p class="font-normal text-gray-700">Visualize a lista completa de ordens de serviço atribuídas a você.</p>
        </a>
        
        <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow opacity-75">
            <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                <i data-lucide="calendar"></i> Agenda (Em breve)
            </h5>
            <p class="font-normal text-gray-700">O calendário de serviços estará disponível em breve.</p>
        </div>
    </div>
</div>
@endsection

{{-- Scripts --}}
@push('scripts')
{{-- Mantive seu script, caso você adicione um carrossel futuramente, mas no momento não há elemento #carrossel no HTML acima --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll("#carrossel .slides img");
    if(slides.length > 0) {
        let current = 0;
        setInterval(() => {
            slides[current].classList.remove("opacity-100");
            slides[current].classList.add("opacity-0");
            current = (current + 1) % slides.length;
            slides[current].classList.remove("opacity-0");
            slides[current].classList.add("opacity-100");
        }, 5000);
    }
});
</script>
@endpush

{{-- Estilos --}}
@push('styles')
<style>
.card {
    background: #fdfdfd;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    transition: transform .2s ease, box-shadow .2s ease;
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.18);
}
</style>
@endpush
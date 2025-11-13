@extends('layouts.dashboard')

@section('title', 'Painel do Usuário')

@section('content')
<div class="bg-white overflow-hidden p-8 rounded-lg" style="box-shadow: 0 4px 12px rgba(56, 194, 36, 0.3);">
    <h2 class="text-3xl font-bold text-[#358054] mb-4">Bem-vindo, {{ $user->name }}</h2>
    <p class="text-gray-700 text-lg">
        Continue nos ajudando a cuidar das árvores da nossa cidade! <br>
        Cada ação, cada solicitação e cada cuidado faz a diferença para manter Paracambi mais verde, saudável e bonita. <br>
        Juntos, estamos construindo um futuro mais sustentável e cheio de vida.
    </p>

    {{-- Carrossel de imagens --}}
    <div class="relative w-full max-w-8x1 mx-auto rounded-xl overflow-hidden shadow-lg cursor-pointer mt-8" id="carrossel">
        <div class="slides relative w-full h-[600px]">
            <img src="{{ asset('images/fabrica.jpeg') }}" class="absolute inset-0 w-full h-full object-cover opacity-100 transition-opacity duration-1000" alt="imagem 1">
            <img src="{{ asset('images/arvore.jpeg') }}" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000" alt="imagem 2">
            <img src="{{ asset('images/fotofabrica.jpg') }}" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000" alt="imagem 3">
        </div>
    </div>

    {{-- Texto introdutório para as estatísticas --}}
    <div class="mt-10 text-center">
    <h3 class="text-2xl font-semibold text-[#358054] mb-3">Cuidar da natureza é cuidar da nossa cidade</h3>
    <p class="text-gray-700 text-lg leading-relaxed max-w-5xl mx-auto">
        Paracambi segue comprometida com a preservação e o monitoramento de suas árvores!<br>
        O mapeamento ambiental reflete o empenho da população e da gestão pública em proteger nosso patrimônio natural.<br>
        Abaixo, você confere alguns dados que representam esse compromisso com o meio ambiente.
    </p>
    </div>


    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="card text-center">
            <h3>Total de Árvores</h3>
            <p class="text-4xl font-bold text-green-700">{{ $stats['total_trees']}}</p>
        </div>

        <div class="card text-center">
            <h3>Atividades Registradas</h3>
            <p class="text-4xl font-bold text-blue-700">{{ $stats['total_activities']}}</p>
        </div>

        <div class="card text-center">
            <h3>Espécies no Mapa</h3>
            <p class="text-4xl font-bold text-purple-700">{{ $stats['total_species']}}</p>
        </div>
    </div>

    {{-- Conexão com os ODS --}}
    <div class="mt-12 text-center">
        <h3 class="text-2xl font-semibold text-[#358054] mb-3">Cuidar das árvores é cuidar do futuro</h3>
        <p class="text-gray-700 text-lg leading-relaxed max-w-3xl mx-auto">
        Ao ajudar a preservar as árvores de Paracambi, você também está contribuindo com os Objetivos de Desenvolvimento Sustentável <strong>(ODS)</strong> da ONU.<br>  
        Essas ações fortalecem metas globais essenciais para o planeta, como: 
        <br>
        </p>
    </div>

     {{-- Cards dos ODS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mt-10">
        <!-- ODS 3 - Saúde e Bem-Estar -->
        <div class="card bg-gray-50 shadow-lg rounded-lg text-center p-6">
            <img src="{{ asset('images/ods3.jpg') }}" alt="ODS 3" class="w-48 h-48 object-cover rounded-lg mx-auto mb-4">
            <h4 class="text-xl font-semibold text-[#358054]">ODS 3 - Saúde e Bem-Estar</h4>
            <p class="text-gray-700">Garantir uma vida saudável e promover o bem-estar para todos, em todas as idades.</p>
        </div>

        <!-- ODS 11 - Cidades e Comunidades Sustentáveis -->
        <div class="card bg-gray-50 shadow-lg rounded-lg text-center p-6">
            <img src="{{ asset('images/ods11.jpg') }}" alt="ODS 11" class="w-48 h-48 object-cover rounded-lg mx-auto mb-4">
            <h4 class="text-xl font-semibold text-[#358054]">ODS 11 - Cidades Sustentáveis</h4>
            <p class="text-gray-700">Tornar as cidades e os assentamentos humanos inclusivos, seguros, resilientes e sustentáveis.</p>
        </div>

        <!-- ODS 13 - Ação contra a Mudança Global do Clima -->
        <div class="card bg-gray-50 shadow-lg rounded-lg text-center p-6">
            <img src="{{ asset('images/ods13.jpg') }}" alt="ODS 13" class="w-48 h-48 object-cover rounded-lg mx-auto mb-4">
            <h4 class="text-xl font-semibold text-[#358054]">ODS 13 - Ação contra a Mudança Climática</h4>
            <p class="text-gray-700">Melhorar a educação e a conscientização sobre as mudanças climáticas e suas consequências.</p>
        </div>

        <!-- ODS 15 - Vida Terrestre -->
        <div class="card bg-gray-50 shadow-lg rounded-lg text-center p-6">
            <img src="{{ asset('images/ods15.jpg') }}" alt="ODS 15" class="w-48 h-48 object-cover rounded-lg mx-auto mb-4">
            <h4 class="text-xl font-semibold text-[#358054]">ODS 15 - Vida Terrestre</h4>
            <p class="text-gray-700">Proteger, restaurar e promover o uso sustentável dos ecossistemas terrestres.</p>
        </div>

        <!-- ODS 17 - Parcerias e Meios de Implementação -->
        <div class="card bg-gray-50 shadow-lg rounded-lg text-center p-6">
            <img src="{{ asset('images/ods17.jpg') }}" alt="ODS 17" class="w-48 h-48 object-cover rounded-lg mx-auto mb-4">
            <h4 class="text-xl font-semibold text-[#358054]">ODS 17 - Parcerias e Meios de Implementação</h4>
            <p class="text-gray-700">Fortalecer os meios de implementação e revitalizar a parceria global para o desenvolvimento sustentável.</p>
        </div>
    </div>

</div>
@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll("#carrossel .slides img");
    let current = 0;

    // Troca automática a cada 5 segundos
    setInterval(() => {
        slides[current].classList.remove("opacity-100");
        slides[current].classList.add("opacity-0");

        current = (current + 1) % slides.length;

        slides[current].classList.remove("opacity-0");
        slides[current].classList.add("opacity-100");
    }, 5000);

    // Troca manual ao clicar na imagem
    const carrossel = document.getElementById("carrossel");
    carrossel.addEventListener("click", () => {
        slides[current].classList.remove("opacity-100");
        slides[current].classList.add("opacity-0");

        current = (current + 1) % slides.length;

        slides[current].classList.remove("opacity-0");
        slides[current].classList.add("opacity-100");
    });
});
</script>
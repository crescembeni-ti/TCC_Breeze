<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre - Árvores de Paracambi</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- CSS específico da página -->
    @vite('resources/css/about.css')

     <!-- Ícone do site -->
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>
<body class="font-sans antialiased bg-gray-100"> 
    <div class="min-h-screen">
        
        <header class="site-header bg-green-700 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-12">
                         <a href="{{ route('home') }}" class="flex items-center gap-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Árvores de Paracambi" class="h-24 w-24 object-contain">
                            <h1 class="text-5xl font-bold">
                            <span class="text-[#358054]">Sobre o</span>
                            <span class="text-[#a0c520]">Projeto</span>
                            </h1>
                    </div>       
                    <div class="flex gap-4">
                        <!-- ▼▼▼ BOTÕES ATUALIZADOS ▼▼▼ -->
                        <a href="{{ route('home') }}" class="btn bg-white text-green-700 hover:bg-gray-100">Voltar ao Mapa</a>
                        <a href="{{ route('contact') }}" class="btn bg-white text-blue-700 hover:bg-gray-100">Fazer solicitação</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-lg p-8 info-column"> 
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Árvores de Paracambi</h2>
                
                <div class="prose max-w-none">
                    <p>
                        Árvores de Paracambi é uma iniciativa dedicada ao mapeamento e preservação do patrimônio arbóreo da cidade de Paracambi, localizada no estado do Rio de Janeiro. Este projeto tem como objetivo principal criar um inventário completo das árvores urbanas do município, permitindo que cidadãos, gestores públicos e pesquisadores acompanhem a saúde e o desenvolvimento da floresta urbana local.
                    </p>

                    <h3>Nossa Missão</h3>
                    <p>
                        Nossa missão é promover a conscientização ambiental e facilitar a gestão sustentável das Árvores Urbanas de Paracambi. Através deste mapa interativo, buscamos engajar a comunidade local no cuidado e na preservação das árvores, reconhecendo sua importância fundamental para a qualidade de vida, o equilíbrio ecológico e o bem-estar da população.
                    </p>

                    <h3>Como Funciona</h3>
                    <p>
                        O sistema permite que usuários cadastrados registrem árvores encontradas pela cidade, incluindo informações detalhadas como:
                    </p>
                    <ul>
                        <li>Localização geográfica precisa (latitude e longitude)</li>
                        <li>Espécie da árvore (nome comum e científico)</li>
                        <li>Diâmetro do tronco e estado de saúde</li>
                        <li>Histórico de atividades de manutenção</li>
                        <li>Fotografias das árvores</li>
                    </ul>

                    <h3>Benefícios das Árvores Urbanas</h3>
                    <p>
                        As árvores urbanas desempenham um papel crucial no ambiente urbano, proporcionando diversos benefícios:
                    </p>
                    <ul>
                        <li><strong>Qualidade do Ar:</strong> Filtram poluentes e produzem oxigênio</li>
                        <li><strong>Conforto Térmico:</strong> Reduzem a temperatura ambiente através da sombra e evapotranspiração</li>
                        <li><strong>Gestão de Águas Pluviais:</strong> Interceptam a água da chuva, reduzindo o escoamento superficial</li>
                        <li><strong>Biodiversidade:</strong> Fornecem habitat para diversas espécies de fauna</li>
                        <li><strong>Bem-estar Social:</strong> Melhoram a estética urbana e proporcionam espaços de convivência</li>
                    </ul>

                    <h3>Participe</h3>
                    <p>
                        Convidamos todos os moradores de Paracambi a participarem deste projeto. Cadastre-se no sistema, registre as árvores do seu bairro, acompanhe as atividades de manutenção e contribua para a preservação do nosso patrimônio verde. Juntos, podemos construir uma cidade mais verde, saudável e sustentável para as futuras gerações.
                    </p>

                    <div class="bg-green-50 border-l-4 border-green-500 p-6 mt-8">
                        <p class="text-green-800 font-semibold">
                            Para mais informações ou para reportar problemas, entre em contato conosco através da <a href="{{ route('contact') }}" class="underline hover:text-green-900">página de solicitações</a>.
                        </p>
                    </div>
                </div>
            </div>
        </main>
        
        <footer class="bg-gray-800 shadow mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-300">© {{ date('Y') }} Mapa de Árvores de Paracambi-RJ.</p>
            </div>
        </footer>
    </div>
</body>
</html>

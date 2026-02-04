<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Você está offline - TCC Breeze</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0f172a; color: white; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    <div class="text-center max-w-md">
        <div class="mb-6 flex justify-center">
            <svg class="w-24 h-24 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-4">Ops! Sem conexão.</h1>
        <p class="text-slate-400 mb-8">
            Parece que você está sem internet no momento. Algumas funcionalidades podem estar indisponíveis, mas você ainda pode navegar pelo que foi salvo.
        </p>
        <button onclick="window.location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
            Tentar novamente
        </button>
    </div>
</body>
</html>

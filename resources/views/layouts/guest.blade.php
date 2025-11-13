<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Árvores de Paracambi - Login</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Ícone do site -->
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    </head>

    <body class="font-sans antialiased login-bg" style="background-image: url('{{ asset('images/bosque.jpeg') }}'); background-size:cover;">

    <div class="min-h-screen flex flex-col items-center justify-center">

        {{-- Card principal --}}
        <div class="w-full sm:max-w-md px-8 py-6 bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl animate-fadeInUp">
            {{ $slot }}
        </div>

    </div>
</body>
</html>
</html>

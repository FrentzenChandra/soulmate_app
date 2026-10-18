<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Antrian Servis' }} - Soulmate Komputer</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full w-full overflow-hidden select-none selection:bg-purple-500 selection:text-white">
    {{ $slot }}
    @livewireScripts
</body>
</html>

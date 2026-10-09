<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0E1726]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Login' }} - Soulmate Komputer POS & Servis</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-brand-gradient {
            background: linear-gradient(135deg, #5E17EB 0%, #E6007A 100%);
        }
        .text-brand-purple {
            color: #5E17EB;
        }
        .btn-brand {
            background: linear-gradient(135deg, #5E17EB 0%, #7C3AED 100%);
            transition: all 0.2s ease-in-out;
        }
        .btn-brand:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px -4px rgba(94, 23, 235, 0.45);
        }
    </style>
</head>
<body class="h-full text-[#1A1D20] antialiased flex flex-col justify-center items-center min-h-screen p-4 sm:p-6 lg:p-8 bg-[#0B111E] selection:bg-[#5E17EB] selection:text-white">

    <main class="w-full max-w-5xl my-auto">
        {{ $slot }}
    </main>

    @if (session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info') || session()->has('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if (session()->has('success'))
                    if (window.swalToast) window.swalToast('success', 'Berhasil', {!! json_encode(session('success')) !!});
                @endif
                @if (session()->has('error'))
                    if (window.swalToast) window.swalToast('error', 'Pemberitahuan', {!! json_encode(session('error')) !!});
                @endif
                @if (session()->has('warning'))
                    if (window.swalToast) window.swalToast('warning', 'Peringatan', {!! json_encode(session('warning')) !!});
                @endif
                @if (session()->has('info'))
                    if (window.swalToast) window.swalToast('info', 'Info', {!! json_encode(session('info')) !!});
                @endif
                @if (session()->has('status'))
                    if (window.swalToast) window.swalToast('info', 'Status', {!! json_encode(session('status')) !!});
                @endif
            });
        </script>
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>

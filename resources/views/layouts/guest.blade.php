<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8F9FA]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Soulmate Komputer') }}</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href=https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap rel=stylesheet>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>
    </head>
    <body class="h-full font-sans antialiased text-[#1A1D20] bg-[#F8F9FA]">
        {{ $slot }}

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

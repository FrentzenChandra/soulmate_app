<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Soulmate Komputer' }} - Device Care People First</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_bulat.png') }}">
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
<body class="bg-[#F8F9FA] text-gray-700 antialiased font-sans flex flex-col min-h-screen selection:bg-[#5E17EB] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          cartCount: {{ array_sum(array_column(session('soulmate_cart', []), 'qty')) }}
      }"
      @cart-updated.window="cartCount = $event.detail.count">

    <!-- 1. STICKY MAIN HEADER NAVIGATION -->
    <header class="w-full border-b border-gray-100 bg-white/95 backdrop-blur-md sticky top-0 z-50 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">

            <!-- Left: Logo + Navigation Menu -->
            <div class="flex items-center gap-6 xl:gap-10">
                <!-- Logo Section -->
                <a href="{{ route('home') }}" class="flex items-center shrink-0 group" title="Soulmate Komputer">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Soulmate Komputer"
                         class="h-10 sm:h-12 w-auto object-contain group-hover:scale-105 transition-transform">
                    <span class="sr-only">SOULMATE KOMPUTER - Device Care People First</span>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden lg:flex items-center gap-1 xl:gap-2">
                    <!-- 1. Beranda -->
                    <a href="{{ route('home') }}"
                       class="px-5 py-2 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('home') || request()->routeIs('beranda') ? 'bg-[#5E17EB] text-white shadow-sm shadow-purple-500/25' : 'text-[#0c1330] hover:text-[#5E17EB] hover:bg-purple-50/60' }}">
                        Beranda
                    </a>

                    <!-- 2. Produk -->
                    <a href="{{ route('web.produk') }}"
                       class="px-5 py-2 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('web.produk*') ? 'bg-[#5E17EB] text-white shadow-sm shadow-purple-500/25' : 'text-[#0c1330] hover:text-[#5E17EB] hover:bg-purple-50/60' }}">
                        Produk
                    </a>

                    <!-- 3. Servis -->
                    <a href="{{ route('web.servis') }}"
                       class="px-5 py-2 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('web.servis*') ? 'bg-[#5E17EB] text-white shadow-sm shadow-purple-500/25' : 'text-[#0c1330] hover:text-[#5E17EB] hover:bg-purple-50/60' }}">
                        Servis
                    </a>
                </nav>
            </div>

            <!-- Right Action: WhatsApp Chat Sekarang & Cart Icon -->
            <div class="flex items-center gap-4 sm:gap-5">
                <!-- WhatsApp Contact Block -->
                <a href="https://wa.me/6281995466255" target="_blank" rel="noopener noreferrer"
                   class="hidden sm:flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-xs group-hover:bg-[#20bd5a] group-hover:scale-105 transition-all shrink-0">
                        <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-xs sm:text-sm font-extrabold text-[#0c1330] group-hover:text-[#25D366] transition-colors">0819-9546-6255</span>
                        <span class="text-[11px] font-medium text-gray-500">Chat Sekarang</span>
                    </div>
                </a>

                <!-- Cart Shortcut (Desktop & Mobile) -->
                <a href="{{ route('web.produk', ['open_cart' => 1]) }}"
                   @click="if (window.location.pathname === '{{ url('/produk') }}' || window.location.pathname === '/produk') { $event.preventDefault(); window.dispatchEvent(new CustomEvent('open-cart-modal')); }"
                   title="Keranjang Belanja"
                   class="p-2 rounded-xl text-[#0c1330] hover:text-[#5E17EB] hover:bg-purple-50 transition-colors relative flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-text="cartCount"
                          class="absolute -top-1 -right-1 min-w-[20px] h-[20px] px-1 bg-[#E6007A] text-white text-[11px] font-extrabold rounded-full flex items-center justify-center shadow-xs border-2 border-white">
                    </span>
                </a>

                @auth
                <a href="{{ route('dashboard') }}"
                   class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#5E17EB] text-white font-bold text-xs shadow-xs hover:opacity-95 transition-all">
                    Dashboard
                </a>
                @endauth

                <!-- Mobile Hamburger Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        type="button"
                        class="lg:hidden p-2 rounded-xl text-gray-700 hover:text-purple-600 hover:bg-gray-100 focus:outline-hidden"
                        aria-label="Buka Menu">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenuOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden bg-white border-b border-gray-200 px-4 pt-2 pb-6 space-y-2 shadow-xl">
            <a href="{{ route('home') }}" class="block px-4 py-2.5 rounded-xl font-bold {{ request()->routeIs('home') || request()->routeIs('beranda') ? 'bg-purple-50 text-[#301ac4]' : 'text-gray-700 hover:bg-gray-50' }}">Beranda</a>
            <a href="{{ route('web.produk') }}" class="block px-4 py-2.5 rounded-xl font-bold {{ request()->routeIs('web.produk*') ? 'bg-purple-50 text-[#301ac4]' : 'text-gray-700 hover:bg-gray-50' }}">Produk</a>
            <a href="{{ route('web.servis') }}" class="block px-4 py-2.5 rounded-xl font-bold {{ request()->routeIs('web.servis*') ? 'bg-purple-50 text-[#301ac4]' : 'text-gray-700 hover:bg-gray-50' }}">Servis</a>
            <a href="{{ route('web.produk', ['open_cart' => 1]) }}"
               @click="if (window.location.pathname === '/produk') { mobileMenuOpen = false; $event.preventDefault(); window.dispatchEvent(new CustomEvent('open-cart-modal')); }"
               class="flex items-center justify-between px-4 py-2.5 rounded-xl font-bold text-gray-700 hover:bg-gray-50">
                <span class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Keranjang Belanja</span>
                </span>
                <span x-cloak x-show="cartCount > 0" x-text="cartCount + ' item'" class="px-2 py-0.5 rounded-full text-xs font-black bg-[#5E17EB] text-white"></span>
            </a>
        </div>
    </header>

    <!-- 2. MAIN BODY CONTENT -->
    <main class="flex-1 w-full">
        {{ $slot }}
    </main>

    <!-- 3. FOOTER SECTION (Identik dengan Desain 5 Halaman) -->
    <footer class="bg-[#0c1330] text-white relative mt-auto">
        <!-- Rainbow Brand Top Accent Stripe -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#301ac4] via-[#5E17EB] to-[#db00df]"></div>

        <div class="pt-16 pb-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

                <!-- Col 1: Brand & Slogan -->
                <div>
                    <div class="flex flex-col gap-2 mb-6">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Soulmate Komputer Logo"
                             class="h-10 w-auto max-w-[220px] object-contain bg-white rounded-xl p-1.5 shadow-sm">
                        <span class="text-[11px] text-gray-400 font-semibold tracking-wide">
                            Device Care People First
                        </span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed font-normal">
                        Solusi teknologi untuk kebutuhan Anda.<br>
                        Produk original, layanan terbaik, dan kepercayaan pelanggan adalah prioritas kami.
                    </p>
                </div>

                <!-- Col 2: Navigation Links -->
                <div>
                    <h4 class="font-extrabold text-white text-base mb-5 tracking-wide">Menu</h4>
                    <ul class="space-y-3 font-medium text-sm text-gray-300">
                        <li><a href="{{ route('home') }}" class="hover:text-purple-400 transition-colors">Beranda</a></li>
                        <li><a href="{{ route('web.produk') }}" class="hover:text-purple-400 transition-colors">Produk</a></li>
                        <li><a href="{{ route('web.servis') }}" class="hover:text-purple-400 transition-colors">Servis</a></li>
                        <li><a href="{{ route('antrian.display') }}" target="_blank" class="hover:text-purple-400 transition-colors">Antrian Servis (TV)</a></li>
                    </ul>
                </div>

                <!-- Col 3: Hubungi Kami -->
                <div>
                    <h4 class="font-extrabold text-white text-base mb-5 tracking-wide">Hubungi Kami</h4>
                    <ul class="space-y-3.5 text-sm text-gray-300 font-medium">
                        <li class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <a href="https://wa.me/6281995466255" target="_blank" class="hover:text-white transition-colors">0819 9546 6255</a>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-purple-400 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Jl. Masjid Jamik, Kel. Masjid Jamik, Kec. Rangkui, Kota Pangkalpinang, Kep. Bangka Belitung</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <a href="mailto:soulmatekomputer24@gmail.com" class="hover:text-white transition-colors">soulmatekomputer24@gmail.com</a>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Ikuti Kami -->
                <div>
                    <h4 class="font-extrabold text-white text-base mb-5 tracking-wide">Ikuti Kami</h4>
                    <div class="flex items-center gap-3 mb-6">
                        <!-- Instagram -->
                        <a href="#" class="w-9 h-9 rounded-xl bg-pink-600/90 hover:bg-pink-600 flex items-center justify-center text-white transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <!-- TikTok -->
                        <a href="#" class="w-9 h-9 rounded-xl bg-gray-800 hover:bg-gray-700 flex items-center justify-center text-white transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                        </a>
                        <!-- YouTube -->
                        <a href="#" class="w-9 h-9 rounded-xl bg-red-600 hover:bg-red-700 flex items-center justify-center text-white transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <!-- Facebook -->
                        <a href="#" class="w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 flex items-center justify-center text-white transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                    <p class="text-sm italic text-purple-300 font-serif">
                        &ldquo;Device Care People First&rdquo;
                    </p>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="pt-8 border-t border-gray-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400 font-medium">
                <p>&copy; 2026 Soulmate Komputer. Semua Hak Dilindungi.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-white transition-colors">Syarat & Ketentuan</a>
                    <span class="text-gray-700">|</span>
                    <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

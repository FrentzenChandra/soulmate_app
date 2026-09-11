<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Soulmate Komputer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-brand-purple {
            background-color: #5E17EB;
        }
        .text-brand-purple {
            color: #5E17EB;
        }
        .bg-brand-blue {
            background-color: #0052FF;
        }
        .text-brand-blue {
            color: #0052FF;
        }
        .bg-brand-pink {
            background-color: #E6007A;
        }
        .bg-brand-gradient {
            background: linear-gradient(135deg, #5E17EB 0%, #E6007A 100%);
        }
        .card-custom {
            border-radius: 16px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="h-full text-[#1A1D20] antialiased bg-[#F8F9FA] selection:bg-[#5E17EB] selection:text-white" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar Desktop & Mobile -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0E1726] text-white flex flex-col transition-transform duration-300 ease-in-out md:static md:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <!-- Brand Logo -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800/80">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Soulmate" class="w-10 h-10 rounded-xl object-contain bg-white/5 p-1 border border-white/10 shadow-lg shadow-purple-950/50">
                    <div>
                        <span class="text-base font-extrabold tracking-tight block leading-tight text-white">SOULMATE</span>
                        <span class="text-[10px] text-purple-400 font-bold tracking-wider uppercase">Komputer & Servis</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Role Badge / User Info Pill -->
            <div class="px-4 py-2.5 mx-3 my-2.5 rounded-xl bg-gray-900/90 border border-gray-800 flex items-center justify-between">
                <div class="flex items-center space-x-2.5 truncate">
                    <div class="w-2.5 h-2.5 rounded-full {{ auth()->user()->isOwner() ? 'bg-amber-400' : 'bg-emerald-400' }} animate-pulse"></div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-gray-400 font-medium">
                            @if(auth()->user()->isOwner())
                                <span class="text-amber-400 font-semibold">👑 Owner (Multi-Store)</span>
                            @else
                                {{ auth()->user()->role_name }} • {{ auth()->user()->cabang?->nama_cabang ?? 'Pusat' }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links with Fading Bottom -->
            <div class="relative flex-1 min-h-0 flex flex-col">
                <nav class="flex-1 px-3 py-1 space-y-1 overflow-y-auto custom-scrollbar pb-8">
                
                <!-- Menu Utama -->
                <p class="px-3 pt-3 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Menu Utama</p>

                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>

                <!-- Modul Servis -->
                @hasanyrole('Owner|Superadmin|Teknisi')
                <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Layanan Servis</p>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Servis Masuk
                </a>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Servis Selesai / Keluar
                </a>
                @endhasanyrole

                <!-- Modul Kasir POS -->
                @hasanyrole('Owner|Superadmin|Kasir')
                <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Kasir & Penjualan</p>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Kasir POS (Produk)
                </a>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Riwayat Transaksi
                </a>
                @endhasanyrole

                <!-- Master Data & Stok -->
                @hasanyrole('Owner|Superadmin')
                <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Katalog & Stok</p>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Master Produk
                </a>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                    Stok Cabang
                </a>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Data Pelanggan
                </a>
                @endhasanyrole

                <!-- Display Antrian Smart TV -->
                <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Layar Toko</p>

                <a href="#" target="_blank" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Display Antrian TV
                    <span class="ml-auto text-[10px] bg-teal-500/20 text-teal-300 px-2 py-0.5 rounded-full font-bold">Live</span>
                </a>

                <!-- Pengaturan Cabang & User Khusus Owner / Superadmin -->
                @role('Owner')
                <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Owner & Laporan</p>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Master Cabang
                </a>

                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Laporan Keuangan
                </a>
                @endrole
                </nav>

                <!-- Bottom Fading Overlay for smooth scrolling menu -->
                <div class="pointer-events-none absolute bottom-0 inset-x-0 h-10 bg-gradient-to-t from-[#0E1726] via-[#0E1726]/80 to-transparent"></div>
            </div>

            <!-- Bottom Profile & Logout -->
            <div class="p-4 border-t border-gray-800/80 bg-[#0A111D]/60">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Topbar Header -->
            <header class="bg-white border-b border-gray-200/80 px-6 py-4 flex items-center justify-between z-10">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <!-- Cabang Indicator / Switcher -->
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center justify-center p-2 rounded-lg bg-purple-50 text-[#5E17EB]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </span>
                        <div>
                            <span class="text-xs text-gray-400 font-medium block leading-none">Cabang Aktif</span>
                            <span class="text-sm font-bold text-gray-800">
                                @if(auth()->user()->isOwner())
                                    <span class="text-amber-600">Seluruh Cabang (Owner Multi-Store)</span>
                                @else
                                    {{ auth()->user()->cabang?->nama_cabang ?? 'Cabang Pusat' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Action / User Profile -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-bold text-gray-900 leading-tight">{{ auth()->user()->name }}</span>
                        <span class="text-xs font-semibold text-[#5E17EB]">{{ auth()->user()->role_name }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-brand-gradient text-white flex items-center justify-center font-bold text-base shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Main Page Slot -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                {{ $slot }}
            </main>

        </div>

    </div>

    @livewireScripts
</body>
</html>

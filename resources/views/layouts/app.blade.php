<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F8F9FA] overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Soulmate Komputer</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.png') }}">
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
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @media print {
            aside, header, nav, .no-print {
                display: none !important;
            }
            body, .flex, main {
                overflow: visible !important;
                height: auto !important;
                background: white !important;
            }
            @page {
                size: 9.5in 11in;
                margin: 6mm 10mm;
            }
        }
    </style>
</head>
<body class="h-full text-[#1A1D20] antialiased bg-[#F8F9FA] selection:bg-[#5E17EB] selection:text-white overflow-hidden"
      x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('soulmate_sidebar_collapsed') === 'true' }"
      @resize.window="if (window.innerWidth >= 768) { sidebarOpen = false; }">

    <div class="flex h-screen w-full max-w-full overflow-hidden">

        <!-- Mobile Backdrop Overlay -->
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-gray-950/60 backdrop-blur-xs md:hidden"
             style="display: none;"></div>

        <!-- Sidebar Desktop & Mobile -->
        <aside class="fixed inset-y-0 left-0 z-50 bg-[#0E1726] text-white flex flex-col transition-all duration-300 ease-in-out md:static md:shrink-0 overflow-hidden"
               :class="{
                   'translate-x-0 shadow-2xl w-64 max-w-[80vw] sm:max-w-xs': sidebarOpen,
                   '-translate-x-full w-64 max-w-[80vw] sm:max-w-xs': !sidebarOpen,
                   'md:w-64 md:translate-x-0 md:opacity-100': !sidebarCollapsed,
                   'md:w-0 md:opacity-0 md:pointer-events-none': sidebarCollapsed
               }">

            <!-- Inner Fixed Width Container to prevent content squash during transition -->
            <div class="w-64 min-w-[16rem] h-full flex flex-col flex-1 min-h-0">

                <!-- Brand Logo -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800/80">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo_bulat.png') }}" alt="Logo Soulmate" class="w-10 h-10 rounded-xl object-contain bg-white/5 p-1 border border-white/10 shadow-lg shadow-purple-950/50">
                    <div>
                        <span class="text-base font-extrabold tracking-tight block leading-tight text-white">SOULMATE</span>
                        <span class="text-base font-extrabold tracking-tight block leading-tight text-white">KOMPUTER</span>
                    </div>
                </a>
                <div class="flex items-center space-x-1">
                    <!-- Collapse Button Desktop -->
                    <button @click="sidebarCollapsed = true; localStorage.setItem('soulmate_sidebar_collapsed', 'true')"
                            title="Tutup Sidebar"
                            type="button"
                            class="hidden md:flex p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800/60 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <!-- Close Button Mobile -->
                    <button @click="sidebarOpen = false" type="button" class="md:hidden text-gray-400 hover:text-white p-1 rounded-lg hover:bg-gray-800/60 transition-colors" title="Tutup Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Role Badge / User Info Pill -->
            <div class="px-4 py-2.5 mx-3 my-2.5 rounded-xl bg-gray-900/90 border border-gray-800 flex items-center justify-between">
                <div class="flex items-center space-x-2.5 truncate">
                    <div class="w-2.5 h-2.5 rounded-full {{ auth()->user()->isOwner() ? 'bg-purple-400 shadow-sm shadow-purple-400' : 'bg-emerald-400 shadow-sm shadow-emerald-400' }} animate-pulse"></div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] font-medium">
                            @if(auth()->user()->isOwner())
                                @if(session('active_branch_id', 'hq') === 'hq')
                                    <span class="text-purple-300 font-semibold">Owner · Konteks HQ</span>
                                @else
                                    <span class="text-blue-300 font-semibold">Owner · Mode Cabang</span>
                                @endif
                            @else
                                <span class="text-gray-400">{{ auth()->user()->role_name }} · Soulmate Komputer</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links with Fading Bottom -->
            <div class="relative flex-1 min-h-0 flex flex-col">
                <nav x-data x-init="$nextTick(() => {
                        const scrollToActive = () => {
                            const active = $el.querySelector('a.bg-\\[\\#5E17EB\\], a.bg-\\[\\#0052FF\\], a.bg-brand-purple, a.bg-brand-blue, [data-sidebar-active]');
                            if (active) {
                                active.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'smooth' });
                            }
                        };
                        scrollToActive();
                        $watch('sidebarOpen', val => { if (val) setTimeout(scrollToActive, 150); });
                        $watch('sidebarCollapsed', val => { if (!val) setTimeout(scrollToActive, 150); });
                    })"
                    class="flex-1 px-3 py-1 space-y-1 overflow-y-auto no-scrollbar pb-8">

                @php
                    $isOwner = auth()->user()->isOwner();
                    $isHQ = $isOwner && session('active_branch_id', 'hq') === 'hq';
                    $activeBranchSession = session('active_branch_id', 'hq');

                    // Hitung jumlah alert stok (kritis + kosong) yang aktif untuk sinkron dengan NotificationBell
                    if ($isHQ) {
                        $emptyStockSidebarCount = \App\Models\Stok::where('is_aktif', true)
                            ->whereHas('produk')
                            ->whereHas('cabang', fn ($q) => $q->where('status', 'aktif'))
                            ->whereColumn('jumlah', '<=', 'min_stok')
                            ->count();
                    } else {
                        $targetCabId = $isOwner ? (int) $activeBranchSession : auth()->user()->cabang_id;
                        $targetCabId = $targetCabId ?: 1;
                        $emptyStockSidebarCount = \App\Models\Stok::where('cabang_id', $targetCabId)
                            ->where('is_aktif', true)
                            ->whereHas('produk')
                            ->whereColumn('jumlah', '<=', 'min_stok')
                            ->count();
                    }
                @endphp

                @if($isHQ)
                    <!-- ============================================== -->
                    <!-- SIDEBAR: KONTEKS KANTOR PUSAT (HQ) - OWNER ONLY -->
                    <!-- ============================================== -->
                    <p class="px-3 pt-3 pb-1 text-[10px] font-extrabold tracking-wider text-purple-400 uppercase flex items-center justify-between">
                        <span>Tata Kelola Pusat</span>
                        <span class="text-[9px] bg-purple-500/20 text-purple-300 px-1.5 py-0.5 rounded font-bold">HQ</span>
                    </p>

                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Dashboard HQ
                    </a>

                    <a href="/pulse" target="_blank" class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                        <svg class="w-5 h-5 mr-3 text-purple-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span>Server Metrics</span>
                        <span class="ml-auto text-[10px] bg-purple-500/20 text-purple-300 px-2 py-0.5 rounded-full font-bold">Pulse</span>
                    </a>

                    <p class="px-3 pt-4 pb-1 text-[10px] font-extrabold tracking-wider text-gray-400 uppercase">Master & Organisasi</p>

                    <a href="{{ route('master.cabang') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.cabang*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.cabang*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Master Cabang
                    </a>

                    <a href="{{ route('master.ppn') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.ppn*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.ppn*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z"></path></svg>
                        Master Tarif PPN
                    </a>

                    <a href="{{ route('master.users') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.users*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.users*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Master Pengguna
                    </a>

                    <a href="{{ route('master.produk') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.produk*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.produk*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Master Produk
                    </a>

                    <a href="{{ route('master.servis') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.servis*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.servis*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Master Servis
                    </a>

                    <a href="{{ route('cabang.stok') }}"
                        class="group flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('cabang.stok*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('cabang.stok*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                            <span>Manajemen Stok</span>
                        </div>
                        @if($emptyStockSidebarCount > 0)
                            <span class="text-[10px] bg-rose-500/20 text-rose-300 px-2 py-0.5 rounded-full font-bold border border-rose-500/30">{{ $emptyStockSidebarCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('master.customer') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.customer*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.customer*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Data Pelanggan
                    </a>



                    <p class="px-3 pt-4 pb-1 text-[10px] font-extrabold tracking-wider text-gray-400 uppercase">Layanan Servis</p>

                    <a href="{{ route('servis.masuk') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.masuk*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.masuk*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Servis Masuk
                    </a>

                    <a href="{{ route('servis.keluar') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.keluar*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.keluar*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Servis Keluar
                    </a>

                    <a href="{{ route('servis.batal') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.batal*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.batal*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Servis Batal
                    </a>

                    <a href="{{ route('servis.non-real') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.non-real*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.non-real*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Servis Non Real
                    </a>

                    <p class="px-3 pt-4 pb-1 text-[10px] font-extrabold tracking-wider text-gray-400 uppercase">Kas & Transaksi</p>

                    <a href="{{ route('transaksi.harian') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('transaksi.harian*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('transaksi.harian*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Transaksi Harian
                    </a>

                    <a href="{{ route('transaksi.full') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('transaksi.full*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('transaksi.full*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Transaksi FULL
                    </a>

                    <a href="{{ route('target.bulanan') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('target.bulanan*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('target.bulanan*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Target Bulanan
                    </a>

                    <a href="{{ route('audit-laci') }}"
                        class="group flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('audit-laci*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-400 hover:bg-gray-800/80 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('audit-laci*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span>Audit Laci</span>
                        </div>
                        <span class="text-[9px] bg-gray-800 text-gray-400 border border-gray-700 px-1.5 py-0.5 rounded font-semibold">Nonaktif</span>
                    </a>

                    <a href="{{ route('kas-keluar') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('kas-keluar*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('kas-keluar*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Kas Keluar
                    </a>

                    @if(auth()->user()->isOwner() || auth()->user()->hasRole(['Superadmin', 'Admin']))
                    <a href="{{ route('saldo.index') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('saldo*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('saldo*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Saldo
                    </a>
                    @endif

                    {{-- [TEMP HIDE] Modal Awal
                    <a href="{{ route('modal-awal') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('modal-awal*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('modal-awal*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Modal Awal
                    </a>
                    --}}

                    <a href="{{ route('serial-number') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('serial-number*') ? 'bg-[#5E17EB] text-white shadow-lg shadow-purple-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('serial-number*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Serial Number
                    </a>

                    <p class="px-3 pt-4 pb-1 text-[10px] font-extrabold tracking-wider text-gray-400 uppercase">Laporan Global</p>

                    <div x-data="{ open: {{ request()->routeIs('laporan.penjualan*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            type="button"
                            class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('laporan.penjualan*') ? 'bg-gray-800/80 text-white' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('laporan.penjualan*') ? 'text-[#5E17EB]' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span>Laporan Penjualan</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-8 pr-2 space-y-1 py-1">
                            <a href="{{ route('laporan.penjualan', ['tipeTransaksiFilter' => 'real']) }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'real' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'real' ? 'bg-white' : 'bg-emerald-400' }} mr-2.5"></span>
                                <span>Penjualan Real</span>
                            </a>
                            <a href="{{ route('laporan.penjualan', ['tipeTransaksiFilter' => 'non_real']) }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'non_real' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'non_real' ? 'bg-white' : 'bg-amber-400' }} mr-2.5"></span>
                                <span>Nota Non-Real</span>
                            </a>
                            <a href="{{ route('laporan.penjualan', ['tipeTransaksiFilter' => 'semua']) }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('laporan.penjualan*') && (request('tipeTransaksiFilter') === 'semua' || !request('tipeTransaksiFilter')) ? 'bg-gray-700 text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('laporan.penjualan*') && (request('tipeTransaksiFilter') === 'semua' || !request('tipeTransaksiFilter')) ? 'bg-white' : 'bg-gray-400' }} mr-2.5"></span>
                                <span>Semua Laporan</span>
                            </a>
                        </div>
                    </div>

                    <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-400 hover:bg-gray-800/80 hover:text-white transition-all">
                        <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Konsolidasi Finansial
                        <span class="ml-auto text-[9px] bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded font-semibold">Segera</span>
                    </a>

                    <!-- Dropdown Setting (HQ) -->
                    @if(auth()->user()->hasAnyRole(['Owner', 'Superadmin']))
                    <div x-data="{ open: {{ (request()->routeIs('master.kategori*') || request()->routeIs('master.metode-pembayaran*') || request()->routeIs('master.sumber-dana*') || request()->routeIs('master.kategori-kas*')) ? 'true' : 'false' }} }" class="space-y-1 pt-2 border-t border-gray-800/50">
                        <button @click="open = !open"
                            type="button"
                            class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ (request()->routeIs('master.kategori*') || request()->routeIs('master.metode-pembayaran*') || request()->routeIs('master.sumber-dana*') || request()->routeIs('master.kategori-kas*')) ? 'bg-gray-800/80 text-white' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ (request()->routeIs('master.kategori*') || request()->routeIs('master.metode-pembayaran*') || request()->routeIs('master.sumber-dana*') || request()->routeIs('master.kategori-kas*')) ? 'text-[#5E17EB]' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Setting</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-8 pr-2 space-y-1 py-1">
                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.kategori') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ (request()->routeIs('master.kategori') || request()->routeIs('master.kategori.*')) ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('master.kategori') || request()->routeIs('master.kategori.*')) ? 'bg-white' : 'bg-purple-400' }} mr-2.5"></span>
                                <span>Master Kategori</span>
                            </a>
                            @endhasanyrole

                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.metode-pembayaran') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('master.metode-pembayaran*') ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.metode-pembayaran*') ? 'bg-white' : 'bg-purple-400' }} mr-2.5"></span>
                                <span>Metode Pembayaran</span>
                            </a>
                            @endhasanyrole

                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.sumber-dana') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('master.sumber-dana*') ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.sumber-dana*') ? 'bg-white' : 'bg-purple-400' }} mr-2.5"></span>
                                <span>Sumber Dana</span>
                            </a>
                            @endhasanyrole

                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.kategori-kas') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('master.kategori-kas*') ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.kategori-kas*') ? 'bg-white' : 'bg-purple-400' }} mr-2.5"></span>
                                <span>Kategori Kas</span>
                            </a>
                            @endhasanyrole
                        </div>
                    </div>
                    @endif

                @else
                    <!-- ============================================== -->
                    <!-- SIDEBAR: KONTEKS OPERASIONAL (STAFF & CABANG)  -->
                    <!-- ============================================== -->
                    <p class="px-3 pt-3 pb-1 text-[10px] font-extrabold tracking-wider text-blue-400 uppercase flex items-center justify-between">
                        <span>{{ $isOwner ? 'Operasional Cabang' : 'Menu Utama' }}</span>
                        @if($isOwner)
                            <span class="text-[9px] bg-blue-500/20 text-blue-300 px-1.5 py-0.5 rounded font-bold">Mode Cabang</span>
                        @endif
                    </p>

                    <!-- 1. Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>

                    <!-- 2. Kasir & Penjualan -->
                    @hasanyrole('Owner|Superadmin|Admin|Bendahara|Kasir')
                    <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Kasir & Penjualan</p>

                    <a href="{{ route('kasir.produk') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('kasir.produk*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('kasir.produk*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Kasir POS
                    </a>

                    <div x-data="{ open: {{ request()->routeIs('laporan.penjualan*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            type="button"
                            class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('laporan.penjualan*') ? 'bg-gray-800/80 text-white' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('laporan.penjualan*') ? 'text-[#0052FF]' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span>Riwayat Transaksi</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-8 pr-2 space-y-1 py-1">
                            <a href="{{ route('laporan.penjualan', ['tipeTransaksiFilter' => 'real']) }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'real' ? 'bg-[#0052FF] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'real' ? 'bg-white' : 'bg-emerald-400' }} mr-2.5"></span>
                                <span>Penjualan Real</span>
                            </a>
                            <a href="{{ route('laporan.penjualan', ['tipeTransaksiFilter' => 'non_real']) }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'non_real' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('laporan.penjualan*') && request('tipeTransaksiFilter') === 'non_real' ? 'bg-white' : 'bg-amber-400' }} mr-2.5"></span>
                                <span>Nota Non-Real</span>
                            </a>
                            <a href="{{ route('laporan.penjualan', ['tipeTransaksiFilter' => 'semua']) }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('laporan.penjualan*') && (request('tipeTransaksiFilter') === 'semua' || !request('tipeTransaksiFilter')) ? 'bg-gray-700 text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('laporan.penjualan*') && (request('tipeTransaksiFilter') === 'semua' || !request('tipeTransaksiFilter')) ? 'bg-white' : 'bg-gray-400' }} mr-2.5"></span>
                                <span>Semua Transaksi</span>
                            </a>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- 3. Layanan Servis -->
                    @hasanyrole('Owner|Superadmin|Admin|Bendahara|Teknisi|Kasir')
                    <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Layanan Servis</p>

                    <a href="{{ route('servis.masuk') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.masuk*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.masuk*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Servis Masuk
                    </a>

                    <a href="{{ route('servis.keluar') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.keluar*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.keluar*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Servis Selesai
                    </a>

                    <a href="{{ route('servis.batal') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.batal*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.batal*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Servis Batal
                    </a>

                    <a href="{{ route('servis.non-real') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('servis.non-real*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('servis.non-real*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Servis Non Real
                    </a>
                    @endhasanyrole

                    <!-- 4. Kas & Transaksi -->
                    @hasanyrole('Owner|Superadmin|Admin|Bendahara|Kasir')
                    <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Kas & Transaksi</p>

                    @hasanyrole('Owner|Admin|Bendahara|Kasir')
                    <a href="{{ route('transaksi.harian') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('transaksi.harian*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('transaksi.harian*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Transaksi Harian
                    </a>
                    @endhasanyrole

                    @hasanyrole('Owner|Superadmin')
                    <a href="{{ route('transaksi.full') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('transaksi.full*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('transaksi.full*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Transaksi Full
                    </a>
                    @endhasanyrole

                    @hasanyrole('Owner|Superadmin')
                    <a href="{{ route('target.bulanan') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('target.bulanan*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('target.bulanan*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Target Bulanan
                    </a>
                    @endhasanyrole

                    @hasanyrole('Owner|Superadmin|Bendahara')
                    <a href="{{ route('kas-keluar') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('kas-keluar*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('kas-keluar*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Kas Keluar
                    </a>

                    <a href="{{ route('saldo.index') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('saldo*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('saldo*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Saldo
                    </a>
                    @endhasanyrole

                    @hasanyrole('Owner|Superadmin|Admin|Bendahara|Kasir')
                    <a href="{{ route('serial-number') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('serial-number*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('serial-number*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Serial Number
                    </a>
                    @endhasanyrole
                    @endhasanyrole

                    <!-- 5. Master Data -->
                    @hasanyrole('Owner|Superadmin|Admin|Bendahara|Teknisi|Kasir')
                    <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Master Data</p>

                    @hasanyrole('Owner|Superadmin|Admin|Bendahara|Teknisi')
                    <a href="{{ route('master.produk') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.produk*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.produk*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Master Produk
                    </a>

                    <a href="{{ route('master.servis') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.servis*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.servis*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Master Servis
                    </a>
                    @endhasanyrole

                    @hasanyrole('Owner|Superadmin|Admin|Bendahara|Kasir')
                    <a href="{{ route('cabang.stok') }}"
                        class="group flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('cabang.stok*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('cabang.stok*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                            <span>Manajemen Stok</span>
                        </div>
                        @if($emptyStockSidebarCount > 0)
                            <span class="text-[10px] bg-rose-500/20 text-rose-300 px-2 py-0.5 rounded-full font-bold border border-rose-500/30">{{ $emptyStockSidebarCount }}</span>
                        @endif
                    </a>
                    @endhasanyrole



                    <a href="{{ route('master.customer') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.customer*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.customer*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Data Pelanggan
                    </a>

                    @hasanyrole('Owner|Superadmin')
                    <a href="{{ route('master.users') }}"
                        class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('master.users*') ? 'bg-[#0052FF] text-white shadow-lg shadow-blue-900/40' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('master.users*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Master Pengguna
                    </a>

                    <!-- Dropdown Setting (Branch) -->
                    <div x-data="{ open: {{ (request()->routeIs('master.kategori*') || request()->routeIs('master.metode-pembayaran*') || request()->routeIs('master.sumber-dana*') || request()->routeIs('master.kategori-kas*')) ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            type="button"
                            class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-xl transition-all {{ (request()->routeIs('master.kategori*') || request()->routeIs('master.metode-pembayaran*') || request()->routeIs('master.sumber-dana*') || request()->routeIs('master.kategori-kas*')) ? 'bg-gray-800/80 text-white' : 'text-gray-300 hover:bg-gray-800/80 hover:text-white' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ (request()->routeIs('master.kategori*') || request()->routeIs('master.metode-pembayaran*') || request()->routeIs('master.sumber-dana*') || request()->routeIs('master.kategori-kas*')) ? 'text-[#0052FF]' : 'text-gray-400 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Setting</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-8 pr-2 space-y-1 py-1">
                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.kategori') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ (request()->routeIs('master.kategori') || request()->routeIs('master.kategori.*')) ? 'bg-[#0052FF] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('master.kategori') || request()->routeIs('master.kategori.*')) ? 'bg-white' : 'bg-blue-400' }} mr-2.5"></span>
                                <span>Master Kategori</span>
                            </a>
                            @endhasanyrole

                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.metode-pembayaran') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('master.metode-pembayaran*') ? 'bg-[#0052FF] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.metode-pembayaran*') ? 'bg-white' : 'bg-blue-400' }} mr-2.5"></span>
                                <span>Metode Pembayaran</span>
                            </a>
                            @endhasanyrole

                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.sumber-dana') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('master.sumber-dana*') ? 'bg-[#0052FF] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.sumber-dana*') ? 'bg-white' : 'bg-blue-400' }} mr-2.5"></span>
                                <span>Sumber Dana</span>
                            </a>
                            @endhasanyrole

                            @hasanyrole('Owner|Superadmin')
                            <a href="{{ route('master.kategori-kas') }}"
                                class="flex items-center px-3 py-2 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('master.kategori-kas*') ? 'bg-[#0052FF] text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.kategori-kas*') ? 'bg-white' : 'bg-blue-400' }} mr-2.5"></span>
                                <span>Kategori Kas</span>
                            </a>
                            @endhasanyrole
                        </div>
                    </div>
                    @endhasanyrole
                    @endhasanyrole

                    <!-- 6. Layar Toko -->
                    <p class="px-3 pt-4 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase">Layar Toko</p>

                    <a href="#" target="_blank" class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                        <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Display Antrian TV
                        <span class="ml-auto text-[10px] bg-teal-500/20 text-teal-300 px-2 py-0.5 rounded-full font-bold">Live</span>
                    </a>

                    @if($isOwner)
                    <a href="/pulse" target="_blank" class="group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-300 hover:bg-gray-800/80 hover:text-white transition-all">
                        <svg class="w-5 h-5 mr-3 text-purple-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span>Server Metrics</span>
                        <span class="ml-auto text-[10px] bg-purple-500/20 text-purple-300 px-2 py-0.5 rounded-full font-bold">Pulse</span>
                    </a>
                    @endif


                    @if($isOwner)
                        <div class="pt-6 px-1">
                            <button wire:click="$dispatch('switchBranchHQ')"
                                    class="w-full flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl text-xs font-bold bg-purple-950/70 border border-purple-800/60 text-purple-300 hover:bg-purple-900 hover:text-white transition-all shadow-md cursor-pointer">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                                <span>Kembali ke Pusat (HQ)</span>
                            </button>
                        </div>
                    @endif
                @endif

                </nav>

                <!-- Bottom Fading Overlay for smooth scrolling menu -->
                <div class="pointer-events-none absolute bottom-0 inset-x-0 h-10 bg-gradient-to-t from-[#0E1726] via-[#0E1726]/80 to-transparent"></div>
            </div>

            <!-- Bottom Profile & Logout -->
            <div class="p-4 border-t border-gray-800/80 bg-[#0A111D]/60">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 max-w-full overflow-hidden">

            <!-- Topbar Header -->
            <header class="bg-white border-b border-gray-200/80 px-4 sm:px-6 py-3.5 flex items-center justify-between relative z-30 shrink-0">
                <div class="flex items-center space-x-3">
                    <!-- Mobile Hamburger Button -->
                    <button @click="sidebarOpen = true" type="button" class="md:hidden text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" title="Buka Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <!-- Desktop Sidebar Toggle Button -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('soulmate_sidebar_collapsed', sidebarCollapsed)"
                            type="button"
                            class="hidden md:flex items-center justify-center p-2 rounded-xl text-gray-500 hover:text-[#5E17EB] hover:bg-purple-50 border border-gray-200 transition-all cursor-pointer group"
                            title="Toggle Sidebar">
                        <svg class="w-5 h-5 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </button>

                    <!-- Branch Switcher Component -->
                    <livewire:components.branch-switcher />
                </div>

                <!-- Right Actions (Ringkasan Transaksi, Notification Bell & Profile Dropdown) -->
                <div class="flex items-center space-x-3">
                    @if(auth()->user()->isSuperadmin() || auth()->user()->isOwner())
                        <!-- Ringkasan Transaksi Superadmin -->
                        <livewire:components.navbar-ringkasan-transaksi />
                    @endif

                    <!-- Notification Bell Component -->
                    <livewire:components.notification-bell />

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
                    <button @click="profileOpen = !profileOpen"
                            type="button"
                            class="flex items-center space-x-3 p-1 rounded-xl hover:bg-gray-100 transition-colors focus:outline-none cursor-pointer group">
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-sm font-bold text-gray-900 leading-tight">{{ auth()->user()->name }}</span>
                            <span class="text-xs font-bold text-[#5E17EB]">{{ auth()->user()->role_name }}</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl overflow-hidden bg-brand-gradient text-white flex items-center justify-center font-bold text-base shadow-sm ring-2 ring-purple-100 group-hover:ring-purple-200 transition-all shrink-0">
                            @if(auth()->user()->profile_photo_url || auth()->user()->foto_url)
                                <img src="{{ auth()->user()->profile_photo_url ?? asset(auth()->user()->foto_url) }}"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                     alt="{{ auth()->user()->name }}"
                                     class="w-full h-full object-cover">
                                <span style="display:none;" class="w-full h-full flex items-center justify-center">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @else
                                <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Profile Dropdown Menu -->
                    <div x-show="profileOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl shadow-purple-950/15 border border-gray-100 py-2 z-50 divide-y divide-gray-100"
                         style="display: none;">

                        <div class="px-4 py-3">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Masuk sebagai</p>
                            <p class="text-sm font-extrabold text-gray-900 truncate mt-0.5">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? auth()->user()->username }}</p>
                            <div class="mt-2.5 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                <span>{{ auth()->user()->role_name }}</span>
                                @if(auth()->user()->isOwner() && auth()->user()->cabang)
                                    <span class="inline-block w-1 h-1 rounded-full bg-gray-300 mx-1 align-middle"></span>
                                    <span>{{ auth()->user()->cabang->nama_cabang }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('profile.show') }}" class="flex items-center px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-purple-50 hover:text-[#5E17EB] transition-colors cursor-pointer group">
                                <svg class="w-4 h-4 mr-2.5 text-gray-400 group-hover:text-[#5E17EB] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Profil & Keamanan (2FA)</span>
                            </a>
                        </div>

                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center px-4 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer group">
                                    <svg class="w-4 h-4 mr-2.5 text-rose-500 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Keluar (Logout)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </header>

            <!-- Main Page Slot -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 md:p-8 min-w-0 max-w-full">
                {{ $slot }}
            </main>

        </div>

    </div>

    <!-- Unified SweetAlert2 Session Handler -->
    @if (session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info') || session()->has('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if (session()->has('success'))
                    if (window.swalToast) {
                        window.swalToast('success', 'Berhasil', {!! json_encode(session('success')) !!});
                    }
                @endif
                @if (session()->has('error'))
                    if (window.swalToast) {
                        window.swalToast('error', 'Pemberitahuan', {!! json_encode(session('error')) !!});
                    }
                @endif
                @if (session()->has('warning'))
                    if (window.swalToast) {
                        window.swalToast('warning', 'Peringatan', {!! json_encode(session('warning')) !!});
                    }
                @endif
                @if (session()->has('info'))
                    if (window.swalToast) {
                        window.swalToast('info', 'Info', {!! json_encode(session('info')) !!});
                    }
                @endif
                @if (session()->has('status'))
                    if (window.swalToast) {
                        window.swalToast('info', 'Status', {!! json_encode(session('status')) !!});
                    }
                @endif
            });
        </script>
    @endif

    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tanda Terima Servis - {{ $servis->no_servis_frontend ?? $servis->no_servis }} - Soulmate Komputer</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: 24cm 15cm;
            margin: 4mm 6mm;
        }
        @media print {
            html, body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .tanda-terima-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
            .tanda-terima-container * {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-6 px-4 font-sans antialiased text-gray-900 selection:bg-purple-500 selection:text-white">

    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print bg-white p-3.5 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="{{ route('servis.masuk') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all inline-flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Servis Masuk</span>
            </a>
            <span class="text-xs font-mono text-gray-500 font-semibold">| {{ $servis->no_servis_frontend ?? $servis->no_servis }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition-all shadow-md inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Print</span>
            </button>
        </div>
    </div>

    <!-- Tanda Terima Container (Strict 1-Page on 24x15 cm) -->
    <div id="receiptPrintArea" class="tanda-terima-container max-w-3xl mx-auto bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-md space-y-2.5 text-gray-900 text-xs">

        <!-- Header: Store Identity & Service Ticket Info -->
        <div class="grid grid-cols-12 gap-3 items-start pb-1">

            <!-- Left: Logo & Toko -->
            <div class="col-span-7 flex items-start space-x-2.5">
                <img src="{{ asset('images/logo_bulat.png') }}" alt="Logo" class="w-10 h-10 rounded-xl object-cover shrink-0 border border-gray-100 shadow-xs">
                <div>
                    <h1 class="text-base font-black text-gray-900 tracking-tight leading-none">Soulmate Komputer</h1>
                    <p class="text-[10px] font-medium text-gray-600 mt-0.5 leading-tight">
                        {{ $servis->cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang' }}
                    </p>
                    <p class="text-[10px] font-semibold text-gray-700 mt-0.5">
                        WhatsApp: {{ $servis->cabang?->no_wa ?? '081995466255' }}
                    </p>
                </div>
            </div>

            <!-- Right: Meta Info (Tanggal, Jam, Kode Servis, Pelanggan) -->
            <div class="col-span-5 grid grid-cols-2 gap-1.5 text-[11px]">
                <div>
                    <span class="text-[9px] font-bold text-gray-400 uppercase block leading-none">Tanggal :</span>
                    <span class="font-bold text-gray-800 font-mono text-[10px] block mt-0.5">{{ $servis->tanggal_masuk ? $servis->tanggal_masuk->format('d-m-Y H:i') : '-' }}</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-gray-400 uppercase block leading-none">Pelanggan :</span>
                    <span class="font-bold text-gray-900 block truncate text-[10px] mt-0.5" title="{{ $servis->customer?->nama ?? 'Pelanggan Umum' }}">
                        {{ $servis->customer?->nama ?? 'Pelanggan Umum' }}
                    </span>
                    <span class="text-[10px] font-mono text-gray-600 block">{{ $servis->customer?->no_hp ?? '-' }}</span>
                </div>
                <div class="col-span-2 pt-0.5 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-gray-500 uppercase">Kode Servis:</span>
                    <span class="text-xs font-black text-[#5E17EB] font-mono tracking-wider">
                        {{ $servis->no_servis_frontend ?? $servis->no_servis }}
                    </span>
                </div>
            </div>

        </div>

        <!-- Divider Strip -->
        <div class="border-t border-b border-dashed border-gray-400 py-0.5 text-center font-bold tracking-widest text-[10px] uppercase text-gray-700 select-none">
            &middot;&middot;&middot;&middot;&middot; TANDA TERIMA BARANG SERVIS &middot;&middot;&middot;&middot;&middot;
        </div>

        <!-- Service Specs Grid -->
        <div class="grid grid-cols-2 gap-x-4 gap-y-2 pt-0.5 text-[11px]">

            <!-- Row 1: Nama Barang / Unit & Keluhan -->
            <div class="border-b border-dashed border-gray-200 pb-1.5">
                <span class="text-[9px] font-bold text-gray-400 uppercase block leading-none">Unit Perangkat :</span>
                <span class="text-xs font-bold text-gray-900 mt-0.5 block">
                    {{ $servis->tipe_unit ?? ($servis->kategori_perangkat_label . ' (Tanpa Tipe)') }}
                </span>
                <span class="text-[10px] text-gray-500">
                    Kategori: {{ $servis->kategori_perangkat_label }}
                    @if($servis->serial_number) &bull; S/N: <strong class="font-mono text-gray-700">{{ $servis->serial_number }}</strong> @endif
                </span>
            </div>

            <div class="border-b border-dashed border-gray-200 pb-1.5">
                <span class="text-[9px] font-bold text-gray-400 uppercase block leading-none">Keluhan Kerusakan :</span>
                <span class="text-[10px] font-semibold text-gray-800 mt-0.5 block whitespace-pre-line leading-tight">
                    {{ $servis->keluhan ?: '-' }}
                </span>
            </div>

            <!-- Row 2: Kelengkapan & Catatan / Kondisi Fisik -->
            <div>
                <span class="text-[9px] font-bold text-gray-400 uppercase block leading-none">Kelengkapan Unit :</span>
                <span class="text-[10px] font-medium text-gray-800 mt-0.5 block whitespace-pre-line leading-tight">
                    {{ $servis->kelengkapan ?: 'Hanya Unit' }}
                </span>
            </div>

            <div>
                <span class="text-[9px] font-bold text-gray-400 uppercase block leading-none">Kondisi Fisik :</span>
                <span class="text-[10px] font-medium text-gray-800 mt-0.5 block whitespace-pre-line leading-tight">
                    {{ $servis->kondisi_barang ?: ($servis->catatan ?: '-') }}
                </span>
            </div>

        </div>

        <!-- Tanda Tangan Section -->
        <div class="grid grid-cols-2 gap-6 pt-1 text-center text-[10px]">
            <div>
                <p class="font-medium text-gray-500">Pelanggan,</p>
                <div class="h-6"></div>
                <p class="font-bold text-gray-900 border-t border-gray-300 pt-0.5 inline-block min-w-[120px]">
                    {{ $servis->customer?->nama ?? 'Pelanggan' }}
                </p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Hormat kami,</p>
                <div class="h-6"></div>
                <p class="font-bold text-gray-900 border-t border-gray-300 pt-0.5 inline-block min-w-[120px]">
                    {{ $servis->pembuat?->name ?? 'Soulmate Komputer' }}
                </p>
            </div>
        </div>

        <!-- Box Footer: Public Web Tracking Info -->
        <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-200 flex items-center space-x-2.5">
            <div class="w-6 h-6 rounded-lg bg-purple-100 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
            </div>
            <div class="text-[10px] leading-snug text-gray-700">
                Pengecekan status servis dapat dilakukan di <strong class="text-[#0052FF] font-bold">www.soulmatekomputer.com</strong> dengan memasukkan kode servis: <span class="font-mono font-bold text-gray-900 bg-white px-1.5 py-0.2 rounded border border-gray-300">{{ $servis->no_servis_frontend ?? $servis->no_servis }}</span>
            </div>
        </div>

    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    window.print();
                }, 300);
            });
        }
    </script>
</body>
</html>

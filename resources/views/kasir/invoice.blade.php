<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Invoice - {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }} - Soulmate Komputer</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: 24cm 14cm;
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
            .sales-invoice-container {
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
            .sales-invoice-container * {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-6 px-4 font-sans antialiased text-gray-900 selection:bg-purple-500 selection:text-white">

    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print bg-white p-3.5 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="{{ route('kasir.produk') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all inline-flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke POS</span>
            </a>
            <span class="text-xs font-mono text-gray-500 font-semibold">| {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition-all shadow-md inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Print</span>
            </button>
        </div>
    </div>

    <!-- Reusable Invoice Template -->
    <x-sales-invoice :penjualan="$penjualan" />

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

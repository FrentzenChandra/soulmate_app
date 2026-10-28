<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $slip->nama_staf_snapshot ?: ($slip->user->name ?? 'Karyawan') }} - {{ sprintf('%04d-%02d', $slip->periode_tahun, $slip->periode_bulan) }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        @media print {
            html, body {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .slip-gaji-root {
                border: 1.5px solid #E2E8F0 !important;
                box-shadow: none !important;
                border-radius: 18px !important;
                padding: 18px 24px !important;
                width: 100% !important;
                max-width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen text-black antialiased">

    <!-- Floating Top Bar (Hanya tampil di layar, otomatis tersembunyi saat dicetak) -->
    <div class="no-print bg-white/95 backdrop-blur-xs border-b border-gray-200 sticky top-0 z-50 px-4 py-3 flex items-center justify-between shadow-xs">
        <div class="flex items-center space-x-3">
            <span class="w-3 h-3 rounded-full bg-[#5E17EB]"></span>
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-black block">
                    Mode Cetak Slip Gaji &bull; {{ $slip->no_slip }}
                </span>
                <span class="text-[10px] text-gray-500 font-bold block">
                    Desain 1:1 Presisi. Gunakan opsi "Save as PDF" di dialog print untuk menyimpan PDF jernih tanpa terpotong.
                </span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-800 text-white font-black text-xs shadow-md shadow-purple-900/20 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>

            <button type="button" onclick="window.close()"
                    class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-black hover:bg-gray-100 transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- Konten Slip Gaji (1:1 Sesuai Mockup) -->
    <div class="p-4 sm:p-8 flex items-center justify-center print-wrapper">
        <div class="w-full max-w-4xl">
            <x-slip-gaji-card :slip="$slip" />
        </div>
    </div>

    <!-- Auto Print Trigger -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>

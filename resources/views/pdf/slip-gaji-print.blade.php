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
    @vite(['resources/css/app.css'])
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        @page {
            size: 210mm 175mm;
            margin: 0;
        }

        @media print {
            html, body {
                width: 100% !important;
                height: auto !important; min-height: 0 !important;
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
                width: 210mm !important;
                min-height: 0 !important;
                display: block !important;
            }
            .slip-gaji-root {
                display: flex !important;
                flex-direction: column !important;
                min-height: 0 !important;
                max-height: 173mm !important;
                border: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 1.5mm !important;
                width: 206mm !important;
                max-width: 206mm !important;
                margin: 1mm auto 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                overflow: hidden !important;
            }
            .slip-gaji-header {
                height: 21mm !important;
                margin-bottom: 1mm !important;
                border-bottom: 1px solid #E2E8F0 !important;
            }
            .slip-gaji-header img {
                height: 19mm !important;
            }
            .slip-profile-box {
                flex-direction: row !important;
                align-items: center !important;
                margin: 1mm 0 !important;
                padding: 1.2mm 2.5mm !important;
                border-color: #DDD6FE !important;
                background: #F6F3FC !important;
                box-shadow: 0 0.5mm 1.5mm rgba(57, 24, 108, .05) !important;
            }
            .slip-profile-box > div:first-child,
            .slip-profile-meta {
                width: 50% !important;
            }
            .slip-profile-meta {
                border-left: 1px solid #A78BFA !important;
                padding-left: 3mm !important;
            }
            .slip-profile-box img,
            .slip-profile-box .w-10,
            .slip-profile-box .w-12,
            .slip-profile-box [class*="w-["] {
                width: 8mm !important;
                height: 8mm !important;
            }
            .slip-profile-box span {
                font-size: 8pt !important;
                line-height: 1.2 !important;
            }
            .slip-earnings,
            .slip-deductions {
                margin-bottom: 1mm !important;
                border: 1px solid #A78BFA !important;
                border-radius: 2mm !important;
                overflow: hidden !important;
            }
            .slip-deductions {
                border-color: #F472B6 !important;
            }
            .slip-section-heading {
                min-height: 5.2mm !important;
                padding: 0.8mm 2mm !important;
                border-radius: 2mm !important;
                font-size: 8.5pt !important;
                grid-template-columns: repeat(6, minmax(0, 7.1667fr)) repeat(2, minmax(0, 7fr)) repeat(2, minmax(0, 9fr)) repeat(2, minmax(0, 12.5fr)) !important;
            }
            .slip-section-heading > div:not(:last-child) {
                border-right: 1px solid rgba(255,255,255,.35);
            }
            .slip-section-heading > div:last-child {
                justify-content: center !important;
                text-align: center !important;
            }
            .slip-section-rows > div {
                min-height: 4.2mm !important;
                padding-top: 0.35mm !important;
                padding-bottom: 0.35mm !important;
                font-size: 8pt !important;
                line-height: 1.2 !important;
                grid-template-columns: repeat(6, minmax(0, 7.1667fr)) repeat(2, minmax(0, 7fr)) repeat(2, minmax(0, 9fr)) repeat(2, minmax(0, 12.5fr)) !important;
            }
            .slip-section-rows > div:nth-child(even) {
                background: #F2F0FC !important;
            }
            .slip-section-rows > div > div:not(:last-child) {
                border-right: 1px solid #E8E1F3;
            }
            .slip-section-rows .rounded-full {
                width: 3mm !important;
                height: 3mm !important;
                flex: 0 0 3mm !important;
                border-radius: 0 !important;
                background: transparent !important;
            }
            .slip-section-rows svg {
                width: 2.4mm !important;
                height: 2.4mm !important;
            }
            .slip-earnings > div:last-child,
            .slip-deductions > div:last-child {
                min-height: 5.2mm !important;
                margin-top: 0.6mm !important;
                padding: 0.6mm 2mm !important;
                font-size: 8.5pt !important;
            }
            .slip-net-total {
                min-height: 7.5mm !important;
                margin: 1mm 0 !important;
                padding: 0.8mm 3mm !important;
                border-radius: 2mm !important;
            }
            .slip-net-total .w-7,
            .slip-net-total .w-8,
            .slip-net-total .w-9 {
                width: 5.5mm !important;
                height: 5.5mm !important;
            }
            .slip-net-total svg {
                width: 3.5mm !important;
                height: 3.5mm !important;
            }
            .slip-net-total span.text-lg,
            .slip-net-total span.text-xl,
            .slip-net-total span.text-2xl,
            .slip-net-total span.text-3xl {
                font-size: 13pt !important;
                line-height: 1 !important;
            }
            .slip-gaji-root .font-mono {
                font-family: 'Plus Jakarta Sans', Arial, sans-serif !important;
            }
            .slip-gaji-footer {
                min-height: 13.5mm !important;
                height: 13.5mm !important;
                margin-top: auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .slip-gaji-footer table {
                min-height: 13.5mm !important;
                height: 13.5mm !important;
            }
            .slip-gaji-footer td {
                padding-top: 0.5mm !important;
                padding-bottom: 0.5mm !important;
            }
            .slip-gaji-footer div[style*="height:14px"],
            .slip-gaji-footer div[style*="height:30px"] {
                height: 2.5mm !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
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
                    Pilih Simpan sebagai PDF dan aktifkan Grafik latar belakang agar warna ikut tercetak.
                </span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-800 text-white font-black text-xs shadow-md shadow-purple-900/20 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak PDF</span>
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
            <x-slip-gaji-card :slip="$slip" :hide-empty-deductions="true" />
        </div>
    </div>

    @if($autoPrint)
        <script>
            window.addEventListener('load', () => {
                const openPrintDialog = () => setTimeout(() => window.print(), 250);
                if (document.fonts?.ready) {
                    document.fonts.ready.then(openPrintDialog);
                } else {
                    openPrintDialog();
                }
            });
        </script>
    @endif
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tanda Terima Servis - {{ $servis->no_servis_frontend ?? $servis->no_servis }} - Soulmate Komputer</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .tanda-terima-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            @page {
                size: 210mm 148mm; /* A5 Landscape */
                margin: 6mm 10mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 font-sans antialiased text-gray-900 selection:bg-purple-500 selection:text-white">
    
    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="{{ route('servis.masuk') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all inline-flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Servis Masuk</span>
            </a>
            <span class="text-xs font-mono text-gray-500 font-semibold">| {{ $servis->no_servis_frontend ?? $servis->no_servis }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <button id="btnSavePdf" onclick="saveReceiptAsPdf()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-900/20 inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span id="txtSavePdf">Simpan PDF</span>
            </button>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition-all shadow-md inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Print</span>
            </button>
        </div>
    </div>

    <!-- Tanda Terima Container (A5 Landscape Layout / Clean Reference Design) -->
    <div id="receiptPrintArea" class="tanda-terima-container max-w-3xl mx-auto bg-white p-8 sm:p-10 rounded-3xl border border-gray-200 shadow-xl space-y-6 text-gray-900 text-xs sm:text-sm">
        
        <!-- Header: Store Identity & Service Ticket Info -->
        <div class="grid grid-cols-12 gap-4 items-start pb-4">
            
            <!-- Left: Logo & Toko -->
            <div class="col-span-7 flex items-start space-x-3.5">
                <img src="{{ asset('images/logo_bulat.jpg') }}" alt="Logo" class="w-14 h-14 rounded-2xl object-cover shrink-0 border border-gray-100 shadow-xs">
                <div>
                    <h1 class="text-xl font-black text-gray-900 tracking-tight leading-none">Soulmate Komputer</h1>
                    <p class="text-[11px] font-medium text-gray-600 mt-1 leading-snug">
                        {{ $servis->cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang' }}
                    </p>
                    <p class="text-[11px] font-semibold text-gray-700 mt-0.5">
                        Hp/WA: {{ $servis->cabang?->no_wa ?? '081995466255' }}
                    </p>
                </div>
            </div>

            <!-- Right: Meta Info (Tanggal, Jam, Kode Servis, Pelanggan) -->
            <div class="col-span-5 grid grid-cols-2 gap-2 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Tanggal :</span>
                    <span class="font-black text-gray-800 font-mono">{{ $servis->tanggal_masuk ? $servis->tanggal_masuk->format('d-m-Y') : '-' }}</span>
                    <span class="text-[11px] text-gray-500 font-mono block">{{ $servis->tanggal_masuk ? $servis->tanggal_masuk->format('H:i:s') : '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Pelanggan :</span>
                    <span class="font-black text-gray-900 block truncate" title="{{ $servis->customer?->nama ?? 'Pelanggan Umum' }}">
                        {{ $servis->customer?->nama ?? 'Pelanggan Umum' }}
                    </span>
                    <span class="text-[11px] font-mono text-gray-600 block">{{ $servis->customer?->no_hp ?? '-' }}</span>
                </div>
                <div class="col-span-2 pt-1 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-500 uppercase">Kode Servis:</span>
                    <span class="text-base font-black text-[#5E17EB] font-mono tracking-wider">
                        {{ $servis->no_servis_frontend ?? $servis->no_servis }}
                    </span>
                </div>
            </div>

        </div>

        <!-- Divider Strip -->
        <div class="border-t-2 border-b-2 border-dashed border-gray-400 py-1 text-center font-black tracking-widest text-[11px] uppercase text-gray-700 select-none">
            &middot;&middot;&middot;&middot;&middot;&middot;&middot; TANDA TERIMA BARANG SERVIS &middot;&middot;&middot;&middot;&middot;&middot;&middot;
        </div>

        <!-- Service Specs Grid -->
        <div class="grid grid-cols-2 gap-x-8 gap-y-4 pt-1">
            
            <!-- Row 1: Nama Barang / Unit & Keluhan -->
            <div class="border-b border-dashed border-gray-200 pb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Nama Barang / Unit :</span>
                <span class="text-sm font-black text-gray-900 mt-0.5 block">
                    {{ $servis->tipe_unit ?? ($servis->kategori_perangkat_label . ' (Tanpa Tipe)') }}
                </span>
                <span class="text-[11px] text-gray-500 font-medium">
                    Kategori: {{ $servis->kategori_perangkat_label }} 
                    @if($servis->serial_number) &bull; S/N: <strong class="font-mono text-gray-700">{{ $servis->serial_number }}</strong> @endif
                </span>
            </div>

            <div class="border-b border-dashed border-gray-200 pb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Permasalahan / Keluhan :</span>
                <span class="text-xs sm:text-sm font-bold text-gray-800 mt-0.5 block whitespace-pre-line leading-snug">
                    {{ $servis->keluhan ?: '-' }}
                </span>
            </div>

            <!-- Row 2: Kelengkapan & Catatan / Kondisi Fisik -->
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Kelengkapan Bawaan :</span>
                <span class="text-xs sm:text-sm font-bold text-gray-800 mt-0.5 block whitespace-pre-line leading-snug">
                    {{ $servis->kelengkapan ?: 'Hanya Unit' }}
                </span>
            </div>

            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Catatan / Kondisi Fisik :</span>
                <span class="text-xs sm:text-sm font-bold text-gray-800 mt-0.5 block whitespace-pre-line leading-snug">
                    {{ $servis->kondisi_barang ?: ($servis->catatan ?: '-') }}
                </span>
            </div>

        </div>

        <!-- Tanda Tangan Section -->
        <div class="grid grid-cols-2 gap-8 pt-4 text-center text-xs">
            <div>
                <p class="font-bold text-gray-500">Pelanggan,</p>
                <div class="h-14"></div>
                <p class="font-black text-gray-900 border-t border-gray-300 pt-1 inline-block min-w-[140px]">
                    {{ $servis->customer?->nama ?? 'Pelanggan' }}
                </p>
            </div>
            <div>
                <p class="font-bold text-gray-500">Hormat kami,</p>
                <div class="h-14"></div>
                <p class="font-black text-gray-900 border-t border-gray-300 pt-1 inline-block min-w-[140px]">
                    {{ $servis->pembuat?->name ?? 'Soulmate Komputer' }}
                </p>
            </div>
        </div>

        <!-- Box Footer: Public Web Tracking Info (Sesuai Referensi Gambar) -->
        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/90 flex items-center space-x-3.5">
            <div class="w-9 h-9 rounded-xl bg-purple-100 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
            </div>
            <div class="text-xs leading-relaxed text-gray-700">
                Pengecekan status servis dapat dilakukan di <strong class="text-[#0052FF] font-black">www.soulmatekomputer.com</strong> dengan memasukkan kode servis: <span class="font-mono font-black text-gray-900 bg-white px-2 py-0.5 rounded border border-gray-300 shadow-2xs">{{ $servis->no_servis_frontend ?? $servis->no_servis }}</span>
            </div>
        </div>

    </div>

    <!-- html2pdf.js Bundle for direct PDF rendering & auto-saving -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        function saveReceiptAsPdf() {
            const btn = document.getElementById('btnSavePdf');
            const txt = document.getElementById('txtSavePdf');
            const element = document.getElementById('receiptPrintArea');

            if (!element) return;

            if (txt) txt.textContent = 'Membuat PDF...';
            if (btn) btn.disabled = true;

            const opt = {
                margin:       [0.2, 0.25, 0.2, 0.25],
                filename:     'Tanda_Terima_Servis_' + '{{ Str::slug($servis->no_servis_frontend ?? $servis->no_servis) }}' + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
                jsPDF:        { unit: 'in', format: [8.27, 5.83], orientation: 'landscape' }
            };

            return html2pdf().set(opt).from(element).save().then(() => {
                if (txt) txt.innerHTML = 'Simpan PDF';
                if (btn) btn.disabled = false;
            }).catch(err => {
                console.error('PDF export error:', err);
                if (txt) txt.innerHTML = 'Simpan PDF';
                if (btn) btn.disabled = false;
            });
        }

        // Auto Save on load if requested via query param ?autosave=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autosave') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    saveReceiptAsPdf();
                }, 400);
            });
        } else if (urlParams.get('autoprint') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    window.print();
                }, 300);
            });
        }
    </script>
</body>
</html>

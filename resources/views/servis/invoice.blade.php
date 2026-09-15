<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faktur Servis - {{ $servis->no_servis_frontend ?? $servis->no_servis }} - Soulmate Komputer</title>
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
            .servis-invoice-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 9.5in !important;
                max-width: 9.5in !important;
            }
            @page {
                size: 9.5in 11in;
                margin: 6mm 10mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 font-sans antialiased text-gray-900 selection:bg-purple-500 selection:text-white">
    
    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="{{ route('servis.pengerjaan') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all inline-flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Servis Keluar</span>
            </a>
            <span class="text-xs font-mono text-gray-500 font-semibold">| {{ $servis->no_servis_frontend ?? $servis->no_servis }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <button id="btnSavePdf" onclick="saveInvoiceAsPdf()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-900/20 inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span id="txtSavePdf">Simpan PDF (9.5 &times; 11 Inci)</span>
            </button>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition-all shadow-md inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Print</span>
            </button>
        </div>
    </div>

    <!-- Faktur Container (Continuous 9.5x11 in layout / Clean Professional) -->
    <div id="invoicePrintArea" class="servis-invoice-container max-w-3xl mx-auto bg-white p-8 sm:p-10 rounded-3xl border border-gray-200 shadow-xl space-y-6 text-gray-900 text-xs sm:text-sm">
        
        <!-- Header: Store Identity & Service Invoice Info -->
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
                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Tgl Masuk / Ambil :</span>
                    <span class="font-black text-gray-800 font-mono block">{{ $servis->tanggal_masuk ? $servis->tanggal_masuk->format('d/m/y') : '-' }} &rarr; {{ $servis->tanggal_ambil ? $servis->tanggal_ambil->format('d/m/y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Pelanggan :</span>
                    <span class="font-black text-gray-900 block truncate" title="{{ $servis->customer?->nama ?? 'Pelanggan Umum' }}">
                        {{ $servis->customer?->nama ?? 'Pelanggan Umum' }}
                    </span>
                    <span class="text-[11px] font-mono text-gray-600 block">{{ $servis->customer?->no_hp ?? '-' }}</span>
                </div>
                <div class="col-span-2 pt-1 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-500 uppercase">Faktur Servis:</span>
                    <span class="text-base font-black text-[#5E17EB] font-mono tracking-wider">
                        {{ $servis->no_servis_frontend ?? $servis->no_servis }}
                    </span>
                </div>
            </div>

        </div>

        <!-- Divider Strip -->
        <div class="border-t-2 border-b-2 border-dashed border-gray-400 py-1 text-center font-black tracking-widest text-[11px] uppercase text-gray-700 select-none">
            &middot;&middot;&middot;&middot;&middot;&middot;&middot; FAKTUR PELUNASAN SERVIS &amp; PENGAMBILAN UNIT &middot;&middot;&middot;&middot;&middot;&middot;&middot;
        </div>

        <!-- Info Unit -->
        <div class="grid grid-cols-2 gap-4 pb-2 border-b border-dashed border-gray-200 text-xs">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Spesifikasi Perangkat :</span>
                <span class="text-sm font-black text-gray-900">{{ $servis->tipe_unit ?: $servis->kategori_perangkat_label }}</span>
                @if($servis->serial_number)
                    <span class="block text-[11px] text-gray-500 font-mono">S/N: {{ $servis->serial_number }}</span>
                @endif
            </div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Teknisi / Kasir :</span>
                <span class="font-bold text-gray-800">{{ $servis->teknisi?->name ?? '-' }} / {{ $servis->kasir?->name ?? 'Kasir Toko' }}</span>
            </div>
        </div>

        <!-- Tabel Rincian Jasa & Sparepart -->
        <div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b-2 border-gray-800 text-[11px] font-black uppercase text-gray-700">
                        <th class="py-2">No</th>
                        <th class="py-2">Rincian Jasa / Sparepart</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Harga</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($servis->details as $idx => $det)
                        <tr>
                            <td class="py-2.5 font-mono text-gray-500">{{ $idx + 1 }}</td>
                            <td class="py-2.5 font-bold text-gray-900">
                                {{ $det->nama_item }}
                                <span class="text-[10px] font-normal text-gray-500 ml-1">({{ $det->produk_id ? 'Sparepart' : 'Jasa Perbaikan' }})</span>
                            </td>
                            <td class="py-2.5 text-center font-mono font-bold">{{ $det->qty }}</td>
                            <td class="py-2.5 text-right font-mono">Rp {{ number_format($det->harga, 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right font-mono font-black text-gray-900">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 text-center text-gray-400 italic">Biaya Servis Standar</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-800 font-black text-sm">
                        <td colspan="4" class="py-2.5 text-right uppercase">TOTAL TAGIHAN :</td>
                        <td class="py-2.5 text-right font-mono text-[#5E17EB]">Rp {{ number_format($servis->total_biaya, 0, ',', '.') }}</td>
                    </tr>
                    @if($servis->bayar > 0)
                        <tr class="font-semibold text-xs text-gray-600">
                            <td colspan="4" class="py-1 text-right">Bayar ({{ strtoupper($servis->metode_pembayaran ?: 'Tunai') }}) :</td>
                            <td class="py-1 text-right font-mono">Rp {{ number_format($servis->bayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="font-semibold text-xs text-gray-600">
                            <td colspan="4" class="py-1 text-right">Kembalian :</td>
                            <td class="py-1 text-right font-mono">Rp {{ number_format($servis->kembalian, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <!-- Box Garansi Toko -->
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/90 flex items-start space-x-3.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div class="text-xs leading-relaxed text-gray-800">
                @if($servis->garansi_hari > 0 && $servis->garansi_sampai)
                    <strong class="text-emerald-800 font-black block">GARANSI TOKO RESMI ({{ $servis->garansi_hari }} HARI)</strong>
                    Unit ini mendapatkan garansi perbaikan hingga: <strong class="font-mono text-emerald-900 bg-white px-2 py-0.5 rounded border border-emerald-300">{{ $servis->garansi_sampai->format('d F Y') }}</strong>.
                    <span class="block text-[11px] text-gray-500 mt-0.5">Garansi tidak berlaku jika segel rusak, terkena cairan, atau kesalahan pemakaian/kelistrikan pengguna.</span>
                @else
                    <strong class="text-gray-700 font-bold block">KETENTUAN LAYANAN</strong>
                    Barang yang telah diambil telah dicek dan berfungsi normal di depan pelanggan.
                @endif
            </div>
        </div>

        <!-- Tanda Tangan Section -->
        <div class="grid grid-cols-2 gap-8 pt-4 text-center text-xs">
            <div>
                <p class="font-bold text-gray-500">Penerima / Pelanggan,</p>
                <div class="h-14"></div>
                <p class="font-black text-gray-900 border-t border-gray-300 pt-1 inline-block min-w-[140px]">
                    {{ $servis->customer?->nama ?? 'Pelanggan' }}
                </p>
            </div>
            <div>
                <p class="font-bold text-gray-500">Kasir / Teknisi,</p>
                <div class="h-14"></div>
                <p class="font-black text-gray-900 border-t border-gray-300 pt-1 inline-block min-w-[140px]">
                    {{ $servis->kasir?->name ?? ($servis->teknisi?->name ?? 'Soulmate Komputer') }}
                </p>
            </div>
        </div>

    </div>

    <!-- html2pdf.js Bundle for direct PDF rendering & auto-saving -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        function saveInvoiceAsPdf() {
            const btn = document.getElementById('btnSavePdf');
            const txt = document.getElementById('txtSavePdf');
            const element = document.getElementById('invoicePrintArea');

            if (!element) return;

            if (txt) txt.textContent = 'Membuat PDF...';
            if (btn) btn.disabled = true;

            const opt = {
                margin:       [0.2, 0.25, 0.2, 0.25],
                filename:     'Faktur_Servis_{{ Str::slug($servis->no_servis_frontend ?? $servis->no_servis) }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
                jsPDF:        { unit: 'in', format: [9.5, 11], orientation: 'portrait' }
            };

            return html2pdf().set(opt).from(element).save().then(() => {
                if (txt) txt.innerHTML = 'Simpan PDF (9.5 &times; 11 Inci)';
                if (btn) btn.disabled = false;
            }).catch(err => {
                console.error('PDF export error:', err);
                if (txt) txt.innerHTML = 'Simpan PDF (9.5 &times; 11 Inci)';
                if (btn) btn.disabled = false;
            });
        }

        // Auto Save on load if requested via query param ?autosave=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autosave') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    saveInvoiceAsPdf();
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

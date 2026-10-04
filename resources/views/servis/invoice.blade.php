<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faktur Servis - {{ $servis->no_servis_frontend ?? $servis->no_servis }} - Soulmate Komputer</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: portrait;
            margin: 4mm 15mm;
        }
        * {
            font-family: Arial, Helvetica, sans-serif !important;
        }
        @media print {
            html, body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .servis-invoice-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .servis-invoice-container tr,
            .servis-invoice-container .avoid-break {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-6 px-4 antialiased text-gray-900 selection:bg-purple-500 selection:text-white" style="font-family: Arial, Helvetica, sans-serif;">

    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-4xl mx-auto mb-4 flex items-center justify-between no-print bg-white p-3.5 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <button type="button"
                    onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('servis.keluar') }}'; }"
                    class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all inline-flex items-center space-x-1 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </button>
            <span class="text-xs font-mono text-gray-500 ">| {{ $servis->no_servis_frontend ?? $servis->no_servis }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition-all shadow-md inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Print</span>
            </button>
        </div>
    </div>

    <!-- Faktur Container (Strict 1-Page on 24x14 cm continuous form) -->
    <div id="invoicePrintArea" class="servis-invoice-container max-w-4xl mx-auto bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-md space-y-3.5 text-gray-900 text-xs">

        @php
            $tglSelesai = $servis->tanggal_selesai ?? ($servis->tanggal_ambil ?? $servis->updated_at);
        @endphp

        <!-- Header: Store Identity & Service Invoice Info -->
        <div class="grid grid-cols-12 gap-3 items-start pb-1">

            <!-- Left: Logo & Toko -->
            <div class="col-span-7 flex items-start space-x-3.5">
                <img src="{{ asset('images/logo_bulat.png') }}" alt="Logo" class="w-16 h-16 rounded-2xl object-cover shrink-0 border border-gray-100 shadow-xs">
                <div>
                    <h1 class="text-[19px] font-black text-gray-900 tracking-tight leading-tight">Soulmate Komputer</h1>
                    <p class="text-[11px] font-medium text-gray-600 mt-1 leading-snug">
                        {{ $servis->cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang' }}
                    </p>
                    <p class="text-[11px] font-semibold text-gray-700 mt-0.5">
                        WhatsApp: {{ format_whatsapp($servis->cabang?->no_wa ?? '081995466255') }}
                    </p>
                </div>
            </div>

            <!-- Right: Meta Info (Tanggal, Jam, Kode Servis, Pelanggan) -->
            <div class="col-span-5 text-xs">
                <table class="w-full">
                    <tr>
                        <td class="text-[11px]  text-gray-500 uppercase py-0.5 whitespace-nowrap w-28">Tanggal Selesai</td>
                        <td class="px-1 text-gray-400  w-3">:</td>
                        <td class=" text-gray-800 font-mono text-xs py-0.5">{{ $tglSelesai ? $tglSelesai->format('d/m/Y H:i:s') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-[11px]  text-gray-500 uppercase py-0.5 whitespace-nowrap">Pelanggan</td>
                        <td class="px-1 text-gray-400 ">:</td>
                        <td class=" text-gray-900 text-xs py-0.5">
                            <span class="truncate block max-w-[200px]" title="{{ $servis->customer?->nama ?? 'Pelanggan Umum' }}">
                                {{ $servis->customer?->nama ?? 'Pelanggan Umum' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-[11px]  text-gray-500 uppercase py-0.5 whitespace-nowrap">NO. FAKTUR SERVIS</td>
                        <td class="px-1 text-gray-400 ">:</td>
                        <td class="font-black text-[#5E17EB] font-mono text-[13px] py-0.5">
                            {{ $servis->no_servis_frontend ?? $servis->no_servis }}
                        </td>
                    </tr>
                </table>
            </div>

        </div>

        <!-- Divider Strip -->
        <div class="border-t border-b border-dashed border-gray-400 py-1 text-center font-bold tracking-widest text-[11px] uppercase text-gray-700 select-none">
            &middot;&middot;&middot;&middot;&middot; FAKTUR SERVIS &middot;&middot;&middot;&middot;&middot;
        </div>

        <!-- Info Unit -->
        <div class="grid grid-cols-2 gap-2 pb-1 border-b border-dashed border-gray-200 text-xs">
            <div>
                <span class="text-[11px]  text-gray-500 uppercase block leading-none">PERANGKAT :</span>
                <span class="text-xs  text-gray-900 mt-1 inline-block">{{ $servis->tipe_unit ?: $servis->kategori_perangkat_label }}</span>
            </div>
            <div class="text-right">
                <span class="text-[11px]  text-gray-400 uppercase block leading-none">Petugas Kasir :</span>
                <span class=" text-gray-800 text-xs mt-1 inline-block">{{ $servis->kasir?->name ?? ($servis->teknisi?->name ?? 'Soulmate Komputer') }}</span>
            </div>
        </div>

        <!-- Tabel Rincian Jasa & Sparepart -->
        <div>
            <table class="w-full text-left border-collapse text-xs table-fixed">
                <thead>
                    <tr class="border-b border-gray-800 text-xs  uppercase text-gray-700">
                        <th class="py-1.5 w-[5%]">No</th>
                        <th class="py-1.5 w-[35%]">Nama Barang / Jasa</th>
                        <th class="py-1.5 text-center w-[13%]">Garansi</th>
                        <th class="py-1.5 text-center w-[9%]">Qty</th>
                        <th class="py-1.5 text-right w-[19%]">Harga</th>
                        <th class="py-1.5 text-right w-[19%]">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($servis->details as $idx => $det)
                        <tr>
                            <td class="py-1 font-mono text-gray-500 text-xs align-top">{{ $idx + 1 }}</td>
                            <td class="py-1  text-gray-900 align-top">
                                {{ $det->nama_item }}
                            </td>
                            <td class="py-1 text-center font-mono text-xs text-gray-700 align-top">
                                {{ $det->garansi_label }}
                            </td>
                            <td class="py-1 text-center font-mono  align-top">{{ $det->qty }}</td>
                            <td class="py-1 text-right font-mono align-top whitespace-nowrap">Rp {{ number_format($det->harga, 0, ',', '.') }}</td>
                            <td class="py-1 text-right font-mono  text-gray-900 align-top whitespace-nowrap">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-2 text-center text-gray-400 italic">Biaya Servis Standar</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-800  text-xs">
                        <td colspan="5" class="py-2 text-right text-xs sm:text-sm font-black text-gray-900 pr-2">Grandtotal :</td>
                        <td class="py-2 text-right font-mono text-[#5E17EB] font-black text-xs sm:text-sm whitespace-nowrap">Rp {{ number_format($servis->total_biaya, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan Section -->
        <div class="grid grid-cols-2 gap-6 pt-1 text-center text-xs avoid-break">
            <div>
                <p class="font-medium text-gray-500">Pelanggan,</p>
                <div class="h-11"></div>
                <p class=" text-gray-900 border-t border-gray-300 pt-0.5 inline-block min-w-[140px]">
                    {{ $servis->customer?->nama ?? 'Pelanggan' }}
                </p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Kasir Toko,</p>
                <div class="h-11"></div>
                <p class=" text-gray-900 border-t border-gray-300 pt-0.5 inline-block min-w-[140px]">
                    {{ $servis->kasir?->name ?? ($servis->teknisi?->name ?? 'Soulmate Komputer') }}
                </p>
            </div>
        </div>

        <!-- Box Garansi Toko (Non Aktif) -->
        {{-- <div class="p-3 rounded-xl bg-emerald-50/90 border border-emerald-200 flex items-start space-x-3 avoid-break">
            <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0  mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div class="text-[11px] leading-snug text-gray-800">
                @php
                    $hasGaransi = $servis->details->where('garansi_hari', '>', 0)->count() > 0;
                @endphp
                @if($hasGaransi)
                    <strong class="text-emerald-800  block uppercase text-xs">KETENTUAN GARANSI PER ITEM</strong>
                    Garansi berlaku per item sesuai rincian pada tabel di atas terhitung sejak tanggal konfirmasi selesai.
                    <span class="block text-[10px] text-gray-500 mt-0.5">Garansi tidak berlaku jika nota hilang, segel rusak, terkena cairan, atau akibat kelalaian pemakaian/kelistrikan.</span>
                @else
                    <strong class="text-gray-700  block text-xs">KETENTUAN LAYANAN</strong>
                    Barang yang telah diambil telah dicek dan berfungsi normal di depan pelanggan (Non-Garansi).
                @endif
            </div>
        </div> --}}

    </div>

    <script>
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1' || window.self !== window.top) {
                setTimeout(() => {
                    window.focus();
                    window.print();
                }, 200);
            }
        });
    </script>
</body>
</html>

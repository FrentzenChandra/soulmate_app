@props(['slip'])

@php
    $service = app(\App\Services\SlipGajiService::class);
    $staf = $slip->user;

    $stafNama = $slip->nama_staf_snapshot ?: ($staf ? $staf->name : '-');
    $stafJabatan = $slip->bagian_snapshot ?: ($staf ? $staf->bagian : '-');

    $targetDate = \Carbon\Carbon::createFromDate($slip->periode_tahun, $slip->periode_bulan, 1);
    $periodeText = $slip->periode_text ?: $targetDate->translatedFormat('F Y');
    $periodeUpper = strtoupper($periodeText);

    // Tanggal lahir & usia
    $tglLahirObj = $slip->tanggal_lahir_snapshot ? \Carbon\Carbon::parse($slip->tanggal_lahir_snapshot) : ($staf && $staf->tanggal_lahir ? \Carbon\Carbon::parse($staf->tanggal_lahir) : null);
    $tanggalLahirFormatted = $tglLahirObj ? $tglLahirObj->translatedFormat('d F Y') : '-';
    $usiaFormatted = $tglLahirObj ? $service->formatUsia($tglLahirObj, $targetDate) : '-';

    // Tanggal mulai & masa kerja
    $tglMulaiObj = $slip->tanggal_mulai_bekerja_snapshot ? \Carbon\Carbon::parse($slip->tanggal_mulai_bekerja_snapshot) : ($staf && $staf->tanggal_mulai_bekerja ? \Carbon\Carbon::parse($staf->tanggal_mulai_bekerja) : null);
    $tanggalMulaiFormatted = $tglMulaiObj ? $tglMulaiObj->translatedFormat('F Y') : '-';
    $masaKerjaFormatted = $tglMulaiObj ? $service->formatMasaKerja($tglMulaiObj, $targetDate) : '-';

    // Item potongan
    $potonganItems = $slip->items ? $slip->items->filter(fn($it) => (float)$it->nominal > 0) : collect();
@endphp

<div class="relative bg-white rounded-2xl sm:rounded-3xl border border-gray-200/90 shadow-xl overflow-hidden p-5 sm:p-7 text-black select-none max-w-4xl mx-auto slip-gaji-root">

    <x-slip-gaji-header :periode="$periodeUpper" />

    <!-- ======================================================== -->
    <!-- 2. DATA KARYAWAN (METADATA CARD)                        -->
    <!-- ======================================================== -->
    <div class="my-3.5 bg-[#F8FAFC] border border-gray-200/80 rounded-xl sm:rounded-2xl p-3.5 sm:p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 sm:gap-5">
        <!-- Kolom Kiri: Avatar, Nama, Jabatan -->
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-purple-100 border border-purple-200 flex items-center justify-center text-[#5E17EB] shadow-2xs shrink-0">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                </svg>
            </div>
            <div class="space-y-0.5 text-xs">
                <div class="flex items-baseline space-x-2">
                    <span class="font-bold text-gray-500 w-24">Nama Karyawan</span>
                    <span class="text-gray-400">:</span>
                    <span class="font-black text-black text-xs sm:text-sm">{{ $stafNama }}</span>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="font-bold text-gray-500 w-24">Jabatan</span>
                    <span class="text-gray-400">:</span>
                    <span class="font-black text-black">{{ $stafJabatan ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tanggal Lahir, Periode, Masa Kerja -->
        <div class="space-y-0.5 text-xs sm:border-l sm:border-gray-200 sm:pl-5 w-full md:w-auto">
            <div class="flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-bold text-gray-500 w-20">Tanggal Lahir</span>
                <span class="text-gray-400">:</span>
                <span class="font-bold text-black">{{ $tanggalLahirFormatted }}</span>
                @if($usiaFormatted !== '-')
                    <span class="text-gray-600 font-semibold text-[10.5px]">({{ $usiaFormatted }})</span>
                @endif
            </div>

            <div class="flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold text-gray-500 w-20">Periode Gaji</span>
                <span class="text-gray-400">:</span>
                <span class="font-bold text-black">{{ $periodeText }}</span>
            </div>

            <div class="flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
                <span class="font-bold text-gray-500 w-20">Masa Kerja</span>
                <span class="text-gray-400">:</span>
                <span class="font-bold text-black">{{ $tanggalMulaiFormatted }}</span>
                @if($masaKerjaFormatted !== '-')
                    <span class="text-gray-600 font-semibold text-[10.5px]">({{ $masaKerjaFormatted }})</span>
                @endif
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. TABEL PENERIMAAN                                     -->
    <!-- ======================================================== -->
    <div class="space-y-1 mb-3.5">
        <!-- Header Bar Ungu-Pink dengan Ikon -->
        <div class="text-white rounded-xl px-3.5 py-2 grid grid-cols-12 gap-2 items-center text-[11px] font-black uppercase tracking-wider shadow-2xs"
             style="background: linear-gradient(to right, #5E17EB, #E6007A) !important;">
            <div class="col-span-6 flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>PENERIMAAN</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Hari</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                <span>Per Hari</span>
            </div>
            <div class="col-span-2 text-right flex items-center justify-end space-x-1">
                <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Jumlah</span>
            </div>
        </div>

        <!-- Baris Item Penerimaan -->
        <div class="divide-y divide-gray-100 text-xs">
            <!-- 1. Gaji Pokok - Person Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </span>
                    <span>Gaji Pokok</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}
                </div>
            </div>

            <!-- 2. Uang Makan - Cutlery Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-blue-100 text-[#0052FF] flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>
                    </span>
                    <span>Uang Makan</span>
                </div>
                <div class="col-span-2 text-center font-mono font-bold text-black">{{ $slip->uang_makan_hari ?: '-' }}</div>
                <div class="col-span-2 text-center font-mono font-bold text-black">
                    {{ (float)$slip->uang_makan_per_hari > 0 ? 'Rp ' . number_format($slip->uang_makan_per_hari, 0, ',', '.') : '-' }}
                </div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->uang_makan_total, 0, ',', '.') }}
                </div>
            </div>

            <!-- 3. Uang Transportasi - Car Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-blue-100 text-[#0052FF] flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>
                    </span>
                    <span>Uang Transportasi</span>
                </div>
                <div class="col-span-2 text-center font-mono font-bold text-black">{{ $slip->uang_transport_hari ?: '-' }}</div>
                <div class="col-span-2 text-center font-mono font-bold text-black">
                    {{ (float)$slip->uang_transport_per_hari > 0 ? 'Rp ' . number_format($slip->uang_transport_per_hari, 0, ',', '.') : '-' }}
                </div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->uang_transport_total, 0, ',', '.') }}
                </div>
            </div>

            <!-- 4. Tunjangan - Star Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-pink-100 text-[#E6007A] flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    </span>
                    <span>{{ $slip->tunjangan_nama ?: 'Tunjangan' }}</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    {{ (float)$slip->tunjangan_nominal > 0 ? 'Rp ' . number_format($slip->tunjangan_nominal, 0, ',', '.') : '-' }}
                </div>
            </div>

            <!-- 5. Bonus - Gift Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/></svg>
                    </span>
                    <span>Bonus</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    {{ (float)$slip->bonus_nominal > 0 ? 'Rp ' . number_format($slip->bonus_nominal, 0, ',', '.') : '-' }}
                </div>
            </div>

            <!-- 6. Lembur/Performa - Bar Chart Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/></svg>
                    </span>
                    <span>Lembur/Performa</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    {{ (float)$slip->lembur_performa_nominal > 0 ? 'Rp ' . number_format($slip->lembur_performa_nominal, 0, ',', '.') : '-' }}
                </div>
            </div>

            <!-- 7. Servisan Selesai - Wrench Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/></svg>
                    </span>
                    <span>Servisan Selesai</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    {{ (float)$slip->servisan_selesai_nominal > 0 ? 'Rp ' . number_format($slip->servisan_selesai_nominal, 0, ',', '.') : '-' }}
                </div>
            </div>
        </div>

        <!-- Strip Highlight Kuning Total Penghasilan Bruto (Sesuai Gambar) -->
        <div class="rounded-xl px-3.5 py-2 flex items-center justify-between shadow-2xs mt-1.5"
             style="background-color: #FEF3C7 !important; border: 1.5px solid #FDE68A !important;">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"></path>
                </svg>
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Penghasilan Bruto</span>
            </div>
            <div class="rounded-lg px-3 py-1 shadow-2xs"
                 style="background-color: #FACC15 !important; border: 1px solid #EAB308 !important;">
                <span class="font-mono font-black text-xs sm:text-sm text-black">
                    Rp {{ number_format($slip->total_bruto, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. TABEL PENGURANGAN (Hanya render jika ada atau jika bernilai 0 di mode draft) -->
    <!-- ======================================================== -->
    @if($potonganItems->isNotEmpty() || (float)$slip->total_pengurangan > 0 || !$slip->isFinal())
    <div class="space-y-1 mb-3.5">
        <!-- Header Bar Merah dengan Ikon -->
        <div class="text-white rounded-xl px-3.5 py-2 grid grid-cols-12 gap-2 items-center text-[11px] font-black uppercase tracking-wider shadow-2xs"
             style="background: linear-gradient(to right, #E11D48, #F43F5E) !important;">
            <div class="col-span-6 flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>PENGURANGAN</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Hari</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Per Hari</span>
            </div>
            <div class="col-span-2 text-right flex items-center justify-end space-x-1">
                <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Jumlah</span>
            </div>
        </div>

        <!-- Baris Item Pengurangan -->
        <div class="divide-y divide-gray-100 text-xs">
            @forelse($potonganItems as $item)
                <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                    <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                        <span class="w-4 h-4 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zM13 9V3.5L18.5 9H13z"/></svg>
                        </span>
                        <span>{{ $item->nama_item ?: ($item->kategori ?: 'Potongan') }}</span>
                    </div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-right font-mono font-black text-rose-600">
                        Rp {{ number_format($item->nominal, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="grid grid-cols-12 gap-2 items-center py-1.5 px-3 hover:bg-gray-50/60 transition-colors">
                    <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                        <span class="w-4 h-4 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zM13 9V3.5L18.5 9H13z"/></svg>
                        </span>
                        <span>Hutang</span>
                    </div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-right font-mono font-black text-rose-600">-</div>
                </div>
            @endforelse
        </div>

        <!-- Strip Highlight Merah Total Pengurangan -->
        <div class="rounded-xl px-3.5 py-2 flex items-center justify-between shadow-2xs mt-1.5"
             style="background-color: #FEE2E2 !important; border: 1.5px solid #FECACA !important;">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Pengurangan</span>
            </div>
            <span class="font-mono font-black text-xs sm:text-sm text-rose-600">
                Rp {{ number_format($slip->total_pengurangan, 0, ',', '.') }}
            </span>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 5. STRIP UTAMA TOTAL DITERIMA KARYAWAN (HIJAU VIBRANT)  -->
    <!-- ======================================================== -->
    <div class="rounded-xl sm:rounded-2xl px-4 sm:px-6 py-2.5 sm:py-3.5 flex items-center justify-between text-white shadow-md shadow-emerald-900/15 my-3.5"
         style="background: linear-gradient(to right, #059669, #0D9488) !important;">
        <div class="flex items-center space-x-2.5 sm:space-x-3">
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-white shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
            <div>
                <span class="text-xs sm:text-sm font-black uppercase tracking-wider block">TOTAL DITERIMA KARYAWAN</span>
            </div>
        </div>
        <div class="text-right">
            <span class="text-lg sm:text-2xl font-mono font-black text-white tracking-tight">
                Rp {{ number_format($slip->total_diterima, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <x-slip-gaji-footer :periode="$periodeText" />

</div>

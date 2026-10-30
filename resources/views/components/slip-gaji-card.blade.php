@props(['slip', 'hideEmptyDeductions' => false])

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

    // Item potongan & penerimaan tambahan
    $potonganItems = $slip->items ? $slip->items->filter(fn($it) => ($it->tipe ?? 'pengurangan') === 'pengurangan' && (float)$it->nominal > 0) : collect();
    $penerimaanTambahanItems = $slip->items ? $slip->items->filter(fn($it) => ($it->tipe ?? '') === 'penerimaan' && (float)$it->nominal > 0) : collect();
    $hasPengurangan = $potonganItems->isNotEmpty() || (float) $slip->total_pengurangan > 0;
@endphp

<div class="relative bg-white rounded-2xl sm:rounded-3xl border border-gray-200/90 shadow-xl overflow-hidden p-3.5 sm:p-5 text-black select-none max-w-4xl mx-auto slip-gaji-root">

    <x-slip-gaji-header :periode="$periodeUpper" />

    <!-- ======================================================== -->
    <!-- 2. DATA KARYAWAN (METADATA CARD)                        -->
    <!-- ======================================================== -->
    <div class="slip-profile-box my-2 bg-[#F1ECFF] border border-purple-200/80 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 flex flex-col md:flex-row items-start md:items-center justify-between gap-2.5 sm:gap-4">
        <!-- Kolom Kiri: Avatar, Nama, Jabatan -->
        <div class="flex items-center space-x-3">
            <x-slip-gaji-employee-avatar :user="$staf" :size="40" />
            <div class="space-y-0.5 text-xs">
                <div class="flex items-baseline space-x-2">
                    <span class="font-bold text-[#6D5AA4] w-24">Nama Karyawan</span>
                    <span class="font-black text-[#25115F] text-xs sm:text-sm">{{ $stafNama }}</span>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="font-bold text-[#6D5AA4] w-24">Jabatan</span>
                    <span class="font-black text-[#25115F]">{{ $stafJabatan ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tanggal Lahir, Periode, Masa Kerja -->
        <div class="slip-profile-meta space-y-0.5 text-xs sm:border-l sm:border-purple-300 sm:pl-4 w-full md:w-auto">
            <div class="flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-bold text-[#6D5AA4] w-20">Tanggal Lahir</span>
                <span class="font-bold text-[#25115F]">{{ $tanggalLahirFormatted }}</span>
                @if($usiaFormatted !== '-')
                    <span class="text-gray-600 font-semibold text-[10.5px]">({{ $usiaFormatted }})</span>
                @endif
            </div>

            <div class="flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-bold text-[#6D5AA4] w-20">Periode Gaji</span>
                <span class="font-bold text-[#25115F]">{{ $periodeText }}</span>
            </div>

            <div class="flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-purple-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4.5 8-11a8 8 0 10-16 0c0 6.5 8 11 8 11zm0-8a3 3 0 110-6 3 3 0 010 6z"></path>
                </svg>
                <span class="font-bold text-[#6D5AA4] w-20">Masa Kerja</span>
                <span class="font-bold text-[#25115F]">{{ $tanggalMulaiFormatted }}</span>
                @if($masaKerjaFormatted !== '-')
                    <span class="text-gray-600 font-semibold text-[10.5px]">({{ $masaKerjaFormatted }})</span>
                @endif
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. TABEL PENERIMAAN                                     -->
    <!-- ======================================================== -->
    <div class="slip-earnings space-y-1 mb-2">
        <!-- Header Bar Ungu-Pink dengan Ikon -->
        <div class="slip-section-heading text-white rounded-xl px-3 py-1.5 grid grid-cols-12 gap-2 items-center text-[10px] font-black uppercase tracking-wider shadow-2xs"
             style="background: linear-gradient(to right, #5E17EB, #E6007A) !important;">
            <div class="col-span-6 flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-white shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3C7.58 3 4 4.34 4 6s3.58 3 8 3 8-1.34 8-3-3.58-3-8-3zm-8 6v3c0 1.66 3.58 3 8 3s8-1.34 8-3V9c-1.53 1.15-4.62 2-8 2S5.53 10.15 4 9zm0 6v3c0 1.66 3.58 3 8 3s8-1.34 8-3v-3c-1.53 1.15-4.62 2-8 2s-6.47-.85-8-2z"></path>
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
        <div class="slip-section-rows divide-y divide-purple-100 text-xs">
            <!-- 1. Gaji Pokok - Person Icon -->
            <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                    <span class="w-3.5 h-3.5 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center shrink-0">
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
            <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                    <span class="w-3.5 h-3.5 rounded-full bg-blue-100 text-[#0052FF] flex items-center justify-center shrink-0">
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
            <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                    <span class="w-3.5 h-3.5 rounded-full bg-blue-100 text-[#0052FF] flex items-center justify-center shrink-0">
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

            <!-- Penerimaan Tambahan (Dinamis / Custom) -->
            @foreach($penerimaanTambahanItems as $pItem)
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                    <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                        <span class="w-3.5 h-3.5 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center shrink-0">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </span>
                        <span>{{ $pItem->nama_item }}</span>
                    </div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-right font-mono font-black text-black">
                        Rp {{ number_format($pItem->nominal, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach

            <!-- Fallback Legacy Slips (Jika belum ada di slip_gaji_items) -->
            @if($penerimaanTambahanItems->isEmpty())
                @if((float)$slip->bonus_nominal > 0)
                    <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                        <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/></svg>
                            </span>
                            <span>Bonus</span>
                        </div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-right font-mono font-black text-black">
                            Rp {{ number_format($slip->bonus_nominal, 0, ',', '.') }}
                        </div>
                    </div>
                @endif
                @if((float)$slip->lembur_performa_nominal > 0)
                    <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                        <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                            <span class="w-3.5 h-3.5 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/></svg>
                            </span>
                            <span>Lembur/Performa</span>
                        </div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-right font-mono font-black text-black">
                            Rp {{ number_format($slip->lembur_performa_nominal, 0, ',', '.') }}
                        </div>
                    </div>
                @endif
                @if((float)$slip->tunjangan_nominal > 0)
                    <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                        <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                            <span class="w-3.5 h-3.5 rounded-full bg-pink-100 text-[#E6007A] flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            </span>
                            <span>{{ $slip->tunjangan_nama ?: 'Tunjangan' }}</span>
                        </div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-right font-mono font-black text-black">
                            Rp {{ number_format($slip->tunjangan_nominal, 0, ',', '.') }}
                        </div>
                    </div>
                @endif
                @if((float)$slip->servisan_selesai_nominal > 0)
                    <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                        <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                            <span class="w-3.5 h-3.5 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/></svg>
                            </span>
                            <span>Servisan Selesai</span>
                        </div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                        <div class="col-span-2 text-right font-mono font-black text-black">
                            Rp {{ number_format($slip->servisan_selesai_nominal, 0, ',', '.') }}
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Strip Highlight Kuning Total Penghasilan Bruto (Sesuai Gambar) -->
        <div class="rounded-xl px-3 py-1 flex items-center justify-between shadow-2xs mt-1"
             style="background-color: #FFF1A8 !important; border: 1.5px solid #FDE68A !important;">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C7.58 2 4 3.34 4 5s3.58 3 8 3 8-1.34 8-3-3.58-3-8-3zm-8 6v3c0 1.66 3.58 3 8 3s8-1.34 8-3V8c-1.53 1.15-4.62 2-8 2S5.53 9.15 4 8zm0 6v3c0 1.66 3.58 3 8 3s8-1.34 8-3v-3c-1.53 1.15-4.62 2-8 2s-6.47-.85-8-2z"></path>
                </svg>
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Penghasilan Bruto</span>
            </div>
            <div class="rounded-lg px-2.5 py-0.5 shadow-2xs"
                 style="background-color: #FFDA55 !important; border: 1px solid #F4C735 !important;">
                <span class="font-mono font-black text-sm text-black">
                    Rp {{ number_format($slip->total_bruto, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    @if($hasPengurangan || !$hideEmptyDeductions)
    <!-- ======================================================== -->
    <!-- 4. TABEL PENGURANGAN -->
    <!-- ======================================================== -->
    <div class="slip-deductions space-y-1 mb-2">
        <!-- Header Bar Merah dengan Ikon -->
        <div class="slip-section-heading text-white rounded-xl px-3 py-1.5 grid grid-cols-12 gap-2 items-center text-[10px] font-black uppercase tracking-wider shadow-2xs"
             style="background: linear-gradient(to right, #E11D48, #F43F5E) !important;">
            <div class="col-span-6 flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 5v8m-4-4 4 4 4-4"></path>
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
        <div class="slip-section-rows divide-y divide-rose-100 text-xs">
            @forelse($potonganItems as $item)
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                    <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                        <span class="w-3.5 h-3.5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
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
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-3 hover:bg-gray-50/60 transition-colors">
                    <div class="col-span-6 flex items-center space-x-2 font-bold text-black">
                        <span class="w-3.5 h-3.5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
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
        <div class="rounded-xl px-3 py-1 flex items-center justify-between shadow-2xs mt-1"
             style="background-color: #FCE1F0 !important; border: 1.5px solid #FBCFE8 !important;">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zM13 9V3.5L18.5 9H13z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Pengurangan</span>
            </div>
            <span class="font-mono font-black text-xs text-rose-600">
                Rp {{ number_format($slip->total_pengurangan, 0, ',', '.') }}
            </span>
        </div>
    </div>
    @endif
    <!-- ======================================================== -->
    <!-- 5. STRIP UTAMA TOTAL DITERIMA KARYAWAN (HIJAU VIBRANT)  -->
    <!-- ======================================================== -->
    <div class="slip-net-total rounded-xl sm:rounded-2xl px-3 sm:px-4 py-1.5 sm:py-2 flex items-center justify-between text-white shadow-md shadow-emerald-900/15 my-2"
         style="background: linear-gradient(to right, #08A85D, #007A52) !important;">
        <div class="flex items-center space-x-2 sm:space-x-2.5">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-white shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
            <div>
                <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider block">TOTAL DITERIMA KARYAWAN</span>
            </div>
        </div>
        <div class="text-right border-l border-white/50 pl-3">
            <span class="text-lg sm:text-2xl font-mono font-black text-white tracking-tight">
                Rp {{ number_format($slip->total_diterima, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <x-slip-gaji-footer :periode="$periodeText" />

</div>

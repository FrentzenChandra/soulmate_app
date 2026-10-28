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

    // Cabang & Kota
    $namaCabang = $slip->cabang ? $slip->cabang->nama_cabang : 'Pangkalpinang';
@endphp

<div class="relative bg-white rounded-3xl border border-gray-200/90 shadow-xl overflow-hidden p-6 sm:p-8 text-black select-none max-w-4xl mx-auto">

    <!-- ======================================================== -->
    <!-- 1. HEADER (LOGO KIRI + BANNER GEOMETRIS KANAN)          -->
    <!-- ======================================================== -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100">
        <!-- Logo & Identitas Perusahaan -->
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#5E17EB] to-[#E6007A] flex items-center justify-center text-white shadow-md shadow-purple-900/20 shrink-0">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <div>
                <div class="flex items-center space-x-1.5 leading-none">
                    <span class="text-xl sm:text-2xl font-black text-[#5E17EB] tracking-wider">SOULMATE</span>
                    <span class="text-xl sm:text-2xl font-black text-black tracking-wider">KOMPUTER</span>
                </div>
                <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mt-1 block">
                    LAPTOP &bull; KOMPUTER &bull; PRINTER &bull; CCTV &bull; SERVICE
                </span>
            </div>
        </div>

        <!-- Banner Kanan SLIP GAJI KARYAWAN -->
        <div class="relative bg-gradient-to-r from-[#5E17EB] via-[#902BF5] to-[#E6007A] rounded-2xl px-5 py-3.5 flex items-center justify-between sm:space-x-6 text-white shadow-md shadow-purple-900/25">
            <div>
                <span class="text-lg sm:text-xl font-black tracking-wider block leading-tight">SLIP GAJI</span>
                <span class="text-[10px] font-bold text-white/90 uppercase tracking-widest block">KARYAWAN</span>
            </div>
            <div class="bg-white text-[#5E17EB] font-black text-xs px-3.5 py-1.5 rounded-full shadow-xs tracking-wider uppercase whitespace-nowrap">
                {{ $periodeUpper }}
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. DATA KARYAWAN (METADATA CARD)                        -->
    <!-- ======================================================== -->
    <div class="my-5 bg-[#F8FAFC] border border-gray-200/80 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <!-- Kolom Kiri: Avatar, Nama, Jabatan -->
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-full bg-purple-100 border border-purple-200 flex items-center justify-center text-[#5E17EB] font-black text-lg shadow-xs shrink-0">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                </svg>
            </div>
            <div class="space-y-1 text-xs">
                <div class="flex items-baseline space-x-2">
                    <span class="font-bold text-gray-500 w-24">Nama Karyawan</span>
                    <span class="text-gray-400">:</span>
                    <span class="font-black text-black text-sm">{{ $stafNama }}</span>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="font-bold text-gray-500 w-24">Jabatan</span>
                    <span class="text-gray-400">:</span>
                    <span class="font-black text-black">{{ $stafJabatan ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tanggal Lahir, Periode, Masa Kerja -->
        <div class="space-y-1 text-xs sm:border-l sm:border-gray-200 sm:pl-6 w-full md:w-auto">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-bold text-gray-500 w-20">Tanggal Lahir</span>
                <span class="text-gray-400">:</span>
                <span class="font-bold text-black">{{ $tanggalLahirFormatted }}</span>
                @if($usiaFormatted !== '-')
                    <span class="text-gray-600 font-semibold text-[11px]">({{ $usiaFormatted }})</span>
                @endif
            </div>

            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold text-gray-500 w-20">Periode Gaji</span>
                <span class="text-gray-400">:</span>
                <span class="font-bold text-black">{{ $periodeText }}</span>
            </div>

            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="font-bold text-gray-500 w-20">Masa Kerja</span>
                <span class="text-gray-400">:</span>
                <span class="font-bold text-black">{{ $tanggalMulaiFormatted }}</span>
                @if($masaKerjaFormatted !== '-')
                    <span class="text-gray-600 font-semibold text-[11px]">({{ $masaKerjaFormatted }})</span>
                @endif
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. TABEL PENERIMAAN                                     -->
    <!-- ======================================================== -->
    <div class="space-y-1.5 mb-5">
        <!-- Header Bar Ungu-Pink dengan Ikon -->
        <div class="bg-gradient-to-r from-[#5E17EB] to-[#E6007A] text-white rounded-xl px-4 py-2.5 grid grid-cols-12 gap-2 items-center text-xs font-black uppercase tracking-wider shadow-xs">
            <div class="col-span-6 flex items-center space-x-2">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>PENERIMAAN</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Hari</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                <span>Per Hari</span>
            </div>
            <div class="col-span-2 text-right flex items-center justify-end space-x-1">
                <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Jumlah</span>
            </div>
        </div>

        <!-- Baris Item Penerimaan -->
        <div class="divide-y divide-gray-100 text-xs">
            <!-- Gaji Pokok -->
            <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center text-[10px]">&bull;</span>
                    <span>Gaji Pokok</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}
                </div>
            </div>

            <!-- Uang Makan -->
            <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-blue-100 text-[#0052FF] flex items-center justify-center text-[10px]">&bull;</span>
                    <span>Uang Makan</span>
                </div>
                <div class="col-span-2 text-center font-mono font-bold text-black">{{ $slip->uang_makan_hari }}</div>
                <div class="col-span-2 text-center font-mono font-bold text-black">Rp {{ number_format($slip->uang_makan_per_hari, 0, ',', '.') }}</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->uang_makan_total, 0, ',', '.') }}
                </div>
            </div>

            <!-- Uang Transportasi -->
            <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-blue-100 text-[#0052FF] flex items-center justify-center text-[10px]">&bull;</span>
                    <span>Uang Transportasi</span>
                </div>
                <div class="col-span-2 text-center font-mono font-bold text-black">{{ $slip->uang_transport_hari }}</div>
                <div class="col-span-2 text-center font-mono font-bold text-black">Rp {{ number_format($slip->uang_transport_per_hari, 0, ',', '.') }}</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->uang_transport_total, 0, ',', '.') }}
                </div>
            </div>

            <!-- Tunjangan -->
            @if($slip->tunjangan_nominal > 0 || !empty($slip->tunjangan_nama))
            <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-pink-100 text-[#E6007A] flex items-center justify-center text-[10px]">&bull;</span>
                    <span>{{ $slip->tunjangan_nama ?: 'Tunjangan' }}</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->tunjangan_nominal, 0, ',', '.') }}
                </div>
            </div>
            @endif

            <!-- Bonus -->
            @if($slip->bonus_nominal > 0)
            <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px]">&bull;</span>
                    <span>Bonus</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->bonus_nominal, 0, ',', '.') }}
                </div>
            </div>
            @endif

            <!-- Lembur/Performa -->
            @if($slip->lembur_performa_nominal > 0)
            <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-[10px]">&bull;</span>
                    <span>Lembur/Performa</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->lembur_performa_nominal, 0, ',', '.') }}
                </div>
            </div>
            @endif

            <!-- Servisan Selesai -->
            @if($slip->servisan_selesai_nominal > 0)
            <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                    <span class="w-4 h-4 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center text-[10px]">&bull;</span>
                    <span>Servisan Selesai</span>
                </div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                <div class="col-span-2 text-right font-mono font-black text-black">
                    Rp {{ number_format($slip->servisan_selesai_nominal, 0, ',', '.') }}
                </div>
            </div>
            @endif
        </div>

        <!-- Strip Highlight Kuning Total Penghasilan Bruto -->
        <div class="bg-[#FEF3C7] border border-[#FDE68A] rounded-xl px-4 py-3 flex items-center justify-between shadow-2xs mt-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-700" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Penghasilan Bruto</span>
            </div>
            <span class="font-mono font-black text-base text-black">
                Rp {{ number_format($slip->total_bruto, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. TABEL PENGURANGAN                                    -->
    <!-- ======================================================== -->
    <div class="space-y-1.5 mb-5">
        <!-- Header Bar Merah dengan Ikon -->
        <div class="bg-gradient-to-r from-rose-600 to-rose-500 text-white rounded-xl px-4 py-2.5 grid grid-cols-12 gap-2 items-center text-xs font-black uppercase tracking-wider shadow-xs">
            <div class="col-span-6 flex items-center space-x-2">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>PENGURANGAN</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Hari</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center space-x-1">
                <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Per Hari</span>
            </div>
            <div class="col-span-2 text-right flex items-center justify-end space-x-1">
                <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Jumlah</span>
            </div>
        </div>

        <!-- Baris Item Pengurangan -->
        <div class="divide-y divide-gray-100 text-xs">
            @forelse($slip->items as $item)
                <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                    <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                        <span class="w-4 h-4 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-[10px]">&bull;</span>
                        <span>{{ $item->nama_item ?: ($item->kategori ?: 'Potongan') }}</span>
                    </div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-right font-mono font-black text-rose-600">
                        Rp {{ number_format($item->nominal, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="grid grid-cols-12 gap-2 items-center py-2 px-4 hover:bg-gray-50/60 transition-colors">
                    <div class="col-span-6 flex items-center space-x-2.5 font-bold text-black">
                        <span class="w-4 h-4 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-[10px]">&bull;</span>
                        <span>Hutang</span>
                    </div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-center text-gray-400 font-bold">-</div>
                    <div class="col-span-2 text-right font-mono font-black text-rose-600">
                        Rp 0
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Strip Highlight Merah Total Pengurangan -->
        <div class="bg-[#FEE2E2] border border-[#FECACA] rounded-xl px-4 py-3 flex items-center justify-between shadow-2xs mt-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-700" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Pengurangan</span>
            </div>
            <span class="font-mono font-black text-base text-rose-600">
                Rp {{ number_format($slip->total_pengurangan, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 5. STRIP UTAMA TOTAL DITERIMA KARYAWAN                  -->
    <!-- ======================================================== -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl px-6 py-4 flex items-center justify-between text-white shadow-lg shadow-emerald-900/20 my-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-white shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
            <div>
                <span class="text-xs sm:text-sm font-black uppercase tracking-wider block">TOTAL DITERIMA KARYAWAN</span>
                <span class="text-[10px] text-emerald-100 font-semibold block">Gaji bersih yang dibayarkan ke staf</span>
            </div>
        </div>
        <div class="text-right">
            <span class="text-xl sm:text-3xl font-mono font-black text-white tracking-tight">
                Rp {{ number_format($slip->total_diterima, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 6. FOOTER SLIP GAJI (UCAPAN TERIMA KASIH & TANDA TANGAN) -->
    <!-- ======================================================== -->
    <div class="relative pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-6 text-xs">
        <!-- Sisi Kiri: Ucapan Terima Kasih -->
        <div class="space-y-1 text-center sm:text-left z-10">
            <span class="block font-serif italic text-xl font-black text-[#5E17EB]">
                Terima Kasih
            </span>
            <p class="text-gray-600 font-semibold max-w-xs leading-relaxed text-[11px]">
                Atas dedikasi dari kerja keras Anda untuk kemajuan Soulmate Komputer.
            </p>
        </div>

        <!-- Tengah: Slogan Watermark -->
        <div class="hidden md:block text-center z-10">
            <span class="text-[11px] font-black tracking-[0.25em] text-gray-300 uppercase block">
                BERSAMA LEBIH MAJU
            </span>
        </div>

        <!-- Sisi Kanan: Area Tanda Tangan -->
        <div class="text-center z-10 min-w-[200px]">
            <p class="font-bold text-black text-[11px]">
                {{ $namaCabang }}, {{ $periodeText }}
            </p>
            <div class="h-14 flex items-center justify-center">
                <!-- Stempel atau Ruang TTD -->
                <span class="text-[10px] text-gray-300 italic">Tanda Tangan Digital / Sah</span>
            </div>
            <p class="font-black text-black border-t border-gray-300 pt-1 text-xs uppercase tracking-wider">
                Manajemen Soulmate Komputer
            </p>
        </div>

        <!-- Aksen Dekorasi Geometris Pojok Kiri Bawah -->
        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-gradient-to-tr from-[#5E17EB]/10 to-[#E6007A]/15 rounded-full blur-xl pointer-events-none"></div>
    </div>

</div>


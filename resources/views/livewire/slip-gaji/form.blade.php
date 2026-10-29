<div class="space-y-5 max-w-5xl mx-auto pb-12 text-black">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2 text-[11px] font-bold text-black uppercase tracking-wider mb-1">
                <span>HR &amp; KARYAWAN</span>
                <span>&bull;</span>
                <span class="text-[#5E17EB]">Gaji</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-black tracking-tight">
                {{ $slipId ? ($isFinal ? 'Detail Slip Gaji' : 'Edit Gaji Karyawan') : 'Input Gaji Karyawan' }}
            </h1>
            <p class="text-xs font-medium text-black mt-0.5">
                Masukkan komponen gaji karyawan sesuai dengan periode yang dipilih.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Periode Pill -->
            <div class="inline-flex items-center space-x-2 bg-gray-50 border border-gray-200 px-3.5 py-2 rounded-xl text-xs font-bold text-black">
                <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Periode: {{ \Carbon\Carbon::createFromDate($periodeTahun, $periodeBulan, 1)->translatedFormat('F Y') }}</span>
            </div>

            @if($slipId && $isFinal)
                @can('gaji.export_pdf')
                <a href="{{ route('slip-gaji.pdf', $slipId) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold bg-[#5E17EB] text-white hover:bg-purple-800 shadow-sm transition-all cursor-pointer"
                   title="Buka dialog cetak slip gaji">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Cetak PDF</span>
                </a>
                <a href="{{ route('slip-gaji.cetak', $slipId) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white border border-gray-200 text-black hover:bg-gray-50 transition-colors cursor-pointer"
                   title="Buka pratinjau sebelum mencetak">
                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Pratinjau Cetak</span>
                </a>
                @endcan
            @endif

            <a href="{{ route('slip-gaji.index') }}" wire:navigate
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-gray-200 text-black hover:bg-gray-50 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Alert Banner Kenaikan 6 Bulan -->
    @if($showKenaikanBanner)
    <div class="bg-pink-50 border border-[#E6007A]/40 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-xs">
        <div class="flex items-start space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#E6007A] text-white flex items-center justify-center font-black shrink-0 text-lg shadow-sm">
                !
            </div>
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-[#E6007A] block">
                    Pemberitahuan Kenaikan Gaji Berkala (6 Bulan)
                </span>
                <p class="text-sm font-bold text-black mt-0.5">
                    {{ $activeKenaikanNama }} sudah memasuki {{ $activeKenaikanMilestone }} bulan masa kerja. Berhak mendapatkan kenaikan gaji pokok sebesar <span class="font-extrabold text-[#E6007A]">Rp {{ number_format($activeKenaikanNominal, 0, ',', '.') }}</span>.
                </p>
                @if(!$canEditGajiPokok)
                    <p class="text-xs font-semibold text-black mt-1">
                        Hubungi Owner untuk menerapkan kenaikan gaji pokok ini ke slip gaji staf.
                    </p>
                @endif
            </div>
        </div>

        @if($canEditGajiPokok && !$isFinal)
        <div class="flex items-center flex-wrap gap-2 shrink-0 sm:self-center">
            <button type="button" wire:click="terapkanKenaikan"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#5E17EB] text-white hover:bg-purple-800 shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Terapkan Kenaikan</span>
            </button>
            <button type="button" wire:click="tandaiKenaikanDitangani"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-gray-300 text-black hover:bg-gray-100 transition-colors cursor-pointer">
                <span>Tandai Sudah Ditangani</span>
            </button>
        </div>
        @endif
    </div>
    @endif

    <!-- Alert Data Karyawan Belum Lengkap -->
    @if(!$stafDataLengkap && $selectedUserId)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center space-x-3 text-xs font-bold text-black">
        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span>Data Tanggal Lahir atau Tanggal Mulai Bekerja staf ini belum dilengkapi di Master Pengguna. Harap lengkapi profil staf agar usia dan masa kerja terhitung akurat.</span>
    </div>
    @endif

    @if($slipId && $isFinal && $slipModel)
        <!-- ============================================== -->
        <!-- DETAIL TAMPILAN 1:1 SLIP GAJI FINAL (MOCKUP)   -->
        <!-- ============================================== -->
        <div class="py-2">
            <x-slip-gaji-card :slip="$slipModel" />
        </div>
    @else
    <!-- Card 1: Data Karyawan -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-100">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0052FF] flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h2 class="text-sm font-black text-black">Data Karyawan</h2>
            </div>

            @if(!$isFinal)
            <div>
                <button type="button" wire:click="salinDariBulanLalu"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white border border-gray-200 text-black hover:bg-gray-50 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                    </svg>
                    <span>Salin dari Bulan Lalu</span>
                </button>
            </div>
            @endif
        </div>

        <div class="flex flex-col md:flex-row gap-5 items-start">
            <!-- Avatar Foto Karyawan -->
            <div class="shrink-0 flex items-center justify-center">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-purple-50 border-2 border-purple-100 flex items-center justify-center text-[#5E17EB] font-black text-xl shadow-xs">
                    @if($stafFotoUrl)
                        <img src="{{ $stafFotoUrl }}" alt="{{ $stafNama }}" class="w-full h-full object-cover">
                    @else
                        <span>{{ $stafInisial ?: 'ST' }}</span>
                    @endif
                </div>
            </div>

            <!-- Fields Form Karyawan -->
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                <!-- Nama Karyawan -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-black uppercase tracking-wider">
                        Nama Karyawan <span class="text-rose-500 font-bold">*</span>
                    </label>
                    @if(!$slipId && !$isFinal)
                        <select wire:model.live="selectedUserId"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold text-black focus:border-[#5E17EB] focus:bg-white transition-colors cursor-pointer">
                            @foreach($stafs as $staf)
                                <option value="{{ $staf->id }}">{{ $staf->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-black text-black">
                            {{ $stafNama }}
                        </div>
                    @endif
                </div>

                <!-- Jabatan -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-black uppercase tracking-wider">Jabatan</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold text-black">
                        {{ $stafJabatan ?: '-' }}
                    </div>
                </div>

                <!-- Tanggal Lahir -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-black uppercase tracking-wider">Tanggal Lahir</label>
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold text-black">
                        <span class="inline-flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>{{ $stafTanggalLahir }}</span>
                        </span>
                        @if($stafUsia !== '-')
                            <span class="bg-white border border-gray-200 text-black px-2 py-0.5 rounded-lg text-[10px] font-bold shadow-2xs">
                                {{ $stafUsia }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Periode Gaji -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-black uppercase tracking-wider">
                        Periode Gaji <span class="text-rose-500 font-bold">*</span>
                    </label>
                    @if(!$isFinal)
                    <div class="grid grid-cols-2 gap-2">
                        <select wire:model.live="periodeBulan"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold text-black focus:border-[#5E17EB] focus:bg-white cursor-pointer">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(2026, $m, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <select wire:model.live="periodeTahun"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold text-black focus:border-[#5E17EB] focus:bg-white cursor-pointer">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    @else
                    <div class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold text-black">
                        {{ \Carbon\Carbon::createFromDate($periodeTahun, $periodeBulan, 1)->translatedFormat('F Y') }}
                    </div>
                    @endif
                </div>

                <!-- Masa Kerja -->
                <div class="space-y-1 sm:col-span-2">
                    <label class="block text-[11px] font-bold text-black uppercase tracking-wider">Masa Kerja</label>
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold text-black">
                        <span class="inline-flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>{{ $stafTanggalMulaiBekerja }}</span>
                        </span>
                        @if($stafMasaKerja !== '-')
                            <span class="bg-white border border-gray-200 text-black px-2.5 py-0.5 rounded-lg text-[10px] font-bold shadow-2xs">
                                {{ $stafMasaKerja }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Komponen Gaji -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-xs space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-sm font-black text-black">Komponen Gaji</h2>
            </div>

            @if(!$isFinal)
            <button type="button" wire:click="hitungUlangDariAbsensi"
                    class="text-xs font-bold text-black hover:text-[#5E17EB] inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Sinkronkan Kehadiran ({{ $uangMakanHari }} Hari)</span>
            </button>
            @endif
        </div>

        <!-- 1. TABEL PENERIMAAN -->
        <div class="space-y-2">
            <div class="grid grid-cols-12 gap-2 text-[11px] font-black uppercase tracking-wider text-black px-2 pb-1">
                <div class="col-span-5 sm:col-span-6">Penerimaan</div>
                <div class="col-span-2 text-center">Hari</div>
                <div class="col-span-2 text-center">Per Hari</div>
                <div class="col-span-3 sm:col-span-2 text-right">Total</div>
            </div>

            <div class="space-y-1.5">
                <!-- Gaji Pokok -->
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-2 rounded-xl hover:bg-gray-50/50">
                    <div class="col-span-5 sm:col-span-6 text-xs font-bold text-black">Gaji Pokok</div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-3 sm:col-span-2 text-right">
                        @if($canEditGajiPokok && !$isFinal)
                            <x-input-ribuan wire:model.live="gajiPokok" />
                        @else
                            <div class="py-1.5 px-2.5 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-right">
                                Rp {{ number_format($gajiPokok, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Uang Makan -->
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-2 rounded-xl hover:bg-gray-50/50">
                    <div class="col-span-5 sm:col-span-6 text-xs font-bold text-black">Uang Makan</div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-center">
                            {{ $uangMakanHari }}
                        </span>
                    </div>
                    <div class="col-span-2 text-center">
                        @if($canEditGajiPokok && !$isFinal)
                            <x-input-ribuan wire:model.live="uangMakanPerHari" input-class="text-center" />
                        @else
                            <div class="py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-center">
                                Rp {{ number_format($uangMakanPerHari, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-span-3 sm:col-span-2 text-right">
                        <div class="py-1.5 px-2.5 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-right">
                            Rp {{ number_format($uangMakanTotal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Uang Transportasi -->
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-2 rounded-xl hover:bg-gray-50/50">
                    <div class="col-span-5 sm:col-span-6 text-xs font-bold text-black">Uang Transportasi</div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-center">
                            {{ $uangTransportHari }}
                        </span>
                    </div>
                    <div class="col-span-2 text-center">
                        @if($canEditGajiPokok && !$isFinal)
                            <x-input-ribuan wire:model.live="uangTransportPerHari" input-class="text-center" />
                        @else
                            <div class="py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-center">
                                Rp {{ number_format($uangTransportPerHari, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-span-3 sm:col-span-2 text-right">
                        <div class="py-1.5 px-2.5 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-right">
                            Rp {{ number_format($uangTransportTotal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Bonus -->
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-2 rounded-xl hover:bg-gray-50/50">
                    <div class="col-span-5 sm:col-span-6 text-xs font-bold text-black">Bonus</div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-3 sm:col-span-2 text-right">
                        @if(!$isFinal)
                            <x-input-ribuan wire:model.live="bonusNominal" />
                        @else
                            <div class="py-1.5 px-2.5 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-right">
                                Rp {{ number_format($bonusNominal, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Lembur/Performa -->
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-2 rounded-xl hover:bg-gray-50/50">
                    <div class="col-span-5 sm:col-span-6 text-xs font-bold text-black">Lembur/Performa</div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-3 sm:col-span-2 text-right">
                        @if(!$isFinal)
                            <x-input-ribuan wire:model.live="lemburPerformaNominal" />
                        @else
                            <div class="py-1.5 px-2.5 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-right">
                                Rp {{ number_format($lemburPerformaNominal, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Penerimaan Tambahan (Dinamis / Custom) -->
                @foreach($penerimaanItems as $index => $item)
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-2 rounded-xl hover:bg-gray-50/50" wire:key="penerimaan-row-{{ $index }}">
                    <div class="col-span-5 sm:col-span-6">
                        @if(!$isFinal)
                            <input type="text"
                                   wire:model.live.debounce.300ms="penerimaanItems.{{ $index }}.nama_item"
                                   class="w-full px-2.5 py-1.5 font-bold text-xs text-black bg-gray-50 border border-gray-200 rounded-lg focus:border-[#5E17EB] focus:bg-white transition-colors">
                        @else
                            <span class="text-xs font-bold text-black">{{ $item['nama_item'] ?: 'Penerimaan Tambahan' }}</span>
                        @endif
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-3 sm:col-span-2 flex items-center justify-end space-x-1.5">
                        @if(!$isFinal)
                            <div class="flex-1">
                                <x-input-ribuan wire:key="penerimaan-nominal-{{ $index }}"
                                                wire:model.live="penerimaanItems.{{ $index }}.nominal" />
                            </div>
                            <button type="button" wire:click="hapusPenerimaan({{ $index }})"
                                    class="p-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer shrink-0"
                                    title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        @else
                            <div class="py-1.5 px-2.5 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-right flex-1">
                                Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Tombol Tambah Penerimaan -->
            @if(!$isFinal)
            <div class="pt-1">
                <button type="button" wire:click="tambahPenerimaan"
                        class="text-xs font-bold text-black hover:bg-gray-50 border border-gray-200 px-3.5 py-1.5 rounded-xl inline-flex items-center space-x-1.5 cursor-pointer transition-colors shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Penerimaan</span>
                </button>
            </div>
            @endif

            <!-- Strip Highlight Total Penghasilan Bruto (Sesuai Gambar) -->
            <div class="bg-[#FEF3C7] border border-[#FDE68A] rounded-xl px-4 py-3 flex items-center justify-between mt-3">
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Penghasilan Bruto</span>
                <span class="font-mono font-black text-sm text-black">Rp {{ number_format($totalBruto, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- 2. TABEL PENGURANGAN -->
        <div class="space-y-2 pt-3 border-t border-gray-100">
            <div class="grid grid-cols-12 gap-2 text-[11px] font-black uppercase tracking-wider text-black px-2 pb-1">
                <div class="col-span-5 sm:col-span-6">Pengurangan</div>
                <div class="col-span-2 text-center">Hari</div>
                <div class="col-span-2 text-center">Per Hari</div>
                <div class="col-span-3 sm:col-span-2 text-right">Total</div>
            </div>

            <div class="space-y-1.5">
                @forelse($penguranganItems as $index => $item)
                <div class="grid grid-cols-12 gap-2 items-center py-1 px-2 rounded-xl hover:bg-gray-50/50" wire:key="potongan-row-{{ $index }}">
                    <div class="col-span-5 sm:col-span-6">
                        @if(!$isFinal)
                            <input type="text"
                                   wire:model.live.debounce.300ms="penguranganItems.{{ $index }}.nama_item"
                                   class="w-full px-2.5 py-1.5 font-bold text-xs text-black bg-gray-50 border border-gray-200 rounded-lg focus:border-[#5E17EB] focus:bg-white transition-colors">
                        @else
                            <span class="text-xs font-bold text-black">{{ $item['nama_item'] ?: 'Potongan' }}</span>
                        @endif
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="inline-block w-full py-1.5 px-2 bg-gray-100 rounded-lg text-xs font-bold text-black text-center">-</span>
                    </div>
                    <div class="col-span-3 sm:col-span-2 flex items-center justify-end space-x-1.5">
                        @if(!$isFinal)
                            <div class="flex-1">
                                <x-input-ribuan wire:key="potongan-nominal-{{ $index }}"
                                                wire:model.live="penguranganItems.{{ $index }}.nominal" />
                            </div>
                            <button type="button" wire:click="hapusPotongan({{ $index }})"
                                    class="p-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer shrink-0"
                                    title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        @else
                            <div class="py-1.5 px-2.5 bg-gray-100 rounded-lg text-xs font-mono font-bold text-black text-right flex-1">
                                Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-2 px-2 text-center text-xs font-medium text-black">
                    Belum ada potongan.
                </div>
                @endforelse
            </div>

            <!-- Tombol Tambah Potongan -->
            @if(!$isFinal)
            <div class="pt-1">
                <button type="button" wire:click="tambahPotongan"
                        class="text-xs font-bold text-black hover:bg-gray-50 border border-gray-200 px-3.5 py-1.5 rounded-xl inline-flex items-center space-x-1.5 cursor-pointer transition-colors shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Potongan</span>
                </button>
            </div>
            @endif

            <!-- Strip Highlight Total Pengurangan (Sesuai Gambar) -->
            <div class="bg-[#FEE2E2] border border-[#FECACA] rounded-xl px-4 py-3 flex items-center justify-between mt-3">
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Pengurangan</span>
                <span class="font-mono font-black text-sm text-black">Rp {{ number_format($totalPengurangan, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- 3. STRIP HIGHLIGHT TOTAL DITERIMA KARYAWAN -->
        <div class="bg-[#D1FAE5] border border-[#A7F3D0] rounded-xl px-4 py-3.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <span class="text-xs font-black text-black uppercase tracking-wider">Total Diterima Karyawan</span>
            </div>
            <span class="text-lg sm:text-2xl font-mono font-black text-black tracking-tight">
                Rp {{ number_format($totalDiterima, 0, ',', '.') }}
            </span>
        </div>

        <!-- 4. ACTION BUTTONS BOTTOM (Sesuai Gambar) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-gray-100">
            <div>
                @if(!$isFinal)
                <button type="button" wire:click="resetForm"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold bg-white border border-gray-200 text-black hover:bg-gray-50 transition-colors cursor-pointer shadow-2xs">
                    Reset
                </button>
                @endif
            </div>

            <div class="flex items-center flex-wrap gap-2.5">
                @if(!$isFinal)
                    <!-- Tombol Simpan Draft -->
                    @if($slipId ? auth()->user()->can('gaji.edit') : auth()->user()->can('gaji.create'))
                    <button type="button" wire:click="saveDraft"
                            wire:loading.attr="disabled"
                            wire:target="saveDraft"
                            class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-white border border-purple-200 text-[#5E17EB] hover:bg-purple-50 shadow-2xs transition-all cursor-pointer disabled:opacity-50">
                        <svg class="w-4 h-4 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveDraft">Simpan Draft</span>
                        <span wire:loading wire:target="saveDraft">Menyimpan...</span>
                    </button>
                    @endif

                    <!-- Tombol Simpan & Buat Slip -->
                    @can('gaji.finalize')
                    <button type="button"
                            wire:click="finalize"
                            wire:loading.attr="disabled"
                            wire:target="finalize"
                            wire:confirm="Yakin ingin memfinalkan slip gaji ini? Nilai dan profil staf akan dibekukan permanen dan hanya dapat dibuka kembali oleh Owner."
                            class="inline-flex items-center justify-center space-x-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-[#5E17EB] text-white hover:bg-purple-800 shadow-md shadow-purple-900/20 transition-all cursor-pointer disabled:opacity-50">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="finalize">Simpan &amp; Buat Slip</span>
                        <span wire:loading wire:target="finalize">Memproses...</span>
                    </button>
                    @endcan
                @else
                    <span class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Slip Gaji Telah Difinalkan &amp; Snapshot Dibekukan</span>
                    </span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

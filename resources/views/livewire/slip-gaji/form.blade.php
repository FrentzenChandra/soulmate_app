<div class="space-y-6 max-w-5xl mx-auto pb-12 text-black">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-black uppercase tracking-wider mb-1">
                <span>HR &amp; Karyawan</span>
                <span>&bull;</span>
                <span class="text-[#5E17EB]">Slip Gaji</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-black tracking-tight">
                {{ $slipId ? ($isFinal ? 'Detail Slip Gaji' : 'Edit Draft Slip Gaji') : 'Input Gaji Karyawan' }}
            </h1>
            <p class="text-xs font-medium text-black mt-0.5">
                Masukkan komponen gaji karyawan sesuai dengan periode yang dipilih.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Periode Pill -->
            <div class="inline-flex items-center space-x-2 bg-gray-100 border border-gray-300 px-3 py-2 rounded-xl text-xs font-bold text-black">
                <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Periode: {{ \Carbon\Carbon::createFromDate($periodeTahun, $periodeBulan, 1)->translatedFormat('F Y') }}</span>
            </div>

            @if($slipId && $isFinal)
                <!-- Tombol Cetak PDF -->
                @can('gaji.export_pdf')
                <a href="{{ route('slip-gaji.pdf', $slipId) }}" target="_blank"
                   class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold bg-[#0052FF] text-white hover:bg-blue-700 shadow-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Slip Gaji</span>
                </a>
                @endcan
            @endif

            <a href="{{ route('slip-gaji.index') }}" wire:navigate
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-black text-black hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Alert Banner Kenaikan 6 Bulan -->
    @if($showKenaikanBanner)
    <div class="bg-pink-50 border-2 border-[#E6007A] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-xs">
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
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-black text-black hover:bg-gray-100 transition-colors cursor-pointer">
                <span>Tandai Sudah Ditangani</span>
            </button>
        </div>
        @endif
    </div>
    @endif

    <!-- Alert Data Karyawan Belum Lengkap -->
    @if(!$stafDataLengkap && $selectedUserId)
    <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 flex items-center space-x-3 text-xs font-bold text-black">
        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span>Data Tanggal Lahir atau Tanggal Mulai Bekerja staf ini belum dilengkapi di Master Pengguna. Harap lengkapi profil staf agar usia dan masa kerja terhitung akurat.</span>
    </div>
    @endif

    <!-- Card 1: Data Karyawan -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-gray-200">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-[#0052FF]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-black">Data Karyawan</h2>
                    <p class="text-xs font-medium text-black">Identitas dan masa kerja staf yang menerima gaji</p>
                </div>
            </div>

            @if(!$isFinal)
            <div class="flex items-center gap-2">
                <button type="button" wire:click="salinDariBulanLalu"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white border border-black text-black hover:bg-gray-100 transition-colors cursor-pointer"
                        title="Salin Gaji Pokok, Tunjangan, dan Tarif Per Hari dari slip Final terakhir">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                    </svg>
                    <span>Salin dari Bulan Lalu</span>
                </button>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Nama Karyawan -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-black">
                    Nama Karyawan <span class="text-rose-500 font-bold">*</span>
                </label>
                @if(!$slipId && !$isFinal)
                    <select wire:model.live="selectedUserId"
                            class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-bold text-black focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] transition-colors cursor-pointer">
                        @foreach($stafs as $staf)
                            <option value="{{ $staf->id }}">{{ $staf->name }} ({{ $staf->bagian ?: 'Staf' }})</option>
                        @endforeach
                    </select>
                @else
                    <div class="flex items-center space-x-3 p-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-[#5E17EB] text-white flex items-center justify-center font-bold text-xs shrink-0">
                            {{ $stafInisial ?: 'ST' }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-extrabold text-black block truncate">{{ $stafNama }}</span>
                            <span class="text-xs font-semibold text-black block">{{ $stafJabatan }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Jabatan -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-black">Jabatan</label>
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold text-black">
                    {{ $stafJabatan ?: '-' }}
                </div>
            </div>

            <!-- Periode Gaji -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-black">
                    Periode Gaji <span class="text-rose-500 font-bold">*</span>
                </label>
                @if(!$isFinal)
                <div class="grid grid-cols-2 gap-2">
                    <select wire:model.live="periodeBulan"
                            class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-black focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] cursor-pointer">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(2026, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                    <select wire:model.live="periodeTahun"
                            class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-black focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] cursor-pointer">
                        @for($y = 2024; $y <= 2030; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @else
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold text-black">
                    {{ \Carbon\Carbon::createFromDate($periodeTahun, $periodeBulan, 1)->translatedFormat('F Y') }}
                </div>
                @endif
            </div>

            <!-- Tanggal Lahir & Usia -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-black">Tanggal Lahir</label>
                <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-black">
                    <span>{{ $stafTanggalLahir }}</span>
                    @if($stafUsia !== '-')
                        <span class="bg-blue-50 border border-blue-200 text-black px-2 py-0.5 rounded font-extrabold text-[11px]">{{ $stafUsia }}</span>
                    @endif
                </div>
            </div>

            <!-- Masa Kerja -->
            <div class="space-y-1.5 md:col-span-2">
                <label class="block text-xs font-bold text-black">Masa Kerja (Mulai Bekerja)</label>
                <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-black">
                    <span>{{ $stafTanggalMulaiBekerja }}</span>
                    @if($stafMasaKerja !== '-')
                        <span class="bg-purple-50 border border-purple-200 text-black px-2 py-0.5 rounded font-extrabold text-[11px]">{{ $stafMasaKerja }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Komponen Gaji -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-xs space-y-6">
        <div class="flex items-center space-x-2.5 pb-4 border-b border-gray-200">
            <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-200 flex items-center justify-center text-[#5E17EB]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-extrabold text-black">Komponen Gaji</h2>
                <p class="text-xs font-medium text-black">Rincian komponen penerimaan bruto dan pengurangan potongan</p>
            </div>
        </div>

        <!-- 1. TABEL PENERIMAAN -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-black">Penerimaan</span>
                @if(!$isFinal)
                <button type="button" wire:click="hitungUlangDariAbsensi"
                        class="text-[11px] font-bold text-black hover:underline inline-flex items-center space-x-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Sinkronkan Kehadiran ({{ $uangMakanHari }} Hari)</span>
                </button>
                @endif
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200 text-black font-extrabold">
                            <th class="py-2.5 px-3">Penerimaan</th>
                            <th class="py-2.5 px-3 w-20 text-center">Hari</th>
                            <th class="py-2.5 px-3 w-36 text-right">Per Hari</th>
                            <th class="py-2.5 px-3 w-40 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-semibold text-black">
                        <!-- Gaji Pokok -->
                        <tr>
                            <td class="py-2.5 px-3 font-bold">Gaji Pokok</td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2 px-3 text-right">
                                @if($canEditGajiPokok && !$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="gajiPokok"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg font-mono font-bold text-black block text-right">
                                        Rp {{ number_format($gajiPokok, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                        </tr>

                        <!-- Uang Makan -->
                        <tr>
                            <td class="py-2.5 px-3 font-bold">Uang Makan</td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold">{{ $uangMakanHari }}</td>
                            <td class="py-2 px-3 text-right">
                                @if($canEditGajiPokok && !$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="uangMakanPerHari"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg font-mono font-bold text-black block text-right">
                                        Rp {{ number_format($uangMakanPerHari, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold">
                                Rp {{ number_format($uangMakanTotal, 0, ',', '.') }}
                            </td>
                        </tr>

                        <!-- Uang Transportasi -->
                        <tr>
                            <td class="py-2.5 px-3 font-bold">Uang Transportasi</td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold">{{ $uangTransportHari }}</td>
                            <td class="py-2 px-3 text-right">
                                @if($canEditGajiPokok && !$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="uangTransportPerHari"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg font-mono font-bold text-black block text-right">
                                        Rp {{ number_format($uangTransportPerHari, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold">
                                Rp {{ number_format($uangTransportTotal, 0, ',', '.') }}
                            </td>
                        </tr>

                        <!-- Tunjangan -->
                        <tr>
                            <td class="py-2 px-3">
                                @if(!$isFinal)
                                    <input type="text"
                                           wire:model.live.debounce.300ms="tunjanganNama"
                                           class="w-full px-2.5 py-1.5 font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB]">
                                @else
                                    <span class="font-bold text-black">{{ $tunjanganNama }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2 px-3 text-right">
                                @if(!$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="tunjanganNominal"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="font-mono font-bold text-black">Rp {{ number_format($tunjanganNominal, 0, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Bonus -->
                        <tr>
                            <td class="py-2.5 px-3 font-bold">Bonus</td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2 px-3 text-right">
                                @if(!$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="bonusNominal"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="font-mono font-bold text-black">Rp {{ number_format($bonusNominal, 0, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Lembur/Performa (Pengecualian Eksplisit) -->
                        <tr>
                            <td class="py-2.5 px-3 font-bold">Lembur/Performa</td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2 px-3 text-right">
                                @if(!$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="lemburPerformaNominal"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="font-mono font-bold text-black">Rp {{ number_format($lemburPerformaNominal, 0, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Servisan Selesai -->
                        <tr>
                            <td class="py-2.5 px-3 font-bold">Servisan Selesai</td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2 px-3 text-right">
                                @if(!$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="servisanSelesaiNominal"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="font-mono font-bold text-black">Rp {{ number_format($servisanSelesaiNominal, 0, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-50/80 border-t-2 border-amber-300 font-extrabold text-black">
                            <td colspan="3" class="py-3 px-3 text-black font-black uppercase tracking-wider">
                                Total Penghasilan Bruto
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-black text-sm text-black">
                                Rp {{ number_format($totalBruto, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. TABEL PENGURANGAN -->
        <div class="space-y-2 pt-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-black">Pengurangan</span>
                @if(!$isFinal)
                <button type="button" wire:click="tambahPotongan"
                        class="text-xs font-bold text-black hover:bg-gray-100 border border-black px-2.5 py-1 rounded-lg inline-flex items-center space-x-1 cursor-pointer transition-colors">
                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Potongan</span>
                </button>
                @endif
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200 text-black font-extrabold">
                            <th class="py-2.5 px-3">Pengurangan</th>
                            <th class="py-2.5 px-3 w-20 text-center">Hari</th>
                            <th class="py-2.5 px-3 w-36 text-right">Per Hari</th>
                            <th class="py-2.5 px-3 w-40 text-right">Total</th>
                            @if(!$isFinal)
                                <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-semibold text-black">
                        @forelse($penguranganItems as $index => $item)
                        <tr>
                            <td class="py-2 px-3">
                                @if(!$isFinal)
                                    <div class="flex items-center space-x-2">
                                        <select wire:model.live="penguranganItems.{{ $index }}.kategori"
                                                class="bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-bold text-black cursor-pointer">
                                            <option value="Kasbon">Kasbon</option>
                                            <option value="Hutang">Hutang</option>
                                            <option value="Denda">Denda</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                        @if(($item['kategori'] ?? '') === 'Lainnya')
                                            <input type="text"
                                                   wire:model.live.debounce.300ms="penguranganItems.{{ $index }}.nama_item"
                                                   class="px-2 py-1.5 font-bold text-xs text-black bg-white border border-gray-300 rounded-lg flex-1">
                                        @endif
                                    </div>
                                @else
                                    <span class="font-bold text-black">{{ $item['nama_item'] ?? $item['kategori'] }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2 px-3 text-right">
                                @if(!$isFinal)
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-xs font-bold text-black">Rp</span>
                                        <input type="number" step="1000" min="0"
                                               wire:model.live.debounce.300ms="penguranganItems.{{ $index }}.nominal"
                                               class="w-full pl-8 pr-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-white border border-gray-300 rounded-lg focus:border-[#5E17EB]">
                                    </div>
                                @else
                                    <span class="font-mono font-bold text-black">Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            @if(!$isFinal)
                            <td class="py-2.5 px-3 text-center">
                                <button type="button" wire:click="hapusPotongan({{ $index }})"
                                        class="p-1 text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                                        title="Hapus Potongan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ !$isFinal ? 5 : 4 }}" class="py-3 px-3 text-center font-medium text-black">
                                Belum ada potongan untuk slip ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-rose-50/80 border-t-2 border-rose-300 font-extrabold text-black">
                            <td colspan="3" class="py-3 px-3 text-black font-black uppercase tracking-wider">
                                Total Pengurangan
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-black text-sm text-black">
                                Rp {{ number_format($totalPengurangan, 0, ',', '.') }}
                            </td>
                            @if(!$isFinal)
                                <td></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Diterima Karyawan -->
    <div class="bg-emerald-50 border-2 border-emerald-400 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
        <div>
            <span class="text-xs font-black uppercase tracking-wider text-black block">Ringkasan Pembayaran</span>
            <h3 class="text-lg sm:text-xl font-black text-black tracking-tight mt-0.5">TOTAL DITERIMA KARYAWAN</h3>
            <p class="text-xs font-medium text-black">Total Penghasilan Bruto dikurangi Total Pengurangan</p>
        </div>
        <div class="sm:text-right">
            <span class="text-2xl sm:text-4xl font-mono font-black text-black tracking-tight">
                Rp {{ number_format($totalDiterima, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Bilah Aksi Bawah -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-gray-200 shadow-xs">
        <div class="flex items-center space-x-2">
            @if(!$isFinal)
            <button type="button" wire:click="resetForm"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold bg-white border border-black text-black hover:bg-gray-100 transition-colors cursor-pointer">
                Reset
            </button>
            @endif

            @if($isFinal)
                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-black border border-emerald-300">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Slip Gaji Telah Difinalkan &amp; Snapshot Dibekukan</span>
                </span>
            @endif
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            @if(!$isFinal)
                <!-- Tombol Simpan Draft -->
                <button type="button" wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        wire:target="saveDraft"
                        class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-white border border-black text-black hover:bg-gray-100 shadow-xs transition-all cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveDraft">Simpan Draft</span>
                    <span wire:loading wire:target="saveDraft" class="inline-flex items-center space-x-1.5">
                        <svg class="animate-spin w-4 h-4 text-black" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>

                <!-- Tombol Simpan & Finalkan -->
                @can('gaji.finalize')
                <button type="button"
                        wire:click="finalize"
                        wire:loading.attr="disabled"
                        wire:target="finalize"
                        wire:confirm="Yakin ingin memfinalkan slip gaji ini? Nilai dan profil staf akan dibekukan permanen dan hanya dapat dibuka kembali oleh Owner."
                        class="inline-flex items-center justify-center space-x-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-[#5E17EB] text-white hover:bg-purple-800 shadow-md transition-all cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="finalize">Simpan &amp; Finalkan</span>
                    <span wire:loading wire:target="finalize" class="inline-flex items-center space-x-1.5">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Memfinalkan...</span>
                    </span>
                </button>
                @endcan
            @endif
        </div>
    </div>
</div>


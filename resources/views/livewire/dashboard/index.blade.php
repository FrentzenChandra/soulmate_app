<div class="space-y-6">

    @if($isManagement)
        @if($isHQ)
            <!-- ========================================================================= -->
            <!-- HQ VIEW: KANTOR PUSAT MULTI-BRANCH MONITORING                             -->
            <!-- ========================================================================= -->

            <!-- Welcome & Header Banner HQ -->
            <div class="card-custom bg-slate-900 text-white p-6 md:p-8 rounded-3xl relative z-0 overflow-hidden shadow-xl border border-slate-800"
                 style="background-image: url('{{ asset('images/hero_display.jpeg') }}'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-slate-950/85"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center space-x-2 bg-slate-800/90 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white mb-3 border border-slate-700">
                            <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-4 h-4 rounded object-contain">
                            <span>Soulmate Komputer Multi-Branch System</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                            Halo, {{ auth()->user()->name }}!
                        </h1>
                        <p class="text-slate-300 text-sm mt-1.5 max-w-xl leading-relaxed">
                            Mode <strong>Kantor Pusat (HQ)</strong> &mdash; Pantau performa finansial gabungan, kelola cabang, dan distribusi akun admin secara terpusat.
                        </p>
                    </div>

                    <div class="flex items-center space-x-3 shrink-0">
                        <button type="button"
                                wire:click="refreshDashboard"
                                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs transition-all cursor-pointer shadow-sm">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Refresh Data</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Top Quick Metrics Grid HQ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- HQ Metric 1: Total Cabang Aktif -->
                <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100 shadow-sm">
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Cabang Beroperasi</span>
                        <span class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-1 block">{{ $totalCabangAktif }} <span class="text-sm font-semibold text-gray-400">Cabang</span></span>
                        <span class="text-xs font-semibold text-emerald-600 mt-1 inline-flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            Semua status aktif
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>

                <!-- HQ Metric 2: Total Omzet Hari Ini (Global) -->
                <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100 shadow-sm">
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Omzet Gabungan Hari Ini</span>
                        <span class="text-xl md:text-2xl font-extrabold text-[#5E17EB] mt-1 block">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</span>
                        <span class="text-xs font-semibold text-purple-600 mt-1 inline-block">
                            {{ $totalTransaksiHariIni }} Transaksi lunas hari ini
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- HQ Metric 3: Servis Aktif Global -->
                <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100 shadow-sm">
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Servis Berjalan (Global)</span>
                        <span class="text-2xl md:text-3xl font-extrabold text-[#0052FF] mt-1 block">{{ $countTotalAktif }} <span class="text-sm font-semibold text-gray-400">Unit</span></span>
                        <span class="text-xs font-semibold text-blue-600 mt-1 inline-block">
                            {{ $countPengecekan }} Cek &bull; {{ $countPengerjaan }} Pengerjaan
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                </div>

                <!-- HQ Metric 4: Total Pengguna / Staff -->
                <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100 shadow-sm">
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Staff &amp; Admin</span>
                        <span class="text-2xl md:text-3xl font-extrabold text-[#E6007A] mt-1 block">{{ $totalUserAktif }} <span class="text-sm font-semibold text-gray-400">Akun</span></span>
                        <span class="text-xs font-semibold text-pink-600 mt-1 inline-block">
                            Superadmin, Teknisi, &amp; Kasir
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Branch Monitoring Cards Grid -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Monitoring Real-Time Antar Cabang</h2>
                        <p class="text-xs text-gray-400">Ringkasan status harian dan akses cepat ke tiap cabang</p>
                    </div>
                    <a href="{{ route('master.cabang') }}" class="text-xs font-bold text-[#5E17EB] hover:underline flex items-center space-x-1">
                        <span>Kelola Cabang</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @forelse($branchSnapshots as $b)
                        <div class="bg-white card-custom rounded-2xl p-5 border border-gray-100 flex flex-col justify-between hover:shadow-lg transition-all">
                            <div>
                                <div class="flex items-start justify-between pb-3 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="px-3.5 py-2 rounded-xl bg-purple-100/90 border border-purple-200 text-[#5E17EB] font-black flex items-center justify-center text-xs tracking-wider shadow-sm shrink-0">
                                            {{ $b['kode'] }}
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-extrabold text-gray-900 leading-tight">{{ $b['nama'] }}</h3>
                                            <span class="text-[11px] text-gray-400 block">{{ Str::limit($b['alamat'], 28) }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] flex items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                                        Aktif
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 my-4">
                                    <div class="bg-gray-50/70 p-3 rounded-xl">
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Omzet Hari Ini</span>
                                        <span class="text-sm font-extrabold text-gray-900 mt-0.5 block">Rp {{ number_format($b['omzet_hari_ini'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-gray-50/70 p-3 rounded-xl">
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Servis Aktif</span>
                                        <span class="text-sm font-extrabold text-blue-600 mt-0.5 block">{{ $b['servis_aktif'] }} Unit</span>
                                    </div>
                                </div>

                                <div class="text-xs space-y-1 text-gray-600 mb-4 pb-2 border-b border-gray-50">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-gray-400">Super Admin (PIC):</span>
                                        <span class="font-bold text-gray-800">{{ $b['superadmin'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-gray-400">Total Karyawan:</span>
                                        <span class="font-semibold text-gray-700">{{ $b['total_staff'] }} Orang</span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" wire:click="switchToBranch({{ $b['id'] }})"
                                    class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-xl bg-purple-50 hover:bg-[#5E17EB] text-[#5E17EB] hover:text-white font-bold text-xs transition-all group cursor-pointer">
                                <span>Masuk &amp; Kelola Cabang Ini</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    @empty
                        <div class="col-span-3 bg-white p-8 rounded-2xl text-center text-gray-400 card-custom">
                            Belum ada cabang terdaftar. <a href="{{ route('master.cabang') }}" class="text-[#5E17EB] font-bold underline">Tambah Cabang</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 7 Days Comparison Chart HQ (ApexCharts) -->
            <div class="bg-white card-custom rounded-2xl p-6 border border-gray-100 shadow-sm" 
                 x-data="hqComparisonChart(@js($chartSeries), @js($chartCategories))"
                 x-init="initChart()">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">Tren Penjualan Multi-Cabang (7 Hari Terakhir)</h2>
                        <p class="text-xs text-gray-400">Komparasi pendapatan harian dari seluruh cabang aktif</p>
                    </div>
                </div>
                <div x-ref="hqChart" class="w-full"></div>
            </div>

        @else
            <!-- ========================================================================= -->
            <!-- BRANCH VIEW (SUPERADMIN & OWNER IN BRANCH MODE) - 4 ROW ULTRA FAST        -->
            <!-- ========================================================================= -->

            <!-- Header Banner Cabang -->
            <div class="bg-white card-custom p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] border border-purple-100 flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                        {{ $activeCabang?->kode_cabang ?? 'POS' }}
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $activeCabang?->nama_cabang ?? 'Dashboard Cabang' }}</h1>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200/60">
                                Aktif
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            {{ $salamWaktu }}, <strong>{{ auth()->user()->name }}</strong> &bull; {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-2.5 shrink-0">
                    <button type="button"
                            wire:click="refreshDashboard"
                            wire:loading.attr="disabled"
                            wire:target="refreshDashboard"
                            class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 disabled:opacity-50 text-[#5E17EB] border border-purple-200 text-xs font-bold transition-all cursor-pointer">
                        <svg wire:loading.remove wire:target="refreshDashboard" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <svg wire:loading wire:target="refreshDashboard" class="animate-spin w-3.5 h-3.5 text-[#5E17EB]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Refresh Data</span>
                    </button>

                    @if($isOwner)
                        <button type="button" wire:click="switchToHq"
                                class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span>Ke Pusat (HQ)</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- BARIS 1: FINANSIAL BULAN BERJALAN (1 s/d Hari Ini) - EXACT TRANSAKSI FULL -->
            <!-- ========================================================================= -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                <!-- 1. Pendapatan (Ungu) -->
                <div class="bg-white p-4 rounded-2xl border border-purple-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between pb-2 border-b border-purple-100/80">
                        <span class="text-[10px] font-black text-purple-950 uppercase tracking-wider">Pendapatan</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-purple-50 text-[#5E17EB] border border-purple-200/60">
                            Bulan Ini
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="text-base sm:text-xl font-mono font-black text-[#5E17EB] tracking-tight block truncate">
                            Rp {{ number_format($finansial->total_pendapatan ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium mt-1 block">Tgl 1 &ndash; {{ now()->translatedFormat('d M') }}</span>
                    </div>
                </div>

                <!-- 2. HPP / Modal (Oren) -->
                <div class="bg-white p-4 rounded-2xl border border-amber-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between pb-2 border-b border-amber-100/80">
                        <span class="text-[10px] font-black text-amber-950 uppercase tracking-wider">HPP</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200/60">
                            Modal
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="text-base sm:text-xl font-mono font-black text-amber-600 tracking-tight block truncate">
                            Rp {{ number_format($finansial->total_hpp ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium mt-1 block">Total modal barang/servis</span>
                    </div>
                </div>

                <!-- 3. Nett / Laba (Hijau) -->
                <div class="bg-white p-4 rounded-2xl border border-emerald-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between pb-2 border-b border-emerald-100/80">
                        <span class="text-[10px] font-black text-emerald-950 uppercase tracking-wider">Nett</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            Margin
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="text-base sm:text-xl font-mono font-black {{ ($finansial->total_nett ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight block truncate">
                            Rp {{ number_format($finansial->total_nett ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium mt-1 block">Pendapatan bersih</span>
                    </div>
                </div>

                <!-- 4. Jumlah Lunas (Biru) -->
                <div class="bg-white p-4 rounded-2xl border border-blue-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between pb-2 border-b border-blue-100/80">
                        <span class="text-[10px] font-black text-blue-950 uppercase tracking-wider">Jumlah Lunas</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-50 text-[#0052FF] border border-blue-200/60">
                            Terbayar
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="text-base sm:text-xl font-mono font-black text-[#0052FF] tracking-tight block truncate">
                            Rp {{ number_format($finansial->total_lunas ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium mt-1 block">Sudah dibayar customer</span>
                    </div>
                </div>

                <!-- 5. Jumlah Bon (Merah) -->
                <div class="bg-white p-4 rounded-2xl border border-rose-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
                    <div class="flex items-center justify-between pb-2 border-b border-rose-100/80">
                        <span class="text-[10px] font-black text-rose-950 uppercase tracking-wider">Jumlah Bon</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200/60">
                            Piutang
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="text-base sm:text-xl font-mono font-black text-rose-600 tracking-tight block truncate">
                            Rp {{ number_format($finansial->total_bon ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium mt-1 block">Belum dibayar customer</span>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- BARIS 2: OPERASIONAL SERVIS (DENGAN FILTER BULAN & TAHUN)                 -->
            <!-- ========================================================================= -->
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full bg-[#5E17EB]"></div>
                        <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">Statistik Servis Toko</h2>
                    </div>

                    <!-- Filter Dropdown Bulan & Tahun -->
                    <div class="flex items-center space-x-2">
                        <select wire:model.live="filterBulanServis" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 shadow-xs focus:ring-1 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ sprintf('%02d', $m) }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>

                        <select wire:model.live="filterTahunServis" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 shadow-xs focus:ring-1 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Servis Masuk -->
                    <div class="bg-white card-custom p-5 rounded-2xl border border-blue-200/80 shadow-xs flex items-center justify-between hover:shadow-md transition-all">
                        <div>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Jumlah Servis Masuk</span>
                            <span class="text-2xl sm:text-3xl font-extrabold text-[#0052FF] mt-1 block">
                                {{ $servisStats->total_masuk ?? 0 }} <span class="text-sm font-semibold text-slate-400">Unit</span>
                            </span>
                            <span class="text-[11px] font-semibold text-blue-600 mt-1 block">
                                Periode {{ \Carbon\Carbon::create(null, (int)($filterBulanServis ?: now()->month), 1)->translatedFormat('F') }} {{ $filterTahunServis ?: now()->year }}
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] border border-blue-100 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>

                    <!-- Servis Selesai (Belum Diambil) -->
                    <div class="bg-white card-custom p-5 rounded-2xl border border-emerald-200/80 shadow-xs flex items-center justify-between hover:shadow-md transition-all">
                        <div>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Jumlah Servis Selesai</span>
                            <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1 block">
                                {{ $servisStats->selesai_belum_ambil ?? 0 }} <span class="text-sm font-semibold text-slate-400">Unit</span>
                            </span>
                            <span class="text-[11px] font-semibold text-emerald-600 mt-1 block">
                                Belum diambil customer
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Servis Batal (Belum Diambil) -->
                    <div class="bg-white card-custom p-5 rounded-2xl border border-rose-200/80 shadow-xs flex items-center justify-between hover:shadow-md transition-all">
                        <div>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Jumlah Servis Batal</span>
                            <span class="text-2xl sm:text-3xl font-extrabold text-rose-600 mt-1 block">
                                {{ $servisStats->batal_belum_ambil ?? 0 }} <span class="text-sm font-semibold text-slate-400">Unit</span>
                            </span>
                            <span class="text-[11px] font-semibold text-rose-600 mt-1 block">
                                Belum diambil customer
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- BARIS 3: SALDO REKENING (GROUPED PER SUMBER SALDO) + TOTAL SALDO BANNER   -->
            <!-- ========================================================================= -->
            <div class="bg-white card-custom p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                <!-- Header Saldo -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Saldo Rekening Soulmate Komputer</h2>
                            <p class="text-xs text-slate-400">Rincian saldo kas per akun bank operasional</p>
                        </div>
                    </div>

                    <a href="{{ route('saldo.index') }}" class="text-xs font-bold text-[#5E17EB] hover:underline flex items-center space-x-1">
                        <span>Buka Menu Saldo</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <!-- Sections Grouped per Sumber Saldo -->
                <div class="space-y-5">
                    @forelse($sumberSaldos as $sumber)
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2 text-xs font-black uppercase tracking-wider text-slate-500">
                                <span class="w-2 h-2 rounded-full {{ $loop->first ? 'bg-[#5E17EB]' : 'bg-[#0052FF]' }}"></span>
                                <span>{{ $sumber->nama }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
                                @forelse($sumber->rekenings as $rek)
                                    <div class="p-4 rounded-xl border border-slate-200/90 bg-slate-50/50 hover:bg-white hover:border-purple-200 hover:shadow-xs transition-all flex items-center space-x-3.5">
                                        <!-- Bank Avatar / Badge -->
                                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-extrabold text-xs text-[#5E17EB] shrink-0 shadow-2xs uppercase">
                                            {{ substr($rek->nama_bank, 0, 3) }}
                                        </div>
                                        <div class="truncate">
                                            <span class="text-xs font-black text-slate-900 block truncate">{{ $rek->nama_bank }}</span>
                                            <span class="text-[11px] text-slate-500 font-medium block truncate">{{ $rek->atas_nama ?: '-' }}</span>
                                            <span class="text-xs font-mono font-extrabold text-[#5E17EB] block mt-1">
                                                Rp {{ number_format($rek->saldo, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full py-3 text-center text-xs text-slate-400 italic">
                                        Belum ada rekening tercatat di {{ $sumber->nama }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-400">
                            Belum ada data Sumber Saldo.
                        </div>
                    @endforelse
                </div>

                <!-- Dark Solid Total Saldo Banner -->
                <div class="bg-slate-900 text-white p-5 rounded-2xl border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-11 h-11 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-amber-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <span class="text-sm font-black tracking-wider uppercase block text-white">TOTAL SALDO</span>
                            <span class="text-xs text-slate-400 block">Seluruh rekening kas Soulmate Komputer</span>
                        </div>
                    </div>
                    <div class="sm:text-right">
                        <span class="text-xl sm:text-2xl md:text-3xl font-mono font-black text-white tracking-tight">
                            Rp {{ number_format($totalSaldoKeseluruhan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- BARIS 4: PENDAPATAN REAL NET HARIAN (BULAN BERJALAN) - APEXCHARTS         -->
            <!-- ========================================================================= -->
            <div class="bg-white card-custom rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm"
                 x-data="dailyNettChart(@js($chartCategories), @js($chartSeriesData))"
                 x-init="initChart()">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2 mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Pendapatan Real Net Harian (Bulan Berjalan)</h2>
                            <p class="text-xs text-slate-400">Grafik margin/laba bersih per hari dari Transaksi FULL</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                            1 {{ now()->translatedFormat('M') }} &ndash; {{ now()->translatedFormat('j M Y') }}
                        </span>
                    </div>
                </div>

                <div x-ref="dailyChart" class="w-full"></div>
            </div>

        @endif

    @else
        <!-- ========================================================================= -->
        <!-- STAFF PURE WELCOME & SERVICE EXCELLENCE DASHBOARD                         -->
        <!-- ========================================================================= -->

        <!-- 1. Hero Vision & Welcome Banner (Solid Clean Anti-AI Slop) -->
        <div class="card-custom bg-slate-900 text-white p-6 sm:p-8 md:p-10 rounded-3xl relative z-0 overflow-hidden shadow-lg border border-slate-800"
             style="background-image: url('{{ asset('images/hero_display.jpeg') }}'); background-size: cover; background-position: center;">

            <!-- Solid Clean Dark Overlay (Anti-AI Slop) -->
            <div class="absolute inset-0 bg-slate-950/85"></div>

            <div class="relative z-10 max-w-3xl">
                <!-- Brand Badge, Date & Role -->
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <div class="inline-flex items-center space-x-2 bg-slate-800/90 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white border border-slate-700">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-4 h-4 rounded object-contain">
                        <span>Soulmate Komputer</span>
                    </div>
                    <span class="inline-flex items-center space-x-1.5 bg-slate-800/90 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-300 border border-slate-700">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    </span>
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold bg-[#5E17EB] text-white">
                        {{ auth()->user()->roles->first()?->name ?? 'Staff Operasional' }}
                    </span>
                </div>

                <!-- Main Greeting -->
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    {{ $salamWaktu }}, {{ auth()->user()->name }}!
                </h1>

                <!-- Service Excellence Tag -->
                <div class="mt-3 inline-flex items-center space-x-2 px-3 py-1 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 text-xs font-bold">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span>Standar Layanan: Pelayanan Nomor 1 &amp; Kepuasan Pelanggan</span>
                </div>

                <!-- Mission Description -->
                <p class="text-slate-300 text-sm sm:text-base mt-3.5 leading-relaxed font-normal max-w-2xl">
                    Setiap pelanggan adalah prioritas utama kita. Sambut dengan ramah, berikan diagnosa yang jujur dan transparan, serta pastikan setiap solusi selesai tepat waktu dengan kualitas terbaik.
                </p>

                <!-- Status Indicator & Operational Tip -->
                <div class="mt-6 flex flex-wrap items-center gap-3 text-xs">
                    <span class="inline-flex items-center space-x-2 bg-emerald-950/80 text-emerald-400 border border-emerald-800 px-3.5 py-1.5 rounded-xl font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Sistem Aktif &amp; Terhubung</span>
                    </span>
                    <span class="text-slate-400 bg-slate-800/90 px-3.5 py-1.5 rounded-xl border border-slate-700 font-medium">
                        Pilih menu di sidebar sebelah kiri untuk navigasi tugas harian Anda
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. 3 Pilar Utama "Pelayanan Nomor 1" (Solid White Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white card-custom p-6 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Senyum, Salam &amp; Sapa</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Sambut setiap pelanggan dengan ramah dan tulus. Dengarkan setiap keluhan perangkat dengan penuh perhatian dan empati.
                    </p>
                </div>
            </div>

            <div class="bg-white card-custom p-6 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Diagnosa Teliti &amp; Jujur</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Cek fisik dan fungsi unit dengan teliti. Berikan transparansi biaya dan solusi yang tepat tanpa manipulasi.
                    </p>
                </div>
            </div>

            <div class="bg-white card-custom p-6 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] flex items-center justify-center shrink-0 border border-pink-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Solutif &amp; Tuntas Cepat</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Selesaikan perbaikan dengan standar mutu terbaik dan berikan update status pengerjaan yang jelas kepada pelanggan.
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Komitmen 5S Standar Pelayanan Soulmate (Solid Dark Slate Card) -->
        <div class="bg-slate-900 text-white card-custom p-6 rounded-2xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start space-x-4">
                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0 text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-white">Budaya 5S Pelayanan Soulmate</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        <span class="font-bold text-amber-300">Senyum &bull; Salam &bull; Sapa &bull; Sopan &bull; Solutif</span> &mdash; Jadikan setiap interaksi pelanggan sebagai pengalaman yang memuaskan dan berkesan.
                    </p>
                </div>
            </div>
            <div class="shrink-0 flex items-center space-x-2">
                <span class="text-xs font-bold bg-[#5E17EB] text-white px-3.5 py-1.5 rounded-xl border border-purple-400/20">
                    Pelayanan Nomor 1
                </span>
            </div>
        </div>

    @endif

</div>

@push('scripts')
<script>
    function hqComparisonChart(series, categories) {
        return {
            chart: null,
            initChart() {
                this.$nextTick(() => {
                    if (typeof ApexCharts === 'undefined' || !this.$refs.hqChart) return;
                    const options = {
                        chart: {
                            type: 'area',
                            height: 320,
                            fontFamily: 'Plus Jakarta Sans, sans-serif',
                            toolbar: { show: false }
                        },
                        colors: ['#5E17EB', '#0052FF', '#E6007A', '#10B981'],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2.5 },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        series: series,
                        xaxis: {
                            categories: categories,
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(val) {
                                    return 'Rp ' + (val / 1000).toLocaleString('id-ID') + 'k';
                                }
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return 'Rp ' + Number(val).toLocaleString('id-ID');
                                }
                            }
                        },
                        grid: {
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4
                        }
                    };

                    if (this.chart) {
                        this.chart.destroy();
                    }
                    this.chart = new ApexCharts(this.$refs.hqChart, options);
                    this.chart.render();
                });
            }
        };
    }

    function dailyNettChart(categories, seriesData) {
        return {
            chart: null,
            initChart() {
                this.$nextTick(() => {
                    if (typeof ApexCharts === 'undefined' || !this.$refs.dailyChart) return;
                    const options = {
                        chart: {
                            type: 'bar',
                            height: 320,
                            fontFamily: 'Plus Jakarta Sans, sans-serif',
                            toolbar: { show: false }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 6,
                                columnWidth: '50%',
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        },
                        colors: ['#5E17EB'],
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                if (!val || val === 0) return '';
                                const absVal = Math.abs(val);
                                if (absVal >= 1000000) {
                                    return (val / 1000000).toFixed(1) + ' Jt';
                                } else if (absVal >= 1000) {
                                    return (val / 1000).toFixed(0) + 'k';
                                }
                                return val;
                            },
                            offsetY: -20,
                            style: {
                                fontSize: '10px',
                                fontWeight: 700,
                                colors: ['#5E17EB']
                            }
                        },
                        series: [{
                            name: 'Real Net Harian',
                            data: seriesData
                        }],
                        xaxis: {
                            categories: categories,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    colors: '#64748b'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(val) {
                                    return 'Rp ' + (val / 1000000).toFixed(0) + ' Jt';
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    colors: '#64748b'
                                }
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return 'Rp ' + Number(val).toLocaleString('id-ID');
                                }
                            }
                        },
                        grid: {
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4
                        }
                    };

                    if (this.chart) {
                        this.chart.destroy();
                    }
                    this.chart = new ApexCharts(this.$refs.dailyChart, options);
                    this.chart.render();
                });
            }
        };
    }
</script>
@endpush

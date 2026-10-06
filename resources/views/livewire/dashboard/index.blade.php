<div class="space-y-6">

    @if($isManagement)
        <!-- Welcome & Header Banner -->
        <div class="card-custom bg-cover bg-center text-white p-6 md:p-8 rounded-3xl relative z-0 overflow-hidden shadow-xl shadow-purple-950/20"
         style="background-image: url('{{ asset('images/hero_display.jpeg') }}');">
        
        <!-- Dark Brand Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#0E1726]/95 via-[#5E17EB]/90 to-[#E6007A]/80"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center space-x-2 bg-white/15 backdrop-blur-md px-3.5 py-1 rounded-full text-xs font-bold text-white mb-3 border border-white/20">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-4 h-4 rounded object-contain">
                    <span>{{ $isHQ ? 'Soulmate Komputer Multi-Branch System' : 'Soulmate Komputer' }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                    Halo, {{ auth()->user()->name }}!
                </h1>
                <p class="text-purple-100 text-sm mt-1.5 max-w-xl leading-relaxed">
                    @if($isHQ)
                        Mode <strong>Kantor Pusat (HQ)</strong> — Pantau performa finansial gabungan, kelola cabang, dan distribusi akun admin secara terpusat.
                    @elseif($isOwner)
                        Mode Operasional <strong>{{ $activeCabang?->nama_cabang ?? 'Cabang' }}</strong> — Role: <strong>Owner</strong>.
                    @else
                        Selamat datang di Sistem Operasional Soulmate Komputer — Role: <strong>{{ auth()->user()->role_name }}</strong>.
                    @endif
                </p>
            </div>

            @if($isOwner)
                <div class="flex items-center space-x-3 shrink-0">
                    @if(!$isHQ)
                        <button wire:click="switchToHq" 
                                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-white text-purple-900 font-bold text-xs hover:bg-purple-50 transition-all shadow-lg cursor-pointer">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span>Kembali ke Pusat (HQ)</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <!-- Decorative Circles -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-pink-500/20 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Top Quick Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @if($isHQ)
            <!-- HQ Metric 1: Total Cabang Aktif -->
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Cabang Beroperasi</span>
                    <span class="text-2xl md:text-3xl font-extrabold text-[#1A1D20] mt-1 block">{{ $totalCabangAktif }} <span class="text-sm font-semibold text-gray-400">Cabang</span></span>
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
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
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
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Servis Berjalan (Global)</span>
                    <span class="text-2xl md:text-3xl font-extrabold text-[#0052FF] mt-1 block">{{ $countTotalAktif }} <span class="text-sm font-semibold text-gray-400">Unit</span></span>
                    <span class="text-xs font-semibold text-blue-600 mt-1 inline-block">
                        {{ $countPengecekan }} Cek • {{ $countPengerjaan }} Pengerjaan
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
            </div>

            <!-- HQ Metric 4: Total Pengguna / Staff -->
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Staff & Admin</span>
                    <span class="text-2xl md:text-3xl font-extrabold text-[#E6007A] mt-1 block">{{ $totalUserAktif }} <span class="text-sm font-semibold text-gray-400">Akun</span></span>
                    <span class="text-xs font-semibold text-pink-600 mt-1 inline-block">
                        Superadmin, Teknisi, & Kasir
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>

        @else
            <!-- Branch Metric 1: Servis Antrian -->
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Servis Dalam Antrian</span>
                    <span class="text-2xl md:text-3xl font-extrabold text-[#1A1D20] mt-1 block">{{ $countTotalAktif }} <span class="text-sm font-medium text-gray-400">Unit</span></span>
                    <span class="text-xs font-semibold text-blue-600 mt-1 inline-block">
                        {{ $countPengecekan }} Cek • {{ $countPengerjaan }} Proses
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
            </div>

            <!-- Branch Metric 2: Servis Siap Diambil -->
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Siap Diambil / Ditagih</span>
                    <span class="text-2xl md:text-3xl font-extrabold text-[#10B981] mt-1 block">{{ $countSelesai }} <span class="text-sm font-medium text-gray-400">Unit</span></span>
                    <span class="text-xs font-semibold text-emerald-600 mt-1 inline-block">
                        Siap penyerahan & kasir
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#10B981] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Branch Metric 3: Omzet Hari Ini -->
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Omzet Hari Ini</span>
                    <span class="text-xl md:text-2xl font-extrabold text-[#1A1D20] mt-1 block">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</span>
                    <span class="text-xs font-semibold text-purple-600 mt-1 inline-block">
                        {{ $totalTransaksiHariIni }} Transaksi tercatat
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Branch Metric 4: Omzet Bulan Ini -->
            <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Omzet Bulan Ini</span>
                    <span class="text-xl md:text-2xl font-extrabold text-[#E6007A] mt-1 block">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</span>
                    <span class="text-xs font-semibold text-pink-600 mt-1 inline-block">
                        {{ $totalTransaksiBulanIni }} Transaksi tercatat
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        @endif
    </div>

    @if($isHQ)
        <!-- ============================================== -->
        <!-- HQ VIEW: BRANCH SNAPSHOT MONITORING & CHARTS   -->
        <!-- ============================================== -->

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
                            <!-- Card Header -->
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

                            <!-- Metrics per Branch -->
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

                            <!-- Staff & PIC Info -->
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

                        <!-- Action Button -->
                        <button wire:click="switchToBranch({{ $b['id'] }})"
                                class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-xl bg-purple-50 hover:bg-[#5E17EB] text-[#5E17EB] hover:text-white font-bold text-xs transition-all group cursor-pointer">
                            <span>Masuk & Kelola Cabang Ini</span>
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

        <!-- 7 Days Comparison Chart (ApexCharts) -->
        <div class="bg-white card-custom rounded-2xl p-6 border border-gray-100" 
             x-data="{
                initChart() {
                    let series = @json($chartSeries);
                    let categories = @json($chartCategories);
                    let options = {
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
                                    return 'Rp ' + val.toLocaleString('id-ID');
                                }
                            }
                        },
                        grid: {
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4,
                        }
                    };
                    let chart = new ApexCharts(this.$refs.hqChart, options);
                    chart.render();
                }
             }"
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
        <!-- ============================================== -->
        <!-- BRANCH VIEW: ANTRIAN SERVIS & OPERASIONAL      -->
        <!-- ============================================== -->

        <!-- Live Smart TV Display Queue Widget -->
        <div class="bg-[#0E1726] text-white card-custom p-6 rounded-2xl border border-gray-800 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-800 gap-2">
                <div class="flex items-center space-x-3">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
                    <div>
                        <h2 class="text-base font-extrabold tracking-tight">MONITOR ANTRIAN SERVIS TOKO (LIVE SMART TV)</h2>
                        <p class="text-xs text-gray-400">Sinkronisasi status pengerjaan unit secara real-time</p>
                    </div>
                </div>
                <span class="text-xs bg-gray-800 text-gray-300 font-semibold px-3 py-1 rounded-full border border-gray-700">
                    CCTV Supervised
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
                <!-- Laptop Card -->
                <div class="bg-gray-900/90 border border-blue-500/30 rounded-xl p-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-800 text-blue-400 font-bold text-sm">
                        <span>LAPTOP</span>
                        <span class="text-xs bg-blue-500/10 px-2 py-0.5 rounded text-blue-300">{{ $antrianLaptop['cek'] + $antrianLaptop['prg'] + $antrianLaptop['ok'] }} Total</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center pt-3">
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">CEK</span>
                            <span class="text-xl font-extrabold text-[#0052FF]">{{ $antrianLaptop['cek'] }}</span>
                        </div>
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">PRG</span>
                            <span class="text-xl font-extrabold text-[#E6007A]">{{ $antrianLaptop['prg'] }}</span>
                        </div>
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">OK</span>
                            <span class="text-xl font-extrabold text-[#10B981]">{{ $antrianLaptop['ok'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Printer Card -->
                <div class="bg-gray-900/90 border border-pink-500/30 rounded-xl p-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-800 text-pink-400 font-bold text-sm">
                        <span>PRINTER</span>
                        <span class="text-xs bg-pink-500/10 px-2 py-0.5 rounded text-pink-300">{{ $antrianPrinter['cek'] + $antrianPrinter['prg'] + $antrianPrinter['ok'] }} Total</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center pt-3">
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">CEK</span>
                            <span class="text-xl font-extrabold text-[#0052FF]">{{ $antrianPrinter['cek'] }}</span>
                        </div>
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">PRG</span>
                            <span class="text-xl font-extrabold text-[#E6007A]">{{ $antrianPrinter['prg'] }}</span>
                        </div>
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">OK</span>
                            <span class="text-xl font-extrabold text-[#10B981]">{{ $antrianPrinter['ok'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Komputer PC Card -->
                <div class="bg-gray-900/90 border border-indigo-500/30 rounded-xl p-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-800 text-indigo-400 font-bold text-sm">
                        <span>KOMPUTER / PC</span>
                        <span class="text-xs bg-indigo-500/10 px-2 py-0.5 rounded text-indigo-300">{{ $antrianKomputer['cek'] + $antrianKomputer['prg'] + $antrianKomputer['ok'] }} Total</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center pt-3">
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">CEK</span>
                            <span class="text-xl font-extrabold text-[#0052FF]">{{ $antrianKomputer['cek'] }}</span>
                        </div>
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">PRG</span>
                            <span class="text-xl font-extrabold text-[#E6007A]">{{ $antrianKomputer['prg'] }}</span>
                        </div>
                        <div class="bg-gray-800/60 p-2 rounded-lg">
                            <span class="text-[10px] text-gray-400 block font-bold">OK</span>
                            <span class="text-xl font-extrabold text-[#10B981]">{{ $antrianKomputer['ok'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Columns: Recent Servis & Recent Penjualan -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Antrian Servis Terbaru -->
            <div class="bg-white card-custom rounded-2xl p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">Servis Masuk Terbaru</h2>
                        <p class="text-xs text-gray-400">Daftar unit servis yang baru diterima</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 uppercase font-bold text-[10px]">
                                <th class="py-2.5">No. Servis</th>
                                <th class="py-2.5">Perangkat</th>
                                <th class="py-2.5">Pelanggan</th>
                                <th class="py-2.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-gray-100">
                            @forelse($recentServis as $srv)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-3 font-bold text-[#5E17EB]">{{ $srv->no_servis }}</td>
                                    <td class="py-3">
                                        <span class="font-bold text-gray-900 block">{{ $srv->tipe_unit ?? ucfirst($srv->kategori_perangkat) }}</span>
                                        <span class="text-[11px] text-gray-400">{{ ucfirst($srv->kategori_perangkat) }}</span>
                                    </td>
                                    <td class="py-3 text-gray-700 font-medium">{{ $srv->customer?->nama ?? '-' }}</td>
                                    <td class="py-3">
                                        @if($srv->status === 'pengecekan')
                                            <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-bold text-[10px]">Pengecekan</span>
                                        @elseif($srv->status === 'pengerjaan')
                                            <span class="px-2.5 py-1 rounded-full bg-pink-50 text-pink-700 font-bold text-[10px]">Pengerjaan</span>
                                        @elseif($srv->status === 'selesai')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]">Siap Diambil</span>
                                        @elseif($srv->status === 'sudah_dibayar')
                                            <span class="px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 font-bold text-[10px]">Lunas</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 font-bold text-[10px]">{{ $srv->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data servis masuk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transaksi Kasir Terbaru -->
            <div class="bg-white card-custom rounded-2xl p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">Transaksi Penjualan Terbaru</h2>
                        <p class="text-xs text-gray-400">Histori transaksi kasir & tagihan servis</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 uppercase font-bold text-[10px]">
                                <th class="py-2.5">No. Transaksi</th>
                                <th class="py-2.5">Tipe</th>
                                <th class="py-2.5">Total</th>
                                <th class="py-2.5">Metode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentPenjualan as $pj)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-3 font-bold text-gray-900">{{ $pj->no_transaksi }}</td>
                                    <td class="py-3">
                                        @if($pj->tipe_transaksi === 'pembayaran_servis')
                                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[10px]">Tagih Servis</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-pink-50 text-pink-700 font-bold text-[10px]">Kasir Produk</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-bold text-gray-900">Rp {{ number_format($pj->total, 0, ',', '.') }}</td>
                                    <td class="py-3 uppercase font-semibold text-gray-500 text-[10px]">{{ $pj->metode_bayar }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada transaksi kasir.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Low Stock Warning Table -->
        @if(count($lowStocks) > 0)
            <div class="bg-white card-custom rounded-2xl p-6 border-l-4 border-amber-500">
                <div class="flex items-center space-x-2 mb-3">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h2 class="text-sm font-extrabold text-gray-900">Peringatan: Stok Produk Menipis (<= 5 Unit)</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach($lowStocks as $stk)
                        <div class="bg-amber-50/60 border border-amber-200/80 rounded-xl p-3">
                            <span class="text-[11px] font-bold text-gray-900 line-clamp-1">{{ $stk->produk?->nama_produk }}</span>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-[10px] text-gray-500 font-medium">{{ $isHQ ? ($stk->cabang?->nama_cabang ?? 'Toko') : ($stk->produk?->kategori?->nama_kategori ?? 'Umum') }}</span>
                                <span class="text-xs font-extrabold text-amber-700 bg-amber-200/60 px-2 py-0.5 rounded-full">{{ $stk->jumlah }} Pcs</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
    @else
        <!-- ========================================================================= -->
        <!-- STAFF WELCOME & VISION DASHBOARD: TEKNISI, ADMIN, KASIR, BENDAHARA, DLL   -->
        <!-- ========================================================================= -->

        <!-- 1. Hero Vision & Welcome Banner -->
        <div class="card-custom bg-cover bg-center text-white p-6 sm:p-8 md:p-10 rounded-3xl relative z-0 overflow-hidden shadow-xl shadow-purple-950/20"
             style="background-image: url('{{ asset('images/hero_display.jpeg') }}');">

            <!-- Dark Brand Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#0E1726]/95 via-[#0E1726]/85 to-[#5E17EB]/75"></div>

            <div class="relative z-10 max-w-3xl">
                <!-- Brand Badge, Date & Role -->
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <div class="inline-flex items-center space-x-2 bg-white/15 backdrop-blur-md px-3.5 py-1 rounded-full text-xs font-bold text-white border border-white/20">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-4 h-4 rounded object-contain">
                        <span>Soulmate Komputer</span>
                    </div>
                    <span class="inline-flex items-center space-x-1.5 bg-purple-500/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold text-purple-200 border border-purple-400/30">
                        <svg class="w-3.5 h-3.5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-200 border border-blue-400/30">
                        {{ auth()->user()->roles->first()?->name ?? 'Staff Operasional' }}
                    </span>
                </div>

                <!-- Main Greeting -->
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    {{ $salamWaktu }}, <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-purple-100 to-pink-200">{{ auth()->user()->name }}</span>!
                </h1>

                <!-- Vision Message -->
                <p class="text-purple-100/90 text-sm sm:text-base mt-3 leading-relaxed">
                    Selamat datang di sistem operasional <strong>Soulmate Komputer</strong>. Bersama kita wujudkan visi menjadi pusat layanan servis &amp; retail IT terbesar, modern, dan terpercaya!
                </p>

                <!-- Status Indicator -->
                <div class="mt-5 flex items-center space-x-3 text-xs text-purple-200/90">
                    <span class="inline-flex items-center space-x-1.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1 rounded-full font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Sistem Aktif &amp; Terhubung</span>
                    </span>
                    <span class="text-purple-300/70 hidden sm:inline">&bull; Siap melayani pelanggan dengan sepenuh hati</span>
                </div>
            </div>

            <!-- Decorative Glow Elements -->
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-[#5E17EB]/30 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-12 -top-12 w-64 h-64 bg-[#0052FF]/20 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- 2. Quick Action Shortcuts Grid (Berdasarkan Menu Sidebar & Spatie @can) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-gray-900 tracking-tight flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-[#5E17EB]"></span>
                        <span>Menu &amp; Aksi Cepat Operasional</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pilih modul tugas harian sesuai dengan wewenang tugas Anda</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- 1. Kasir POS -->
                @can('kasir.produk.view')
                <a href="{{ route('kasir.produk') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] group-hover:bg-[#0052FF] group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">POS</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-[#0052FF] transition-colors">Kasir POS</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Buka kasir untuk transaksi penjualan produk, aksesoris, &amp; sparepart</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-[#0052FF]">
                        <span>Buka Kasir</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

                <!-- 2. Servis Masuk -->
                @can('servis.masuk.view')
                <a href="{{ route('servis.masuk') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-purple-300 hover:shadow-lg hover:shadow-purple-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] group-hover:bg-[#5E17EB] group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">Tiket</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-[#5E17EB] transition-colors">Servis Masuk</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Penerimaan unit servis baru, diagnosa awal kerusakan, &amp; kelengkapan</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-[#5E17EB]">
                        <span>Input Servis</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

                <!-- 3. Servis Selesai / Keluar -->
                @can('servis.keluar.view')
                <a href="{{ route('servis.keluar') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#10B981] group-hover:bg-[#10B981] group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Selesai</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-[#10B981] transition-colors">Servis Selesai</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Penyerahan unit servis kepada pelanggan &amp; cetak nota pengambilan</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-[#10B981]">
                        <span>Lihat Unit Siap</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

                <!-- 4. Transaksi Harian -->
                @can('transaksi.harian.view')
                <a href="{{ route('transaksi.harian') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Harian</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-indigo-600 transition-colors">Transaksi Harian</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Pencatatan nota kasir &amp; transaksi operasional harian toko</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-indigo-600">
                        <span>Buka Transaksi</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

                <!-- 5. Manajemen Stok Cabang -->
                @can('cabang.stok.view')
                <a href="{{ route('cabang.stok') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Stok</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-amber-600 transition-colors">Manajemen Stok</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Cek stok fisik sparepart, unit, dan aksesoris di toko</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-amber-600">
                        <span>Cek Stok</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

                <!-- 6. Kas Keluar -->
                @can('kas.keluar.view')
                <a href="{{ route('kas-keluar') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-rose-300 hover:shadow-lg hover:shadow-rose-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">Kas</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-rose-600 transition-colors">Kas Keluar</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Pencatatan biaya operasional, belanja sparepart, dan logistik</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-rose-600">
                        <span>Catat Kas Keluar</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

                <!-- 7. Data Pelanggan -->
                @can('master.customer.view')
                <a href="{{ route('master.customer') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-teal-300 hover:shadow-lg hover:shadow-teal-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-600 bg-teal-50 px-2 py-0.5 rounded-full">Pelanggan</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-teal-600 transition-colors">Data Pelanggan</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Pencarian nomor WhatsApp dan riwayat pelanggan toko</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-teal-600">
                        <span>Buka Pelanggan</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

                <!-- 8. Serial Number -->
                @can('serial_number.view')
                <a href="{{ route('serial-number') }}"
                   class="group bg-white card-custom p-5 rounded-2xl border border-gray-100 hover:border-pink-300 hover:shadow-lg hover:shadow-pink-500/5 transition-all flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] group-hover:bg-[#E6007A] group-hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full">SN</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-[#E6007A] transition-colors">Serial Number</h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">Pencarian nomor seri unit barang &amp; status masa garansi</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs font-bold text-[#E6007A]">
                        <span>Cari SN</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endcan

            </div>
        </div>

        <!-- 3. SOP Standar Pelayanan Prima -->
        <div class="bg-gradient-to-r from-purple-900 to-indigo-950 text-white card-custom p-6 rounded-2xl border border-purple-800/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start space-x-4">
                <div class="w-10 h-10 rounded-xl bg-purple-500/20 border border-purple-400/30 flex items-center justify-center shrink-0 text-purple-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-white">Standar Operasional Pelayanan Prima</h3>
                    <p class="text-xs text-purple-200/80 mt-1 leading-relaxed">
                        Pastikan kondisi fisik, kelengkapan unit servis, dan nomor WhatsApp aktif pelanggan dicatat dengan cermat saat penerimaan unit.
                    </p>
                </div>
            </div>
            <div class="shrink-0 flex items-center space-x-2">
                <span class="text-xs font-bold bg-white/10 px-3 py-1.5 rounded-xl border border-white/15 text-purple-200">
                    Soulmate Quality First
                </span>
            </div>
        </div>

    @endif

</div>

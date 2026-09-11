<div class="space-y-6">

    <!-- Welcome & Branch Switcher Banner -->
    <div class="card-custom bg-cover bg-center text-white p-6 md:p-8 rounded-2xl relative overflow-hidden shadow-xl shadow-purple-950/20"
         style="background-image: url('{{ asset('images/hero_display.jpeg') }}');">
        
        <!-- Dark Brand Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#0E1726]/95 via-[#5E17EB]/85 to-[#E6007A]/75"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center space-x-2 bg-white/15 backdrop-blur-md px-3.5 py-1 rounded-full text-xs font-semibold text-white mb-3 border border-white/20">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-4 h-4 rounded object-contain">
                    <span>Soulmate Komputer Multi-Branch System</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                    Halo, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="text-purple-100 text-sm mt-1.5 max-w-xl leading-relaxed">
                    @if(auth()->user()->isOwner())
                        Anda memiliki akses penuh untuk memantau aktivitas operasional, performa servis, dan pendapatan dari <strong>seluruh cabang</strong>.
                    @else
                        Panel operasional <strong>{{ auth()->user()->cabang?->nama_cabang ?? 'Pusat' }}</strong> — Role: <strong>{{ auth()->user()->role_name }}</strong>.
                    @endif
                </p>
            </div>

            <!-- Branch Switcher for Owner -->
            @if(auth()->user()->isOwner())
                <div class="bg-[#0E1726]/80 backdrop-blur-md p-3.5 rounded-2xl border border-white/20 shadow-lg shrink-0">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-purple-300 mb-1.5 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span>Filter Cakupan Cabang:</span>
                    </label>
                    <select wire:change="filterCabang($event.target.value ? $event.target.value : null)" 
                            class="bg-white text-gray-900 text-sm font-bold rounded-xl px-3.5 py-2.5 border-0 shadow-sm focus:ring-2 focus:ring-purple-400 cursor-pointer w-full md:w-auto">
                        <option value="">🌐 Semua Cabang (Konsolidasi Global)</option>
                        @foreach($cabangs as $cb)
                            <option value="{{ $cb->id }}" {{ $selectedCabangId == $cb->id ? 'selected' : '' }}>
                                🏬 {{ $cb->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- Decorative Circles -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-pink-500/20 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Servis Aktif -->
        <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Servis Dalam Antrian</span>
                <span class="text-2xl md:text-3xl font-extrabold text-[#1A1D20] mt-1 block">{{ $countTotalAktif }} <span class="text-sm font-medium text-gray-400">Unit</span></span>
                <span class="text-xs font-semibold text-blue-600 mt-1 inline-block">
                    {{ $countPengecekan }} Cek • {{ $countPengerjaan }} Proses
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
        </div>

        <!-- Servis Siap Diambil / Ditagih -->
        <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Siap Diambil (Tagih)</span>
                <span class="text-2xl md:text-3xl font-extrabold text-[#10B981] mt-1 block">{{ $countSelesai }} <span class="text-sm font-medium text-gray-400">Unit</span></span>
                <span class="text-xs font-semibold text-emerald-600 mt-1 inline-block">
                    Siap penyerahan & kasir
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#10B981] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Omzet Hari Ini -->
        <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Omzet Hari Ini</span>
                <span class="text-xl md:text-2xl font-extrabold text-[#1A1D20] mt-1 block">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</span>
                <span class="text-xs font-semibold text-purple-600 mt-1 inline-block">
                    Penjualan & servis lunas
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Omzet Bulan Ini -->
        <div class="bg-white card-custom p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Omzet Bulan Ini</span>
                <span class="text-xl md:text-2xl font-extrabold text-[#E6007A] mt-1 block">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</span>
                <span class="text-xs font-semibold text-pink-600 mt-1 inline-block">
                    {{ $totalTransaksiBulanIni }} Transaksi tercatat
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </div>

    </div>

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
                🎥 CCTV Supervised
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
            <!-- Laptop Card -->
            <div class="bg-gray-900/90 border border-blue-500/30 rounded-xl p-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-800 text-blue-400 font-bold text-sm">
                    <span>💻 LAPTOP</span>
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
                    <span>🖨️ PRINTER</span>
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
                    <span>🖥️ KOMPUTER / PC</span>
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
        <div class="bg-white card-custom rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-gray-900">Servis Masuk Terbaru</h2>
                    <p class="text-xs text-gray-400">Daftar unit yang baru masuk ke sistem</p>
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
                    <tbody class="divide-y divide-gray-100">
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
        <div class="bg-white card-custom rounded-2xl p-6">
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
                                <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data transaksi kasir.</td>
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
                            <span class="text-[10px] text-gray-500 font-medium">{{ $stk->cabang?->nama_cabang }}</span>
                            <span class="text-xs font-extrabold text-amber-700 bg-amber-200/60 px-2 py-0.5 rounded-full">{{ $stk->jumlah }} Pcs</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<div class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN                                 -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Laporan &amp; Riwayat Penjualan
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Histori transaksi kasir POS, audit nota non-real, dan rincian omzet harian/bulanan toko.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('kasir.produk') }}"
               class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-2xl bg-[#0052FF] hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Buka Kasir POS</span>
            </a>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- FLASH NOTIFICATION ALERTS                      -->
    <!-- ============================================== -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-black flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-900 text-xs font-black flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- METRIC CARDS (RINGKASAN FINANSIAL DINAMIS)     -->
    <!-- ============================================== -->
    @if($tipeTransaksiFilter === 'real')
        <!-- ============================================== -->
        <!-- VIEW METRIK: KHUSUS TRANSAKSI REAL             -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Omzet Bersih Real -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Omzet Bersih (Real)</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        Rp {{ number_format($totalOmzetReal, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                        &bull; 100% Masuk Pembukuan Kas
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Card 2: Jumlah Transaksi Real -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Jumlah Transaksi</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        {{ $totalTransaksiReal }}
                    </span>
                    <span class="text-[11px] font-semibold text-blue-600 mt-0.5 inline-block">
                        &bull; Transaksi Fisik Lunas
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Card 3: Total Produk Fisik Terjual -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Produk Terjual</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        {{ number_format($totalQtyReal, 0, ',', '.') }} UNIT
                    </span>
                    <span class="text-[11px] font-semibold text-teal-600 mt-0.5 inline-block">
                        &bull; Stok Fisik Terpotong
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>

            <!-- Card 4: Diskon & PPN Real -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Diskon &amp; Pajak (Real)</span>
                    <div class="mt-1.5 space-y-0.5">
                        <span class="text-xs font-extrabold text-rose-600 block">
                            Diskon: Rp {{ number_format($totalDiskonReal, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-extrabold text-purple-600 block">
                            PPN: Rp {{ number_format($totalPpnReal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] flex items-center justify-center shrink-0 border border-pink-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
            </div>
        </div>

    @elseif($tipeTransaksiFilter === 'non_real')
        <!-- ============================================== -->
        <!-- VIEW METRIK: KHUSUS TRANSAKSI NON-REAL         -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Nilai Kertas Nota -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Nilai Nota</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        Rp {{ number_format($totalNilaiNonReal, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-semibold text-amber-600 mt-0.5 inline-block">
                        &bull; Nominal Pada Struk Cetak
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>

            <!-- Card 2: Jumlah Nota Non-Real -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Jumlah Nota Non-Real</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        {{ $totalNotaNonReal }}
                    </span>
                    <span class="text-[11px] font-semibold text-orange-600 mt-0.5 inline-block">
                        &bull; Dokumen Cetak / Simulasi
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 border border-orange-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                </div>
            </div>

            <!-- Card 3: Total Item Dicetak -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Item Dicetak</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        {{ number_format($totalQtyNonReal, 0, ',', '.') }} UNIT
                    </span>
                    <span class="text-[11px] font-semibold text-blue-600 mt-0.5 inline-block">
                        &bull; Stok Toko Aman / Utuh
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>

            <!-- Card 4: Status Dampak Finansial & Inventaris -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Dampak Finansial</span>
                    <div class="mt-1.5 space-y-0.5">
                        <span class="text-xs font-extrabold text-emerald-600 block">
                            Rp 0 Masuk Kas
                        </span>
                        <span class="text-xs font-extrabold text-gray-500 block">
                            0 Potong Stok Fisik
                        </span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
        </div>

    @else
        <!-- ============================================== -->
        <!-- VIEW METRIK: SEMUA TIPE TRANSAKSI (DEFAULT)    -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Omzet Real -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Omzet Bersih (Real)</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        Rp {{ number_format($totalOmzetReal, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                        &bull; {{ $totalTransaksiReal }} Transaksi Lunas
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Card 2: Total Transaksi Real -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Penjualan Real</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        {{ $totalTransaksiReal }}
                    </span>
                    <span class="text-[11px] font-semibold text-blue-600 mt-0.5 inline-block">
                        &bull; Memotong Stok Fisik
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Card 3: Total Transaksi Non-Real -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Transaksi Non-Real</span>
                    <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                        {{ $totalNotaNonReal }}
                    </span>
                    <span class="text-[11px] font-semibold text-amber-600 mt-0.5 inline-block">
                        &bull; Tanpa Potong Stok
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>

            <!-- Card 4: Total Diskon & PPN -->
            <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Diskon &amp; Pajak</span>
                    <div class="mt-1.5 space-y-0.5">
                        <span class="text-xs font-extrabold text-rose-600 block">
                            Diskon: Rp {{ number_format($totalDiskon, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-extrabold text-purple-600 block">
                            PPN: Rp {{ number_format($totalPpn, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#E6007A] flex items-center justify-center shrink-0 border border-pink-100 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- NOTE BESAR: PERINGATAN TRANSAKSI NON-REAL      -->
    <!-- ============================================== -->
    @if($tipeTransaksiFilter === 'non_real')
        <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-900 shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-amber-200 text-amber-900 text-[11px] font-black tracking-wider uppercase">PEMBERITAHUAN AUDIT NOTA NON-REAL</span>
                </div>
                <h3 class="text-sm sm:text-base font-black text-amber-950 mt-1">
                    Transaksi Non-Real TIDAK Masuk Omzet &amp; TIDAK Merubah Nilai Stok Toko
                </h3>
                <p class="text-xs sm:text-sm font-bold text-amber-800 mt-1 leading-relaxed">
                    Seluruh data transaksi bertipe Non-Real di bawah ini murni berfungsi sebagai cetakan nota / formalitas administrasi. Data transaksi ini <span class="underline font-black text-amber-950">TIDAK AKAN MASUK KE DALAM PERHITUNGAN OMZET KAS</span> dan <span class="underline font-black text-amber-950">TIDAK AKAN MENGURANGI/MERUBAH NILAI STOK FISIK</span>.
                </p>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- FILTER BAR & PENCARIAN                         -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-5 space-y-4 border border-gray-100/90 shadow-sm">

        <!-- Baris 1: Pencarian & Filter Periode Utama -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <!-- Universal Loose Search Bar -->
            <div class="md:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2.5 text-xs font-bold rounded-2xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
            </div>

            <!-- Filter Periode Preset & Size -->
            <div class="md:col-span-4 flex items-center gap-2">
                <select wire:model.live="periodeFilter"
                        class="w-full text-xs font-bold px-3.5 py-2.5 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] bg-white">
                    <option value="hari_ini">Hari Ini</option>
                    <option value="kemarin">Kemarin</option>
                    <option value="7_hari">7 Hari Terakhir</option>
                    <option value="bulan_ini">Bulan Ini</option>
                    <option value="bulan_lalu">Bulan Lalu</option>
                    <option value="custom">Rentang Tanggal Khusus</option>
                </select>
                <x-table-size-selector />
            </div>
        </div>

        <!-- Baris 2: Sub-Filter (Tipe Transaksi, Status, Kasir, Cabang HQ, Custom Date Range) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-2 border-t border-gray-100">

            <!-- Filter Tipe Transaksi -->
            <div class="{{ $isHQ ? 'lg:col-span-3' : 'lg:col-span-3' }}">
                <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-1">Tipe Transaksi</label>
                <select wire:model.live="tipeTransaksiFilter"
                        class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                    <option value="semua">Semua Tipe Transaksi</option>
                    <option value="real">Penjualan Real (Stok Berkurang)</option>
                    <option value="non_real">Transaksi Non-Real</option>
                </select>
            </div>

            <!-- Filter Status Pembayaran -->
            <div class="{{ $isHQ ? 'lg:col-span-3' : 'lg:col-span-3' }}">
                <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-1">Status Pembayaran</label>
                <select wire:model.live="statusFilter"
                        class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                    <option value="semua">Semua Status</option>
                    <option value="lunas">Lunas</option>
                    <option value="bon">BON (Belum Lunas)</option>
                    <option value="batal">Batal</option>
                </select>
            </div>

            <!-- Filter Kasir -->
            <div class="{{ $isHQ ? 'lg:col-span-3' : 'lg:col-span-3' }}">
                <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-1">Staf Kasir</label>
                <select wire:model.live="kasirFilter"
                        class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                    <option value="semua">Semua Staf Kasir</option>
                    @foreach($kasirs as $k)
                        <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->role_name }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Cabang (Owner HQ Saja) -->
            @if($isHQ)
                <div class="lg:col-span-3">
                    <label class="block text-[10px] font-bold text-purple-900 uppercase tracking-wider mb-1">Cabang Toko</label>
                    <select wire:model.live="cabangFilter"
                            class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-purple-200 focus:ring-2 focus:ring-[#5E17EB] bg-purple-50/50 text-purple-900">
                        <option value="semua">Semua Cabang (Global)</option>
                        @foreach($cabangs as $cb)
                            <option value="{{ $cb->id }}">{{ $cb->nama_cabang }} ({{ $cb->kode_cabang }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Custom Date Range Inputs (Jika opsi custom dipilih) -->
            @if($periodeFilter === 'custom')
                <div class="{{ $isHQ ? 'lg:col-span-12' : 'lg:col-span-3' }} flex items-center space-x-2">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-1">Dari</label>
                        <input type="date" wire:model.live="startDate" class="w-full text-xs font-bold px-2.5 py-1.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] text-gray-900">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-1">Sampai</label>
                        <input type="date" wire:model.live="endDate" class="w-full text-xs font-bold px-2.5 py-1.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] text-gray-900">
                    </div>
                </div>
            @endif

        </div>

    </div>

    <!-- ============================================== -->
    <!-- TABEL RIWAYAT TRANSAKSI PENJUALAN              -->
    <!-- ============================================== -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="card-custom bg-white overflow-hidden border border-gray-100/90 shadow-sm">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] font-extrabold text-gray-900 uppercase tracking-wider">
                        <x-th-sort field="no_transaksi" :sortField="$sortField" :sortDirection="$sortDirection" class="px-5">No. Invoice</x-th-sort>
                        <x-th-sort field="tanggal" :sortField="$sortField" :sortDirection="$sortDirection" class="px-4">Waktu Transaksi</x-th-sort>
                        <th class="py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-900">Pelanggan</th>
                        <th class="py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-900">Kasir</th>
                        @if($isHQ)
                            <th class="py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-900">Cabang</th>
                        @endif
                        <th class="py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-900 text-center">Tipe Transaksi</th>
                        <th class="py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-900 text-center">Status</th>
                        <x-th-sort field="total" :sortField="$sortField" :sortDirection="$sortDirection" align="right" class="px-4">Total Belanja</x-th-sort>
                        <th class="py-3.5 px-5 font-extrabold text-[10px] tracking-wider uppercase text-gray-900 text-right">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query/Filter/Search/Sort/Pagination -->
                <x-table-skeleton
                    :columns="$isHQ ? 9 : 8"
                    :rows="8"
                    target="search, periodeFilter, startDate, endDate, tipeTransaksiFilter, statusFilter, kasirFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore" />

                <!-- Data Transaksi Penjualan -->
                <tbody wire:loading.remove
                       wire:target="search, periodeFilter, startDate, endDate, tipeTransaksiFilter, statusFilter, kasirFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($penjualans as $pj)
                        <tr class="hover:bg-gray-50/80 transition-colors {{ !$pj->is_real ? 'bg-amber-50/20' : '' }}">

                            <!-- No. Invoice & Tipe Badge -->
                            <td class="py-4 px-5 font-mono">
                                <div class="flex flex-col">
                                    <span class="font-extrabold text-gray-900 text-xs">
                                        {{ $pj->no_transaksi_frontend }}
                                    </span>
                                    @if($isHQ)
                                        <span class="text-[10px] text-gray-500 font-bold mt-0.5">{{ $pj->no_transaksi }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tanggal & Waktu -->
                            <td class="py-4 px-4">
                                <span class="font-bold text-gray-900 block">
                                    {{ $pj->tanggal ? $pj->tanggal->format('d M Y') : '-' }}
                                </span>
                                <span class="text-[11px] text-gray-600 font-semibold block mt-0.5 font-mono">
                                    {{ $pj->tanggal ? $pj->tanggal->format('H:i') . ' WIB' : '' }}
                                </span>
                            </td>

                            <!-- Pelanggan -->
                            <td class="py-4 px-4">
                                <div class="flex flex-col">
                                    <span class="font-extrabold text-gray-900 block">
                                        {{ $pj->customer?->nama ?? 'Pelanggan Umum' }}
                                    </span>
                                    @if($pj->customer?->no_hp)
                                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $pj->customer->no_hp)) }}"
                                           target="_blank"
                                           class="text-[11px] font-bold text-emerald-700 hover:text-emerald-900 inline-flex items-center space-x-1 mt-0.5">
                                            <span>{{ $pj->customer->no_hp }}</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Kasir -->
                            <td class="py-4 px-4">
                                <span class="font-bold text-gray-900 block">
                                    {{ $pj->kasir?->name ?? 'Staf Kasir' }}
                                </span>
                                <span class="text-[10px] text-gray-600 font-bold block">
                                    {{ $pj->kasir?->role_name ?? 'Admin' }}
                                </span>
                            </td>

                            <!-- Cabang (Owner HQ Only) -->
                            @if($isHQ)
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        {{ $pj->cabang?->kode_cabang ?? 'CB' }} &bull; {{ $pj->cabang?->nama_cabang ?? 'Cabang' }}
                                    </span>
                                </td>
                            @endif

                            <!-- Tipe Transaksi (Real vs Non-Real) -->
                            <td class="py-4 px-4 text-center">
                                @if(!$pj->is_real)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                        Non-Real
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                        Real
                                    </span>
                                @endif
                            </td>

                            <!-- Status Pembayaran (Inline Status Dropdown) -->
                            <td class="py-4 px-4 text-center">
                                @php
                                    $statusVal = strtolower($pj->status ?? 'lunas');
                                    $statusBg = match($statusVal) {
                                        'lunas', 'selesai' => 'bg-emerald-50 text-emerald-900 border-emerald-400 font-black',
                                        'bon', 'belum_lunas' => 'bg-amber-50 text-amber-900 border-amber-400 font-black',
                                        'batal' => 'bg-rose-50 text-rose-900 border-rose-400 font-black',
                                        default => 'bg-gray-50 text-gray-900 border-gray-300 font-bold',
                                    };
                                @endphp
                                <select wire:change="updateStatusDirect({{ $pj->id }}, $event.target.value)"
                                        wire:loading.attr="disabled"
                                        class="text-xs rounded-xl px-2.5 py-1 font-black border transition-all cursor-pointer shadow-xs focus:ring-2 focus:ring-[#5E17EB] {{ $statusBg }}">
                                    <option value="lunas" {{ in_array($statusVal, ['lunas', 'selesai']) ? 'selected' : '' }}>Lunas</option>
                                    <option value="bon" {{ in_array($statusVal, ['bon', 'belum_lunas']) ? 'selected' : '' }}>BON</option>
                                    <option value="batal" {{ $statusVal === 'batal' ? 'selected' : '' }}>Batal</option>
                                </select>
                            </td>

                            <!-- Total Belanja -->
                            <td class="py-4 px-4 text-right">
                                <span class="text-sm font-black text-gray-900 block font-mono">
                                    Rp {{ number_format($pj->total, 0, ',', '.') }}
                                </span>
                                @if($pj->diskon > 0)
                                    <span class="text-[10px] font-bold text-rose-600 block">
                                        Diskon -Rp {{ number_format($pj->diskon, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <!-- Tombol Detail Modal -->
                                    <button wire:click="openDetailModal({{ $pj->id }})"
                                            class="p-2 rounded-xl text-gray-700 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Lihat Rincian Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- Tombol Cetak Struk -->
                                    <a href="{{ route('kasir.produk.invoice', $pj->id) }}"
                                       target="_blank"
                                       class="p-2 rounded-xl text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
                                       title="Cetak Struk Thermal">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 9 : 8 }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="font-bold text-gray-600">Tidak ada riwayat transaksi ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau rentang tanggal filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Infinite Scroll Sentinel & Status -->
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
            <x-infinite-scroll-sentinel
                :has-more="$hasMore"
                target="loadMore"
                label="transaksi"
                :current="$penjualans->count()"
                :total="$totalItems"
                color="purple" />
        </div>

    </div>

    <!-- ============================================== -->
    <!-- MODAL DETAIL TRANSAKSI PENJUALAN               -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedPenjualan)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-white font-mono tracking-tight leading-snug">
                                        {{ $selectedPenjualan->no_transaksi_frontend }}
                                    </h3>
                                    @if(!$selectedPenjualan->is_real)
                                        <span class="text-[10px] font-black px-2 py-0.5 rounded-lg bg-amber-400 text-amber-950">
                                            Transaksi Non-Real
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-white/20 text-white border border-white/30">
                                            Transaksi Real
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">
                                    {{ $selectedPenjualan->tanggal ? $selectedPenjualan->tanggal->translatedFormat('d F Y - H:i') . ' WIB' : '-' }}
                                </p>
                            </div>
                        </div>

                        <button wire:click="closeDetailModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    @if(!$selectedPenjualan->is_real)
                        <!-- Note Peringatan Transaksi Non-Real di Modal -->
                        <div class="mx-6 mt-4 p-3.5 bg-amber-50 border-2 border-amber-300 rounded-2xl flex items-start space-x-3 text-amber-900 shadow-xs">
                            <div class="w-7 h-7 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <span class="text-[11px] font-black uppercase tracking-wide text-amber-950 block">Audit Dokumen Non-Real:</span>
                                <p class="text-xs font-bold text-amber-800 mt-0.5 leading-relaxed">
                                    Transaksi ini berstatus Non-Real (Formalitas Cetak). Data transaksi ini <strong class="underline font-black text-amber-950">TIDAK MASUK OMZET KAS</strong> dan <strong class="underline font-black text-amber-950">TIDAK MERUBAH / MEMOTONG NILAI STOK FISIK</strong> barang toko.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Snapshot Informasi Pelanggan & Kasir -->
                    <div class="p-6 pb-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                            <span class="text-[10px] font-bold text-gray-900 uppercase block">Pelanggan</span>
                            <span class="text-xs font-black text-gray-900 block mt-1 truncate">
                                {{ $selectedPenjualan->customer?->nama ?? 'Pelanggan Umum' }}
                            </span>
                            <span class="text-[10px] text-gray-700 font-bold block">
                                {{ $selectedPenjualan->customer?->no_hp ?? '-' }}
                            </span>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                            <span class="text-[10px] font-bold text-gray-900 uppercase block">Kasir</span>
                            <span class="text-xs font-black text-gray-900 block mt-1 truncate">
                                {{ $selectedPenjualan->kasir?->name ?? 'Staf' }}
                            </span>
                            <span class="text-[10px] text-gray-700 font-bold block">
                                {{ $selectedPenjualan->kasir?->role_name ?? 'Admin' }}
                            </span>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                            <span class="text-[10px] font-bold text-gray-900 uppercase block">Tipe Transaksi</span>
                            <span class="text-xs font-black text-gray-900 block mt-1">
                                {{ $selectedPenjualan->is_real ? 'Transaksi Real' : 'Transaksi Non-Real' }}
                            </span>
                            @php
                                $dStatus = strtolower($selectedPenjualan->status ?? 'lunas');
                                $dStatusLabel = match($dStatus) {
                                    'lunas', 'selesai' => 'Lunas',
                                    'bon', 'belum_lunas' => 'BON (Belum Lunas)',
                                    'batal' => 'Batal',
                                    default => ucfirst($dStatus),
                                };
                                $dStatusColor = match($dStatus) {
                                    'lunas', 'selesai' => 'text-emerald-700',
                                    'bon', 'belum_lunas' => 'text-amber-800',
                                    'batal' => 'text-rose-700',
                                    default => 'text-gray-900',
                                };
                            @endphp
                            <span class="text-[10px] {{ $dStatusColor }} font-black block">
                                Status: {{ $dStatusLabel }}
                            </span>
                        </div>
                    </div>

                    <!-- Tabel Rincian Item Belanja -->
                    <div class="p-6 py-3">
                        <span class="text-xs font-extrabold text-gray-900 uppercase tracking-wider block mb-2">
                            Daftar Item Belanja
                        </span>
                        <div class="border border-gray-200 rounded-2xl overflow-hidden max-h-60 overflow-y-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-400 uppercase border-b border-gray-200">
                                        <th class="py-2.5 px-4">Item & Barcode</th>
                                        <th class="py-2.5 px-3 text-center">Qty</th>
                                        <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($selectedPenjualan->details as $d)
                                        <tr>
                                            <td class="py-3 px-4">
                                                <span class="font-extrabold text-gray-900 block">
                                                    {{ $d->produk?->nama_produk ?? $d->nama_item }}
                                                </span>
                                                @if($d->produk?->barcode)
                                                    <span class="font-mono text-[10px] text-gray-400">
                                                        {{ $d->produk->barcode }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold text-gray-700">
                                                {{ $d->qty }} UNIT
                                            </td>
                                            <td class="py-3 px-3 text-right font-mono text-gray-700">
                                                Rp {{ number_format($d->harga, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-bold text-gray-900 font-mono">
                                                Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Ringkasan Perhitungan Finansial -->
                    <div class="px-6 py-2">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200/80 space-y-2 text-xs">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal Belanja</span>
                                <span class="font-bold font-mono">Rp {{ number_format($selectedPenjualan->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($selectedPenjualan->diskon > 0)
                                <div class="flex justify-between text-rose-600 font-bold">
                                    <span>Potongan Diskon</span>
                                    <span class="font-mono">-Rp {{ number_format($selectedPenjualan->diskon, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($selectedPenjualan->ppn_nominal > 0)
                                <div class="flex justify-between text-purple-700 font-bold">
                                    <span>PPN ({{ $selectedPenjualan->ppn_persen }}%)</span>
                                    <span class="font-mono">+Rp {{ number_format($selectedPenjualan->ppn_nominal, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="pt-2 border-t border-gray-200 flex justify-between items-center text-sm font-black text-gray-900">
                                <span>Total Tagihan Akhir</span>
                                <span class="text-base font-black text-[#5E17EB] font-mono">Rp {{ number_format($selectedPenjualan->total, 0, ',', '.') }}</span>
                            </div>
                            @if($selectedPenjualan->jumlah_bayar > 0)
                                <div class="flex justify-between text-[11px] text-gray-500 pt-1">
                                    <span>Nominal Diterima: Rp {{ number_format($selectedPenjualan->jumlah_bayar, 0, ',', '.') }}</span>
                                    <span>Kembalian: Rp {{ number_format($selectedPenjualan->kembalian, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Modal Action -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl border-t border-gray-100 mt-3">
                        <button type="button"
                                wire:click="closeDetailModal"
                                class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            Tutup
                        </button>

                        <a href="{{ route('kasir.produk.invoice', $selectedPenjualan->id) }}"
                           target="_blank"
                           class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-xs font-bold text-white transition-all shadow-md shadow-blue-600/20 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Struk Thermal</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

<div x-data x-init="
    if (window.Echo) {
        window.Echo.channel('servis-updates')
            .listen('.ServisStatusUpdated', () => { $wire.$refresh(); })
            .listen('ServisStatusUpdated', () => { $wire.$refresh(); });
    }
" x-on:open-print-invoice.window="window.open($event.detail.url, '_blank')" class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & RINGKASAN                    -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Servis Keluar &amp; Pelunasan
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Workspace pengerjaan servis keluar, rincian biaya jasa &amp; sparepart, serta kasir pelunasan unit.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('servis.masuk') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Terima Servis Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi Session Flash -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- METRIC CARDS: RINGKASAN WORKSPACE              -->
    <!-- ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Sedang Dikerjakan -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Dalam Pengerjaan</span>
                <span class="text-2xl font-black text-blue-600 mt-1.5 block font-mono">
                    {{ $totalDalamProses }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-gray-500 mt-0.5 inline-block">
                    &bull; Pengecekan / Proses
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Selesai (Siap Ambil) -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Siap Diambil</span>
                <span class="text-2xl font-black text-emerald-600 mt-1.5 block font-mono">
                    {{ $totalSiapAmbil }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                    &bull; Siap Pelunasan
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Sudah Diambil / Lunas -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Sudah Diambil</span>
                <span class="text-2xl font-black text-indigo-600 mt-1.5 block font-mono">
                    {{ $totalSudahDiambil }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-gray-500 mt-0.5 inline-block">
                    &bull; Lunas &amp; Bergaransi
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Card 4: Total Omzet Servis Lunas -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Omzet Servis Lunas</span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    Rp {{ number_format($totalOmzetServis, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#5E17EB] mt-0.5 inline-block">
                    &bull; Jasa &amp; Sparepart
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- TOOLBAR PENCARIAN & FILTER                     -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-3">
        
        <!-- Search Bar Universal -->
        <div class="w-full lg:w-96 relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] transition-all" />
        </div>

        <!-- Filter Controls -->
        <div class="w-full lg:w-auto flex flex-wrap items-center gap-2">
            
            <!-- Filter Status (5 Status Utama) -->
            <select wire:model.live="statusFilter" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                <option value="semua">Semua Status</option>
                <option value="pengecekan">Pengecekan</option>
                <option value="perbaikan">Perbaikan</option>
                <option value="selesai">Selesai</option>
                <option value="sudah_diambil">Sudah Diambil</option>
                <option value="batal">Batal</option>
            </select>

            <!-- Filter Status Pengambilan -->
            <select wire:model.live="ambilFilter" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                <option value="semua">Semua Pengambilan</option>
                <option value="belum_diambil">Belum Diambil</option>
                <option value="sudah_diambil">Sudah Diambil</option>
                <option value="hari_ini">Diambil Hari Ini</option>
            </select>

            <!-- Filter Kategori Perangkat -->
            <select wire:model.live="kategoriFilter" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                <option value="semua">Semua Perangkat</option>
                <option value="laptop">Laptop</option>
                <option value="printer">Printer</option>
                <option value="komputer">Komputer</option>
                <option value="lainnya">Lainnya</option>
            </select>

            <!-- Filter Cabang (Owner HQ Only) -->
            @if($isHQ)
                <select wire:model.live="cabangFilter" class="px-3 py-2 bg-purple-50 border border-purple-200 rounded-xl text-xs font-bold text-[#5E17EB]">
                    <option value="semua">Semua Cabang (HQ)</option>
                    @foreach($cabangs as $cb)
                        <option value="{{ $cb->id }}">{{ $cb->nama_cabang }}</option>
                    @endforeach
                </select>
            @endif

        </div>

    </div>

    <!-- ============================================== -->
    <!-- TABEL SERVIS KELUAR & PELUNASAN (COMPACT)     -->
    <!-- ============================================== -->
    <div class="card-custom bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden relative">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fixed min-w-[950px]">
                <colgroup>
                    <col class="{{ $isHQ ? 'w-[14%]' : 'w-[15%]' }}">
                    <col class="{{ $isHQ ? 'w-[15%]' : 'w-[17%]' }}">
                    <col class="{{ $isHQ ? 'w-[18%]' : 'w-[23%]' }}">
                    @if($isHQ)
                        <col class="w-[11%]">
                    @endif
                    <col class="{{ $isHQ ? 'w-[14%]' : 'w-[15%]' }}">
                    <col class="{{ $isHQ ? 'w-[13%]' : 'w-[14%]' }}">
                    <col class="{{ $isHQ ? 'w-[13%]' : 'w-[14%]' }}">
                    <col class="w-[12%]">
                </colgroup>

                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/80 text-[10px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <x-th-sort field="tanggal_masuk" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Waktu / Tiket</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Pelanggan</th>
                        <x-th-sort field="tipe_unit" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Unit &amp; Keluhan</x-th-sort>
                        @if($isHQ)
                            <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Cabang</th>
                        @endif
                        <x-th-sort field="status" :sortField="$sortField" :sortDirection="$sortDirection" align="center" class="py-2.5 px-3 text-center">Status</x-th-sort>
                        <x-th-sort field="tanggal_ambil" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Waktu Diambil</x-th-sort>
                        <x-th-sort field="total_biaya" :sortField="$sortField" :sortDirection="$sortDirection" align="right" class="py-2.5 px-3 text-right">Total Biaya</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query/Filter/Search/Sort/Pagination -->
                <tbody wire:key="servis-keluar-skeleton-body"
                       wire:loading 
                       wire:target="search, statusFilter, ambilFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore" 
                       class="divide-y divide-gray-100 bg-white">
                    @for($i = 0; $i < 6; $i++)
                        <tr class="animate-pulse">
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-36"></div></td>
                            @if($isHQ)
                                <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-16"></div></td>
                            @endif
                            <td class="py-2 px-3 text-center"><div class="h-4 bg-gray-200 rounded-full w-20 mx-auto"></div></td>
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-20"></div></td>
                            <td class="py-2 px-3 text-right"><div class="h-3 bg-gray-200 rounded w-20 ml-auto"></div></td>
                            <td class="py-2 px-3 text-center"><div class="h-5 bg-gray-100 rounded w-16 mx-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <!-- Data Rows -->
                <tbody wire:key="servis-keluar-data-body"
                       wire:loading.remove 
                       wire:target="search, statusFilter, ambilFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore" 
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $sv)
                        <tr wire:key="pengerjaan-row-{{ $sv->id }}" class="hover:bg-gray-50/70 transition-colors {{ $sv->status === 'selesai' ? 'bg-emerald-50/20' : '' }}">
                            
                            <!-- Tiket & Waktu -->
                            <td class="py-2 px-3 min-w-[120px] whitespace-nowrap">
                                <span class="font-mono font-black text-[#5E17EB] text-xs block leading-tight">
                                    {{ $sv->no_servis_frontend }}
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono block leading-tight">
                                    {{ $sv->tanggal_masuk ? $sv->tanggal_masuk->format('d/m/Y H:i') : '-' }}
                                </span>
                            </td>

                            <!-- Pelanggan -->
                            <td class="py-2 px-3 min-w-[130px]">
                                <span class="font-bold text-gray-900 block truncate max-w-[140px] leading-tight" title="{{ $sv->customer?->nama }}">
                                    {{ $sv->customer?->nama ?? 'Pelanggan Umum' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-500 block leading-tight">
                                    {{ $sv->customer?->no_hp ?? '-' }}
                                </span>
                            </td>

                            <!-- Unit & Keluhan -->
                            <td class="py-2 px-3 min-w-[170px] max-w-[220px]">
                                <span class="font-bold text-gray-900 block truncate leading-tight" title="{{ $sv->tipe_unit }}">
                                    {{ $sv->tipe_unit ?: $sv->kategori_perangkat_label }}
                                </span>
                                <p class="text-[10px] text-gray-500 truncate mt-0.5 leading-tight" title="{{ $sv->keluhan }}">
                                    {{ $sv->keluhan ?: '-' }}
                                </p>
                            </td>

                            <!-- Cabang (HQ Only) -->
                            @if($isHQ)
                                <td class="py-2 px-3 w-28 whitespace-nowrap">
                                    @if($sv->cabang)
                                        <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $sv->cabang->nama_cabang }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            @endif

                            <!-- Badge Status -->
                            <td class="py-2 px-3 w-32 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sv->status_badge_class }}">
                                    {{ $sv->status_label }}
                                </span>
                                @if($sv->garansi_hari > 0 && ($sv->status === 'sudah_diambil' || $sv->status === 'sudah_dibayar'))
                                    <span class="block text-[9px] font-bold text-emerald-600 mt-0.5 leading-tight">
                                        Garansi s/d {{ $sv->garansi_sampai ? $sv->garansi_sampai->format('d/m/Y') : '-' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Waktu Diambil -->
                            <td class="py-2 px-3 w-32 whitespace-nowrap">
                                @if($sv->tanggal_ambil)
                                    <span class="font-mono font-bold text-gray-900 text-xs block leading-tight">
                                        {{ $sv->tanggal_ambil->format('d/m/Y') }}
                                    </span>
                                    <span class="text-[10px] text-gray-500 font-mono block leading-tight">
                                        {{ $sv->tanggal_ambil->format('H:i') }} WIB
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                        Belum Diambil
                                    </span>
                                @endif
                            </td>

                            <!-- Total Biaya -->
                            <td class="py-2 px-3 w-32 text-right whitespace-nowrap">
                                <span class="font-mono font-black text-gray-900 text-xs block leading-tight">
                                    Rp {{ number_format($sv->total_biaya, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-gray-400 font-medium block leading-tight">
                                    {{ $sv->details->count() }} item
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-2 px-3 w-28 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">
                                    
                                    <!-- Tombol Workspace Pengerjaan -->
                                    <button type="button"
                                            wire:click="openPengerjaanModal({{ $sv->id }})"
                                            wire:loading.attr="disabled"
                                            title="Buka Workspace Pengerjaan & Billing"
                                            class="p-1 rounded-md bg-blue-50 hover:bg-blue-100 disabled:opacity-50 text-[#0052FF] font-bold text-xs transition-all cursor-pointer border border-blue-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- Tombol Kasir Pelunasan (Jika status belum lunas & belum batal) -->
                                    @if($sv->status !== 'sudah_diambil' && $sv->status !== 'sudah_dibayar' && $sv->status !== 'batal')
                                        <button type="button"
                                                wire:click="openCheckoutModal({{ $sv->id }})"
                                                wire:loading.attr="disabled"
                                                title="Kasir Pelunasan & Pengambilan Unit"
                                                class="px-2.5 py-1 rounded-md bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-xs transition-all cursor-pointer shadow-2xs inline-flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <span>Bayar</span>
                                        </button>
                                    @else
                                        <!-- Tombol Cetak Faktur Lunas -->
                                        <a href="{{ route('servis.invoice', $sv->id) }}"
                                           target="_blank"
                                           title="Cetak Faktur Pelunasan"
                                           class="p-1 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-all cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 8 : 7 }}" class="py-12 text-center text-gray-400 font-semibold">
                                Tidak ada data pengerjaan servis yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Infinite Scroll Sentinel -->
        @if($hasMore)
            <div class="p-4 border-t border-gray-100 flex justify-center">
                <button type="button"
                        wire:click="loadMore"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 disabled:opacity-50 text-gray-700 font-bold text-xs border border-gray-200 transition-all cursor-pointer flex items-center gap-2">
                    <svg wire:loading wire:target="loadMore" class="animate-spin h-3.5 w-3.5 text-gray-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span wire:loading.remove wire:target="loadMore">Muat Lebih Banyak...</span>
                    <span wire:loading wire:target="loadMore">Memuat data...</span>
                </button>
            </div>
        @endif

    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: WORKSPACE PENGERJAAN & BILLING        -->
    <!-- ============================================== -->
    @if($modalPengerjaanOpen && $activeServis)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- Header Modal -->
                <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shrink-0 shadow-sm">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Workspace Servis Keluar</h3>
                                <span class="font-mono font-bold text-white text-xs px-2 py-0.5 bg-white/20 rounded-lg border border-white/30">{{ $activeServis->no_servis_frontend }}</span>
                            </div>
                            <p class="text-xs text-purple-200 font-medium mt-0.5 truncate">
                                {{ $activeServis->tipe_unit }} &bull; Pelanggan: <strong class="text-white">{{ $activeServis->customer?->nama }}</strong>
                            </p>
                        </div>
                    </div>

                    <button type="button" wire:click="closePengerjaanModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Body Modal (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-6">
                    
                    <!-- Flash Message Billing -->
                    @if (session()->has('billing_success'))
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                            {{ session('billing_success') }}
                        </div>
                    @endif

                    <!-- Section Info Unit & Keluhan -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-200/80 text-xs">
                        <div>
                            <span class="text-gray-400 font-bold uppercase block text-[10px]">Keluhan Pelanggan:</span>
                            <p class="font-bold text-gray-800 mt-0.5">{{ $activeServis->keluhan ?: '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold uppercase block text-[10px]">Kelengkapan:</span>
                            <p class="font-semibold text-gray-800 mt-0.5">{{ $activeServis->kelengkapan ?: 'Hanya Unit' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold uppercase block text-[10px]">Kondisi / Catatan:</span>
                            <p class="font-semibold text-gray-800 mt-0.5">{{ $activeServis->kondisi_barang ?: ($activeServis->catatan ?: '-') }}</p>
                        </div>
                    </div>

                    <!-- Section Update Status Pengerjaan -->
                    <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100 text-xs">
                        <label class="block text-gray-700 font-bold mb-1.5">Status Pengerjaan Saat Ini:</label>
                        <select wire:model="pengerjaanStatus" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-gray-800 text-xs focus:ring-2 focus:ring-[#0052FF]/20 focus:border-[#0052FF]">
                            <option value="pengecekan">Pengecekan</option>
                            <option value="perbaikan">Perbaikan</option>
                            <option value="selesai">Selesai</option>
                            <option value="batal">Batal</option>
                        </select>
                    </div>

                    <!-- Section Billing Items (Jasa & Sparepart) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-wider">
                                Rincian Biaya Servis (Jasa &amp; Sparepart Toko)
                            </h4>
                            <span class="text-xs font-mono font-black text-[#5E17EB]">
                                Total: Rp {{ number_format($activeServis->total_biaya, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Tabel Item Billing Saat Ini -->
                        <div class="rounded-2xl border border-gray-200 overflow-hidden">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-bold text-gray-500 uppercase">
                                        <th class="py-2.5 px-3">Rincian Jasa / Sparepart Servis</th>
                                        <th class="py-2.5 px-3 text-center">Tipe</th>
                                        <th class="py-2.5 px-3 text-center">Qty</th>
                                        <th class="py-2.5 px-3 text-right">Harga</th>
                                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                                        <th class="py-2.5 px-3 text-center w-12">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($activeServis->details as $item)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="py-2.5 px-3">
                                                <span class="font-bold text-gray-900 block">{{ $item->nama_item }}</span>
                                                @if($item->masterServis?->keterangan)
                                                    <span class="text-[10px] text-gray-400 font-medium block">{{ $item->masterServis->keterangan }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                @if(($item->jenis === 'jasa') || ($item->masterServis?->jenis === 'jasa') || (is_null($item->produk_id) && ! $item->master_servis_id))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                                        Jasa
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                                        Sparepart
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 text-center font-mono font-bold">
                                                {{ $item->qty }} UNIT
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono text-gray-700">
                                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono font-black text-gray-900">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                <button type="button"
                                                        wire:click="hapusItemBilling({{ $item->id }})"
                                                        wire:loading.attr="disabled"
                                                        title="Hapus Item"
                                                        class="text-rose-500 hover:text-rose-700 disabled:opacity-50 p-1 rounded-lg cursor-pointer transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-5 text-center text-gray-400 italic font-medium">
                                                Belum ada rincian jasa servis atau sparepart yang ditambahkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Form Input Tambah Jasa / Sparepart dari Master Servis -->
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/90 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Jasa / Sparepart dari Master Servis
                                </span>
                                @if($selectedMasterServisId)
                                    <button type="button" wire:click="resetBillingInput" class="text-[11px] font-bold text-rose-600 hover:underline cursor-pointer">
                                        Reset Pilihan
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                
                                <!-- Input Pencarian Master Servis -->
                                <div class="sm:col-span-7 relative">
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Cari dari Katalog Master Servis:</label>
                                    
                                    @if($selectedMasterServisId)
                                        <div class="flex items-center justify-between px-3 py-2 bg-purple-50 border border-purple-200 rounded-xl text-xs">
                                            <div class="truncate pr-2">
                                                <div class="flex items-center space-x-1.5">
                                                    <span class="font-black text-gray-900 block truncate">{{ $selectedMasterServisNama }}</span>
                                                    <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded {{ $selectedMasterServisJenis === 'jasa' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                        {{ $selectedMasterServisJenis === 'jasa' ? 'Jasa' : 'Sparepart' }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] text-purple-700 font-bold font-mono">Rp {{ number_format($selectedMasterServisHarga, 0, ',', '.') }}</span>
                                            </div>
                                            <button type="button" wire:click="resetBillingInput" class="text-gray-400 hover:text-rose-600 p-1 cursor-pointer shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    @else
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="sparepartSearch"
                                                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]" />
                                            
                                            <!-- Dropdown Suggestions -->
                                            @if($sparepartSuggestions->isNotEmpty() && ! $selectedMasterServisId)
                                                <div class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-gray-200 z-50 max-h-56 overflow-y-auto divide-y divide-gray-100">
                                                    @foreach($sparepartSuggestions as $sp)
                                                        <button type="button"
                                                                wire:click="selectMasterServis({{ $sp->id }})"
                                                                class="w-full p-2.5 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer transition-colors">
                                                            <div class="truncate pr-2">
                                                                <div class="flex items-center space-x-1.5">
                                                                    <span class="font-bold text-gray-900 block truncate">{{ $sp->nama_item }}</span>
                                                                    <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded {{ $sp->jenis === 'jasa' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-[#5E17EB] border border-purple-200' }}">
                                                                        {{ $sp->jenis === 'jasa' ? 'Jasa' : 'Sparepart' }}
                                                                    </span>
                                                                </div>
                                                                <span class="text-[10px] text-gray-400 font-mono">{{ $sp->kode_item ?: ($sp->kategori_perangkat_label ?? '-') }}</span>
                                                            </div>
                                                            <div class="text-right shrink-0">
                                                                <span class="font-mono font-black text-[#5E17EB] block">Rp {{ number_format($sp->harga_jual, 0, ',', '.') }}</span>
                                                                <span class="text-[10px] text-gray-400">{{ $sp->garansi_default_hari > 0 ? 'Garansi ' . $sp->garansi_default_hari . 'hr' : 'No Garansi' }}</span>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @error('selectedMasterServisId') <span class="text-[10px] text-rose-600 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <!-- Input Qty -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">
                                        Jumlah / Qty:
                                    </label>
                                    <input type="number"
                                           wire:model="sparepartQty"
                                           min="1"
                                           class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono font-bold text-center" />
                                    @error('sparepartQty') <span class="text-[10px] text-rose-600 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <!-- Tombol Tambah -->
                                <div class="sm:col-span-3">
                                    <button type="button"
                                            wire:click="tambahItemBilling"
                                            wire:loading.attr="disabled"
                                            class="w-full py-2 bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer flex items-center justify-center gap-1.5 transition-all">
                                        <svg wire:loading wire:target="tambahItemBilling" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span wire:loading.remove wire:target="tambahItemBilling">+ Tambah Item</span>
                                        <span wire:loading wire:target="tambahItemBilling">Menambahkan...</span>
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- Catatan Pengerjaan Tambahan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Diagnosa / Tindakan Servis:</label>
                        <textarea wire:model="catatanPengerjaan" rows="2" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium"></textarea>
                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-between shrink-0">
                    <button type="button" wire:click="closePengerjaanModal" class="px-4 py-2 rounded-xl text-gray-600 font-bold text-xs hover:bg-gray-200 cursor-pointer">
                        Tutup
                    </button>

                    <div class="flex items-center space-x-2">
                        <button type="button"
                                wire:click="simpanPengerjaan"
                                wire:loading.attr="disabled"
                                class="px-6 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 disabled:opacity-50 text-white font-black text-xs shadow-md shadow-blue-600/25 transition-all cursor-pointer flex items-center gap-2">
                            <svg wire:loading wire:target="simpanPengerjaan" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="simpanPengerjaan">Simpan &amp; Update Status</span>
                            <span wire:loading wire:target="simpanPengerjaan">Menyimpan Perubahan...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 2: KASIR PELUNASAN & PENGAMBILAN UNIT    -->
    <!-- ============================================== -->
    @if($modalCheckoutOpen && $checkoutServis)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- Header Modal -->
                <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shrink-0 shadow-sm">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-white tracking-tight leading-snug">Kasir Pelunasan Servis</h3>
                            <p class="text-xs text-purple-200 font-medium font-mono mt-0.5">{{ $checkoutServis->no_servis_frontend }} &bull; {{ $checkoutServis->customer?->nama }}</p>
                        </div>
                    </div>

                    <button type="button" wire:click="closeCheckoutModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-6 overflow-y-auto space-y-5 text-xs">
                    
                    <!-- Ringkasan Total Tagihan -->
                    <div class="p-5 rounded-2xl bg-gray-50 border border-gray-200/90 space-y-2.5">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Total Biaya Jasa:</span>
                            <span class="font-mono font-bold">Rp {{ number_format($checkoutServis->total_jasa, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Total Biaya Sparepart:</span>
                            <span class="font-mono font-bold">Rp {{ number_format($checkoutServis->total_sparepart, 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                            <span class="font-black text-gray-900 uppercase text-xs">TOTAL PELUNASAN:</span>
                            <span class="font-mono text-xl font-black text-[#5E17EB]">Rp {{ number_format($checkoutServis->total_biaya, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Garansi Toko Selector -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-800 uppercase tracking-wider">Masa Berlaku Garansi Toko:</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" wire:click="setGaransi(0)" class="py-2.5 px-1 rounded-xl text-center font-bold border transition-all cursor-pointer {{ $garansiHari === 0 && empty($customGaransiHari) ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                                Tanpa Garansi
                            </button>
                            <button type="button" wire:click="setGaransi(7)" class="py-2.5 px-1 rounded-xl text-center font-bold border transition-all cursor-pointer {{ $garansiHari === 7 && empty($customGaransiHari) ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                                7 Hari
                            </button>
                            <button type="button" wire:click="setGaransi(30)" class="py-2.5 px-1 rounded-xl text-center font-bold border transition-all cursor-pointer {{ $garansiHari === 30 && empty($customGaransiHari) ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                                30 Hari
                            </button>
                            <button type="button" wire:click="setGaransi(90)" class="py-2.5 px-1 rounded-xl text-center font-bold border transition-all cursor-pointer {{ $garansiHari === 90 && empty($customGaransiHari) ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                                90 Hari
                            </button>
                        </div>
                    </div>

                    <!-- Catatan Pelunasan / Pengambilan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Pelunasan / Keterangan (Opsional):</label>
                        <textarea wire:model="catatanPelunasan" rows="2" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium"></textarea>
                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-between shrink-0">
                    <button type="button" wire:click="closeCheckoutModal" class="px-4 py-2 rounded-xl text-gray-600 font-bold text-xs hover:bg-gray-200 cursor-pointer">
                        Batal
                    </button>

                    <button type="button"
                            wire:click="simpanPelunasan"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs shadow-md shadow-emerald-600/25 transition-all cursor-pointer inline-flex items-center space-x-2">
                        <svg wire:loading wire:target="simpanPelunasan" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <svg wire:loading.remove wire:target="simpanPelunasan" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span wire:loading.remove wire:target="simpanPelunasan">Proses Pelunasan &amp; Cetak Nota</span>
                        <span wire:loading wire:target="simpanPelunasan">Memproses Pelunasan...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>

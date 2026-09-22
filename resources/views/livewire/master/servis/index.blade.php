<div class="space-y-6 pb-12">

    <!-- ============================================== -->
    <!-- FLASH NOTIFICATIONS                            -->
    <!-- ============================================== -->
    @if(session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            <div class="flex items-center space-x-2.5">
                <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-800 p-1 rounded-lg transition-colors cursor-pointer">&times;</button>
        </div>
    @endif

    @if(session()->has('warning'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- HEADER & BREADCRUMB                            -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>{{ (auth()->user()?->isOwner() && session('selected_cabang_id') === 'all') ? 'Kantor Pusat' : 'Soulmate Komputer' }}</span>
                <span>/</span>
                <span class="text-[#5E17EB] font-bold">Master Servis &amp; Sparepart</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                Master Servis
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Katalog master jasa perbaikan dan suku cadang untuk penerbitan nota servis.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="openCreateModal"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-bold text-white transition-all shadow-md shadow-purple-600/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Servis</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card-custom bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Layanan Jasa</span>
                <span class="text-2xl font-black text-blue-600 font-mono mt-0.5 block">{{ $totalJasa }}</span>
                <span class="text-[11px] text-gray-400 block mt-0.5">Jasa aktif di sistem</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>

        <div class="card-custom bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Sparepart</span>
                <span class="text-2xl font-black text-[#5E17EB] font-mono mt-0.5 block">{{ $totalSparepart }}</span>
                <span class="text-[11px] text-gray-400 block mt-0.5">Suku cadang aktif</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
            </div>
        </div>

        <div class="card-custom bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Master Item</span>
                <span class="text-2xl font-black text-gray-900 font-mono mt-0.5 block">{{ $totalJasa + $totalSparepart }}</span>
                <span class="text-[11px] text-gray-400 block mt-0.5">Siap digunakan pada nota servis</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center border border-gray-200 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- TOOLBAR PENCARIAN & FILTER                     -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-3.5 sm:p-4 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col lg:flex-row items-center justify-between gap-3">

        <!-- Universal Loose Search Bar -->
        <div class="w-full lg:w-96 relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] transition-all" />
        </div>

        <!-- Filter Controls -->
        <div class="w-full lg:w-auto flex flex-wrap items-center gap-2.5">

            <!-- Filter Jenis (Jasa / Sparepart) -->
            <select wire:model.live="jenisFilter" class="px-3.5 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] cursor-pointer">
                <option value="semua">Semua Jenis</option>
                <option value="jasa">Jasa Servis Saja</option>
                <option value="sparepart">Sparepart Saja</option>
            </select>

            <!-- Filter Kategori Perangkat -->
            <select wire:model.live="kategoriFilter" class="px-3.5 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] cursor-pointer">
                <option value="semua">Semua Perangkat</option>
                <option value="laptop">Laptop</option>
                <option value="printer">Printer</option>
                <option value="komputer">Komputer</option>
                <option value="lainnya">Lainnya</option>
            </select>

            <!-- Filter Status -->
            <select wire:model.live="statusFilter" class="px-3.5 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] cursor-pointer">
                <option value="semua">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Non-Aktif</option>
            </select>

            <!-- Filter Stok (Sparepart) -->
            <select wire:model.live="stokFilter" class="px-3.5 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] cursor-pointer">
                <option value="semua">Semua Stok</option>
                <option value="tersedia">Stok Tersedia</option>
                <option value="kritis">Stok Kritis</option>
                <option value="habis">Stok Habis</option>
            </select>

            <x-table-size-selector />
        </div>

    </div>

    <!-- ============================================== -->
    <!-- TABEL MASTER SERVIS & SPAREPART                -->
    <!-- ============================================== -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="card-custom bg-white overflow-hidden border border-gray-200/80 shadow-xs rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs table-fixed min-w-[960px] border-collapse">
                <colgroup>
                    <col style="width: 90px;">
                    <col style="width: 250px;">
                    <col style="width: 105px;">
                    <col style="width: 95px;">
                    <col style="width: 110px;">
                    <col style="width: 120px;">
                    <col style="width: 130px;">
                    <col style="width: 95px;">
                    <col style="width: 95px;">
                    <col style="width: 80px;">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-200/80 bg-gray-50/80 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                        <x-th-sort field="kode_item" :sortField="$sortField" :sortDirection="$sortDirection">Kode</x-th-sort>
                        <x-th-sort field="nama_item" :sortField="$sortField" :sortDirection="$sortDirection">Nama Servis</x-th-sort>
                        <x-th-sort field="jenis" :sortField="$sortField" :sortDirection="$sortDirection">Jenis</x-th-sort>
                        <x-th-sort field="kategori_perangkat" :sortField="$sortField" :sortDirection="$sortDirection">Kategori</x-th-sort>
                        <x-th-sort field="harga_modal" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Harga Modal</x-th-sort>
                        <x-th-sort field="harga_jual" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Tarif Jual</x-th-sort>
                        <x-th-sort field="stok" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Stok</x-th-sort>
                        <x-th-sort field="garansi_default_hari" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Garansi</x-th-sort>
                        <x-th-sort field="is_aktif" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Status</x-th-sort>
                        <th class="py-3.5 px-4 text-center font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader (Strict Alignment) -->
                <tbody wire:key="master-servis-skeleton"
                       wire:loading.delay
                       wire:target="search, jenisFilter, kategoriFilter, statusFilter, stokFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 bg-white">
                    @for($i = 0; $i < 6; $i++)
                        <tr class="animate-pulse">
                            <td class="py-3.5 px-4"><div class="h-3.5 bg-gray-200 rounded w-14"></div></td>
                            <td class="py-3.5 px-4"><div class="h-3.5 bg-gray-200 rounded w-44 mb-1"></div><div class="h-2.5 bg-gray-100 rounded w-24"></div></td>
                            <td class="py-3.5 px-4"><div class="h-5 bg-gray-200 rounded-full w-16"></div></td>
                            <td class="py-3.5 px-4"><div class="h-3.5 bg-gray-200 rounded w-14"></div></td>
                            <td class="py-3.5 px-4 text-right"><div class="h-3.5 bg-gray-200 rounded w-16 ml-auto"></div></td>
                            <td class="py-3.5 px-4 text-right"><div class="h-4 bg-gray-200 rounded w-20 ml-auto"></div></td>
                            <td class="py-3.5 px-4 text-center"><div class="h-5 bg-gray-200 rounded-full w-20 mx-auto"></div></td>
                            <td class="py-3.5 px-4 text-center"><div class="h-3.5 bg-gray-200 rounded w-12 mx-auto"></div></td>
                            <td class="py-3.5 px-4 text-center"><div class="h-5 bg-gray-200 rounded-full w-14 mx-auto"></div></td>
                            <td class="py-3.5 px-4 text-center"><div class="h-7 bg-gray-100 rounded-xl w-14 mx-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <!-- Real Data -->
                <tbody wire:key="master-servis-data"
                       wire:loading.remove
                       wire:target="search, jenisFilter, kategoriFilter, statusFilter, stokFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($items as $item)
                        @php
                            $stk = $item->jenis === 'sparepart' ? $item->stoks->firstWhere('cabang_id', $activeCabang?->id) : null;
                            $stkQty = $stk ? $stk->jumlah : 0;
                            $stkMin = $stk ? $stk->min_stok : 0;
                            $isZero = $item->jenis === 'sparepart' && $item->is_aktif && $stkQty <= 0;
                            $isCrit = $item->jenis === 'sparepart' && $item->is_aktif && ! $isZero && $stkQty <= $stkMin;
                        @endphp
                        <tr wire:key="ms-row-{{ $item->id }}" class="transition-colors group {{ $isZero ? 'bg-rose-50/40 hover:bg-rose-50/60' : ($isCrit ? 'bg-amber-50/30 hover:bg-amber-50/50' : 'hover:bg-purple-50/20') }}">

                            <!-- Kode Item -->
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-500 {{ $isZero ? 'border-l-4 border-rose-500 pl-3' : ($isCrit ? 'border-l-4 border-amber-500 pl-3' : '') }}">
                                {{ $item->kode_item ?: '-' }}
                            </td>

                            <!-- Nama Item & Keterangan -->
                            <td class="py-3.5 px-4 min-w-[200px]">
                                <div class="space-y-1">
                                    <div class="flex items-center flex-wrap gap-1.5">
                                        <span class="font-bold text-gray-900 block group-hover:text-[#5E17EB] transition-colors">
                                            {{ $item->nama_item }}
                                        </span>
                                        @if($isZero)
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-rose-600 text-white text-[10px] font-black shadow-xs tracking-wider">
                                                <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                <span>HABIS</span>
                                            </span>
                                        @elseif($isCrit)
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] font-black shadow-xs tracking-wider">
                                                <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                <span>KRITIS (SISA {{ $stkQty }})</span>
                                            </span>
                                        @endif
                                    </div>
                                    @if($item->keterangan)
                                        <span class="text-[10px] text-gray-400 block line-clamp-1">
                                            {{ $item->keterangan }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Jenis Badge -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($item->jenis === 'jasa')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200/80">
                                        Jasa Servis
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 text-[#5E17EB] border border-purple-200/80">
                                        Sparepart
                                    </span>
                                @endif
                            </td>

                            <!-- Kategori Perangkat -->
                            <td class="py-3.5 px-4 whitespace-nowrap font-medium text-gray-700">
                                {{ $item->kategori_perangkat_label }}
                            </td>

                            <!-- Harga Modal -->
                            <td class="py-3.5 px-4 text-right font-mono text-gray-500 whitespace-nowrap">
                                {{ $item->harga_modal > 0 ? 'Rp ' . number_format($item->harga_modal, 0, ',', '.') : '-' }}
                            </td>

                            <!-- Harga Jual -->
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900 whitespace-nowrap">
                                Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                            </td>

                            <!-- Stok Fisik (Khusus Sparepart) -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap font-mono">
                                @if($item->jenis === 'sparepart')
                                    @if($stkQty <= 0)
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>0 Unit (Habis)</span>
                                        </span>
                                    @elseif($stkQty <= $stkMin)
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>{{ $stkQty }} Unit (Kritis)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>{{ $stkQty }} Unit</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 font-mono text-[11px]">- (Jasa)</span>
                                @endif
                            </td>

                            <!-- Garansi Default -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap font-mono text-gray-600">
                                {{ $item->garansi_default_hari > 0 ? $item->garansi_default_hari . ' Hari' : 'Tanpa Garansi' }}
                            </td>

                            <!-- Status Aktif -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button type="button"
                                        wire:click="toggleAktif({{ $item->id }})"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold cursor-pointer transition-colors {{ $item->is_aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' }}">
                                    {{ $item->is_aktif ? 'Aktif' : 'Non-Aktif' }}
                                </button>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center space-x-1">
                                    <button type="button"
                                            wire:click="editItem({{ $item->id }})"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#5E17EB] hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Edit Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button type="button"
                                            wire:click="confirmDelete({{ $item->id }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus / Nonaktifkan Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-14 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center border border-gray-100">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    </div>
                                    <p class="font-bold text-gray-700 text-sm">Tidak ada data layanan servis atau sparepart</p>
                                    <p class="text-xs text-gray-400">Silakan ubah kata kunci pencarian atau tambahkan item master baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Infinite Scroll Sentinel -->
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
            <x-infinite-scroll-sentinel
                :has-more="$hasMore ?? false"
                target="loadMore"
                label="item servis"
                :current="$items->count()"
                :total="$totalItems ?? $items->count()"
                color="purple" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL FORM TAMBAH / EDIT MASTER SERVIS         -->
    <!-- ============================================== -->
    @if($modalFormOpen)
        <div wire:key="modal-master-servis-form" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeFormModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100 text-xs">

                    <form wire:submit="save">
                        <!-- Header Modal -->
                        <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                    @if($jenis === 'sparepart')
                                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    @else
                                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                        {{ $isEditMode ? 'Edit Item Servis' : 'Tambah Item Servis' }}
                                    </h3>
                                    <p class="text-xs text-purple-200 font-medium mt-0.5">Konfigurasi tarif jasa, suku cadang, dan stok nota servis</p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeFormModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0" title="Tutup">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <!-- Body Modal Form -->
                        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">

                            <!-- 1. Pilihan Jenis Item (Segmented Cards) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">
                                    Pilih Jenis Item Servis <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">

                                    <!-- Card Jasa -->
                                    <button type="button"
                                            wire:click="setJenis('jasa')"
                                            class="p-4 rounded-2xl border text-left transition-all cursor-pointer flex items-start space-x-3.5 {{ $jenis === 'jasa' ? 'border-2 border-blue-600 bg-blue-50/40 shadow-xs ring-2 ring-blue-600/10' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/50' }}">
                                        <div class="w-10 h-10 rounded-xl {{ $jenis === 'jasa' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center shrink-0 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-sm text-gray-900 {{ $jenis === 'jasa' ? 'text-blue-900' : '' }}">Jasa Servis</span>
                                                @if($jenis === 'jasa')
                                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-gray-500 mt-1 leading-snug">Perbaikan, instalasi sistem, pembersihan &amp; penanganan teknisi (tanpa stok fisik).</p>
                                        </div>
                                    </button>

                                    <!-- Card Sparepart -->
                                    <button type="button"
                                            wire:click="setJenis('sparepart')"
                                            class="p-4 rounded-2xl border text-left transition-all cursor-pointer flex items-start space-x-3.5 {{ $jenis === 'sparepart' ? 'border-2 border-[#5E17EB] bg-purple-50/40 shadow-xs ring-2 ring-purple-600/10' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/50' }}">
                                        <div class="w-10 h-10 rounded-xl {{ $jenis === 'sparepart' ? 'bg-[#5E17EB] text-white' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center shrink-0 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-sm text-gray-900 {{ $jenis === 'sparepart' ? 'text-purple-950' : '' }}">Sparepart / Suku Cadang</span>
                                                @if($jenis === 'sparepart')
                                                    <span class="w-2 h-2 rounded-full bg-[#5E17EB]"></span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-gray-500 mt-1 leading-snug">Komponen fisik pengganti (SSD, RAM, LCD, Baterai, Adaptor, Keyboard, dll).</p>
                                        </div>
                                    </button>

                                </div>
                            </div>

                            <!-- 2. Informasi Dasar Item -->
                            <div class="bg-gray-50/70 p-4 sm:p-5 rounded-2xl border border-gray-200/80 space-y-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 rounded-full bg-[#5E17EB]"></div>
                                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">Nama Servis</h4>
                                </div>

                                <!-- Nama Servis -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                        Nama Servis <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           wire:model="nama_item"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 font-semibold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] bg-white transition-all text-xs @error('nama_item') border-rose-400 focus:border-rose-500 focus:ring-rose-200 @enderror">
                                    @error('nama_item') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Kategori & Kode Item -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                            Kategori Perangkat <span class="text-rose-500">*</span>
                                        </label>
                                        <select wire:model="kategori_perangkat"
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 font-semibold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] bg-white transition-all text-xs cursor-pointer">
                                            <option value="semua">Semua Perangkat</option>
                                            <option value="laptop">Laptop</option>
                                            <option value="printer">Printer</option>
                                            <option value="komputer">Komputer</option>
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                            Kode Item SKU <span class="text-gray-400 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text"
                                               wire:model="kode_item"
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 font-mono font-semibold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] bg-white transition-all text-xs @error('kode_item') border-rose-400 @enderror">
                                        @error('kode_item') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Penetapan Tarif & Harga -->
                            <div class="bg-gray-50/70 p-4 sm:p-5 rounded-2xl border border-gray-200/80 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                        <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">Harga & Margin Keuntungan</h4>
                                    </div>
                                    @if((float) $harga_jual > 0 && (float) $harga_modal > 0 && (float) $harga_jual >= (float) $harga_modal)
                                        @php
                                            $untung = (float) $harga_jual - (float) $harga_modal;
                                            $marginPersen = round(($untung / (float) $harga_jual) * 100);
                                        @endphp
                                        <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span>Estimasi Laba: Rp {{ number_format($untung, 0, ',', '.') }} (+{{ $marginPersen }}%)</span>
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                            Harga Modal
                                        </label>
                                        <x-input-rupiah wire:model.live="harga_modal" />
                                        @error('harga_modal') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                            Harga Jual Servis <span class="text-rose-500">*</span>
                                        </label>
                                        <x-input-rupiah wire:model.live="harga_jual" />
                                        @error('harga_jual') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Alokasi Stok Fisik & Batas Peringatan (Hanya Muncul untuk Sparepart) -->
                            @if($jenis === 'sparepart' && $activeCabang)
                                <div class="bg-purple-50/50 p-4 sm:p-5 rounded-2xl border border-purple-200/80 space-y-4 animate-fadeIn">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 rounded-full bg-[#5E17EB]"></div>
                                            <h4 class="text-xs font-extrabold text-[#5E17EB] uppercase tracking-wider">Manajemen Stok</h4>
                                        </div>
                                        <span class="text-[11px] text-purple-600 font-medium">Peringatan otomatis saat stok &le; batas minimum</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                                Stok Fisik Saat Ini (Unit) <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="number"
                                                   min="0"
                                                   wire:model="initialStock.{{ $activeCabang->id }}"
                                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 font-mono font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] bg-white transition-all text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                                Batas Minimum Peringatan (Min Stok) <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="number"
                                                   min="0"
                                                   wire:model="minStock.{{ $activeCabang->id }}"
                                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 font-mono font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] bg-white transition-all text-xs">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- 5. Garansi, Catatan Teknis & Status -->
                            <div class="bg-gray-50/70 p-4 sm:p-5 rounded-2xl border border-gray-200/80 space-y-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">Garansi, Catatan &amp; Status</h4>
                                </div>

                                <!-- Masa Garansi Bawaan -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                        Masa Garansi Bawaan (Hari)
                                    </label>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                        <input type="number"
                                               wire:model="garansi_default_hari"
                                               min="0"
                                               class="w-full sm:w-32 px-3.5 py-2.5 rounded-xl border border-gray-200 font-mono font-semibold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] bg-white transition-all text-xs">

                                        <!-- Quick Preset Pills -->
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <button type="button" wire:click="$set('garansi_default_hari', 0)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition-colors cursor-pointer {{ $garansi_default_hari == 0 ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100' }}">0 Hari (Tanpa Garansi)</button>
                                            <button type="button" wire:click="$set('garansi_default_hari', 7)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition-colors cursor-pointer {{ $garansi_default_hari == 7 ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100' }}">7 Hari</button>
                                            <button type="button" wire:click="$set('garansi_default_hari', 14)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition-colors cursor-pointer {{ $garansi_default_hari == 14 ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100' }}">14 Hari</button>
                                            <button type="button" wire:click="$set('garansi_default_hari', 30)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition-colors cursor-pointer {{ $garansi_default_hari == 30 ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100' }}">30 Hari</button>
                                            <button type="button" wire:click="$set('garansi_default_hari', 90)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition-colors cursor-pointer {{ $garansi_default_hari == 90 ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100' }}">90 Hari</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Keterangan / Catatan Teknis -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                        Keterangan / Catatan Teknis <span class="text-gray-400 font-normal">(Opsional)</span>
                                    </label>
                                    <textarea wire:model="keterangan"
                                              rows="2"
                                              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 font-semibold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] bg-white transition-all text-xs"></textarea>
                                </div>

                                <!-- Checklist Status Aktif (Di Bawah Keterangan) -->
                                <div class="pt-1">
                                    <label for="is_aktif_checkbox" class="flex items-center space-x-3 p-3.5 bg-white rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="checkbox"
                                               id="is_aktif_checkbox"
                                               wire:model="is_aktif"
                                               class="w-4 h-4 rounded text-[#5E17EB] focus:ring-[#5E17EB] border-gray-300 cursor-pointer">
                                        <div class="select-none flex-1 flex items-center justify-between">
                                            <div>
                                                <span class="text-xs font-bold text-gray-900 block">Item Aktif</span>
                                                <span class="text-[11px] text-gray-500 block mt-0.5">Centang untuk mengaktifkan item agar dapat langsung dipilih pada nota servis.</span>
                                            </div>
                                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $is_aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                                {{ $is_aktif ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <!-- Footer Modal Actions -->
                        <div class="bg-gray-50/80 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeFormModal"
                                    class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-xs text-gray-700 hover:bg-gray-100 hover:border-gray-300 transition-colors cursor-pointer shadow-2xs">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 font-bold text-xs text-white transition-all shadow-md shadow-purple-600/20 cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="save">{{ $isEditMode ? 'Simpan Perubahan' : 'Tambahkan Item' }}</span>
                                <span wire:loading wire:target="save" class="inline-flex items-center space-x-1.5">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Menyimpan...</span>
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI HAPUS                         -->
    <!-- ============================================== -->
    @if($modalDeleteOpen)
        <div wire:key="modal-delete-master-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="cancelDelete"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs p-6 space-y-4">

                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto border border-rose-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>

                    <div class="text-center space-y-1.5">
                        <h3 class="text-base font-black text-gray-900">Konfirmasi Hapus Item Servis</h3>
                        <p class="text-xs text-gray-500">
                            Apakah Anda yakin ingin menghapus <strong>{{ $deleteItemNama }}</strong> ({{ $deleteItemJenis }})?
                        </p>
                        @if($deleteItemUsageCount > 0)
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-[11px] font-semibold text-left mt-2">
                                <strong>Catatan Riwayat:</strong> Item ini sudah terpakai pada {{ $deleteItemUsageCount }} transaksi servis. Item akan dinonaktifkan secara otomatis agar nota servis lama tidak rusak.
                            </div>
                        @endif
                    </div>

                    <div class="pt-2 flex items-center justify-end space-x-3">
                        <button type="button"
                                wire:click="cancelDelete"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 font-bold text-xs text-gray-700 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="deleteItem"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 font-bold text-xs text-white transition-all shadow-md shadow-rose-600/20 cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="deleteItem">Ya, Hapus Item</span>
                            <span wire:loading wire:target="deleteItem" class="inline-flex items-center space-x-1.5">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Menghapus...</span>
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

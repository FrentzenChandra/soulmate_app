<div x-data x-init="
    if (window.Echo) {
        window.Echo.channel('servis-updates')
            .listen('.ServisStatusUpdated', () => { $wire.$refresh(); })
            .listen('ServisStatusUpdated', () => { $wire.$refresh(); });
    }
" x-on:open-print-receipt.window="window.open($event.detail.url, '_blank')"
  x-on:open-print-invoice.window="window.open($event.detail.url, '_blank')"
  class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOMBOL AKSI UTAMA            -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Servis Masuk
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Penerimaan unit servis baru, diagnosa kerusakan, tindakan teknisi, dan antrean perbaikan unit.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-2xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Terima Servis Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash Message -->
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
    <!-- METRIC CARDS: RINGKASAN ANTREAN MASUK          -->
    <!-- ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card 1: Total Antrean Aktif -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Antrean Masuk</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalUnitMasuk }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-purple-600 mt-0.5 inline-block">
                    &bull; Pengecekan, Proses &amp; Selesai
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Pengecekan / Diagnosa -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Tahap Pengecekan</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalPengecekan }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-amber-600 mt-0.5 inline-block">
                    &bull; Antre Diagnosa Awal
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>

        <!-- Card 3: Dalam Perbaikan -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Dalam Perbaikan</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalPengerjaan }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-blue-600 mt-0.5 inline-block">
                    &bull; Sedang Dikerjakan
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>

        <!-- Card 4: Selesai / Siap Diambil -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Siap Diambil</span>
                <span class="text-2xl font-black text-emerald-600 mt-1.5 block font-mono">
                    {{ $totalSiapDiambil }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                    &bull; Menunggu Cetak Nota
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- FILTER BAR & PENCARIAN                         -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-5 space-y-4 border border-gray-100/90 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

            <!-- Pencarian Multi-Kata Longgar -->
            <div class="{{ $isHQ ? 'md:col-span-3' : 'md:col-span-4' }} relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all">
            </div>

            <!-- Filter Tipe Transaksi: Real vs Non-Real -->
            <div class="md:col-span-2">
                <select wire:model.live="tipeFilter"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                    <option value="semua">Semua Tipe</option>
                    <option value="real">Servis Real</option>
                    <option value="non_real">Servis Non-Real</option>
                </select>
            </div>

            <!-- Filter Status di Servis Masuk -->
            <div class="{{ $isHQ ? 'md:col-span-4' : 'md:col-span-3' }}">
                <select wire:model.live="statusFilter"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                    <option value="semua">Semua Status Masuk</option>
                    <option value="pengecekan">Tahap Pengecekan</option>
                    <option value="perbaikan">Tahap Perbaikan</option>
                    <option value="selesai">Tahap Selesai</option>
                </select>
            </div>

            <!-- Filter Kategori Perangkat & Size -->
            <div class="{{ $isHQ ? 'md:col-span-3' : 'md:col-span-3' }} flex items-center gap-2">
                <select wire:model.live="kategoriFilter"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                    <option value="semua">Semua Kategori Perangkat</option>
                    <option value="laptop">Laptop</option>
                    <option value="printer">Printer</option>
                    <option value="komputer">Komputer</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                <x-table-size-selector />
            </div>

            <!-- Filter Cabang (HQ Saja) -->
            @if($isHQ)
                <div class="md:col-span-12 pt-1 flex justify-end">
                    <div class="w-full sm:w-72">
                        <select wire:model.live="cabangFilter"
                                class="w-full py-2 px-3 rounded-xl border border-purple-200 bg-purple-50/40 text-xs font-bold text-[#5E17EB] focus:bg-white focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                            <option value="semua">Semua Cabang (HQ)</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL ANTREAN SERVIS MASUK (FIXED COLUMNS)     -->
    <!-- ============================================== -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="card-custom bg-white overflow-hidden border border-gray-200 shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fixed min-w-[1100px]">
                <colgroup>
                    <col style="width: 130px;">
                    <col style="width: 90px;">
                    <col style="width: 110px;">
                    <col style="width: 140px;">
                    <col style="width: 210px;">
                    <col style="width: 240px;">
                    @if($isHQ)
                        <col style="width: 100px;">
                    @endif
                    <col style="width: 120px;">
                    <col style="width: 160px;">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/80 text-[10px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <x-th-sort field="no_servis" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">No. Tiket</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-center">Tipe</th>
                        <x-th-sort field="tanggal_masuk" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Waktu Masuk</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Pelanggan</th>
                        <x-th-sort field="tipe_unit" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Perangkat</x-th-sort>
                        <x-th-sort field="keluhan" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Keluhan</x-th-sort>
                        @if($isHQ)
                            <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Cabang</th>
                        @endif
                        <x-th-sort field="status" :sortField="$sortField" :sortDirection="$sortDirection" align="center" class="py-2.5 px-3 text-center">Status</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query/Filter/Search/Sort/Pagination -->
                <tbody wire:key="servis-skeleton-body"
                       wire:loading.delay
                       wire:target="search, statusFilter, tipeFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 bg-white">
                    @for($i = 0; $i < 6; $i++)
                        <tr class="animate-pulse">
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2.5 px-3 text-center"><div class="h-4 bg-gray-200 rounded-full w-14 mx-auto"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-20"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-28"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-40"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-40"></div></td>
                            @if($isHQ)
                                <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-16"></div></td>
                            @endif
                            <td class="py-2.5 px-3 text-center"><div class="h-5 bg-gray-200 rounded-full w-20 mx-auto"></div></td>
                            <td class="py-2.5 px-3 text-center"><div class="h-6 bg-gray-100 rounded w-28 mx-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <tbody wire:key="servis-data-body"
                       wire:loading.remove
                       wire:target="search, statusFilter, tipeFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $item)
                        <tr wire:key="servis-row-{{ $item->id }}" class="hover:bg-purple-50/30 transition-colors group {{ $item->status === 'selesai' ? 'bg-emerald-50/15' : '' }}">

                            <!-- No Tiket Servis -->
                            <td class="py-2.5 px-3 whitespace-nowrap {{ $item->status === 'pengecekan' ? 'border-l-4 border-amber-500' : ($item->status === 'selesai' ? 'border-l-4 border-emerald-500' : 'border-l-4 border-blue-500') }}">
                                <span class="font-mono font-black text-gray-900 block group-hover:text-[#5E17EB] transition-colors leading-tight truncate">
                                    {{ $item->no_servis_frontend }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-600 block leading-tight mt-0.5 truncate">
                                    {{ $item->pembuat?->name ?? 'Staf' }}
                                </span>
                            </td>

                            <!-- Tipe Transaksi -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                @if($item->is_real)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Real</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Non-Real</span>
                                @endif
                            </td>

                            <!-- Tanggal Masuk -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="font-bold text-gray-800 block leading-tight">
                                    {{ $item->tanggal_masuk ? $item->tanggal_masuk->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-600 block leading-tight mt-0.5">
                                    {{ $item->tanggal_masuk ? $item->tanggal_masuk->format('H:i') . ' WIB' : '-' }}
                                </span>
                            </td>

                            <!-- Pelanggan -->
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-gray-900 block truncate max-w-[150px] leading-tight" title="{{ $item->customer?->nama ?? 'Pelanggan Umum' }}">
                                    {{ $item->customer?->nama ?? 'Pelanggan Umum' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-600 block leading-tight mt-0.5">
                                    {{ $item->customer?->no_hp ?? '-' }}
                                </span>
                            </td>

                            <!-- Perangkat -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <div class="flex items-center space-x-1.5 truncate">
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0 {{ $item->kategori_perangkat === 'laptop' ? 'bg-blue-100 text-blue-700' : ($item->kategori_perangkat === 'komputer' ? 'bg-purple-100 text-purple-700' : ($item->kategori_perangkat === 'printer' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700')) }}">
                                        {{ $item->kategori_perangkat_label }}
                                    </span>
                                    <span class="font-bold text-gray-900 truncate" title="{{ $item->tipe_unit }}">
                                        {{ $item->tipe_unit }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-600 font-medium truncate mt-0.5 leading-tight" title="{{ $item->kelengkapan ?: '-' }}">
                                    Kelengkapan: <span class="text-gray-700 font-semibold">{{ $item->kelengkapan ?: '-' }}</span>
                                </p>
                            </td>

                            <!-- Keluhan -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <p class="text-xs font-semibold text-gray-700 truncate leading-snug" title="{{ $item->keluhan }}">
                                    {{ $item->keluhan ?: '-' }}
                                </p>
                            </td>

                            <!-- Cabang (HQ Saja) -->
                            @if($isHQ)
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-700 block truncate max-w-[100px]">
                                        {{ $item->cabang?->nama_cabang ?? 'Pusat' }}
                                    </span>
                                </td>
                            @endif

                            <!-- Status -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black {{ $item->status === 'pengecekan' ? 'bg-amber-100 text-amber-800 border border-amber-300' : ($item->status === 'selesai' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-300') }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>

                            <!-- 5 TOMBOL AKSI LENGKAP -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">

                                    <!-- Aksi 1: Cetak Nota Faktur (Jika status Selesai) -->
                                    @if($item->status === 'selesai')
                                        <button type="button"
                                                wire:click="cetakNota({{ $item->id }})"
                                                wire:confirm="Cetak nota faktur servis ini? Tindakan ini akan memindahkan status servis dan hanya akan tampil pada menu Servis Keluar."
                                                wire:loading.attr="disabled"
                                                class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer shadow-xs disabled:opacity-50"
                                                title="Cetak Nota Faktur Servis">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </button>
                                    @endif

                                    <!-- Aksi 2: Lihat / Detail Modal -->
                                    <button type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer"
                                            title="Lihat Detail &amp; Riwayat">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- Aksi 3: Rubah Status Cepat (Logo Kunci Inggris) -->
                                    <button type="button"
                                            wire:click="openStatusModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0052FF] transition-colors cursor-pointer"
                                            title="Ubah Status Pengerjaan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </button>

                                    <!-- Aksi 4: Cetak Tanda Terima Masuk -->
                                    <a href="{{ route('servis.tanda-terima', $item->id) }}"
                                       target="_blank"
                                       class="p-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-[#5E17EB] transition-colors cursor-pointer"
                                       title="Cetak Tanda Terima Servis">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </a>

                                    <!-- Aksi 5: Tindakan & Komponen -->
                                    <button type="button"
                                            wire:click="openTindakanModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors cursor-pointer"
                                            title="Tindakan, Komponen &amp; Selesaikan Servis">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 8 : 7 }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <p class="font-bold text-gray-600">Belum ada data servis masuk ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau klik "Terima Servis Baru".</p>
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
                :has-more="$hasMore"
                target="loadMore"
                label="unit servis"
                :current="$servisList->count()"
                :total="$totalItems"
                color="purple" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: FORM PENERIMAAN SERVIS BARU           -->
    <!-- ============================================== -->
    @if($modalCreateOpen)
        <div wire:key="modal-create-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeCreateModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Penerimaan Servis Baru</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Input unit masuk, keluhan kerusakan, dan cetak tanda terima</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCreateModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanServisMasuk">
                        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">

                            @error('general')
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 font-bold">
                                    {{ $message }}
                                </div>
                            @enderror

                            <!-- Pilihan Tipe Servis & Tanggal Masuk -->
                            <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">Tipe Servis</span>
                                    <div class="flex items-center space-x-2">
                                        <button type="button"
                                                wire:click="$set('isReal', true)"
                                                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all cursor-pointer {{ $isReal ? 'bg-[#5E17EB] text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                                            Servis Real
                                        </button>
                                        <button type="button"
                                                wire:click="$set('isReal', false)"
                                                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all cursor-pointer {{ ! $isReal ? 'bg-amber-500 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                                            Servis Non-Real
                                        </button>
                                    </div>
                                </div>

                                @if(! $isReal)
                                    <div class="pt-2 border-t border-purple-100/80 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="block font-bold text-gray-700 text-xs">
                                                Tanggal Masuk <span class="text-rose-500">*</span>
                                            </label>
                                        </div>
                                        <input type="datetime-local"
                                               wire:model="customTanggalMasuk"
                                               class="w-full px-3.5 py-2 rounded-xl border border-amber-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-amber-200 focus:border-amber-500">
                                        @error('customTanggalMasuk') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        <p class="text-[10px] text-gray-500">
                                            &bull; Servis Non-Real tidak akan mengurangi stok sparepart gudang cabang dan tidak dimasukkan ke laporan omzet kas.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Data Pelanggan -->
                            <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">1. Data Pelanggan</span>
                                    <div class="flex items-center space-x-2">
                                        <button type="button"
                                                wire:click="$set('customerType', 'langganan')"
                                                class="px-2.5 py-1 rounded-lg font-bold text-[10px] transition-all cursor-pointer {{ $customerType === 'langganan' ? 'bg-[#5E17EB] text-white shadow-2xs' : 'bg-gray-200 text-gray-700' }}">
                                            Pilih Terdaftar
                                        </button>
                                        <button type="button"
                                                wire:click="$set('customerType', 'baru')"
                                                class="px-2.5 py-1 rounded-lg font-bold text-[10px] transition-all cursor-pointer {{ $customerType === 'baru' ? 'bg-[#5E17EB] text-white shadow-2xs' : 'bg-gray-200 text-gray-700' }}">
                                            + Pelanggan Baru
                                        </button>
                                    </div>
                                </div>

                                @if($customerType === 'langganan')
                                    <div class="space-y-2">
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="customerSearch"
                                                   class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @if(!empty($customerSuggestions) && !$selectedCustomerId)
                                                <div class="absolute z-10 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100">
                                                    @foreach($customerSuggestions as $cs)
                                                        <button type="button"
                                                                wire:key="sugg-cust-{{ $cs->id }}"
                                                                wire:click="selectCustomer({{ $cs->id }}, '{{ $cs->nama }}')"
                                                                class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                            <span class="font-bold text-gray-900">{{ $cs->nama }}</span>
                                                            <span class="font-mono text-gray-500">{{ $cs->no_hp }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @if($selectedCustomerId)
                                            <div class="p-2 bg-purple-50 border border-purple-200 rounded-xl flex items-center justify-between text-[11px] text-purple-900 font-bold">
                                                <span>Pelanggan Terpilih: <strong>{{ $customerSearch }}</strong></span>
                                                <button type="button" wire:click="$set('selectedCustomerId', null)" class="text-rose-500 hover:underline">Ganti</button>
                                            </div>
                                        @endif
                                        @error('selectedCustomerId') <span class="text-[11px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block font-bold text-gray-700 mb-1">Nama Lengkap Pelanggan <span class="text-rose-500">*</span></label>
                                            <input type="text"
                                                   wire:model="newCustomerNama"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @error('newCustomerNama') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block font-bold text-gray-700 mb-1">WhatsApp <span class="text-rose-500">*</span></label>
                                            <input type="text"
                                                   wire:model="newCustomerNoHp"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @error('newCustomerNoHp') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Detail Perangkat -->
                            <div class="space-y-3">
                                <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">2. Detail Perangkat &amp; Unit</span>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'laptop')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'laptop' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Laptop
                                    </button>
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'printer')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'printer' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Printer
                                    </button>
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'komputer')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'komputer' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Komputer
                                    </button>
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'lainnya')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'lainnya' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Lainnya
                                    </button>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Tipe Perangkat <span class="text-rose-500">*</span></label>
                                    <input type="text"
                                           wire:model="tipeUnit"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    @error('tipeUnit') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Kelengkapan Bawaan Unit</label>
                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        @foreach(['Unit', 'Charger', 'Kabel Power', 'Tas Laptop', 'Box', 'Flashdisk', 'Harddisk'] as $chip)
                                            <button type="button"
                                                    wire:key="chip-{{ Str::slug($chip) }}"
                                                    wire:click="toggleKelengkapanChip('{{ $chip }}')"
                                                    class="px-3 py-1 rounded-lg text-xs font-bold border transition-all cursor-pointer {{ in_array($chip, $kelengkapanChips) ? 'bg-[#5E17EB] text-white border-[#5E17EB] shadow-2xs' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                                                {{ $chip }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <input type="text"
                                           wire:model="customKelengkapan"
                                           class="w-full px-3.5 py-2 rounded-xl border border-gray-200 bg-gray-50/60 text-xs font-semibold text-gray-800 focus:bg-white focus:ring-1 focus:ring-[#5E17EB]">
                                </div>
                            </div>

                            <!-- Keluhan Kerusakan -->
                            <div class="space-y-3">
                                <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">3. Keluhan &amp; Catatan Servis</span>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1">Keluhan Kerusakan <span class="text-rose-500">*</span></label>
                                        <textarea wire:model="keluhan"
                                                  rows="3"
                                                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]"></textarea>
                                        @error('keluhan') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1">Catatan Servis (Opsional)</label>
                                        <textarea wire:model="catatan"
                                                  rows="3"
                                                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]"></textarea>
                                        @error('catatan') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeCreateModal"
                                    class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-bold text-white transition-all shadow-md shadow-purple-600/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading wire:target="simpanServisMasuk" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="simpanServisMasuk">Simpan &amp; Cetak Tanda Terima</span>
                                <span wire:loading wire:target="simpanServisMasuk">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL SUKSES & CETAK TANDA TERIMA SERVIS       -->
    <!-- ============================================== -->
    @if($modalSuccessOpen && $lastCreatedServis)
        <div wire:key="modal-success-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/80 backdrop-blur-xs transition-opacity" wire:click="$set('modalSuccessOpen', false)"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs p-6 space-y-4">

                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border border-emerald-100 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>

                    <div class="text-center space-y-1">
                        <h3 class="text-lg font-black text-gray-900">Unit Servis Berhasil Diterima!</h3>
                        <p class="text-xs text-gray-500">Nomor tiket servis resmi berhasil dibuat ke dalam sistem.</p>
                    </div>

                    <div class="p-4 bg-purple-50 rounded-2xl border border-purple-100 text-center space-y-1">
                        <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider block">KODE TIKET SERVIS</span>
                        <span class="text-xl font-black text-[#5E17EB] font-mono tracking-wider block">
                            {{ $lastCreatedServis->no_servis_frontend }}
                        </span>
                        <span class="text-[11px] font-bold text-gray-700 block">
                            {{ $lastCreatedServis->tipe_unit }} &bull; {{ $lastCreatedServis->customer?->nama }}
                        </span>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('servis.tanda-terima', $lastCreatedServis->id) }}"
                           target="_blank"
                           class="flex-1 py-3 px-4 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs text-center shadow-md shadow-purple-600/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Tanda Terima</span>
                        </a>
                        <button type="button"
                                wire:click="$set('modalSuccessOpen', false)"
                                class="py-3 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 2: DETAIL UNIT & SUB-ACTIONS             -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedServis)
        <div wire:key="modal-detail-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal Detail -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-bold text-white font-mono tracking-tight leading-snug">
                                        {{ $selectedServis->no_servis_frontend }}
                                    </h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white/20 text-white border border-white/30">
                                        {{ $selectedServis->kategori_perangkat_label }}
                                    </span>
                                    @if(! $selectedServis->is_real)
                                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-amber-400 text-amber-950">
                                            NON-REAL
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">
                                    Masuk: {{ $selectedServis->tanggal_masuk ? $selectedServis->tanggal_masuk->translatedFormat('d F Y - H:i') . ' WIB' : '-' }}
                                </p>
                            </div>
                        </div>

                        <button wire:click="closeDetailModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Sub-Action Toolbar di dalam Modal Detail -->
                    <div class="px-6 py-2.5 bg-gray-50/80 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Aksi Khusus:</span>
                        <div class="flex items-center space-x-1.5">
                            <!-- Sub-Aksi: Edit Data Unit -->
                            <button type="button"
                                    wire:click="openEditModal({{ $selectedServis->id }})"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 hover:text-[#0052FF] hover:border-blue-200 font-bold text-[10px] transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span>Edit Unit</span>
                            </button>

                            <!-- Sub-Aksi: Catatan Teknisi -->
                            <button type="button"
                                    wire:click="openCatatanTeknisiModal({{ $selectedServis->id }})"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 hover:text-[#5E17EB] hover:border-purple-200 font-bold text-[10px] transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                <span>Catatan Teknisi</span>
                            </button>

                            <!-- Sub-Aksi: Hapus Servis -->
                            <button type="button"
                                    wire:click="openDeleteModal({{ $selectedServis->id }}, '{{ $selectedServis->no_servis_frontend }}')"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 font-bold text-[10px] transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>

                    <!-- Body Modal Detail -->
                    <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar text-xs">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Pelanggan</span>
                                <span class="font-black text-gray-900 block mt-1 truncate">{{ $selectedServis->customer?->nama ?? 'Umum' }}</span>
                                <span class="text-[10px] text-gray-500 font-mono block">{{ $selectedServis->customer?->no_hp ?? '-' }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Status Saat Ini</span>
                                <span class="font-black text-gray-900 block mt-1">{{ $selectedServis->status_label }}</span>
                                <span class="text-[10px] text-purple-600 font-bold block">{{ $selectedServis->cabang?->nama_cabang }}</span>
                            </div>
                        </div>

                        <!-- Rincian Unit & Keluhan -->
                        <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/80 space-y-2">
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Tipe / Merk Unit</span>
                                <span class="font-black text-gray-900 block">{{ $selectedServis->tipe_unit }}</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Kelengkapan Bawaan</span>
                                <span class="font-bold text-gray-800 block">{{ $selectedServis->kelengkapan ?: 'Hanya Unit' }}</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Keluhan Kerusakan</span>
                                <p class="font-bold text-gray-900 mt-0.5 whitespace-pre-line leading-relaxed">{{ $selectedServis->keluhan }}</p>
                            </div>
                            @if($selectedServis->catatan)
                                <div class="pt-2 border-t border-gray-200">
                                    <span class="text-[10px] font-bold text-purple-600 uppercase block">Catatan Teknisi</span>
                                    <p class="font-semibold text-gray-800 mt-0.5 whitespace-pre-line">{{ $selectedServis->catatan }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Timeline Riwayat Status Log -->
                        <div>
                            <span class="text-xs font-extrabold text-gray-900 uppercase tracking-wider block mb-3">
                                Riwayat Perjalanan Status Servis
                            </span>
                            <div class="space-y-3 pl-2 border-l-2 border-purple-200">
                                @forelse($selectedServis->statusLogs as $log)
                                    <div wire:key="detail-log-{{ $log->id }}" class="relative pl-4">
                                        <span class="absolute -left-[11px] top-1.5 w-3 h-3 rounded-full bg-[#5E17EB] ring-4 ring-purple-100"></span>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-black text-gray-900 capitalize">{{ str_replace('_', ' ', $log->status) }}</span>
                                            <span class="text-[10px] font-mono text-gray-400">&bull; {{ $log->tanggal ? $log->tanggal->translatedFormat('d M Y, H:i') : '-' }}</span>
                                            <span class="text-[10px] text-gray-500 font-bold">({{ $log->user?->name ?? 'Sistem' }})</span>
                                        </div>
                                        @if($log->catatan)
                                            <p class="text-[11px] text-gray-600 mt-0.5 font-medium">{{ $log->catatan }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-gray-400 italic text-[11px]">Belum ada catatan log tambahan.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <!-- Footer Modal Detail -->
                    <div class="bg-gray-50 px-6 py-4 flex flex-wrap items-center justify-between gap-2 rounded-b-3xl border-t border-gray-100">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('servis.tanda-terima', $selectedServis->id) }}"
                               target="_blank"
                               class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-xs font-bold text-white transition-all cursor-pointer shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Tanda Terima</span>
                            </a>

                            @if($selectedServis->status === 'selesai')
                                <button type="button"
                                        wire:click="cetakNota({{ $selectedServis->id }})"
                                        wire:confirm="Cetak nota faktur servis ini? Tindakan ini akan memindahkan status servis dan hanya akan tampil pada menu Servis Keluar."
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition-all cursor-pointer shadow-xs disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    <span>Cetak Nota Faktur</span>
                                </button>
                            @endif
                        </div>

                        <button type="button"
                                wire:click="closeDetailModal"
                                class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 3: EDIT DATA UNIT SERVIS                 -->
    <!-- ============================================== -->
    @if($modalEditOpen)
        <div wire:key="modal-edit-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeEditModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 text-xs">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Edit Data Unit Servis</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Perbarui rincian tipe unit, kelengkapan, atau keluhan</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanEditServis">
                        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">

                            <!-- Kategori Perangkat -->
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Kategori Perangkat <span class="text-rose-500">*</span></label>
                                <select wire:model="editKategoriPerangkat"
                                        class="w-full px-3 py-2 rounded-xl border border-gray-300 font-bold text-gray-800">
                                    <option value="laptop">Laptop</option>
                                    <option value="printer">Printer</option>
                                    <option value="komputer">Komputer</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Tipe Perangkat <span class="text-rose-500">*</span></label>
                                <input type="text"
                                       wire:model="editTipeUnit"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-300 font-bold text-gray-800">
                                @error('editTipeUnit') <span class="text-rose-500 font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Kelengkapan Bawaan</label>
                                <div class="flex flex-wrap gap-1.5 mb-2">
                                    @foreach(['Unit', 'Charger', 'Kabel Power', 'Tas Laptop', 'Box', 'Flashdisk', 'Harddisk'] as $chip)
                                        <button type="button"
                                                wire:key="edit-chip-{{ Str::slug($chip) }}"
                                                wire:click="toggleEditKelengkapanChip('{{ $chip }}')"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold border transition-all cursor-pointer {{ in_array($chip, $editKelengkapanChips) ? 'bg-[#5E17EB] text-white border-[#5E17EB]' : 'bg-white text-gray-600 border-gray-200' }}">
                                            {{ $chip }}
                                        </button>
                                    @endforeach
                                </div>
                                <input type="text"
                                       wire:model="editCustomKelengkapan"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-xs">
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Keluhan Kerusakan <span class="text-rose-500">*</span></label>
                                <textarea wire:model="editKeluhan"
                                          rows="3"
                                          class="w-full px-3 py-2 rounded-xl border border-gray-300 font-semibold text-gray-800"></textarea>
                                @error('editKeluhan') <span class="text-rose-500 font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Catatan Staf / Teknisi</label>
                                <textarea wire:model="editCatatan"
                                          rows="2"
                                          class="w-full px-3 py-2 rounded-xl border border-gray-300 font-semibold text-gray-800"></textarea>
                            </div>

                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button" wire:click="closeEditModal" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs transition-all shadow-md cursor-pointer inline-flex items-center space-x-2">
                                <svg wire:loading wire:target="simpanEditServis" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 4: CATATAN TEKNISI                       -->
    <!-- ============================================== -->
    @if($modalCatatanTeknisiOpen)
        <div wire:key="modal-catatan-teknisi" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeCatatanTeknisiModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Catatan Teknisi</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Rincian temuan atau log teknisi</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCatatanTeknisiModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanCatatanTeknisi">
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1.5">Tulis Catatan / Temuan Teknisi:</label>
                                <textarea wire:model="catatanTeknisiText"
                                          rows="5"
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]"></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button" wire:click="closeCatatanTeknisiModal" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-white font-bold text-xs transition-all shadow-md cursor-pointer inline-flex items-center space-x-2">
                                <svg wire:loading wire:target="simpanCatatanTeknisi" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Simpan Catatan</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 5: HAPUS SERVIS                          -->
    <!-- ============================================== -->
    @if($modalDeleteOpen)
        <div wire:key="modal-delete-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-gray-100 text-xs p-6 space-y-4">

                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto border border-rose-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>

                    <div class="text-center space-y-1">
                        <h3 class="text-base font-black text-gray-900">Hapus Data Servis?</h3>
                        <p class="text-xs text-gray-500">Anda yakin ingin menghapus data servis <span class="font-mono font-bold text-gray-800">{{ $deleteServisKode }}</span>? Data yang dihapus tidak dapat dipulihkan.</p>
                    </div>

                    <div class="flex items-center justify-center space-x-2 pt-2">
                        <button type="button" wire:click="closeDeleteModal" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="konfirmasiHapusServis"
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-rose-600/20 cursor-pointer inline-flex items-center space-x-2">
                            <svg wire:loading wire:target="konfirmasiHapusServis" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Ya, Hapus Data</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 6: UPDATE STATUS CEPAT (KUNCI INGGRIS)   -->
    <!-- ============================================== -->
    @if($modalStatusOpen && $servisToUpdate)
        <div wire:key="modal-status-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeStatusModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Update Status Pengerjaan</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Perbarui status progres servis</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeStatusModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanUpdateStatus">
                        <div class="p-6 space-y-4">

                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Tiket Servis</span>
                                <span class="text-sm font-black text-gray-900 font-mono block">{{ $servisToUpdate->no_servis_frontend }}</span>
                                <span class="text-xs text-gray-600 font-bold block">{{ $servisToUpdate->tipe_unit }} ({{ $servisToUpdate->customer?->nama }})</span>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1.5">Pilih Status Baru <span class="text-rose-500">*</span></label>
                                <select wire:model="updateStatus"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    <option value="pengecekan">Pengecekan</option>
                                    <option value="perbaikan">Perbaikan</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="batal">Batal Servis</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Catatan Perkembangan / Log</label>
                                <textarea wire:model="updateCatatan"
                                          rows="3"
                                          class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]"></textarea>
                            </div>

                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button" wire:click="closeStatusModal" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs transition-all shadow-md cursor-pointer inline-flex items-center space-x-2">
                                <svg wire:loading wire:target="simpanUpdateStatus" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Simpan Status</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 7: TINDAKAN & KOMPONEN (DENGAN SELESAI)  -->
    <!-- ============================================== -->
    @if($modalTindakanOpen && $activeServis)
        <div wire:key="modal-tindakan-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeTindakanModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-100 text-xs">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Tindakan Jasa &amp; Komponen Servis</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5 truncate">
                                    {{ $activeServis->no_servis_frontend }} &bull; {{ $activeServis->tipe_unit }} ({{ $activeServis->customer?->nama }})
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeTindakanModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0" title="Tutup">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal Tindakan -->
                    <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">

                        <!-- Form Tambah Tindakan / Komponen -->
                        <div class="p-4 bg-gray-50/90 rounded-2xl border border-gray-200 space-y-3.5">

                            <!-- Toggle Pilihan Sumber: Layanan Jasa vs Sparepart Produk -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-gray-200/80">
                                <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">
                                    + Tambah Rincian Servis
                                </span>
                                <div class="inline-flex p-1 rounded-xl bg-gray-200/70 space-x-1">
                                    <button type="button"
                                            wire:click="setItemType('jasa')"
                                            class="px-3 py-1.5 rounded-lg font-bold text-xs transition-all cursor-pointer inline-flex items-center space-x-1.5 {{ $itemType === 'jasa' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <span>Layanan Jasa</span>
                                    </button>
                                    <button type="button"
                                            wire:click="setItemType('sparepart')"
                                            class="px-3 py-1.5 rounded-lg font-bold text-xs transition-all cursor-pointer inline-flex items-center space-x-1.5 {{ $itemType === 'sparepart' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                        <span>Sparepart (Produk)</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-12 gap-3">

                                <!-- Autocomplete Input (Zero Placeholder Standard) -->
                                <div class="col-span-12 relative">
                                    <label class="block font-bold text-gray-700 mb-1">
                                        {{ $itemType === 'jasa' ? 'Cari Layanan Jasa (Master Servis)' : 'Cari Sparepart (Master Produk)' }}
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               wire:model.live.debounce.250ms="itemSearch"
                                               class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100">
                                        @if($selectedMasterServisId || $selectedProdukId)
                                            <button type="button" wire:click="resetItemForm" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-rose-500 cursor-pointer" title="Reset Pilihan">
                                                &times;
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Dropdown Saran Layanan Jasa -->
                                    @if($itemType === 'jasa' && !empty($masterServisSuggestions) && !$selectedMasterServisId)
                                        <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                            @foreach($masterServisSuggestions as $ms)
                                                <button type="button"
                                                        wire:key="sugg-ms-{{ $ms->id }}"
                                                        wire:click="selectMasterItem({{ $ms->id }}, '{{ $ms->nama_item }}', 'jasa', {{ $ms->harga_jual }}, {{ $ms->garansi_default_hari ?? 0 }})"
                                                        class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                    <div>
                                                        <span class="font-bold text-gray-900 block">{{ $ms->nama_item }}</span>
                                                        <span class="text-[10px] text-gray-400 uppercase font-mono">{{ $ms->kategori_perangkat_label }} &bull; Garansi {{ $ms->garansi_default_hari ?? 0 }} Hari</span>
                                                    </div>
                                                    <span class="font-mono font-bold text-blue-700">Rp {{ number_format($ms->harga_jual, 0, ',', '.') }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Dropdown Saran Sparepart dari Master Produk -->
                                    @if($itemType === 'sparepart' && !empty($produkSuggestions) && !$selectedProdukId)
                                        <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                            @foreach($produkSuggestions as $prod)
                                                @php
                                                    $stkCabang = $prod->stoks->firstWhere('cabang_id', $activeServis->cabang_id)?->jumlah ?? 0;
                                                @endphp
                                                <button type="button"
                                                        wire:key="sugg-prod-{{ $prod->id }}"
                                                        wire:click="selectProdukItem({{ $prod->id }}, '{{ $prod->nama_produk }}', {{ $prod->harga_jual }}, {{ $stkCabang }}, 30)"
                                                        class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                    <div>
                                                        <span class="font-bold text-gray-900 block">{{ $prod->nama_produk }}</span>
                                                        <div class="flex items-center space-x-2 mt-0.5">
                                                            <span class="text-[10px] text-gray-400 font-mono">{{ $prod->barcode ?: 'Tanpa Barcode' }}</span>
                                                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded {{ $stkCabang > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                                                Stok: {{ $stkCabang }} Unit
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="font-mono font-bold text-purple-700">Rp {{ number_format($prod->harga_jual, 0, ',', '.') }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <!-- Nama Item Terpilih / Input Manual -->
                                <div class="col-span-12 sm:col-span-6">
                                    <label class="block font-bold text-gray-700 mb-1">Nama Item <span class="text-rose-500">*</span></label>
                                    <input type="text"
                                           wire:model="selectedMasterServisNama"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800">
                                </div>

                                <!-- Qty -->
                                <div class="col-span-6 sm:col-span-3">
                                    <label class="block font-bold text-gray-700 mb-1">
                                        Qty
                                        @if($itemType === 'sparepart' && $selectedStokTersedia !== null)
                                            <span class="text-[10px] font-normal text-purple-700 font-mono">(Sisa {{ $selectedStokTersedia }})</span>
                                        @endif
                                    </label>
                                    <input type="number"
                                           min="1"
                                           wire:model.live="itemQty"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 text-center font-mono">
                                </div>

                                <!-- Garansi (Hari) -->
                                <div class="col-span-6 sm:col-span-3">
                                    <label class="block font-bold text-gray-700 mb-1">Garansi (Hari)</label>
                                    <input type="number"
                                           min="0"
                                           wire:model="itemGaransiHari"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-mono font-bold text-gray-800 text-center">
                                </div>

                                <!-- Harga Satuan -->
                                <div class="col-span-12 sm:col-span-6">
                                    <label class="block font-bold text-gray-700 mb-1">Harga Satuan <span class="text-rose-500">*</span></label>
                                    <x-input-rupiah wire:model="selectedMasterServisHarga" inputClass="!py-2 !text-xs !font-bold font-mono" />
                                </div>

                                <!-- Tombol Tambah -->
                                <div class="col-span-12 sm:col-span-6 flex items-end justify-end">
                                    <button type="button"
                                            wire:click="tambahItemTindakan"
                                            wire:loading.attr="disabled"
                                            class="w-full py-2.5 px-5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-purple-600/20 transition-all cursor-pointer inline-flex items-center justify-center space-x-2">
                                        <svg wire:loading wire:target="tambahItemTindakan" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span>+ Tambahkan ke Rincian Servis</span>
                                    </button>
                                </div>

                            </div>
                        </div>

                        <!-- Daftar Rincian Tindakan yang sudah masuk -->
                        <div>
                            <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block mb-2">
                                Rincian Biaya Tindakan &amp; Komponen
                            </span>

                            <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                                            <th class="py-2.5 px-3.5">Item / Tindakan</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Tipe</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Garansi</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Qty</th>
                                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Harga</th>
                                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Subtotal</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-xs">
                                        @forelse($activeServis->details as $det)
                                            <tr wire:key="tindakan-det-{{ $det->id }}" class="hover:bg-gray-50/50">
                                                <td class="py-2.5 px-3.5 font-bold text-gray-900">
                                                    {{ $det->nama_item }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                    @if($det->jenis === 'jasa')
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                                            Layanan Jasa
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                                            Sparepart
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] font-mono font-bold whitespace-nowrap {{ $det->garansi_hari > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-gray-400' }}">
                                                        {{ $det->garansi_label }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold whitespace-nowrap">{{ $det->qty }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono text-gray-600 whitespace-nowrap">Rp {{ number_format($det->harga, 0, ',', '.') }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono font-black text-gray-900 whitespace-nowrap">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                    <button type="button"
                                                            wire:click="hapusItemTindakan({{ $det->id }})"
                                                            class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
                                                            title="Hapus Item">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-6 text-center text-gray-400 italic">
                                                    Belum ada tindakan atau komponen yang ditambahkan.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50/90 border-t border-gray-200">
                                            <td colspan="5" class="py-3 px-3 font-black text-right uppercase tracking-wider text-gray-700 whitespace-nowrap">Total Biaya Servis:</td>
                                            <td class="py-3 px-3 font-mono font-black text-base text-[#5E17EB] text-right whitespace-nowrap">
                                                Rp {{ number_format($activeServis->details->sum('subtotal'), 0, ',', '.') }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Modal Tindakan DENGAN TOMBOL SELESAI -->
                    <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-b-3xl border-t border-gray-100">
                        <button type="button"
                                wire:click="closeTindakanModal"
                                class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer text-xs">
                            Simpan &amp; Tutup
                        </button>

                        <button type="button"
                                wire:click="selesaikanServis"
                                wire:confirm="Selesaikan pengerjaan unit servis ini? Status akan diperbarui menjadi Selesai."
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center space-x-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/25 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading.remove wire:target="selesaikanServis" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg wire:loading wire:target="selesaikanServis" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="selesaikanServis">Selesaikan Pengerjaan Unit</span>
                            <span wire:loading wire:target="selesaikanServis">Menyelesaikan...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

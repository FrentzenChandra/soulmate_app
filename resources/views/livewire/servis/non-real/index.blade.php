<div x-data x-init="
    if (window.Echo) {
        window.Echo.channel('servis-updates')
            .listen('.ServisStatusUpdated', () => { $wire.$refresh(); })
            .listen('ServisStatusUpdated', () => { $wire.$refresh(); });
    }
" x-on:open-print-receipt.window="window.location.href = $event.detail.url"
  x-on:open-print-invoice.window="window.location.href = $event.detail.url"
  class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOMBOL AKSI UTAMA            -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Servis Non Real
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                    Servis Non Real
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Pencatatan data servis non real, cetak tanda terima &amp; faktur, serta kelola rincian tindakan dan komponen tanpa memotong stok fisik.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-2xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Terima Servis Non Real</span>
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
    <!-- METRIC CARDS: RINGKASAN SERVIS NON REAL        -->
    <!-- ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card 1: Total Unit Non Real -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Servis Non Real</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalUnit }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-purple-600 mt-0.5 inline-block">
                    &bull; Keseluruhan Data
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Tahap Pengecekan -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Tahap Pengecekan</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalPengecekan }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-amber-600 mt-0.5 inline-block">
                    &bull; Antre Diagnosa
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

        <!-- Card 4: Selesai / Siap Ambil -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Selesai Dikerjakan</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalSelesai }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                    &bull; Siap Diambil Pelanggan
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- TABEL UTAMA SERVIS NON REAL                    -->
    <!-- ============================================== -->
    <div class="card-custom bg-white overflow-hidden border border-gray-100 shadow-xs"
         x-data="{
             tableSize: localStorage.getItem('soulmate_table_size') || 'M',
             getTextSize() {
                 return this.tableSize === 'S' ? 'text-[11px]' : (this.tableSize === 'L' ? 'text-[13px]' : 'text-xs');
             },
             getPadding() {
                 return this.tableSize === 'S' ? 'py-1.5 px-2.5' : (this.tableSize === 'L' ? 'py-3.5 px-4' : 'py-2.5 px-3');
             }
         }"
         @table-size-changed.window="tableSize = $event.detail">

        <!-- Toolbar Filter & Search -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-col lg:flex-row items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">

                <!-- Input Pencarian Multi-Kata -->
                <div class="relative flex-1 sm:w-64">
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           class="w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-[#5E17EB] focus:border-transparent transition-all" />
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Filter Status -->
                <select wire:model.live="statusFilter"
                        class="py-2 px-3 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Status</option>
                    <option value="pengecekan">Pengecekan</option>
                    <option value="perbaikan">Dalam Perbaikan</option>
                    <option value="selesai">Selesai</option>
                    <option value="sudah_diambil">Sudah Diambil</option>
                    <option value="batal">Batal</option>
                </select>

                <!-- Filter Kategori Perangkat -->
                <select wire:model.live="kategoriFilter"
                        class="py-2 px-3 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Perangkat</option>
                    <option value="laptop">Laptop</option>
                    <option value="printer">Printer</option>
                    <option value="komputer">Komputer</option>
                    <option value="lainnya">Lainnya</option>
                </select>

                <!-- Filter Cabang (HQ Only) -->
                @if($isHQ)
                    <select wire:model.live="cabangFilter"
                            class="py-2 px-3 bg-purple-50 border border-purple-200 rounded-xl text-xs font-bold text-[#5E17EB] focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                        <option value="semua">Semua Cabang</option>
                        @foreach($cabangs as $cab)
                            <option value="{{ $cab->id }}">{{ $cab->nama_cabang }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <!-- Table Font Size Selector -->
            <div class="flex items-center space-x-2 self-end lg:self-auto">
                <x-table-size-selector />
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200/80 bg-gray-50/80 text-[11px] font-black uppercase tracking-wider text-gray-500">
                        <th class="py-3 px-3 cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('no_servis')">
                            <div class="flex items-center space-x-1">
                                <span>No. Servis</span>
                                @if($sortField === 'no_servis')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-3 cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('tanggal_masuk')">
                            <div class="flex items-center space-x-1">
                                <span>Tgl Masuk</span>
                                @if($sortField === 'tanggal_masuk')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-3">Pelanggan</th>
                        <th class="py-3 px-3 cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('tipe_unit')">
                            <div class="flex items-center space-x-1">
                                <span>Perangkat</span>
                                @if($sortField === 'tipe_unit')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-3">Keluhan</th>
                        @if($isHQ)
                            <th class="py-3 px-3">Cabang</th>
                        @endif
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader -->
                <tbody wire:loading.delay
                       wire:target="search, statusFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 animate-pulse">
                    @for($i = 0; $i < 6; $i++)
                        <tr>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-28"></div></td>
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

                <tbody wire:key="servis-non-real-body"
                       wire:loading.remove
                       wire:target="search, statusFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $item)
                        <tr wire:key="servis-row-{{ $item->id }}" class="hover:bg-purple-50/30 transition-colors group {{ $item->status === 'selesai' ? 'bg-emerald-50/15' : '' }}">

                            <!-- No Tiket Servis -->
                            <td class="py-2.5 px-3 whitespace-nowrap {{ $item->status === 'pengecekan' ? 'border-l-4 border-amber-500' : ($item->status === 'selesai' ? 'border-l-4 border-emerald-500' : ($item->status === 'batal' ? 'border-l-4 border-rose-500' : 'border-l-4 border-blue-500')) }}">
                                <span class="font-mono font-black text-gray-900 block group-hover:text-[#5E17EB] transition-colors leading-tight truncate">
                                    {{ $item->no_servis_frontend }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-600 block leading-tight mt-0.5 truncate">
                                    {{ $item->pembuat?->name ?? 'Staf' }}
                                </span>
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black {{ $item->status === 'pengecekan' ? 'bg-amber-100 text-amber-800 border border-amber-300' : ($item->status === 'selesai' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($item->status === 'batal' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-blue-100 text-blue-800 border border-blue-300')) }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>

                            <!-- TOMBOL AKSI LENGKAP -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">

                                    <!-- Aksi 1: Cetak Tanda Terima Masuk -->
                                    <a href="{{ route('servis.tanda-terima', $item->id) }}"
                                       class="p-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-[#5E17EB] transition-colors cursor-pointer"
                                       title="Cetak Tanda Terima Servis">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </a>

                                    <!-- Aksi 2: Cetak Faktur Servis -->
                                    <a href="{{ route('servis.invoice', $item->id) }}"
                                       class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors cursor-pointer"
                                       title="Cetak Faktur Servis">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>

                                    <!-- Aksi 3: Lihat / Detail Modal -->
                                    <button type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer"
                                            title="Lihat Detail &amp; Riwayat">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- Aksi 4: Edit Rincian Servis (Tindakan & Komponen) -->
                                    <button type="button"
                                            wire:click="openTindakanModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition-colors cursor-pointer"
                                            title="Edit Rincian Servis (Tambah Produk &amp; Jasa)">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    </button>

                                    <!-- Aksi 5: Ubah Status -->
                                    <button type="button"
                                            wire:click="openStatusModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0052FF] transition-colors cursor-pointer"
                                            title="Ubah Status Pengerjaan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </button>

                                    <!-- Aksi 6: Edit Data Unit -->
                                    <button type="button"
                                            wire:click="openEditModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer"
                                            title="Edit Data Unit Servis">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- Aksi 7: Hapus Servis -->
                                    @if(auth()->user()->isOwner() || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin'))
                                        <button type="button"
                                                wire:click="openDeleteModal({{ $item->id }})"
                                                class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors cursor-pointer"
                                                title="Hapus Data Servis">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 8 : 7 }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="font-bold text-gray-600">Belum ada data servis non real ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau klik "Terima Servis Non Real".</p>
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
                label="unit servis non real"
                :current="$servisList->count()"
                :total="$totalItems"
                color="purple" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: FORM PENERIMAAN SERVIS NON REAL BARU  -->
    <!-- ============================================== -->
    @if($modalCreateOpen)
        <div wire:key="modal-create-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeCreateModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-[#0E1726] text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-300 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">Terima Servis Non Real Baru</h3>
                                <p class="text-xs text-gray-400">Pencatatan nota servis non real tanpa memotong stok fisik.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit.prevent="simpanServisMasuk" class="p-6 space-y-5">
                        @if ($errors->has('general'))
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-600 font-bold">
                                {{ $errors->first('general') }}
                            </div>
                        @endif

                        <!-- Tanggal Masuk Khusus Non-Real -->
                        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-3">
                            <div>
                                <label class="block text-xs font-black text-amber-900 mb-1">
                                    Tanggal Masuk Servis
                                </label>
                                <input type="datetime-local"
                                       wire:model="customTanggalMasuk"
                                       class="w-full px-3.5 py-2 bg-white border border-amber-300 rounded-xl text-xs font-bold text-gray-900 focus:ring-2 focus:ring-[#5E17EB]" />
                                <p class="text-[11px] text-amber-700 font-medium mt-1">
                                    Biarkan kosong jika ingin menggunakan waktu sekarang (otomatis).
                                </p>
                            </div>
                        </div>

                        <!-- 1. Pemilihan / Input Pelanggan -->
                        <div class="space-y-3 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black uppercase tracking-wider text-gray-700 flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span>Data Pelanggan</span> <span class="text-rose-500 font-bold">*</span>
                                </label>

                                <div class="inline-flex p-0.5 rounded-xl bg-gray-200 text-xs font-bold">
                                    <button type="button"
                                            wire:click="$set('customerType', 'langganan')"
                                            class="px-3 py-1 rounded-lg transition-all {{ $customerType === 'langganan' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                        Pilih Terdaftar
                                    </button>
                                    <button type="button"
                                            wire:click="$set('customerType', 'baru')"
                                            class="px-3 py-1 rounded-lg transition-all {{ $customerType === 'baru' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                        + Pelanggan Baru
                                    </button>
                                </div>
                            </div>

                            @if($customerType === 'langganan')
                                <div class="relative">
                                    <input type="text"
                                           wire:model.live.debounce.250ms="customerSearch"
                                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                    @if(!empty($customerSuggestions))
                                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-20 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                            @foreach($customerSuggestions as $cs)
                                                <button type="button"
                                                        wire:click="selectCustomer({{ $cs->id }}, '{{ addslashes($cs->nama) }} - {{ $cs->no_hp ?? 'Tanpa No HP' }}')"
                                                        class="w-full text-left px-3.5 py-2 hover:bg-purple-50 text-xs flex items-center justify-between transition-colors">
                                                    <span class="font-bold text-gray-800">{{ $cs->nama }}</span>
                                                    <span class="text-[11px] font-mono text-gray-500">{{ $cs->no_hp ?? '-' }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('selectedCustomerId') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Lengkap Pelanggan <span class="text-rose-500 font-bold">*</span></label>
                                        <input type="text"
                                               wire:model="newCustomerNama"
                                               class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                        @error('newCustomerNama') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-1">WhatsApp / No. HP</label>
                                        <input type="text"
                                               wire:model="newCustomerNoHp"
                                               class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                        @error('newCustomerNoHp') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 2. Kategori Perangkat & Tipe Unit -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Kategori Perangkat <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <select wire:model="kategoriPerangkat"
                                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                                    <option value="laptop">Laptop</option>
                                    <option value="printer">Printer</option>
                                    <option value="komputer">Komputer PC</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Merek &amp; Model Unit <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text"
                                       wire:model="tipeUnit"
                                       class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                @error('tipeUnit') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- 3. Kelengkapan Unit (Chips) -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1.5">
                                Kelengkapan Unit Yang Diterima
                            </label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach(['Unit', 'Charger', 'Tas', 'Kabel Power', 'Dus / Box'] as $chip)
                                    <button type="button"
                                            wire:click="toggleKelengkapanChip('{{ $chip }}')"
                                            class="px-3 py-1 rounded-xl text-xs font-bold transition-all cursor-pointer {{ in_array($chip, $kelengkapanChips) ? 'bg-[#5E17EB] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        {{ in_array($chip, $kelengkapanChips) ? '&check;' : '+' }} {{ $chip }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="text"
                                   wire:model="customKelengkapan"
                                   class="w-full px-3.5 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]" />
                        </div>

                        <!-- 4. Keluhan / Kerusakan -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                Keluhan / Indikasi Kerusakan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea wire:model="keluhan"
                                      rows="2"
                                      class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-[#5E17EB]"></textarea>
                            @error('keluhan') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- 5. Kondisi Fisik & Catatan Tambahan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Kondisi Fisik Saat Masuk
                                </label>
                                <textarea wire:model="kondisiBarang"
                                          rows="2"
                                          class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-[#5E17EB]"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Catatan Internal Toko
                                </label>
                                <textarea wire:model="catatan"
                                          rows="2"
                                          class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-[#5E17EB]"></textarea>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <button type="button"
                                    wire:click="closeCreateModal"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/25 transition-all cursor-pointer disabled:opacity-50">
                                <svg wire:loading wire:target="simpanServisMasuk" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="simpanServisMasuk">Simpan Servis Non Real</span>
                                <span wire:loading wire:target="simpanServisMasuk">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 2: EDIT DATA UNIT SERVIS                 -->
    <!-- ============================================== -->
    @if($modalEditOpen)
        <div wire:key="modal-edit-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeEditModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-[#0E1726] text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-300 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">Edit Data Unit Servis Non Real</h3>
                                <p class="text-xs text-gray-400">Koreksi informasi perangkat, pelanggan, atau keluhan servis.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit.prevent="simpanEditServis" class="p-6 space-y-5">
                        @if ($errors->has('editGeneral'))
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-600 font-bold">
                                {{ $errors->first('editGeneral') }}
                            </div>
                        @endif

                        <!-- Ganti Pelanggan -->
                        <div class="space-y-1.5 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700">
                                Pelanggan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text"
                                   wire:model.live.debounce.250ms="editCustomerSearch"
                                   class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                            @error('editCustomerId') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kategori & Tipe Unit -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Kategori Perangkat <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <select wire:model="editKategoriPerangkat"
                                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                                    <option value="laptop">Laptop</option>
                                    <option value="printer">Printer</option>
                                    <option value="komputer">Komputer PC</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Merek &amp; Model Unit <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text"
                                       wire:model="editTipeUnit"
                                       class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                @error('editTipeUnit') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Kelengkapan Chips -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1.5">
                                Kelengkapan Unit
                            </label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach(['Unit', 'Charger', 'Tas', 'Kabel Power', 'Dus / Box'] as $chip)
                                    <button type="button"
                                            wire:click="toggleEditKelengkapanChip('{{ $chip }}')"
                                            class="px-3 py-1 rounded-xl text-xs font-bold transition-all cursor-pointer {{ in_array($chip, $editKelengkapanChips) ? 'bg-[#5E17EB] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        {{ in_array($chip, $editKelengkapanChips) ? '&check;' : '+' }} {{ $chip }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="text"
                                   wire:model="editCustomKelengkapan"
                                   class="w-full px-3.5 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:bg-white focus:ring-2 focus:ring-[#5E17EB]" />
                        </div>

                        <!-- Keluhan -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                Keluhan / Kerusakan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea wire:model="editKeluhan"
                                      rows="2"
                                      class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-[#5E17EB]"></textarea>
                            @error('editKeluhan') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kondisi & Catatan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Kondisi Fisik
                                </label>
                                <textarea wire:model="editKondisiBarang"
                                          rows="2"
                                          class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-[#5E17EB]"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                    Catatan Internal
                                </label>
                                <textarea wire:model="editCatatan"
                                          rows="2"
                                          class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-[#5E17EB]"></textarea>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <button type="button"
                                    wire:click="closeEditModal"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/25 transition-all cursor-pointer disabled:opacity-50">
                                <svg wire:loading wire:target="simpanEditServis" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="simpanEditServis">Simpan Perubahan</span>
                                <span wire:loading wire:target="simpanEditServis">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 3: DETAIL LENGKAP & RIWAYAT SERVIS       -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedServis)
        <div wire:key="modal-detail-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-100">

                    <!-- Header Modal -->
                    <div class="px-6 py-4 bg-[#0E1726] text-white flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-3">
                                <h3 class="text-base font-extrabold text-white">Detail Servis Non Real</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    {{ $selectedServis->no_servis_frontend }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">Informasi unit, tindakan rincian, dan riwayat status pengerjaan.</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">

                        <!-- Info Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 block">Informasi Pelanggan</span>
                                <p class="text-sm font-extrabold text-gray-900">{{ $selectedServis->customer?->nama ?? 'Pelanggan Umum' }}</p>
                                <p class="text-xs text-gray-600 font-mono">{{ $selectedServis->customer?->no_hp ?? '-' }}</p>
                            </div>

                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 block">Perangkat &amp; Kelengkapan</span>
                                <p class="text-sm font-extrabold text-gray-900">{{ $selectedServis->tipe_unit }} ({{ $selectedServis->kategori_perangkat_label }})</p>
                                <p class="text-xs text-gray-600">{{ $selectedServis->kelengkapan ?: 'Hanya Unit' }}</p>
                            </div>
                        </div>

                        <!-- Keluhan & Kondisi -->
                        <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100 space-y-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 block">Keluhan / Kerusakan</span>
                            <p class="text-xs font-semibold text-gray-800">{{ $selectedServis->keluhan ?: '-' }}</p>
                            @if($selectedServis->kondisi_barang)
                                <p class="text-[11px] text-gray-600 font-medium pt-1 border-t border-amber-200/50">
                                    <span class="font-bold">Kondisi Fisik:</span> {{ $selectedServis->kondisi_barang }}
                                </p>
                            @endif
                        </div>

                        <!-- Rincian Tindakan & Biaya -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-700">Rincian Tindakan &amp; Komponen</h4>
                            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase">
                                        <tr>
                                            <th class="py-2.5 px-3">Item / Tindakan</th>
                                            <th class="py-2.5 px-3 text-center">Jenis</th>
                                            <th class="py-2.5 px-3 text-center">Qty</th>
                                            <th class="py-2.5 px-3 text-right">Harga</th>
                                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($selectedServis->details as $det)
                                            <tr>
                                                <td class="py-2.5 px-3 font-semibold text-gray-900">{{ $det->nama_item }}</td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $det->jenis === 'jasa' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                        {{ strtoupper($det->jenis) }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold">{{ $det->qty }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono">Rp {{ number_format($det->harga_satuan, 0, ',', '.') }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-gray-900">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-6 text-center text-gray-400">Belum ada rincian tindakan / sparepart.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-gray-50/80 font-bold border-t border-gray-200">
                                        <tr>
                                            <td colspan="4" class="py-2.5 px-3 text-right font-black uppercase text-[11px] text-gray-700">Total Biaya:</td>
                                            <td class="py-2.5 px-3 text-right font-mono font-black text-sm text-[#5E17EB]">
                                                Rp {{ number_format($selectedServis->total_biaya, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Riwayat Timeline Status -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-700">Riwayat Status Servis</h4>
                            <div class="space-y-2 border-l-2 border-purple-200 pl-4 ml-2">
                                @forelse($selectedServis->statusLogs as $log)
                                    <div class="relative">
                                        <span class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-[#5E17EB]"></span>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-bold text-gray-900 uppercase">{{ $log->status }}</span>
                                            <span class="text-[10px] font-mono text-gray-400">&bull; {{ $log->tanggal ? $log->tanggal->translatedFormat('d M Y, H:i') : '-' }} WIB</span>
                                            <span class="text-[10px] font-semibold text-gray-500">({{ $log->user?->name ?? 'Sistem' }})</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-0.5">{{ $log->catatan }}</p>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400">Belum ada catatan riwayat status.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <!-- Footer Modal -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('servis.tanda-terima', $selectedServis->id) }}"
                               class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-purple-50 text-[#5E17EB] hover:bg-purple-100 font-bold text-xs transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Tanda Terima</span>
                            </a>
                            <a href="{{ route('servis.invoice', $selectedServis->id) }}"
                               class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                <span>Faktur Servis</span>
                            </a>
                        </div>
                        <button type="button"
                                wire:click="closeDetailModal"
                                class="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 4: KELOLA RINCIAN SERVIS (TINDAKAN)      -->
    <!-- ============================================== -->
    @if($modalTindakanOpen && $activeServis)
        <div wire:key="modal-tindakan-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeTindakanModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-100">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-[#0E1726] text-white flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-3">
                                <h3 class="text-base font-extrabold text-white">Rincian Servis Non Real</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                    {{ $activeServis->no_servis_frontend }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $activeServis->tipe_unit }} &bull; {{ $activeServis->customer?->nama ?? 'Pelanggan Umum' }}</p>
                        </div>
                        <button type="button" wire:click="closeTindakanModal" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">

                        @if (session()->has('tindakanSuccess'))
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-bold flex items-center justify-between">
                                <span>{{ session('tindakanSuccess') }}</span>
                            </div>
                        @endif

                        @if ($errors->has('tindakanError'))
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-600 font-bold">
                                {{ $errors->first('tindakanError') }}
                            </div>
                        @endif

                        <!-- Form Input Tindakan Baru -->
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase tracking-wider text-gray-700">Tambah Item Rincian</span>
                                <div class="inline-flex p-0.5 rounded-xl bg-gray-200 text-xs font-bold">
                                    <button type="button"
                                            wire:click="$set('tindakanItemType', 'jasa')"
                                            class="px-3 py-1 rounded-lg transition-all {{ $tindakanItemType === 'jasa' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                        Jasa Servis
                                    </button>
                                    <button type="button"
                                            wire:click="$set('tindakanItemType', 'produk')"
                                            class="px-3 py-1 rounded-lg transition-all {{ $tindakanItemType === 'produk' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                        Sparepart / Produk
                                    </button>
                                </div>
                            </div>

                            @if($tindakanItemType === 'jasa')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Jasa / Tindakan <span class="text-rose-500 font-bold">*</span></label>
                                        <select wire:change="selectMasterServis($event.target.selectedOptions[0].dataset.id, $event.target.selectedOptions[0].dataset.nama, 'jasa', $event.target.selectedOptions[0].dataset.harga)"
                                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]">
                                            <option value="">Pilih dari Master Servis</option>
                                            @foreach($masterServisSuggestions as $ms)
                                                <option value="{{ $ms->id }}"
                                                        data-id="{{ $ms->id }}"
                                                        data-nama="{{ $ms->nama_layanan }}"
                                                        data-harga="{{ $ms->biaya_standard }}">
                                                    {{ $ms->nama_layanan }} - Rp {{ number_format($ms->biaya_standard, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text"
                                               wire:model="selectedMasterServisNama"
                                               class="w-full px-3 py-1.5 mt-1.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                        @error('selectedMasterServisNama') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Biaya (Rp) <span class="text-rose-500 font-bold">*</span></label>
                                        <input type="number"
                                               wire:model="selectedMasterServisHarga"
                                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                        @error('selectedMasterServisHarga') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Pilih Sparepart / Produk <span class="text-rose-500 font-bold">*</span></label>
                                        <select wire:change="selectProdukSparepart($event.target.selectedOptions[0].dataset.id, $event.target.selectedOptions[0].dataset.nama, $event.target.selectedOptions[0].dataset.harga)"
                                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]">
                                            <option value="">Pilih dari Master Produk</option>
                                            @foreach($produkSuggestions as $prd)
                                                <option value="{{ $prd->id }}"
                                                        data-id="{{ $prd->id }}"
                                                        data-nama="{{ $prd->nama_produk }}"
                                                        data-harga="{{ $prd->harga_jual }}">
                                                    {{ $prd->nama_produk }} - Rp {{ number_format($prd->harga_jual, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selectedProdukNama') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Harga Satuan (Rp) <span class="text-rose-500 font-bold">*</span></label>
                                        <input type="number"
                                               wire:model="selectedProdukHargaJual"
                                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB]" />
                                        @error('selectedProdukHargaJual') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-2">
                                <div class="flex items-center space-x-3">
                                    <div class="w-24">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Kuantitas</label>
                                        <input type="number"
                                               wire:model="itemQty"
                                               min="1"
                                               class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-center" />
                                    </div>
                                    <div class="w-28">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Garansi (Hari)</label>
                                        <input type="number"
                                               wire:model="itemGaransiHari"
                                               min="0"
                                               class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-center" />
                                    </div>
                                </div>

                                <button type="button"
                                        wire:click="tambahItemTindakan"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center space-x-1.5 px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer disabled:opacity-50">
                                    <svg wire:loading wire:target="tambahItemTindakan" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span wire:loading.remove wire:target="tambahItemTindakan">+ Tambahkan ke Rincian</span>
                                    <span wire:loading wire:target="tambahItemTindakan">Menambahkan...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tabel Rincian Saat Ini -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-700">Daftar Rincian Terpasang</h4>
                            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase">
                                        <tr>
                                            <th class="py-2.5 px-3">Item / Tindakan</th>
                                            <th class="py-2.5 px-3 text-center">Jenis</th>
                                            <th class="py-2.5 px-3 text-center">Qty</th>
                                            <th class="py-2.5 px-3 text-right">Harga</th>
                                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                                            <th class="py-2.5 px-3 text-center">Hapus</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($activeServis->details as $det)
                                            <tr>
                                                <td class="py-2.5 px-3 font-semibold text-gray-900">{{ $det->nama_item }}</td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $det->jenis === 'jasa' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                        {{ strtoupper($det->jenis) }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold">{{ $det->qty }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono">Rp {{ number_format($det->harga_satuan, 0, ',', '.') }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-gray-900">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <button type="button"
                                                            wire:click="hapusItemTindakan({{ $det->id }})"
                                                            wire:confirm="Hapus item ini dari rincian servis?"
                                                            class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                            title="Hapus item">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-6 text-center text-gray-400">Belum ada item rincian.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-gray-50/80 font-bold border-t border-gray-200">
                                        <tr>
                                            <td colspan="4" class="py-2.5 px-3 text-right font-black uppercase text-[11px] text-gray-700">Total Biaya:</td>
                                            <td class="py-2.5 px-3 text-right font-mono font-black text-sm text-[#5E17EB]">
                                                Rp {{ number_format($activeServis->total_biaya, 0, ',', '.') }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Modal -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <button type="button"
                                wire:click="closeTindakanModal"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                            Selesai &amp; Tutup
                        </button>

                        @if($activeServis->status !== 'selesai')
                            <button type="button"
                                    wire:click="selesaikanServisDariTindakan"
                                    wire:confirm="Tandai pengerjaan servis ini sebagai Selesai?"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-1.5 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/25 transition-all cursor-pointer disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Tandai Selesai</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 5: UPDATE STATUS SERVIS                  -->
    <!-- ============================================== -->
    @if($modalStatusOpen && $servisToUpdate)
        <div wire:key="modal-status-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeStatusModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <div class="px-6 py-4 bg-[#0E1726] text-white flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-extrabold text-white">Update Status Servis</h3>
                            <p class="text-xs text-gray-400 font-mono">{{ $servisToUpdate->no_servis_frontend }}</p>
                        </div>
                        <button type="button" wire:click="closeStatusModal" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanUpdateStatus" class="p-6 space-y-4">
                        @if ($errors->has('updateStatusError'))
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-600 font-bold">
                                {{ $errors->first('updateStatusError') }}
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                Status Pengerjaan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select wire:model="updateStatus"
                                    class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                                <option value="pengecekan">Pengecekan (Diagnosa)</option>
                                <option value="perbaikan">Dalam Perbaikan</option>
                                <option value="pengerjaan">Dalam Pengerjaan</option>
                                <option value="selesai">Selesai (Siap Diambil)</option>
                                <option value="sudah_diambil">Sudah Diambil</option>
                                <option value="batal">Batal Servis</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                Catatan Perubahan Status
                            </label>
                            <textarea wire:model="updateCatatan"
                                      rows="3"
                                      class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-[#5E17EB]"></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <button type="button"
                                    wire:click="closeStatusModal"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/25 transition-all cursor-pointer disabled:opacity-50">
                                <svg wire:loading wire:target="simpanUpdateStatus" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="simpanUpdateStatus">Update Status</span>
                                <span wire:loading wire:target="simpanUpdateStatus">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 6: HAPUS SERVIS                          -->
    <!-- ============================================== -->
    @if($modalDeleteOpen)
        <div wire:key="modal-delete-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <div class="px-6 py-4 bg-rose-600 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <h3 class="text-base font-extrabold text-white">Konfirmasi Hapus Servis</h3>
                        </div>
                        <button type="button" wire:click="closeDeleteModal" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-rose-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="prosesHapusServis" class="p-6 space-y-4">
                        @if ($errors->has('deleteGeneral'))
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-600 font-bold">
                                {{ $errors->first('deleteGeneral') }}
                            </div>
                        @endif

                        <p class="text-xs text-gray-600">
                            Apakah Anda yakin ingin menghapus data servis non real ini? Tindakan ini akan menghapus seluruh data tiket, detail rincian, dan riwayat status secara permanen.
                        </p>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                Alasan Penghapusan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text"
                                   wire:model="deleteAlasan"
                                   class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-rose-500" />
                            @error('deleteAlasan') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1">
                                Password Akun Anda <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="password"
                                   wire:model="deletePassword"
                                   class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-rose-500" />
                            @error('deletePassword') <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <button type="button"
                                    wire:click="closeDeleteModal"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/25 transition-all cursor-pointer disabled:opacity-50">
                                <svg wire:loading wire:target="prosesHapusServis" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="prosesHapusServis">Hapus Permanen</span>
                                <span wire:loading wire:target="prosesHapusServis">Menghapus...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 7: KONFIRMASI SUKSES & CETAK NOTA        -->
    <!-- ============================================== -->
    @if($modalSuccessOpen && $lastCreatedServis)
        <div wire:key="modal-success-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="$set('modalSuccessOpen', false)"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 p-6 space-y-5 text-center">

                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border border-emerald-100 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-black text-gray-900">Servis Non Real Berhasil Dicatat!</h3>
                        <p class="text-xs text-gray-500 mt-1">Nomor tiket servis non real telah diterbitkan:</p>
                        <p class="text-xl font-mono font-black text-[#5E17EB] mt-2 tracking-wider">{{ $lastCreatedServis->no_servis_frontend }}</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-left text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Pelanggan:</span>
                            <span class="font-bold text-gray-900">{{ $lastCreatedServis->customer?->nama ?? 'Pelanggan Umum' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Perangkat:</span>
                            <span class="font-bold text-gray-900">{{ $lastCreatedServis->tipe_unit }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center space-x-3 pt-2">
                        <button type="button"
                                wire:click="$set('modalSuccessOpen', false)"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                            Tutup
                        </button>
                        <a href="{{ route('servis.tanda-terima', $lastCreatedServis->id) }}"
                           target="_blank"
                           class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/25 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Tanda Terima</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

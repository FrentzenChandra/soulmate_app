<div x-data x-init="
    if (window.Echo) {
        window.Echo.channel('servis-updates')
            .listen('.ServisStatusUpdated', () => { $wire.$refresh(); })
            .listen('ServisStatusUpdated', () => { $wire.$refresh(); });
    }
" class="space-y-6">

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
                Pencatatan faktur servis non real, rincian tindakan teknisi, dan cetak faktur tanpa mutasi stok fisik.
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

    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- METRIC CARDS: RINGKASAN SERVIS NON REAL        -->
    <!-- ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card 1: Total Unit Transaksi -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Servis Non Real</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalUnitMasuk }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-purple-600 mt-0.5 inline-block">
                    &bull; Keseluruhan Data Non Real
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Total Pendapatan Nilai Faktur -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Nilai Faktur</span>
                <span class="text-2xl font-black text-emerald-600 mt-1.5 block font-mono">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-emerald-700 mt-0.5 inline-block">
                    &bull; Akumulasi Biaya Servis
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Total Tindakan Jasa -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Layanan Jasa</span>
                <span class="text-2xl font-black text-blue-600 mt-1.5 block font-mono">
                    {{ $totalJasa }} ITEM
                </span>
                <span class="text-[11px] font-semibold text-blue-700 mt-0.5 inline-block">
                    &bull; Tindakan Servis
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>

        <!-- Card 4: Total Sparepart Produk -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Sparepart Komponen</span>
                <span class="text-2xl font-black text-purple-600 mt-1.5 block font-mono">
                    {{ $totalSparepart }} ITEM
                </span>
                <span class="text-[11px] font-semibold text-purple-700 mt-0.5 inline-block">
                    &bull; Komponen Non-Real
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
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
                                <span>Waktu Faktur</span>
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
                        <th class="py-3 px-3 cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('total_biaya')">
                            <div class="flex items-center space-x-1">
                                <span>Total Biaya</span>
                                @if($sortField === 'total_biaya')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        @if($isHQ)
                            <th class="py-3 px-3">Cabang</th>
                        @endif
                        <th class="py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader -->
                <tbody wire:loading.delay
                       wire:target="search, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 animate-pulse">
                    @for($i = 0; $i < 6; $i++)
                        <tr>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-28"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-28"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-36"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-24"></div></td>
                            @if($isHQ)
                                <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-16"></div></td>
                            @endif
                            <td class="py-2.5 px-3 text-center"><div class="h-6 bg-gray-100 rounded w-28 mx-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <tbody wire:key="servis-non-real-body"
                       wire:loading.remove
                       wire:target="search, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $item)
                        <tr wire:key="servis-row-{{ $item->id }}" class="hover:bg-purple-50/30 transition-colors group">

                            <!-- No Tiket Servis -->
                            <td class="py-2.5 px-3 whitespace-nowrap border-l-4 border-[#5E17EB]">
                                <span class="font-mono font-black text-gray-900 block group-hover:text-[#5E17EB] transition-colors leading-tight truncate">
                                    {{ $item->no_servis_frontend }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-500 block leading-tight mt-0.5 truncate">
                                    {{ $item->pembuat?->name ?? 'Staf' }}
                                </span>
                            </td>

                            <!-- Tanggal Masuk (Faktur) -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="font-bold text-gray-800 block leading-tight">
                                    {{ $item->tanggal_masuk ? $item->tanggal_masuk->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-500 block leading-tight mt-0.5">
                                    {{ $item->tanggal_masuk ? $item->tanggal_masuk->format('H:i') . ' WIB' : '-' }}
                                </span>
                            </td>

                            <!-- Pelanggan -->
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-gray-900 block truncate max-w-[150px] leading-tight" title="{{ $item->customer?->nama ?? 'Pelanggan Umum' }}">
                                    {{ $item->customer?->nama ?? 'Pelanggan Umum' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-500 block leading-tight mt-0.5">
                                    {{ $item->customer?->no_hp ?? '-' }}
                                </span>
                            </td>

                            <!-- Perangkat -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <span class="font-bold text-gray-900 truncate block" title="{{ $item->tipe_unit }}">
                                    {{ $item->tipe_unit }}
                                </span>
                                <span class="text-[10px] text-gray-500 font-medium block mt-0.5">
                                    {{ $item->details->count() }} Rincian Item
                                </span>
                            </td>

                            <!-- Total Biaya -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="font-mono font-black text-gray-900 block text-xs">
                                    Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] font-bold text-emerald-600 block mt-0.5">
                                    Lunas (Cash)
                                </span>
                            </td>

                            <!-- Cabang (HQ Saja) -->
                            @if($isHQ)
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-700 block truncate max-w-[100px]">
                                        {{ $item->cabang?->nama_cabang ?? 'Pusat' }}
                                    </span>
                                </td>
                            @endif

                            <!-- TOMBOL AKSI LENGKAP -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">

                                    <!-- Aksi 1: Cetak Faktur Servis -->
                                    <a href="{{ route('servis.invoice', $item->id) }}"
                                       class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors cursor-pointer"
                                       title="Cetak Faktur Servis">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>

                                    <!-- Aksi 2: Lihat / Detail Modal -->
                                    <button type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer"
                                            title="Lihat Detail Transaksi">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- Aksi 3: Update / Edit Servis Non Real -->
                                    <button type="button"
                                            wire:click="openEditModal({{ $item->id }})"
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0052FF] transition-colors cursor-pointer"
                                            title="Update Servis Non Real">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- Aksi 4: Hapus Servis -->
                                    <button type="button"
                                            wire:click="openDeleteModal({{ $item->id }}, '{{ $item->no_servis_frontend }}')"
                                            class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors cursor-pointer"
                                            title="Hapus Transaksi">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 7 : 6 }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="font-bold text-gray-600">Belum ada data servis non real ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Klik "Terima Servis Non Real" untuk mencatat transaksi baru.</p>
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
    <!-- MODAL 1: FORM TAMBAH SERVIS NON REAL TERPADU   -->
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
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Terima Servis Non Real Baru</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Input faktur servis non real &amp; rincian tindakan langsung</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCreateModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanServisMasuk">
                        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">

                            @error('general')
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 font-bold">
                                    {{ $message }}
                                </div>
                            @enderror

                            <!-- 1. BARIS ATAS: WAKTU FAKTUR & DATA PELANGGAN (SEBARIS) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">

                                <!-- Kolom Kiri: Waktu Faktur Servis -->
                                <div class="p-3.5 rounded-2xl bg-purple-50/50 border border-purple-100 space-y-1.5">
                                    <label class="block font-bold text-gray-900 text-xs">
                                        Waktu Faktur Servis <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="datetime-local"
                                           wire:model="customTanggalMasuk"
                                           class="w-full px-3 py-2 rounded-xl border border-purple-200 bg-white font-bold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    @error('customTanggalMasuk') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <!-- Kolom Kanan: Data Pelanggan -->
                                <div class="bg-gray-50/80 p-3.5 rounded-2xl border border-gray-200/80 space-y-2">
                                    <label class="block font-bold text-gray-900 text-xs">
                                        Pelanggan <span class="text-rose-500 font-bold">*</span>
                                    </label>

                                    <div class="space-y-2">
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="customerSearch"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @if(!empty($customerSuggestions) && !$selectedCustomerId)
                                                <div class="absolute z-10 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                                    @foreach($customerSuggestions as $cs)
                                                        <button type="button"
                                                                wire:key="sugg-cust-{{ $cs->id }}"
                                                                wire:click="selectCustomer({{ $cs->id }}, '{{ addslashes($cs->nama) }}')"
                                                                class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                            <span class="font-bold text-gray-900">{{ $cs->nama }}</span>
                                                            <span class="font-mono text-gray-500 text-[11px]">{{ $cs->no_hp ?: '-' }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        @if($selectedCustomerId)
                                            <div class="p-1.5 bg-purple-50 border border-purple-200 rounded-xl flex items-center justify-between text-[11px] text-purple-900 font-bold">
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    {{ $customerSearch }} (Terdaftar)
                                                </span>
                                                <button type="button" wire:click="resetCustomer" class="text-rose-500 hover:underline cursor-pointer font-bold">Ganti</button>
                                            </div>
                                        @endif
                                        @error('customerSearch') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                            </div>

                            <!-- 2. TIPE PERANGKAT -->
                            <div>
                                <label class="block font-bold text-gray-800 mb-1">Tipe Perangkat <span class="text-rose-500 font-bold">*</span></label>
                                <input type="text"
                                       wire:model="tipeUnit"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                @error('tipeUnit') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                            </div>

                            <!-- 3. RINCIAN SERVIS (JASA & SPAREPART PRODUK) -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between pb-1 border-b border-gray-200">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">
                                        Rincian Servis &amp; Komponen <span class="text-rose-500 font-bold">*</span>
                                    </span>
                                    <div class="inline-flex p-0.5 rounded-lg bg-gray-200 space-x-1">
                                        <button type="button"
                                                wire:click="setItemType('jasa')"
                                                class="px-2.5 py-1 rounded-md font-bold text-[10px] transition-all cursor-pointer {{ $itemType === 'jasa' ? 'bg-[#5E17EB] text-white shadow-2xs' : 'text-gray-700 hover:text-gray-900' }}">
                                            Layanan Jasa
                                        </button>
                                        <button type="button"
                                                wire:click="setItemType('sparepart')"
                                                class="px-2.5 py-1 rounded-md font-bold text-[10px] transition-all cursor-pointer {{ $itemType === 'sparepart' ? 'bg-[#5E17EB] text-white shadow-2xs' : 'text-gray-700 hover:text-gray-900' }}">
                                            Sparepart (Produk)
                                        </button>
                                    </div>
                                </div>

                                <!-- Subform Input Item Sesuai Screenshot Desain User -->
                                <div class="p-3.5 bg-gray-50/90 rounded-2xl border border-gray-200 space-y-3">

                                    <!-- Baris 1: Cari Layanan Jasa / Sparepart -->
                                    <div class="relative">
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
                                                            wire:key="sugg-create-ms-{{ $ms->id }}"
                                                            wire:click="selectMasterItem({{ $ms->id }}, '{{ $ms->nama_item }}', {{ $ms->harga_jual }}, {{ $ms->garansi_default_hari ?? 0 }})"
                                                            class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                        <div>
                                                            <span class="font-bold text-gray-900 block">{{ $ms->nama_item }}</span>
                                                            <span class="text-[10px] text-gray-400 font-mono">Garansi {{ $ms->garansi_default_hari ?? 0 }} Hari</span>
                                                        </div>
                                                        <span class="font-mono font-bold text-blue-700">Rp {{ number_format($ms->harga_jual, 0, ',', '.') }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Dropdown Saran Sparepart Produk -->
                                        @if($itemType === 'sparepart' && !empty($produkSuggestions) && !$selectedProdukId)
                                            <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                                @foreach($produkSuggestions as $prod)
                                                    <button type="button"
                                                            wire:key="sugg-create-prod-{{ $prod->id }}"
                                                            wire:click="selectProdukItem({{ $prod->id }}, '{{ $prod->nama_produk }}', {{ $prod->harga_jual }}, 30)"
                                                            class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                        <div>
                                                            <span class="font-bold text-gray-900 block">{{ $prod->nama_produk }}</span>
                                                            <span class="text-[10px] text-gray-400 font-mono">{{ $prod->barcode ?: 'Tanpa Barcode' }}</span>
                                                        </div>
                                                        <span class="font-mono font-bold text-purple-700">Rp {{ number_format($prod->harga_jual, 0, ',', '.') }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Baris 2: Nama Item, Qty, Garansi (Hari) -->
                                    <div class="grid grid-cols-12 gap-3">
                                        <div class="col-span-12 sm:col-span-6">
                                            <label class="block font-bold text-gray-700 mb-1">Nama Item <span class="text-rose-500 font-bold">*</span></label>
                                            <input type="text"
                                                   wire:model="selectedMasterServisNama"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800">
                                            @error('selectedMasterServisNama') <span class="text-[10px] text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-6 sm:col-span-3">
                                            <label class="block font-bold text-gray-700 mb-1">Jumlah</label>
                                            <input type="number"
                                                   min="1"
                                                   wire:model.live="itemQty"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 text-center font-mono">
                                        </div>
                                        <div class="col-span-6 sm:col-span-3">
                                            <label class="block font-bold text-gray-700 mb-1">Garansi (Hari)</label>
                                            <input type="number"
                                                   min="0"
                                                   wire:model="itemGaransiHari"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 text-center font-mono">
                                        </div>
                                    </div>

                                    <!-- Baris 3: Harga Satuan & Tombol + Tambahkan ke Rincian Servis -->
                                    <div class="grid grid-cols-12 gap-3 items-end">
                                        <div class="col-span-12 sm:col-span-6">
                                            <label class="block font-bold text-gray-700 mb-1">Harga Satuan <span class="text-rose-500 font-bold">*</span></label>
                                            <x-input-rupiah wire:model="selectedMasterServisHarga" inputClass="!py-2 !text-xs !font-bold font-mono" />
                                            @error('selectedMasterServisHarga') <span class="text-[10px] text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <button type="button"
                                                    wire:click="addItemToCreate"
                                                    class="w-full py-2.5 px-4 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs transition-all shadow-md shadow-purple-600/20 cursor-pointer inline-flex items-center justify-center space-x-1.5">
                                                <span>+ Tambahkan ke Rincian Servis</span>
                                            </button>
                                        </div>
                                    </div>

                                </div>

                                <!-- Tabel Daftar Item Yang Sudah Ditambahkan -->
                                <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                                                <th class="py-2 px-3">Item</th>
                                                <th class="py-2 px-2 text-center">Tipe</th>
                                                <th class="py-2 px-2 text-center">Garansi</th>
                                                <th class="py-2 px-2 text-center">Jumlah</th>
                                                <th class="py-2 px-3 text-right">Harga</th>
                                                <th class="py-2 px-3 text-right">Subtotal</th>
                                                <th class="py-2 px-2 text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 text-xs">
                                            @forelse($createItems as $idx => $ci)
                                                <tr wire:key="create-item-row-{{ $idx }}" class="hover:bg-gray-50/50">
                                                    <td class="py-2 px-3 font-bold text-gray-900">{{ $ci['nama_item'] }}</td>
                                                    <td class="py-2 px-2 text-center">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold {{ $ci['jenis'] === 'jasa' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                                            {{ $ci['jenis'] === 'jasa' ? 'Jasa' : 'Sparepart' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-2 text-center font-mono text-[10px] text-gray-600">{{ $ci['garansi_hari'] }} Hr</td>
                                                    <td class="py-2 px-2 text-center font-mono font-bold">{{ $ci['qty'] }}</td>
                                                    <td class="py-2 px-3 text-right font-mono text-gray-600">Rp {{ number_format($ci['harga'], 0, ',', '.') }}</td>
                                                    <td class="py-2 px-3 text-right font-mono font-black text-gray-900">Rp {{ number_format($ci['subtotal'], 0, ',', '.') }}</td>
                                                    <td class="py-2 px-2 text-center">
                                                        <button type="button" wire:click="removeItemFromCreate({{ $idx }})" class="p-1 rounded text-rose-500 hover:bg-rose-50 cursor-pointer" title="Hapus Item">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="py-4 text-center text-gray-400 italic text-[11px]">
                                                        Belum ada tindakan / sparepart yang ditambahkan.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(!empty($createItems))
                                            <tfoot>
                                                <tr class="bg-purple-50/70 border-t border-purple-100 font-bold">
                                                    <td colspan="5" class="py-2.5 px-3 text-right font-black uppercase text-gray-700">Total Biaya Servis:</td>
                                                    <td class="py-2.5 px-3 font-mono font-black text-sm text-[#5E17EB] text-right">
                                                        Rp {{ number_format(array_sum(array_column($createItems, 'subtotal')), 0, ',', '.') }}
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                                @error('createItems') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
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
    <!-- MODAL 2: FORM EDIT SERVIS NON REAL TERPADU     -->
    <!-- ============================================== -->
    @if($modalEditOpen)
        <div wire:key="modal-edit-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeEditModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal Edit -->
                    <div class="bg-[#0052FF] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Update Servis Non Real</h3>
                                <p class="text-xs text-blue-200 font-medium mt-0.5">Perbarui tanggal, pelanggan, unit &amp; rincian biaya servis</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanEditServis">
                        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">

                            @error('general')
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 font-bold">
                                    {{ $message }}
                                </div>
                            @enderror

                            <!-- 1. BARIS ATAS: WAKTU FAKTUR & DATA PELANGGAN (SEBARIS) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">

                                <!-- Kolom Kiri: Waktu Faktur Servis -->
                                <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-1.5">
                                    <label class="block font-bold text-gray-900 text-xs">
                                        Waktu Faktur Servis <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="datetime-local"
                                           wire:model="editTanggalMasuk"
                                           class="w-full px-3 py-2 rounded-xl border border-blue-200 bg-white font-bold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                    @error('editTanggalMasuk') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <!-- Kolom Kanan: Data Pelanggan -->
                                <div class="bg-gray-50/80 p-3.5 rounded-2xl border border-gray-200/80 space-y-2">
                                    <label class="block font-bold text-gray-900 text-xs">
                                        Pelanggan <span class="text-rose-500 font-bold">*</span>
                                    </label>

                                    <div class="space-y-2">
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="editCustomerSearch"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                            @if(!empty($customerSuggestions) && !$editCustomerId)
                                                <div class="absolute z-10 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                                    @foreach($customerSuggestions as $cs)
                                                        <button type="button"
                                                                wire:key="sugg-edit-cust-{{ $cs->id }}"
                                                                wire:click="selectEditCustomer({{ $cs->id }}, '{{ addslashes($cs->nama) }}')"
                                                                class="w-full px-3 py-2 text-left hover:bg-blue-50 flex items-center justify-between text-xs cursor-pointer">
                                                            <span class="font-bold text-gray-900">{{ $cs->nama }}</span>
                                                            <span class="font-mono text-gray-500 text-[11px]">{{ $cs->no_hp ?: '-' }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        @if($editCustomerId)
                                            <div class="p-1.5 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-[11px] text-blue-900 font-bold">
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    {{ $editCustomerSearch }} (Terdaftar)
                                                </span>
                                                <button type="button" wire:click="resetEditCustomer" class="text-rose-500 hover:underline cursor-pointer font-bold">Ganti</button>
                                            </div>
                                        @endif
                                        @error('editCustomerSearch') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                            </div>

                            <!-- 2. TIPE PERANGKAT -->
                            <div>
                                <label class="block font-bold text-gray-800 mb-1">Tipe Perangkat <span class="text-rose-500 font-bold">*</span></label>
                                <input type="text"
                                       wire:model="editTipeUnit"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                @error('editTipeUnit') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                            </div>

                            <!-- 3. RINCIAN SERVIS (JASA & SPAREPART PRODUK) -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between pb-1 border-b border-gray-200">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">
                                        Rincian Servis &amp; Komponen <span class="text-rose-500 font-bold">*</span>
                                    </span>
                                    <div class="inline-flex p-0.5 rounded-lg bg-gray-200 space-x-1">
                                        <button type="button"
                                                wire:click="setItemType('jasa')"
                                                class="px-2.5 py-1 rounded-md font-bold text-[10px] transition-all cursor-pointer {{ $itemType === 'jasa' ? 'bg-[#0052FF] text-white shadow-2xs' : 'text-gray-700 hover:text-gray-900' }}">
                                            Layanan Jasa
                                        </button>
                                        <button type="button"
                                                wire:click="setItemType('sparepart')"
                                                class="px-2.5 py-1 rounded-md font-bold text-[10px] transition-all cursor-pointer {{ $itemType === 'sparepart' ? 'bg-[#0052FF] text-white shadow-2xs' : 'text-gray-700 hover:text-gray-900' }}">
                                            Sparepart (Produk)
                                        </button>
                                    </div>
                                </div>

                                <!-- Subform Input Item Sesuai Screenshot Desain User -->
                                <div class="p-3.5 bg-gray-50/90 rounded-2xl border border-gray-200 space-y-3">

                                    <!-- Baris 1: Cari Layanan Jasa / Sparepart -->
                                    <div class="relative">
                                        <label class="block font-bold text-gray-700 mb-1">
                                            {{ $itemType === 'jasa' ? 'Cari Layanan Jasa (Master Servis)' : 'Cari Sparepart (Master Produk)' }}
                                        </label>
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="itemSearch"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:border-[#0052FF] focus:ring-2 focus:ring-blue-100">
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
                                                            wire:key="sugg-edit-ms-{{ $ms->id }}"
                                                            wire:click="selectMasterItem({{ $ms->id }}, '{{ $ms->nama_item }}', {{ $ms->harga_jual }}, {{ $ms->garansi_default_hari ?? 0 }})"
                                                            class="w-full px-3 py-2 text-left hover:bg-blue-50 flex items-center justify-between text-xs cursor-pointer">
                                                        <div>
                                                            <span class="font-bold text-gray-900 block">{{ $ms->nama_item }}</span>
                                                            <span class="text-[10px] text-gray-400 font-mono">Garansi {{ $ms->garansi_default_hari ?? 0 }} Hari</span>
                                                        </div>
                                                        <span class="font-mono font-bold text-blue-700">Rp {{ number_format($ms->harga_jual, 0, ',', '.') }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Dropdown Saran Sparepart Produk -->
                                        @if($itemType === 'sparepart' && !empty($produkSuggestions) && !$selectedProdukId)
                                            <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                                @foreach($produkSuggestions as $prod)
                                                    <button type="button"
                                                            wire:key="sugg-edit-prod-{{ $prod->id }}"
                                                            wire:click="selectProdukItem({{ $prod->id }}, '{{ $prod->nama_produk }}', {{ $prod->harga_jual }}, 30)"
                                                            class="w-full px-3 py-2 text-left hover:bg-blue-50 flex items-center justify-between text-xs cursor-pointer">
                                                        <div>
                                                            <span class="font-bold text-gray-900 block">{{ $prod->nama_produk }}</span>
                                                            <span class="text-[10px] text-gray-400 font-mono">{{ $prod->barcode ?: 'Tanpa Barcode' }}</span>
                                                        </div>
                                                        <span class="font-mono font-bold text-purple-700">Rp {{ number_format($prod->harga_jual, 0, ',', '.') }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Baris 2: Nama Item, Qty, Garansi (Hari) -->
                                    <div class="grid grid-cols-12 gap-3">
                                        <div class="col-span-12 sm:col-span-6">
                                            <label class="block font-bold text-gray-700 mb-1">Nama Item <span class="text-rose-500 font-bold">*</span></label>
                                            <input type="text"
                                                   wire:model="selectedMasterServisNama"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800">
                                            @error('selectedMasterServisNama') <span class="text-[10px] text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-6 sm:col-span-3">
                                            <label class="block font-bold text-gray-700 mb-1">Jumlah</label>
                                            <input type="number"
                                                   min="1"
                                                   wire:model.live="itemQty"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 text-center font-mono">
                                        </div>
                                        <div class="col-span-6 sm:col-span-3">
                                            <label class="block font-bold text-gray-700 mb-1">Garansi (Hari)</label>
                                            <input type="number"
                                                   min="0"
                                                   wire:model="itemGaransiHari"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 text-center font-mono">
                                        </div>
                                    </div>

                                    <!-- Baris 3: Harga Satuan & Tombol + Tambahkan ke Rincian Servis -->
                                    <div class="grid grid-cols-12 gap-3 items-end">
                                        <div class="col-span-12 sm:col-span-6">
                                            <label class="block font-bold text-gray-700 mb-1">Harga Satuan <span class="text-rose-500 font-bold">*</span></label>
                                            <x-input-rupiah wire:model="selectedMasterServisHarga" inputClass="!py-2 !text-xs !font-bold font-mono" />
                                            @error('selectedMasterServisHarga') <span class="text-[10px] text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <button type="button"
                                                    wire:click="addItemToEdit"
                                                    class="w-full py-2.5 px-4 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-white font-bold text-xs transition-all shadow-md shadow-blue-600/20 cursor-pointer inline-flex items-center justify-center space-x-1.5">
                                                <span>+ Tambahkan ke Rincian Servis</span>
                                            </button>
                                        </div>
                                    </div>

                                </div>

                                <!-- Tabel Daftar Item Edit -->
                                <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                                                <th class="py-2 px-3">Item</th>
                                                <th class="py-2 px-2 text-center">Tipe</th>
                                                <th class="py-2 px-2 text-center">Garansi</th>
                                                <th class="py-2 px-2 text-center">Jumlah</th>
                                                <th class="py-2 px-3 text-right">Harga</th>
                                                <th class="py-2 px-3 text-right">Subtotal</th>
                                                <th class="py-2 px-2 text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 text-xs">
                                            @forelse($editItems as $idx => $ei)
                                                <tr wire:key="edit-item-row-{{ $idx }}" class="hover:bg-gray-50/50">
                                                    <td class="py-2 px-3 font-bold text-gray-900">{{ $ei['nama_item'] }}</td>
                                                    <td class="py-2 px-2 text-center">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold {{ $ei['jenis'] === 'jasa' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                                            {{ $ei['jenis'] === 'jasa' ? 'Jasa' : 'Sparepart' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-2 text-center font-mono text-[10px] text-gray-600">{{ $ei['garansi_hari'] }} Hr</td>
                                                    <td class="py-2 px-2 text-center font-mono font-bold">{{ $ei['qty'] }}</td>
                                                    <td class="py-2 px-3 text-right font-mono text-gray-600">Rp {{ number_format($ei['harga'], 0, ',', '.') }}</td>
                                                    <td class="py-2 px-3 text-right font-mono font-black text-gray-900">Rp {{ number_format($ei['subtotal'], 0, ',', '.') }}</td>
                                                    <td class="py-2 px-2 text-center">
                                                        <button type="button" wire:click="removeItemFromEdit({{ $idx }})" class="p-1 rounded text-rose-500 hover:bg-rose-50 cursor-pointer" title="Hapus Item">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="py-4 text-center text-gray-400 italic text-[11px]">
                                                        Belum ada tindakan / sparepart yang ditambahkan.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(!empty($editItems))
                                            <tfoot>
                                                <tr class="bg-blue-50/70 border-t border-blue-100 font-bold">
                                                    <td colspan="5" class="py-2.5 px-3 text-right font-black uppercase text-gray-700">Total Biaya Servis:</td>
                                                    <td class="py-2.5 px-3 font-mono font-black text-sm text-[#0052FF] text-right">
                                                        Rp {{ number_format(array_sum(array_column($editItems, 'subtotal')), 0, ',', '.') }}
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                                @error('editItems') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeEditModal"
                                    class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center space-x-2 px-6 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-xs font-bold text-white transition-all shadow-md shadow-blue-600/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading wire:target="simpanEditServis" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
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
    <!-- MODAL 3: DETAIL UNIT                           -->
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
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-bold text-white font-mono tracking-tight leading-snug">
                                        {{ $selectedServis->no_servis_frontend }}
                                    </h3>
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-amber-400 text-amber-950">
                                        NON-REAL
                                    </span>
                                </div>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">
                                    Faktur: {{ $selectedServis->tanggal_masuk ? $selectedServis->tanggal_masuk->translatedFormat('d F Y - H:i') . ' WIB' : '-' }}
                                </p>
                            </div>
                        </div>

                        <button wire:click="closeDetailModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
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
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Perangkat</span>
                                <span class="font-black text-gray-900 block mt-1">{{ $selectedServis->tipe_unit }}</span>
                                <span class="text-[10px] text-purple-600 font-bold block">{{ $selectedServis->cabang?->nama_cabang }}</span>
                            </div>
                        </div>

                        <!-- Rincian Item Tindakan -->
                        <div class="space-y-2">
                            <span class="text-xs font-black text-gray-900 uppercase tracking-wider block">
                                Rincian Biaya Servis
                            </span>
                            <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                                            <th class="py-2.5 px-3">Item Tindakan / Sparepart</th>
                                            <th class="py-2.5 px-2 text-center">Tipe</th>
                                            <th class="py-2.5 px-2 text-center">Garansi</th>
                                            <th class="py-2.5 px-2 text-center">Jumlah</th>
                                            <th class="py-2.5 px-3 text-right">Harga</th>
                                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($selectedServis->details as $det)
                                            <tr wire:key="detail-row-{{ $det->id }}" class="hover:bg-gray-50/50">
                                                <td class="py-2 px-3 font-bold text-gray-900">{{ $det->nama_item }}</td>
                                                <td class="py-2 px-2 text-center">
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold {{ $det->jenis === 'jasa' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                                        {{ $det->jenis === 'jasa' ? 'Jasa' : 'Sparepart' }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-2 text-center font-mono text-[10px] text-gray-600">{{ $det->garansi_hari }} Hr</td>
                                                <td class="py-2 px-2 text-center font-mono font-bold">{{ $det->qty }}</td>
                                                <td class="py-2 px-3 text-right font-mono text-gray-600">Rp {{ number_format($det->harga, 0, ',', '.') }}</td>
                                                <td class="py-2 px-3 text-right font-mono font-black text-gray-900">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-3 text-center text-gray-400 italic text-[11px]">
                                                    Tidak ada rincian item.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-purple-50/70 border-t border-purple-100 font-bold">
                                            <td colspan="5" class="py-2.5 px-3 text-right font-black uppercase text-gray-700">Total Biaya:</td>
                                            <td class="py-2.5 px-3 font-mono font-black text-sm text-[#5E17EB] text-right">
                                                Rp {{ number_format($selectedServis->total_biaya, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Modal Detail -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                        <a href="{{ route('servis.invoice', $selectedServis->id) }}"
                           class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition-all cursor-pointer shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Faktur</span>
                        </a>

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
    <!-- MODAL 4: HAPUS SERVIS                          -->
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
                        <p class="text-xs text-gray-500">Anda yakin ingin menghapus data servis non real <span class="font-mono font-bold text-gray-800">{{ $deleteServisKode }}</span>? Data yang dihapus tidak dapat dipulihkan.</p>
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
    <!-- MODAL 5: SUKSES & CETAK FAKTUR SERVIS          -->
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
                        <h3 class="text-lg font-black text-gray-900">Servis Non Real Berhasil Disimpan!</h3>
                        <p class="text-xs text-gray-500">Nomor faktur servis resmi berhasil dicatat ke dalam sistem.</p>
                    </div>

                    <div class="p-4 bg-purple-50 rounded-2xl border border-purple-100 text-center space-y-1">
                        <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider block">KODE FAKTUR SERVIS</span>
                        <span class="text-xl font-black text-[#5E17EB] font-mono tracking-wider block">
                            {{ $lastCreatedServis->no_servis_frontend }}
                        </span>
                        <span class="text-[11px] font-bold text-gray-700 block">
                            {{ $lastCreatedServis->tipe_unit }} &bull; {{ $lastCreatedServis->customer?->nama }}
                        </span>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('servis.invoice', $lastCreatedServis->id) }}"
                           class="flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Faktur</span>
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

</div>

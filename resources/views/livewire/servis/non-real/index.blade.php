<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6" x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'M' }" @table-size-changed.window="tableSize = $event.detail">

    <!-- Header & Page Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-lg text-xs font-black tracking-wider uppercase bg-purple-100 text-[#5E17EB] border border-purple-200">
                    Non-Real &amp; Faktur Cepat
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight mt-1">Servis Non Real</h1>
            <p class="text-xs sm:text-sm text-gray-500 font-medium">Pengelolaan transaksi servis cepat untuk keperluan administrasi dan pencetakan faktur tanpa memotong stok fisik.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] text-white text-xs sm:text-sm font-bold shadow-md hover:bg-purple-700 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Servis Non Real</span>
            </button>
        </div>
    </div>

    <!-- Metric Cards Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center font-black shrink-0 border border-purple-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider truncate">Total Servis Non Real</p>
                <p class="text-lg sm:text-xl font-black text-gray-900">{{ number_format($totalServisNonReal) }} <span class="text-xs font-semibold text-gray-400">Unit</span></p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0052FF] flex items-center justify-center font-black shrink-0 border border-blue-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider truncate">Total Pendapatan</p>
                <p class="text-lg sm:text-xl font-black text-[#0052FF]">Rp {{ number_format($totalPendapatanNonReal, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black shrink-0 border border-emerald-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider truncate">Item Jasa</p>
                <p class="text-lg sm:text-xl font-black text-emerald-700">{{ number_format($totalItemJasa) }} <span class="text-xs font-semibold text-gray-400">Item</span></p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#E6007A] flex items-center justify-center font-black shrink-0 border border-pink-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider truncate">Item Sparepart</p>
                <p class="text-lg sm:text-xl font-black text-[#E6007A]">{{ number_format($totalItemSparepart) }} <span class="text-xs font-semibold text-gray-400">Item</span></p>
            </div>
        </div>
    </div>

    <!-- Toolbar Filter & Search -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-gray-100 shadow-xs space-y-3">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <!-- Search Multi-word -->
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full text-xs font-semibold pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] focus:border-transparent transition-all">
            </div>

            <!-- Filter Cabang & Table Size -->
            <div class="flex items-center flex-wrap gap-2">
                @if(Auth::user()->isOwner() && $cabangs->count() > 0)
                    <div class="flex items-center space-x-1.5 bg-gray-50 px-2.5 py-1.5 rounded-xl border border-gray-200">
                        <label class="text-[11px] font-bold text-gray-600">Cabang:</label>
                        <select wire:model.live="cabangFilter" class="text-xs font-semibold bg-transparent border-0 p-0 pr-6 focus:ring-0 text-gray-800 cursor-pointer">
                            <option value="semua">Semua Cabang</option>
                            @foreach($cabangs as $cb)
                                <option value="{{ $cb->id }}">{{ $cb->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <x-table-size-selector />
            </div>
        </div>
    </div>

    <!-- Tabel Data Servis Non Real (Format & Desain Selaras Servis Masuk) -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse table-fixed min-w-[1000px]" :class="{
                'text-[11px]': tableSize === 'S',
                'text-[12px]': tableSize === 'M',
                'text-[13px]': tableSize === 'L'
            }">
                <colgroup>
                    <col style="width: 140px;">
                    <col style="width: 120px;">
                    <col style="width: 150px;">
                    <col style="width: 220px;">
                    <col style="width: 140px;">
                    @if(Auth::user()->isOwner())
                        <col style="width: 110px;">
                    @endif
                    <col style="width: 150px;">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/80 text-[10px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <x-th-sort field="no_servis" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">No. Tiket</x-th-sort>
                        <x-th-sort field="tanggal_masuk" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Waktu Faktur</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Pelanggan</th>
                        <x-th-sort field="tipe_unit" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Perangkat</x-th-sort>
                        <x-th-sort field="total_biaya" :sortField="$sortField" :sortDirection="$sortDirection" align="right" class="py-2.5 px-3 text-right">Total Biaya</x-th-sort>
                        @if(Auth::user()->isOwner())
                            <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Cabang</th>
                        @endif
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query/Filter/Search/Sort/Pagination -->
                <tbody wire:key="servis-skeleton-body"
                       wire:loading.delay
                       wire:target="search, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 bg-white">
                    @for($i = 0; $i < 6; $i++)
                        <tr class="animate-pulse">
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-20"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-28"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-36"></div></td>
                            <td class="py-2.5 px-3 text-right"><div class="h-3.5 bg-gray-200 rounded w-20 ml-auto"></div></td>
                            @if(Auth::user()->isOwner())
                                <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-16"></div></td>
                            @endif
                            <td class="py-2.5 px-3 text-center"><div class="h-6 bg-gray-100 rounded w-28 mx-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <tbody wire:key="servis-data-body"
                       wire:loading.remove
                       wire:target="search, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $item)
                        <tr wire:key="servis-non-real-{{ $item->id }}" class="hover:bg-purple-50/30 transition-colors group">
                            <!-- No. Tiket / Faktur & Pembuat -->
                            <td class="py-2.5 px-3 whitespace-nowrap border-l-4 border-[#5E17EB]">
                                <a href="{{ route('servis.invoice', $item->id) }}" class="font-mono font-black text-gray-900 block group-hover:text-[#5E17EB] transition-colors leading-tight truncate">
                                    {{ $item->no_servis_frontend }}
                                </a>
                                <span class="text-[10px] font-medium text-gray-600 block leading-tight mt-0.5 truncate">
                                    {{ $item->pembuat?->name ?? 'Staf' }}
                                </span>
                            </td>

                            <!-- Waktu Faktur -->
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

                            <!-- Perangkat (Murni Tipe Unit Tanpa Badge Laptop) -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <span class="font-bold text-gray-900 block truncate leading-tight" title="{{ $item->tipe_unit }}">
                                    {{ $item->tipe_unit }}
                                </span>
                                <p class="text-[10px] text-gray-600 font-medium truncate mt-0.5 leading-tight">
                                    Total: <span class="text-gray-700 font-semibold">{{ $item->details->count() }} Item Rincian</span>
                                </p>
                            </td>

                            <!-- Total Biaya -->
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <span class="font-black text-gray-900 block leading-tight">
                                    Rp {{ number_format((float) $item->total_biaya, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-emerald-700 font-bold block leading-tight mt-0.5">
                                    Lunas (Cash)
                                </span>
                            </td>

                            <!-- Cabang (Owner HQ) -->
                            @if(Auth::user()->isOwner())
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-700 block truncate max-w-[100px]">
                                        {{ $item->cabang?->nama_cabang ?? 'Pusat' }}
                                    </span>
                                </td>
                            @endif

                            <!-- Aksi (4 Tombol Lengkap) -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">
                                    <!-- 1. Cetak Faktur -->
                                    <button type="button"
                                            onclick="window.printDocument('{{ route('servis.invoice', $item->id) }}')"
                                            title="Cetak Faktur Servis"
                                            class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </button>

                                    <!-- 2. Lihat Detail -->
                                    <button type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            title="Lihat Rincian Detail"
                                            class="p-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-700 hover:text-white transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- 3. Update Servis Non Real -->
                                    <button type="button"
                                            wire:click="openEditModal({{ $item->id }})"
                                            title="Edit / Update Servis Non Real"
                                            class="p-1.5 rounded-lg bg-blue-50 text-[#0052FF] hover:bg-[#0052FF] hover:text-white transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- 4. Hapus Servis -->
                                    <button type="button"
                                            wire:click="confirmDelete({{ $item->id }})"
                                            title="Hapus Servis Non Real"
                                            class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->isOwner() ? 7 : 6 }}" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-sm font-bold text-gray-500">Belum ada transaksi servis non real.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($servisList->hasPages())
            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500 font-semibold">
                    Menampilkan {{ $servisList->firstItem() ?? 0 }} s/d {{ $servisList->lastItem() ?? 0 }} dari {{ $servisList->total() }} transaksi
                </p>
                @if($servisList->hasMorePages())
                    <button type="button"
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-purple-50 text-[#5E17EB] text-xs font-bold rounded-xl hover:bg-purple-100 transition-colors cursor-pointer flex items-center space-x-2">
                        <span wire:loading.remove wire:target="loadMore">Muat Lebih Banyak</span>
                        <span wire:loading wire:target="loadMore">Memuat...</span>
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL 1: TAMBAH SERVIS NON REAL BARU                                    -->
    <!-- ======================================================================= -->
    @if($modalCreateOpen)
        <div wire:key="modal-create-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
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
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Tambah Servis Non Real</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Input transaksi servis cepat, data pelanggan, dan rincian biaya faktur instan</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCreateModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">
                    @if($errors->has('general'))
                        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl font-bold">
                            {{ $errors->first('general') }}
                        </div>
                    @endif

                    <!-- 1. Waktu Faktur & Data Pelanggan (Sebaris Grid) -->
                    <div class="p-3.5 bg-gray-50/75 rounded-xl border border-gray-200 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">
                            <!-- Waktu Faktur Servis -->
                            <div>
                                <label class="block font-bold text-gray-900 mb-1">
                                    Waktu Faktur Servis <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="datetime-local"
                                       wire:model="customTanggalMasuk"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                <p class="text-[10px] text-gray-500 mt-1 font-medium">Waktu selesai diselaraskan otomatis dengan waktu faktur.</p>
                                @error('customTanggalMasuk') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                            </div>

                            <!-- Data Pelanggan -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="font-bold text-gray-900">
                                        Pelanggan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <div class="inline-flex rounded-lg bg-gray-200 p-0.5">
                                        <button type="button"
                                                wire:click="setCustomerType('langganan')"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold transition-all cursor-pointer {{ $customerType === 'langganan' ? 'bg-white text-[#5E17EB] shadow-xs' : 'text-gray-600' }}">
                                            Pilih Terdaftar
                                        </button>
                                        <button type="button"
                                                wire:click="setCustomerType('baru')"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold transition-all cursor-pointer {{ $customerType === 'baru' ? 'bg-white text-[#5E17EB] shadow-xs' : 'text-gray-600' }}">
                                            + Pelanggan Baru
                                        </button>
                                    </div>
                                </div>

                                @if($customerType === 'langganan')
                                    <div class="space-y-1.5">
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="customerSearch"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @if(!empty($customerSuggestions) && !$selectedCustomerId)
                                                <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
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
                                                <span>{{ $customerSearch }} (Terdaftar)</span>
                                                <button type="button" wire:click="resetCustomer" class="text-rose-500 hover:underline cursor-pointer font-bold">Ganti</button>
                                            </div>
                                        @endif
                                        @error('selectedCustomerId') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block font-bold text-gray-700 mb-1 text-[11px]">Nama <span class="text-rose-500 font-bold">*</span></label>
                                            <input type="text"
                                                   wire:model="newCustomerNama"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @error('newCustomerNama') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block font-bold text-gray-700 mb-1 text-[11px]">Nomor WhatsApp <span class="text-rose-500 font-bold">*</span></label>
                                            <input type="text"
                                                   wire:model="newCustomerNoHp"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @error('newCustomerNoHp') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 2. Tipe Perangkat -->
                    <div>
                        <label class="block font-bold text-gray-800 mb-1">Perangkat <span class="text-rose-500 font-bold">*</span></label>
                        <input type="text"
                               wire:model="tipeUnit"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                        @error('tipeUnit') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <!-- 3. Rincian Servis (Jasa & Sparepart) -->
                    <div class="p-3.5 bg-gray-50/75 rounded-xl border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-gray-900 uppercase tracking-wider text-[11px]">Rincian Servis</span>
                            <div class="inline-flex rounded-lg bg-gray-200 p-0.5">
                                <button type="button"
                                        wire:click="setItemType('jasa')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer {{ $itemType === 'jasa' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-700' }}">
                                    Layanan Jasa
                                </button>
                                <button type="button"
                                        wire:click="setItemType('sparepart')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer {{ $itemType === 'sparepart' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-700' }}">
                                    Sparepart Produk
                                </button>
                            </div>
                        </div>

                        <!-- Sub-Form Tambah Item -->
                        <div class="bg-white p-3 rounded-xl border border-gray-200 space-y-2.5">
                            <div class="relative">
                                <label class="block font-bold text-gray-700 mb-0.5 text-[11px]">Cari {{ $itemType === 'jasa' ? 'Master Jasa Servis' : 'Katalog Sparepart' }}</label>
                                <input type="text"
                                       wire:model.live.debounce.250ms="itemSearch"
                                       class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-semibold focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">

                                <!-- Dropdown Suggestion -->
                                @if($itemType === 'jasa' && !empty($masterServisSuggestions))
                                    <div class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-200 divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                        @foreach($masterServisSuggestions as $ms)
                                            <button type="button"
                                                    wire:click="selectMasterItem({{ $ms->id }}, '{{ addslashes($ms->nama_item) }}', {{ $ms->harga_jual }}, {{ $ms->garansi_hari }})"
                                                    class="w-full p-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                <div>
                                                    <span class="font-bold text-gray-900 block">{{ $ms->nama_item }}</span>
                                                    <span class="text-[10px] text-gray-500 font-mono">Garansi: {{ $ms->garansi_hari }} Hari</span>
                                                </div>
                                                <span class="font-black text-[#5E17EB]">Rp {{ number_format((float) $ms->harga_jual, 0, ',', '.') }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($itemType === 'sparepart' && !empty($produkSuggestions))
                                    <div class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-200 divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                        @foreach($produkSuggestions as $p)
                                            <button type="button"
                                                    wire:click="selectProdukItem({{ $p->id }}, '{{ addslashes($p->nama_produk) }}', {{ $p->harga_jual }}, 30)"
                                                    class="w-full p-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                <div>
                                                    <span class="font-bold text-gray-900 block">{{ $p->nama_produk }}</span>
                                                    <span class="text-[10px] text-gray-500 font-mono">Stok: {{ $p->stoks->sum('jumlah') }}</span>
                                                </div>
                                                <span class="font-black text-[#5E17EB]">Rp {{ number_format((float) $p->harga_jual, 0, ',', '.') }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if($selectedMasterServisNama)
                                <div class="p-2 bg-purple-50 rounded-lg border border-purple-200 flex items-center justify-between">
                                    <span class="font-bold text-purple-900 text-xs truncate">Terpilih: {{ $selectedMasterServisNama }}</span>
                                    <button type="button" wire:click="resetItemForm" class="text-rose-500 hover:underline text-[11px] font-bold cursor-pointer">Batal</button>
                                </div>
                            @endif

                            <div class="grid grid-cols-3 gap-2 items-end">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1 text-[10px]">Harga Satuan</label>
                                    <x-input-rupiah wire:model="selectedMasterServisHarga" inputClass="!py-1.5 !pl-8 !text-xs !rounded-lg" />
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1 text-[10px]">Jumlah</label>
                                    <input type="number" min="1" wire:model="itemQty" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs font-bold">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1 text-[10px]">Garansi (Hari)</label>
                                    <input type="number" min="0" wire:model="itemGaransiHari" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs font-bold">
                                </div>
                            </div>

                            <button type="button"
                                    wire:click="addItemToCreate"
                                    class="w-full py-2 bg-purple-100 hover:bg-[#5E17EB] text-[#5E17EB] hover:text-white font-bold rounded-lg transition-colors cursor-pointer text-xs flex items-center justify-center space-x-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                <span>Tambahkan ke Rincian</span>
                            </button>
                            @error('itemSearch') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Daftar Rincian Item Ditambahkan -->
                        @if(count($createItems) > 0)
                            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black uppercase text-gray-600">
                                            <th class="p-2">Item</th>
                                            <th class="p-2">Tipe</th>
                                            <th class="p-2 text-center">Jumlah</th>
                                            <th class="p-2 text-right">Harga</th>
                                            <th class="p-2 text-right">Subtotal</th>
                                            <th class="p-2 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($createItems as $idx => $ci)
                                            <tr class="hover:bg-gray-50/50">
                                                <td class="p-2 font-bold text-gray-900">{{ $ci['nama_item'] }}</td>
                                                <td class="p-2">
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $ci['tipe'] === 'jasa' ? 'bg-emerald-50 text-emerald-700' : 'bg-pink-50 text-[#E6007A]' }}">
                                                        {{ ucfirst($ci['tipe']) }}
                                                    </span>
                                                </td>
                                                <td class="p-2 text-center font-bold">{{ $ci['qty'] }}</td>
                                                <td class="p-2 text-right font-mono">Rp {{ number_format((float) $ci['harga_satuan'], 0, ',', '.') }}</td>
                                                <td class="p-2 text-right font-black text-[#5E17EB]">Rp {{ number_format((float) $ci['subtotal'], 0, ',', '.') }}</td>
                                                <td class="p-2 text-center">
                                                    <button type="button" wire:click="removeItemFromCreate({{ $idx }})" class="text-rose-500 hover:text-rose-700 font-bold p-1 cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-purple-50/60 font-black text-xs text-gray-900">
                                            <td colspan="4" class="p-2 text-right">TOTAL BIAYA:</td>
                                            <td class="p-2 text-right text-[#5E17EB] text-sm">Rp {{ number_format((float) collect($createItems)->sum('subtotal'), 0, ',', '.') }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="p-3 bg-white rounded-xl border border-dashed border-gray-300 text-center text-gray-400 text-xs font-semibold">
                                Belum ada item jasa atau sparepart yang ditambahkan ke rincian.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500">Metode: <strong class="text-gray-800">Cash</strong></span>
                    <div class="flex items-center space-x-2">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 rounded-xl text-gray-700 bg-gray-200 hover:bg-gray-300 text-xs font-bold transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="simpanServisMasuk"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold shadow-md transition-all cursor-pointer flex items-center space-x-2 disabled:opacity-50">
                            <svg wire:loading wire:target="simpanServisMasuk" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="simpanServisMasuk">Simpan Faktur Servis</span>
                            <span wire:loading wire:target="simpanServisMasuk">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL 2: UPDATE / EDIT SERVIS NON REAL                                  -->
    <!-- ======================================================================= -->
    @if($modalEditOpen)
        <div wire:key="modal-edit-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
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
                                <p class="text-xs text-blue-100 font-medium mt-0.5">Perbarui rincian jasa, sparepart, atau informasi transaksi non real</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body Edit -->
                    <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">
                    @if($errors->has('general'))
                        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl font-bold">
                            {{ $errors->first('general') }}
                        </div>
                    @endif

                    <!-- 1. Waktu Faktur & Data Pelanggan (Sebaris Grid) -->
                    <div class="p-3.5 bg-gray-50/75 rounded-xl border border-gray-200 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">
                            <!-- Waktu Faktur Servis -->
                            <div>
                                <label class="block font-bold text-gray-900 mb-1">
                                    Waktu Faktur Servis <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="datetime-local"
                                       wire:model="editTanggalMasuk"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                <p class="text-[10px] text-gray-500 mt-1 font-medium">Waktu selesai diselaraskan otomatis dengan waktu faktur.</p>
                                @error('editTanggalMasuk') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                            </div>

                            <!-- Data Pelanggan -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="font-bold text-gray-900">
                                        Pelanggan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <div class="inline-flex rounded-lg bg-gray-200 p-0.5">
                                        <button type="button"
                                                wire:click="setEditCustomerType('langganan')"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold transition-all cursor-pointer {{ $editCustomerType === 'langganan' ? 'bg-white text-[#0052FF] shadow-xs' : 'text-gray-600' }}">
                                            Pilih Terdaftar
                                        </button>
                                        <button type="button"
                                                wire:click="setEditCustomerType('baru')"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold transition-all cursor-pointer {{ $editCustomerType === 'baru' ? 'bg-white text-[#0052FF] shadow-xs' : 'text-gray-600' }}">
                                            + Pelanggan Baru
                                        </button>
                                    </div>
                                </div>

                                @if($editCustomerType === 'langganan')
                                    <div class="space-y-1.5">
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="editCustomerSearch"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                            @if(!empty($customerSuggestions) && !$editCustomerId)
                                                <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
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
                                                <span>{{ $editCustomerSearch }} (Terdaftar)</span>
                                                <button type="button" wire:click="resetEditCustomer" class="text-rose-500 hover:underline cursor-pointer font-bold">Ganti</button>
                                            </div>
                                        @endif
                                        @error('editCustomerId') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block font-bold text-gray-700 mb-1 text-[11px]">Nama <span class="text-rose-500 font-bold">*</span></label>
                                            <input type="text"
                                                   wire:model="editNewCustomerNama"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                            @error('editNewCustomerNama') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block font-bold text-gray-700 mb-1 text-[11px]">Nomor WhatsApp <span class="text-rose-500 font-bold">*</span></label>
                                            <input type="text"
                                                   wire:model="editNewCustomerNoHp"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                            @error('editNewCustomerNoHp') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 2. Tipe Perangkat -->
                    <div>
                        <label class="block font-bold text-gray-800 mb-1">Tipe Perangkat <span class="text-rose-500 font-bold">*</span></label>
                        <input type="text"
                               wire:model="editTipeUnit"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                        @error('editTipeUnit') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <!-- 3. Rincian Servis Edit (Jasa & Sparepart) -->
                    <div class="p-3.5 bg-gray-50/75 rounded-xl border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-gray-900 uppercase tracking-wider text-[11px]">Rincian Servis</span>
                            <div class="inline-flex rounded-lg bg-gray-200 p-0.5">
                                <button type="button"
                                        wire:click="setItemType('jasa')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer {{ $itemType === 'jasa' ? 'bg-[#0052FF] text-white shadow-xs' : 'text-gray-700' }}">
                                    Layanan Jasa
                                </button>
                                <button type="button"
                                        wire:click="setItemType('sparepart')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer {{ $itemType === 'sparepart' ? 'bg-[#0052FF] text-white shadow-xs' : 'text-gray-700' }}">
                                    Sparepart Produk
                                </button>
                            </div>
                        </div>

                        <!-- Sub-Form Tambah Item Edit -->
                        <div class="bg-white p-3 rounded-xl border border-gray-200 space-y-2.5">
                            <div class="relative">
                                <label class="block font-bold text-gray-700 mb-0.5 text-[11px]">Cari {{ $itemType === 'jasa' ? 'Master Jasa Servis' : 'Katalog Sparepart' }}</label>
                                <input type="text"
                                       wire:model.live.debounce.250ms="itemSearch"
                                       class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-semibold focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">

                                <!-- Dropdown Suggestion Edit -->
                                @if($itemType === 'jasa' && !empty($masterServisSuggestions))
                                    <div class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-200 divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                        @foreach($masterServisSuggestions as $ms)
                                            <button type="button"
                                                    wire:click="selectMasterItem({{ $ms->id }}, '{{ addslashes($ms->nama_item) }}', {{ $ms->harga_jual }}, {{ $ms->garansi_hari }})"
                                                    class="w-full p-2 text-left hover:bg-blue-50 flex items-center justify-between text-xs cursor-pointer">
                                                <div>
                                                    <span class="font-bold text-gray-900 block">{{ $ms->nama_item }}</span>
                                                    <span class="text-[10px] text-gray-500 font-mono">Garansi: {{ $ms->garansi_hari }} Hari</span>
                                                </div>
                                                <span class="font-black text-[#0052FF]">Rp {{ number_format((float) $ms->harga_jual, 0, ',', '.') }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($itemType === 'sparepart' && !empty($produkSuggestions))
                                    <div class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-200 divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                        @foreach($produkSuggestions as $p)
                                            <button type="button"
                                                    wire:click="selectProdukItem({{ $p->id }}, '{{ addslashes($p->nama_produk) }}', {{ $p->harga_jual }}, 30)"
                                                    class="w-full p-2 text-left hover:bg-blue-50 flex items-center justify-between text-xs cursor-pointer">
                                                <div>
                                                    <span class="font-bold text-gray-900 block">{{ $p->nama_produk }}</span>
                                                    <span class="text-[10px] text-gray-500 font-mono">Stok: {{ $p->stoks->sum('jumlah') }}</span>
                                                </div>
                                                <span class="font-black text-[#0052FF]">Rp {{ number_format((float) $p->harga_jual, 0, ',', '.') }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if($selectedMasterServisNama)
                                <div class="p-2 bg-blue-50 rounded-lg border border-blue-200 flex items-center justify-between">
                                    <span class="font-bold text-blue-900 text-xs truncate">Terpilih: {{ $selectedMasterServisNama }}</span>
                                    <button type="button" wire:click="resetItemForm" class="text-rose-500 hover:underline text-[11px] font-bold cursor-pointer">Batal</button>
                                </div>
                            @endif

                            <div class="grid grid-cols-3 gap-2 items-end">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1 text-[10px]">Harga Satuan</label>
                                    <x-input-rupiah wire:model="selectedMasterServisHarga" inputClass="!py-1.5 !pl-8 !text-xs !rounded-lg" />
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1 text-[10px]">Jumlah</label>
                                    <input type="number" min="1" wire:model="itemQty" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs font-bold">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1 text-[10px]">Garansi (Hari)</label>
                                    <input type="number" min="0" wire:model="itemGaransiHari" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs font-bold">
                                </div>
                            </div>

                            <button type="button"
                                    wire:click="addItemToEdit"
                                    class="w-full py-2 bg-blue-100 hover:bg-[#0052FF] text-[#0052FF] hover:text-white font-bold rounded-lg transition-colors cursor-pointer text-xs flex items-center justify-center space-x-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                <span>Tambahkan ke Rincian</span>
                            </button>
                            @error('itemSearch') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Daftar Rincian Item Edit -->
                        @if(count($editItems) > 0)
                            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black uppercase text-gray-600">
                                            <th class="p-2">Item</th>
                                            <th class="p-2">Tipe</th>
                                            <th class="p-2 text-center">Jumlah</th>
                                            <th class="p-2 text-right">Harga</th>
                                            <th class="p-2 text-right">Subtotal</th>
                                            <th class="p-2 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($editItems as $idx => $ei)
                                            <tr class="hover:bg-gray-50/50">
                                                <td class="p-2 font-bold text-gray-900">{{ $ei['nama_item'] }}</td>
                                                <td class="p-2">
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $ei['tipe'] === 'jasa' ? 'bg-emerald-50 text-emerald-700' : 'bg-pink-50 text-[#E6007A]' }}">
                                                        {{ ucfirst($ei['tipe']) }}
                                                    </span>
                                                </td>
                                                <td class="p-2 text-center font-bold">{{ $ei['qty'] }}</td>
                                                <td class="p-2 text-right font-mono">Rp {{ number_format((float) $ei['harga_satuan'], 0, ',', '.') }}</td>
                                                <td class="p-2 text-right font-black text-[#0052FF]">Rp {{ number_format((float) $ei['subtotal'], 0, ',', '.') }}</td>
                                                <td class="p-2 text-center">
                                                    <button type="button" wire:click="removeItemFromEdit({{ $idx }})" class="text-rose-500 hover:text-rose-700 font-bold p-1 cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-blue-50/60 font-black text-xs text-gray-900">
                                            <td colspan="4" class="p-2 text-right">TOTAL BIAYA:</td>
                                            <td class="p-2 text-right text-[#0052FF] text-sm">Rp {{ number_format((float) collect($editItems)->sum('subtotal'), 0, ',', '.') }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="p-3 bg-white rounded-xl border border-dashed border-gray-300 text-center text-gray-400 text-xs font-semibold">
                                Belum ada item jasa atau sparepart yang ditambahkan ke rincian.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Modal Edit -->
                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500">Metode: <strong class="text-gray-800">Cash</strong></span>
                    <div class="flex items-center space-x-2">
                        <button type="button" wire:click="closeEditModal" class="px-4 py-2 rounded-xl text-gray-700 bg-gray-200 hover:bg-gray-300 text-xs font-bold transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="simpanEditServis"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-white text-xs font-bold shadow-md transition-all cursor-pointer flex items-center space-x-2 disabled:opacity-50">
                            <svg wire:loading wire:target="simpanEditServis" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="simpanEditServis">Simpan Perubahan</span>
                            <span wire:loading wire:target="simpanEditServis">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL 3: LIHAT DETAIL SERVIS NON REAL                                   -->
    <!-- ======================================================================= -->
    @if($modalDetailOpen && $selectedServis)
        <div wire:key="modal-detail-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100">
                    <!-- Header Detail -->
                    <div class="bg-gray-900 px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-bold text-white tracking-tight leading-snug">{{ $selectedServis->no_servis_frontend }}</h3>
                                    <span class="px-2 py-0.5 rounded-md bg-purple-500/30 text-purple-300 text-[10px] font-black uppercase tracking-wider border border-purple-400/30">Non-Real</span>
                                </div>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">Rincian faktur servis cepat dan breakdown item</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">
                        <!-- Info Baris -->
                        <div class="grid grid-cols-2 gap-3 p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                            <div>
                                <span class="text-[10px] font-bold text-gray-500 uppercase block">Pelanggan</span>
                                <span class="font-bold text-gray-900 text-sm block">{{ $selectedServis->customer?->nama ?? '-' }}</span>
                                <span class="text-[10px] text-gray-500 font-mono block">{{ $selectedServis->customer?->no_hp ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-500 uppercase block">Waktu Faktur</span>
                                <span class="font-bold text-gray-900 block">{{ $selectedServis->tanggal_masuk ? $selectedServis->tanggal_masuk->format('d M Y, H:i') : '-' }} WIB</span>
                                <span class="text-[10px] text-gray-500 block">Petugas: {{ $selectedServis->pembuat?->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-500 uppercase block">Unit Perangkat</span>
                                <span class="font-bold text-gray-900 block">{{ $selectedServis->tipe_unit }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-500 uppercase block">Masa Garansi</span>
                                <span class="font-bold text-emerald-700 block">{{ $selectedServis->garansi_hari }} Hari (s/d {{ $selectedServis->garansi_sampai ? \Carbon\Carbon::parse($selectedServis->garansi_sampai)->format('d M Y') : '-' }})</span>
                            </div>
                        </div>

                        <!-- Rincian Item Detail -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black uppercase text-gray-600">
                                        <th class="p-2.5">Item</th>
                                        <th class="p-2.5">Tipe</th>
                                        <th class="p-2.5 text-center">Jumlah</th>
                                        <th class="p-2.5 text-right">Harga</th>
                                        <th class="p-2.5 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($selectedServis->details as $d)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="p-2.5 font-bold text-gray-900">{{ $d->nama_item }}</td>
                                            <td class="p-2.5">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ ($d->jenis ?? ($d->master_servis_id ? 'jasa' : 'sparepart')) === 'jasa' ? 'bg-emerald-50 text-emerald-700' : 'bg-pink-50 text-[#E6007A]' }}">
                                                    {{ ucfirst($d->jenis ?? ($d->master_servis_id ? 'jasa' : 'sparepart')) }}
                                                </span>
                                            </td>
                                            <td class="p-2.5 text-center font-bold">{{ $d->qty }}</td>
                                            <td class="p-2.5 text-right font-mono">Rp {{ number_format((float) ($d->harga ?? ($d->qty > 0 ? $d->subtotal / $d->qty : $d->subtotal)), 0, ',', '.') }}</td>
                                            <td class="p-2.5 text-right font-black text-[#5E17EB]">Rp {{ number_format((float) $d->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-4 text-center text-gray-400 font-semibold">Tidak ada rincian item.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-purple-50/70 font-black text-xs text-gray-900">
                                        <td colspan="4" class="p-2.5 text-right">TOTAL FAKTUR:</td>
                                        <td class="p-2.5 text-right text-[#5E17EB] text-sm">Rp {{ number_format((float) $selectedServis->total_biaya, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Detail -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <button type="button"
                                onclick="window.printDocument('{{ route('servis.invoice', $selectedServis->id) }}')"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Faktur</span>
                        </button>
                        <button type="button" wire:click="closeDetailModal" class="px-4 py-2 rounded-xl text-gray-700 bg-gray-200 hover:bg-gray-300 text-xs font-bold transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL 4: KONFIRMASI HAPUS SERVIS NON REAL                               -->
    <!-- ======================================================================= -->
    @if($modalDeleteOpen)
        <div wire:key="modal-delete-servis-non-real" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="$set('modalDeleteOpen', false)"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    <div class="p-6 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <h3 class="text-base font-black text-gray-900">Hapus Servis Non Real?</h3>
                        <p class="text-xs text-gray-500 font-medium">Transaksi <strong class="text-gray-900">{{ $deleteServisKode }}</strong> beserta seluruh rincian itemnya akan dihapus permanen.</p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-2">
                        <button type="button" wire:click="$set('modalDeleteOpen', false)" class="px-4 py-2 rounded-xl text-gray-700 bg-gray-200 hover:bg-gray-300 text-xs font-bold transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="deleteServis"
                                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md transition-all cursor-pointer">
                            Hapus Permanen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

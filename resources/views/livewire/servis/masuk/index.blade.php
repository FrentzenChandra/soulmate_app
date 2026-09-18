<div x-data x-init="
    if (window.Echo) {
        window.Echo.channel('servis-updates')
            .listen('.ServisStatusUpdated', () => { $wire.$refresh(); })
            .listen('ServisStatusUpdated', () => { $wire.$refresh(); });
    }
" x-on:open-print-receipt.window="window.open($event.detail.url, '_blank')" class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOMBOL AKSI UTAMA            -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Servis Masuk &amp; Antrean Unit
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Penerimaan unit servis baru (Laptop, PC, Printer), nomor tiket nota harian, dan pemantauan antrean perbaikan.
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
    <!-- METRIC CARDS: RINGKASAN ANTREAN SERVIS         -->
    <!-- ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Unit Masuk -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Unit Masuk</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalUnitMasuk }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-purple-600 mt-0.5 inline-block">
                    &bull; Keseluruhan Antrean
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Pengecekan / Diagnosa -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Pengecekan / Diagnosa</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalPengecekan }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-amber-600 mt-0.5 inline-block">
                    &bull; Antre Pengecekan Awal
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
                    &bull; Proses Perbaikan
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0 border border-blue-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>

        <!-- Card 4: Selesai (Siap Diambil) -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Selesai</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalSelesai }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                    &bull; Hubungi Pelanggan
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
            <div class="{{ $isHQ ? 'md:col-span-5' : 'md:col-span-6' }} relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all">
            </div>

            <!-- Filter Status (5 Status Utama) -->
            <div class="{{ $isHQ ? 'md:col-span-4' : 'md:col-span-3' }}">
                <select wire:model.live="statusFilter"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                    <option value="semua">Semua Status Servis</option>
                    <option value="pengecekan">Pengecekan</option>
                    <option value="perbaikan">Perbaikan</option>
                    <option value="selesai">Selesai</option>
                    <option value="sudah_diambil">Sudah Diambil</option>
                    <option value="batal">Batal</option>
                </select>
            </div>

            <!-- Filter Kategori Perangkat -->
            <div class="{{ $isHQ ? 'md:col-span-3' : 'md:col-span-3' }}">
                <select wire:model.live="kategoriFilter"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                    <option value="semua">Semua Kategori Perangkat</option>
                    <option value="laptop">Laptop / Notebook</option>
                    <option value="komputer">PC Komputer / Desktop</option>
                    <option value="printer">Printer / Scanner</option>
                    <option value="lainnya">Perangkat Lainnya</option>
                </select>
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
    <!-- TABEL ANTREAN SERVIS MASUK (COMPACT DENSITY)  -->
    <!-- ============================================== -->
    <div class="card-custom bg-white overflow-hidden border border-gray-200 shadow-sm rounded-2xl">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/80 text-[10px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <x-th-sort field="no_servis" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">No. Tiket</x-th-sort>
                        <x-th-sort field="tanggal_masuk" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Waktu Masuk</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Pelanggan</th>
                        <x-th-sort field="tipe_unit" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Unit &amp; Keluhan</x-th-sort>
                        @if($isHQ)
                            <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Cabang</th>
                        @endif
                        <x-th-sort field="status" :sortField="$sortField" :sortDirection="$sortDirection" align="center" class="py-2.5 px-3 text-center">Status</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query/Filter/Search/Sort/Pagination -->
                <tbody wire:key="servis-skeleton-body"
                       wire:loading.delay 
                       wire:target="search, statusFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore" 
                       class="divide-y divide-gray-100 bg-white">
                    @for($i = 0; $i < 6; $i++)
                        <tr class="animate-pulse">
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-20"></div></td>
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-36"></div></td>
                            @if($isHQ)
                                <td class="py-2 px-3"><div class="h-3 bg-gray-200 rounded w-16"></div></td>
                            @endif
                            <td class="py-2 px-3 text-center"><div class="h-4 bg-gray-200 rounded-full w-20 mx-auto"></div></td>
                            <td class="py-2 px-3 text-right"><div class="h-5 bg-gray-100 rounded w-16 ml-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <tbody wire:key="servis-data-body"
                       wire:loading.remove 
                       wire:target="search, statusFilter, kategoriFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore" 
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $item)
                        <tr wire:key="servis-row-{{ $item->id }}" class="hover:bg-purple-50/30 transition-colors group">
                            
                            <!-- No Tiket Servis -->
                            <td class="py-2 px-3 whitespace-nowrap">
                                <span class="font-mono font-black text-gray-900 block group-hover:text-[#5E17EB] transition-colors leading-tight">
                                    {{ $item->no_servis_frontend }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-400 block leading-tight">
                                    {{ $item->pembuat?->name ?? 'Staf' }}
                                </span>
                            </td>

                            <!-- Tanggal Masuk -->
                            <td class="py-2 px-3 whitespace-nowrap">
                                <span class="font-bold text-gray-800 block leading-tight">
                                    {{ $item->tanggal_masuk ? $item->tanggal_masuk->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-400 block leading-tight">
                                    {{ $item->tanggal_masuk ? $item->tanggal_masuk->format('H:i') . ' WIB' : '-' }}
                                </span>
                            </td>

                            <!-- Pelanggan -->
                            <td class="py-2 px-3">
                                <span class="font-bold text-gray-900 block truncate max-w-[140px] leading-tight" title="{{ $item->customer?->nama ?? 'Pelanggan Umum' }}">
                                    {{ $item->customer?->nama ?? 'Pelanggan Umum' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-500 block leading-tight">
                                    {{ $item->customer?->no_hp ?? '-' }}
                                </span>
                            </td>

                            <!-- Unit & Keluhan -->
                            <td class="py-2 px-3 max-w-xs">
                                <div class="flex items-center space-x-1.5">
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0 {{ $item->kategori_perangkat === 'laptop' ? 'bg-blue-100 text-blue-700' : ($item->kategori_perangkat === 'komputer' ? 'bg-purple-100 text-purple-700' : ($item->kategori_perangkat === 'printer' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700')) }}">
                                        {{ $item->kategori_perangkat_label }}
                                    </span>
                                    <span class="font-bold text-gray-900 truncate" title="{{ $item->tipe_unit }}">
                                        {{ $item->tipe_unit }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-500 font-medium truncate mt-0.5 leading-tight" title="{{ $item->keluhan }}">
                                    Keluhan: <span class="text-gray-700">{{ $item->keluhan }}</span>
                                </p>
                            </td>

                            <!-- Cabang (HQ Only) -->
                            @if($isHQ)
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">
                                        {{ $item->cabang?->nama_cabang ?? 'Pusat' }}
                                    </span>
                                </td>
                            @endif

                            <!-- Status Pengerjaan -->
                            <td class="py-2 px-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->status_badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-2 px-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-1">
                                    
                                    <!-- Tombol Update Status Cepat -->
                                    <button type="button"
                                            wire:click="openStatusModal({{ $item->id }})"
                                            class="p-1 rounded-md bg-gray-100 hover:bg-blue-50 text-gray-600 hover:text-[#0052FF] transition-colors cursor-pointer"
                                            title="Perbarui Status & Log">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>

                                    <!-- Tombol Cetak Tanda Terima -->
                                    <a href="{{ route('servis.tanda-terima', $item->id) }}" 
                                       target="_blank"
                                       class="p-1 rounded-md bg-gray-100 hover:bg-purple-50 text-gray-600 hover:text-[#5E17EB] transition-colors cursor-pointer"
                                       title="Cetak Tanda Terima Servis">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>

                                    <!-- Tombol Detail -->
                                    <button type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            class="p-1 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer"
                                            title="Lihat Detail & Riwayat Timeline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 7 : 6 }}" class="py-12 text-center">
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
    <!-- MODAL FORM: PENERIMAAN SERVIS BARU             -->
    <!-- ============================================== -->
    @if($modalCreateOpen)
        <div wire:key="modal-create-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeCreateModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-[#0E1726] px-6 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-300 flex items-center justify-center border border-purple-500/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-white">Penerimaan Servis Baru</h3>
                                <p class="text-xs text-purple-200">Input unit masuk, keluhan kerusakan, &amp; cetak tanda terima</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal Form -->
                    <form wire:submit.prevent="simpanServisMasuk">
                        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">
                            
                            @error('general')
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 font-bold">
                                    {{ $message }}
                                </div>
                            @enderror

                            <!-- Section 1: Informasi Pelanggan -->
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
                                            <label class="block font-bold text-gray-700 mb-1">Nama Lengkap Pelanggan *</label>
                                            <input type="text"
                                                   wire:model="newCustomerNama"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @error('newCustomerNama') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block font-bold text-gray-700 mb-1">No. WhatsApp / HP *</label>
                                            <input type="text"
                                                   wire:model="newCustomerNoHp"
                                                   class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            @error('newCustomerNoHp') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Section 2: Spesifikasi Perangkat -->
                            <div class="space-y-3">
                                <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">2. Detail Perangkat &amp; Unit</span>
                                
                                <!-- Kategori Perangkat Pills -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'laptop')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'laptop' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Laptop
                                    </button>
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'komputer')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'komputer' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        PC Komputer
                                    </button>
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'printer')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'printer' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Printer / Scan
                                    </button>
                                    <button type="button"
                                            wire:click="$set('kategoriPerangkat', 'lainnya')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $kategoriPerangkat === 'lainnya' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] shadow-2xs font-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Lainnya
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1">Tipe / Merk Perangkat *</label>
                                        <input type="text"
                                               wire:model="tipeUnit"
                                               class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                        @error('tipeUnit') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1">Nomor Seri / Serial Number (Opsional)</label>
                                        <input type="text"
                                               wire:model="serialNumber"
                                               class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-mono font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    </div>
                                </div>

                                <!-- Kelengkapan Bawaan Chips -->
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Kelengkapan Bawaan Unit</label>
                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        @foreach(['Unit', 'Adaptor / Charger', 'Kabel Power', 'Tas Laptop', 'Dus / Box', 'Mouse', 'Flashdisk / Harddisk'] as $chip)
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
                                           class="w-full px-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs font-semibold text-gray-800 focus:bg-white focus:ring-1 focus:ring-[#5E17EB]">
                                </div>
                            </div>

                            <!-- Section 3: Diagnosa Keluhan & Kondisi Fisik -->
                            <div class="space-y-3">
                                <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">3. Keluhan &amp; Kondisi Fisik</span>
                                
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Keluhan Kerusakan / Permasalahan *</label>
                                    <textarea wire:model="keluhan"
                                              rows="2"
                                              class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]"></textarea>
                                    @error('keluhan') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1">Kondisi Fisik / Bodi Awal</label>
                                        <input type="text"
                                               wire:model="kondisiBarang"
                                               class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                                        <input type="text"
                                               wire:model="catatan"
                                               class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Footer Modal Actions -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeCreateModal"
                                    class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-bold text-white transition-all shadow-md shadow-purple-600/20 cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="simpanServisMasuk">Simpan &amp; Cetak Tanda Terima</span>
                                <span wire:loading wire:target="simpanServisMasuk" class="flex items-center space-x-1">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Menyimpan &amp; Mencetak...</span>
                                </span>
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
                            <span>Cetak Ulang Tanda Terima</span>
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
    <!-- MODAL DETAIL UNIT & TIMELINE STATUS            -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedServis)
        <div wire:key="modal-detail-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                    
                    <!-- Header Modal Detail -->
                    <div class="p-6 pb-4 border-b border-gray-100 flex items-start justify-between">
                        <div class="flex items-start space-x-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-black text-gray-900 font-mono">
                                        {{ $selectedServis->no_servis_frontend }}
                                    </h3>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-purple-100 text-[#5E17EB]">
                                        {{ $selectedServis->kategori_perangkat_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Masuk: {{ $selectedServis->tanggal_masuk ? $selectedServis->tanggal_masuk->translatedFormat('d F Y - H:i') . ' WIB' : '-' }}
                                </p>
                            </div>
                        </div>

                        <button wire:click="closeDetailModal" class="p-2 text-gray-400 hover:text-gray-700 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal Detail -->
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar text-xs">
                        
                        <!-- Snapshot Pelanggan & Status -->
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
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Tipe / Merk Unit</span>
                                    <span class="font-black text-gray-900 block">{{ $selectedServis->tipe_unit }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Serial Number (S/N)</span>
                                    <span class="font-mono font-bold text-gray-700 block">{{ $selectedServis->serial_number ?: '-' }}</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-gray-200">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Kelengkapan Bawaan</span>
                                <span class="font-bold text-gray-800 block">{{ $selectedServis->kelengkapan ?: 'Hanya Unit' }}</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Keluhan Kerusakan</span>
                                <p class="font-bold text-gray-900 mt-0.5 whitespace-pre-line leading-relaxed">{{ $selectedServis->keluhan }}</p>
                            </div>
                            @if($selectedServis->kondisi_barang)
                                <div class="pt-2 border-t border-gray-200">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Kondisi Fisik Bodi</span>
                                    <p class="font-semibold text-gray-700 mt-0.5">{{ $selectedServis->kondisi_barang }}</p>
                                </div>
                            @endif
                            @if($selectedServis->catatan)
                                <div class="pt-2 border-t border-gray-200">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Catatan Staf</span>
                                    <p class="font-semibold text-gray-700 mt-0.5">{{ $selectedServis->catatan }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Timeline Riwayat Status Log -->
                        <div>
                            <span class="text-xs font-extrabold text-gray-900 uppercase tracking-wider block mb-3">
                                Riwayat Perjalanan Status Servis
                            </span>
                            <div class="space-y-3 pl-2 border-l-2 border-purple-200">
                                @forelse($selectedServis->statusLogs->sortByDesc('id') as $log)
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

                    <!-- Footer Modal Action -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                        <a href="{{ route('servis.tanda-terima', $selectedServis->id) }}"
                           target="_blank"
                           class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-xs font-bold text-white transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Tanda Terima</span>
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
    <!-- MODAL UPDATE STATUS CEPAT                      -->
    <!-- ============================================== -->
    @if($modalStatusOpen && $servisToUpdate)
        <div wire:key="modal-status-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeStatusModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs">
                    
                    <div class="bg-[#0E1726] px-6 py-4 text-white flex items-center justify-between">
                        <h3 class="text-sm font-black text-white">Update Status Pengerjaan</h3>
                        <button type="button" wire:click="closeStatusModal" class="text-gray-400 hover:text-white cursor-pointer">
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
                                <label class="block font-bold text-gray-700 mb-1.5">Pilih Status Baru *</label>
                                <select wire:model="updateStatus"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    <option value="pengecekan">Pengecekan</option>
                                    <option value="perbaikan">Perbaikan</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="sudah_diambil">Sudah Diambil</option>
                                    <option value="batal">Batal</option>
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
                            <button type="button" wire:click="closeStatusModal" class="px-4 py-2 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-white font-bold transition-all shadow-md cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="simpanUpdateStatus">Simpan Status</span>
                                <span wire:loading wire:target="simpanUpdateStatus">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

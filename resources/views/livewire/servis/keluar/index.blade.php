<div x-data x-init="
    if (window.Echo) {
        window.Echo.channel('servis-updates')
            .listen('.ServisStatusUpdated', () => { $wire.$refresh(); })
            .listen('ServisStatusUpdated', () => { $wire.$refresh(); });
    }
" x-on:open-print-invoice.window="window.open($event.detail.url, '_blank')" class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & INFORMASI                     -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Servis Keluar
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Pemantauan unit selesai diservis, penyerahan unit ke pelanggan, kasir pelunasan, dan garansi purna jual.
            </p>
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
    <!-- METRIC CARDS: RINGKASAN SERVIS KELUAR          -->
    <!-- ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Card 1: Siap Diambil / Belum Diambil -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Belum Diambil (Siap)</span>
                <span class="text-2xl font-black text-amber-600 mt-1.5 block font-mono">
                    {{ $totalSiapDiambil }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-amber-600 mt-0.5 inline-block">
                    &bull; Menunggu Serah Terima
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Sudah Diambil -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Sudah Diserahkan</span>
                <span class="text-2xl font-black text-emerald-600 mt-1.5 block font-mono">
                    {{ $totalSudahDiambil }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                    &bull; Selesai &amp; Lunas
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Total Pelunasan Biaya Servis -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Terbayar</span>
                <span class="text-2xl font-black text-[#5E17EB] mt-1.5 block font-mono">
                    Rp {{ number_format($totalPendapatanServis, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-purple-600 mt-0.5 inline-block">
                    &bull; Servis Tuntas
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- FILTER BAR & PENCARIAN                         -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-5 space-y-4 border border-gray-100/90 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
            
            <!-- Pencarian Multi-Kata Longgar -->
            <div class="{{ $isHQ ? 'md:col-span-6' : 'md:col-span-8' }} relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all">
            </div>

            <!-- Filter Status Pengambilan -->
            <div class="{{ $isHQ ? 'md:col-span-3' : 'md:col-span-4' }} flex items-center gap-2">
                <select wire:model.live="statusFilter"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                    <option value="semua">Semua Status (Belum &amp; Sudah Diambil)</option>
                    <option value="selesai">Belum Diambil (Siap Diambil)</option>
                    <option value="sudah_diambil">Sudah Diambil (Selesai Diserahkan)</option>
                </select>
                <x-table-size-selector />
            </div>

            <!-- Filter Cabang (HQ Saja) -->
            @if($isHQ)
                <div class="md:col-span-3">
                    <select wire:model.live="cabangFilter"
                            class="w-full py-2.5 px-3 rounded-xl border border-purple-200 bg-purple-50/40 text-xs font-bold text-[#5E17EB] focus:bg-white focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                        <option value="semua">Semua Cabang (HQ)</option>
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL SERVIS KELUAR (BELUM DIAMBIL DI ATAS)    -->
    <!-- ============================================== -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="card-custom bg-white overflow-hidden border border-gray-200 shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fixed min-w-[1060px]">
                <colgroup>
                    <col style="width: 140px;">
                    <col style="width: 160px;">
                    <col style="width: 220px;">
                    <col style="width: 200px;">
                    <col style="width: 140px;">
                    <col style="width: 160px;">
                    <col style="width: 160px;">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/80 text-[10px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <x-th-sort field="no_servis" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Kode Servis</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Pelanggan</th>
                        <x-th-sort field="tipe_unit" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Perangkat</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Tindakan</th>
                        <x-th-sort field="total_biaya" :sortField="$sortField" :sortDirection="$sortDirection" align="right" class="py-2.5 px-3 text-right">Biaya</x-th-sort>
                        <x-th-sort field="status" :sortField="$sortField" :sortDirection="$sortDirection" align="center" class="py-2.5 px-3 text-center">Status</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query/Filter/Search/Sort/Pagination -->
                <tbody wire:key="servis-keluar-skeleton-body"
                       wire:loading.delay 
                       wire:target="search, statusFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore" 
                       class="divide-y divide-gray-100 bg-white">
                    @for($i = 0; $i < 6; $i++)
                        <tr class="animate-pulse">
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-28"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-40"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-36"></div></td>
                            <td class="py-2.5 px-3 text-right"><div class="h-3.5 bg-gray-200 rounded w-20 ml-auto"></div></td>
                            <td class="py-2.5 px-3 text-center"><div class="h-5 bg-gray-200 rounded-full w-24 mx-auto"></div></td>
                            <td class="py-2.5 px-3 text-center"><div class="h-6 bg-gray-100 rounded w-28 mx-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <tbody wire:key="servis-keluar-data-body"
                       wire:loading.remove 
                       wire:target="search, statusFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore" 
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $item)
                        <tr wire:key="servis-keluar-row-{{ $item->id }}" class="hover:bg-purple-50/30 transition-colors group {{ $item->status === 'selesai' ? 'bg-amber-50/20' : '' }}">
                            
                            <!-- No Tiket Servis -->
                            <td class="py-2.5 px-3 whitespace-nowrap {{ $item->status === 'selesai' ? 'border-l-4 border-amber-500' : 'border-l-4 border-emerald-500' }}">
                                <span class="font-mono font-black text-gray-900 block group-hover:text-[#5E17EB] transition-colors leading-tight">
                                    {{ $item->no_servis_frontend }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-600 block leading-tight mt-0.5">
                                    {{ $item->cabang?->nama_cabang ?? 'Pusat' }}
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

                            <!-- Unit & Keluhan -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <div class="flex items-center space-x-1.5 truncate">
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0 {{ $item->kategori_perangkat === 'laptop' ? 'bg-blue-100 text-blue-700' : ($item->kategori_perangkat === 'komputer' ? 'bg-purple-100 text-purple-700' : ($item->kategori_perangkat === 'printer' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700')) }}">
                                        {{ $item->kategori_perangkat_label }}
                                    </span>
                                    <span class="font-bold text-gray-900 truncate" title="{{ $item->tipe_unit }}">
                                        {{ $item->tipe_unit }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-600 font-medium truncate mt-0.5 leading-tight" title="{{ $item->keluhan }}">
                                    Keluhan: <span class="text-gray-700">{{ $item->keluhan }}</span>
                                </p>
                            </td>

                            <!-- Servis & Kelengkapan -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <span class="text-[11px] font-bold text-gray-800 block truncate" title="{{ $item->details->pluck('nama_item')->implode(', ') ?: 'Tidak ada tindakan tambahan' }}">
                                    {{ $item->details->pluck('nama_item')->implode(', ') ?: 'Pengecekan Standard' }}
                                </span>
                                <span class="text-[10px] text-gray-600 block truncate mt-0.5" title="{{ $item->kelengkapan }}">
                                    Bawaan: <span class="text-gray-700">{{ $item->kelengkapan ?: 'Hanya Unit' }}</span>
                                </span>
                            </td>

                            <!-- Biaya Servis -->
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <span class="font-mono font-black text-xs text-[#5E17EB] block leading-tight">
                                    Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                                </span>
                                @if($item->status === 'sudah_diambil' || $item->status === 'sudah_dibayar')
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-700 inline-block mt-0.5">
                                        {{ $item->metode_pembayaran ?: 'Lunas' }}
                                    </span>
                                @else
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded bg-amber-100 text-amber-700 inline-block mt-0.5">
                                        Belum Lunas
                                    </span>
                                @endif
                            </td>

                            <!-- Status & Waktu Ambil -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                @if($item->status === 'selesai')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                        &bull; Belum Diambil
                                    </span>
                                    <span class="text-[10px] text-gray-600 font-medium block mt-0.5">
                                        Selesai: {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/y H:i') : '-' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sudah Diambil
                                    </span>
                                    <span class="text-[10px] font-mono text-gray-600 font-bold block mt-0.5">
                                        {{ $item->tanggal_ambil ? $item->tanggal_ambil->translatedFormat('d M Y, H:i') : '-' }}
                                    </span>
                                @endif
                            </td>

                            <!-- 4 TOMBOL AKSI LENGKAP -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">
                                    
                                    <!-- Aksi 1: Lihat Detail Modal -->
                                    <button type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer disabled:opacity-50"
                                            title="Lihat Detail Nota">
                                         <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- Aksi 2: Edit Nota -->
                                    @if($item->status === 'selesai')
                                        <button type="button"
                                                wire:click="openEditNotaModal({{ $item->id }})"
                                                wire:loading.attr="disabled"
                                                class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0052FF] transition-colors cursor-pointer disabled:opacity-50"
                                                title="Edit Nota Servis">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                    @else
                                        <button type="button"
                                                disabled
                                                class="p-1.5 rounded-lg bg-gray-100 text-gray-300 cursor-not-allowed"
                                                title="Nota terkunci (Unit sudah diserahkan)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                    @endif

                                    <!-- Aksi 3: Cetak Faktur Nota -->
                                    <a href="{{ route('servis.invoice', $item->id) }}" 
                                       target="_blank"
                                       class="p-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-[#5E17EB] transition-colors cursor-pointer"
                                       title="Cetak Faktur Nota">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>

                                    <!-- Aksi 4: Serah Terima / Pelunasan -->
                                    @if($item->status === 'selesai')
                                        <button type="button"
                                                wire:click="openAmbilModal({{ $item->id }})"
                                                wire:loading.attr="disabled"
                                                class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-xs cursor-pointer disabled:opacity-50"
                                                title="Pelunasan Servis">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    @else
                                        <button type="button"
                                                disabled
                                                class="p-1.5 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed"
                                                title="Unit Sudah Diserahkan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <p class="font-bold text-gray-600">Belum ada data servis keluar ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Semua data unit berstatus 'Selesai' atau 'Sudah Diambil' akan tampil di sini.</p>
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
                label="unit servis keluar" 
                :current="$servisList->count()" 
                :total="$totalItems" 
                color="purple" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL DETAIL SERVIS KELUAR                     -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedServis)
        <div wire:key="modal-detail-keluar" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                    
                    <!-- Header Modal -->
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
                                </div>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">
                                    Masuk: {{ $selectedServis->tanggal_masuk ? $selectedServis->tanggal_masuk->translatedFormat('d M Y') : '-' }} &bull; Selesai: {{ $selectedServis->tanggal_selesai ? $selectedServis->tanggal_selesai->translatedFormat('d M Y - H:i') : '-' }}
                                </p>
                            </div>
                        </div>

                        <button wire:click="closeDetailModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar text-xs">
                        
                        <!-- Snapshot Status & Tanggal Ambil -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Pelanggan</span>
                                <span class="font-black text-gray-900 block mt-1 truncate">{{ $selectedServis->customer?->nama ?? 'Umum' }}</span>
                                <span class="text-[10px] text-gray-500 font-mono block">{{ $selectedServis->customer?->no_hp ?? '-' }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Status Fisik</span>
                                <span class="font-black text-gray-900 block mt-1">
                                    {{ $selectedServis->status === 'selesai' ? 'Belum Diambil' : 'Sudah Diambil' }}
                                </span>
                                <span class="text-[10px] text-purple-600 font-bold block">{{ $selectedServis->cabang?->nama_cabang }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Waktu Pengambilan</span>
                                <span class="font-mono font-bold text-gray-900 block mt-1">
                                    {{ $selectedServis->tanggal_ambil ? $selectedServis->tanggal_ambil->translatedFormat('d M Y, H:i') : 'Belum Diserahkan' }}
                                </span>
                                <span class="text-[10px] text-emerald-600 font-bold block">
                                    {{ $selectedServis->status === 'sudah_diambil' || $selectedServis->status === 'sudah_dibayar' ? 'Unit Telah Diterima Pelanggan' : 'Menunggu Pengambilan' }}
                                </span>
                            </div>
                        </div>

                        <!-- Rincian Biaya & Komponen -->
                        <div class="border border-gray-200 rounded-2xl overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-extrabold text-gray-500 uppercase">
                                        <th class="py-2 px-3">Item / Tindakan</th>
                                        <th class="py-2 px-3 text-center">Jenis</th>
                                        <th class="py-2 px-3 text-center">Garansi</th>
                                        <th class="py-2 px-3 text-center">Qty</th>
                                        <th class="py-2 px-3 text-right">Harga</th>
                                        <th class="py-2 px-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($selectedServis->details as $d)
                                        <tr>
                                            <td class="py-2 px-3 font-bold text-gray-900">{{ $d->nama_item }}</td>
                                            <td class="py-2 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $d->jenis === 'jasa' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                                    {{ $d->jenis }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-3 text-center font-mono">
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $d->garansi_hari > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                                    {{ $d->garansi_label }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-3 text-center font-mono font-bold">{{ $d->qty }}</td>
                                            <td class="py-2 px-3 text-right font-mono text-gray-600">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                            <td class="py-2 px-3 text-right font-mono font-black text-gray-900">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-4 text-center text-gray-400 italic">Pengecekan Standard (Rp 0)</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-purple-50/50 border-t border-purple-100">
                                        <td colspan="5" class="py-2.5 px-3 font-black text-right uppercase tracking-wider text-purple-900">Total Biaya:</td>
                                        <td class="py-2.5 px-3 font-mono font-black text-sm text-[#5E17EB] text-right">
                                            Rp {{ number_format($selectedServis->total_biaya, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>

                    <!-- Footer Modal Detail -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                        <a href="{{ route('servis.invoice', $selectedServis->id) }}"
                           target="_blank"
                           class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-xs font-bold text-white transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Faktur Nota</span>
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
    <!-- MODAL EDIT NOTA                                -->
    <!-- ============================================== -->
    @if($modalEditNotaOpen && $editNotaServis)
        <div wire:key="modal-edit-nota" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeEditNotaModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Edit Nota Servis</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5 truncate">{{ $editNotaServis->no_servis_frontend }} &bull; {{ $editNotaServis->tipe_unit }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeEditNotaModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar text-xs">
                        
                        <!-- Tambah Item Baru -->
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200 space-y-3">
                            <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">+ Tambah Item Nota</span>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <div class="sm:col-span-4 relative">
                                    <label class="block font-bold text-gray-700 mb-1">Cari Master Servis</label>
                                    <input type="text" wire:model.live.debounce.250ms="itemSearch" class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-xs">
                                    @if(!empty($masterServisSuggestions) && !$selectedMasterServisId)
                                        <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                            @foreach($masterServisSuggestions as $ms)
                                                <button type="button" wire:key="sugg-edit-{{ $ms->id }}" wire:click="selectMasterItem({{ $ms->id }}, '{{ $ms->nama_item }}', '{{ $ms->jenis }}', {{ $ms->harga_jual }}, {{ $ms->garansi_default_hari ?? 0 }})" class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between text-xs cursor-pointer">
                                                    <div>
                                                        <span class="font-bold text-gray-900 block">{{ $ms->nama_item }}</span>
                                                        <span class="text-[10px] text-gray-400 uppercase font-mono">{{ $ms->jenis_label }} &bull; {{ $ms->garansi_default_hari > 0 ? $ms->garansi_default_hari.'hr' : 'No Grs' }}</span>
                                                    </div>
                                                    <span class="font-mono font-bold text-purple-700">Rp {{ number_format($ms->harga_jual, 0, ',', '.') }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block font-bold text-gray-700 mb-1">Nama Item *</label>
                                    <input type="text" wire:model="selectedMasterServisNama" class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-xs">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-gray-700 mb-1">Jenis</label>
                                    <select wire:model="selectedMasterServisJenis" class="w-full px-2 py-2 rounded-xl border border-gray-300 bg-white font-bold text-xs">
                                        <option value="jasa">Jasa</option>
                                        <option value="sparepart">Sparepart</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block font-bold text-gray-700 mb-1">Qty</label>
                                    <input type="number" min="1" wire:model="itemQty" class="w-full px-2 py-2 rounded-xl border border-gray-300 bg-white font-bold text-center text-xs">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-gray-700 mb-1">Garansi (Hari)</label>
                                    <input type="number" min="0" wire:model="itemGaransiHari" class="w-full px-2 py-2 rounded-xl border border-gray-300 bg-white font-bold text-center text-xs">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block font-bold text-gray-700 mb-1">Harga Satuan *</label>
                                    <x-input-rupiah wire:model="selectedMasterServisHarga" inputClass="!py-2 !text-xs !font-bold" />
                                </div>
                                <div class="sm:col-span-8 flex items-end">
                                    <button type="button"
                                            wire:click="tambahItemNota"
                                            wire:loading.attr="disabled"
                                            class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white font-bold transition-all shadow-sm cursor-pointer text-xs inline-flex items-center justify-center space-x-1.5">
                                        <svg wire:loading wire:target="tambahItemNota" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span>+ Tambahkan Item</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Item -->
                        <div>
                            <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block mb-2">Rincian Item Nota</span>
                            <div class="border border-gray-200 rounded-2xl overflow-hidden">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-extrabold text-gray-500 uppercase">
                                            <th class="py-2 px-3">Item</th>
                                            <th class="py-2 px-3 text-center whitespace-nowrap">Jenis</th>
                                            <th class="py-2 px-3 text-center whitespace-nowrap">Garansi</th>
                                            <th class="py-2 px-3 text-center whitespace-nowrap">Qty</th>
                                            <th class="py-2 px-3 text-right whitespace-nowrap">Harga</th>
                                            <th class="py-2 px-3 text-right whitespace-nowrap">Subtotal</th>
                                            <th class="py-2 px-3 text-center whitespace-nowrap">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($editNotaServis->details as $det)
                                            <tr wire:key="det-nota-{{ $det->id }}" class="hover:bg-gray-50/50">
                                                <td class="py-2 px-3 font-bold text-gray-900">{{ $det->nama_item }}</td>
                                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $det->jenis === 'jasa' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                                        {{ $det->jenis }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] font-mono font-bold whitespace-nowrap {{ $det->garansi_hari > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-gray-400' }}">
                                                        {{ $det->garansi_label }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3 text-center font-mono font-bold whitespace-nowrap">{{ $det->qty }}</td>
                                                <td class="py-2 px-3 text-right font-mono text-gray-600 whitespace-nowrap">Rp {{ number_format($det->harga, 0, ',', '.') }}</td>
                                                <td class="py-2 px-3 text-right font-mono font-black text-gray-900 whitespace-nowrap">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                                    <button type="button"
                                                            wire:click="hapusItemNota({{ $det->id }})"
                                                            wire:loading.attr="disabled"
                                                            class="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer disabled:opacity-50"
                                                            title="Hapus Item">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-6 text-center text-gray-400 italic">Belum ada item rincian.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50/90 border-t border-gray-200">
                                            <td colspan="5" class="py-3 px-3 font-black text-right uppercase tracking-wider text-gray-700 whitespace-nowrap">Total Tagihan:</td>
                                            <td class="py-3 px-3 font-mono font-black text-base text-[#5E17EB] text-right whitespace-nowrap">
                                                Rp {{ number_format($editNotaServis->details->sum('subtotal'), 0, ',', '.') }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end rounded-b-3xl border-t border-gray-100">
                        <button type="button" wire:click="closeEditNotaModal" class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer text-xs">
                            Selesai &amp; Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL CENTANG DIAMBIL / KASIR SERAH TERIMA     -->
    <!-- ============================================== -->
    @if($modalAmbilOpen && $servisToAmbil)
        <div wire:key="modal-ambil-servis" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeAmbilModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs">
                    
                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Pelunasan Servis</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Serah terima unit dan pembayaran sisa tagihan</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeAmbilModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="prosesPenyerahanUnit">
                        <div class="p-6 space-y-4">
                            
                            <!-- Snapshot Unit & Tagihan -->
                            <div class="p-3.5 bg-purple-50 rounded-2xl border border-purple-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider block">Total Tagihan Servis</span>
                                    <span class="text-xs font-bold text-gray-700 block mt-0.5">
                                        {{ $servisToAmbil->no_servis_frontend }} &bull; {{ $servisToAmbil->customer?->nama }}
                                    </span>
                                </div>
                                <span class="text-lg font-black text-[#5E17EB] font-mono">
                                    Rp {{ number_format($servisToAmbil->total_biaya, 0, ',', '.') }}
                                </span>
                            </div>

                            @error('ambilError')
                                <div class="p-2.5 bg-rose-50 text-rose-700 font-bold rounded-xl border border-rose-200">
                                    {{ $message }}
                                </div>
                            @enderror

                            <!-- Metode Pembayaran -->
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Metode Pembayaran *</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button"
                                            wire:click="$set('metodePembayaran', 'cash')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $metodePembayaran === 'cash' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] font-black' : 'border-gray-200 bg-white text-gray-700' }}">
                                        Cash (Tunai)
                                    </button>
                                    <button type="button"
                                            wire:click="$set('metodePembayaran', 'transfer')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $metodePembayaran === 'transfer' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] font-black' : 'border-gray-200 bg-white text-gray-700' }}">
                                        Transfer
                                    </button>
                                    <button type="button"
                                            wire:click="$set('metodePembayaran', 'qris')"
                                            class="py-2 px-3 rounded-xl font-bold border text-center transition-all cursor-pointer {{ $metodePembayaran === 'qris' ? 'bg-purple-50 border-[#5E17EB] text-[#5E17EB] font-black' : 'border-gray-200 bg-white text-gray-700' }}">
                                        QRIS
                                    </button>
                                </div>
                            </div>

                            <!-- Nominal Bayar -->
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Nominal Diterima Kasir *</label>
                                <x-input-rupiah wire:model.live.debounce.250ms="nominalBayar" inputClass="!py-2.5 !text-sm !font-mono !font-bold" />
                                @error('nominalBayar') <span class="text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                            </div>

                            <!-- Kembalian -->
                            @if((float)$nominalBayar >= (float) $servisToAmbil->total_biaya)
                                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs font-bold text-emerald-800">
                                    <span>Kembalian Kasir:</span>
                                    <span class="font-mono text-sm font-black">
                                        Rp {{ number_format(max(0, (float)$nominalBayar - (float) $servisToAmbil->total_biaya), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button" wire:click="closeAmbilModal" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                     wire:loading.attr="disabled"
                                     class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black transition-all shadow-md cursor-pointer disabled:opacity-50 inline-flex items-center space-x-1.5">
                                <svg wire:loading wire:target="prosesPenyerahanUnit" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Serahkan Unit</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

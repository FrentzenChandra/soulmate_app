<div x-data x-init="
    if (window.Echo) {
        window.Echo.channel('servis-updates')
            .listen('.ServisStatusUpdated', () => { $wire.$refresh(); })
            .listen('ServisStatusUpdated', () => { $wire.$refresh(); });
    }
" class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & INFORMASI                     -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Servis Batal
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Daftar unit yang dibatalkan pengerjaannya, status pengembalian fisik unit, dan pencatatan serah terima ke pelanggan.
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
    <!-- METRIC CARDS: RINGKASAN SERVIS BATAL           -->
    <!-- ============================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <!-- Card 1: Total Unit Batal -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Servis Batal</span>
                <span class="text-2xl font-black text-gray-900 mt-1.5 block font-mono">
                    {{ $totalBatal }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-rose-600 mt-0.5 inline-block">
                    &bull; Keseluruhan Batal
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Fisik Belum Diambil Pelanggan -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Fisik Belum Diambil</span>
                <span class="text-2xl font-black text-amber-600 mt-1.5 block font-mono">
                    {{ $totalBatalBelumDiambil }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-amber-600 mt-0.5 inline-block">
                    &bull; Masih Tertinggal di Toko
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Fisik Sudah Dikembalikan -->
        <div class="card-custom bg-white p-5 flex items-center justify-between border border-gray-100/90 shadow-sm">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Fisik Sudah Diambil</span>
                <span class="text-2xl font-black text-emerald-600 mt-1.5 block font-mono">
                    {{ $totalBatalSudahDiambil }} UNIT
                </span>
                <span class="text-[11px] font-semibold text-emerald-600 mt-0.5 inline-block">
                    &bull; Serah Terima Tuntas
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
            <div class="{{ $isHQ ? 'md:col-span-4' : 'md:col-span-5' }} relative">
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

            <!-- Filter Status Pengambilan Fisik Unit -->
            <div class="{{ $isHQ ? 'md:col-span-3' : 'md:col-span-5' }} flex items-center gap-2">
                <select wire:model.live="statusPengambilan"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                    <option value="semua">Semua Status Fisik Unit</option>
                    <option value="belum_diambil">Fisik Belum Diambil (Di Toko)</option>
                    <option value="sudah_diambil">Fisik Sudah Diambil Pelanggan</option>
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
    <!-- TABEL SERVIS BATAL (BELUM DIAMBIL DI ATAS)     -->
    <!-- ============================================== -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="card-custom bg-white overflow-hidden border border-gray-200 shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fixed min-w-[1050px]">
                <colgroup>
                    <col style="width: 130px;">
                    <col style="width: 90px;">
                    <col style="width: 150px;">
                    <col style="width: 230px;">
                    <col style="width: 170px;">
                    <col style="width: 160px;">
                    <col style="width: 140px;">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/80 text-[10px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <x-th-sort field="no_servis" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Kode Servis</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-center">Tipe</th>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Pelanggan</th>
                        <x-th-sort field="tipe_unit" :sortField="$sortField" :sortDirection="$sortDirection" class="py-2.5 px-3">Perangkat</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500">Kelengkapan</th>
                        <x-th-sort field="tanggal_ambil" :sortField="$sortField" :sortDirection="$sortDirection" align="center" class="py-2.5 px-3 text-center">Status</x-th-sort>
                        <th class="py-2.5 px-3 font-extrabold text-[10px] tracking-wider uppercase text-gray-500 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query/Filter/Search/Sort/Pagination -->
                <tbody wire:key="servis-batal-skeleton-body"
                       wire:loading.delay
                       wire:target="search, statusPengambilan, tipeFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 bg-white">
                    @for($i = 0; $i < 6; $i++)
                        <tr class="animate-pulse">
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-24"></div></td>
                            <td class="py-2.5 px-3 text-center"><div class="h-4 bg-gray-200 rounded-full w-14 mx-auto"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-28"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-44"></div></td>
                            <td class="py-2.5 px-3"><div class="h-3.5 bg-gray-200 rounded w-32"></div></td>
                            <td class="py-2.5 px-3 text-center"><div class="h-5 bg-gray-200 rounded-full w-24 mx-auto"></div></td>
                            <td class="py-2.5 px-3 text-center"><div class="h-6 bg-gray-100 rounded w-24 mx-auto"></div></td>
                        </tr>
                    @endfor
                </tbody>

                <tbody wire:key="servis-batal-data-body"
                       wire:loading.remove
                       wire:target="search, statusPengambilan, tipeFilter, cabangFilter, sortBy, sortField, sortDirection, loadMore"
                       class="divide-y divide-gray-100 text-xs">
                    @forelse($servisList as $item)
                        <tr wire:key="servis-batal-row-{{ $item->id }}" class="hover:bg-purple-50/30 transition-colors group {{ is_null($item->tanggal_ambil) ? 'bg-rose-50/20' : '' }}">

                            <!-- No Tiket Servis -->
                            <td class="py-2.5 px-3 whitespace-nowrap {{ is_null($item->tanggal_ambil) ? 'border-l-4 border-rose-500' : 'border-l-4 border-gray-400' }}">
                                <span class="font-mono font-black text-gray-900 block group-hover:text-[#5E17EB] transition-colors leading-tight truncate">
                                    {{ $item->no_servis_frontend }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-400 block leading-tight mt-0.5 truncate">
                                    {{ $item->cabang?->nama_cabang ?? 'Pusat' }}
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

                            <!-- Pelanggan -->
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-gray-900 block truncate max-w-[150px] leading-tight" title="{{ $item->customer?->nama ?? 'Pelanggan Umum' }}">
                                    {{ $item->customer?->nama ?? 'Pelanggan Umum' }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-500 block leading-tight mt-0.5">
                                    {{ $item->customer?->no_hp ?? '-' }}
                                </span>
                            </td>

                            <!-- Unit & Keluhan -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <div class="flex items-center space-x-1.5 truncate">
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0 bg-rose-100 text-rose-700">
                                        Batal
                                    </span>
                                    <span class="font-bold text-gray-900 truncate" title="{{ $item->tipe_unit }}">
                                        {{ $item->tipe_unit }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-500 font-medium truncate mt-0.5 leading-tight" title="{{ $item->keluhan }}">
                                    Keluhan: <span class="text-gray-700">{{ $item->keluhan }}</span>
                                </p>
                            </td>

                            <!-- Kelengkapan -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <span class="text-[11px] font-bold text-gray-800 block truncate" title="{{ $item->kelengkapan }}">
                                    {{ $item->kelengkapan ?: 'Hanya Unit' }}
                                </span>
                            </td>

                            <!-- Status & Waktu Pengambilan Fisik -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                @if(is_null($item->tanggal_ambil))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        &bull; Servisan Belum Diambil
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium block mt-0.5">
                                        Unit masih di toko
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                        Servis Sudah Diambil
                                    </span>
                                    <span class="text-[10px] font-mono text-gray-500 font-bold block mt-0.5">
                                        {{ $item->tanggal_ambil->translatedFormat('d M Y, H:i') }}
                                    </span>
                                @endif
                            </td>

                            <!-- 4 TOMBOL AKSI LENGKAP -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">

                                    <!-- Aksi 1: Lihat Detail -->
                                    <button type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer disabled:opacity-50"
                                            title="Lihat Detail &amp; Riwayat">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- Aksi 2: Ubah Status Pengerjaan (Logo Kunci Inggris) -->
                                    <button type="button"
                                            wire:click="openUbahStatusModal({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0052FF] transition-colors cursor-pointer disabled:opacity-50"
                                            title="Ubah Status Pengerjaan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </button>

                                    <!-- Aksi 3: Cetak Tanda Terima / Bukti Batal -->
                                    <a href="{{ route('servis.tanda-terima', $item->id) }}"
                                       class="p-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-[#5E17EB] transition-colors cursor-pointer"
                                       title="Cetak Bukti Servis Batal">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>

                                    <!-- Aksi 4: Centang Diambil Langsung (Tanpa Biaya) -->
                                    @if(is_null($item->tanggal_ambil))
                                        <button type="button"
                                                wire:click="openAmbilBatalModal({{ $item->id }})"
                                                wire:loading.attr="disabled"
                                                class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-xs cursor-pointer disabled:opacity-50"
                                                title="Serah Terima Fisik Unit Kembali ke Pelanggan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    @else
                                        <button type="button"
                                                disabled
                                                class="p-1.5 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed"
                                                title="Fisik Unit Sudah Diserahkan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <p class="font-bold text-gray-600">Belum ada data servis batal ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Data unit berstatus 'Batal' akan terpusat di sini untuk pemantauan fisik barang.</p>
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
                label="unit servis batal"
                :current="$servisList->count()"
                :total="$totalItems"
                color="purple" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL DETAIL SERVIS BATAL                      -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedServis)
        <div wire:key="modal-detail-batal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-bold text-white font-mono tracking-tight leading-snug">
                                        {{ $selectedServis->no_servis_frontend }}
                                    </h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white/20 text-white border border-white/30">
                                        Servis Dibatalkan
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

                    <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar text-xs">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Pelanggan</span>
                                <span class="font-black text-gray-900 block mt-1 truncate">{{ $selectedServis->customer?->nama ?? 'Umum' }}</span>
                                <span class="text-[10px] text-gray-500 font-mono block">{{ $selectedServis->customer?->no_hp ?? '-' }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Status Pengambilan Fisik</span>
                                <span class="font-black text-gray-900 block mt-1">
                                    {{ is_null($selectedServis->tanggal_ambil) ? 'Belum Diambil Pelanggan' : 'Sudah Diambil Pelanggan' }}
                                </span>
                                <span class="text-[10px] text-gray-500 font-mono block">
                                    {{ $selectedServis->tanggal_ambil ? $selectedServis->tanggal_ambil->translatedFormat('d M Y, H:i') : 'Masih di Toko' }}
                                </span>
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
                                    <span class="text-[10px] font-bold text-purple-600 uppercase block">Alasan Pembatalan / Catatan</span>
                                    <p class="font-semibold text-gray-800 mt-0.5 whitespace-pre-line">{{ $selectedServis->catatan }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Timeline Riwayat Status Log -->
                        <div>
                            <span class="text-xs font-extrabold text-gray-900 uppercase tracking-wider block mb-3">
                                Riwayat Status Servis
                            </span>
                            <div class="space-y-3 pl-2 border-l-2 border-rose-200">
                                @forelse($selectedServis->statusLogs as $log)
                                    <div wire:key="batal-log-{{ $log->id }}" class="relative pl-4">
                                        <span class="absolute -left-[11px] top-1.5 w-3 h-3 rounded-full bg-rose-500 ring-4 ring-rose-100"></span>
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

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                        <a href="{{ route('servis.tanda-terima', $selectedServis->id) }}"
                           class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-xs font-bold text-white transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Bukti Servis Batal</span>
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
    <!-- MODAL KONFIRMASI SERAH TERIMA UNIT BATAL       -->
    <!-- ============================================== -->
    @if($modalAmbilBatalOpen && $servisToAmbilBatal)
        <div wire:key="modal-ambil-batal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeAmbilBatalModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Serah Terima Pengembalian Unit</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Pengembalian unit servis batal ke pelanggan</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeAmbilBatalModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="serahkanUnitBatal">
                        <div class="p-6 space-y-4">

                            <div class="p-3.5 bg-rose-50 rounded-2xl border border-rose-100">
                                <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Pengembalian Unit Batal</span>
                                <span class="text-sm font-black text-gray-900 block mt-0.5">
                                    {{ $servisToAmbilBatal->no_servis_frontend }} &bull; {{ $servisToAmbilBatal->tipe_unit }}
                                </span>
                                <span class="text-xs text-gray-600 block mt-0.5">
                                    Pelanggan: <strong>{{ $servisToAmbilBatal->customer?->nama }}</strong> ({{ $servisToAmbilBatal->customer?->no_hp }})
                                </span>
                                <span class="text-[11px] font-bold text-emerald-700 block mt-2">
                                    &bull; Tanpa tagihan biaya (Rp 0)
                                </span>
                            </div>

                            @error('ambilBatalError')
                                <div class="p-2.5 bg-rose-50 text-rose-700 font-bold rounded-xl border border-rose-200">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Catatan Serah Terima (Opsional)</label>
                                <textarea wire:model="catatanSerahTerima"
                                          rows="3"
                                          class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]"></textarea>
                            </div>

                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button" wire:click="closeAmbilBatalModal" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black transition-all shadow-md cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="serahkanUnitBatal">Konfirmasi Penyerahan Unit</span>
                                <span wire:loading wire:target="serahkanUnitBatal">Memproses...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL UBAH STATUS / UPDATE STATUS CEPAT        -->
    <!-- ============================================== -->
    @if($modalUbahStatusOpen && $servisToUbahStatus)
        <div wire:key="modal-ubah-status-batal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeUbahStatusModal"></div>

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
                        <button type="button" wire:click="closeUbahStatusModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanUbahStatus">
                        <div class="p-6 space-y-4">

                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Tiket Servis</span>
                                <span class="text-sm font-black text-gray-900 font-mono block">{{ $servisToUbahStatus->no_servis_frontend }}</span>
                                <span class="text-xs text-gray-600 font-bold block">{{ $servisToUbahStatus->tipe_unit }} ({{ $servisToUbahStatus->customer?->nama }})</span>
                            </div>

                            @error('ubahStatusError')
                                <div class="p-2.5 bg-rose-50 text-rose-700 font-bold rounded-xl border border-rose-200">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div>
                                <label class="block font-bold text-gray-700 mb-1.5">Pilih Status Baru <span class="text-rose-500">*</span></label>
                                <select wire:model="targetStatus"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                    <option value="pengecekan">Pengecekan</option>
                                    <option value="perbaikan">Perbaikan</option>
                                    <option value="selesai">Selesai </option>
                                    <option value="batal">Batal Servis</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Catatan Perkembangan / Log</label>
                                <textarea wire:model="catatanUbahStatus"
                                          rows="3"
                                          class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]"></textarea>
                            </div>

                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button" wire:click="closeUbahStatusModal" class="px-4 py-2 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold transition-all shadow-md cursor-pointer disabled:opacity-50 inline-flex items-center space-x-1.5">
                                <svg wire:loading wire:target="simpanUbahStatus" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
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

</div>

<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>Operasional Cabang</span>
                <span>/</span>
                <span class="text-blue-600 font-bold">Stok & Inventori</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                Stok Cabang: {{ $cabang?->nama_cabang ?? 'Pilih Cabang' }}
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">Monitoring ketersediaan fisik suku cadang & barang dagang di outlet toko ini.</p>
        </div>

        @if(auth()->user()->isOwner() || auth()->user()->hasRole('Superadmin'))
            <a href="{{ route('master.produk') }}" 
               class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs border border-gray-200 shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Kelola Katalog Master</span>
            </a>
        @endif
    </div>

    <!-- Metric Cards Ringkasan Stok Cabang -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total SKU -->
        <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Jenis Barang</span>
                <span class="text-xl md:text-2xl font-extrabold text-gray-900 mt-0.5 block">{{ $totalSku }} <span class="text-xs font-medium text-gray-400">SKU</span></span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>

        <!-- Metric 2: Total Unit Fisik -->
        <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Fisik Unit</span>
                <span class="text-xl md:text-2xl font-extrabold text-[#0052FF] mt-0.5 block">{{ number_format($totalUnit, 0, ',', '.') }} <span class="text-xs font-medium text-gray-400">Pcs</span></span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0052FF] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </div>
        </div>

        <!-- Metric 3: Stok Kritis -->
        <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Stok Kritis (&le; 3)</span>
                <span class="text-xl md:text-2xl font-extrabold text-amber-600 mt-0.5 block">{{ $totalKritis }} <span class="text-xs font-medium text-gray-400">SKU</span></span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>

        <!-- Metric 4: Stok Habis -->
        <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Stok Kosong</span>
                <span class="text-xl md:text-2xl font-extrabold text-rose-600 mt-0.5 block">{{ $totalHabis }} <span class="text-xs font-medium text-gray-400">SKU</span></span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder="Scan barcode / cari nama produk..." 
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-gray-50/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0052FF] focus:border-[#0052FF] transition-all">
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Filter Kategori -->
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Kategori:</span>
                <select wire:model.live="kategoriFilter" 
                        class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-400">
                    <option value="semua">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Stok -->
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Status:</span>
                <select wire:model.live="stokFilter" 
                        class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-400">
                    <option value="semua">Semua Status</option>
                    <option value="aktif">Aktif Dijual di Cabang Ini</option>
                    <option value="non_aktif">Non-Aktif / Belum Dijual</option>
                    <option value="tersedia">Stok Tersedia (&gt; Min Alert)</option>
                    <option value="kritis">Stok Kritis (&le; Min Alert)</option>
                    <option value="habis">Stok Kosong (0)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white card-custom rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-14 font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Foto</th>
                        <x-th-sort field="nama_produk" :sortField="$sortField" :sortDirection="$sortDirection">Produk & Barcode</x-th-sort>
                        <x-th-sort field="kategori_id" :sortField="$sortField" :sortDirection="$sortDirection">Kategori</x-th-sort>
                        <x-th-sort field="harga_jual" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Harga Toko (POS)</x-th-sort>
                        <x-th-sort field="is_aktif" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Status Ketersediaan</x-th-sort>
                        <x-th-sort field="stok_jumlah" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Sisa Stok Fisik</x-th-sort>
                        <th class="py-3.5 px-5 text-right font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi Penyesuaian</th>
                    </tr>
                </thead>
                <x-table-skeleton :columns="7" :rows="6" target="search, kategoriFilter, stokFilter, sortField, sortDirection" color="blue" />
                <tbody wire:loading.remove wire:target="search, kategoriFilter, stokFilter, sortField, sortDirection" class="divide-y divide-gray-100">
                    @forelse($stokItems as $item)
                        @php
                            $stok = $item->stoks->first();
                            $isAktif = $stok ? (bool) $stok->is_aktif : false;
                            $qty = $stok?->jumlah ?? 0;
                            $minStok = $stok?->min_stok ?? 3;
                            $isZero = $isAktif && $qty <= 0;
                            $isCrit = $isAktif && ! $isZero && $qty <= $minStok;
                        @endphp
                        <tr class="transition-colors {{ $isZero ? 'bg-rose-50/40 border-l-4 border-rose-500 hover:bg-rose-50/60' : ($isCrit ? 'bg-amber-50/30 border-l-4 border-amber-500 hover:bg-amber-50/50' : 'hover:bg-gray-50/80') }}">
                            <!-- Foto Produk -->
                            <td class="py-4 px-4 text-center">
                                <div class="relative inline-block">
                                    @if($item->foto_url && file_exists(public_path($item->foto_url)))
                                        <img src="{{ asset($item->foto_url) }}" 
                                             alt="{{ $item->nama_produk }}" 
                                             loading="lazy"
                                             decoding="async"
                                             class="w-12 h-12 rounded-xl object-cover border {{ $isZero ? 'border-rose-300 ring-2 ring-rose-200' : ($isCrit ? 'border-amber-300 ring-2 ring-amber-200' : 'border-gray-200') }} shadow-xs mx-auto">
                                    @elseif($item->foto_url && (str_starts_with($item->foto_url, 'http') || str_starts_with($item->foto_url, 'storage/') || str_starts_with($item->foto_url, 'images/')))
                                        <img src="{{ asset($item->foto_url) }}" 
                                             alt="{{ $item->nama_produk }}" 
                                             loading="lazy"
                                             decoding="async"
                                             class="w-12 h-12 rounded-xl object-cover border {{ $isZero ? 'border-rose-300 ring-2 ring-rose-200' : ($isCrit ? 'border-amber-300 ring-2 ring-amber-200' : 'border-gray-200') }} shadow-xs mx-auto"
                                             onerror="this.onerror=null;this.src='{{ asset('images/logo_bulat.jpg') }}';">
                                    @else
                                        <div class="w-12 h-12 rounded-xl {{ $isZero ? 'bg-rose-100 text-rose-600 border border-rose-200' : ($isCrit ? 'bg-amber-100 text-amber-600 border border-amber-200' : 'bg-blue-50 text-[#0052FF] border border-blue-100') }} flex items-center justify-center mx-auto shadow-2xs">
                                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif

                                    <!-- Floating Alert Indicator on Thumbnail (Hanya jika aktif) -->
                                    @if($isZero)
                                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-md ring-2 ring-white" title="Stok Kosong!">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </span>
                                    @elseif($isCrit)
                                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-md ring-2 ring-white animate-bounce" title="Stok Kritis!">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01"></path></svg>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Produk Info & Alert Pill -->
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    <div class="flex items-center flex-wrap gap-1.5">
                                        <span class="font-extrabold text-sm text-gray-900 block leading-tight">
                                            {{ $item->nama_produk }}
                                        </span>
                                        @if(!$isAktif)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-500 text-[10px] font-bold border border-gray-200">
                                                Tidak Dijual di Cabang Ini
                                            </span>
                                        @elseif($isZero)
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-rose-600 text-white text-[10px] font-black shadow-xs tracking-wider">
                                                <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                <span>HABIS</span>
                                            </span>
                                        @elseif($isCrit)
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] font-black shadow-xs tracking-wider">
                                                <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                <span>KRITIS (SISA {{ $qty }})</span>
                                            </span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-[11px] text-gray-400 block">
                                        {{ $item->barcode ?? 'Tanpa Barcode' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-bold text-[11px] border border-blue-200">
                                    {{ $item->kategori?->nama_kategori ?? 'Umum' }}
                                </span>
                            </td>

                            <!-- Harga Jual -->
                            <td class="py-4 px-4 text-right">
                                <span class="font-extrabold text-gray-900 block">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</span>
                            </td>

                            <!-- Status Badge dengan Icon Alert yang Jelas -->
                            <td class="py-4 px-4 text-center">
                                @if(!$isAktif)
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        <span>Tidak Aktif Dijual</span>
                                    </span>
                                @elseif($qty > $minStok)
                                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Tersedia Aman</span>
                                    </span>
                                @elseif($qty > 0)
                                    <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                        <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span>Menipis ({{ $qty }})</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-rose-100 text-rose-900 border border-rose-300 shadow-2xs">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span>Stok Kosong (0)</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Sisa Stok -->
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-base font-black px-2.5 py-0.5 rounded-lg {{ !$isAktif ? 'text-gray-500 bg-gray-100' : ($qty > $minStok ? 'text-gray-900 bg-gray-100' : ($qty > 0 ? 'text-amber-950 bg-amber-200/70 border border-amber-300' : 'text-rose-950 bg-rose-200/70 border border-rose-300')) }}">
                                        {{ $qty }}
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-400 mt-0.5">Unit Fisik</span>
                                </div>
                            </td>

                            <!-- Tombol Penyesuaian -->
                            <td class="py-4 px-5 text-right">
                                <button wire:click="openAdjustModal({{ $item->id }})" 
                                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-[#0052FF] text-[#0052FF] hover:text-white font-bold text-xs transition-all shadow-xs cursor-pointer group">
                                    <svg class="w-3.5 h-3.5 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                    <span>Penyesuaian</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    <p class="font-bold text-gray-600">Tidak ada data stok ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Silakan tambahkan produk baru di katalog master atau sesuaikan filter.</p>
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
                :has-more="$hasMore ?? false" 
                target="loadMore" 
                label="produk" 
                :current="count($stokItems)" 
                :total="$totalItems ?? count($stokItems)" 
                color="blue" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL PENYESUAIAN STOK (STOCK OPNAME)          -->
    <!-- ============================================== -->
    @if($modalAdjustOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="$set('modalAdjustOpen', false)"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-[#0E1726] to-[#0052FF] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">Penyesuaian Stok (Opname)</h3>
                                <p class="text-xs text-blue-200">Outlet: {{ $cabang?->nama_cabang }}</p>
                            </div>
                        </div>
                        <button wire:click="$set('modalAdjustOpen', false)" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Form -->
                    <form wire:submit.prevent="saveAdjustment">
                        <div class="p-6 space-y-4">
                            <!-- Info Produk dengan Foto -->
                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-200 flex items-center space-x-3">
                                @if($selectedProdukFoto && file_exists(public_path($selectedProdukFoto)))
                                    <img src="{{ asset($selectedProdukFoto) }}" 
                                         alt="{{ $selectedProdukName }}" 
                                         class="w-12 h-12 rounded-xl object-cover border border-gray-200 shadow-xs shrink-0">
                                @elseif($selectedProdukFoto && (str_starts_with($selectedProdukFoto, 'http') || str_starts_with($selectedProdukFoto, 'storage/') || str_starts_with($selectedProdukFoto, 'images/')))
                                    <img src="{{ asset($selectedProdukFoto) }}" 
                                         alt="{{ $selectedProdukName }}" 
                                         class="w-12 h-12 rounded-xl object-cover border border-gray-200 shadow-xs shrink-0"
                                         onerror="this.onerror=null;this.src='{{ asset('images/logo_bulat.jpg') }}';">
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0052FF] border border-blue-100 flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <span class="text-xs font-extrabold text-gray-900 block truncate">{{ $selectedProdukName }}</span>
                                    <span class="text-[11px] font-mono text-gray-500 block mt-0.5">Barcode: {{ $selectedProdukBarcode }}</span>
                                </div>
                            </div>

                            <!-- Perbandingan Stok Saat Ini vs Baru -->
                            <div class="grid grid-cols-2 gap-3 text-center">
                                <div class="bg-gray-100/80 p-3 rounded-2xl">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase block">Stok Tercatat</span>
                                    <span class="text-2xl font-extrabold text-gray-700 mt-1 block">{{ $currentStock }}</span>
                                </div>
                                <div class="bg-blue-50/80 p-3 rounded-2xl border border-blue-200">
                                    <span class="text-[10px] font-bold text-blue-600 uppercase block">Stok Fisik Baru</span>
                                    <span class="text-2xl font-black text-[#0052FF] mt-1 block">{{ $newStock }}</span>
                                </div>
                            </div>

                            <!-- Input Jumlah Baru & Quick Stepper -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Input Jumlah Fisik Sebenarnya <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" 
                                       wire:model.live="newStock"
                                       min="0"
                                       {{ !$isAktif ? 'disabled' : '' }}
                                       class="w-full text-center text-lg font-black py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#0052FF] focus:border-[#0052FF] {{ !$isAktif ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                                @error('newStock') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror

                                <!-- Quick Increment Buttons -->
                                <div class="flex items-center justify-center space-x-1.5 mt-2">
                                    <button type="button" wire:click="quickDelta(-5)" {{ !$isAktif ? 'disabled' : '' }} class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs {{ !$isAktif ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}">-5</button>
                                    <button type="button" wire:click="quickDelta(-1)" {{ !$isAktif ? 'disabled' : '' }} class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs {{ !$isAktif ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}">-1</button>
                                    <button type="button" wire:click="quickDelta(1)" {{ !$isAktif ? 'disabled' : '' }} class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs {{ !$isAktif ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}">+1</button>
                                    <button type="button" wire:click="quickDelta(5)" {{ !$isAktif ? 'disabled' : '' }} class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs {{ !$isAktif ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}">+5</button>
                                    <button type="button" wire:click="quickDelta(10)" {{ !$isAktif ? 'disabled' : '' }} class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs {{ !$isAktif ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}">+10</button>
                                </div>
                            </div>

                            <!-- Batas Minimum Stok & Status Alokasi Cabang -->
                            <div class="grid grid-cols-2 gap-3 pt-1 border-t border-gray-100">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Batas Min. Stok (Alert) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number" 
                                           wire:model="minStok"
                                           min="0"
                                           {{ !$isAktif ? 'disabled' : '' }}
                                           class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#0052FF] {{ !$isAktif ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }} @error('minStok') border-rose-500 @enderror">
                                    <span class="text-[10px] text-gray-400 block mt-0.5">Notif bunyi jika stok ≤ nilai ini</span>
                                    @error('minStok') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Status di Cabang Ini
                                    </label>
                                    <label class="flex items-center space-x-2 p-2 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer mt-0.5">
                                        <input type="checkbox" wire:model.live="isAktif" class="rounded text-[#0052FF] focus:ring-[#0052FF]">
                                        <span class="text-xs font-bold text-gray-800">Aktif Dijual</span>
                                    </label>
                                    <span class="text-[10px] text-gray-400 block mt-0.5">Bila nonaktif, 0 notifikasi</span>
                                </div>
                            </div>

                            <!-- Catatan Penyesuaian -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Alasan / Keterangan Penyesuaian <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       wire:model="catatan"
                                       placeholder="Contoh: Hasil Stock Opname Mingguan / Suplai Masuk"
                                       class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#0052FF] focus:border-[#0052FF] @error('catatan') border-rose-500 @enderror">
                                @error('catatan') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl">
                            <button type="button" 
                                    wire:click="$set('modalAdjustOpen', false)"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 disabled:opacity-50 text-xs font-bold text-white shadow-lg shadow-blue-900/30 transition-all cursor-pointer inline-flex items-center space-x-2">
                                <svg wire:loading wire:target="saveAdjust" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Simpan Stok Baru</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

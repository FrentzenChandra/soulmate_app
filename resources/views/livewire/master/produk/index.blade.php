<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>{{ $isHQ ? 'Kantor Pusat' : ($isOwner ? ($activeCabang?->nama_cabang ?? 'Cabang') : 'Soulmate Komputer') }}</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Katalog Master Produk</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Katalog Produk & Sparepart</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                @if($isHQ)
                    Database produk global, harga modal, harga jual, dan distribusi stok seluruh cabang.
                @elseif($isOwner)
                    Database produk & ketersediaan stok fisik di <strong>{{ $activeCabang?->nama_cabang }}</strong>.
                @else
                    Database katalog produk & ketersediaan stok toko.
                @endif
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if($isHQ)
                <button wire:click="openKategoriModal"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs border border-gray-200 shadow-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <span>Kelola Kategori ({{ $kategoris->count() }})</span>
                </button>
            @endif

            <button wire:click="openCreateModal"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Produk Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full lg:w-96">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-gray-50/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all">
        </div>

        <!-- Filter Selects -->
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <!-- Filter Kategori -->
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Kategori:</span>
                <select wire:model.live="kategoriFilter"
                        class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-purple-400">
                    <option value="semua">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kategori }} ({{ $k->produks_count }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Stok -->
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Stok:</span>
                <select wire:model.live="stokFilter"
                        class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-purple-400">
                    <option value="semua">Semua Status</option>
                    <option value="tersedia">Stok Tersedia (&gt; 3)</option>
                    <option value="kritis">Stok Kritis (&le; 3)</option>
                    <option value="habis">Stok Habis (0)</option>
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
                        <th class="py-3.5 px-4 w-14 text-center font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Foto</th>
                        <x-th-sort field="nama_produk" :sortField="$sortField" :sortDirection="$sortDirection">Info Produk & SKU</x-th-sort>
                        <x-th-sort field="kategori_id" :sortField="$sortField" :sortDirection="$sortDirection">Kategori</x-th-sort>
                        <x-th-sort field="harga_beli" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Harga Modal</x-th-sort>
                        <x-th-sort field="harga_jual" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Harga Jual</x-th-sort>
                        @if($isHQ)
                            @foreach($cabangs as $c)
                                <th class="py-3.5 px-3 text-center font-extrabold text-[10px] tracking-wider uppercase text-gray-400">{{ $c->kode_cabang }}</th>
                            @endforeach
                            <x-th-sort field="stok_total" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Total Global</x-th-sort>
                        @else
                            <th class="py-3.5 px-4 text-center font-extrabold text-[10px] tracking-wider uppercase text-gray-400">{{ $isOwner ? ('Stok ' . ($activeCabang?->nama_cabang ?? 'Cabang')) : 'Stok Toko' }}</th>
                        @endif
                        <th class="py-3.5 px-5 text-right font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <x-table-skeleton :columns="$isHQ ? (6 + $cabangs->count()) : 7" :rows="6" target="search, kategoriFilter, statusFilter, sortBy, sortDir" />
                <tbody wire:loading.remove wire:target="search, kategoriFilter, statusFilter, sortBy, sortDir" class="divide-y divide-gray-100">
                    @forelse($produks as $p)
                        @php
                            $totalStok = $p->stoks->sum('jumlah');
                            $margin = $p->harga_beli > 0 ? (($p->harga_jual - $p->harga_beli) / $p->harga_beli) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Foto Thumbnail -->
                            <td class="py-4 px-4 text-center">
                                @if($p->foto_url && file_exists(public_path($p->foto_url)))
                                    <img src="{{ asset($p->foto_url) }}"
                                         alt="{{ $p->nama_produk }}"
                                         class="w-11 h-11 rounded-xl object-cover border border-gray-200 shadow-xs mx-auto">
                                @elseif($p->foto_url && (str_starts_with($p->foto_url, 'http') || str_starts_with($p->foto_url, 'storage/') || str_starts_with($p->foto_url, 'images/')))
                                    <img src="{{ asset($p->foto_url) }}"
                                         alt="{{ $p->nama_produk }}"
                                         class="w-11 h-11 rounded-xl object-cover border border-gray-200 shadow-xs mx-auto"
                                         onerror="this.onerror=null;this.src='{{ asset('images/logo_bulat.jpg') }}';">
                                @else
                                    <div class="w-11 h-11 rounded-xl bg-purple-50 border border-purple-100 text-[#5E17EB] flex items-center justify-center mx-auto shadow-2xs">
                                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                            </td>

                            <!-- Produk Info & Barcode -->
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    <span class="font-extrabold text-sm text-gray-900 block leading-tight">
                                        {{ $p->nama_produk }}
                                    </span>
                                    <div class="flex items-center space-x-2">
                                        @if($p->barcode)
                                            <span class="font-mono text-[11px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md font-semibold border border-gray-200/80 inline-flex items-center space-x-1">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                                <span>{{ $p->barcode }}</span>
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">Tanpa barcode</span>
                                        @endif

                                        @if($p->ready_marketplace)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Online Store</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 font-bold text-[11px] border border-purple-200">
                                    {{ $p->kategori?->nama_kategori ?? 'Umum' }}
                                </span>
                            </td>

                            <!-- Harga Modal -->
                            <td class="py-4 px-4 text-right">
                                <span class="font-medium text-gray-600 block">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</span>
                            </td>

                            <!-- Harga Jual & Margin -->
                            <td class="py-4 px-4 text-right">
                                <span class="font-extrabold text-gray-900 block">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-emerald-600 font-bold">+{{ number_format($margin, 0) }}% margin</span>
                            </td>

                            <!-- Stok Breakdown -->
                            @if($isHQ)
                                @foreach($cabangs as $c)
                                    @php
                                        $stokCab = $p->stoks->firstWhere('cabang_id', $c->id)?->jumlah ?? 0;
                                    @endphp
                                    <td class="py-4 px-3 text-center">
                                        <span class="inline-block px-2 py-0.5 rounded-lg text-xs font-extrabold {{ $stokCab > 3 ? 'bg-gray-100 text-gray-800' : ($stokCab > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-700') }}">
                                            {{ $stokCab }}
                                        </span>
                                    </td>
                                @endforeach
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black {{ $totalStok > 3 ? 'bg-purple-100 text-[#5E17EB]' : ($totalStok > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $totalStok }} Unit
                                    </span>
                                </td>
                            @else
                                @php
                                    $targetCabangId = $activeCabang?->id ?? 1;
                                    $stokCab = $p->stoks->firstWhere('cabang_id', $targetCabangId)?->jumlah ?? 0;
                                @endphp
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-extrabold {{ $stokCab > 3 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($stokCab > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $stokCab > 3 ? 'bg-emerald-500' : ($stokCab > 0 ? 'bg-amber-500 animate-pulse' : 'bg-rose-500') }}"></span>
                                        <span>{{ $stokCab }} Unit</span>
                                    </span>
                                </td>
                            @endif

                            <!-- Aksi -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end space-x-1">
                                    <button wire:click="openEditModal({{ $p->id }})"
                                            class="p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Edit Produk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    @if($isHQ)
                                        <button wire:click="openDeleteConfirmation({{ $p->id }})"
                                                class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                title="Hapus Produk Master">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @else
                                        <button wire:click="openDeleteConfirmation({{ $p->id }})"
                                                class="p-2 rounded-xl text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer"
                                                title="Nonaktifkan Produk di Toko">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? (6 + $cabangs->count()) : 7 }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    <p class="font-bold text-gray-600">Tidak ada produk ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau tambah produk baru.</p>
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
                :has-more="$hasMore" 
                target="loadMore" 
                label="produk" 
                :current="$produks->count()" 
                :total="$totalProduk" 
                color="purple" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL FORM PRODUK (TAMBAH / EDIT)              -->
    <!-- ============================================== -->
    @if($modalProdukOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="$set('modalProdukOpen', false)"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-[#0E1726] to-[#5E17EB] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">
                                    {{ $isEditMode ? 'Edit Data Produk' : 'Tambah Produk Baru' }}
                                </h3>
                                <p class="text-xs text-purple-200">
                                    {{ $isHQ ? 'Katalog global untuk seluruh cabang toko' : ($isOwner ? ('Penugasan produk & stok untuk ' . ($activeCabang?->nama_cabang ?? 'Cabang')) : 'Pengaturan katalog & stok produk toko') }}
                                </p>
                            </div>
                        </div>
                        <button wire:click="$set('modalProdukOpen', false)" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="saveProduk">
                        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">

                            <!-- Upload Foto Produk & Live Preview -->
                            <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/80">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Foto Produk
                                    </label>
                                    <span class="text-[10px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-100">
                                        Kompresi WebP Otomatis (&le;40 KB)
                                    </span>
                                </div>

                                <!-- Loading State Saat File Diupload (Centered & Responsive) -->
                                <x-photo-upload-loading target="foto" color="purple" />

                                <div wire:loading.remove wire:target="foto">
                                    @if($foto)
                                        <!-- PREVIEW FOTO BARU SEBELUM SIMPAN -->
                                        <div class="p-3.5 bg-white rounded-2xl border-2 border-[#5E17EB]/30 shadow-xs flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                                            <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-gray-100 border border-purple-200 shrink-0 shadow-inner group">
                                                <img src="{{ $foto->temporaryUrl() }}" alt="Preview Foto Baru" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <span class="text-[10px] font-black text-white px-2 py-0.5 bg-[#5E17EB]/80 rounded-full">Preview</span>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        Foto Baru Terpilih
                                                    </span>
                                                    <span class="text-[10px] text-gray-400 font-mono">
                                                        {{ round($foto->getSize() / 1024, 1) }} KB
                                                    </span>
                                                </div>
                                                <p class="text-xs font-bold text-gray-900 truncate mt-1">
                                                    {{ $foto->getClientOriginalName() }}
                                                </p>
                                                <p class="text-[10px] text-gray-500 mt-0.5">
                                                    Foto ini siap disimpan dan akan otomatis dikonversi ke format WebP ringan saat Anda menekan tombol <strong class="text-[#5E17EB]">Simpan Produk</strong>.
                                                </p>
                                                <div class="mt-2.5 flex items-center space-x-2">
                                                    <button type="button"
                                                            wire:click="cancelUploadFoto"
                                                            class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        <span>Batal / Hapus Pilihan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($existing_foto_url)
                                        <!-- PREVIEW FOTO YANG SUDAH TERSIMPAN SEBELUMNYA -->
                                        <div class="p-3 bg-white rounded-2xl border border-gray-200 flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                                            <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gray-100 border border-gray-200 shrink-0">
                                                <img src="{{ asset($existing_foto_url) }}"
                                                     onerror="this.src='{{ asset('images/logo_bulat.jpg') }}';"
                                                     alt="Foto Saat Ini"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                                    Foto Produk Aktif
                                                </span>
                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <label class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-[#5E17EB] bg-purple-50 hover:bg-purple-100 border border-purple-200 transition-colors cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                        <span>Ganti Foto Baru</span>
                                                        <input type="file" wire:model="foto" accept="image/*" class="hidden">
                                                    </label>
                                                    <button type="button"
                                                            wire:click="removeExistingFoto"
                                                            class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        <span>Hapus Foto</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    @else
                                        <!-- PILIH FOTO (BELUM ADA FOTO) -->
                                        <div class="flex items-center space-x-4">
                                            <div class="w-14 h-14 rounded-2xl bg-white border-2 border-dashed border-gray-300 flex items-center justify-center shrink-0 text-gray-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <div class="flex-1">
                                                <input type="file"
                                                       wire:model="foto"
                                                       accept="image/*"
                                                       class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#5E17EB]/10 file:text-[#5E17EB] hover:file:bg-[#5E17EB]/20 cursor-pointer">
                                                <p class="text-[10px] text-gray-400 mt-1">Pilih gambar JPG, PNG, atau WEBP (Maksimal 4MB).</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @error('foto')
                                    <span class="text-[11px] text-rose-500 font-semibold block mt-2">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Nama Produk -->
                            <div class="relative">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Produk / Sparepart <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       wire:model.live.debounce.300ms="nama_produk"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('nama_produk') border-rose-500 @enderror">
                                @error('nama_produk') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror

                                <!-- Interactive Suggestion Dropdown: Product Exists Search List -->
                                @if(!$isEditMode && !empty($duplicateMasterProducts) && count($duplicateMasterProducts) > 0)
                                    <div class="mt-2.5 p-3.5 rounded-2xl bg-purple-50/70 border-2 border-purple-200/80 shadow-md space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-600 text-white shadow-2xs">
                                                    Product Exists ({{ count($duplicateMasterProducts) }})
                                                </span>
                                                <span class="text-xs font-bold text-purple-950">Produk Serupa Ditemukan di Katalog</span>
                                            </div>
                                            <span class="text-[10px] text-purple-600 font-medium">Klik produk untuk langsung tambah/atur stok</span>
                                        </div>

                                        <div class="divide-y divide-purple-100 bg-white rounded-xl border border-purple-100 overflow-hidden shadow-2xs">
                                            @foreach($duplicateMasterProducts as $dup)
                                                @php
                                                    $dupStok = $dup->stoks->firstWhere('cabang_id', $activeCabang?->id);
                                                    $isAktifInBranch = $dupStok && $dupStok->is_aktif;
                                                @endphp
                                                <div class="p-3 hover:bg-purple-50/50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 group">
                                                    <div class="flex items-center space-x-3 min-w-0">
                                                        <div class="w-11 h-11 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden shrink-0 flex items-center justify-center">
                                                            @if($dup->foto_url)
                                                                <img src="{{ $dup->foto_url }}" alt="{{ $dup->nama_produk }}" class="w-full h-full object-cover">
                                                            @else
                                                                <span class="text-xs font-black text-gray-400 uppercase">{{ substr($dup->nama_produk, 0, 2) }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div class="flex items-center space-x-1.5 flex-wrap">
                                                                <h5 class="text-xs font-extrabold text-gray-900 group-hover:text-[#5E17EB] transition-colors truncate">
                                                                    {{ $dup->nama_produk }}
                                                                </h5>
                                                                @if($dup->kategori)
                                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                                        {{ $dup->kategori->nama_kategori }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center space-x-3 text-[11px] text-gray-500 mt-0.5">
                                                                <span class="font-mono">Barcode: {{ $dup->barcode ?: '-' }}</span>
                                                                <span>·</span>
                                                                <span class="font-bold text-[#5E17EB]">Rp {{ number_format($dup->harga_jual, 0, ',', '.') }}</span>
                                                                <span>·</span>
                                                                <span class="font-semibold {{ $isAktifInBranch ? 'text-emerald-700' : 'text-amber-600' }}">
                                                                    {{ $isOwner ? ($activeCabang?->nama_cabang ?? 'Cabang') : 'Status' }}: {{ $isAktifInBranch ? 'Stok ' . $dupStok->jumlah : 'Belum Aktif' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            wire:click="selectExistingProduct({{ $dup->id }})"
                                                            class="px-3.5 py-2 rounded-xl bg-[#5E17EB] hover:bg-[#4E10C7] text-white text-xs font-extrabold shadow-sm transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center space-x-1.5">
                                                        <span>Pilih & Atur Stok</span>
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Kategori -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Kategori Produk
                                    </label>
                                    <select wire:model="kategori_id"
                                            class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                        <option value="">-- Tanpa Kategori (Umum) --</option>
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Barcode / SKU -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Barcode / SKU Toko
                                    </label>
                                    <div class="flex space-x-2">
                                        <input type="text"
                                               wire:model.live.debounce.350ms="barcode"
                                               class="w-full text-xs font-mono px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('barcode') border-rose-500 @enderror">
                                        <button type="button"
                                                wire:click="generateBarcode"
                                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-colors shrink-0 cursor-pointer"
                                                title="Generate Barcode Otomatis">
                                            Acak
                                        </button>
                                    </div>
                                    @error('barcode') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Harga Beli & Harga Jual -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Harga Modal (Beli) <span class="text-rose-500">*</span>
                                    </label>
                                    <x-input-rupiah wire:model.live="harga_beli" />
                                    @error('harga_beli') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Harga Jual Toko (POS) <span class="text-rose-500">*</span>
                                    </label>
                                    <x-input-rupiah wire:model.live="harga_jual" />
                                    @error('harga_jual') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                            <!-- Harga & Margin Profit Live Calculator -->
                            <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/80 space-y-4">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Harga & Margin Keuntungan</h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Harga Beli / Modal -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                            Harga Beli / Modal (Rp) <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-extrabold text-gray-400">Rp</span>
                                            <input type="number" 
                                                   wire:model.live.debounce.300ms="harga_beli" 
                                                   min="0"
                                                   class="w-full pl-9 pr-3 py-2 text-xs font-extrabold rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('harga_beli') border-rose-500 @enderror">
                                        </div>
                                        @error('harga_beli') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Harga Jual Toko -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                            Harga Jual Toko / POS (Rp) <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-extrabold text-gray-400">Rp</span>
                                            <input type="number" 
                                                   wire:model.live.debounce.300ms="harga_jual" 
                                                   min="0"
                                                   class="w-full pl-9 pr-3 py-2 text-xs font-extrabold rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('harga_jual') border-rose-500 @enderror">
                                        </div>
                                        @error('harga_jual') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Live Profit Margin Indicator -->
                                @php
                                    $hb = (float)$harga_beli;
                                    $hj = (float)$harga_jual;
                                    $laba = $hj - $hb;
                                    $calcMargin = $hb > 0 ? ($laba / $hb) * 100 : 0;
                                @endphp
                                <div class="p-3 rounded-xl {{ $laba >= 0 ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-rose-50 border border-rose-200 text-rose-900' }} flex items-center justify-between text-xs font-bold">
                                    <span class="flex items-center space-x-1.5">
                                        <span>Estimasi Laba per Unit:</span>
                                    </span>
                                    <span class="font-extrabold text-emerald-600">Rp {{ number_format(max(0, $laba), 0, ',', '.') }} ({{ number_format($calcMargin, 1) }}%)</span>
                                </div>
                            </div>

                            <!-- Alokasi Stok Fisik (Dinamis: Sesuai Mode HQ vs Mode Cabang) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    {{ $isHQ ? 'Alokasi & Manajemen Stok per Cabang' : ($isOwner ? ('Stok di ' . ($activeCabang?->nama_cabang ?? 'Cabang Ini')) : 'Manajemen Stok Produk') }}
                                </label>

                                @if($isHQ)
                                    <!-- Grid Semua Cabang Khusus HQ -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        @foreach($cabangs as $c)
                                            <div class="p-3 rounded-xl border {{ !empty($isAktifStock[$c->id]) ? 'bg-purple-50/40 border-purple-200' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                                                <div class="flex items-center justify-between mb-2">
                                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                                        <input type="checkbox"
                                                               wire:model.live="isAktifStock.{{ $c->id }}"
                                                               class="rounded text-[#5E17EB] focus:ring-[#5E17EB] h-3.5 w-3.5 border-gray-300">
                                                        <span class="text-xs font-extrabold text-gray-800">{{ $c->nama_cabang }}</span>
                                                    </label>
                                                    <span class="text-[10px] font-bold text-purple-600 uppercase">{{ $c->kode_cabang }}</span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Stok Fisik</label>
                                                        <input type="number"
                                                               wire:model="initialStock.{{ $c->id }}"
                                                               min="0"
                                                               {{ empty($isAktifStock[$c->id]) ? 'disabled' : '' }}
                                                               class="w-full text-xs font-extrabold px-2.5 py-1.5 rounded-lg border {{ empty($isAktifStock[$c->id]) ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' : 'border-gray-300 bg-white focus:ring-2 focus:ring-[#5E17EB]' }}">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-amber-600 mb-0.5">Min Alert</label>
                                                        <input type="number"
                                                               wire:model="minStock.{{ $c->id }}"
                                                               min="1"
                                                               {{ empty($isAktifStock[$c->id]) ? 'disabled' : '' }}
                                                               class="w-full text-xs font-extrabold px-2.5 py-1.5 rounded-lg border {{ empty($isAktifStock[$c->id]) ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' : 'border-amber-300 bg-white focus:ring-2 focus:ring-amber-500' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- Input Tunggal Khusus Cabang Ini Saja -->
                                    @if($activeCabang)
                                        <div class="bg-blue-50/60 p-3.5 rounded-2xl border border-blue-200 max-w-md">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="flex items-center gap-1.5 cursor-pointer">
                                                    <input type="checkbox"
                                                           wire:model.live="isAktifStock.{{ $activeCabang->id }}"
                                                           class="rounded text-[#0052FF] focus:ring-[#0052FF] h-4 w-4 border-blue-300">
                                                    <span class="text-xs font-extrabold text-gray-900">{{ $isOwner ? ($activeCabang->nama_cabang . ' (Aktif Dijual)') : 'Status Penjualan (Aktif)' }}</span>
                                                </label>
                                                @if($isOwner)
                                                    <span class="text-[10px] font-bold text-blue-600 uppercase">{{ $activeCabang->kode_cabang }}</span>
                                                @endif
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Stok Fisik</label>
                                                    <input type="number"
                                                           wire:model="initialStock.{{ $activeCabang->id }}"
                                                           min="0"
                                                           {{ empty($isAktifStock[$activeCabang->id]) ? 'disabled' : '' }}
                                                           class="w-full text-sm font-extrabold px-3 py-1.5 rounded-xl border {{ empty($isAktifStock[$activeCabang->id]) ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' : 'border-blue-300 bg-white focus:ring-2 focus:ring-[#0052FF]' }}">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-amber-600 mb-0.5">Min Alert Threshold</label>
                                                    <input type="number"
                                                           wire:model="minStock.{{ $activeCabang->id }}"
                                                           min="1"
                                                           {{ empty($isAktifStock[$activeCabang->id]) ? 'disabled' : '' }}
                                                           class="w-full text-sm font-extrabold px-3 py-1.5 rounded-xl border {{ empty($isAktifStock[$activeCabang->id]) ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' : 'border-amber-300 bg-white focus:ring-2 focus:ring-amber-500' }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <!-- Deskripsi & Marketplace Toggle -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Deskripsi & Spesifikasi Singkat
                                </label>
                                <textarea wire:model="deskripsi"
                                          rows="2"
                                          class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]"></textarea>
                            </div>

                            <div class="flex items-center space-x-2 pt-1">
                                <input type="checkbox"
                                       id="ready_marketplace"
                                       wire:model="ready_marketplace"
                                       class="w-4 h-4 rounded text-[#5E17EB] focus:ring-[#5E17EB] border-gray-300 cursor-pointer">
                                <label for="ready_marketplace" class="text-xs font-bold text-gray-700 cursor-pointer">
                                    Tampilkan di Website Marketplace / Katalog Publik
                                </label>
                            </div>

                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl">
                            <button type="button"
                                    wire:click="$set('modalProdukOpen', false)"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-xs font-bold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer inline-flex items-center space-x-2">
                                <svg wire:loading wire:target="saveProduk, foto" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Produk Baru' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL MANAJEMEN KATEGORI PRODUK               -->
    <!-- ============================================== -->
    @if($modalKategoriOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="$set('modalKategoriOpen', false)"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-[#0E1726] to-[#5E17EB] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">Manajemen Kategori</h3>
                                <p class="text-xs text-purple-200">Kelompokkan produk dan suku cadang servis</p>
                            </div>
                        </div>
                        <button wire:click="$set('modalKategoriOpen', false)" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- Input Tambah Kategori Baru -->
                        <div class="bg-purple-50/60 p-4 rounded-2xl border border-purple-100">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Tambah Kategori Baru
                            </label>
                            <div class="flex space-x-2">
                                <input type="text"
                                       wire:model="new_kategori_name"
                                       wire:keydown.enter="saveNewKategori"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#5E17EB] @error('new_kategori_name') border-rose-500 @enderror">
                                <button type="button"
                                        wire:click="saveNewKategori"
                                        class="px-4 py-2.5 bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs rounded-xl transition-all shrink-0 cursor-pointer">
                                    Tambah
                                </button>
                            </div>
                            @error('new_kategori_name') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Daftar Kategori yang Ada -->
                        <div>
                            <span class="text-xs font-extrabold text-gray-400 uppercase tracking-wider block mb-2">Daftar Kategori Terdaftar</span>
                            <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto custom-scrollbar border border-gray-100 rounded-2xl">
                                @forelse($kategoris as $k)
                                    <div class="p-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                        @if($edit_kategori_id === $k->id)
                                            <div class="flex items-center space-x-2 w-full">
                                                <input type="text"
                                                       wire:model="edit_kategori_name"
                                                       wire:keydown.enter="saveEditKategori"
                                                       class="w-full text-xs font-bold px-2.5 py-1.5 rounded-lg border border-purple-300">
                                                <button wire:click="saveEditKategori" class="px-2.5 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold cursor-pointer">Simpan</button>
                                                <button wire:click="cancelEditKategori" class="px-2.5 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs font-bold cursor-pointer">Batal</button>
                                            </div>
                                        @else
                                            <div class="flex items-center space-x-2">
                                                <span class="font-bold text-xs text-gray-900">{{ $k->nama_kategori }}</span>
                                                <span class="text-[10px] text-gray-400">({{ $k->produks_count }} produk)</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <button wire:click="startEditKategori({{ $k->id }})" class="p-1 text-gray-400 hover:text-purple-600 cursor-pointer" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <button wire:click="deleteKategori({{ $k->id }})"
                                                        wire:confirm="Yakin ingin menghapus kategori '{{ $k->nama_kategori }}'?"
                                                        class="p-1 text-gray-400 hover:text-rose-600 cursor-pointer" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-gray-400">
                                        Belum ada kategori terdaftar.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end rounded-b-3xl">
                        <button type="button"
                                wire:click="$set('modalKategoriOpen', false)"
                                class="px-4 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-xs font-bold text-gray-800 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI HAPUS / NONAKTIFKAN PRODUK    -->
    <!-- ============================================== -->
    @if($modalDeleteOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="$set('modalDeleteOpen', false)"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                    
                    <!-- Header Modal with Icon & Context -->
                    <div class="p-6 pb-4">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-2xl {{ $deleteIsHQ ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }} flex items-center justify-center shrink-0 shadow-xs">
                                @if($deleteIsHQ)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-lg font-black text-gray-900 leading-tight">
                                        {{ $deleteIsHQ ? 'Hapus Produk Master' : 'Nonaktifkan Produk' }}
                                    </h3>
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $deleteIsHQ ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ $deleteIsHQ ? 'Konteks HQ' : 'Toko Ini' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $deleteIsHQ ? 'Konfirmasi penghapusan data produk dari basis data master global.' : 'Konfirmasi status ketersediaan dan penjualan produk di cabang ini.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Product Snapshot Card -->
                    <div class="px-6 py-3">
                        <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-200/90 flex items-center space-x-3.5">
                            @if($deleteProdukFoto && file_exists(public_path($deleteProdukFoto)))
                                <img src="{{ asset($deleteProdukFoto) }}" alt="{{ $deleteProdukNama }}" class="w-13 h-13 rounded-xl object-cover border border-gray-200 shadow-xs shrink-0">
                            @elseif($deleteProdukFoto && (str_starts_with($deleteProdukFoto, 'http') || str_starts_with($deleteProdukFoto, 'storage/') || str_starts_with($deleteProdukFoto, 'images/')))
                                <img src="{{ asset($deleteProdukFoto) }}" alt="{{ $deleteProdukNama }}" class="w-13 h-13 rounded-xl object-cover border border-gray-200 shadow-xs shrink-0" onerror="this.onerror=null;this.src='{{ asset('images/logo_bulat.jpg') }}';">
                            @else
                                <div class="w-13 h-13 rounded-xl bg-purple-50 text-[#5E17EB] border border-purple-100 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-extrabold text-gray-900 truncate leading-snug">{{ $deleteProdukNama }}</h4>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="font-mono text-[11px] text-gray-500 bg-white px-1.5 py-0.5 rounded border border-gray-200">{{ $deleteProdukBarcode ?: '-' }}</span>
                                    <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">{{ $deleteProdukKategori }}</span>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="text-[10px] font-bold text-gray-400 block uppercase">Sisa Stok</span>
                                <span class="text-sm font-black text-gray-900 block mt-0.5">{{ $deleteProdukStok }} Unit</span>
                            </div>
                        </div>
                    </div>

                    <!-- Informational Impact Alert -->
                    <div class="px-6 py-2">
                        @if($deleteIsHQ && $deleteProdukHasHistory)
                            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
                                <div class="flex items-start space-x-2.5">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <div>
                                        <p class="font-extrabold">Proteksi Transaksi Aktif</p>
                                        <p class="text-[11px] text-rose-700 mt-0.5 leading-relaxed">
                                            Produk ini <strong>tidak dapat dihapus permanen</strong> karena sudah memiliki riwayat penjualan di kasir atau servis. Untuk menjaga integritas pembukuan, Anda dapat menonaktifkan status penjualannya.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif($deleteIsHQ)
                            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                <div class="flex items-start space-x-2.5">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <div>
                                        <p class="font-extrabold">Peringatan Penghapusan Master</p>
                                        <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                                            Produk akan dihapus dari katalog master global (Soft Delete). Produk tidak akan lagi dapat diakses atau dijual di seluruh cabang.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs">
                                <div class="flex items-start space-x-2.5">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="font-extrabold">Deaktivasi Status Penjualan</p>
                                        <p class="text-[11px] text-blue-700 mt-0.5 leading-relaxed">
                                            Produk ini akan disembunyikan dari POS Kasir dan tidak memicu notifikasi peringatan stok di cabang ini. Data master produk tetap tersimpan dan dapat diaktifkan kembali sewaktu-waktu.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl border-t border-gray-100 mt-4">
                        <button type="button"
                                wire:click="$set('modalDeleteOpen', false)"
                                class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>

                        @if($deleteIsHQ && $deleteProdukHasHistory)
                            <button type="button"
                                    wire:click="$set('modalDeleteOpen', false)"
                                    class="px-4 py-2.5 rounded-xl bg-gray-200 text-xs font-bold text-gray-600 cursor-pointer hover:bg-gray-300">
                                Mengerti
                            </button>
                        @elseif($deleteIsHQ)
                            <button type="button"
                                    wire:click="confirmDelete"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold text-white transition-all shadow-md shadow-rose-600/20 cursor-pointer disabled:opacity-50">
                                <svg wire:loading wire:target="confirmDelete" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span wire:loading.remove wire:target="confirmDelete">Ya, Hapus Produk</span>
                                <span wire:loading wire:target="confirmDelete">Menghapus...</span>
                            </button>
                        @else
                            <button type="button"
                                    wire:click="confirmDelete"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-xs font-bold text-white transition-all shadow-md shadow-amber-600/20 cursor-pointer disabled:opacity-50">
                                <svg wire:loading wire:target="confirmDelete" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span wire:loading.remove wire:target="confirmDelete">Ya, Nonaktifkan</span>
                                <span wire:loading wire:target="confirmDelete">Memproses...</span>
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>{{ $isHQ ? 'Kantor Pusat' : ($activeCabang?->nama_cabang ?? 'Cabang') }}</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Katalog Master Produk</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Katalog Produk & Sparepart</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                @if($isHQ)
                    Database produk global, harga modal, harga jual, dan distribusi stok seluruh cabang.
                @else
                    Database produk & ketersediaan stok fisik di <strong>{{ $activeCabang?->nama_cabang }}</strong>.
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
                   placeholder="Cari nama produk, barcode/SKU..." 
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
                            <th class="py-3.5 px-4 text-center font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Stok {{ $activeCabang?->nama_cabang ?? 'Cabang' }}</th>
                        @endif
                        <th class="py-3.5 px-5 text-right font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
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
                                @elseif($p->foto_url && (str_starts_with($p->foto_url, 'http') || str_starts_with($p->foto_url, 'images/')))
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
                                        <button wire:click="deleteProduk({{ $p->id }})"
                                                wire:confirm="PERINGATAN: Menghapus produk '{{ $p->nama_produk }}' akan menghapusnya dari seluruh cabang. Lanjutkan?"
                                                class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                title="Hapus Produk Master">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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

        <!-- Load More / Info -->
        @if($hasMore)
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500">Menampilkan {{ $produks->count() }} dari {{ $totalProduk }} produk</p>
                <button wire:click="loadMore" class="px-4 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold rounded-xl text-xs transition-colors">
                    Muat Lebih Banyak
                </button>
            </div>
        @endif
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
                                    {{ $isHQ ? 'Katalog global untuk seluruh cabang toko' : 'Penugasan produk & stok untuk ' . ($activeCabang?->nama_cabang ?? 'Cabang') }}
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

                                <!-- Loading State Saat File Diupload -->
                                <div wire:loading wire:target="foto" class="w-full p-4 mb-3 bg-purple-50/80 border border-purple-200 rounded-2xl flex items-center space-x-3 text-purple-700">
                                    <svg class="animate-spin h-5 w-5 text-[#5E17EB] shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Mengunggah & memproses preview foto...</p>
                                        <p class="text-[10px] text-purple-600">Mohon tunggu sebentar sebelum menyimpan</p>
                                    </div>
                                </div>

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
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Produk / Sparepart <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       wire:model="nama_produk"
                                       placeholder="Contoh: SSD NVMe Kingston NV2 512GB"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('nama_produk') border-rose-500 @enderror">
                                @error('nama_produk') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
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
                                               wire:model="barcode"
                                               placeholder="Contoh: 8991234560011"
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
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-gray-400">Rp</span>
                                        <input type="number"
                                               wire:model.live="harga_beli"
                                               class="w-full pl-10 pr-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                    </div>
                                    @error('harga_beli') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Harga Jual Toko (POS) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-gray-400">Rp</span>
                                        <input type="number"
                                               wire:model.live="harga_jual"
                                               class="w-full pl-10 pr-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                    </div>
                                    @error('harga_jual') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="sm:col-span-2 text-right text-xs">
                                    @php
                                        $calcMargin = $harga_beli > 0 ? (($harga_jual - $harga_beli) / $harga_beli) * 100 : 0;
                                        $laba = $harga_jual - $harga_beli;
                                    @endphp
                                    <span class="text-gray-500">Estimasi Laba Kotor: </span>
                                    <span class="font-extrabold text-emerald-600">Rp {{ number_format(max(0, $laba), 0, ',', '.') }} ({{ number_format($calcMargin, 1) }}%)</span>
                                </div>
                            </div>

                            <!-- Alokasi Stok Fisik (Dinamis: Sesuai Mode HQ vs Mode Cabang) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    {{ $isHQ ? 'Alokasi & Manajemen Stok per Cabang' : 'Stok di ' . ($activeCabang?->nama_cabang ?? 'Cabang Ini') }}
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
                                                               placeholder="0"
                                                               {{ empty($isAktifStock[$c->id]) ? 'disabled' : '' }}
                                                               class="w-full text-xs font-extrabold px-2.5 py-1.5 rounded-lg border {{ empty($isAktifStock[$c->id]) ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' : 'border-gray-300 bg-white focus:ring-2 focus:ring-[#5E17EB]' }}">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-amber-600 mb-0.5">Min Alert</label>
                                                        <input type="number"
                                                               wire:model="minStock.{{ $c->id }}"
                                                               min="1"
                                                               placeholder="3"
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
                                                    <span class="text-xs font-extrabold text-gray-900">{{ $activeCabang->nama_cabang }} (Aktif Dijual)</span>
                                                </label>
                                                <span class="text-[10px] font-bold text-blue-600 uppercase">{{ $activeCabang->kode_cabang }}</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Stok Fisik</label>
                                                    <input type="number"
                                                           wire:model="initialStock.{{ $activeCabang->id }}"
                                                           min="0"
                                                           placeholder="0"
                                                           {{ empty($isAktifStock[$activeCabang->id]) ? 'disabled' : '' }}
                                                           class="w-full text-sm font-extrabold px-3 py-1.5 rounded-xl border {{ empty($isAktifStock[$activeCabang->id]) ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' : 'border-blue-300 bg-white focus:ring-2 focus:ring-[#0052FF]' }}">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-amber-600 mb-0.5">Min Alert Threshold</label>
                                                    <input type="number"
                                                           wire:model="minStock.{{ $activeCabang->id }}"
                                                           min="1"
                                                           placeholder="3"
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
                                          placeholder="Catatan kompatibilitas, garansi, atau spesifikasi detail..."
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
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-bold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
                                {{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Produk Baru' }}
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
                                       placeholder="Nama kategori baru..."
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

</div>

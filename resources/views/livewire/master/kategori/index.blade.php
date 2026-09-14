<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>Katalog & Master</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Master Kategori</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Kategori Produk</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola kelompok jenis barang, suku cadang, dan aksesoris komputer untuk seluruh cabang.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('master.produk') }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Lihat Katalog Produk</span>
            </a>

            <button wire:click="openCreateModal"
                    class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Kategori Baru</span>
            </button>
        </div>
    </div>

    <!-- 3 Metrik Ringkasan Kategori -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Kategori -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Kategori</p>
                <h3 class="text-xl font-extrabold text-gray-900">{{ $totalKategori }} <span class="text-xs font-semibold text-gray-400">Grup</span></h3>
            </div>
        </div>

        <!-- Total Produk Terkategori -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0052FF] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Produk Terdaftar</p>
                <h3 class="text-xl font-extrabold text-gray-900">{{ $totalProduk }} <span class="text-xs font-semibold text-gray-400">Item</span></h3>
            </div>
        </div>

        <!-- Kategori Terbanyak -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Kategori Terbanyak</p>
                <h3 class="text-sm font-extrabold text-gray-900 truncate">{{ $topKategori?->nama_kategori ?? '-' }}</h3>
                <p class="text-[10px] text-emerald-600 font-bold">{{ $topKategori?->produks_count ?? 0 }} Produk</p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 flex items-center justify-between gap-4">
        <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama kategori atau slug..." 
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-gray-50/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all">
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5 w-12 text-center font-extrabold text-[10px] tracking-wider uppercase text-gray-400">No</th>
                        <x-th-sort field="nama_kategori" :sortField="$sortField" :sortDirection="$sortDirection" class="px-5">Nama Kategori & Slug</x-th-sort>
                        <x-th-sort field="produks_count" :sortField="$sortField" :sortDirection="$sortDirection" align="center" class="px-5">Jumlah Produk</x-th-sort>
                        <x-th-sort field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" class="px-5">Terakhir Diperbarui</x-th-sort>
                        <th class="py-3.5 px-5 text-right font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <x-table-skeleton :columns="5" :rows="5" target="search, sortField, sortDirection" />
                <tbody wire:loading.remove wire:target="search, sortField, sortDirection" class="divide-y divide-gray-100">
                    @forelse($kategoris as $k)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- No -->
                            <td class="py-4 px-5 text-center text-gray-400 font-mono text-[11px]">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Nama Kategori & Slug -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 block leading-tight">
                                            {{ $k->nama_kategori }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-mono">
                                            slug: {{ $k->slug ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Jumlah Produk Terkait -->
                            <td class="py-4 px-5 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black {{ $k->produks_count > 0 ? 'bg-purple-50 text-[#5E17EB] border border-purple-200' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $k->produks_count }} Produk
                                </span>
                            </td>

                            <!-- Tanggal Diperbarui -->
                            <td class="py-4 px-5 text-gray-500 text-xs">
                                {{ $k->updated_at ? $k->updated_at->format('d M Y, H:i') : '-' }}
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end space-x-1">
                                    <button wire:click="openEditModal({{ $k->id }})" 
                                            class="p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Edit Kategori">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <button wire:click="deleteKategori({{ $k->id }})" 
                                            wire:confirm="Yakin ingin menghapus kategori '{{ $k->nama_kategori }}'?"
                                            class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus Kategori">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    <p class="font-bold text-gray-600">Tidak ada kategori ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau tambah kategori baru.</p>
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
                label="kategori" 
                :current="count($kategoris)" 
                :total="$totalFiltered ?? count($kategoris)" 
                color="purple" />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL FORM TAMBAH / EDIT KATEGORI              -->
    <!-- ============================================== -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-[#0E1726] to-[#5E17EB] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">
                                    {{ $isEditMode ? 'Edit Kategori Produk' : 'Tambah Kategori Baru' }}
                                </h3>
                                <p class="text-xs text-purple-200">Kelompok kategori untuk katalog produk</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <!-- Nama Kategori -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Kategori <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       wire:model.live.debounce.300ms="nama_kategori"
                                       placeholder="Contoh: Storage & SSD"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('nama_kategori') border-rose-500 @enderror">
                                @error('nama_kategori') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Slug Kategori -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Slug URL / Identifier
                                </label>
                                <input type="text" 
                                       wire:model="slug"
                                       placeholder="storage-ssd"
                                       class="w-full text-xs font-mono px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('slug') border-rose-500 @enderror">
                                <p class="text-[10px] text-gray-400 mt-1">Otomatis di-generate dari nama kategori jika dikosongkan.</p>
                                @error('slug') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl">
                            <button type="button" 
                                    wire:click="closeModal"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-xs font-bold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer inline-flex items-center space-x-2">
                                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Kategori' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

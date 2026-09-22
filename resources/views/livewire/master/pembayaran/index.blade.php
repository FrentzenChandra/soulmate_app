<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <span>Master Metode Pembayaran</span>
            </h1>
            <p class="text-xs text-gray-500 font-medium mt-1">Kelola pilihan metode transaksi kasir produk (Tunai, Transfer, QRIS, Kartu Debit, dll).</p>
        </div>

        <button type="button"
                wire:click="openCreateModal"
                wire:loading.attr="disabled"
                class="px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Metode</span>
        </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Bar -->
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2 text-xs font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] focus:border-transparent transition-all">
        </div>

        <!-- Filter Dropdowns -->
        <div class="flex items-center gap-2.5">
            <!-- Filter Kategori -->
            <select wire:model.live="kategoriFilter"
                    class="py-2 px-3 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">
                <option value="semua">Semua Kategori</option>
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer Bank</option>
                <option value="qris">QRIS</option>
                <option value="debit">Kartu Debit</option>
                <option value="lainnya">Lainnya</option>
            </select>

            <!-- Filter Status -->
            <select wire:model.live="statusFilter"
                    class="py-2 px-3 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">
                <option value="semua">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>

            <x-table-size-selector />
        </div>
    </div>

    <!-- Data Table Card -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto" wire:loading.class="opacity-60">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-black uppercase text-gray-500 tracking-wider">
                        <th class="py-3 px-4 w-16 text-center cursor-pointer hover:text-gray-700" wire:click="sortBy('urutan')">
                            <div class="flex items-center justify-center space-x-1">
                                <span>Urutan</span>
                                @if($sortField === 'urutan')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 cursor-pointer hover:text-gray-700" wire:click="sortBy('nama')">
                            <div class="flex items-center space-x-1">
                                <span>Nama Metode</span>
                                @if($sortField === 'nama')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 cursor-pointer hover:text-gray-700" wire:click="sortBy('kategori')">
                            <div class="flex items-center space-x-1">
                                <span>Kategori</span>
                                @if($sortField === 'kategori')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4">Informasi Rekening</th>
                        <th class="py-3 px-4 w-28 text-center cursor-pointer hover:text-gray-700" wire:click="sortBy('is_aktif')">
                            <div class="flex items-center justify-center space-x-1">
                                <span>Status</span>
                                @if($sortField === 'is_aktif')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold text-gray-800">
                    @forelse($items as $item)
                        <tr class="hover:bg-purple-50/20 transition-colors">
                            <td class="py-3 px-4 text-center font-mono font-bold text-gray-500">
                                {{ $item->urutan }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-extrabold text-gray-900 block text-xs">{{ $item->nama }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($item->kategori === 'tunai')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Tunai
                                    </span>
                                @elseif($item->kategori === 'transfer')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Transfer Bank
                                    </span>
                                @elseif($item->kategori === 'qris')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        QRIS
                                    </span>
                                @elseif($item->kategori === 'debit')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Kartu Debit
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ ucfirst($item->kategori) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($item->nomor_rekening || $item->atas_nama)
                                    <div class="space-y-0.5">
                                        @if($item->nomor_rekening)
                                            <span class="font-mono text-xs font-bold text-gray-900 block">{{ $item->nomor_rekening }}</span>
                                        @endif
                                        @if($item->atas_nama)
                                            <span class="text-[10px] text-gray-500 font-medium block">a.n {{ $item->atas_nama }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 font-normal italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button"
                                        wire:click="toggleStatus({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        class="cursor-pointer inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-all {{ $item->is_aktif ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $item->is_aktif ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button"
                                            wire:click="openEditModal({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#5E17EB] hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Edit Metode">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button"
                                            wire:click="delete({{ $item->id }})"
                                            wire:confirm="Yakin ingin menghapus metode pembayaran ini?"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus Metode">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                <p class="text-xs font-bold text-gray-600">Tidak Ada Data Metode Pembayaran</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau tambah metode baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================== -->
    <!-- MODAL CREATE / EDIT METODE PEMBAYARAN          -->
    <!-- ============================================== -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <!-- Modal Header -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    {{ $isEditMode ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran' }}
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Pengaturan jenis penerimaan pembayaran kasir</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Modal Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <!-- Nama Metode -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Metode *
                                </label>
                                <input type="text"
                                       wire:model="nama"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                @error('nama') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Kategori Metode -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Kategori Metode *
                                </label>
                                <select wire:model="kategori"
                                        class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                    <option value="tunai">Tunai (Cash)</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="qris">QRIS (E-Wallet)</option>
                                    <option value="debit">Kartu Debit (EDC)</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                @error('kategori') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nomor Rekening (Opsional) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nomor Rekening (Opsional)
                                </label>
                                <input type="text"
                                       wire:model="nomor_rekening"
                                       class="w-full text-xs font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                @error('nomor_rekening') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Atas Nama (Opsional) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Atas Nama (Opsional)
                                </label>
                                <input type="text"
                                       wire:model="atas_nama"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                @error('atas_nama') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Urutan Tampilan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Urutan Tampilan
                                </label>
                                <input type="number"
                                       min="0"
                                       wire:model="urutan"
                                       class="w-full text-xs font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                @error('urutan') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Status Aktif Checkbox -->
                            <div class="pt-2">
                                <label class="flex items-center space-x-2.5 cursor-pointer">
                                    <input type="checkbox"
                                           wire:model="is_aktif"
                                           class="w-4 h-4 rounded text-[#5E17EB] focus:ring-[#5E17EB] border-gray-300">
                                    <span class="text-xs font-bold text-gray-800">Aktifkan Metode Ini di Kasir</span>
                                </label>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-extrabold text-white shadow-md shadow-purple-900/20 transition-all cursor-pointer flex items-center space-x-1.5">
                                <svg wire:loading wire:target="save" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Metode' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

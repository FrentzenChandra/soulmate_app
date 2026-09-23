<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0052FF] flex items-center justify-center border border-blue-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
                <span>Serial Number</span>
            </h1>
            <p class="text-xs text-gray-500 font-medium mt-1">Pencatatan dan pelacakan unit barang berdasarkan nomor seri dan distributor.</p>
        </div>

        <button type="button"
                wire:click="openCreateModal"
                wire:loading.attr="disabled"
                class="px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Serial Number</span>
        </button>
    </div>

    <!-- Toolbar Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2.5 text-sm font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] focus:border-transparent transition-all">
        </div>
        <x-table-size-selector />
    </div>

    <!-- Data Table -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto" wire:loading.class="opacity-60">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-black uppercase text-gray-500 tracking-wider">
                        <x-th-sort field="sn" :sortField="$sortField" :sortDirection="$sortDirection">Serial Number</x-th-sort>
                        <x-th-sort field="nama" :sortField="$sortField" :sortDirection="$sortDirection">Nama Barang</x-th-sort>
                        <x-th-sort field="merk_tipe_spek" :sortField="$sortField" :sortDirection="$sortDirection">Spesifikasi</x-th-sort>
                        <x-th-sort field="customer_id" :sortField="$sortField" :sortDirection="$sortDirection">Pelanggan</x-th-sort>
                        <x-th-sort field="distributor" :sortField="$sortField" :sortDirection="$sortDirection">Distributor</x-th-sort>
                        <x-th-sort field="created_at" :sortField="$sortField" :sortDirection="$sortDirection">Waktu Input</x-th-sort>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold text-gray-800">
                    <x-table-skeleton :columns="7" :rows="5" target="search, sortField, sortDirection" />
                    @forelse($items as $item)
                        <tr class="hover:bg-purple-50/20 transition-colors" wire:loading.remove wire:target="search, sortField, sortDirection">
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm sm:text-base font-mono font-black bg-blue-50 text-[#0052FF] border border-blue-200 tracking-wider">
                                    {{ $item->sn }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-gray-900 text-sm">
                                {{ $item->nama }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 text-sm">
                                {{ $item->merk_tipe_spek }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($item->customer)
                                    <div class="flex flex-col">
                                        <span class="font-bold text-xs text-gray-900">{{ $item->customer->nama }}</span>
                                        <span class="text-[11px] text-gray-500 font-mono">{{ $item->customer->no_hp ?? '-' }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">
                                        Pelanggan Umum
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ $item->distributor ?: 'Lokal' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500 font-mono text-xs">
                                {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button"
                                            wire:click="openEditModal({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#5E17EB] hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button"
                                            wire:click="delete({{ $item->id }})"
                                            wire:confirm="Yakin ingin menghapus Serial Number ini?"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr wire:loading.remove wire:target="search, sortField, sortDirection">
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                <p class="text-sm font-bold text-gray-600">Tidak Ada Data Serial Number</p>
                                <p class="text-xs text-gray-400 mt-0.5">Belum ada data nomor seri yang tersimpan.</p>
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
    <!-- MODAL FORM SERIAL NUMBER                       -->
    <!-- ============================================== -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    {{ $isEditMode ? 'Edit Serial Number' : 'Tambah Serial Number' }}
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Nomor seri unit produk</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <!-- Serial Number (SN) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Serial Number <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text"
                                       wire:model="sn"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] text-xs font-semibold">
                                @error('sn') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nama Barang / Unit -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Nama Barang <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text"
                                       wire:model="nama"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] text-xs font-semibold">
                                @error('nama') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Merk / Tipe / Spesifikasi -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Spesifikasi <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text"
                                       wire:model="merk_tipe_spek"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] text-xs font-semibold">
                                @error('merk_tipe_spek') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Distributor / Vendor -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Distributor
                                </label>
                                <input type="text"
                                       wire:model="distributor"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] text-xs font-semibold">
                                @error('distributor') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Pelanggan Terkait -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Pelanggan
                                    </label>
                                    <button type="button" wire:click="openCustomerModal" class="text-[11px] font-bold text-[#5E17EB] hover:text-purple-700 flex items-center space-x-1 cursor-pointer">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Tambah Pelanggan</span>
                                    </button>
                                </div>
                                <select wire:model="customer_id"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] text-xs font-semibold bg-white cursor-pointer">
                                    <option value="">Pelanggan Umum (Tanpa Pelanggan Khusus)</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->nama }} ({{ $c->no_hp ?? 'No WhatsApp' }})</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer text-xs">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 font-bold text-white shadow-md shadow-purple-900/20 transition-all cursor-pointer text-xs flex items-center space-x-2">
                                <svg wire:loading wire:target="save" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Serial Number' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL TAMBAH PELANGGAN CEPAT                   -->
    <!-- ============================================== -->
    @if($customerModalOpen)
        <div class="fixed inset-0 z-60 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeCustomerModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    Tambah Pelanggan Baru
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Master Pelanggan Cepat</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCustomerModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="saveNewCustomer">
                        <div class="p-5 space-y-3.5">
                            <!-- Nama Pelanggan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Pelanggan <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text"
                                       wire:model="new_customer_nama"
                                       class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900"
                                       required>
                                @error('new_customer_nama') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nomor WhatsApp / HP -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    WhatsApp
                                </label>
                                <input type="text"
                                       wire:model="new_customer_hp"
                                       class="w-full text-xs font-mono font-semibold px-3 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                @error('new_customer_hp') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-5 py-3.5 flex items-center justify-end space-x-2 rounded-b-3xl">
                            <button type="button"
                                    wire:click="closeCustomerModal"
                                    class="px-3 py-1.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-1.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-extrabold text-white shadow-md transition-all cursor-pointer flex items-center space-x-1.5">
                                <svg wire:loading wire:target="saveNewCustomer" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Simpan Pelanggan</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

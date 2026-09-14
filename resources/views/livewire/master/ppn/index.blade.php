<div class="space-y-6">

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="px-4 py-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-xs font-bold">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>Kantor Pusat (HQ)</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Master Tarif PPN</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Tarif Pajak (PPN)</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola konfigurasi opsi tarif PPN yang berlaku untuk transaksi kasir produk dan penagihan servis.</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-sm shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Tarif PPN Baru</span>
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama tarif PPN..." 
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-gray-50/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all">
        </div>

        <!-- Filter Status -->
        <div class="flex items-center space-x-2">
            <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Status:</span>
            <select wire:model.live="statusFilter" 
                    class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-purple-400">
                <option value="semua">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Non-Aktif</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white card-custom rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <x-th-sort field="nama" :sortField="$sortField" :sortDirection="$sortDirection" class="px-5">Nama Tarif PPN</x-th-sort>
                        <x-th-sort field="persen" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Persentase (%)</x-th-sort>
                        <x-th-sort field="is_aktif" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Status Ketersediaan</x-th-sort>
                        <th class="py-3.5 px-5 text-right font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ppns as $p)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Nama PPN -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 font-extrabold flex items-center justify-center text-xs shadow-xs shrink-0 border border-purple-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 block leading-tight">{{ $p->nama }}</span>
                                        <span class="text-[10px] text-gray-400">ID: #{{ $p->id }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Persentase -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-xl bg-purple-50 text-purple-700 font-extrabold text-xs border border-purple-200">
                                    {{ rtrim(rtrim(number_format($p->persen, 2, '.', ''), '0'), '.') }}%
                                </span>
                            </td>

                            <!-- Status Toggle -->
                            <td class="py-4 px-4 text-center">
                                <button wire:click="toggleStatus({{ $p->id }})"
                                        class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer {{ $p->is_aktif ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-300' }}">
                                    <span class="w-2 h-2 rounded-full {{ $p->is_aktif ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                    <span>{{ $p->is_aktif ? 'Aktif' : 'Non-Aktif' }}</span>
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center space-x-1">
                                    <button wire:click="openEditModal({{ $p->id }})" 
                                            class="p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Edit Tarif PPN">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button wire:click="deletePpn({{ $p->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus tarif PPN \"{{ $p->nama }}\"?"
                                            class="p-2 rounded-xl text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus Tarif PPN">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <p class="font-bold text-gray-600">Tidak ada tarif PPN ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Tambah tarif PPN baru untuk digunakan di kasir & servis.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Infinite Scroll Sentinel & Status -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            @if(isset($hasMore) && $hasMore)
                <div x-intersect="$wire.loadMore()" 
                     class="py-2 flex flex-col items-center justify-center space-y-2 text-center">
                    <div wire:loading wire:target="loadMore" class="flex items-center space-x-2 text-[#5E17EB]">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span class="text-xs font-bold">Memuat tarif lainnya...</span>
                    </div>

                    <button type="button" 
                            wire:click="loadMore" 
                            wire:loading.remove 
                            wire:target="loadMore"
                            class="px-4 py-1.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-600 hover:text-[#5E17EB] hover:border-[#5E17EB] shadow-xs transition-all cursor-pointer">
                        Muat Lebih Banyak
                    </button>
                </div>
            @elseif(count($ppns) > 0)
                <div class="py-1 text-center text-xs font-medium text-gray-400">
                    Menampilkan seluruh {{ count($ppns) }} dari {{ $totalPpn ?? count($ppns) }} tarif PPN
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form Tambah / Edit PPN -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-[#0E1726] to-[#5E17EB] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white" id="modal-title">
                                    {{ $isEditMode ? 'Edit Tarif PPN' : 'Tambah Tarif PPN Baru' }}
                                </h3>
                                <p class="text-xs text-purple-200">Konfigurasi persentase pajak transaksi</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <!-- Nama PPN -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Tarif PPN <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       wire:model="nama" 
                                       placeholder="Contoh: PPN 11%, PPN 12%, Bebas Pajak"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('nama') border-rose-500 @enderror">
                                @error('nama') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Persentase PPN -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Persentase Pajak (%) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           wire:model="persen" 
                                           placeholder="11"
                                           class="w-full text-xs font-bold pl-3.5 pr-8 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('persen') border-rose-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                                        %
                                    </div>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 block">Isi 0 untuk transaksi non-PPN / bebas pajak.</span>
                                @error('persen') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Status Aktif -->
                            <div class="pt-2">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" wire:model="is_aktif" class="w-4 h-4 text-[#5E17EB] rounded border-gray-300 focus:ring-[#5E17EB]">
                                    <div>
                                        <span class="text-xs font-bold text-gray-800 block">Status Aktif</span>
                                        <span class="text-[10px] text-gray-400">Tarif ini dapat dipilih langsung di kasir & penagihan servis</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 rounded-b-3xl border-t border-gray-100 flex items-center justify-end space-x-3">
                            <button type="button" 
                                    wire:click="closeModal" 
                                    class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 rounded-xl hover:bg-gray-200/60 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-900/30 transition-all cursor-pointer">
                                <span wire:loading.remove>{{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Tarif' }}</span>
                                <span wire:loading>Memproses...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>
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
                @if($isHQ)
                    <span>Kantor Pusat (HQ)</span>
                @elseif($isOwner)
                    <span>Cabang {{ auth()->user()->cabang?->nama_cabang ?? 'Aktif' }}</span>
                @else
                    <span>Soulmate Komputer</span>
                @endif
                <span>/</span>
                <span class="text-purple-600 font-bold">Data Pelanggan</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Data Pelanggan</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola data pelanggan toko & servis, kontak WhatsApp, riwayat transaksi, dan tipe keanggotaan.</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-sm shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>Tambah Pelanggan Baru</span>
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
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-gray-50/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all">
        </div>

        <!-- Filter Cabang (Owner/HQ Only) -->
        @if($isHQ)
            <div class="flex items-center space-x-3 w-full md:w-auto">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Cabang:</span>
                    <select wire:model.live="cabangFilter" 
                            class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-purple-400">
                        <option value="semua">Semua Cabang</option>
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    </div>

    <!-- Table Card -->
    <div class="bg-white card-custom rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <x-th-sort field="nama" :sortField="$sortField" :sortDirection="$sortDirection" class="px-5">Nama Pelanggan</x-th-sort>
                        <x-th-sort field="no_hp" :sortField="$sortField" :sortDirection="$sortDirection">Kontak & WhatsApp</x-th-sort>
                        @if($isHQ)
                            <x-th-sort field="cabang_id" :sortField="$sortField" :sortDirection="$sortDirection">Cabang Terdaftar</x-th-sort>
                        @endif
                        <th class="py-3.5 px-5 text-right font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <x-table-skeleton :columns="$isHQ ? 4 : 3" :rows="6" target="search, cabangFilter, sortField, sortDirection" />
                <tbody wire:loading.remove wire:target="search, cabangFilter, sortField, sortDirection" class="divide-y divide-gray-100">
                    @forelse($customers as $c)
                        @php
                            $waNumber = $c->no_hp ? preg_replace('/[^0-9]/', '', $c->no_hp) : '';
                            if (str_starts_with($waNumber, '08')) {
                                $waNumber = '628' . substr($waNumber, 2);
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Nama Pelanggan & Avatar -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl overflow-hidden bg-purple-50 text-purple-700 font-extrabold flex items-center justify-center text-xs shadow-xs shrink-0 border border-purple-100">
                                        <span>{{ strtoupper(substr($c->nama, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 block leading-tight">{{ $c->nama }}</span>
                                        <span class="text-[10px] text-gray-400">ID: #{{ $c->id }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kontak & Quick WA Button -->
                            <td class="py-4 px-4">
                                @if($c->no_hp)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-gray-800">{{ $c->no_hp }}</span>
                                        @if($waNumber)
                                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer"
                                               class="p-1 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                               title="Chat WhatsApp">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">Tidak ada nomor</span>
                                @endif
                            </td>

                            <!-- Cabang -->
                            @if($isHQ)
                                <td class="py-4 px-4">
                                    <div class="flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#5E17EB]"></span>
                                        <span class="font-bold text-gray-800">{{ $c->cabang?->nama_cabang ?? '-' }}</span>
                                    </div>
                                </td>
                            @endif

                            <!-- Actions -->
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center space-x-1">
                                    <button wire:click="openEditModal({{ $c->id }})" 
                                            class="p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer"
                                            title="Edit Pelanggan">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button wire:click="deleteCustomer({{ $c->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus pelanggan \"{{ $c->nama }}\"?"
                                            class="p-2 rounded-xl text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus Pelanggan">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 4 : 3 }}" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span class="text-xs font-medium">Tidak ada data pelanggan yang sesuai kriteria pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Infinite Scroll / Load More -->
        @if($hasMore)
            <div class="p-4 border-t border-gray-100 flex items-center justify-center bg-gray-50/50">
                <button wire:click="loadMore" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-white border border-gray-200 text-xs font-extrabold text-[#5E17EB] hover:bg-purple-50 transition-all shadow-2xs cursor-pointer flex items-center space-x-2">
                    <svg wire:loading wire:target="loadMore" class="animate-spin w-3.5 h-3.5 text-[#5E17EB]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Muat Lebih Banyak Pelanggan ({{ $totalCustomers - $customers->count() }} tersisa)</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah / Edit Pelanggan -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    
                    <!-- Modal Header -->
                    <div class="bg-[#0E1726] px-6 py-5 text-white flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-extrabold text-white">
                                {{ $isEditMode ? 'Ubah Data Pelanggan' : 'Daftar Pelanggan Baru' }}
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $isEditMode ? 'Perbarui informasi kontak pelanggan.' : 'Masukkan nama dan nomor WhatsApp pelanggan.' }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <!-- Nama Pelanggan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Pelanggan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       wire:model="nama" 
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('nama') border-rose-500 @enderror">
                                @error('nama') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- No HP / WhatsApp -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    No. WhatsApp / HP
                                </label>
                                <input type="text" 
                                       wire:model="no_hp" 
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('no_hp') border-rose-500 @enderror">
                                @error('no_hp') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cabang Assignment -->
                            @if($isOwner)
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Cabang Pendaftaran <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model="cabang_id" 
                                            class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('cabang_id') border-rose-500 @enderror">
                                        <option value="">-- Pilih Cabang --</option>
                                        @foreach($cabangs as $c)
                                            <option value="{{ $c->id }}">{{ $c->nama_cabang }} ({{ $c->kode_cabang }})</option>
                                        @endforeach
                                    </select>
                                    @error('cabang_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            @endif
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
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-purple-900/30 transition-all cursor-pointer">
                                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Daftarkan Pelanggan' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>
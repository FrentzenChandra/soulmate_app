<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>Kantor Pusat</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Master Cabang</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Cabang</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola data seluruh outlet cabang toko, nomor WhatsApp pesanan, dan status operasional.</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-sm shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Cabang Baru</span>
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari kode, nama cabang, WA, alamat..."
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-gray-50/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all">
        </div>

        <!-- Filter Status -->
        <div class="flex items-center space-x-2 w-full md:w-auto">
            <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Status:</span>
            <select wire:model.live="statusFilter"
                    class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-purple-400">
                <option value="semua">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white card-custom rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <x-th-sort field="nama_cabang" :sortField="$sortField" :sortDirection="$sortDirection" class="px-5">Kode & Nama Cabang</x-th-sort>
                        <x-th-sort field="alamat" :sortField="$sortField" :sortDirection="$sortDirection">Alamat</x-th-sort>
                        <x-th-sort field="no_wa" :sortField="$sortField" :sortDirection="$sortDirection">Kontak & No. WhatsApp</x-th-sort>
                        <th class="py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Superadmin (PIC)</th>
                        <th class="py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-400 text-center">Staff Terdaftar</th>
                        <x-th-sort field="status" :sortField="$sortField" :sortDirection="$sortDirection" align="center">Status</x-th-sort>
                        <th class="py-3.5 px-5 text-right font-extrabold text-[10px] tracking-wider uppercase text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cabangs as $c)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Kode & Nama -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <span class="w-40 h-9 p-5 text-center rounded-xl bg-purple-50 text-[#5E17EB] font-extrabold flex items-center justify-center text-xs shadow-sm">
                                        {{ $c->kode_cabang }}
                                    </span>
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 block leading-tight">{{ $c->nama_cabang }}</span>
                                        <span class="text-[10px] text-gray-400">ID: #{{ $c->id }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Alamat -->
                            <td class="py-4 px-4 max-w-xs">
                                <span class="text-gray-600 block leading-relaxed line-clamp-2">{{ $c->alamat ?? '-' }}</span>
                            </td>

                            <!-- Kontak & WA -->
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    @if($c->no_wa)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $c->no_wa) }}"
                                           target="_blank"
                                           class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[11px] hover:bg-emerald-100 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                            <span>{{ $c->no_wa }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400 italic">WA belum diisi</span>
                                    @endif

                                    @if($c->no_telp)
                                        <span class="text-[10px] text-gray-400 block font-medium">Telp: {{ $c->no_telp }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Superadmin PIC -->
                            <td class="py-4 px-4">
                                @php
                                    $superadmin = $c->users->first();
                                @endphp
                                @if($superadmin)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center">
                                            {{ strtoupper(substr($superadmin->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-800 block leading-tight">{{ $superadmin->name }}</span>
                                            <span class="text-[10px] text-gray-400">{{ $superadmin->username }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[11px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full font-semibold">
                                        Belum Ada PIC
                                    </span>
                                @endif
                            </td>

                            <!-- Total Staff -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 font-bold text-xs">
                                    {{ $c->users_count }} Orang
                                </span>
                            </td>

                            <!-- Status & Toggle -->
                            <td class="py-4 px-4 text-center">
                                <button wire:click="toggleStatus({{ $c->id }})"
                                        class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer {{ $c->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-300' }}">
                                    <span class="w-2 h-2 rounded-full {{ $c->status === 'aktif' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                    <span>{{ ucfirst($c->status) }}</span>
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-right">
                                <button wire:click="openEditModal({{ $c->id }})"
                                        class="p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer"
                                        title="Edit Data Cabang">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <p class="font-bold text-gray-600">Tidak ada data cabang ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau tambah cabang baru.</p>
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
                        <span class="text-xs font-bold">Memuat cabang lainnya...</span>
                    </div>

                    <button type="button"
                            wire:click="loadMore"
                            wire:loading.remove
                            wire:target="loadMore"
                            class="px-4 py-1.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-600 hover:text-[#5E17EB] hover:border-[#5E17EB] shadow-xs transition-all cursor-pointer">
                        Muat Lebih Banyak Cabang (Scroll Otomatis)
                    </button>
                </div>
            @elseif(count($cabangs) > 0)
                <div class="py-1 text-center text-xs font-medium text-gray-400">
                    ✓ Menampilkan seluruh {{ count($cabangs) }} dari {{ $totalCabang ?? count($cabangs) }} cabang
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form Tambah / Edit Cabang -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-[#0E1726] to-[#5E17EB] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white" id="modal-title">
                                    {{ $isEditMode ? 'Edit Data Cabang' : 'Tambah Cabang Baru' }}
                                </h3>
                                <p class="text-xs text-purple-200">Lengkapi informasi cabang toko di bawah ini</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>


                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Kode Cabang -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Kode Cabang <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           wire:model="kode_cabang"
                                           placeholder="Contoh: CB-PKP"
                                           class="w-full text-xs uppercase font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('kode_cabang') border-rose-500 @enderror">
                                    @error('kode_cabang') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Status Operasional <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model="status"
                                            class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                        <option value="aktif">Aktif (Bisa Transaksi)</option>
                                        <option value="nonaktif">Nonaktif (Tutup)</option>
                                    </select>
                                    @error('status') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Nama Cabang -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Cabang Toko <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       wire:model="nama_cabang"
                                       placeholder="Contoh: Cabang Pangkalpinang"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('nama_cabang') border-rose-500 @enderror">
                                @error('nama_cabang') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- No WA Pesanan -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        No. WhatsApp Pesanan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           wire:model="no_wa"
                                           placeholder="Contoh: 08123456789"
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('no_wa') border-rose-500 @enderror">
                                    <span class="text-[10px] text-gray-400 block mt-0.5">Digunakan pada marketplace publik WA</span>
                                    @error('no_wa') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- No Telp Kantor -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        No. Telepon Toko (Opsional)
                                    </label>
                                    <input type="text"
                                           wire:model="no_telp"
                                           placeholder="Contoh: 0717-432123"
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                    @error('no_telp') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Alamat Cabang -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Alamat Lengkap Cabang
                                </label>
                                <textarea wire:model="alamat"
                                          rows="3"
                                          placeholder="Alamat jalan, nomor, kecamatan, kota..."
                                          class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]"></textarea>
                                @error('alamat') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
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
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-bold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
                                {{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Cabang' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

<div x-data="{
    tableSize: localStorage.getItem('soulmate_table_size') || 'md',
    init() {
        window.addEventListener('table-size-changed', (e) => {
            this.tableSize = e.detail;
        });
    }
}" class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOMBOL AKSI UTAMA            -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Master Pegawai
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Kelola daftar pegawai operasional, teknisi, kasir, dan staf toko untuk transaksi dan target kinerja.
            </p>
        </div>

        @can('master.pegawai.create')
        <div class="flex items-center space-x-3">
            <button type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-2xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Pegawai</span>
            </button>
        </div>
        @endcan
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
    <!-- FILTER & PENCARIAN TOOLBAR                     -->
    <!-- ============================================== -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">

            <!-- Search Multi-Kata -->
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all">
            </div>

            <!-- Filter Bagian -->
            <div class="md:col-span-3">
                <select wire:model.live="bagianFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Bagian</option>
                    @foreach($this->bagianOptions as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Aktif -->
            <div class="md:col-span-2">
                <select wire:model.live="statusFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>

            <!-- Size Selector -->
            <div class="md:col-span-3 flex items-center justify-end">
                <x-table-size-selector />
            </div>

            <!-- Filter Cabang (HQ Saja) -->
            @if($isHQ)
                <div class="md:col-span-12 pt-1 flex justify-end">
                    <div class="w-full sm:w-72">
                        <select wire:model.live="cabangFilter"
                                class="w-full py-2 px-3 rounded-xl border border-purple-200 bg-purple-50/40 text-xs font-bold text-[#5E17EB] focus:bg-white cursor-pointer">
                            <option value="semua">Semua Cabang (HQ)</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL DATA MASTER PEGAWAI                      -->
    <!-- ============================================== -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden"
         :class="'tabulator-size-' + tableSize + ' table-size-' + tableSize">

        <!-- Loading Skeleton Overlay -->
        <div wire:loading.block wire:target="search, bagianFilter, statusFilter, cabangFilter, sortBy, perPage, gotoPage, nextPage, previousPage" class="p-6">
            <x-table-skeleton />
        </div>

        <div wire:loading.remove wire:target="search, bagianFilter, statusFilter, cabangFilter, sortBy, perPage, gotoPage, nextPage, previousPage" class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200/80 text-[11px] font-black uppercase tracking-wider text-gray-600">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 cursor-pointer hover:text-purple-600 transition-colors" wire:click="sortBy('nama')">
                            <div class="flex items-center space-x-1.5">
                                <span>Nama Pegawai</span>
                                @if($sortField === 'nama')
                                    <svg class="w-3.5 h-3.5 text-[#5E17EB] {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 cursor-pointer hover:text-purple-600 transition-colors" wire:click="sortBy('role')">
                            <div class="flex items-center space-x-1.5">
                                <span>Bagian</span>
                                @if($sortField === 'role')
                                    <svg class="w-3.5 h-3.5 text-[#5E17EB] {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                                @endif
                            </div>
                        </th>
                        @if($isHQ)
                            <th class="py-3 px-4">Cabang</th>
                        @endif
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                    @forelse($pegawais as $index => $pegawai)
                        <tr wire:key="pegawai-row-{{ $pegawai->id }}" class="hover:bg-purple-50/30 transition-colors">
                            <td class="py-3 px-4 text-center text-gray-400 font-mono text-xs">
                                {{ $pegawais->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-[#5E17EB] font-black text-xs flex items-center justify-center border border-purple-100 shadow-2xs shrink-0">
                                        {{ strtoupper(substr($pegawai->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900 block leading-tight">{{ $pegawai->nama }}</span>
                                        <span class="text-[10px] text-gray-400 block font-mono mt-0.5">ID: #{{ $pegawai->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black border {{ $pegawai->role_badge_class }}">
                                    {{ $pegawai->role }}
                                </span>
                            </td>
                            @if($isHQ)
                                <td class="py-3 px-4 whitespace-nowrap text-xs font-semibold text-gray-600">
                                    {{ $pegawai->cabang?->nama_cabang ?? 'Pusat' }}
                                </td>
                            @endif
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @can('master.pegawai.edit')
                                <button type="button"
                                        wire:click="toggleStatus({{ $pegawai->id }})"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black transition-all cursor-pointer {{ $pegawai->is_aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $pegawai->is_aktif ? 'bg-emerald-500' : 'bg-gray-400' }} mr-1.5"></span>
                                    <span>{{ $pegawai->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                                @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black {{ $pegawai->is_aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $pegawai->is_aktif ? 'bg-emerald-500' : 'bg-gray-400' }} mr-1.5"></span>
                                    <span>{{ $pegawai->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                                </span>
                                @endcan
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @can('master.pegawai.edit')
                                    <button type="button"
                                            wire:click="openEditModal({{ $pegawai->id }})"
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0052FF] transition-colors cursor-pointer"
                                            title="Edit Pegawai">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    @endcan
                                    @can('master.pegawai.delete')
                                    <button type="button"
                                            wire:click="openDeleteModal({{ $pegawai->id }}, '{{ $pegawai->nama }}')"
                                            class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors cursor-pointer"
                                            title="Hapus Pegawai">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHQ ? 6 : 5 }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p class="font-bold text-gray-600 text-xs">Belum ada data pegawai ditemukan</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Klik tombol "Tambah Pegawai" untuk menambahkan data baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pegawais->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $pegawais->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================== -->
    <!-- MODAL FORM INPUT / EDIT PEGAWAI (2 FIELD SAJA) -->
    <!-- ============================================== -->
    @if($modalOpen)
        <div wire:key="modal-pegawai-form" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 py-5 text-white flex items-center justify-between rounded-t-3xl shadow-sm">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-white/15 flex items-center justify-center border border-white/20 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white tracking-tight">
                                    {{ $isEditMode ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru' }}
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">
                                    {{ $isEditMode ? 'Perbarui nama atau bagian pegawai operasional' : 'Daftarkan nama & bagian pegawai toko' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Form Modal (HANYA 2 FIELD) -->
                    <form wire:submit.prevent="simpanPegawai">
                        <div class="p-6 space-y-4 text-xs">

                            <!-- Field 1: Nama Pegawai -->
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Nama Pegawai <span class="text-rose-500 font-bold">*</span></label>
                                <input type="text"
                                       wire:model="nama"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 font-semibold text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all">
                                @error('nama')
                                    <span class="text-rose-500 font-bold text-[11px] block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Field 2: Bagian Pegawai (Select Option + Custom Input) -->
                            <div class="space-y-2">
                                <label class="block font-bold text-gray-700 mb-1">Bagian Pegawai <span class="text-rose-500 font-bold">*</span></label>
                                <select wire:model.live="bagian"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 font-bold text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all cursor-pointer">
                                    @foreach($this->bagianOptions as $b)
                                        <option value="{{ $b }}">{{ $b }}</option>
                                    @endforeach
                                    <option value="__custom__" class="font-black text-[#5E17EB] bg-purple-50">+ Tambah Bagian Baru...</option>
                                </select>
                                @error('bagian')
                                    <span class="text-rose-500 font-bold text-[11px] block mt-1">{{ $message }}</span>
                                @enderror

                                @if($bagian === '__custom__')
                                    <div class="p-3 bg-purple-50/70 rounded-2xl border border-purple-200 space-y-1.5 mt-2 transition-all">
                                        <label class="block font-bold text-purple-950 text-[11px]">Nama Bagian Baru <span class="text-rose-500 font-bold">*</span></label>
                                        <input type="text"
                                               wire:model="customBagian"
                                               class="w-full px-3.5 py-2 rounded-xl border border-purple-300 bg-white font-bold text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all text-xs">
                                        @error('customBagian')
                                            <span class="text-rose-500 font-bold text-[11px] block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-bold text-white shadow-md shadow-purple-600/20 transition-all cursor-pointer disabled:opacity-50">
                                <svg wire:loading wire:target="simpanPegawai" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="simpanPegawai">Simpan Pegawai</span>
                                <span wire:loading wire:target="simpanPegawai">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI HAPUS PEGAWAI                 -->
    <!-- ============================================== -->
    @if($modalDeleteOpen)
        <div wire:key="modal-delete-pegawai" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-gray-100 text-xs p-6 space-y-4">

                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto border border-rose-100 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>

                    <div class="text-center space-y-1">
                        <h3 class="text-base font-black text-gray-900">Hapus Data Pegawai?</h3>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">
                            Apakah Anda yakin ingin menghapus data pegawai <strong class="text-gray-900">{{ $deleteNama }}</strong>?
                        </p>
                    </div>

                    <div class="pt-2 flex items-center space-x-2">
                        <button type="button"
                                wire:click="closeDeleteModal"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors cursor-pointer text-center">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="konfirmasiHapus"
                                wire:loading.attr="disabled"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all cursor-pointer text-center disabled:opacity-50">
                            <span wire:loading.remove wire:target="konfirmasiHapus">Ya, Hapus</span>
                            <span wire:loading wire:target="konfirmasiHapus">Menghapus...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

<div x-data="{
    tableSize: localStorage.getItem('soulmate_table_size') || 'md',
    init() {
        window.addEventListener('table-size-changed', (e) => {
            this.tableSize = e.detail;
        });
    }
}" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 shadow-2xs shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 tracking-tight">WiFi Absensi</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Kelola daftar IP jaringan WiFi yang diizinkan untuk absensi kehadiran staf.</p>
            </div>
        </div>

        <div>
            <button type="button"
                    wire:click="openCreateModal"
                    wire:loading.attr="disabled"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah WiFi</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-bold">{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', null)" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-bold">{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- Toolbar Pencarian & Filter -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-1 items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] transition-all">
            </div>

            @if ($isOwner)
                <div class="w-48">
                    <select wire:model.live="cabangFilter"
                            class="w-full py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                        <option value="semua">Semua Cabang</option>
                        @foreach ($cabangs as $cb)
                            <option value="{{ $cb->id }}">{{ $cb->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <x-table-size-selector />
        </div>
    </div>

    <!-- Tabel Data WiFi Absensi -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse"
                   :class="{
                       'text-[11px]': tableSize === 'sm',
                       'text-xs': tableSize === 'md',
                       'text-[13px]': tableSize === 'lg'
                   }">
                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-600 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4 font-black">Nama WiFi</th>
                        <th class="py-3.5 px-4 font-black">IP Address</th>
                        @if ($isOwner)
                            <th class="py-3.5 px-4 font-black">Cabang</th>
                        @endif
                        <th class="py-3.5 px-4 font-black text-center">Status</th>
                        <th class="py-3.5 px-4 font-black text-right">Aksi</th>
                    </tr>
                </thead>

                <x-table-skeleton :cols="$isOwner ? 5 : 4" :rows="4" target="search, cabangFilter" />

                <tbody wire:loading.remove wire:target="search, cabangFilter" class="divide-y divide-gray-100 bg-white">
                    @forelse ($wifis as $wifi)
                        <tr class="hover:bg-purple-50/20 transition-colors">
                            <td class="py-3 px-4 font-bold text-gray-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center shrink-0 border border-purple-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                        </svg>
                                    </div>
                                    <span>{{ $wifi->nama }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-4 font-mono font-bold text-gray-800">
                                <span class="bg-gray-100 px-2.5 py-1 rounded-lg text-xs border border-gray-200">
                                    {{ $wifi->ip_address }}
                                </span>
                            </td>

                            @if ($isOwner)
                                <td class="py-3 px-4 text-xs font-semibold text-gray-600">
                                    {{ $wifi->cabang?->nama_cabang ?? '-' }}
                                </td>
                            @endif

                            <td class="py-3 px-4 text-center">
                                <button type="button"
                                        wire:click="toggleStatus({{ $wifi->id }})"
                                        wire:loading.attr="disabled"
                                        class="cursor-pointer inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black transition-colors {{ $wifi->is_aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $wifi->is_aktif ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    <span>{{ $wifi->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end space-x-1">
                                    <button type="button"
                                            wire:click="openEditModal({{ $wifi->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
                                            title="Edit WiFi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <button type="button"
                                            wire:click="delete({{ $wifi->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus IP WiFi ini?"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus WiFi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isOwner ? 5 : 4 }}" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Belum ada WiFi yang terdaftar</p>
                                    <p class="text-xs text-gray-400 max-w-sm">
                                        Daftarkan IP address router atau modem cabang agar staf dapat melakukan absensi.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($wifis->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $wifis->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================== -->
    <!-- MODAL FORM INPUT / EDIT WIFI ABSENSI           -->
    <!-- Ukuran Maksimal sm:max-w-2xl                   -->
    <!-- ============================================== -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
                    <form wire:submit.prevent="save">
                        <!-- Header Modal -->
                        <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-black text-gray-900" id="modal-title">
                                {{ $isEdit ? 'Edit WiFi Absensi' : 'Tambah WiFi Absensi Baru' }}
                            </h3>
                            <button type="button"
                                    wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-600 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Body Modal -->
                        <div class="p-6 space-y-4">
                            <!-- Cabang Selector (Hanya untuk Owner) -->
                            @if ($isOwner)
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">
                                        Cabang <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <select wire:model="cabang_id"
                                            class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                                        <option value="">Pilih Cabang</option>
                                        @foreach ($cabangs as $cb)
                                            <option value="{{ $cb->id }}">{{ $cb->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                    @error('cabang_id') <span class="text-rose-500 text-[11px] font-bold">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <!-- Nama WiFi -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Nama WiFi <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text"
                                       wire:model="nama"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB]">
                                @error('nama') <span class="text-rose-500 text-[11px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <!-- IP Address -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold text-gray-700">
                                        IP Address Jaringan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <button type="button"
                                            wire:click="useCurrentIp"
                                            class="text-[11px] font-bold text-[#5E17EB] hover:text-purple-800 transition-colors cursor-pointer inline-flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>Deteksi Ulang Jaringan</span>
                                    </button>
                                </div>
                                <input type="text"
                                       wire:model="ip_address"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-xs font-mono font-bold text-gray-800 focus:bg-white focus:border-[#5E17EB]">
                                <div class="mt-1.5 space-y-1 text-[11px] text-gray-500">
                                    <p class="flex items-center space-x-1 font-semibold text-emerald-700">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        <span>Terdeteksi otomatis. Mendukung wildcard bintang (*) dan subnet router kantor.</span>
                                    </p>
                                    <p class="text-gray-400">
                                        Format valid: <span class="font-mono text-gray-600 font-bold">192.168.18.*</span> (semua HP di WiFi toko), <span class="font-mono text-gray-600 font-bold">2404:8000:1036:ac2:*</span> (IPv6 kantor), atau IP publik pasti.
                                    </p>
                                </div>
                                @error('ip_address') <span class="text-rose-500 text-[11px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Status Aktif -->
                            <div class="flex items-center space-x-2 pt-2">
                                <input type="checkbox"
                                       id="is_aktif"
                                       wire:model="is_aktif"
                                       class="w-4 h-4 text-[#5E17EB] border-gray-300 rounded focus:ring-purple-500 cursor-pointer">
                                <label for="is_aktif" class="text-xs font-bold text-gray-700 cursor-pointer">
                                    Aktifkan WiFi untuk absensi sekarang
                                </label>
                            </div>
                        </div>

                        <!-- Footer Modal: Standar Loading Kasir Produk -->
                        <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors cursor-pointer">
                                Batal
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="save"
                                    class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-900/20 transition-all flex items-center space-x-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading wire:target="save" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="save">Simpan</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>


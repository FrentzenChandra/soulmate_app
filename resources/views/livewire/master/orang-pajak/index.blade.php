<div class="space-y-6">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>Master & Organisasi</span>
                <span>/</span>
                <span class="text-[#5E17EB] font-bold">Orang Pajak</span>
            </div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-6 bg-[#5E17EB] rounded-full inline-block"></span>
                Master Orang Pajak
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola data rekanan, dinas, dan wajib pajak untuk administrasi faktur pajak</p>
        </div>

        <div class="flex items-center gap-3">
            @canany(['master.orang_pajak.create', 'owner'])
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#5E17EB] hover:bg-[#4d12c4] text-white text-xs font-bold rounded-xl shadow-xs transition-colors duration-150 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Rekanan Baru</span>
            </button>
            @endcanany
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Pencarian</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full text-xs pl-9 pr-3 py-2 bg-gray-50/80 border border-gray-200 rounded-xl text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] focus:outline-hidden transition-colors"
                >
            </div>
        </div>

        <!-- Filter Status & Table Size Selector -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            <div class="w-full sm:w-44">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Status</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full text-xs bg-gray-50/80 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] focus:outline-hidden transition-colors"
                >
                    <option value="semua">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Non-Aktif</option>
                </select>
            </div>
            <div class="pt-5.5">
                <x-table-size-selector />
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div
        x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
        x-on:table-size-changed.window="tableSize = $event.detail"
        :class="'table-size-' + tableSize"
        class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden"
    >
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 text-gray-700 font-bold border-b border-gray-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                        <th scope="col" class="py-3 px-4">Nama</th>
                        <th scope="col" class="py-3 px-4">NPWP</th>
                        <th scope="col" class="py-3 px-4">Kontak</th>
                        <th scope="col" class="py-3 px-4 text-center">Status</th>
                        <th scope="col" class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <x-table-skeleton :columns="6" :rows="6" target="search, statusFilter" />
                <tbody wire:loading.remove wire:target="search, statusFilter" class="divide-y divide-gray-100 font-medium text-gray-900">
                    @forelse ($items as $index => $item)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-3 px-4 text-center font-mono text-gray-500 font-bold">
                                {{ $items->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-gray-900 block text-xs">{{ $item->nama }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-gray-600">
                                {{ $item->npwp ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $item->kontak ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($item->is_aktif)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @canany(['master.orang_pajak.edit', 'owner'])
                                    <button
                                        type="button"
                                        wire:click="edit({{ $item->id }})"
                                        class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Data"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    @endcanany

                                    @canany(['master.orang_pajak.delete', 'owner'])
                                    <button
                                        type="button"
                                        x-on:click="
                                            if (typeof window.swalConfirm === 'function') {
                                                window.swalConfirm('Hapus Orang Pajak?', 'Data rekanan \'{{ addslashes($item->nama) }}\' akan dihapus permanen dari sistem.', {
                                                    danger: true,
                                                    confirmText: 'Ya, Hapus',
                                                    cancelText: 'Batal'
                                                }).then((res) => {
                                                    if (res.isConfirmed) {
                                                        $wire.delete({{ $item->id }});
                                                    }
                                                });
                                            } else if (confirm('Hapus rekanan \'{{ addslashes($item->nama) }}\'?')) {
                                                $wire.delete({{ $item->id }});
                                            }
                                        "
                                        class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                        title="Hapus Data"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @endcanany
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="text-xs">Belum ada data Orang Pajak yang tercatat</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form (sm:max-w-md) -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    <!-- Modal Header -->
                    <div class="bg-[#5E17EB] px-6 py-5 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-bold text-white tracking-tight leading-snug">
                                    {{ $isEditMode ? 'Edit Orang Pajak' : 'Tambah Orang Pajak Baru' }}
                                </h3>
                                <p class="text-[11px] text-purple-200 font-medium">
                                    Administrasi rekanan & wajib pajak
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="p-6 space-y-4">
                        <!-- Nama Rekanan (Single Label Standard & Red Asterisk Standard) -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Nama <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="nama"
                                class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] focus:outline-hidden transition-colors"
                            >
                            @error('nama')
                                <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- NPWP -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                NPWP
                            </label>
                            <input
                                type="text"
                                wire:model="npwp"
                                class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] focus:outline-hidden transition-colors"
                            >
                            @error('npwp')
                                <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Kontak -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Kontak
                            </label>
                            <input
                                type="text"
                                wire:model="kontak"
                                class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] focus:outline-hidden transition-colors"
                            >
                            @error('kontak')
                                <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status Aktif -->
                        <div class="flex items-center gap-2 pt-1">
                            <input
                                type="checkbox"
                                id="is_aktif"
                                wire:model="is_aktif"
                                class="rounded border-gray-300 text-[#5E17EB] focus:ring-[#5E17EB] w-4 h-4 cursor-pointer"
                            >
                            <label for="is_aktif" class="text-xs font-semibold text-gray-700 select-none cursor-pointer">
                                Status Aktif
                            </label>
                        </div>

                        <!-- Actions (Kasir Produk Loading Standard) -->
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#5E17EB] hover:bg-[#4d12c4] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors duration-150 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
                            >
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

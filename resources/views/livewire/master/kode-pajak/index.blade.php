<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2.5 h-6 bg-[#0052FF] rounded-full inline-block"></span>
                Master Kode Pajak (Owner HQ)
            </h1>
            <p class="text-xs text-gray-500 mt-1">Kelola daftar klasifikasi kode transaksi faktur (Kode 1 s/d 5)</p>
        </div>

        <button
            type="button"
            wire:click="openCreateModal"
            class="mt-4 sm:mt-0 inline-flex items-center gap-2 px-4 py-2.5 bg-[#0052FF] hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kode Pajak
        </button>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200 uppercase tracking-wider text-[11px]">
                <tr>
                    <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                    <th scope="col" class="py-3 px-4 w-36">Kode Pajak</th>
                    <th scope="col" class="py-3 px-4">Keterangan Klasifikasi</th>
                    <th scope="col" class="py-3 px-4 text-center">Status</th>
                    <th scope="col" class="py-3 px-4 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium text-gray-900">
                @forelse ($items as $index => $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-center font-mono text-gray-500 font-bold">{{ $index + 1 }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-purple-50 text-[#5E17EB] border border-purple-200">
                                {{ $item->kode }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $item->keterangan ?: '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            @if ($item->is_aktif)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" wire:click="edit({{ $item->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Hapus kode pajak ini?" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Belum ada kode pajak</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4">
            <div class="bg-white w-full max-w-sm rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-900">{{ $isEditMode ? 'Edit Kode Pajak' : 'Tambah Kode Pajak' }}</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form wire:submit="save" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Pajak <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="kode" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#0052FF] focus:outline-hidden">
                        @error('kode') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan Klasifikasi</label>
                        <textarea wire:model="keterangan" rows="2" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#0052FF] focus:outline-hidden"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="kode_aktif" wire:model="is_aktif" class="rounded border-gray-300 text-[#0052FF] focus:ring-[#0052FF]">
                        <label for="kode_aktif" class="text-xs font-semibold text-gray-700">Status Aktif</label>
                    </div>
                    <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-[#0052FF] hover:bg-blue-700 text-white text-xs font-semibold rounded-xl disabled:opacity-50">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>


<div class="space-y-6">

    @php
        $isSuperOrOwner = auth()->user()->isOwner() || auth()->user()->isSuperadmin();
        $todayModal = $this->todayModalAwal;
    @endphp

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-2xs shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 tracking-tight">Modal Awal Kasir</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Pencatatan kas fisik uang kembalian di laci kasir setiap awal hari.</p>
            </div>
        </div>

        <div>
            @if(!$todayModal || $isSuperOrOwner)
                <button type="button"
                        wire:click="openCreateModal"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ $todayModal ? 'Penyesuaian Modal Hari Ini' : 'Input Modal Awal Hari Ini' }}</span>
                </button>
            @else
                <div class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Modal Hari Ini Sudah Dicatat</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Status Banner Modal Awal Hari Ini -->
    <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl {{ $todayModal ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }} flex items-center justify-center shrink-0">
                    @if($todayModal)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    @endif
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status Modal Kasir Hari Ini ({{ now()->translatedFormat('d F Y') }})</span>
                        @if($todayModal)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">Aktif</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">Belum Dicatat</span>
                        @endif
                    </div>
                    @if($todayModal)
                        <p class="text-2xl font-black text-gray-900 mt-0.5">
                            Rp {{ number_format($todayModal->nominal, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Dicatat oleh <span class="font-bold text-gray-700">{{ $todayModal->user?->name ?? 'Kasir' }}</span> &bull; {{ $todayModal->keterangan ?: 'Kas Awal Harian' }}
                        </p>
                    @else
                        <p class="text-sm font-bold text-amber-700 mt-1">
                            Kasir belum menginput modal awal kas untuk hari ini.
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pastikan kasir memasukkan saldo awal laci sebelum memulai transaksi penjualan.
                        </p>
                    @endif
                </div>
            </div>

            @if($todayModal && $isSuperOrOwner)
                <button type="button"
                        wire:click="openEditModal({{ $todayModal->id }})"
                        wire:loading.attr="disabled"
                        class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors cursor-pointer flex items-center justify-center space-x-1.5 self-start md:self-center">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Koreksi Modal Hari Ini</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Toolbar Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Bar -->
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2 text-xs font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#0052FF] focus:border-transparent transition-all">
        </div>

        <!-- Filter Tanggal -->
        <div class="flex items-center gap-2">
            <input type="date"
                   wire:model.live="startDate"
                   class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0052FF]">
            <span class="text-xs font-bold text-gray-400">s.d</span>
            <input type="date"
                   wire:model.live="endDate"
                   class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0052FF]">
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-black uppercase text-gray-500 tracking-wider">
                        <x-th-sort field="tanggal" :sortField="$sortField" :sortDirection="$sortDirection" class="py-3 px-4">
                            Tanggal
                        </x-th-sort>
                        <x-th-sort field="nominal" :sortField="$sortField" :sortDirection="$sortDirection" align="right" class="py-3 px-4">
                            Modal Awal (Kas)
                        </x-th-sort>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4">Petugas Input</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <x-table-skeleton :columns="5" :rows="5" target="search, startDate, endDate, sortField, sortDirection" />
                <tbody wire:loading.remove wire:target="search, startDate, endDate, sortField, sortDirection" class="divide-y divide-gray-100 font-semibold text-gray-800">
                    @forelse($items as $item)
                        <tr class="hover:bg-blue-50/20 transition-colors">
                            <td class="py-3.5 px-4 text-gray-900 font-bold font-mono text-[11px]">
                                {{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}
                                @if($item->tanggal && $item->tanggal->isToday())
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded text-[9px] font-black bg-emerald-100 text-emerald-700">Hari Ini</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-black text-emerald-600 text-sm">
                                Rp {{ number_format($item->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-700 font-medium">
                                {{ $item->keterangan ?: 'Kas Awal Harian' }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 text-[11px]">
                                {{ $item->user?->name ?? 'Kasir' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($isSuperOrOwner)
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button type="button"
                                                wire:click="openEditModal({{ $item->id }})"
                                                wire:loading.attr="disabled"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-[#0052FF] hover:bg-blue-50 transition-colors cursor-pointer"
                                                title="Edit Modal Awal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button"
                                                wire:click="delete({{ $item->id }})"
                                                wire:confirm="Yakin ingin menghapus catatan modal awal ini?"
                                                wire:loading.attr="disabled"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                title="Hapus Modal Awal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold text-gray-400 bg-gray-100" title="Hanya Superadmin yang dapat mengubah catatan">
                                        <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Terkunci
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-xs font-bold text-gray-600">Tidak Ada Catatan Modal Awal</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Klik tombol Input Modal Awal untuk memasukkan kas awal kasir.</p>
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
    <!-- MODAL SET MODAL AWAL                           -->
    <!-- ============================================== -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#0E1726] px-6 py-4 text-white flex items-center justify-between border-b border-gray-800">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-white leading-tight">
                                    {{ $isEditMode ? 'Koreksi Modal Awal' : 'Input Modal Awal Kasir' }}
                                </h3>
                                <p class="text-[11px] text-gray-400 font-medium">Saldo kas fisik laci awal hari</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-white cursor-pointer p-1 rounded-lg hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <!-- Tanggal -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Tanggal *
                                </label>
                                <input type="date"
                                       wire:model="tanggal"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#0052FF] bg-white text-gray-900">
                                @error('tanggal') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nominal Modal Awal -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nominal Modal Awal (Cash) *
                                </label>
                                <x-input-rupiah wire:model="nominal" />
                                @error('nominal') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Keterangan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Catatan Tambahan
                                </label>
                                <input type="text"
                                       wire:model="keterangan"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#0052FF] bg-white text-gray-900">
                                @error('keterangan') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-xs font-extrabold text-white shadow-md shadow-blue-900/20 transition-all cursor-pointer flex items-center space-x-2">
                                <svg wire:loading wire:target="save" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Modal Awal' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

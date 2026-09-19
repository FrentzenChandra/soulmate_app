<div class="space-y-6">

    <!-- Page Header & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <span>Riwayat Tutup Kas</span>
            </h1>
            <p class="text-xs text-gray-500 font-medium mt-1">Histori penutupan buku harian kasir (Closing), rekonsiliasi kas sistem, dan catatan selisih fisik.</p>
        </div>

        <a href="{{ route('transaksi.harian') }}"
           class="px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span>Tutup Kas Hari Ini</span>
        </a>
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
                   placeholder="Cari nama petugas, catatan closing, status..."
                   class="w-full pl-10 pr-4 py-2 text-xs font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] focus:border-transparent transition-all">
        </div>

        <!-- Filter Tanggal -->
        <div class="flex items-center gap-2">
            <input type="date"
                   wire:model.live="startDate"
                   class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">
            <span class="text-xs font-bold text-gray-400">s.d</span>
            <input type="date"
                   wire:model.live="endDate"
                   class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto" wire:loading.class="opacity-60">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-black uppercase text-gray-500 tracking-wider">
                        <x-th-sort field="tanggal" :sortField="$sortField" :sortDirection="$sortDirection">Waktu Tutup</x-th-sort>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4 text-right">Modal Awal</th>
                        <th class="py-3 px-4 text-right">Penjualan Cash</th>
                        <th class="py-3 px-4 text-right">Servis Cash</th>
                        <th class="py-3 px-4 text-right">Kas Keluar</th>
                        <x-th-sort field="kas_sistem" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Kas Sistem</x-th-sort>
                        <x-th-sort field="kas_fisik" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Kas Fisik</x-th-sort>
                        <x-th-sort field="selisih" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Selisih</x-th-sort>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-16">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold text-gray-800">
                    <x-table-skeleton :columns="11" :rows="5" target="search, startDate, endDate, sortField, sortDirection" />
                    @forelse($items as $item)
                        <tr class="hover:bg-purple-50/20 transition-colors" wire:loading.remove wire:target="search, startDate, endDate, sortField, sortDirection">
                            <td class="py-3 px-4 text-gray-600 font-mono text-[11px]">
                                <div>{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}</div>
                                <div class="text-[10px] text-gray-400 font-normal">{{ $item->waktu_tutup ? $item->waktu_tutup->format('H:i') : '' }} WIB</div>
                            </td>
                            <td class="py-3 px-4 font-bold text-gray-900">
                                {{ $item->user?->name ?? 'Kasir' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-600">
                                Rp {{ number_format($item->modal_awal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-[#5E17EB]">
                                Rp {{ number_format($item->total_penjualan_cash, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-[#0052FF]">
                                Rp {{ number_format($item->total_servis_cash, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-rose-600">
                                Rp {{ number_format($item->total_kas_keluar_cash, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($item->kas_sistem, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black text-gray-900">
                                Rp {{ number_format($item->kas_fisik, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black {{ $item->selisih == 0 ? 'text-emerald-600' : ($item->selisih < 0 ? 'text-rose-600' : 'text-blue-600') }}">
                                Rp {{ number_format($item->selisih, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase {{ $item->status_selisih === 'klop' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($item->status_selisih === 'minus' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                                    {{ strtoupper($item->status_selisih) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button"
                                        wire:click="openDetail({{ $item->id }})"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-[#5E17EB] hover:bg-purple-50 transition-colors cursor-pointer"
                                        title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr wire:loading.remove wire:target="search, startDate, endDate, sortField, sortDirection">
                            <td colspan="11" class="py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <p class="text-xs font-bold text-gray-600">Tidak Ada Riwayat Tutup Kas</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Belum ada riwayat closing kasir pada periode ini.</p>
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
    <!-- MODAL DETAIL TUTUP KAS                         -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedDetail)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeDetail"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#0E1726] px-6 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-white">
                                Rincian Tutup Kas ({{ $selectedDetail->tanggal ? $selectedDetail->tanggal->format('d/m/Y') : '-' }})
                            </h3>
                        </div>
                        <button type="button" wire:click="closeDetail" class="text-gray-400 hover:text-white cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-3 text-xs bg-gray-50 p-4 rounded-2xl border border-gray-200">
                            <div>
                                <span class="text-gray-400 block font-bold text-[10px] uppercase">Petugas Closing</span>
                                <span class="font-bold text-gray-900">{{ $selectedDetail->user?->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block font-bold text-[10px] uppercase">Waktu Tutup</span>
                                <span class="font-bold text-gray-900">{{ $selectedDetail->waktu_tutup ? $selectedDetail->waktu_tutup->format('d/m/Y H:i') : '-' }} WIB</span>
                            </div>
                        </div>

                        <!-- Data Finansial -->
                        <div class="p-4 rounded-2xl border border-gray-200 space-y-2 text-xs">
                            <div class="flex justify-between font-medium text-gray-600">
                                <span>Modal Awal (Cash):</span>
                                <span class="font-mono font-bold text-gray-900">Rp {{ number_format($selectedDetail->modal_awal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-medium text-gray-600">
                                <span>Penjualan Retail (Cash):</span>
                                <span class="font-mono font-bold text-[#5E17EB]">Rp {{ number_format($selectedDetail->total_penjualan_cash, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-medium text-gray-600">
                                <span>Penerimaan Servis (Cash):</span>
                                <span class="font-mono font-bold text-[#0052FF]">Rp {{ number_format($selectedDetail->total_servis_cash, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-medium text-rose-600">
                                <span>Kas Keluar Operasional (Cash):</span>
                                <span class="font-mono font-bold">&minus; Rp {{ number_format($selectedDetail->total_kas_keluar_cash, 0, ',', '.') }}</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200 flex justify-between font-black text-sm">
                                <span class="text-gray-800">Total Kas Sistem:</span>
                                <span class="font-mono text-gray-900">Rp {{ number_format($selectedDetail->kas_sistem, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-black text-sm">
                                <span class="text-gray-800">Total Kas Fisik:</span>
                                <span class="font-mono text-emerald-600">Rp {{ number_format($selectedDetail->kas_fisik, 0, ',', '.') }}</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200 flex justify-between font-black text-sm {{ $selectedDetail->selisih == 0 ? 'text-emerald-600' : ($selectedDetail->selisih < 0 ? 'text-rose-600' : 'text-blue-600') }}">
                                <span>Selisih Kas ({{ strtoupper($selectedDetail->status_selisih) }}):</span>
                                <span class="font-mono">Rp {{ number_format($selectedDetail->selisih, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Catatan Closing -->
                        @if($selectedDetail->catatan)
                            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs">
                                <span class="text-[10px] font-bold uppercase text-amber-800 tracking-wider block mb-1">Catatan Petugas</span>
                                <p class="text-amber-900 font-semibold leading-relaxed">{{ $selectedDetail->catatan }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end rounded-b-3xl">
                        <button type="button"
                                wire:click="closeDetail"
                                class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-extrabold text-white shadow-md shadow-purple-900/20 transition-all cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

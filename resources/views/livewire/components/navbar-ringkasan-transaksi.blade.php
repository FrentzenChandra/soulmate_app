<div class="relative" x-data="{ navSummaryOpen: false }" @click.outside="navSummaryOpen = false">
    <!-- Navbar Trigger Button -->
    <button type="button"
            @click="navSummaryOpen = !navSummaryOpen"
            class="px-2.5 py-1.5 rounded-xl bg-purple-50 text-[#5E17EB] border border-purple-200 text-xs font-black hover:bg-purple-100 transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs group"
            title="Ringkasan Transaksi">
        <svg class="w-4 h-4 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span class="hidden sm:inline">Ringkasan</span>
        <svg class="w-3.5 h-3.5 text-purple-400 group-hover:text-[#5E17EB] transition-transform duration-200"
             :class="navSummaryOpen ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Modal / Popover -->
    <div x-show="navSummaryOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="absolute right-0 mt-2 w-[340px] sm:w-[500px] bg-white rounded-2xl shadow-2xl shadow-purple-950/20 border border-gray-200 p-4 z-50 text-xs"
         style="display: none;">

        <!-- Header Modal -->
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#5E17EB] animate-pulse"></span>
                <span class="font-extrabold text-gray-900 text-sm">Ringkasan Akumulasi Toko</span>
            </div>
            <span class="text-[10px] font-mono text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md font-bold border border-purple-200">Superadmin</span>
        </div>

        <!-- Filter Periode Bar (Rentang, Bulanan, Tahunan) -->
        <div class="mb-3 p-2 bg-gray-50 rounded-xl border border-gray-200/80 space-y-2">
            <div class="flex items-center justify-between gap-1">
                <div class="inline-flex p-0.5 bg-gray-200/70 rounded-lg">
                    <button type="button"
                            wire:click="setPeriodeMode('range')"
                            class="px-2 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer {{ $periodeMode === 'range' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                        Rentang (s.d)
                    </button>
                    <button type="button"
                            wire:click="setPeriodeMode('bulanan')"
                            class="px-2 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer {{ $periodeMode === 'bulanan' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                        Bulanan
                    </button>
                    <button type="button"
                            wire:click="setPeriodeMode('tahunan')"
                            class="px-2 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer {{ $periodeMode === 'tahunan' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                        Tahunan
                    </button>
                </div>
            </div>

            <!-- Periode Controls -->
            <div class="pt-1">
                @if($periodeMode === 'range')
                    <div class="flex items-center gap-1.5">
                        <input type="date"
                               wire:model.live="startDate"
                               class="w-full py-1 px-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#5E17EB]">
                        <span class="text-[11px] font-bold text-gray-400">s.d</span>
                        <input type="date"
                               wire:model.live="endDate"
                               class="w-full py-1 px-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#5E17EB]">
                    </div>
                @elseif($periodeMode === 'bulanan')
                    <div class="flex items-center gap-1.5">
                        <select wire:model.live="filterBulan"
                                class="w-1/2 py-1 px-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#5E17EB] cursor-pointer">
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <select wire:model.live="filterTahun"
                                class="w-1/2 py-1 px-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#5E17EB] cursor-pointer">
                            @for($y = (int) now()->year + 1; $y >= 2023; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                @elseif($periodeMode === 'tahunan')
                    <div>
                        <select wire:model.live="filterTahun"
                                class="w-full py-1 px-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#5E17EB] cursor-pointer">
                            @for($y = (int) now()->year + 1; $y >= 2023; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2-Column Summary Table (Spreadsheet Match) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <!-- Kolom Kiri: Arus Pokok -->
            <div class="space-y-1.5 p-2.5 rounded-xl bg-gray-50 border border-gray-100 text-[11px]">
                <div class="flex justify-between items-center py-1 border-b border-gray-200/60 font-bold">
                    <span class="text-gray-600">Pendapatan (Bruto)</span>
                    <span class="font-mono font-black text-gray-900">Rp {{ number_format($totalOmzetAll, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-gray-200/60 font-bold">
                    <span class="text-gray-600">HPP (Modal)</span>
                    <span class="font-mono font-black text-gray-700">Rp {{ number_format($totalModalAll, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-gray-200/60 font-black bg-emerald-50 text-emerald-900 -mx-1 px-1 rounded">
                    <span>Nett (Margin)</span>
                    <span class="font-mono font-black text-emerald-600">Rp {{ number_format($totalMarginAll, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-gray-200/60 font-semibold text-gray-700">
                    <span>Jumlah Lunas</span>
                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($totalPemasukanLunasAll, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 font-semibold text-amber-700">
                    <span>Jumlah Bon</span>
                    <span class="font-mono font-black">Rp {{ number_format($totalBelumLunasAll, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Kolom Kanan: Penjualan vs Servis -->
            <div class="space-y-1.5 p-2.5 rounded-xl bg-gray-50 border border-gray-100 text-[11px]">
                <div class="flex justify-between items-center py-1 border-b border-gray-200/60 font-bold text-purple-900">
                    <span>Penjualan (Nett)</span>
                    <span class="font-mono font-black text-[#5E17EB]">Rp {{ number_format($marginPenjualanTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-gray-200/60 font-bold text-blue-900">
                    <span>Servis (Nett)</span>
                    <span class="font-mono font-black text-[#0052FF]">Rp {{ number_format($marginServisTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-gray-200/60 font-semibold text-gray-700">
                    <span>Penjualan Bruto</span>
                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($totalPenjualanBruto, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 font-semibold text-gray-700">
                    <span>Servis Bruto</span>
                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($totalServisBruto, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Kas Keluar & Laba Bersih Real -->
        <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-[11px] font-mono">
            <span class="text-gray-500">Kas Keluar: Rp {{ number_format($totalKasKeluarAll, 0, ',', '.') }}</span>
            <span class="font-black text-[#5E17EB]">Laba Bersih Real: Rp {{ number_format($labaBersihReal, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

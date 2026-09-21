<div class="space-y-6">

    <!-- Page Header & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0052FF] flex items-center justify-center border border-blue-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span>Transaksi Harian</span>
            </h1>
            <p class="text-xs text-gray-500 font-medium mt-1">Daftar transaksi real hari ini, omset operasional, rekonsiliasi kas laci kasir, dan audit saldo fisik.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Mode Switcher -->
            <div class="inline-flex p-1 bg-gray-100 rounded-xl">
                <button type="button"
                        wire:click="setPeriodeMode('range')"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'range' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                    Rentang (s.d)
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('bulanan')"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'bulanan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                    Bulanan
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('tahunan')"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'tahunan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                    Tahunan
                </button>
            </div>

            <!-- Inputs based on mode -->
            @if($periodeMode === 'range')
                <div class="flex items-center gap-2">
                    <input type="date"
                           wire:model.live="startDate"
                           class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">
                    <span class="text-xs font-bold text-gray-400">s.d</span>
                    <input type="date"
                           wire:model.live="endDate"
                           class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">
                </div>
            @elseif($periodeMode === 'bulanan')
                <div class="flex items-center gap-2">
                    <select wire:model.live="filterBulan"
                            class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
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
                            class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                        @for($y = (int) now()->year + 1; $y >= 2023; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            @elseif($periodeMode === 'tahunan')
                <div class="flex items-center gap-2">
                    <select wire:model.live="filterTahun"
                            class="py-1.5 px-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] cursor-pointer">
                        @for($y = (int) now()->year + 1; $y >= 2023; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            @endif

            <!-- Button Cek Kas Cepat / Audit Laci -->
            <button type="button"
                    wire:click="openCekKasModal"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Cek Kas Cepat / Audit Laci</span>
            </button>
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <!-- Modal Awal -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Modal Awal Laci</span>
            <span class="text-base font-black text-gray-800 mt-1 block font-mono">
                Rp {{ number_format($modalAwalNominal, 0, ',', '.') }}
            </span>
            <span class="text-[10px] text-gray-400 font-medium block mt-0.5">Saldo awal kas</span>
        </div>

        <!-- Penjualan Bruto & Cash -->
        <div class="bg-white p-3.5 rounded-2xl border border-purple-100 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Penjualan (Omset)</span>
            <span class="text-base font-black text-[#5E17EB] mt-1 block font-mono">
                Rp {{ number_format($totalPenjualanBruto, 0, ',', '.') }}
            </span>
            <div class="flex items-center justify-between text-[10px] font-mono mt-0.5">
                <span class="text-emerald-600 font-bold">Cash: Rp {{ number_format($totalPenjualanCash, 0, ',', '.') }}</span>
                <span class="text-amber-600 font-bold">Bon: Rp {{ number_format($totalPenjualanBon, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Servis Bruto & Cash -->
        <div class="bg-white p-3.5 rounded-2xl border border-blue-100 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Servis (Omset)</span>
            <span class="text-base font-black text-[#0052FF] mt-1 block font-mono">
                Rp {{ number_format($totalServisBruto, 0, ',', '.') }}
            </span>
            <div class="flex items-center justify-between text-[10px] font-mono mt-0.5">
                <span class="text-emerald-600 font-bold">Cash: Rp {{ number_format($totalServisCash, 0, ',', '.') }}</span>
                <span class="text-amber-600 font-bold">Bon: Rp {{ number_format($totalServisBelumLunas, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Total Bon / Belum Lunas -->
        <div class="bg-white p-3.5 rounded-2xl border border-amber-200/80 shadow-xs bg-amber-50/20">
            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Total Bon / Piutang</span>
            <span class="text-base font-black text-amber-600 mt-1 block font-mono">
                Rp {{ number_format($totalBelumLunasAll, 0, ',', '.') }}
            </span>
            <span class="text-[10px] text-amber-700/80 font-medium block mt-0.5">Belum dibayar hari ini</span>
        </div>

        <!-- Kas Keluar Cash -->
        <div class="bg-white p-3.5 rounded-2xl border border-rose-100 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Kas Keluar Cash</span>
            <span class="text-base font-black text-rose-600 mt-1 block font-mono">
                Rp {{ number_format($totalKasKeluarCash, 0, ',', '.') }}
            </span>
            <span class="text-[10px] text-gray-400 font-medium block mt-0.5">Biaya operasional laci</span>
        </div>

        <!-- Total Kas Laci Sistem -->
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-300 shadow-xs bg-emerald-50/30">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Kas Laci (Sistem)</span>
                <span class="text-[9px] font-extrabold text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded">Fisik</span>
            </div>
            <span class="text-base font-black text-emerald-600 mt-1 block font-mono">
                Rp {{ number_format($kasSistem, 0, ',', '.') }}
            </span>
            <div class="mt-1 pt-1 border-t border-emerald-200/60 text-[10px] text-gray-500 font-mono flex justify-between">
                <span>Saldo Kas Masuk:</span>
                <span class="font-bold text-gray-800">Rp {{ number_format($saldoKasBersih, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Histori Cek Kas / Audit Laci Hari Ini (Jika Ada) -->
    @if(isset($cekKasLogs) && $cekKasLogs->count() > 0)
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-gray-900">Histori Cek Kas / Audit Laci Hari Ini</h3>
                        <p class="text-[10px] text-gray-500 font-medium">Catatan snapshot kas fisik kasir tanpa mengunci transaksi</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-extrabold border border-emerald-100">
                    {{ $cekKasLogs->count() }}x Audit
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3">Jam</th>
                            <th class="py-2 px-3">Petugas</th>
                            <th class="py-2 px-3 text-right">Uang Sistem</th>
                            <th class="py-2 px-3 text-right">Uang Fisik</th>
                            <th class="py-2 px-3 text-center">Selisih</th>
                            <th class="py-2 px-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @foreach($cekKasLogs as $log)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-2 px-3 font-mono font-bold text-gray-700 whitespace-nowrap">
                                    {{ $log->waktu_cek ? $log->waktu_cek->format('H:i') : '-' }}
                                </td>
                                <td class="py-2 px-3 text-gray-900 font-semibold whitespace-nowrap">
                                    {{ $log->user?->name ?? 'Kasir' }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-gray-700 whitespace-nowrap">
                                    Rp {{ number_format($log->kas_sistem, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-black text-gray-900 whitespace-nowrap">
                                    Rp {{ number_format($log->kas_fisik, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    @if($log->selisih == 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            &bull; Pas (Rp 0)
                                        </span>
                                    @elseif($log->selisih < 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            &bull; Tekor (&minus;Rp {{ number_format(abs($log->selisih), 0, ',', '.') }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                            &bull; Lebih (+Rp {{ number_format($log->selisih, 0, ',', '.') }})
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-gray-600 text-[11px] truncate max-w-xs">
                                    {{ $log->catatan ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tabulator Table Container -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs"
         x-data="tabulatorHarianTable({
             tableData: @js($tableData)
         })"
         x-init="initTable()"
         wire:ignore>

        <div class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-1 max-w-md">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text"
                           x-model="quickSearch"
                           @input="applyFilter()"
                           class="w-full pl-9 pr-3 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] transition-all">
                </div>
                <button type="button"
                        @click="clearAllFilters"
                        class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors flex items-center space-x-1.5 cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 font-semibold flex-wrap">
                <!-- Toggle Group Tanggal -->
                <button type="button"
                        @click="toggleGrouping()"
                        class="px-2.5 py-1 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                        :class="groupByDate ? 'bg-purple-50 text-[#5E17EB] border-purple-200 shadow-2xs' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span x-text="groupByDate ? 'Group Tanggal (ON)' : 'Group Tanggal'"></span>
                </button>

                <!-- Font Size Selector -->
                <x-table-size-selector />

                <template x-if="selectedCount > 0">
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[11px] border border-emerald-200"
                          x-text="selectedCount + ' dipilih'"></span>
                </template>
                <span class="text-[11px] text-gray-400 font-medium hidden md:inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    Tarik garis kolom untuk melebarkan
                </span>
                <span x-text="'Total ' + rowCount + ' Baris'"></span>
            </div>
        </div>

        <div :class="'tabulator-size-' + tableSize + ' table-size-' + tableSize">
            <div x-ref="tableContainer" class="rounded-xl border border-gray-200 text-xs"></div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL CEK KAS CEPAT / AUDIT LACI               -->
    <!-- ============================================== -->
    @if($modalCekKasOpen || $modalTutupKasOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
             x-data="{
                 kasSistem: {{ $kasSistem }},
                 kasFisik: @entangle('kasFisik'),
                 get selisih() {
                     const fisik = Number(String(this.kasFisik).replace(/[^\d]/g, '') || 0);
                     return fisik - this.kasSistem;
                 }
             }">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeCekKasModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-emerald-600 px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    Cek Kas Cepat / Audit Laci
                                </h3>
                                <p class="text-xs text-emerald-100 font-medium mt-0.5">Snapshot saldo kas fisik vs sistem tanpa mengunci transaksi</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCekKasModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="prosesCekKas">
                        <div class="p-6 space-y-4">
                            <!-- Ringkasan Kas Sistem -->
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200/80 space-y-2 text-xs">
                                <div class="flex justify-between font-medium text-gray-600">
                                    <span>Modal Awal Laci:</span>
                                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($modalAwalNominal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium text-gray-600">
                                    <span>Penjualan Kasir (Cash):</span>
                                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($totalPenjualanCash, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium text-gray-600">
                                    <span>Penerimaan Servis (Cash):</span>
                                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($totalServisCash, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium text-rose-600">
                                    <span>Kas Keluar Operasional (Cash):</span>
                                    <span class="font-mono font-bold">&minus; Rp {{ number_format($totalKasKeluarCash, 0, ',', '.') }}</span>
                                </div>
                                <div class="pt-2 border-t border-gray-200 flex justify-between font-black text-sm">
                                    <span class="text-gray-800">Total Kas Sistem (Seharusnya):</span>
                                    <span class="font-mono text-emerald-600">Rp {{ number_format($kasSistem, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Input Kas Fisik -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Total Uang Fisik di Laci *
                                </label>
                                <x-input-rupiah wire:model="kasFisik" />
                                @error('kasFisik') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Indikator Selisih Realtime -->
                            <div class="p-3 rounded-xl border flex items-center justify-between"
                                 :class="{
                                     'bg-emerald-50 border-emerald-200 text-emerald-800': selisih === 0,
                                     'bg-rose-50 border-rose-200 text-rose-800': selisih < 0,
                                     'bg-blue-50 border-blue-200 text-blue-800': selisih > 0
                                 }">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                          x-text="selisih === 0 ? 'Status Kas: KLOP / PAS' : (selisih < 0 ? 'Status Kas: TEKOR / MINUS' : 'Status Kas: LEBIH / SURPLUS')"></span>
                                    <span class="text-xs font-black font-mono"
                                          x-text="'Rp ' + Number(Math.abs(selisih)).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="text-[11px] font-bold"
                                     x-text="selisih === 0 ? 'Sesuai Presisi' : (selisih < 0 ? 'Uang Kurang' : 'Uang Lebih')"></div>
                            </div>

                            <!-- Catatan Audit -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Catatan Audit (Opsional)
                                </label>
                                <textarea wire:model="catatanAudit"
                                          rows="2"
                                          class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900"></textarea>
                                @error('catatanAudit') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl">
                            <button type="button"
                                    wire:click="closeCekKasModal"
                                    class="px-4 py-2 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-extrabold text-white shadow-md shadow-emerald-900/20 transition-all cursor-pointer flex items-center space-x-1.5">
                                <svg wire:loading wire:target="prosesCekKas" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Simpan Catatan Cek Kas</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
window.tabulatorHarianTable = function(config) {
    return {
        table: null,
        quickSearch: '',
        rowCount: (config.tableData || []).length,
        selectedCount: 0,
        groupByDate: true,
        tableSize: localStorage.getItem('soulmate_table_size') || 'md',

        setTableSize(size) {
            this.tableSize = size;
            localStorage.setItem('soulmate_table_size', size);
            window.dispatchEvent(new CustomEvent('table-size-changed', { detail: size }));
            if (this.table) {
                this.table.redraw(true);
            }
        },

        toggleGrouping() {
            this.groupByDate = !this.groupByDate;
            if (this.table) {
                if (this.groupByDate) {
                    this.table.setGroupBy("tanggal_group");
                } else {
                    this.table.setGroupBy(false);
                }
            }
        },

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        initTable() {
            window.addEventListener('table-size-changed', (e) => {
                this.tableSize = e.detail;
                if (this.table) {
                    this.table.redraw(true);
                }
            });

            if (!window.Tabulator) {
                console.error('Tabulator not loaded');
                return;
            }

            const multiSelectFilterFunc = (headerValue, rowValue) => {
                if (!headerValue || (Array.isArray(headerValue) && headerValue.length === 0)) return true;
                if (Array.isArray(headerValue)) {
                    return headerValue.includes(rowValue);
                }
                return String(rowValue).toLowerCase() === String(headerValue).toLowerCase();
            };

            const initialData = config.tableData || [];

            this.table = new Tabulator(this.$refs.tableContainer, {
                data: initialData,
                layout: "fitColumns",
                responsiveLayout: false,
                resizableColumnFit: true,
                pagination: "local",
                paginationSize: 25,
                paginationSizeSelector: [15, 25, 50, 100],
                movableColumns: true,
                groupBy: this.groupByDate ? "tanggal_group" : false,
                groupHeader: function(value, count, data, group) {
                    let totalJual = data.reduce((sum, row) => sum + (Number(row.harga_jual) || 0), 0);
                    let formattedJual = 'Rp ' + Number(totalJual || 0).toLocaleString('id-ID');
                    return `
                        <div class="flex flex-wrap items-center justify-between w-full pr-3 py-0.5 text-xs font-bold text-gray-800">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#5E17EB]/10 text-[#5E17EB] font-black">${value}</span>
                                <span class="text-gray-500 font-medium text-[11px] font-mono">(${count} Transaksi)</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] font-mono">
                                <span class="text-purple-800 bg-purple-100/70 px-2 py-0.5 rounded-md border border-purple-200">TOTAL OMSET: <strong class="text-purple-700">${formattedJual}</strong></span>
                            </div>
                        </div>
                    `;
                },
                placeholder: "Tidak ada transaksi pada periode ini.",
                columns: [
                    { formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 35, minWidth: 35, maxWidth: 35, resizable: false },
                    { title: "Waktu", field: "tanggal", width: 110, hozAlign: "center", sorter: "string", headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="font-bold text-gray-700 font-mono text-[11px]">${cell.getValue()}</span>` },
                    { title: "No Transaksi", field: "no_transaksi", width: 125, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="font-mono font-extrabold text-gray-900 text-[11px] truncate block" title="${cell.getValue()}">${cell.getValue()}</span>` },
                    { title: "Transaksi", field: "tipe_label", width: 95, hozAlign: "center", headerFilter: "list", headerFilterParams: { valuesLookup: "all", clearable: true, multiselect: true }, headerFilterFunc: multiSelectFilterFunc, formatter: (cell) => {
                        const val = cell.getValue();
                        const isPJ = val === 'Penjualan';
                        return `<span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-black ${isPJ ? 'bg-purple-50 text-[#5E17EB] border border-purple-200' : 'bg-blue-50 text-[#0052FF] border border-blue-200'}">${val}</span>`;
                    }},
                    { title: "Pelanggan", field: "customer", widthGrow: 1, minWidth: 95, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="font-bold text-gray-800 text-[11px] truncate block" title="${cell.getValue()}">${cell.getValue()}</span>` },
                    { title: "Detail Barang & Servis", field: "item_detail", widthGrow: 2, minWidth: 150, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => {
                        const data = cell.getData();
                        const items = data.items_list || (cell.getValue() ? [cell.getValue()] : []);
                        if (!items || items.length === 0) return '<span class="text-gray-400 italic text-[11px]">-</span>';
                        return `<div class="tabulator-cell-list py-1 flex flex-col gap-1 text-[11px] leading-tight">` +
                            items.map(it => `<div class="flex items-start gap-1.5 text-gray-700"><span class="w-1.5 h-1.5 rounded-full bg-[#5E17EB] mt-1 shrink-0"></span><span class="font-medium">${it}</span></div>`).join('') +
                        `</div>`;
                    }},
                    { title: "Nilai (Omset)", field: "harga_jual", hozAlign: "right", width: 115, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="font-mono font-black text-gray-900 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Pajak", field: "pajak", hozAlign: "right", width: 115, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => {
                        const val = cell.getValue();
                        const data = cell.getData();
                        const tipe = data.pajak_tipe || (val > 0 ? 'PPN' : 'Non-PPN');
                        if (val > 0) {
                            return `<div class="flex flex-col items-end leading-tight">
                                <span class="font-mono font-black text-gray-900 text-[11px]">${this.formatRupiah(val)}</span>
                                <span class="text-[9px] font-extrabold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200/80 mt-0.5 whitespace-nowrap">${tipe}</span>
                            </div>`;
                        }
                        return `<div class="flex flex-col items-end leading-tight">
                            <span class="font-mono text-gray-400 text-[11px]">Rp 0</span>
                            <span class="text-[9px] font-semibold text-gray-400 mt-0.5 whitespace-nowrap">${tipe}</span>
                        </div>`;
                    }},
                    { title: "Status", field: "status", width: 75, hozAlign: "center", headerFilter: "list", headerFilterParams: { valuesLookup: "all", clearable: true, multiselect: true }, headerFilterFunc: multiSelectFilterFunc, formatter: (cell) => {
                        const isLunas = cell.getValue() === 'Lunas';
                        return `<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black ${isLunas ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'}">${cell.getValue()}</span>`;
                    }},
                    { title: "Metode", field: "metode_bayar", width: 85, hozAlign: "center", headerFilter: "list", headerFilterParams: { valuesLookup: "all", clearable: true, multiselect: true }, headerFilterFunc: multiSelectFilterFunc, formatter: (cell) => {
                        const isCash = cell.getData().is_cash;
                        return `<span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold ${isCash ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200'}">${cell.getValue()}</span>`;
                    }},
                    { title: "Petugas", field: "petugas", widthGrow: 1, minWidth: 90, headerFilter: "list", headerFilterParams: { valuesLookup: "all", clearable: true, multiselect: true }, headerFilterFunc: multiSelectFilterFunc, formatter: (cell) => `<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 font-bold text-[10px] truncate max-w-full" title="${cell.getValue()}">${cell.getValue()}</span>` },
                ]
            });

            window.tabulatorInstance = this.table;

            this.table.on("dataFiltered", (filters, rows) => {
                this.rowCount = rows.length;
            });

            this.table.on("rowSelectionChanged", (data, rows) => {
                this.selectedCount = rows.length;
            });

            Livewire.on('harian-table-updated', ({ data }) => {
                if (this.table && Array.isArray(data)) {
                    this.table.setData(data);
                    this.rowCount = data.length;
                    this.selectedCount = 0;
                }
            });
        },

        applyFilter() {
            if (!this.table) return;
            const term = this.quickSearch.toLowerCase();
            if (!term) {
                this.table.clearFilter();
                return;
            }

            this.table.setFilter((data) => {
                return (data.no_transaksi && data.no_transaksi.toLowerCase().includes(term)) ||
                       (data.customer && data.customer.toLowerCase().includes(term)) ||
                       (data.item_detail && data.item_detail.toLowerCase().includes(term)) ||
                       (data.metode_bayar && data.metode_bayar.toLowerCase().includes(term)) ||
                       (data.status && data.status.toLowerCase().includes(term)) ||
                       (data.petugas && data.petugas.toLowerCase().includes(term));
            });
        },

        clearAllFilters() {
            this.quickSearch = '';
            if (this.table) {
                this.table.clearFilter(true);
                this.table.clearHeaderFilter();
                this.table.deselectRow();
                this.rowCount = this.table.getData().length;
                this.selectedCount = 0;
            }
        }
    };
};
</script>
@endpush

<div class="p-4 sm:p-6 max-w-[1600px] mx-auto space-y-4">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[11px] font-extrabold uppercase tracking-wider border border-emerald-200/60">
                    Histori Kasir
                </span>
                <h1 class="text-xl font-black text-gray-900 tracking-tight">Audit Laci</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 font-medium">
                Catatan riwayat snapshot kas fisik vs kas sistem untuk audit kasir
            </p>
        </div>

        <!-- Filter Periode & Action Button -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Mode Selector Switcher -->
            <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200">
                <button type="button"
                        wire:click="setPeriodeMode('range')"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ $periodeMode === 'range' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                    Rentang
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('bulanan')"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ $periodeMode === 'bulanan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                    Bulanan
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('tahunan')"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ $periodeMode === 'tahunan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                    Tahunan
                </button>
            </div>

            <!-- Dynamic Date Controls -->
            @if($periodeMode === 'range')
                <div class="flex items-center gap-1.5">
                    <input type="date"
                           wire:model.live="startDate"
                           class="px-2.5 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB]">
                    <span class="text-xs text-gray-400 font-bold">s.d</span>
                    <input type="date"
                           wire:model.live="endDate"
                           class="px-2.5 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB]">
                </div>
            @elseif($periodeMode === 'bulanan')
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="filterBulan"
                            class="px-2.5 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB]">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ sprintf('%02d', $m) }}">
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    <select wire:model.live="filterTahun"
                            class="px-2.5 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB]">
                        @foreach(range(now()->year, now()->year - 4) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($periodeMode === 'tahunan')
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="filterTahun"
                            class="px-2.5 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB]">
                        @foreach(range(now()->year, now()->year - 4) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filter Status Selisih -->
            <select wire:model.live="statusFilter"
                    class="px-2.5 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB]">
                <option value="semua">Semua Status</option>
                <option value="klop">Klop Sesuai</option>
                <option value="minus">Minus Tekor</option>
                <option value="lebih">Lebih Surplus</option>
            </select>

            <!-- Button Audit Laci Sekarang -->
            <button type="button"
                    wire:click="openAuditModal"
                    wire:loading.attr="disabled"
                    class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-900/20 transition-all flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Audit Laci Sekarang</span>
            </button>
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Total Frekuensi Audit -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Frekuensi Audit</span>
            <span class="text-lg font-black text-gray-900 mt-1 block font-mono">
                {{ $totalAudit }}x
            </span>
            <span class="text-[10px] text-gray-400 font-medium block mt-0.5">Pengecekan laci kasir</span>
        </div>

        <!-- Total Audit Klop -->
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-100 shadow-xs bg-emerald-50/20">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Audit Klop</span>
            <span class="text-lg font-black text-emerald-600 mt-1 block font-mono">
                {{ $totalKlop }}x
            </span>
            <span class="text-[10px] text-emerald-600/80 font-medium block mt-0.5">Fisik sesuai sistem (Rp 0)</span>
        </div>

        <!-- Akumulasi Minus -->
        <div class="bg-white p-3.5 rounded-2xl border border-rose-100 shadow-xs bg-rose-50/20">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Akumulasi Minus</span>
                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-rose-100 text-rose-800 rounded">{{ $totalMinusCount }}x</span>
            </div>
            <span class="text-lg font-black text-rose-600 mt-1 block font-mono">
                &minus; Rp {{ number_format($totalMinusNominal, 0, ',', '.') }}
            </span>
            <span class="text-[10px] text-rose-600/80 font-medium block mt-0.5">Total fisik kurang</span>
        </div>

        <!-- Akumulasi Lebih -->
        <div class="bg-white p-3.5 rounded-2xl border border-blue-100 shadow-xs bg-blue-50/20">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-blue-700 uppercase tracking-wider block">Akumulasi Lebih</span>
                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-100 text-blue-800 rounded">{{ $totalLebihCount }}x</span>
            </div>
            <span class="text-lg font-black text-[#0052FF] mt-1 block font-mono">
                + Rp {{ number_format($totalLebihNominal, 0, ',', '.') }}
            </span>
            <span class="text-[10px] text-blue-600/80 font-medium block mt-0.5">Total fisik surplus</span>
        </div>
    </div>

    <!-- Tabulator Table Container -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs"
         x-data="tabulatorAuditTable({
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
    <!-- MODAL AUDIT LACI CEPAT                         -->
    <!-- ============================================== -->
    @if($modalAuditOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
             x-data="{
                 kasSistem: {{ $liveKasSistem }},
                 kasFisik: @entangle('kasFisik'),
                 get selisih() {
                     const fisik = Number(String(this.kasFisik).replace(/[^\d]/g, '') || 0);
                     return fisik - this.kasSistem;
                 }
             }">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeAuditModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-emerald-600 px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    Audit Laci Kasir
                                </h3>
                                <p class="text-xs text-emerald-100 font-medium mt-0.5">Snapshot saldo kas fisik vs kas sistem hari ini</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeAuditModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="prosesAudit">
                        <div class="p-6 space-y-4">
                            <!-- Ringkasan Kas Sistem -->
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200/80 space-y-2 text-xs">
                                <div class="flex justify-between font-medium text-gray-600">
                                    <span>Modal Awal:</span>
                                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($modalAwalToday, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium text-gray-600">
                                    <span>Penjualan Tunai:</span>
                                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($penjualanCashToday, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium text-gray-600">
                                    <span>Servis Tunai:</span>
                                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($servisCashToday, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium text-rose-600">
                                    <span>Kas Keluar Tunai:</span>
                                    <span class="font-mono font-bold">&minus; Rp {{ number_format($kasKeluarCashToday, 0, ',', '.') }}</span>
                                </div>
                                <div class="pt-2 border-t border-gray-200 flex justify-between font-black text-sm">
                                    <span class="text-gray-800">Kas Sistem:</span>
                                    <span class="font-mono text-emerald-600">Rp {{ number_format($liveKasSistem, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Input Kas Fisik -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Uang Fisik di Laci
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
                                          x-text="selisih === 0 ? 'Status: KLOP / PAS' : (selisih < 0 ? 'Status: MINUS' : 'Status: LEBIH')"></span>
                                    <span class="text-xs font-medium opacity-90"
                                          x-text="selisih === 0 ? 'Uang fisik sama dengan hitungan sistem' : (selisih < 0 ? 'Uang fisik kurang dari sistem' : 'Uang fisik lebih dari sistem')"></span>
                                </div>
                                <span class="font-mono font-black text-sm"
                                      :class="{
                                          'text-emerald-700': selisih === 0,
                                          'text-rose-700': selisih < 0,
                                          'text-blue-700': selisih > 0
                                      }"
                                      x-text="selisih === 0 ? 'Rp 0' : (selisih < 0 ? '- Rp ' + Math.abs(selisih).toLocaleString('id-ID') : '+ Rp ' + selisih.toLocaleString('id-ID'))">
                                </span>
                            </div>

                            <!-- Catatan Audit -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Catatan
                                </label>
                                <textarea wire:model="catatanAudit"
                                          rows="2"
                                          class="w-full text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 rounded-b-3xl border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button"
                                    wire:click="closeAuditModal"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition-all cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-900/20 transition-all flex items-center gap-2 cursor-pointer">
                                <span wire:loading.remove wire:target="prosesAudit">Simpan Audit Laci</span>
                                <span wire:loading wire:target="prosesAudit">Menyimpan...</span>
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
window.tabulatorAuditTable = function(config) {
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
                this.table.redraw(true);
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
                groupStartOpen: true,
                groupToggleElement: "header",
                groupHeader: function(value, count, data, group) {
                    let totalMinus = data.filter(r => r.status_selisih === 'Minus').length;
                    return `
                        <div class="flex flex-wrap items-center justify-between w-full pr-3 py-0.5 text-xs font-bold text-gray-800 cursor-pointer select-none">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#5E17EB]/10 text-[#5E17EB] font-black">${value}</span>
                                <span class="text-gray-500 font-medium text-[11px] font-mono">(${count}x Audit)</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px]">
                                ${totalMinus > 0 ? `<span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-bold border border-rose-200">${totalMinus}x Minus</span>` : '<span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">Semua Klop</span>'}
                            </div>
                        </div>
                    `;
                },
                placeholder: "Belum ada catatan audit laci pada periode ini.",
                columns: [
                  //  { formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 35, minWidth: 35, maxWidth: 35, resizable: false },
                    { title: "Waktu", field: "waktu", width: 120, hozAlign: "center", sorter: "string", headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="font-bold text-gray-700 font-mono text-[11px]">${cell.getValue()}</span>` },
                    { title: "Petugas", field: "petugas", widthGrow: 1, minWidth: 100, headerFilter: "list", headerFilterParams: { valuesLookup: "all", clearable: true, multiselect: true }, headerFilterFunc: multiSelectFilterFunc, formatter: (cell) => `<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 font-bold text-[11px]">${cell.getValue()}</span>` },
                    { title: "Modal Awal", field: "modal_awal", hozAlign: "right", width: 110, formatter: (cell) => `<span class="font-mono text-gray-600 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Penjualan", field: "penjualan_cash", hozAlign: "right", width: 110, formatter: (cell) => `<span class="font-mono text-purple-700 font-bold text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Servis", field: "servis_cash", hozAlign: "right", width: 110, formatter: (cell) => `<span class="font-mono text-blue-700 font-bold text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Kas Keluar", field: "kas_keluar_cash", hozAlign: "right", width: 110, formatter: (cell) => `<span class="font-mono text-rose-600 font-bold text-[11px]">${cell.getValue() > 0 ? '&minus; ' : ''}${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Kas Sistem", field: "kas_sistem", hozAlign: "right", width: 120, formatter: (cell) => `<span class="font-mono font-extrabold text-gray-900 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Kas Fisik", field: "kas_fisik", hozAlign: "right", width: 120, formatter: (cell) => `<span class="font-mono font-black text-emerald-700 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Selisih", field: "selisih", hozAlign: "right", width: 130, formatter: (cell) => {
                        const val = Number(cell.getValue() || 0);
                        const data = cell.getData();
                        if (val === 0) {
                            return `<span class="inline-flex px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-black text-[10px] border border-emerald-200">KLOP (Rp 0)</span>`;
                        } else if (val < 0) {
                            return `<span class="inline-flex px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-black text-[10px] border border-rose-200">&minus; ${this.formatRupiah(Math.abs(val))}</span>`;
                        } else {
                            return `<span class="inline-flex px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-black text-[10px] border border-blue-200">+ ${this.formatRupiah(val)}</span>`;
                        }
                    }},
                    { title: "Status", field: "status_selisih", width: 85, hozAlign: "center", headerFilter: "list", headerFilterParams: { valuesLookup: "all", clearable: true, multiselect: true }, headerFilterFunc: multiSelectFilterFunc, formatter: (cell) => {
                        const val = cell.getValue();
                        if (val === 'Klop') {
                            return `<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-black text-[10px]">Klop</span>`;
                        } else if (val === 'Minus') {
                            return `<span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-black text-[10px]">Minus</span>`;
                        } else {
                            return `<span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-black text-[10px]">Lebih</span>`;
                        }
                    }},
                    { title: "Catatan", field: "catatan", widthGrow: 2, minWidth: 140, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="text-gray-700 text-[11px] truncate block" title="${cell.getValue()}">${cell.getValue()}</span>` },
                ]
            });

            window.tabulatorInstance = this.table;

            this.table.on("dataFiltered", (filters, rows) => {
                this.rowCount = rows.length;
            });

            this.table.on("rowSelectionChanged", (data, rows) => {
                this.selectedCount = rows.length;
            });

            Livewire.on('audit-table-updated', ({ data }) => {
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
                return (data.petugas && data.petugas.toLowerCase().includes(term)) ||
                       (data.catatan && data.catatan.toLowerCase().includes(term)) ||
                       (data.status_selisih && data.status_selisih.toLowerCase().includes(term)) ||
                       (data.waktu && data.waktu.toLowerCase().includes(term));
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

<div class="space-y-6">

    <!-- Page Header & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span>Transaksi FULL</span>
            </h1>
            <p class="text-xs text-gray-500 font-medium mt-1">Rekapitulasi lengkap transaksi penjualan retail, penerimaan servis, modal HPP, dan kas tunai bersih.</p>
        </div>

        <!-- Filter Periode (s.d, Bulanan, Tahunan) -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
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
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- OMZET - KAS KELUAR -->
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-200/80 shadow-xs relative overflow-hidden bg-emerald-50/20">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">OMZET - KAS KELUAR</span>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-extrabold text-[9px] uppercase">Arus Kas</span>
            </div>

            <div class="mt-2 space-y-2">
                <!-- Kas Real Lunas -->
                <div class="p-2 rounded-xl bg-emerald-50/60 border border-emerald-100">
                    <div class="flex items-center justify-between text-xs font-bold text-emerald-900">
                        <span>Kas Real (Lunas):</span>
                        <span class="text-emerald-600 font-black font-mono">Rp {{ number_format($saldoBersihReal, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[10px] text-gray-500 font-mono">
                        <span>+ Masuk Lunas: Rp {{ number_format($totalPemasukanLunasAll, 0, ',', '.') }}</span>
                        <span>&minus; Kas Keluar: Rp {{ number_format($totalKasKeluarAll, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Saldo + Piutang / Bon -->
                <div class="p-2 rounded-xl bg-purple-50/60 border border-purple-100">
                    <div class="flex items-center justify-between text-xs font-bold text-purple-900">
                        <span>Saldo (+ Piutang Bon):</span>
                        <span class="text-[#5E17EB] font-black font-mono">Rp {{ number_format($saldoBersihPlusBon, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[10px] text-gray-500 font-mono">
                        <span>+ Masuk Lunas: Rp {{ number_format($totalPemasukanLunasAll, 0, ',', '.') }}</span>
                        <span class="text-amber-700 font-semibold">+ Bon: Rp {{ number_format($totalBelumLunasAll, 0, ',', '.') }}</span>
                        <span>&minus; Kas Keluar: Rp {{ number_format($totalKasKeluarAll, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penjualan Produk -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Penjualan Produk</span>
            <div class="mt-1.5 space-y-0.5">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-gray-500">Lunas:</span>
                    <span class="text-gray-900 font-mono">Rp {{ number_format($totalPenjualanLunasAll, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-amber-600">Bon:</span>
                    <span class="text-amber-600 font-mono">Rp {{ number_format($totalPenjualanBon, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-1.5 pt-1.5 border-t border-gray-100 space-y-0.5 text-[10px]">
                <div class="flex justify-between font-semibold text-emerald-800">
                    <span>Nett Lunas:</span>
                    <span class="font-mono font-bold text-emerald-600">Rp {{ number_format($marginPenjualanLunas, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-semibold text-purple-800">
                    <span>Nett (+ Bon):</span>
                    <span class="font-mono font-bold text-[#5E17EB]">Rp {{ number_format($marginPenjualanTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Layanan Servis -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Layanan Servis</span>
            <div class="mt-1.5 space-y-0.5">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-gray-500">Lunas:</span>
                    <span class="text-[#0052FF] font-mono">Rp {{ number_format($totalServisLunasAll, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-amber-600">Belum Lunas:</span>
                    <span class="text-amber-600 font-mono">Rp {{ number_format($totalServisBelumLunas, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-1.5 pt-1.5 border-t border-gray-100 space-y-0.5 text-[10px]">
                <div class="flex justify-between font-semibold text-blue-800">
                    <span>Nett Lunas:</span>
                    <span class="font-mono font-bold text-[#0052FF]">Rp {{ number_format($marginServisLunas, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-semibold text-purple-800">
                    <span>Nett (+ Bon):</span>
                    <span class="font-mono font-bold text-[#5E17EB]">Rp {{ number_format($marginServisTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Bon / Piutang Belum Lunas -->
        <div class="bg-white p-3.5 rounded-2xl border border-amber-200/80 shadow-xs bg-amber-50/20">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Total Piutang & Bon</span>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-extrabold text-[9px] uppercase">Belum Lunas</span>
            </div>
            <span class="text-lg font-black text-amber-600 mt-1.5 block font-mono">
                Rp {{ number_format($totalBelumLunasAll, 0, ',', '.') }}
            </span>
            <span class="text-[10px] text-amber-700/80 font-medium block mt-0.5">
                Produk & Servis belum dibayar
            </span>
        </div>

        <!-- Total Laba Bersih (Nett Profit) -->
        <div class="bg-white p-3.5 rounded-2xl border border-purple-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Laba Bersih (Nett)</span>
                <span class="px-1.5 py-0.5 rounded bg-purple-100 text-[#5E17EB] font-extrabold text-[9px] uppercase">Margin &minus; Kas Keluar</span>
            </div>
            <div class="mt-2 space-y-2">
                <!-- Laba Real Lunas -->
                <div class="p-2 rounded-xl bg-emerald-50/60 border border-emerald-100">
                    <div class="flex items-center justify-between text-xs font-bold text-emerald-900">
                        <span>Laba Real (Lunas):</span>
                        <span class="text-emerald-600 font-black font-mono">Rp {{ number_format($labaBersihReal, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[10px] text-gray-500 font-mono">
                        <span>+ Margin Lunas: Rp {{ number_format($totalMarginLunas, 0, ',', '.') }}</span>
                        <span>&minus; Kas Keluar: Rp {{ number_format($totalKasKeluarAll, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Laba + Piutang / Bon -->
                <div class="p-2 rounded-xl bg-purple-50/60 border border-purple-100">
                    <div class="flex items-center justify-between text-xs font-bold text-purple-900">
                        <span>Laba (+ Piutang Bon):</span>
                        <span class="text-[#5E17EB] font-black font-mono">Rp {{ number_format($labaBersihPlusBon, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5 text-[10px] text-gray-500 font-mono">
                        <span>+ Margin Lunas: Rp {{ number_format($totalMarginLunas, 0, ',', '.') }}</span>
                        <span class="text-amber-700 font-semibold">+ Margin Bon: Rp {{ number_format($totalMarginBon, 0, ',', '.') }}</span>
                        <span>&minus; Kas Keluar: Rp {{ number_format($totalKasKeluarAll, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar Filters & Export Buttons -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3"
         x-data="{
             tipe: @entangle('tipeFilter'),
             exportCSV() { window.tabulatorInstance?.download('csv', 'transaksi-full.csv'); },
             exportJSON() { window.tabulatorInstance?.download('json', 'transaksi-full.json'); },
             printTable() { window.tabulatorInstance?.print(false, true); }
         }">

        <!-- Filter Jenis Transaksi -->
        <div class="flex items-center gap-2">
            <button type="button"
                    wire:click="$set('tipeFilter', 'semua')"
                    class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all cursor-pointer {{ $tipeFilter === 'semua' ? 'bg-[#5E17EB] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua Transaksi
            </button>
            <button type="button"
                    wire:click="$set('tipeFilter', 'penjualan')"
                    class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all cursor-pointer {{ $tipeFilter === 'penjualan' ? 'bg-[#5E17EB] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Penjualan Produk
            </button>
            <button type="button"
                    wire:click="$set('tipeFilter', 'servis')"
                    class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all cursor-pointer {{ $tipeFilter === 'servis' ? 'bg-[#0052FF] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Servis Selesai
            </button>
        </div>

        <!-- Quick Actions / Export -->
        <div class="flex items-center gap-2">
            <button type="button"
                    @click="clearAllFilters"
                    class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reset Filter</span>
            </button>
            <button type="button"
                    @click="exportCSV"
                    class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export CSV</span>
            </button>
            <button type="button"
                    @click="printTable"
                    class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Rekap</span>
            </button>
        </div>
    </div>

    <!-- Tabulator Table Container -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs"
         x-data="tabulatorFullTable({
             tableData: @js($tableData)
         })"
         x-init="initTable()"
         wire:ignore>

        <div class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text"
                       x-model="quickSearch"
                       @input="applyFilter()"
                       class="w-full pl-9 pr-3 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] transition-all">
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 font-semibold flex-wrap">
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

</div>

@push('scripts')
<script>
window.tabulatorFullTable = function(config) {
    return {
        table: null,
        quickSearch: '',
        rowCount: (config.tableData || []).length,
        selectedCount: 0,
        tableSize: localStorage.getItem('soulmate_table_size') || 'md',

        setTableSize(size) {
            this.tableSize = size;
            localStorage.setItem('soulmate_table_size', size);
            window.dispatchEvent(new CustomEvent('table-size-changed', { detail: size }));
            if (this.table) {
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

            this.table = new Tabulator(this.$refs.tableContainer, {
                data: config.tableData,
                layout: "fitColumns",
                responsiveLayout: false,
                resizableColumnFit: true,
                pagination: "local",
                paginationSize: 15,
                paginationSizeSelector: [15, 25, 50, 100],
                movableColumns: true,
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
                    { title: "Detail Barang & Jasa", field: "item_detail", widthGrow: 2, minWidth: 150, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => {
                        const data = cell.getData();
                        const items = data.items_list || (cell.getValue() ? [cell.getValue()] : []);
                        if (!items || items.length === 0) return '<span class="text-gray-400 italic text-[11px]">-</span>';
                        return `<div class="tabulator-cell-list py-1 flex flex-col gap-1 text-[11px] leading-tight">` +
                            items.map(it => `<div class="flex items-start gap-1.5 text-gray-700"><span class="w-1.5 h-1.5 rounded-full bg-[#5E17EB] mt-1 shrink-0"></span><span class="font-medium">${it}</span></div>`).join('') +
                        `</div>`;
                    }},
                    { title: "Modal (HPP)", field: "modal", hozAlign: "right", width: 100, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="font-mono font-semibold text-gray-500 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Harga Jual", field: "harga_jual", hozAlign: "right", width: 105, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => `<span class="font-mono font-black text-gray-900 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Laba (Nett)", field: "margin", hozAlign: "right", width: 105, headerFilter: "input", headerFilterPlaceholder: "", formatter: (cell) => {
                        const val = cell.getValue();
                        return `<span class="font-mono font-black text-[11px] ${val >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${this.formatRupiah(val)}</span>`;
                    }},
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

            this.$watch('$wire.tableData', (newData) => {
                if (this.table && Array.isArray(newData)) {
                    this.table.setData(newData);
                    this.rowCount = newData.length;
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

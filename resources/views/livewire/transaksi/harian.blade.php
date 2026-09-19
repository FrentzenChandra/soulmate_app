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
            <p class="text-xs text-gray-500 font-medium mt-1">Daftar transaksi real hari ini, rekonsiliasi kas laci kasir, dan penutupan buku harian (Closing).</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Date Picker -->
            <input type="date"
                   wire:model.live="selectedDate"
                   class="py-2 px-3 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">

            <!-- Button Tutup Kasir -->
            <button type="button"
                    wire:click="openTutupKasModal"
                    wire:loading.attr="disabled"
                    class="px-4 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Tutup Kasir</span>
            </button>
        </div>
    </div>

    <!-- Banner Status Closing Kasir Hari Ini (Jika Sudah Tutup Kas) -->
    @if($this->todayTutupKas)
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-black text-emerald-900">Kasir Sudah Ditutup (Closing)</span>
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $this->todayTutupKas->status_selisih === 'klop' ? 'bg-emerald-200 text-emerald-800' : ($this->todayTutupKas->status_selisih === 'minus' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ strtoupper($this->todayTutupKas->status_selisih) }}
                        </span>
                    </div>
                    <p class="text-[11px] text-emerald-700 mt-0.5">
                        Ditutup oleh <span class="font-bold">{{ $this->todayTutupKas->user?->name }}</span> pada {{ $this->todayTutupKas->waktu_tutup ? $this->todayTutupKas->waktu_tutup->format('H:i') : '-' }} WIB &bull; Kas Fisik: <span class="font-mono font-bold">Rp {{ number_format($this->todayTutupKas->kas_fisik, 0, ',', '.') }}</span> (Selisih: Rp {{ number_format($this->todayTutupKas->selisih, 0, ',', '.') }})
                    </p>
                </div>
            </div>
            <a href="{{ route('tutup-kas') }}" class="px-3 py-1.5 rounded-xl bg-white border border-emerald-300 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition-colors text-center shrink-0">
                Lihat Riwayat Closing
            </a>
        </div>
    @endif

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Modal Awal -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Modal Awal Laci</span>
            <span class="text-base font-black text-gray-800 mt-1 block font-mono">
                Rp {{ number_format($modalAwalNominal, 0, ',', '.') }}
            </span>
        </div>

        <!-- Penjualan Cash -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Penjualan Cash</span>
            <span class="text-base font-black text-[#5E17EB] mt-1 block font-mono">
                Rp {{ number_format($totalPenjualanCash, 0, ',', '.') }}
            </span>
        </div>

        <!-- Servis Cash -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Servis Cash</span>
            <span class="text-base font-black text-[#0052FF] mt-1 block font-mono">
                Rp {{ number_format($totalServisCash, 0, ',', '.') }}
            </span>
        </div>

        <!-- Kas Keluar Cash -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Kas Keluar Cash</span>
            <span class="text-base font-black text-rose-600 mt-1 block font-mono">
                Rp {{ number_format($totalKasKeluarCash, 0, ',', '.') }}
            </span>
        </div>

        <!-- Total Kas Sistem -->
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-300 shadow-xs bg-emerald-50/20">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Kas Laci (Sistem)</span>
            <span class="text-base font-black text-emerald-600 mt-1 block font-mono">
                Rp {{ number_format($kasSistem, 0, ',', '.') }}
            </span>
        </div>
    </div>

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
                           placeholder="Cari transaksi hari ini..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] transition-all">
                </div>
                <button type="button"
                        @click="clearAllFilters"
                        class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors flex items-center space-x-1.5 cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 font-semibold">
                <template x-if="selectedCount > 0">
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-[#5E17EB] font-bold text-[11px] border border-purple-200"
                          x-text="selectedCount + ' dipilih'"></span>
                </template>
                <span class="text-[11px] text-gray-400 font-medium hidden md:inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    Tarik garis kolom untuk melebarkan
                </span>
                <span x-text="'Total ' + rowCount + ' Transaksi Hari Ini'"></span>
            </div>
        </div>

        <div x-ref="tableContainer" class="rounded-xl border border-gray-200 text-xs"></div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL TUTUP KASIR (CLOSING)                    -->
    <!-- ============================================== -->
    @if($modalTutupKasOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
             x-data="{
                 kasSistem: {{ $kasSistem }},
                 kasFisik: @entangle('kasFisik'),
                 get selisih() {
                     const fisik = Number(String(this.kasFisik).replace(/[^\d]/g, '') || 0);
                     return fisik - this.kasSistem;
                 }
             }">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeTutupKasModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#0E1726] px-6 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-white">
                                Tutup Kasir Harian (Closing)
                            </h3>
                        </div>
                        <button type="button" wire:click="closeTutupKasModal" class="text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="prosesTutupKas">
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
                                <x-input-rupiah wire:model="kasFisik" placeholder="Hitung dan masukkan uang fisik di laci..." />
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

                            <!-- Catatan Closing -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Catatan Closing (Opsional)
                                </label>
                                <textarea wire:model="catatanClosing"
                                          rows="2"
                                          placeholder="Tulis alasan jika ada selisih uang atau catatan khusus..."
                                          class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900"></textarea>
                                @error('catatanClosing') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl">
                            <button type="button"
                                    wire:click="closeTutupKasModal"
                                    class="px-4 py-2 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-xs font-extrabold text-white shadow-md shadow-blue-900/20 transition-all cursor-pointer flex items-center space-x-1.5">
                                <svg wire:loading wire:target="prosesTutupKas" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Simpan Tutup Kas</span>
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

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        initTable() {
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
                paginationSize: 15,
                paginationSizeSelector: [15, 25, 50, 100],
                movableColumns: true,
                placeholder: "Belum ada transaksi pada tanggal ini.",
                columns: [
                    { formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 35, minWidth: 35, maxWidth: 35, resizable: false },
                    { title: "Jam", field: "tanggal", width: 65, hozAlign: "center", sorter: "string", headerFilter: "input", headerFilterPlaceholder: "Jam", formatter: (cell) => `<span class="font-bold text-gray-700 font-mono text-[11px]">${cell.getValue()}</span>` },
                    { title: "No Transaksi", field: "no_transaksi", width: 125, headerFilter: "input", headerFilterPlaceholder: "Cari no...", formatter: (cell) => `<span class="font-mono font-extrabold text-gray-900 text-[11px] truncate block" title="${cell.getValue()}">${cell.getValue()}</span>` },
                    { title: "Transaksi", field: "tipe_label", width: 95, hozAlign: "center", headerFilter: "list", headerFilterParams: { valuesLookup: "all", clearable: true, multiselect: true }, headerFilterFunc: multiSelectFilterFunc, formatter: (cell) => {
                        const val = cell.getValue();
                        const isPJ = val === 'Penjualan';
                        return `<span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-black ${isPJ ? 'bg-purple-50 text-[#5E17EB] border border-purple-200' : 'bg-blue-50 text-[#0052FF] border border-blue-200'}">${val}</span>`;
                    }},
                    { title: "Pelanggan", field: "customer", widthGrow: 1, minWidth: 95, headerFilter: "input", headerFilterPlaceholder: "Cari...", formatter: (cell) => `<span class="font-bold text-gray-800 text-[11px] truncate block" title="${cell.getValue()}">${cell.getValue()}</span>` },
                    { title: "Detail Barang / Servis", field: "item_detail", widthGrow: 2, minWidth: 140, headerFilter: "input", headerFilterPlaceholder: "Cari barang...", formatter: (cell) => `<span class="text-gray-600 font-medium text-[11px] truncate block" title="${cell.getValue()}">${cell.getValue()}</span>` },
                    { title: "Modal (HPP)", field: "modal", hozAlign: "right", width: 100, headerFilter: "input", headerFilterPlaceholder: ">=...", formatter: (cell) => `<span class="font-mono font-semibold text-gray-500 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Harga Jual", field: "harga_jual", hozAlign: "right", width: 105, headerFilter: "input", headerFilterPlaceholder: ">=...", formatter: (cell) => `<span class="font-mono font-black text-gray-900 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
                    { title: "Laba (Nett)", field: "margin", hozAlign: "right", width: 105, headerFilter: "input", headerFilterPlaceholder: ">=...", formatter: (cell) => {
                        const val = cell.getValue();
                        return `<span class="font-mono font-black text-[11px] ${val >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${this.formatRupiah(val)}</span>`;
                    }},
                    { title: "Pajak", field: "pajak", hozAlign: "right", width: 80, headerFilter: "input", headerFilterPlaceholder: ">=...", formatter: (cell) => `<span class="font-mono text-gray-500 text-[11px]">${this.formatRupiah(cell.getValue())}</span>` },
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

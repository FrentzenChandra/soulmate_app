<div class="space-y-6">

    <!-- Page Header & Summary Cards -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span>Kas Keluar</span>
            </h1>
            <p class="text-xs text-gray-500 font-medium mt-1">Catat dan pantau seluruh pengeluaran operasional kas toko.</p>
        </div>

        <button type="button"
                wire:click="openCreateModal"
                wire:loading.attr="disabled"
                class="px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Catat Kas Keluar</span>
        </button>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Pengeluaran</span>
            <span class="text-xl font-black text-rose-600 mt-1 block">
                Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}
            </span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Kas Tunai (Cash)</span>
            <span class="text-xl font-black text-gray-900 mt-1 block">
                Rp {{ number_format($totalCash, 0, ',', '.') }}
            </span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Non-Tunai (Transfer Bank)</span>
            <span class="text-xl font-black text-blue-600 mt-1 block">
                Rp {{ number_format($totalNonCash, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- FILTER BAR & MODE PERIODE                      -->
    <!-- ============================================== -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-4">
        
        <!-- Baris 1: Mode Periode Switcher & Input Tanggal Dinamis -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-100">
            <div class="flex items-center space-x-1.5 bg-gray-100 p-1 rounded-xl">
                <button type="button"
                        wire:click="setPeriodeMode('semua')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'semua' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                    Semua
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('hari_ini')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'hari_ini' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                    Hari Ini
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('range')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'range' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                    Rentang Tanggal
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('bulanan')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'bulanan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                    Bulanan
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('tahunan')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'tahunan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                    Tahunan
                </button>
            </div>

            <!-- Input Tanggal Dinamis -->
            <div class="flex items-center space-x-2 sm:ml-auto">
                @if($periodeMode === 'range' || $periodeMode === 'hari_ini')
                    <input type="date"
                           wire:model.live="startDate"
                           class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB]">
                    <span class="text-xs font-bold text-gray-400">s/d</span>
                    <input type="date"
                           wire:model.live="endDate"
                           class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB]">
                @elseif($periodeMode === 'bulanan')
                    <select wire:model.live="filterBulan"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ sprintf('%02d', $m) }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="filterTahun"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700">
                        @foreach(range(now()->year - 3, now()->year + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                @elseif($periodeMode === 'tahunan')
                    <select wire:model.live="filterTahun"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700">
                        @foreach(range(now()->year - 3, now()->year + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <!-- Baris 2: Filter Kategori & Sumber Dana -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
            {{-- 
            <!-- Search Bar Backend Multi-Kata (Di-comment karena pencarian sudah di-handle oleh Tabulator Quick Search & Header Filter) -->
            <div class="md:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2 text-xs font-semibold text-gray-800 bg-gray-50/60 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] focus:border-transparent transition-all">
            </div>
            --}}

            <!-- Filter Sumber Dana -->
            <div class="md:col-span-6">
                <select wire:model.live="sumberDanaFilter"
                        class="w-full py-2 px-3 text-xs font-bold text-gray-700 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Sumber Dana</option>
                    @foreach($sumberDanas as $sd)
                        <option value="{{ $sd->id }}">{{ $sd->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kategori -->
            <div class="md:col-span-6">
                <select wire:model.live="kategoriFilter"
                        class="w-full py-2 px-3 text-xs font-bold text-gray-700 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Kategori</option>
                    @foreach($kategoriKasKeluars as $kat)
                        <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL DATA KAS KELUAR (TABULATOR SPREADSHEET)  -->
    <!-- ============================================== -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs"
         x-data="tabulatorKasKeluarTable({
             tableData: @js($tableData)
         })"
         x-init="initTable()"
         wire:ignore>

        <div class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Tabulator Quick Search -->
            <div class="flex items-center gap-2 flex-1 max-w-md">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text"
                           x-model="quickSearch"
                           @input="applyFilter()"
                           class="w-full pl-9 pr-3 py-1.5 text-xs font-bold text-gray-900 bg-gray-50/60 border border-gray-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5E17EB] transition-all">
                </div>
                <button type="button"
                        @click="clearAllFilters"
                        class="px-3 py-1.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-900 hover:bg-gray-100 transition-colors flex items-center space-x-1.5 cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>
            </div>

            <!-- Toolbar Actions & Font Sizer -->
            <div class="flex items-center gap-3 text-xs text-gray-900 font-bold flex-wrap">
                <!-- Toggle Group Tanggal -->
                <button type="button"
                        @click="toggleGrouping()"
                        class="px-2.5 py-1 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                        :class="groupByDate ? 'bg-purple-50 text-[#5E17EB] border-purple-200 shadow-2xs' : 'border-gray-300 text-gray-900 hover:bg-gray-50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span x-text="groupByDate ? 'Group Tanggal (ON)' : 'Group Tanggal'"></span>
                </button>

                <!-- Font Size Selector -->
                <x-table-size-selector />

                <template x-if="selectedCount > 0">
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-[#5E17EB] font-bold text-[11px] border border-purple-200"
                          x-text="selectedCount + ' dipilih'"></span>
                </template>
                <span class="text-[11px] text-gray-900 font-bold hidden md:inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    Tarik garis kolom untuk melebarkan
                </span>
                <span class="text-gray-900 font-black" x-text="'Total ' + rowCount + ' Pengeluaran'"></span>
            </div>
        </div>

        <div :class="'tabulator-size-' + tableSize + ' table-size-' + tableSize">
            <div x-ref="tableContainer" class="rounded-xl border border-gray-200 text-xs"></div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL CATAT KAS KELUAR                         -->
    <!-- ============================================== -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    {{ $isEditMode ? 'Edit Kas Keluar' : 'Catat Kas Keluar' }}
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Pencatatan pengeluaran operasional toko</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 border border-white/20 text-white text-xs font-bold font-mono shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                            </div>
                            <button type="button" wire:click="closeModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Sumber Dana -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Sumber Dana <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model="sumber_dana_id"
                                            class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                        @foreach($sumberDanas as $sd)
                                            <option value="{{ $sd->id }}">{{ $sd->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('sumber_dana_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Kategori Kas Keluar -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Kategori Biaya <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model="kategori_kas_keluar_id"
                                            class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900">
                                        @foreach($kategoriKasKeluars as $kat)
                                            <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('kategori_kas_keluar_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Nominal Uang -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Total Uang Keluar <span class="text-rose-500">*</span>
                                </label>
                                <x-input-rupiah wire:model="nominal" />
                                @error('nominal') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Keterangan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Keterangan Pengeluaran <span class="text-rose-500">*</span>
                                </label>
                                <textarea wire:model="keterangan"
                                          rows="3"
                                          class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900"></textarea>
                                @error('keterangan') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl">
                            <button type="button"
                                    wire:click="closeModal"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-extrabold text-white shadow-md shadow-purple-900/20 transition-all cursor-pointer flex items-center space-x-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading wire:target="save" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="save">{{ $isEditMode ? 'Simpan Perubahan' : 'Catat Kas Keluar' }}</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
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
window.tabulatorKasKeluarTable = function(config) {
    return {
        table: null,
        quickSearch: '',
        groupByDate: true,
        rowCount: (config.tableData || []).length,
        selectedCount: 0,
        tableSize: localStorage.getItem('soulmate_table_size') || 'md',

        formatRupiah(number) {
            return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
        },

        toggleGrouping() {
            this.groupByDate = !this.groupByDate;
            if (this.table) {
                this.table.setGroupBy(this.groupByDate ? "tanggal_group" : false);
            }
        },

        initTable() {
            window.activeKasKeluarWire = this.$wire;

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
                groupOrder: function(a, b, aGroup, bGroup) {
                    let aRows = aGroup.getRows();
                    let bRows = bGroup.getRows();
                    let aTime = aRows.length ? (aRows[0].getData().waktu_epoch || 0) : 0;
                    let bTime = bRows.length ? (bRows[0].getData().waktu_epoch || 0) : 0;
                    return bTime - aTime;
                },
                initialSort: [
                    { column: "waktu_epoch", dir: "desc" }
                ],
                groupStartOpen: true,
                groupToggleElement: "header",
                groupHeader: function(value, count, data, group) {
                    let totalNominalGroup = data.reduce((acc, r) => acc + Number(r.nominal || 0), 0);
                    return `
                        <div class="flex flex-wrap items-center justify-between w-full pr-3 py-0.5 text-xs font-bold text-slate-800 cursor-pointer select-none">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#5E17EB]/10 text-[#5E17EB] font-black">${value}</span>
                                <span class="text-slate-600 font-bold text-[11px] font-mono">(${count} Pengeluaran)</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] font-mono">
                                <span class="text-rose-700 font-bold">Total: <strong class="text-rose-600 font-black">Rp ${totalNominalGroup.toLocaleString('id-ID')}</strong></span>
                            </div>
                        </div>
                    `;
                },
                placeholder: "Belum ada catatan kas keluar pada periode ini.",
                columns: [
                    { formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 30, minWidth: 30, maxWidth: 30, resizable: false },
                    {
                        title: "Kategori",
                        field: "kategori",
                        width: 140,
                        minWidth: 120,
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => `
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-50 text-[#5E17EB] border border-purple-200 truncate">
                                ${cell.getValue()}
                            </span>
                        `
                    },
                    {
                        title: "Jam",
                        field: "jam",
                        width: 80,
                        minWidth: 70,
                        hozAlign: "center",
                        sorter: "string",
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => `<span class="font-mono font-bold text-gray-700 text-xs">${cell.getValue()}</span>`
                    },
                    {
                        title: "Petugas",
                        field: "petugas",
                        width: 130,
                        minWidth: 110,
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => `<span class="font-bold text-gray-900 text-xs truncate">${cell.getValue()}</span>`
                    },
                    {
                        title: "Keterangan",
                        field: "keterangan",
                        widthGrow: 2,
                        minWidth: 160,
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => `<span class="font-bold text-black text-sm block py-0.5 truncate" title="${cell.getValue()}">${cell.getValue()}</span>`
                    },
                    {
                        title: "Sumber Dana",
                        field: "sumber_dana",
                        width: 130,
                        minWidth: 110,
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => `<span class="font-bold text-emerald-600 text-xs truncate">${cell.getValue()}</span>`
                    },
                    {
                        title: "Total Uang",
                        field: "nominal",
                        hozAlign: "right",
                        width: 140,
                        minWidth: 120,
                        formatter: (cell) => `<span class="font-mono font-black text-rose-600 text-xs">${this.formatRupiah(cell.getValue())}</span>`
                    },
                    {
                        title: "Aksi",
                        field: "id",
                        width: 80,
                        maxWidth: 80,
                        hozAlign: "center",
                        headerSort: false,
                        resizable: false,
                        formatter: (cell) => {
                            const id = cell.getValue();
                            return `
                                <div class="flex items-center justify-center space-x-1">
                                    <button type="button" onclick="window.editKasKeluar(${id})" class="p-1 rounded bg-purple-50 hover:bg-purple-100 text-[#5E17EB] transition-colors cursor-pointer" title="Edit Catatan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" onclick="window.deleteKasKeluarConfirm(${id})" class="p-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors cursor-pointer" title="Hapus Catatan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            this.table.on("dataFiltered", (filters, rows) => {
                this.rowCount = rows.length;
            });

            this.table.on("rowSelectionChanged", (data, rows) => {
                this.selectedCount = rows.length;
            });

            Livewire.on('kas-keluar-table-updated', ({ data }) => {
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
                return (data.jam && data.jam.toLowerCase().includes(term)) ||
                       (data.kategori && data.kategori.toLowerCase().includes(term)) ||
                       (data.petugas && data.petugas.toLowerCase().includes(term)) ||
                       (data.keterangan && data.keterangan.toLowerCase().includes(term)) ||
                       (data.sumber_dana && data.sumber_dana.toLowerCase().includes(term)) ||
                       (data.nominal_formatted && data.nominal_formatted.toLowerCase().includes(term));
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

window.editKasKeluar = (id) => {
    if (window.activeKasKeluarWire) window.activeKasKeluarWire.openEditModal(id);
};
window.deleteKasKeluarConfirm = (id) => {
    if (window.swalConfirm) {
        window.swalConfirm('Hapus Kas Keluar?', 'Catatan pengeluaran kas ini akan dihapus secara permanen.', {
            danger: true,
            confirmText: 'Ya, Hapus',
            cancelText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed && window.activeKasKeluarWire) {
                window.activeKasKeluarWire.delete(id);
            }
        });
    } else if (window.Swal) {
        window.Swal.fire({
            title: 'Hapus Kas Keluar?',
            text: 'Catatan pengeluaran kas ini akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && window.activeKasKeluarWire) {
                window.activeKasKeluarWire.delete(id);
            }
        });
    } else if (confirm('Yakin ingin menghapus catatan kas keluar ini?')) {
        if (window.activeKasKeluarWire) window.activeKasKeluarWire.delete(id);
    }
};
</script>
@endpush


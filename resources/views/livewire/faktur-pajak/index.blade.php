<div class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOMBOL TAMBAH FAKTUR          -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Faktur Pajak
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Administrasi faktur pengadaan, kalkulasi PPN 12%, PPh, dan pelacakan status selesai.
            </p>
        </div>

        @canany(['pajak.create', 'owner'])
        <div class="flex items-center space-x-2">
            <button type="button"
                    wire:click="openCreateModal"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-black text-xs shadow-md shadow-purple-600/25 transition-all cursor-pointer disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Input Faktur Baru</span>
            </button>
        </div>
        @endcanany
    </div>

    <!-- Alert Flash Message -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- FILTER BAR, PERIODE & PENCARIAN (TRANSAKSI FULL) -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-5 space-y-4 border border-gray-100/90 shadow-sm rounded-2xl">

        <!-- Baris 1: Mode Periode Switcher -->
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
            <div class="flex items-center space-x-2">
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
                            <option value="{{ sprintf('%02d', $m) }}">{{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}</option>
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

        <!-- Baris 2: Search & Filter Dropdown -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
            <!-- Pencarian Multi-Kata Longgar -->
            <div class="md:col-span-3 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 transition-all">
            </div>

            <!-- Filter Status Selesai -->
            <div class="md:col-span-2">
                <select wire:model.live="statusFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Status</option>
                    <option value="selesai">Selesai</option>
                    <option value="belum">Belum Selesai</option>
                </select>
            </div>

            <!-- Filter Rekanan -->
            <div class="md:col-span-3">
                <select wire:model.live="orangFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Rekanan</option>
                    @foreach ($orangOptions as $o)
                        <option value="{{ $o->id }}">{{ $o->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Saluran Via -->
            <div class="md:col-span-2">
                <select wire:model.live="viaFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Saluran</option>
                    @foreach ($viaOptions as $v)
                        <option value="{{ $v->nama }}">{{ $v->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kode Pajak -->
            <div class="md:col-span-2">
                <select wire:model.live="kodeFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                    <option value="semua">Semua Kode</option>
                    @foreach ($kodeOptions as $k)
                        <option value="{{ $k->kode }}">{{ $k->kode }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Cabang (HQ Saja) -->
            @if($isHQ)
                <div class="md:col-span-12 pt-1 flex justify-end">
                    <div class="w-full sm:w-72">
                        <select wire:model.live="cabangFilter"
                                class="w-full py-2 px-3 rounded-xl border border-purple-200 bg-purple-50/40 text-xs font-bold text-[#5E17EB] focus:bg-white cursor-pointer">
                            <option value="semua">Semua Cabang (HQ)</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL DATA FAKTUR PAJAK (TABULATOR)            -->
    <!-- ============================================== -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs"
         x-data="fakturPajakTable({
             tableData: @js($tableData),
         })"
         x-init="initTable()"
         wire:ignore>

        <div class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
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

            <div class="flex items-center gap-3 text-xs text-gray-900 font-bold flex-wrap">
                <!-- Toggle Group Tanggal -->
                <button type="button"
                        @click="toggleGroupDate()"
                        class="px-2.5 py-1 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                        :class="groupByDate ? 'bg-purple-50 text-[#5E17EB] border-purple-200 shadow-2xs' : 'border-gray-300 text-gray-900 hover:bg-gray-50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span x-text="groupByDate ? 'Group Tanggal (ON)' : 'Group Tanggal'"></span>
                </button>

                <!-- Font Size Selector -->
                <x-table-size-selector />

                <!-- Tombol Export Excel -->
                @canany(['pajak.export_excel', 'owner'])
                <div class="flex items-center gap-1.5 border-l border-gray-200 pl-2">
                    <button type="button"
                            @click="exportExcel()"
                            class="px-2.5 py-1 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
                            title="Download Excel (.xlsx)">
                        <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Excel</span>
                    </button>
                </div>
                @endcanany

                <span class="text-[11px] text-gray-900 font-bold hidden md:inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    Tarik garis kolom untuk melebarkan
                </span>
                <span class="text-gray-900 font-black" x-text="'Total ' + rowCount + ' Faktur'"></span>
            </div>
        </div>

        <div :class="'tabulator-size-' + tableSize + ' table-size-' + tableSize + ' tabulator-dense'">
            <div id="faktur-pajak-table" class="rounded-xl border border-gray-200 text-xs"></div>
        </div>
    </div>

    <!-- MODAL FORM FAKTUR PAJAK (RULE 21: sm:max-w-2xl) -->
    @if ($modalFormOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 overflow-y-auto">
            <div
                class="bg-white w-full sm:max-w-2xl rounded-2xl shadow-xl border border-gray-200 overflow-hidden my-8"
                x-data="{
                    bruto: @entangle('formBruto'),
                    pphPersen: @entangle('formPphPersen'),
                    get numBruto() {
                        let clean = (this.bruto || '0').toString().replace(/[^0-9]/g, '');
                        return parseFloat(clean) || 0;
                    },
                    get numDpp() {
                        return Math.round(this.numBruto / 1.11);
                    },
                    get numDppLain() {
                        return Math.round(this.numDpp * (11 / 12));
                    },
                    get numPpn() {
                        return Math.round(this.numDppLain * 0.12);
                    },
                    get numPph() {
                        let pct = parseFloat(this.pphPersen) || 0;
                        return Math.round(this.numDpp * (pct / 100));
                    },
                    get numNetto() {
                        return Math.round(this.numBruto - this.numPpn - this.numPph);
                    },
                    formatRp(val) {
                        return 'Rp ' + Math.round(val || 0).toLocaleString('id-ID');
                    },
                    maskBruto(e) {
                        let val = e.target.value.replace(/[^0-9]/g, '');
                        if (!val) { this.bruto = '0'; return; }
                        this.bruto = parseInt(val, 10).toLocaleString('id-ID');
                    }
                }"
            >
                <!-- Modal Header -->
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-2 h-4 bg-[#5E17EB] rounded-full inline-block"></span>
                        {{ $isEditMode ? 'Edit Faktur Pajak' : 'Input Faktur Pajak Baru' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-lg leading-none cursor-pointer">&times;</button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="save" class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Tanggal Faktur -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Tanggal Faktur <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="formTanggal"
                                class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden"
                            >
                            @error('formTanggal') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Via Pajak -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Via Pajak <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select
                                wire:model.live="formMasterViaPajakId"
                                class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden"
                            >
                                @foreach ($viaOptions as $v)
                                    <option value="{{ $v->id }}">{{ $v->nama }}</option>
                                @endforeach
                            </select>
                            @error('formViaPajak') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Nama Rekanan (Autocomplete Autosearch) -->
                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Nama Rekanan / Instansi <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="orangPajakSearch"
                            wire:change="$set('formNamaPajak', $event.target.value)"
                            class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden"
                        >
                        @error('formNamaPajak') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror

                        <!-- Suggestions Dropdown -->
                        @if (! empty($orangPajakSuggestions))
                            <div class="absolute left-0 right-0 z-30 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden divide-y divide-gray-100">
                                @foreach ($orangPajakSuggestions as $s)
                                    <button
                                        type="button"
                                        wire:click="selectOrangPajak({{ $s['id'] }}, '{{ addslashes($s['nama']) }}')"
                                        class="w-full text-left px-3 py-2 hover:bg-purple-50 text-xs flex items-center justify-between transition-colors"
                                    >
                                        <span class="font-bold text-gray-900">{{ $s['nama'] }}</span>
                                        <span class="text-[10px] text-gray-400 font-mono">{{ $s['npwp'] ?: '-' }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Uraian Barang / Jasa -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Uraian Pengadaan Barang / Jasa <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <textarea
                            wire:model="formBarang"
                            rows="2"
                            class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden"
                        ></textarea>
                        @error('formBarang') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Grid: Kode Pajak & Jenis PPh -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Kode Pajak -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Pajak</label>
                            <select
                                wire:model.live="formMasterKodePajakId"
                                class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden"
                            >
                                <option value="">Tanpa Kode Pajak</option>
                                @foreach ($kodeOptions as $k)
                                    <option value="{{ $k->id }}">{{ $k->kode }} - {{ $k->keterangan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jenis PPh -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Jenis PPh <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select
                                wire:model.live="formMasterPphId"
                                class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden"
                            >
                                @foreach ($pphOptions as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_pph }} ({{ number_format($p->persentase, 2) }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Input Bruto Rupiah (Preserve-Cursor) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Nilai Bruto (Rp) <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-gray-400">Rp</span>
                            <input
                                type="text"
                                x-model="bruto"
                                @input="maskBruto($event)"
                                class="w-full text-xs font-mono font-bold bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden text-right"
                            >
                        </div>
                        @error('formBruto') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- PANEL PREVIEW KALKULASI OTOMATIS (READ-ONLY) -->
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <div class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Simulasi Kalkulasi Pajak</span>
                            <span class="text-[10px] text-slate-500 font-mono">PPN 12% &bull; PPh <span x-text="pphPersen + '%'"></span></span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs pt-1 border-t border-slate-200/60">
                            <div>
                                <span class="block text-[10px] text-gray-500">DPP (Bruto/1.11)</span>
                                <span class="font-mono font-bold text-gray-800" x-text="formatRp(numDpp)"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-gray-500">DPP Lain (11/12)</span>
                                <span class="font-mono font-bold text-gray-800" x-text="formatRp(numDppLain)"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-rose-600 font-semibold">PPN 12%</span>
                                <span class="font-mono font-bold text-rose-600" x-text="formatRp(numPpn)"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-amber-600 font-semibold">PPh Potongan</span>
                                <span class="font-mono font-bold text-amber-600" x-text="formatRp(numPph)"></span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">Netto Diterima:</span>
                            <span class="text-sm font-black font-mono text-[#0052FF]" x-text="formatRp(numNetto)"></span>
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                        <textarea
                            wire:model="formCatatan"
                            rows="2"
                            class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-900 focus:bg-white focus:border-[#5E17EB] focus:outline-hidden"
                        ></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#5E17EB] hover:bg-[#4d12c4] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors duration-150 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
                        >
                            <svg wire:loading wire:target="save" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="save">Simpan Faktur</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL DETAIL FAKTUR PAJAK -->
    @if ($modalDetailOpen && $selectedFaktur)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white w-full sm:max-w-xl rounded-2xl shadow-xl border border-gray-200 overflow-hidden my-8">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-4 bg-[#5E17EB] rounded-full inline-block"></span>
                        <h3 class="text-sm font-bold text-gray-900">Rincian Faktur: {{ $selectedFaktur->no_faktur }}</h3>
                    </div>
                    <button type="button" wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 text-lg leading-none cursor-pointer">&times;</button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <!-- Status & Metadata -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <div>
                            <span class="block text-[10px] text-gray-500 font-semibold uppercase tracking-wider">Status Faktur</span>
                            @if ($selectedFaktur->is_selesai)
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs mt-0.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selesai ({{ $selectedFaktur->selesai_at?->format('d/m/Y H:i') }})
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-rose-600 font-bold text-xs mt-0.5">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Belum Selesai
                                </span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] text-gray-500 font-semibold uppercase tracking-wider">Petugas Input</span>
                            <span class="font-bold text-gray-800">{{ $selectedFaktur->user?->name ?: 'Sistem' }}</span>
                        </div>
                    </div>

                    <!-- Rincian Rekanan & Barang -->
                    <div class="space-y-2 border-b border-gray-100 pb-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Rekanan / Instansi:</span>
                            <span class="font-bold text-gray-900">{{ $selectedFaktur->nama_pajak_snapshot }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Saluran Via:</span>
                            <span class="font-semibold text-gray-900">{{ $selectedFaktur->via_pajak_snapshot }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kode Pajak:</span>
                            <span class="font-semibold text-gray-900">{{ $selectedFaktur->kode_pajak_snapshot ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-0.5">Uraian Barang / Jasa:</span>
                            <div class="p-2.5 bg-gray-50 rounded-xl text-gray-800 font-medium whitespace-pre-wrap">{{ $selectedFaktur->barang_snapshot }}</div>
                        </div>
                    </div>

                    <!-- Breakdown Finansial -->
                    <div class="space-y-1.5 pt-1">
                        <div class="flex justify-between text-gray-700">
                            <span>Nilai Bruto:</span>
                            <span class="font-mono font-bold">Rp {{ number_format($selectedFaktur->bruto, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>DPP (Bruto / 1.11):</span>
                            <span class="font-mono">Rp {{ number_format($selectedFaktur->dpp, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>DPP Lain (11 / 12):</span>
                            <span class="font-mono">Rp {{ number_format($selectedFaktur->dpp_lain, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-rose-600 font-semibold">
                            <span>PPN 12%:</span>
                            <span class="font-mono">Rp {{ number_format($selectedFaktur->ppn_nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-amber-600 font-semibold">
                            <span>{{ $selectedFaktur->jenis_pph_snapshot }} ({{ number_format($selectedFaktur->pph_persen, 2) }}%):</span>
                            <span class="font-mono">Rp {{ number_format($selectedFaktur->pph_nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-black pt-2 border-t border-gray-200 text-[#0052FF]">
                            <span>Netto Diterima:</span>
                            <span class="font-mono">Rp {{ number_format($selectedFaktur->netto, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if ($selectedFaktur->catatan)
                        <div class="pt-2 border-t border-gray-100">
                            <span class="text-gray-500 block mb-0.5">Catatan:</span>
                            <p class="text-gray-700 italic">{{ $selectedFaktur->catatan }}</p>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="button" wire:click="closeDetail" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    window.fakturPajakTable = function(config) {
        return {
            table: null,
            tableSize: localStorage.getItem('soulmate_table_size') || 'M',
            groupByDate: true,
            quickSearch: '',
            rowCount: (config.tableData || []).length,

            initTable() {
                window.addEventListener('table-size-changed', (e) => {
                    this.tableSize = e.detail;
                    if (this.table) {
                        this.table.redraw(true);
                    }
                });

                this.renderTable(config.tableData || []);

                // Listen real-time data updates from Livewire (Rule 24 Standard)
                Livewire.on('faktur-pajak-table-updated', ({ data }) => {
                    if (this.table && data) {
                        this.table.setData(data);
                        this.rowCount = data.length;
                        this.applyFilter();
                    }
                });

                // Listen reload event from Livewire
                Livewire.on('table-reload', () => {
                    Livewire.dispatch('$refresh');
                });
            },

            applyFilter() {
                if (!this.table) return;
                const q = (this.quickSearch || '').toLowerCase().trim();
                if (!q) {
                    this.table.clearFilter();
                    this.rowCount = this.table.getData().length;
                    return;
                }
                const terms = q.split(/\s+/).filter(Boolean);
                this.table.setFilter((row) => {
                    const text = [
                        row.no_faktur || '',
                        row.nama || '',
                        row.barang || '',
                        row.via || '',
                        row.kode || '',
                        row.user_input || '',
                        row.catatan || '',
                        row.jenis_pph || '',
                        row.tanggal || ''
                    ].join(' ').toLowerCase();
                    return terms.every(term => text.includes(term));
                });
                this.rowCount = this.table.getData("active").length;
            },

            clearAllFilters() {
                this.quickSearch = '';
                if (this.table) {
                    this.table.clearFilter();
                    this.rowCount = this.table.getData().length;
                }
            },

            toggleGroupDate() {
                this.groupByDate = !this.groupByDate;
                if (this.table) {
                    this.table.setGroupBy(this.groupByDate ? "tanggal_group" : false);
                    this.table.redraw(true);
                }
            },

            formatRupiah(val) {
                return 'Rp ' + Math.round(Number(val) || 0).toLocaleString('id-ID');
            },

            renderTable(data) {
                const self = this;
                this.table = new Tabulator("#faktur-pajak-table", {
                    data: data,
                    layout: "fitColumns",
                    responsiveLayout: false,
                    pagination: "local",
                    paginationSize: 200,
                    paginationSizeSelector: [200, 1000, 2000, 10000],
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
                    dataFiltered: function(filters, rows) {
                        self.rowCount = rows.length;
                    },
                    groupHeader: function(value, count, groupData) {
                        let totalBruto = groupData.reduce((acc, r) => acc + Number(r.bruto || 0), 0);
                        let totalPpn = groupData.reduce((acc, r) => acc + Number(r.ppn_nominal || 0), 0);
                        let totalPph = groupData.reduce((acc, r) => acc + Number(r.pph_nominal || 0), 0);
                        let totalNetto = groupData.reduce((acc, r) => acc + Number(r.netto || 0), 0);
                        return `
                            <div class="flex flex-wrap items-center justify-between w-full pr-3 py-0.5 text-xs font-bold text-slate-800 cursor-pointer select-none">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-[#5E17EB]/10 text-[#5E17EB] font-black">${value}</span>
                                    <span class="text-slate-600 font-bold text-[11px] font-mono">(${count} Faktur)</span>
                                </div>
                                <div class="flex items-center gap-3 text-[11px] font-mono">
                                    <span class="text-slate-600 font-bold">Bruto: <strong class="text-slate-800">Rp ${totalBruto.toLocaleString('id-ID')}</strong></span>
                                    <span class="text-rose-600 font-bold">PPN: <strong>Rp ${totalPpn.toLocaleString('id-ID')}</strong></span>
                                    <span class="text-amber-600 font-bold">PPh: <strong>Rp ${totalPph.toLocaleString('id-ID')}</strong></span>
                                    <span class="text-[#0052FF] font-bold">Netto: <strong class="text-[#0052FF] font-black">Rp ${totalNetto.toLocaleString('id-ID')}</strong></span>
                                </div>
                            </div>
                        `;
                    },
                    placeholder: "Belum ada transaksi faktur pajak.",
                    columns: [
                        // 1. NO (DESC)
                        {
                            title: "No",
                            field: "no",
                            width: 55,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => `<span class="font-mono font-bold text-slate-500">${cell.getValue()}</span>`
                        },
                        // 2. STATUS SELESAI (CENTANG HIJAU / SILANG MERAH)
                        {
                            title: "Selesai",
                            field: "is_selesai",
                            width: 70,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const isSelesai = cell.getValue();
                                if (isSelesai) {
                                    return `
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 shadow-2xs" title="Selesai">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    `;
                                } else {
                                    return `
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-100 text-rose-600 shadow-2xs" title="Belum Selesai">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    `;
                                }
                            }
                        },
                        // 3. FAKTUR
                        {
                            title: "Faktur",
                            field: "no_faktur",
                            width: 130,
                            headerFilter: "input",
                            headerFilterPlaceholder: "",
                            formatter: (cell) => {
                                const d = cell.getData();
                                return `
                                    <div class="leading-tight py-0.5">
                                        <span class="font-mono font-bold text-[#5E17EB] block text-xs">${d.no_faktur}</span>
                                        <div class="flex items-center gap-1 text-[9px] text-gray-500 font-semibold mt-0.5">
                                            <span class="truncate max-w-[55px]">${d.user_input}</span>
                                            <span>&bull;</span>
                                            <span class="font-mono">${d.jam}</span>
                                        </div>
                                    </div>
                                `;
                            }
                        },
                        // 4. NAMA
                        {
                            title: "Nama",
                            field: "nama",
                            widthGrow: 1.5,
                            headerFilter: "input",
                            headerFilterPlaceholder: "",
                            formatter: (cell) => `<span class="font-bold text-gray-900 block truncate text-xs" title="${cell.getValue()}">${cell.getValue()}</span>`
                        },
                        // 5. BARANG
                        {
                            title: "Barang",
                            field: "barang",
                            widthGrow: 2,
                            headerFilter: "input",
                            headerFilterPlaceholder: "",
                            formatter: (cell) => `<span class="text-gray-700 block truncate text-xs" title="${cell.getValue()}">${cell.getValue()}</span>`
                        },
                        // 6. SALURAN
                        {
                            title: "Saluran",
                            field: "via",
                            width: 110,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            headerFilter: "input",
                            headerFilterPlaceholder: "",
                            formatter: (cell) => {
                                const d = cell.getData();
                                return `
                                    <div class="space-y-0.5 py-0.5">
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[8px] font-bold uppercase tracking-wider bg-blue-50 text-[#0052FF] border border-blue-200">
                                            ${d.via}
                                        </span>
                                        <div class="text-[9px] font-mono font-bold text-gray-500">${d.kode}</div>
                                    </div>
                                `;
                            }
                        },
                        // 7. BRUTO
                        {
                            title: "Bruto",
                            field: "bruto",
                            width: 120,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-bold text-gray-900">${self.formatRupiah(cell.getValue())}</span>`
                        },
                        // 8. DPP
                        {
                            title: "DPP",
                            field: "dpp",
                            width: 110,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono text-gray-600">${self.formatRupiah(cell.getValue())}</span>`
                        },
                        // 9. DPP LAIN
                        {
                            title: "DPP Lain",
                            field: "dpp_lain",
                            width: 110,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono text-gray-600">${self.formatRupiah(cell.getValue())}</span>`
                        },
                        // 10. PPN
                        {
                            title: "PPN",
                            field: "ppn_nominal",
                            width: 110,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-bold text-rose-600">${self.formatRupiah(cell.getValue())}</span>`
                        },
                        // 11. PPH (TARIF)
                        {
                            title: "PPh",
                            field: "jenis_pph",
                            width: 100,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const d = cell.getData();
                                return `<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">${d.jenis_pph}</span>`;
                            }
                        },
                        // 12. PPH NOMINAL
                        {
                            title: "PPh Nominal",
                            field: "pph_nominal",
                            width: 115,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-bold text-amber-600">${self.formatRupiah(cell.getValue())}</span>`
                        },
                        // 13. NETTO
                        {
                            title: "Netto",
                            field: "netto",
                            width: 125,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-black text-[#0052FF]">${self.formatRupiah(cell.getValue())}</span>`
                        },
                        // 14. AKSI
                        {
                            title: "Aksi",
                            field: "id",
                            width: 130,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            headerSort: false,
                            formatter: (cell) => {
                                const d = cell.getData();
                                const toggleTitle = d.is_selesai ? 'Tandai Belum Selesai' : 'Tandai Selesai';
                                const toggleClass = d.is_selesai ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50';
                                return `
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Tombol Centang (Toggle Selesai) -->
                                        <button
                                            type="button"
                                            onclick="Livewire.dispatch('call-toggle-selesai', { id: ${d.id} })"
                                            class="p-1 rounded-lg ${toggleClass} transition-colors cursor-pointer"
                                            title="${toggleTitle}"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>

                                        <!-- Detail -->
                                        <button
                                            type="button"
                                            onclick="Livewire.dispatch('call-open-detail', { id: ${d.id} })"
                                            class="p-1 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                                            title="Detail Faktur"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            onclick="Livewire.dispatch('call-edit-faktur', { id: ${d.id} })"
                                            class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Faktur"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Hapus -->
                                        <button
                                            type="button"
                                            onclick="if(confirm('Yakin ingin menghapus faktur ini?')) { Livewire.dispatch('call-delete-faktur', { id: ${d.id} }); }"
                                            class="p-1 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Faktur"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ]
                });
            },
            exportExcel() {
                if (typeof XLSX === 'undefined') {
                    alert('Library export Excel sedang dimuat, silakan coba sesaat lagi.');
                    return;
                }
                const rows = this.table.getData();
                if (!rows.length) {
                    alert('Tidak ada data faktur untuk diekspor.');
                    return;
                }
                const formatted = rows.map((r, i) => ({
                    'No': i + 1,
                    'Status': r.is_selesai ? 'Selesai' : 'Belum',
                    'No Faktur': r.no_faktur,
                    'Tanggal': r.tanggal,
                    'Petugas': r.user_input,
                    'Rekanan': r.nama,
                    'Barang / Jasa': r.barang,
                    'Saluran': r.via,
                    'Kode Pajak': r.kode,
                    'Bruto (Rp)': r.bruto,
                    'DPP (Rp)': r.dpp,
                    'DPP Lain (Rp)': r.dpp_lain,
                    'PPN 12% (Rp)': r.ppn_nominal,
                    'Jenis PPh': r.jenis_pph,
                    'Tarif PPh (%)': r.pph_persen,
                    'PPh Nominal (Rp)': r.pph_nominal,
                    'Netto (Rp)': r.netto,
                    'Catatan': r.catatan,
                }));
                const ws = XLSX.utils.json_to_sheet(formatted);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Faktur Pajak");
                XLSX.writeFile(wb, `Laporan_Faktur_Pajak_${new Date().toISOString().slice(0, 10)}.xlsx`);
            }
        };
    }

    // Global Event Listeners from Tabulator buttons to Livewire
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('call-toggle-selesai', (event) => {
            @this.call('toggleSelesai', event.id);
        });
        Livewire.on('call-open-detail', (event) => {
            @this.call('openDetail', event.id);
        });
        Livewire.on('call-edit-faktur', (event) => {
            @this.call('edit', event.id);
        });
        Livewire.on('call-delete-faktur', (event) => {
            @this.call('delete', event.id);
        });
    });
</script>
@endpush


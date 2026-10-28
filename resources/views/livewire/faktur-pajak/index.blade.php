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
    <!-- KARTU RINGKASAN METRIK (BULAN BERJALAN)         -->
    <!-- ============================================== -->
    <div>
        <!-- Skeleton Shimmer Saat Memuat / Memproses Mutasi Data -->
        <div wire:loading wire:target="save, delete, toggleSelesai" class="w-full">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @for ($i = 0; $i < 3; $i++)
                    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs animate-pulse">
                        <div class="flex items-center justify-between">
                            <div class="h-3.5 bg-gray-200 rounded w-24"></div>
                            <div class="w-8 h-8 bg-gray-100 rounded-xl"></div>
                        </div>
                        <div class="h-7 bg-gray-200 rounded w-40 mt-3"></div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Kartu Metrik Riil (Aktif) -->
        <div wire:loading.remove wire:target="save, delete, toggleSelesai" class="w-full">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-metric-card
                    label="Total Bruto"
                    :value="'Rp ' . number_format($totalBruto, 0, ',', '.')"
                    color="purple"
                />
                <x-metric-card
                    label="Total PPN"
                    :value="'Rp ' . number_format($totalPpn, 0, ',', '.')"
                    color="blue"
                />
                <x-metric-card
                    label="Total PPh"
                    :value="'Rp ' . number_format($totalPph, 0, ',', '.')"
                    color="pink"
                />
            </div>
        </div>
    </div>

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
            <div x-ref="tableContainer" id="faktur-pajak-table" class="rounded-xl border border-gray-200 text-xs"></div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL FORM FAKTUR PAJAK (RULE 21: sm:max-w-2xl) -->
    <!-- ============================================== -->
    @if ($modalFormOpen)
        <div wire:key="modal-form-faktur" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100"
                     x-data="{
                         bruto: @entangle('formBruto'),
                         pphPersen: @entangle('formPphPersen'),
                         get numBruto() {
                             let clean = (this.bruto || '').toString().replace(/[^0-9]/g, '');
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
                             if (!val) { this.bruto = ''; return; }
                             this.bruto = parseInt(val, 10).toLocaleString('id-ID');
                         }
                     }">

                    <!-- Header Modal Form -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    {{ $isEditMode ? 'Edit Faktur Pajak' : 'Input Faktur Pajak Baru' }}
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">
                                    Administrasi faktur pengadaan, kalkulasi PPN 12%, dan PPh
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <!-- Badge Tanggal Pembuatan di Header -->
                            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 border border-white/20 text-white text-xs font-bold font-mono shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                            </div>

                            <button type="button" wire:click="closeModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">

                            @if($errors->any())
                                <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-bold space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Terdapat kesalahan input:</span>
                                    </div>
                                    <ul class="list-disc list-inside text-[11px] font-semibold pl-1 space-y-0.5 text-rose-600">
                                        @foreach($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Section 1: Informasi Wajib Pajak -->
                            <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">1. Informasi Wajib Pajak</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Nama Wajib Pajak -->
                                    <div class="relative">
                                        <label class="block font-bold text-gray-700 mb-1.5 text-xs">
                                            Nama Wajib Pajak <span class="text-rose-500 font-bold">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.250ms="orangPajakSearch"
                                            wire:change="$set('formNamaPajak', $event.target.value)"
                                            class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all"
                                        >
                                        @error('formNamaPajak') <span class="text-rose-500 font-bold block mt-1">{{ $message }}</span> @enderror

                                        <!-- Suggestions Autocomplete -->
                                        @if (! empty($orangPajakSuggestions))
                                            <div class="absolute left-0 right-0 z-30 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto custom-scrollbar">
                                                @foreach ($orangPajakSuggestions as $s)
                                                    <button
                                                        type="button"
                                                        wire:click="selectOrangPajak({{ $s['id'] }}, '{{ addslashes($s['nama']) }}')"
                                                        class="w-full text-left px-3.5 py-2 hover:bg-purple-50 text-xs flex items-center justify-between transition-colors cursor-pointer"
                                                    >
                                                        <span class="font-bold text-gray-900">{{ $s['nama'] }}</span>
                                                        <span class="text-[10px] text-gray-400 font-mono">{{ $s['npwp'] ?: '-' }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Uraian Barang -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5 text-xs">
                                            Uraian Barang <span class="text-rose-500 font-bold">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="formBarang"
                                            class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-900 text-xs focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all"
                                        >
                                        @error('formBarang') <span class="text-rose-500 font-bold block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Klasifikasi Pajak -->
                            <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">2. Klasifikasi Pajak</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <!-- Saluran Via Pajak -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5 text-xs">
                                            Saluran Via <span class="text-rose-500 font-bold">*</span>
                                        </label>
                                        <select
                                            wire:model.live="formMasterViaPajakId"
                                            class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all cursor-pointer"
                                        >
                                            @foreach ($viaOptions as $v)
                                                <option value="{{ $v->id }}">{{ $v->nama }}</option>
                                            @endforeach
                                        </select>
                                        @error('formViaPajak') <span class="text-rose-500 font-bold block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Kode Pajak -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5 text-xs">
                                            Kode Pajak
                                        </label>
                                        <select
                                            wire:model.live="formMasterKodePajakId"
                                            class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all cursor-pointer"
                                        >
                                            <option value="">Tanpa Kode</option>
                                            @foreach ($kodeOptions as $k)
                                                <option value="{{ $k->id }}">{{ $k->kode }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Jenis PPh -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5 text-xs">
                                            Jenis PPh <span class="text-rose-500 font-bold">*</span>
                                        </label>
                                        <select
                                            wire:model.live="formMasterPphId"
                                            class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all cursor-pointer"
                                        >
                                            @foreach ($pphOptions as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama_pph }} ({{ number_format($p->persentase, 2) }}%)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Nilai Finansial -->
                            <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">3. Nilai Finansial</span>
                                </div>

                                <!-- Nilai Bruto & Catatan (Sebaris 50% - 50%) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Input Bruto -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5 text-xs">
                                            Nilai Bruto (Rp) <span class="text-rose-500 font-bold">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-gray-400">Rp</span>
                                            <input
                                                type="text"
                                                x-model="bruto"
                                                @input="maskBruto($event)"
                                                class="w-full pl-10 pr-4 py-2 font-mono font-bold text-right text-gray-900 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all text-xs"
                                            >
                                        </div>
                                        @error('formBruto') <span class="text-rose-500 font-bold block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Catatan -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5 text-xs">
                                            Catatan
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="formCatatan"
                                            class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 text-xs focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] transition-all"
                                        >
                                    </div>
                                </div>

                                <!-- Card Simulasi Kalkulasi -->
                                <div class="p-3.5 bg-white rounded-xl border border-gray-200 shadow-2xs space-y-2.5">
                                    <div class="text-[10px] font-bold text-gray-600 uppercase tracking-wider flex items-center justify-between border-b border-gray-100 pb-2">
                                        <span>Rincian Kalkulasi Pajak</span>
                                        <span class="text-[10px] text-purple-700 font-mono font-bold">PPN 12%</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <div>
                                            <span class="block text-[10px] font-bold text-gray-500">DPP (Bruto/1.11)</span>
                                            <span class="font-mono font-bold text-gray-800" x-text="formatRp(numDpp)"></span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] font-bold text-gray-500">DPP Lain (11/12)</span>
                                            <span class="font-mono font-bold text-gray-800" x-text="formatRp(numDppLain)"></span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] font-bold text-rose-600">PPN 12%</span>
                                            <span class="font-mono font-bold text-rose-600" x-text="formatRp(numPpn)"></span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                                        <span class="text-xs font-black text-gray-900">Netto Diterima:</span>
                                        <span class="text-base font-black font-mono text-[#0052FF]" x-text="formatRp(numNetto)"></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Footer Modal Form -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer text-xs">
                                Batal
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-black text-white shadow-md shadow-purple-900/20 transition-all cursor-pointer flex items-center space-x-1.5 disabled:opacity-50">
                                <svg wire:loading wire:target="save" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="save">{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Faktur' }}</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL DETAIL FAKTUR PAJAK                      -->
    <!-- ============================================== -->
    @if ($modalDetailOpen && $selectedFaktur)
        <div wire:key="modal-detail-faktur" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetail"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100">

                    <!-- Header Modal Detail -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    Detail Faktur Pajak
                                </h3>
                                <p class="text-xs text-purple-200 font-mono font-bold mt-0.5">
                                    {{ $selectedFaktur->no_faktur }}
                                </p>
                            </div>
                        </div>

                        <button type="button" wire:click="closeDetail" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <!-- Status & Metadata -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50/80 rounded-2xl border border-gray-200/80">
                            <div>
                                <span class="block text-[10px] text-gray-500 font-bold uppercase tracking-wider">Status Faktur</span>
                                @if ($selectedFaktur->is_selesai)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs mt-0.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Selesai ({{ $selectedFaktur->selesai_at?->translatedFormat('d M Y H:i') }})
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
                                <span class="block text-[10px] text-gray-500 font-bold uppercase tracking-wider">Petugas Input</span>
                                <span class="font-bold text-gray-900">{{ $selectedFaktur->user?->name ?: 'Sistem' }}</span>
                            </div>
                        </div>

                        <!-- Rincian Wajib Pajak & Barang -->
                        <div class="space-y-2.5 bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80">
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-semibold">Nama Wajib Pajak:</span>
                                <span class="font-bold text-gray-900">{{ $selectedFaktur->nama_pajak_snapshot }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-semibold">Saluran Via:</span>
                                <span class="font-bold text-gray-900">{{ $selectedFaktur->via_pajak_snapshot }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-semibold">Kode Pajak:</span>
                                <span class="font-bold text-gray-900">{{ $selectedFaktur->kode_pajak_snapshot ?: '-' }}</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200/60">
                                <span class="text-gray-500 font-semibold block mb-1">Uraian Barang:</span>
                                <div class="p-3 bg-white rounded-xl border border-gray-200 text-gray-800 font-medium whitespace-pre-wrap">{{ $selectedFaktur->barang_snapshot }}</div>
                            </div>
                        </div>

                        <!-- Breakdown Finansial -->
                        <div class="space-y-2 bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80">
                            <div class="flex justify-between text-gray-700 font-semibold">
                                <span>Nilai Bruto:</span>
                                <span class="font-mono font-bold text-gray-900">Rp {{ number_format($selectedFaktur->bruto, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500 font-semibold">
                                <span>DPP (Bruto / 1.11):</span>
                                <span class="font-mono">Rp {{ number_format($selectedFaktur->dpp, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500 font-semibold">
                                <span>DPP Lain (11 / 12):</span>
                                <span class="font-mono">Rp {{ number_format($selectedFaktur->dpp_lain, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-rose-600 font-bold">
                                <span>PPN 12%:</span>
                                <span class="font-mono">Rp {{ number_format($selectedFaktur->ppn_nominal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-amber-600 font-bold">
                                <span>{{ $selectedFaktur->jenis_pph_snapshot }} ({{ number_format($selectedFaktur->pph_persen, 2) }}%):</span>
                                <span class="font-mono">Rp {{ number_format($selectedFaktur->pph_nominal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-black pt-2.5 border-t border-gray-200 text-[#0052FF]">
                                <span>Netto Diterima:</span>
                                <span class="font-mono">Rp {{ number_format($selectedFaktur->netto, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if ($selectedFaktur->catatan)
                            <div class="p-3 bg-gray-50/80 rounded-2xl border border-gray-200/80">
                                <span class="text-gray-500 font-bold block mb-1 text-[10px] uppercase">Catatan:</span>
                                <p class="text-gray-800 italic">{{ $selectedFaktur->catatan }}</p>
                            </div>
                        @endif

                        <div class="pt-2 flex justify-end">
                            <button type="button" wire:click="closeDetail" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script>
    window.fakturPajakTable = function(config) {
        return {
            table: null,
            tableSize: localStorage.getItem('soulmate_table_size') || 'M',
            groupByDate: true,
            quickSearch: '',
            rowCount: (config.tableData || []).length,

            initTable() {
                window.activeFakturPajakWire = this.$wire;

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

                this.$nextTick(() => {
                    this.renderTable(config.tableData || []);
                });

                // Listen real-time data updates from Livewire (Rule 24 Standard)
                Livewire.on('faktur-pajak-table-updated', (payload) => {
                    const data = (payload && payload.data) ? payload.data : (Array.isArray(payload) ? payload : []);
                    if (this.table) {
                        this.table.replaceData(data).then(() => {
                            this.rowCount = data.length;
                            this.applyFilter();
                        }).catch(() => {
                            this.table.setData(data);
                            this.rowCount = data.length;
                            this.applyFilter();
                        });
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

            formatAngka(val) {
                return Math.round(Number(val) || 0).toLocaleString('id-ID');
            },

            formatRupiah(val) {
                return this.formatAngka(val);
            },

            renderTable(data) {
                const self = this;
                const container = this.$refs.tableContainer || document.getElementById('faktur-pajak-table');
                if (!container) return;

                this.table = new Tabulator(container, {
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
                        return `
                            <div class="flex flex-wrap items-center justify-between w-full pr-3 py-0.5 text-xs font-bold text-slate-800 cursor-pointer select-none">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-[#5E17EB]/10 text-[#5E17EB] font-black">${value}</span>
                                    <span class="text-slate-600 font-bold text-[11px] font-mono">(${count} Faktur)</span>
                                </div>
                            </div>
                        `;
                    },
                    placeholder: "Belum ada transaksi faktur pajak.",
                    columns: [
                        // 1. NO (DESC) - FIT TO CONTENT
                        {
                            title: "No",
                            field: "no",
                            width: 42,
                            minWidth: 38,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => `<span class="font-mono font-bold text-black">${cell.getValue()}</span>`
                        },
                        // 2. STATUS SELESAI - INTERAKTIF DENGAN ICON TOGGLE
                        {
                            title: "Selesai",
                            field: "is_selesai",
                            width: 55,
                            minWidth: 50,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const d = cell.getData();
                                const isSelesai = !!cell.getValue();
                                const safeNama = (d.nama || '').replace(/'/g, "\\'");
                                const toggleTitle = isSelesai ? 'Tandai Belum Selesai' : 'Tandai Selesai';
                                const btnClass = isSelesai
                                    ? 'bg-emerald-100 hover:bg-amber-100 text-emerald-700 hover:text-amber-700 border-emerald-300 hover:border-amber-300'
                                    : 'bg-gray-100 hover:bg-emerald-100 text-gray-400 hover:text-emerald-700 border-gray-300 hover:border-emerald-300';

                                return `
                                    <button
                                        type="button"
                                        onclick="window.toggleSelesaiConfirm(${d.id}, ${isSelesai ? 'true' : 'false'}, '${safeNama}')"
                                        class="inline-flex items-center justify-center w-5.5 h-5.5 rounded-lg border ${btnClass} transition-colors cursor-pointer shadow-2xs"
                                        title="${toggleTitle}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                `;
                            }
                        },
                        // 3. NAMA - FIT TO CONTENT
                        {
                            title: "Nama",
                            field: "nama",
                            width: 120,
                            minWidth: 100,
                            headerFilter: "input",
                            headerFilterPlaceholder: "",
                            formatter: (cell) => {
                                const d = cell.getData();
                                return `<span class="font-bold text-black block truncate text-xs" title="${d.nama}">${d.nama}</span>`;
                            }
                        },
                        // 4. BARANG - MENYERAP SISA LEBAR LAYAR
                        {
                            title: "Barang",
                            field: "barang",
                            widthGrow: 1,
                            minWidth: 100,
                            headerFilter: "input",
                            headerFilterPlaceholder: "",
                            formatter: (cell) => `<span class="text-black font-medium block text-xs leading-snug truncate" title="${cell.getValue()}">${cell.getValue()}</span>`
                        },
                        // 5. VIA - FIT TO CONTENT
                        {
                            title: "Via",
                            field: "via",
                            width: 80,
                            minWidth: 74,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const val = (cell.getValue() || '').trim();
                                if (!val || val === '-') return '<span class="text-black font-bold font-mono text-xs">-</span>';
                                const v = val.toLowerCase();
                                let colorClass = 'bg-gray-100 text-black border-gray-300';
                                if (v.includes('katalog')) {
                                    colorClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                } else if (v.includes('langsung')) {
                                    colorClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                } else if (v.includes('siplah')) {
                                    colorClass = 'bg-blue-50 text-[#0052FF] border-blue-200';
                                }
                                return `<span class="inline-block px-1.5 py-0.5 rounded text-[9.5px] font-bold border ${colorClass}">${val}</span>`;
                            }
                        },
                        // 6. KODE - FIT TO CONTENT
                        {
                            title: "Kode",
                            field: "kode",
                            width: 58,
                            minWidth: 48,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const val = (cell.getValue() || '').trim();
                                if (!val || val === '-') return '<span class="text-black font-bold font-mono text-xs">-</span>';
                                let colorClass = 'bg-gray-100 text-black border-gray-300';
                                if (val.includes('2')) {
                                    colorClass = 'bg-purple-50 text-[#5E17EB] border-purple-200';
                                } else if (val.includes('3')) {
                                    colorClass = 'bg-orange-50 text-orange-700 border-orange-200';
                                } else if (val.includes('4')) {
                                    colorClass = 'bg-blue-50 text-[#0052FF] border-blue-200';
                                }
                                return `<span class="inline-block px-1.5 py-0.5 rounded text-[9.5px] font-bold border ${colorClass}">${val}</span>`;
                            }
                        },
                        // 7. BRUTO - WIDTH 92 & MINWIDTH 80
                        {
                            title: "Bruto",
                            field: "bruto",
                            width: 92,
                            minWidth: 80,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-bold text-[#5E17EB] whitespace-nowrap">${self.formatAngka(cell.getValue())}</span>`
                        },
                        // 8. DPP - WIDTH 92 & MINWIDTH 80
                        {
                            title: "DPP",
                            field: "dpp",
                            width: 92,
                            minWidth: 80,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-black text-black whitespace-nowrap">${self.formatAngka(cell.getValue())}</span>`
                        },
                        // 9. DPP LAIN - WIDTH 92 & MINWIDTH 80
                        {
                            title: "DPP Lain",
                            field: "dpp_lain",
                            width: 92,
                            minWidth: 80,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-black text-black whitespace-nowrap">${self.formatAngka(cell.getValue())}</span>`
                        },
                        // 10. PPN - WIDTH 92 & MINWIDTH 80
                        {
                            title: "PPN",
                            field: "ppn_nominal",
                            width: 92,
                            minWidth: 80,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-bold text-[#0052FF] whitespace-nowrap">${self.formatAngka(cell.getValue())}</span>`
                        },
                        // 11. JENIS PPH - FIT TO CONTENT
                        {
                            title: "Jenis PPh",
                            field: "jenis_pph",
                            width: 78,
                            minWidth: 72,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const d = cell.getData();
                                const val = (d.jenis_pph || '').toLowerCase();
                                let badgeClass = 'bg-gray-100 text-black border-gray-300';
                                if (val.includes('22')) {
                                    badgeClass = 'bg-amber-50 text-amber-700 border-amber-300';
                                } else if (val.includes('23')) {
                                    badgeClass = 'bg-purple-50 text-[#5E17EB] border-purple-200';
                                }
                                return `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold border ${badgeClass}">${d.jenis_pph}</span>`;
                            }
                        },
                        // 12. PPH - WIDTH 92 & MINWIDTH 80
                        {
                            title: "PPh",
                            field: "pph_nominal",
                            width: 92,
                            minWidth: 80,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-bold text-rose-600 whitespace-nowrap">${self.formatAngka(cell.getValue())}</span>`
                        },
                        // 13. NETTO - WIDTH 92 & MINWIDTH 80
                        {
                            title: "Netto",
                            field: "netto",
                            width: 92,
                            minWidth: 80,
                            hozAlign: "right",
                            headerHozAlign: "right",
                            formatter: (cell) => `<span class="font-mono font-black text-emerald-600 whitespace-nowrap">${self.formatAngka(cell.getValue())}</span>`
                        },
                        // 14. AKSI - FIT TO CONTENT
                        {
                            title: "Aksi",
                            field: "id",
                            width: 72,
                            minWidth: 68,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            headerSort: false,
                            formatter: (cell) => {
                                const d = cell.getData();
                                const safeNama = (d.nama || '').replace(/'/g, "\\'");
                                return `
                                    <div class="flex items-center justify-center gap-0.5">
                                        <!-- Detail -->
                                        <button
                                            type="button"
                                            onclick="window.openDetailFaktur(${d.id})"
                                            class="p-0.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors cursor-pointer"
                                            title="Detail Faktur"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            onclick="window.editFaktur(${d.id})"
                                            class="p-0.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors cursor-pointer"
                                            title="Edit Faktur"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Hapus -->
                                        <button
                                            type="button"
                                            onclick="window.deleteFakturConfirm(${d.id}, '${safeNama}')"
                                            class="p-0.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded transition-colors cursor-pointer"
                                            title="Hapus Faktur"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    'Via': r.via,
                    'Kode': r.kode,
                    'Bruto (Rp)': r.bruto,
                    'DPP (Rp)': r.dpp,
                    'DPP Lain (Rp)': r.dpp_lain,
                    'PPN 12% (Rp)': r.ppn_nominal,
                    'Jenis PPh': r.jenis_pph,
                    'Tarif PPh (%)': r.pph_persen,
                    'PPh (Rp)': r.pph_nominal,
                    'Netto (Rp)': r.netto,
                    'Catatan': r.catatan,
                }));
                const ws = XLSX.utils.json_to_sheet(formatted);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Faktur Pajak");
                XLSX.writeFile(wb, `Laporan_Faktur_Pajak_${new Date().toISOString().slice(0, 10)}.xlsx`);
            }
        };
    };

    // Global Action Helpers linked directly to Livewire Component
    window.toggleSelesaiConfirm = function(id, isSelesai, nama) {
        const title = isSelesai ? 'Tandai Belum Selesai?' : 'Tandai Faktur Selesai?';
        const text = isSelesai
            ? `Ubah status faktur rekanan "${nama}" menjadi Belum Selesai?`
            : `Ubah status faktur rekanan "${nama}" menjadi Selesai?`;
        const confirmText = isSelesai ? 'Ya, Belum Selesai' : 'Ya, Selesai';

        if (typeof window.swalConfirm === 'function') {
            window.swalConfirm(title, text, {
                danger: isSelesai,
                confirmText: confirmText,
                cancelText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed && window.activeFakturPajakWire) {
                    window.activeFakturPajakWire.toggleSelesai(id);
                }
            });
        } else if (confirm(`${title}\n${text}`)) {
            if (window.activeFakturPajakWire) {
                window.activeFakturPajakWire.toggleSelesai(id);
            }
        }
    };

    window.toggleSelesaiFaktur = function(id) {
        if (window.activeFakturPajakWire) {
            window.activeFakturPajakWire.toggleSelesai(id);
        }
    };

    window.openDetailFaktur = function(id) {
        if (window.activeFakturPajakWire) {
            window.activeFakturPajakWire.openDetail(id);
        }
    };

    window.editFaktur = function(id) {
        if (window.activeFakturPajakWire) {
            window.activeFakturPajakWire.edit(id);
        }
    };

    window.deleteFakturConfirm = function(id, nama) {
        if (typeof window.swalConfirm === 'function') {
            window.swalConfirm(
                'Hapus Faktur Pajak?',
                `Data faktur untuk rekanan "${nama}" akan dihapus permanen dari sistem.`,
                { danger: true, confirmText: 'Ya, Hapus', cancelText: 'Batal' }
            ).then((res) => {
                if (res.isConfirmed && window.activeFakturPajakWire) {
                    window.activeFakturPajakWire.delete(id);
                }
            });
        } else if (confirm(`Hapus faktur untuk "${nama}"?`)) {
            if (window.activeFakturPajakWire) {
                window.activeFakturPajakWire.delete(id);
            }
        }
    };
</script>
@endpush


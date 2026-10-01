<div class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOMBOL TAMBAH TRANSAKSI       -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">
                    Transaksi Harian
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Daftar catatan transaksi operasional harian kasir dan layanan servis.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button"
                    wire:click="openCreateModal"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-white font-black text-xs shadow-md shadow-blue-600/25 transition-all cursor-pointer disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Input Transaksi Baru</span>
            </button>
        </div>
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
    <!-- FILTER BAR, PERIODE & PENCARIAN                -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-5 space-y-4 border border-gray-100/90 shadow-sm">

        <!-- Baris 1: Mode Periode Switcher -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-100">
            <div class="flex items-center space-x-1.5 bg-gray-100 p-1 rounded-xl">
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
                <button type="button"
                        wire:click="setPeriodeMode('semua')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'semua' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                    Semua
                </button>
            </div>

            <!-- Input Tanggal Dinamis -->
            <div class="flex items-center space-x-2 sm:ml-auto">
                @if($periodeMode === 'range' || $periodeMode === 'hari_ini')
                    <input type="date"
                           wire:model.live="startDate"
                           class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#0052FF]">
                    <span class="text-xs font-bold text-gray-400">s/d</span>
                    <input type="date"
                           wire:model.live="endDate"
                           class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#0052FF]">
                @elseif($periodeMode === 'bulanan')
                    <select wire:model.live="filterBulan"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ sprintf('%02d', $m) }}">{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
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

        <!-- Baris 2: Search & Filter Kategori/Status/Metode -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">

            <!-- Pencarian Multi-Kata Longgar -->
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#0052FF] focus:ring-2 focus:ring-blue-100 transition-all">
            </div>

            <!-- Filter Tipe Transaksi -->
            <div class="md:col-span-2">
                <select wire:model.live="tipeFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#0052FF] cursor-pointer">
                    <option value="semua">Semua Tipe</option>
                    <option value="penjualan">Penjualan</option>
                    <option value="servis">Servis</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div class="md:col-span-2">
                <select wire:model.live="statusFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#0052FF] cursor-pointer">
                    <option value="semua">Semua Status</option>
                    <option value="Lunas">Lunas</option>
                    <option value="Bon">Bon</option>
                </select>
            </div>

            <!-- Filter Metode & Size Selector -->
            <div class="md:col-span-4 flex items-center gap-2">
                <select wire:model.live="metodeFilter"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#0052FF] cursor-pointer">
                    <option value="semua">Semua Metode</option>
                    @foreach($metodeList as $m)
                        <option value="{{ $m->nama }}">{{ $m->nama }}</option>
                    @endforeach
                </select>
                <x-table-size-selector />
            </div>

        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL DATA TRANSAKSI HARIAN (TABULATOR)        -->
    <!-- ============================================== -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs"
         x-data="tabulatorTransaksiHarianTable({
             tableData: @js($tableData),
             metodeList: @js($metodeList->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama]))
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
                           class="w-full pl-9 pr-3 py-1.5 text-xs font-bold text-gray-900 bg-gray-50/60 border border-gray-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#0052FF] transition-all">
                </div>
                <button type="button"
                        @click="clearAllFilters"
                        class="px-3 py-1.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-900 hover:bg-gray-100 transition-colors flex items-center space-x-1.5 cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-900 font-bold flex-wrap">
                <!-- Font Size Selector -->
                <x-table-size-selector />

                <template x-if="selectedCount > 0">
                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#0052FF] font-bold text-[11px] border border-blue-200"
                          x-text="selectedCount + ' dipilih'"></span>
                </template>
                <span class="text-[11px] text-gray-900 font-bold hidden md:inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    Tarik garis kolom untuk melebarkan
                </span>
                <span class="text-gray-900 font-black" x-text="'Total ' + rowCount + ' Transaksi'"></span>
            </div>
        </div>

        <div :class="'tabulator-size-' + tableSize + ' table-size-' + tableSize">
            <div x-ref="tableContainer" class="rounded-xl border border-gray-200 text-xs"></div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: FORM INPUT / EDIT TRANSAKSI HARIAN    -->
    <!-- ============================================== -->
    @if($modalFormOpen)
        <div wire:key="modal-form-harian" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeFormModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#0052FF] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                                    {{ $transaksiId ? 'Edit Transaksi' : 'Input Transaksi Baru' }}
                                </h3>
                                <p class="text-xs text-blue-100 font-medium mt-0.5">
                                    Entri data transaksi harian mandiri dengan kustomisasi HPP modal dan laba bersih
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <!-- Badge Tanggal Transaksi di Ujung Kanan Header -->
                            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 border border-white/20 text-white text-xs font-bold font-mono shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ \Carbon\Carbon::parse($formTanggalOnly ?: now())->translatedFormat('l, d F Y') }}</span>
                            </div>

                            <button type="button" wire:click="closeFormModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <form wire:submit.prevent="simpanTransaksi">
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

                            <!-- Section 1: Informasi Transaksi & Pelanggan -->
                            <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">1. Informasi Transaksi &amp; Pelanggan</span>
                                    <span class="text-[11px] font-bold text-gray-500 font-mono sm:hidden">
                                        {{ \Carbon\Carbon::parse($formTanggalOnly ?: now())->translatedFormat('d M Y') }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                                    <!-- Tipe Transaksi (Pewarnaan Ungu / Kuning) -->
                                    <div class="sm:col-span-5">
                                        <div class="flex items-center justify-between min-h-[22px] mb-1.5">
                                            <label class="block font-bold text-gray-700">Tipe Transaksi <span class="text-rose-500 font-bold">*</span></label>
                                        </div>
                                        <select wire:model.live="formTipeTransaksi"
                                                class="w-full px-3.5 py-2 rounded-xl border font-bold text-xs transition-all cursor-pointer {{ $formTipeTransaksi === 'penjualan' ? 'bg-purple-50/80 border-purple-300 text-[#5E17EB] focus:ring-2 focus:ring-purple-200' : 'bg-amber-50/80 border-amber-300 text-amber-900 focus:ring-2 focus:ring-amber-200' }}">
                                            <option value="penjualan">Penjualan Produk</option>
                                            <option value="servis">Layanan Servis</option>
                                        </select>
                                    </div>

                                    <!-- Data Pelanggan -->
                                    <div class="sm:col-span-7">
                                        <div class="flex items-center justify-between min-h-[22px] mb-1.5">
                                            <label class="block font-bold text-gray-700">Nama Pelanggan <span class="text-rose-500 font-bold">*</span></label>
                                            <div class="flex items-center space-x-1">
                                                <button type="button"
                                                        wire:click="$set('formCustomerType', 'umum')"
                                                        class="px-2 py-0.5 rounded-lg font-bold text-[10px] transition-all cursor-pointer {{ $formCustomerType === 'umum' ? 'bg-[#0052FF] text-white shadow-2xs' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                                    Umum
                                                </button>
                                                <button type="button"
                                                        wire:click="$set('formCustomerType', 'langganan')"
                                                        class="px-2 py-0.5 rounded-lg font-bold text-[10px] transition-all cursor-pointer {{ $formCustomerType === 'langganan' ? 'bg-[#0052FF] text-white shadow-2xs' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                                    Master
                                                </button>
                                            </div>
                                        </div>

                                        @if($formCustomerType === 'umum')
                                            <div>
                                                <input type="text"
                                                       wire:model="formNamaPelanggan"
                                                       class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF] transition-all">
                                                @error('formNamaPelanggan') <span class="text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                            </div>
                                        @else
                                            <div class="relative">
                                                @if($formCustomerId)
                                                    <div class="px-3.5 py-1.5 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs font-bold min-h-[38px]">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <div class="w-5 h-5 rounded-md bg-blue-200 text-[#0052FF] flex items-center justify-center font-black text-[10px] shrink-0">
                                                                {{ strtoupper(substr($formNamaPelanggan, 0, 1)) }}
                                                            </div>
                                                            <span class="truncate text-blue-950">{{ $formNamaPelanggan }}</span>
                                                            @if($formNoHp)
                                                                <span class="text-blue-600 font-mono text-[10px] shrink-0">({{ $formNoHp }})</span>
                                                            @endif
                                                        </div>
                                                        <button type="button" wire:click="$set('formCustomerId', null)" class="text-rose-600 hover:text-rose-700 text-[10px] font-black hover:underline cursor-pointer ml-2 shrink-0">Ganti</button>
                                                    </div>
                                                @else
                                                    <input type="text"
                                                           wire:model.live.debounce.250ms="formCustomerSearch"
                                                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF] transition-all">
                                                    @if(!empty($customerSuggestions))
                                                        <div class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-48 overflow-y-auto custom-scrollbar">
                                                            @foreach($customerSuggestions as $cs)
                                                                <button type="button"
                                                                        wire:key="sugg-cust-harian-{{ $cs->id }}"
                                                                        wire:click="selectCustomer({{ $cs->id }}, '{{ addslashes($cs->nama) }}', '{{ $cs->no_hp }}')"
                                                                        class="w-full px-3 py-2 text-left hover:bg-blue-50 flex items-center justify-between text-xs cursor-pointer">
                                                                    <span class="font-bold text-gray-900">{{ $cs->nama }}</span>
                                                                    <span class="font-mono text-gray-500 text-[11px]">{{ $cs->no_hp }}</span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif
                                                @error('formNamaPelanggan') <span class="text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Rincian Item Transaksi -->
                            <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">2. Rincian Item Transaksi</span>
                                        <span class="text-[10px] text-gray-500 font-medium">Bisa memilih Master Produk / Servis atau custom manual. Stok tidak akan berkurang.</span>
                                    </div>
                                    <button type="button"
                                            wire:click="addItemRow"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-blue-100 hover:bg-blue-200 text-[#0052FF] font-black text-[11px] transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Tambah Baris</span>
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    @foreach($items as $idx => $itemRow)
                                        <div wire:key="item-row-harian-{{ $idx }}" class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs space-y-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-[10px] font-black text-gray-400 uppercase">Item #{{ $idx + 1 }}</span>
                                                @if(count($items) > 1)
                                                    <button type="button"
                                                            wire:click="removeItemRow({{ $idx }})"
                                                            class="text-rose-500 hover:text-rose-700 text-[10px] font-bold cursor-pointer">
                                                        Hapus Baris
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="space-y-3">
                                                <!-- Baris 1: Item & Jumlah (Sebaris: Item Luas, Jumlah Kompak) -->
                                                <div class="flex items-start gap-2.5">
                                                    <!-- Item (Luas) -->
                                                    <div class="flex-1 min-w-0 relative" x-data="{ openSuggest: false }" @click.outside="openSuggest = false">
                                                        <label class="block font-bold text-gray-700 mb-1 text-[11px]">Item <span class="text-rose-500 font-bold">*</span></label>
                                                        <input type="text"
                                                               wire:model.live.debounce.200ms="items.{{ $idx }}.nama_item"
                                                               @focus="openSuggest = true"
                                                               @click="openSuggest = true"
                                                               @input="openSuggest = true"
                                                               @keydown.escape="openSuggest = false"
                                                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-bold text-gray-900 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                                        @error("items.{$idx}.nama_item")
                                                            <span class="text-rose-500 font-bold text-[10px] block mt-0.5">{{ $message }}</span>
                                                        @enderror

                                                        @if(!empty($productSuggestionsMap[$idx]) && count($productSuggestionsMap[$idx]) > 0)
                                                            <div x-show="openSuggest" class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-60 overflow-y-auto">
                                                                @foreach($productSuggestionsMap[$idx] as $sug)
                                                                    <button type="button"
                                                                            wire:key="sugg-item-harian-{{ $idx }}-{{ $sug['type'] }}-{{ $loop->index }}"
                                                                            @click="openSuggest = false"
                                                                            wire:click="selectItemSuggestion({{ $idx }}, '{{ addslashes($sug['nama']) }}', {{ $sug['harga_modal'] }}, {{ $sug['harga_jual'] }}, {{ $sug['id'] ? $sug['id'] : 'null' }})"
                                                                            class="w-full px-3.5 py-2.5 text-left hover:bg-blue-50/80 flex items-center justify-between text-xs cursor-pointer transition-colors">
                                                                        <div class="min-w-0 pr-3">
                                                                            <div class="flex items-center space-x-2">
                                                                                <span class="font-bold text-gray-900 truncate">{{ $sug['nama'] }}</span>
                                                                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded {{ $sug['type'] === 'produk' ? 'bg-blue-100 text-[#0052FF]' : 'bg-purple-100 text-[#5E17EB]' }}">
                                                                                    {{ $sug['badge'] }}
                                                                                </span>
                                                                            </div>
                                                                            <span class="text-[10px] text-gray-400 font-mono block mt-0.5">{{ $sug['kode'] }}</span>
                                                                        </div>
                                                                        <div class="text-right shrink-0">
                                                                            <span class="text-[10px] text-amber-700 font-bold block">Modal: Rp {{ number_format($sug['harga_modal'], 0, ',', '.') }}</span>
                                                                            <span class="text-xs text-[#0052FF] font-black block">Biaya: Rp {{ number_format($sug['harga_jual'], 0, ',', '.') }}</span>
                                                                        </div>
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Jumlah (Kecil / Kompak) -->
                                                    <div class="w-20 sm:w-24 shrink-0">
                                                        <label class="block font-bold text-gray-700 mb-1 text-[11px] text-center">Jumlah <span class="text-rose-500 font-bold">*</span></label>
                                                        <input type="number"
                                                               min="1"
                                                               wire:model.live.debounce.250ms="items.{{ $idx }}.qty"
                                                               class="w-full px-2 py-2 rounded-xl border border-gray-300 font-mono font-black text-center text-gray-900 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                                        @error("items.{$idx}.qty")
                                                            <span class="text-rose-500 font-bold text-[10px] block mt-0.5 text-center">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Baris 2: Biaya, Modal, Pajak (Sederet) -->
                                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 pt-1 border-t border-gray-100">
                                                    <!-- 1. Biaya (Harga Jual per Unit) -->
                                                    <div class="sm:col-span-5"
                                                         x-data="{
                                                             raw: @entangle('items.' . $idx . '.harga_jual').live,
                                                             get formatted() {
                                                                 if (!this.raw && this.raw !== 0) return '';
                                                                 let num = parseInt(this.raw.toString().replace(/[^0-9]/g, ''), 10);
                                                                 return isNaN(num) || num === 0 ? '' : num.toLocaleString('id-ID');
                                                             },
                                                             update(e) {
                                                                 let clean = e.target.value.replace(/[^0-9]/g, '');
                                                                 this.raw = clean ? parseInt(clean, 10) : 0;
                                                                 $wire.call('recalculateRow', {{ $idx }});
                                                             }
                                                         }">
                                                        <label class="block font-bold text-[#0052FF] mb-1 text-[10px]">Biaya <span class="text-rose-500 font-bold">*</span></label>
                                                        <div class="relative">
                                                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-[10px] font-bold text-blue-700/70 select-none pointer-events-none font-mono">Rp</span>
                                                            <input type="text"
                                                                   inputmode="numeric"
                                                                   :value="formatted"
                                                                   @input="update($event)"
                                                                   class="w-full pl-8 pr-2.5 py-1.5 rounded-lg border border-blue-300 bg-blue-50/40 font-mono font-black text-[#0052FF] text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                                        </div>
                                                        @error("items.{$idx}.harga_jual")
                                                            <span class="text-rose-500 font-bold text-[10px] block mt-0.5">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <!-- 2. Modal (HPP per Unit) -->
                                                    <div class="sm:col-span-4"
                                                         x-data="{
                                                             raw: @entangle('items.' . $idx . '.harga_modal').live,
                                                             get formatted() {
                                                                 if (!this.raw && this.raw !== 0) return '';
                                                                 let num = parseInt(this.raw.toString().replace(/[^0-9]/g, ''), 10);
                                                                 return isNaN(num) || num === 0 ? '' : num.toLocaleString('id-ID');
                                                             },
                                                             update(e) {
                                                                 let clean = e.target.value.replace(/[^0-9]/g, '');
                                                                 this.raw = clean ? parseInt(clean, 10) : 0;
                                                                 $wire.call('recalculateRow', {{ $idx }});
                                                             }
                                                         }">
                                                        <label class="block font-bold text-amber-800 mb-1 text-[10px]">Modal <span class="text-rose-500 font-bold">*</span></label>
                                                        <div class="relative">
                                                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-[10px] font-bold text-amber-700/70 select-none pointer-events-none font-mono">Rp</span>
                                                            <input type="text"
                                                                   inputmode="numeric"
                                                                   :value="formatted"
                                                                   @input="update($event)"
                                                                   class="w-full pl-8 pr-2.5 py-1.5 rounded-lg border border-amber-300 bg-amber-50/40 font-mono font-bold text-amber-900 text-xs focus:ring-2 focus:ring-amber-200 focus:border-amber-500">
                                                        </div>
                                                        @error("items.{$idx}.harga_modal")
                                                            <span class="text-rose-500 font-bold text-[10px] block mt-0.5">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <!-- 3. Pajak -->
                                                    <div class="sm:col-span-3">
                                                        <label class="block font-bold text-gray-700 mb-1 text-[10px]">Pajak</label>
                                                        <select wire:model.live="items.{{ $idx }}.ppn_id"
                                                                class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF] transition-all cursor-pointer">
                                                            @if(!$ppnList->contains(fn($p) => (float)$p->persen == 0))
                                                                <option value="">Tanpa PPN</option>
                                                            @endif
                                                            @foreach($ppnList as $p)
                                                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Subtotal & Laba Baris -->
                                            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                                                <div class="flex items-center space-x-3 text-gray-500 font-medium">
                                                    <span>Subtotal Biaya: <strong class="text-gray-900 font-mono">Rp {{ number_format((float)($itemRow['subtotal_jual'] ?? 0), 0, ',', '.') }}</strong></span>
                                                    <span>Subtotal Modal: <strong class="text-amber-800 font-mono">Rp {{ number_format((float)($itemRow['subtotal_modal'] ?? 0), 0, ',', '.') }}</strong></span>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-600">Laba Bersih:</span>
                                                    <span class="font-mono font-black {{ ($itemRow['laba'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }} ml-1">
                                                        Rp {{ number_format((float)($itemRow['laba'] ?? 0), 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Section 3: Pembayaran, Status, Pegawai & Catatan -->
                            <div class="bg-gray-50/80 p-3.5 rounded-2xl border border-gray-200/80 space-y-2.5">
                                <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block">3. Pembayaran, Status, Pegawai &amp; Catatan</span>

                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 items-start">
                                    <!-- 1. Status Transaksi -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1 text-[10px]">Status <span class="text-rose-500 font-bold">*</span></label>
                                        <select wire:model="formStatus"
                                                class="w-full px-2.5 py-1.5 rounded-xl border border-gray-300 bg-white font-black text-gray-900 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF] transition-all cursor-pointer">
                                            <option value="Lunas">Lunas</option>
                                            <option value="Bon">Bon</option>
                                        </select>
                                    </div>

                                    <!-- 2. Metode Pembayaran -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1 text-[10px]">Metode Pembayaran <span class="text-rose-500 font-bold">*</span></label>
                                        <select wire:model.live="formMetodePembayaranId"
                                                class="w-full px-2.5 py-1.5 rounded-xl border border-gray-300 bg-white font-bold text-gray-900 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF] transition-all cursor-pointer">
                                            <option value="">-- Pilih Metode --</option>
                                            @foreach($metodeList as $m)
                                                <option value="{{ $m->id }}">{{ $m->nama }}</option>
                                            @endforeach
                                        </select>
                                        @error('formMetodePembayaran') <span class="text-rose-500 font-bold text-[10px] block mt-0.5">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- 3. Pegawai / Petugas (Custom Dropdown UI dengan Search) -->
                                    <div x-data="{
                                        open: false,
                                        search: '',
                                        selectedId: @entangle('formPegawaiId').live,
                                        users: @js($pegawaiList->map(fn($p) => [
                                            'id' => $p->id,
                                            'name' => $p->nama,
                                            'role' => $p->role
                                        ])),
                                        get selectedUser() {
                                            return this.users.find(u => u.id == this.selectedId) || { id: null, name: 'Tanpa Pegawai', role: '-' };
                                        },
                                        get filteredUsers() {
                                            if (!this.search.trim()) return this.users;
                                            const q = this.search.toLowerCase();
                                            return this.users.filter(u => u.name.toLowerCase().includes(q) || u.role.toLowerCase().includes(q));
                                        },
                                        select(id) {
                                            this.selectedId = id;
                                            this.open = false;
                                            this.search = '';
                                        }
                                    }" class="relative" @click.outside="open = false; search = ''">
                                        <label class="block font-bold text-gray-700 mb-1 text-[10px]">Pegawai</label>

                                        <!-- Trigger Button -->
                                        <button type="button"
                                                @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                                                class="w-full px-2.5 py-1 rounded-xl border border-gray-300 bg-white text-gray-900 flex items-center justify-between gap-1.5 shadow-2xs hover:border-[#0052FF] focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF] transition-all cursor-pointer text-left min-h-[34px]">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <div class="w-5 h-5 rounded-md flex items-center justify-center font-black text-[9px] shrink-0"
                                                     :class="selectedUser.id ? 'bg-blue-100 text-[#0052FF]' : 'bg-gray-100 text-gray-500'">
                                                    <span x-text="selectedUser.id ? selectedUser.name.charAt(0).toUpperCase() : '-'"></span>
                                                </div>
                                                <div class="min-w-0 leading-tight">
                                                    <span class="block truncate text-[11px] font-bold" :class="selectedUser.id ? 'text-gray-900' : 'text-gray-500'" x-text="selectedUser.name"></span>
                                                    <span class="block truncate text-[9px] font-semibold text-gray-400 capitalize" x-text="selectedUser.role"></span>
                                                </div>
                                            </div>
                                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform shrink-0" :class="open ? 'rotate-180 text-[#0052FF]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>

                                        <!-- Custom Dropdown Menu (Dropup) -->
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute z-50 left-0 right-0 bottom-full mb-1.5 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden divide-y divide-gray-100 max-h-56 overflow-y-auto custom-scrollbar"
                                             style="display: none;">

                                            <!-- Search Box (Zero Placeholder) -->
                                            <div class="p-1.5 border-b border-gray-100 bg-gray-50/80 sticky top-0 z-10">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-gray-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                    </span>
                                                    <input type="text"
                                                           x-model="search"
                                                           x-ref="searchInput"
                                                           @keydown.escape="open = false; search = ''"
                                                           class="w-full pl-7 pr-2 py-1 rounded-lg border border-gray-200 bg-white font-semibold text-gray-900 text-[11px] focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]"
                                                           aria-label="Cari Pegawai">
                                                </div>
                                            </div>

                                            <!-- User List -->
                                            <div class="divide-y divide-gray-100">
                                                <!-- Opsi Tanpa Pegawai / Kosong -->
                                                <button type="button"
                                                        @click="select(null)"
                                                        class="w-full px-2.5 py-1.5 text-left flex items-center justify-between gap-1.5 hover:bg-blue-50/70 transition-colors cursor-pointer"
                                                        :class="!selectedId ? 'bg-blue-50 text-[#0052FF]' : 'text-gray-800'">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <div class="w-5 h-5 rounded-md flex items-center justify-center font-black text-[9px] shrink-0"
                                                             :class="!selectedId ? 'bg-[#0052FF] text-white shadow-2xs' : 'bg-gray-100 text-gray-500'">
                                                            -
                                                        </div>
                                                        <div class="min-w-0 leading-tight">
                                                            <span class="block truncate font-bold text-[11px]" :class="!selectedId ? 'text-[#0052FF]' : 'text-gray-700'">Tanpa Pegawai</span>
                                                            <span class="block truncate text-[9px] font-semibold text-gray-400">-</span>
                                                        </div>
                                                    </div>
                                                    <template x-if="!selectedId">
                                                        <svg class="w-3.5 h-3.5 text-[#0052FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                    </template>
                                                </button>

                                                <template x-for="user in filteredUsers" :key="user.id">
                                                    <button type="button"
                                                            @click="select(user.id)"
                                                            class="w-full px-2.5 py-1.5 text-left flex items-center justify-between gap-1.5 hover:bg-blue-50/70 transition-colors cursor-pointer"
                                                            :class="user.id == selectedId ? 'bg-blue-50 text-[#0052FF]' : 'text-gray-800'">
                                                        <div class="flex items-center gap-1.5 min-w-0">
                                                            <div class="w-5 h-5 rounded-md flex items-center justify-center font-black text-[9px] shrink-0"
                                                                 :class="user.id == selectedId ? 'bg-[#0052FF] text-white shadow-2xs' : 'bg-gray-100 text-gray-700'">
                                                                <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                                            </div>
                                                            <div class="min-w-0 leading-tight">
                                                                <span class="block truncate font-bold text-[11px]" :class="user.id == selectedId ? 'text-[#0052FF]' : 'text-gray-900'" x-text="user.name"></span>
                                                                <span class="block truncate text-[9px] font-semibold text-gray-400 capitalize" x-text="user.role"></span>
                                                            </div>
                                                        </div>
                                                        <template x-if="user.id == selectedId">
                                                            <svg class="w-3.5 h-3.5 text-[#0052FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                        </template>
                                                    </button>
                                                </template>

                                                <template x-if="filteredUsers.length === 0">
                                                    <div class="p-2.5 text-center text-[11px] text-gray-400 font-medium">
                                                        Pegawai tidak ditemukan
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. Catatan Transaksi -->
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1 text-[10px]">Catatan</label>
                                        <input type="text"
                                               wire:model="formCatatan"
                                               class="w-full px-2.5 py-1.5 rounded-xl border border-gray-300 bg-white font-semibold text-gray-800 text-xs focus:ring-2 focus:ring-blue-200 focus:border-[#0052FF]">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Footer Modal Form -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="closeFormModal"
                                    class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer text-xs">
                                Batal
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-xl bg-[#0052FF] hover:bg-blue-700 text-xs font-black text-white shadow-md shadow-blue-900/20 transition-all cursor-pointer flex items-center space-x-1.5 disabled:opacity-50">
                                <svg wire:loading wire:target="simpanTransaksi" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="simpanTransaksi">Simpan Transaksi</span>
                                <span wire:loading wire:target="simpanTransaksi">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 2: LIHAT DETAIL TRANSAKSI                -->
    <!-- ============================================== -->
    @if($modalDetailOpen && $selectedTransaksi)
        <div wire:key="modal-detail-harian" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                    <!-- Header Modal Detail -->
                    <div class="bg-[#0052FF] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Detail Transaksi</h3>
                                <p class="text-xs text-blue-100 font-medium mt-0.5 truncate">
                                    {{ $selectedTransaksi->no_transaksi_frontend }} &bull; {{ $selectedTransaksi->nama_pelanggan }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">

                        <!-- Snapshot Ringkas -->
                        <div class="grid grid-cols-2 {{ $selectedTransaksi->tipe_transaksi === 'servis' && $selectedTransaksi->kategori_perangkat ? 'sm:grid-cols-5' : 'sm:grid-cols-4' }} gap-2.5">
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Penginput</span>
                                <span class="font-bold text-gray-900 block mt-0.5 truncate" title="{{ $selectedTransaksi->userInput?->name ?? 'System' }}">{{ $selectedTransaksi->userInput?->name ?? 'System' }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Tipe</span>
                                <span class="font-bold text-[#0052FF] uppercase block mt-0.5">{{ $selectedTransaksi->tipe_transaksi }}</span>
                            </div>
                            @if($selectedTransaksi->tipe_transaksi === 'servis' && $selectedTransaksi->kategori_perangkat)
                                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                    <span class="text-[10px] font-bold text-amber-900 uppercase block">Perangkat</span>
                                    <span class="font-bold text-amber-900 block mt-0.5">{{ $selectedTransaksi->kategori_perangkat_label }}</span>
                                </div>
                            @endif
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Waktu</span>
                                <span class="font-bold text-gray-900 block mt-0.5">{{ $selectedTransaksi->tanggal ? $selectedTransaksi->tanggal->format('d/m/Y H:i') : '-' }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Petugas</span>
                                <span class="font-bold text-gray-900 block mt-0.5">{{ $selectedTransaksi->pegawai?->nama ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Data Pelanggan & Metode -->
                        <div class="p-3.5 bg-gray-50/80 rounded-2xl border border-gray-200/80 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Pelanggan</span>
                                <span class="font-bold text-gray-900 text-sm block">{{ $selectedTransaksi->nama_pelanggan }}</span>
                                <span class="text-[10px] text-gray-900 font-mono font-bold">{{ $selectedTransaksi->no_hp ?: '-' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Metode / Status</span>
                                <span class="font-bold text-gray-900 block">{{ $selectedTransaksi->metode_pembayaran }}</span>
                                <div class="flex items-center justify-end gap-1.5 mt-0.5">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black {{ $selectedTransaksi->status === 'Lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $selectedTransaksi->status }}
                                    </span>
                                    @if($selectedTransaksi->status === 'Lunas' && ($selectedTransaksi->pelunas || $selectedTransaksi->tanggal_lunas))
                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                            Oleh: {{ $selectedTransaksi->pelunas?->name ?? ($selectedTransaksi->userInput?->name ?? 'System') }} &bull; {{ $selectedTransaksi->tanggal_lunas ? $selectedTransaksi->tanggal_lunas->format('d/m/Y H:i') : '-' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Item Tabel -->
                        <div>
                            <span class="text-[11px] font-black text-gray-900 uppercase tracking-wider block mb-2">Daftar Item Barang / Layanan:</span>
                            <div class="rounded-xl border border-gray-200 overflow-hidden">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 text-[10px] font-black text-gray-900 uppercase">
                                        <tr>
                                            <th class="py-2 px-3">Item</th>
                                            <th class="py-2 px-3 text-center">Qty</th>
                                            <th class="py-2 px-3 text-right">Modal (HPP)</th>
                                            <th class="py-2 px-3 text-right">Harga Jual</th>
                                            <th class="py-2 px-3 text-right">Laba</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($selectedTransaksi->details as $d)
                                            <tr>
                                                <td class="py-2.5 px-3 font-bold text-gray-900">{{ $d->nama_item }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-gray-900">{{ $d->qty }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-amber-800">Rp {{ number_format($d->harga_modal, 0, ',', '.') }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-gray-900">Rp {{ number_format($d->harga_jual, 0, ',', '.') }}</td>
                                                <td class="py-2.5 px-3 text-right font-mono font-black {{ $d->laba >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">Rp {{ number_format($d->laba, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-blue-50/50 border-t border-blue-100 font-bold">
                                        <tr>
                                            <td colspan="2" class="py-2 px-3 text-gray-900 font-bold">Total Ringkasan:</td>
                                            <td class="py-2 px-3 text-right font-mono text-amber-900 font-bold">Rp {{ number_format($selectedTransaksi->total_modal, 0, ',', '.') }}</td>
                                            <td class="py-2 px-3 text-right font-mono text-base text-[#0052FF] font-black">Rp {{ number_format($selectedTransaksi->grand_total, 0, ',', '.') }}</td>
                                            <td class="py-2 px-3 text-right font-mono text-base text-emerald-700 font-black">Rp {{ number_format($selectedTransaksi->total_laba, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        @if($selectedTransaksi->catatan)
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Catatan:</span>
                                <p class="font-bold text-gray-900 mt-0.5 whitespace-pre-line">{{ $selectedTransaksi->catatan }}</p>
                            </div>
                        @endif

                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end rounded-b-3xl border-t border-gray-100">
                        <button type="button"
                                wire:click="closeDetailModal"
                                class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 3: HAPUS TRANSAKSI                       -->
    <!-- ============================================== -->
    @if($modalDeleteOpen)
        <div wire:key="modal-del-harian" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="$set('modalDeleteOpen', false)"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 text-xs">

                    <div class="bg-rose-600 px-6 sm:px-7 py-5 text-white flex items-center justify-between rounded-t-3xl gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-white tracking-tight">Hapus Transaksi</h3>
                        </div>
                    </div>

                    <div class="p-6 space-y-3">
                        <p class="text-gray-700 font-medium leading-relaxed">
                            Apakah Anda yakin ingin menghapus data transaksi <strong class="text-gray-900 font-mono">{{ $deleteCode }}</strong>?
                        </p>
                        <p class="text-[11px] text-gray-500">
                            Tindakan ini permanen dan akan menghapus seluruh rincian item di dalamnya.
                        </p>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-2 rounded-b-3xl border-t border-gray-100">
                        <button type="button"
                                wire:click="$set('modalDeleteOpen', false)"
                                class="px-4 py-2 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="deleteTransaksi"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition-all shadow-md cursor-pointer disabled:opacity-50 flex items-center space-x-1.5">
                            <svg wire:loading wire:target="deleteTransaksi" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="deleteTransaksi">Ya, Hapus</span>
                            <span wire:loading wire:target="deleteTransaksi">Menghapus...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
window.tabulatorTransaksiHarianTable = function(config) {
    return {
        table: null,
        tableSize: localStorage.getItem('soulmate_table_size') || 'md',
        quickSearch: '',
        rowCount: (config.tableData || []).length,
        selectedCount: 0,

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        initTable() {
            window.activeTransaksiHarianWire = this.$wire;

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
                initialSort: [
                    { column: "waktu_epoch", dir: "desc" }
                ],
                placeholder: "Belum ada transaksi hari ini.",
                columns: [
                    { formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 30, minWidth: 30, maxWidth: 30, resizable: false },
                    {
                        title: "Transaksi",
                        field: "tipe_transaksi",
                        width: 130,
                        minWidth: 115,
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => {
                            const d = cell.getData();
                            const isServis = d.tipe_transaksi === 'servis';
                            const badgeColor = isServis
                                ? 'bg-amber-100 text-amber-800 border border-amber-200 font-black'
                                : 'bg-purple-100 text-[#5E17EB] border border-purple-200 font-black';
                            return `
                                <div class="leading-tight py-0.5">
                                    <div class="mb-1">
                                        <span class="inline-block px-2 py-0.5 rounded text-[8.5px] uppercase tracking-wider ${badgeColor}">
                                            ${d.tipe_transaksi}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[9.5px] text-gray-500 font-semibold">
                                        <span class="truncate font-bold text-gray-700" title="Diinput oleh: ${d.user_input}">${d.user_input}</span>
                                        <span class="text-gray-300 font-bold">&bull;</span>
                                        <span class="font-mono text-gray-600 font-bold shrink-0">${d.jam}</span>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        title: "Pelanggan",
                        field: "pelanggan",
                        widthGrow: 1,
                        minWidth: 110,
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => `<span class="font-bold text-gray-900 block truncate text-[11px]" title="${cell.getValue()}">${cell.getValue()}</span>`
                    },
                    {
                        title: "Item",
                        field: "rincian_item",
                        widthGrow: 2,
                        minWidth: 140,
                        headerFilter: "input",
                        headerFilterPlaceholder: "",
                        formatter: (cell) => {
                            const d = cell.getData();
                            const items = d.items_list || [];
                            if (items.length === 0) {
                                return `<span class="text-[10px] text-gray-400 italic">Tanpa item</span>`;
                            }
                            const html = items.map(it => `
                                <div class="flex items-start gap-1.5 leading-tight py-0.5 min-h-[18px]">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#0052FF] shrink-0 mt-1"></span>
                                    <span class="text-[10px] font-bold text-slate-800 leading-snug"><strong class="font-mono text-[#0052FF]">${it.qty}x</strong> ${it.nama}</span>
                                </div>
                            `).join('');
                            return `<div class="py-0.5 space-y-0.5">${html}</div>`;
                        }
                    },
                    {
                        title: "Biaya",
                        field: "grand_total",
                        hozAlign: "right",
                        width: 115,
                        formatter: (cell) => {
                            const d = cell.getData();
                            const items = d.items_list || [];
                            if (items.length === 0) {
                                return `<span class="font-mono font-black text-[#5E17EB] text-[11px]">${this.formatRupiah(d.grand_total)}</span>`;
                            }
                            const html = items.map(it => `
                                <div class="leading-tight py-0.5 min-h-[18px] text-[10.5px] font-bold text-slate-800 font-mono">
                                    ${this.formatRupiah(it.subtotal_jual)}
                                </div>
                            `).join('');
                            return `<div class="py-0.5 space-y-0.5 text-right">${html}</div>`;
                        }
                    },
                    {
                        title: "Modal",
                        field: "total_modal",
                        hozAlign: "right",
                        width: 105,
                        formatter: (cell) => {
                            const d = cell.getData();
                            const items = d.items_list || [];
                            if (items.length === 0) {
                                return `<span class="font-mono font-bold text-amber-600 text-[11px]">${this.formatRupiah(d.total_modal)}</span>`;
                            }
                            const html = items.map(it => `
                                <div class="leading-tight py-0.5 min-h-[18px] text-[10.5px] font-bold text-amber-600 font-mono">
                                    ${this.formatRupiah(it.subtotal_modal)}
                                </div>
                            `).join('');
                            return `<div class="py-0.5 space-y-0.5 text-right">${html}</div>`;
                        }
                    },
                    {
                        title: "Pajak",
                        field: "has_ppn",
                        width: 90,
                        hozAlign: "center",
                        formatter: (cell) => {
                            const d = cell.getData();
                            const items = d.items_list || [];
                            const isMulti = items.length > 1;

                            if (items.length === 0) {
                                const taxLabel = d.has_ppn ? 'PPN' : 'Non PPN';
                                const taxColor = d.has_ppn ? 'text-[#0052FF]' : 'text-pink-600';
                                return `<span class="text-[10px] font-black ${taxColor} uppercase tracking-wider">${taxLabel}</span>`;
                            }

                            if (!isMulti) {
                                const it = items[0];
                                const itemHasPpn = it.has_ppn !== undefined ? it.has_ppn : d.has_ppn;
                                const taxLabel = itemHasPpn ? 'PPN' : 'Non PPN';
                                const taxColor = itemHasPpn ? 'text-[#0052FF]' : 'text-pink-600';
                                return `<span class="text-[10px] font-black ${taxColor} uppercase tracking-wider">${taxLabel}</span>`;
                            }

                            const html = items.map(it => {
                                const itemHasPpn = it.has_ppn !== undefined ? it.has_ppn : d.has_ppn;
                                const taxLabel = itemHasPpn ? 'PPN' : 'Non PPN';
                                const taxColor = itemHasPpn ? 'text-[#0052FF]' : 'text-pink-600';
                                return `
                                    <div class="leading-tight py-0.5 min-h-[18px] text-[10px] font-black ${taxColor} uppercase tracking-wider">
                                        ${taxLabel}
                                    </div>
                                `;
                            }).join('');
                            return `<div class="py-0.5 space-y-0.5 text-center">${html}</div>`;
                        }
                    },
                    {
                        title: "Status",
                        field: "status",
                        width: 115,
                        hozAlign: "center",
                        headerFilter: "list",
                        headerFilterParams: { values: ["Lunas", "Bon"], clearable: true },
                        headerFilterFunc: multiSelectFilterFunc,
                        formatter: (cell) => {
                            const d = cell.getData();
                            const val = (cell.getValue() || '').toString();
                            const isLunas = val.toLowerCase() === 'lunas';
                            const id = d.id;
                            const pelunasInfo = (isLunas && d.pelunas_nama && d.pelunas_nama !== '-')
                                ? `<div class="text-[8.5px] text-emerald-700 font-bold leading-tight mt-0.5 truncate max-w-[105px] mx-auto" title="Dilunasi oleh: ${d.pelunas_nama} (${d.jam_lunas})">${d.pelunas_nama} (${d.jam_lunas})</div>`
                                : '';
                            return `
                                <div class="py-0.5">
                                    <select class="py-0.5 px-1.5 rounded text-[9.5px] font-black border transition-all cursor-pointer focus:ring-1 focus:ring-blue-200 ${
                                        isLunas ? 'bg-emerald-50 text-emerald-800 border-emerald-400 font-black' : 'bg-rose-50 text-rose-800 border-rose-400 font-black'
                                    }" onchange="window.updateStatusHarianDirect(${id}, this.value)">
                                        <option value="Lunas" ${isLunas ? 'selected' : ''}>Lunas</option>
                                        <option value="Bon" ${!isLunas ? 'selected' : ''}>Bon</option>
                                    </select>
                                    ${pelunasInfo}
                                </div>
                            `;
                        }
                    },
                    {
                        title: "Metode",
                        field: "metode_pembayaran",
                        width: 115,
                        hozAlign: "center",
                        formatter: (cell) => {
                            const d = cell.getData();
                            const val = (d.metode_pembayaran || '').trim();
                            const currentId = d.metode_pembayaran_id;
                            const id = d.id;
                            const isCash = val.toLowerCase().includes('cash') || val.toLowerCase().includes('tunai');
                            const isEmpty = !val || val === '-' || val.toLowerCase() === 'tanpa metode';

                            let colorStyle = 'bg-blue-50 text-[#0052FF] border-blue-300';
                            if (isCash) {
                                colorStyle = 'bg-pink-50 text-pink-700 border-pink-300';
                            } else if (isEmpty) {
                                colorStyle = 'bg-gray-100 text-gray-500 border-gray-300';
                            }

                            const options = (config.metodeList || []).map(m => {
                                const selected = (currentId && m.id == currentId) || (m.nama.toLowerCase() === val.toLowerCase()) ? 'selected' : '';
                                return `<option value="${m.id}" ${selected}>${m.nama}</option>`;
                            }).join('');

                            return `
                                <select class="py-0.5 px-1.5 rounded text-[9.5px] font-bold border transition-all cursor-pointer focus:ring-1 focus:ring-blue-200 ${colorStyle}"
                                        onchange="window.updateMetodeHarianDirect(${id}, parseInt(this.value, 10))">
                                    ${options}
                                </select>
                            `;
                        }
                    },
                    {
                        title: "Aksi",
                        field: "id",
                        width: 85,
                        maxWidth: 85,
                        hozAlign: "center",
                        headerSort: false,
                        resizable: false,
                        formatter: (cell) => {
                            const id = cell.getValue();
                            const data = cell.getData();
                            const noTrx = (data.no_transaksi || '').replace(/'/g, "\\'");
                            return `
                                <div class="flex items-center justify-center space-x-1">
                                    <button type="button" onclick="window.viewTransaksiHarianDetail(${id})" class="p-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors cursor-pointer" title="Lihat Detail Transaksi">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button type="button" onclick="window.editTransaksiHarian(${id})" class="p-1 rounded bg-blue-50 hover:bg-blue-100 text-[#0052FF] transition-colors cursor-pointer" title="Edit Transaksi">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" onclick="window.deleteTransaksiHarianConfirm(${id}, '${noTrx}')" class="p-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors cursor-pointer" title="Hapus Transaksi">
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

            Livewire.on('transaksi-harian-table-updated', ({ data }) => {
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
                       (data.pelanggan && data.pelanggan.toLowerCase().includes(term)) ||
                       (data.no_hp && data.no_hp.toLowerCase().includes(term)) ||
                       (data.petugas && data.petugas.toLowerCase().includes(term)) ||
                       (data.rincian_item && data.rincian_item.toLowerCase().includes(term)) ||
                       (data.metode_pembayaran && data.metode_pembayaran.toLowerCase().includes(term)) ||
                       (data.keterangan && data.keterangan.toLowerCase().includes(term)) ||
                       (data.status && data.status.toLowerCase().includes(term));
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

window.viewTransaksiHarianDetail = (id) => {
    if (window.activeTransaksiHarianWire) window.activeTransaksiHarianWire.openDetailModal(id);
};
window.editTransaksiHarian = (id) => {
    if (window.activeTransaksiHarianWire) window.activeTransaksiHarianWire.openEditModal(id);
};
window.deleteTransaksiHarianConfirm = (id, noTrx) => {
    if (window.activeTransaksiHarianWire) window.activeTransaksiHarianWire.confirmDelete(id, noTrx);
};
window.updateStatusHarianDirect = (id, status) => {
    if (window.activeTransaksiHarianWire) window.activeTransaksiHarianWire.updateStatusDirect(id, status);
};
window.updateMetodeHarianDirect = (id, metodeId) => {
    if (window.activeTransaksiHarianWire) window.activeTransaksiHarianWire.updateMetodeDirect(id, metodeId);
};
</script>
@endpush

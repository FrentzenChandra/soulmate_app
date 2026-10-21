<div class="space-y-6">

    <!-- Top Action & Title Bar -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-[#5E17EB] shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Klaim Garansi</h1>
                <p class="text-xs font-semibold text-gray-500 mt-0.5">Pencatatan alur klaim distributor, penanganan kendala barang, dan pengembalian unit</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @can('klaim_garansi.create')
            <button wire:click="openCreateModal"
                    type="button"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-[#5E17EB] text-white hover:bg-purple-700 font-bold text-xs shadow-md shadow-purple-500/20 transition-all cursor-pointer">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Input Klaim Garansi</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Filter Control Bar (Universal Date Standard Rule 13) -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm space-y-3">
        <!-- Baris 1: Mode Periode & Input Tanggal Dinamis -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl">
                <button type="button"
                        wire:click="setPeriodeMode('semua')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeMode === 'semua' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Semua
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('hari_ini')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeMode === 'hari_ini' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Hari Ini
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('range')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeMode === 'range' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Rentang Tanggal
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('bulanan')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeMode === 'bulanan' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Bulanan
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('tahunan')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeMode === 'tahunan' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Tahunan
                </button>
            </div>

            <!-- Dynamic Input Sisi Kanan -->
            <div class="flex items-center space-x-2 sm:ml-auto">
                @if($periodeMode === 'range' || $periodeMode === 'hari_ini')
                    <div class="flex items-center space-x-2">
                        <input type="date"
                               wire:model.live="startDate"
                               class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                        <span class="text-xs font-bold text-gray-400">s/d</span>
                        <input type="date"
                               wire:model.live="endDate"
                               class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                    </div>
                @elseif($periodeMode === 'bulanan')
                    <div class="flex items-center space-x-2">
                        <select wire:model.live="filterBulan"
                                class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
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
                                class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            @for($y = (int) date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                @elseif($periodeMode === 'tahunan')
                    <select wire:model.live="filterTahun"
                            class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                        @for($y = (int) date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                @endif
            </div>
        </div>

        <!-- Baris 2: Dropdown Filter Distributor & Cabang (HQ) -->
        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-gray-100">
            <!-- Filter Distributor -->
            <div class="w-full sm:w-56">
                <select wire:model.live="distributorFilter"
                        class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                    <option value="semua">Semua Distributor</option>
                    @foreach($distributors as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Cabang (HQ Saja Rule 4) -->
            @if($isHQ && $cabangs->isNotEmpty())
            <div class="w-full sm:w-56">
                <select wire:model.live="cabangFilter"
                        class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-purple-200 bg-purple-50/40 text-[#5E17EB] focus:bg-white focus:border-[#5E17EB]">
                    <option value="semua">Semua Cabang (HQ)</option>
                    @foreach($cabangs as $cab)
                        <option value="{{ $cab->id }}">{{ $cab->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL DATA KLAIM GARANSI (TABULATOR STANDARD)  -->
    <!-- ============================================== -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs"
         x-data="klaimGaransiTable({
             tableData: @js($tableData),
         })"
         x-init="initTable()"
         wire:ignore>

        <!-- Toolbar Atas Tabel (Pola Transaksi FULL) -->
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
                <!-- Dropdown Group per Tanggal atau Hari -->
                <div class="flex items-center gap-1.5">
                    <div class="relative">
                        <select x-model="groupMode"
                                @change="handleGroupChange($event.target.value)"
                                class="text-xs font-bold pl-8 pr-7 py-1.5 rounded-xl border border-gray-300 text-gray-800 bg-white hover:border-[#5E17EB] focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] transition-colors cursor-pointer shadow-2xs appearance-none">
                            <option value="tanggal">Group: Per Tanggal</option>
                            <option value="none">Tanpa Grouping</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#5E17EB]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Quick Collapse / Expand Buttons saat Group Aktif -->
                    <template x-if="groupMode === 'tanggal'">
                        <div class="flex items-center gap-1 border-l border-gray-200 pl-1.5">
                            <button type="button"
                                    @click="expandAllGroups()"
                                    class="px-2 py-1 text-[11px] font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors cursor-pointer"
                                    title="Buka Semua Dropdown Tanggal">
                                Buka Semua
                            </button>
                            <button type="button"
                                    @click="collapseAllGroups()"
                                    class="px-2 py-1 text-[11px] font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors cursor-pointer"
                                    title="Tutup Semua Dropdown Tanggal">
                                Tutup Semua
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Font Size Selector (Rule 11) -->
                <x-table-size-selector />

                <span class="text-gray-900 font-black" x-text="'Total ' + rowCount + ' Klaim'"></span>
            </div>
        </div>

        <!-- Tabulator Table Mount Element -->
        <div :class="'tabulator-size-' + tableSize + ' table-size-' + tableSize + ' tabulator-dense'">
            <div x-ref="tableContainer" id="klaim-garansi-table" class="rounded-xl border border-gray-200 text-xs"></div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL FORM INPUT / EDIT KLAIM GARANSI (Rule 21: sm:max-w-2xl)        -->
    <!-- ===================================================================== -->
    @if($modalFormOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-xs" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl border border-gray-100">
                <!-- Header Modal -->
                <div class="px-6 py-4 bg-[#5E17EB] text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-extrabold tracking-tight">
                            {{ $klaimId ? 'Edit Data Klaim Garansi' : 'Input Klaim Garansi Baru' }}
                        </h3>
                    </div>
                    <button wire:click="closeModal" type="button" class="text-white/80 hover:text-white transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form Content -->
                <form wire:submit.prevent="save" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Tanggal Masuk (Required) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Tanggal barang masuk <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input type="date"
                               wire:model="tanggalMasuk"
                               class="w-full text-xs font-semibold px-3.5 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                        @error('tanggalMasuk') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Customer Selection Standard (Rule 5) -->
                    <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700">
                                Customer <span class="text-rose-500 font-bold">*</span>
                            </label>
                            @if(! $klaimId)
                            <div class="flex items-center space-x-1 bg-white p-0.5 rounded-lg border border-gray-200 text-[11px]">
                                <button type="button"
                                        wire:click="$set('customerType', 'langganan')"
                                        class="px-2.5 py-1 rounded-md font-bold transition-all {{ $customerType === 'langganan' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600' }}">
                                    Pilih Terdaftar
                                </button>
                                <button type="button"
                                        wire:click="$set('customerType', 'baru')"
                                        class="px-2.5 py-1 rounded-md font-bold transition-all {{ $customerType === 'baru' ? 'bg-[#5E17EB] text-white shadow-xs' : 'text-gray-600' }}">
                                    + Pelanggan Baru
                                </button>
                            </div>
                            @endif
                        </div>

                        @if($customerType === 'baru')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">
                                        Nama Lengkap Pelanggan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="text"
                                           wire:model="newCustomerNama"
                                           class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] bg-white">
                                    @error('newCustomerNama') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">
                                        Nomor WhatsApp
                                    </label>
                                    <input type="text"
                                           wire:model="newCustomerKontak"
                                           class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] bg-white">
                                    @error('newCustomerKontak') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @else
                            @if($selectedCustomerId || $selectedCustomerNama)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-purple-50/60 border border-purple-100">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-8 h-8 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-xs">
                                            {{ strtoupper(substr($selectedCustomerNama, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-gray-900">{{ $selectedCustomerNama }}</div>
                                            @if($selectedCustomerKontak)
                                                <div class="text-[11px] text-gray-500">{{ $selectedCustomerKontak }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button"
                                            wire:click="resetSelectedCustomer"
                                            class="text-xs font-bold text-purple-700 hover:text-purple-900 hover:underline cursor-pointer">
                                        Ganti
                                    </button>
                                </div>
                            @else
                                <div class="relative">
                                    <input type="text"
                                           wire:model.live.debounce.250ms="customerSearch"
                                           class="w-full text-xs font-semibold px-3.5 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] bg-white">

                                    @if($customerSuggestions->isNotEmpty())
                                        <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-100 max-h-48 overflow-y-auto divide-y divide-gray-50">
                                            @foreach($customerSuggestions as $sug)
                                                <button type="button"
                                                        wire:click="selectCustomer({{ $sug->id }}, '{{ addslashes($sug->nama) }}', '{{ addslashes($sug->no_hp ?? '') }}')"
                                                        class="w-full text-left px-3.5 py-2 text-xs hover:bg-purple-50 flex items-center justify-between transition-colors cursor-pointer">
                                                    <span class="font-bold text-gray-900">{{ $sug->nama }}</span>
                                                    <span class="text-gray-400 text-[11px]">{{ $sug->no_hp ?? '-' }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @error('customerSearch') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        @endif
                    </div>

                    <!-- Barang (Single Binding & Visual Terpilih Standard) -->
                    <div class="space-y-1.5" x-data="{ showDropdown: false }">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700">
                                Barang <span class="text-rose-500 font-bold">*</span>
                            </label>
                            @if($formBarang)
                                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">
                                    Terisi
                                </span>
                            @endif
                        </div>

                        <!-- Card preview barang terpilih (sangat jelas saat edit) -->
                        @if($formBarang)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-purple-50/70 border border-purple-200">
                                <div class="flex items-center space-x-2 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-[#5E17EB] text-white flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[10px] text-gray-500 font-medium block">Nama Barang Tersimpan:</span>
                                        <span class="text-xs font-extrabold text-gray-900 truncate block">{{ $formBarang }}</span>
                                    </div>
                                </div>
                                <button type="button"
                                        wire:click="$set('formBarang', '')"
                                        class="text-xs font-bold text-purple-700 hover:text-purple-900 px-2 py-1 hover:bg-purple-100 rounded-lg transition-colors cursor-pointer shrink-0">
                                    Ganti
                                </button>
                            </div>
                        @endif

                        <div class="relative" @click.outside="showDropdown = false">
                            <input type="text"
                                   wire:model.live.debounce.250ms="formBarang"
                                   @focus="showDropdown = true"
                                   @input="showDropdown = true"
                                   class="w-full text-xs font-semibold px-3.5 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] bg-white">

                            @if($productSuggestions->isNotEmpty())
                                <div x-show="showDropdown"
                                     class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto divide-y divide-gray-50">
                                    <div class="px-3 py-1.5 bg-gray-50 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                        Saran Master Produk (Klik untuk memilih)
                                    </div>
                                    @foreach($productSuggestions as $pSug)
                                        <button type="button"
                                                wire:click="selectProduct('{{ addslashes($pSug->nama_produk) }}'); showDropdown = false;"
                                                class="w-full text-left px-3.5 py-2 text-xs hover:bg-purple-50 flex items-center justify-between transition-colors cursor-pointer">
                                            <span class="font-bold text-gray-900">{{ $pSug->nama_produk }}</span>
                                            <span class="text-gray-400 text-[10px] bg-gray-100 px-1.5 py-0.5 rounded font-mono">Pilih</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <p class="text-[10px] text-gray-400">Pencarian produk hanya mengambil nama barang dan tidak mengubah stok fisik.</p>
                        @error('formBarang') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keluhan (Required Text) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Keluhan <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <textarea wire:model="formKeluhan"
                                  rows="2"
                                  class="w-full text-xs font-semibold px-3.5 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]"></textarea>
                        @error('formKeluhan') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Distributor & Tanggal Kirim -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Distributor
                            </label>
                            <select wire:model="formMasterDistributorId"
                                    class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                                <option value="">Pilih Distributor</option>
                                @foreach($distributors as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                @endforeach
                            </select>
                            @error('formMasterDistributorId') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Kirim
                            </label>
                            <input type="date"
                                   wire:model="formTanggalKirim"
                                   class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            @error('formTanggalKirim') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Tindakan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Tindakan
                        </label>
                        <textarea wire:model="formTindakan"
                                  rows="2"
                                  class="w-full text-xs font-semibold px-3.5 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]"></textarea>
                        @error('formTindakan') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tanggal Selesai Klaim & Tanggal Barang Kembali -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Selesai Klaim
                            </label>
                            <input type="date"
                                   wire:model="formTanggalSelesaiKlaim"
                                   class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            @error('formTanggalSelesaiKlaim') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Barang Kembali
                            </label>
                            <input type="date"
                                   wire:model="formTanggalBarangKembali"
                                   class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            @error('formTanggalBarangKembali') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Penerima Barang -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Penerima Barang
                        </label>
                        <select wire:model="formPenerimaPegawaiId"
                                class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            <option value="">Pilih Penerima (Pegawai)</option>
                            @foreach($pegawais as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                        @error('formPenerimaPegawaiId') <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Footer & Tombol Kasir Loading Standard (Rule 2) -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-2">
                        <button type="button"
                                wire:click="closeModal"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="inline-flex items-center px-5 py-2 rounded-xl bg-[#5E17EB] text-white hover:bg-purple-700 text-xs font-bold shadow-md shadow-purple-500/20 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="save" class="animate-spin w-4 h-4 text-white mr-1.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="save">{{ $klaimId ? 'Perbarui Data' : 'Simpan Klaim' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- ===================================================================== -->
    <!-- MODAL DETAIL KLAIM GARANSI                                           -->
    <!-- ===================================================================== -->
    @if($modalDetailOpen && $selectedKlaim)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-xs" wire:click="closeDetailModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl border border-gray-100">
                <div class="px-6 py-4 bg-gray-900 text-white flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400">Rincian Informasi</span>
                        <h3 class="text-sm font-extrabold text-white">Klaim {{ $selectedKlaim->no_klaim }}</h3>
                    </div>
                    <button wire:click="closeDetailModal" type="button" class="text-white/80 hover:text-white transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs font-semibold text-gray-700">
                    <div class="grid grid-cols-2 gap-3 p-3 bg-purple-50/50 rounded-2xl border border-purple-100">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Tanggal Selesai Klaim</span>
                            <span class="font-extrabold text-emerald-700 text-sm">
                                {{ $selectedKlaim->tanggal_selesai_klaim ? \Carbon\Carbon::parse($selectedKlaim->tanggal_selesai_klaim)->format('d F Y') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Tanggal barang masuk</span>
                            <span class="font-extrabold text-gray-900 text-sm">
                                {{ $selectedKlaim->tanggal_masuk ? \Carbon\Carbon::parse($selectedKlaim->tanggal_masuk)->format('d F Y') : '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Customer</span>
                            <span class="font-bold text-gray-900">{{ $selectedKlaim->customer_nama }}</span>
                            @if($selectedKlaim->customer_kontak)
                                <div class="text-[11px] text-gray-500 font-normal">{{ $selectedKlaim->customer_kontak }}</div>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Barang</span>
                            <span class="font-bold text-gray-900">{{ $selectedKlaim->barang }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Keluhan</span>
                        <p class="text-gray-800 font-normal mt-0.5 whitespace-pre-line">{{ $selectedKlaim->keluhan }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Distributor</span>
                            <span class="font-bold text-purple-700">{{ $selectedKlaim->distributor_nama ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Tanggal Kirim</span>
                            <span>{{ $selectedKlaim->tanggal_kirim ? \Carbon\Carbon::parse($selectedKlaim->tanggal_kirim)->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Tindakan</span>
                        <p class="text-gray-800 font-normal mt-0.5 whitespace-pre-line">{{ $selectedKlaim->tindakan ?: '-' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Tanggal Barang Kembali</span>
                            <span>{{ $selectedKlaim->tanggal_barang_kembali ? \Carbon\Carbon::parse($selectedKlaim->tanggal_barang_kembali)->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Penerima Barang</span>
                            <span class="font-bold text-gray-900">{{ $selectedKlaim->penerima_nama ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 bg-gray-50 text-right border-t border-gray-100">
                    <button wire:click="closeDetailModal"
                            type="button"
                            class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-xs font-bold transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ===================================================================== -->
    <!-- MODAL CONFIRM DELETE                                                 -->
    <!-- ===================================================================== -->
    @if($modalDeleteOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-xs" wire:click="$set('modalDeleteOpen', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-gray-900">Hapus Klaim Garansi?</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Nomor: <span class="font-bold text-gray-800">{{ $deleteNoKlaim }}</span></p>
                    </div>
                </div>

                <p class="text-xs text-gray-600">
                    Data klaim garansi yang dihapus akan dipindahkan ke arsip sampah dan tidak dapat diakses lagi di tabel aktif.
                </p>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end space-x-2">
                    <button wire:click="$set('modalDeleteOpen', false)"
                            type="button"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold transition-all cursor-pointer">
                            Batal
                    </button>
                    <button wire:click="deleteKlaim"
                            type="button"
                            wire:loading.attr="disabled"
                            wire:target="deleteKlaim"
                            class="inline-flex items-center px-4 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700 text-xs font-bold transition-all cursor-pointer disabled:opacity-50">
                        <svg wire:loading wire:target="deleteKlaim" class="animate-spin w-4 h-4 text-white mr-1.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deleteKlaim">Hapus Data</span>
                        <span wire:loading wire:target="deleteKlaim">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    /* 2 Baris untuk Judul Kolom (TH) agar pas dan tidak truncate */
    #klaim-garansi-table .tabulator-header .tabulator-col {
        height: auto !important;
        vertical-align: middle !important;
    }
    #klaim-garansi-table .tabulator-header .tabulator-col .tabulator-col-content {
        padding: 6px 3px !important;
        height: auto !important;
    }
    #klaim-garansi-table .tabulator-header .tabulator-col .tabulator-col-content .tabulator-col-title {
        white-space: normal !important;
        line-height: 1.25 !important;
        text-overflow: clip !important;
        word-break: normal !important;
        overflow-wrap: break-word !important;
        max-height: none !important;
        display: block !important;
        font-size: 10px !important;
        letter-spacing: 0.02em !important;
    }
    /* Group Header Collapsible Accordion Indicator */
    #klaim-garansi-table .tabulator-row.tabulator-group {
        background-color: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        cursor: pointer !important;
        transition: background-color 0.15s ease;
    }
    #klaim-garansi-table .tabulator-row.tabulator-group:hover {
        background-color: #f1f5f9 !important;
    }
    #klaim-garansi-table .tabulator-group .tabulator-group-chevron {
        transition: transform 0.2s ease;
        transform: rotate(0deg);
    }
    #klaim-garansi-table .tabulator-group:not(.tabulator-group-visible) .tabulator-group-chevron {
        transform: rotate(-90deg);
    }
</style>
@endpush

@push('scripts')
<script>
    window.klaimGaransiTable = function(config) {
        return {
            table: null,
            tableData: config.tableData || [],
            quickSearch: '',
            groupMode: 'tanggal',
            tableSize: localStorage.getItem('soulmate_table_size') || 'M',
            rowCount: 0,

            initTable() {
                const self = this;
                window.activeKlaimGaransiWire = this.$wire;

                this.$wire.on('klaim-garansi-table-updated', (event) => {
                    let data = event.data || (Array.isArray(event) ? event[0] : []);
                    if (self.table) {
                        self.table.setData(data);
                        self.rowCount = data.length;
                    }
                });

                window.addEventListener('table-size-changed', (e) => {
                    self.tableSize = e.detail;
                    if (self.table) {
                        self.table.redraw(true);
                    }
                });

                this.renderTable(this.tableData);
            },

            applyFilter() {
                if (!this.table) return;
                const q = this.quickSearch.trim().toLowerCase();
                if (!q) {
                    this.table.clearFilter();
                    this.rowCount = this.table.getData().length;
                    return;
                }
                const terms = q.split(/\s+/).filter(Boolean);
                this.table.setFilter((row) => {
                    const text = [
                        row.no_klaim || '',
                        row.customer_nama || '',
                        row.customer_kontak || '',
                        row.barang || '',
                        row.keluhan || '',
                        row.distributor_nama || '',
                        row.tindakan || '',
                        row.penerima_nama || '',
                        row.tanggal_selesai_klaim_formatted || '',
                        row.tanggal_masuk_formatted || ''
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

            handleGroupChange(val) {
                this.groupMode = val;
                if (this.table) {
                    this.table.setGroupBy(val === 'tanggal' ? "tanggal_group" : false);
                    this.table.redraw(true);
                }
            },

            expandAllGroups() {
                if (this.table) {
                    this.table.getGroups().forEach(g => g.show());
                }
            },

            collapseAllGroups() {
                if (this.table) {
                    this.table.getGroups().forEach(g => g.hide());
                }
            },

            renderTable(data) {
                const self = this;
                const container = this.$refs.tableContainer || document.getElementById('klaim-garansi-table');
                if (!container) return;

                this.table = new Tabulator(container, {
                    data: data,
                    layout: "fitColumns",
                    responsiveLayout: false,
                    pagination: "local",
                    paginationSize: 200,
                    paginationSizeSelector: [200, 1000, 2000, 10000],
                    groupBy: this.groupMode === 'tanggal' ? "tanggal_group" : false,
                    groupToggleElement: "header",
                    groupStartOpen: true,
                    groupOrder: function(a, b, aGroup, bGroup) {
                        let aRows = aGroup.getRows();
                        let bRows = bGroup.getRows();
                        let aTime = aRows.length ? (aRows[0].getData().epoch_sort || 0) : 0;
                        let bTime = bRows.length ? (bRows[0].getData().epoch_sort || 0) : 0;
                        return bTime - aTime;
                    },
                    initialSort: [
                        { column: "tanggal_selesai_klaim", dir: "desc" }
                    ],
                    dataFiltered: function(filters, rows) {
                        self.rowCount = rows.length;
                    },
                    groupHeader: function(value, count, data, group) {
                        return `
                            <div class="flex items-center justify-between w-full pr-3 py-1 text-xs font-bold text-slate-800 cursor-pointer select-none">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#5E17EB] tabulator-group-chevron shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span class="px-2.5 py-0.5 rounded-lg bg-[#5E17EB]/10 text-[#5E17EB] font-black text-xs">${value}</span>
                                    <span class="text-slate-600 font-bold text-[11px] font-mono">(${count} Klaim)</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-semibold hidden sm:inline-block">Klik baris untuk buka atau tutup</span>
                            </div>
                        `;
                    },
                    placeholder: "Belum ada data klaim garansi.",
                    columns: [
                        // NO
                        {
                            title: "No",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">No</div>`,
                            field: "no",
                            width: 44,
                            minWidth: 40,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => `<span class="font-mono font-bold text-slate-500">${cell.getValue()}</span>`
                        },
                        // 1. Tanggal Selesai Klaim (Posisi 1 terdepan - default sort desc)
                        {
                            title: "Tanggal Selesai Klaim",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">Tanggal Selesai<br>Klaim</div>`,
                            field: "tanggal_selesai_klaim",
                            width: 135,
                            minWidth: 115,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const d = cell.getData();
                                if (d.tanggal_selesai_klaim_formatted) {
                                    return `<span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">${d.tanggal_selesai_klaim_formatted}</span>`;
                                }
                                return '<span class="text-gray-300 font-bold">-</span>';
                            }
                        },
                        // 2. Tanggal barang masuk (Posisi 2)
                        {
                            title: "Tanggal barang masuk",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">Tanggal Barang<br>Masuk</div>`,
                            field: "tanggal_masuk",
                            width: 130,
                            minWidth: 110,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const d = cell.getData();
                                return `<span class="font-semibold text-gray-800">${d.tanggal_masuk_formatted || '-'}</span>`;
                            }
                        },
                        // 3. Customer
                        {
                            title: "Customer",
                            titleFormatter: () => `<div class="leading-tight text-left font-extrabold uppercase">Customer</div>`,
                            field: "customer_nama",
                            width: 140,
                            minWidth: 120,
                            formatter: (cell) => {
                                const d = cell.getData();
                                return `
                                    <div class="leading-tight py-0.5">
                                        <div class="font-bold text-gray-900 truncate" title="${d.customer_nama}">${d.customer_nama}</div>
                                        ${d.customer_kontak ? `<div class="text-[10.5px] text-gray-400 font-mono">${d.customer_kontak}</div>` : ''}
                                    </div>
                                `;
                            }
                        },
                        // 4. Barang (Menyerap ruang)
                        {
                            title: "Barang",
                            titleFormatter: () => `<div class="leading-tight text-left font-extrabold uppercase">Barang</div>`,
                            field: "barang",
                            widthGrow: 1,
                            minWidth: 130,
                            formatter: (cell) => `<span class="font-bold text-gray-900 block truncate" title="${cell.getValue()}">${cell.getValue()}</span>`
                        },
                        // 5. Keluhan
                        {
                            title: "Keluhan",
                            titleFormatter: () => `<div class="leading-tight text-left font-extrabold uppercase">Keluhan</div>`,
                            field: "keluhan",
                            widthGrow: 1,
                            minWidth: 150,
                            formatter: (cell) => `<span class="text-gray-600 block text-xs leading-snug line-clamp-2" title="${cell.getValue()}">${cell.getValue()}</span>`
                        },
                        // 6. Distributor
                        {
                            title: "Distributor",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">Distributor</div>`,
                            field: "distributor_nama",
                            width: 130,
                            minWidth: 110,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const val = cell.getValue();
                                if (!val || val === '-') return '<span class="text-gray-300 font-bold">-</span>';
                                return `<span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-purple-50 text-[#5E17EB] border border-purple-200">${val}</span>`;
                            }
                        },
                        // 7. Tanggal Kirim
                        {
                            title: "Tanggal Kirim",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">Tanggal<br>Kirim</div>`,
                            field: "tanggal_kirim",
                            width: 110,
                            minWidth: 95,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const d = cell.getData();
                                return d.tanggal_kirim_formatted ? `<span class="font-medium text-gray-700">${d.tanggal_kirim_formatted}</span>` : '<span class="text-gray-300 font-bold">-</span>';
                            }
                        },
                        // 8. Tindakan
                        {
                            title: "Tindakan",
                            titleFormatter: () => `<div class="leading-tight text-left font-extrabold uppercase">Tindakan</div>`,
                            field: "tindakan",
                            widthGrow: 1,
                            minWidth: 130,
                            formatter: (cell) => {
                                const val = cell.getValue();
                                if (!val || val === '-') return '<span class="text-gray-300 font-bold">-</span>';
                                return `<span class="text-gray-700 block text-xs leading-snug line-clamp-2" title="${val}">${val}</span>`;
                            }
                        },
                        // 9. Tanggal Barang Kembali
                        {
                            title: "Tanggal Barang Kembali",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">Tanggal Barang<br>Kembali</div>`,
                            field: "tanggal_barang_kembali",
                            width: 130,
                            minWidth: 110,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const d = cell.getData();
                                return d.tanggal_barang_kembali_formatted ? `<span class="font-medium text-gray-700">${d.tanggal_barang_kembali_formatted}</span>` : '<span class="text-gray-300 font-bold">-</span>';
                            }
                        },
                        // 10. Penerima Barang
                        {
                            title: "Penerima Barang",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">Penerima<br>Barang</div>`,
                            field: "penerima_nama",
                            width: 125,
                            minWidth: 105,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: (cell) => {
                                const val = cell.getValue();
                                if (!val || val === '-') return '<span class="text-gray-300 font-bold">-</span>';
                                return `<span class="font-semibold text-gray-800">${val}</span>`;
                            }
                        },
                        // Aksi
                        {
                            title: "Aksi",
                            titleFormatter: () => `<div class="leading-tight text-center font-extrabold uppercase">Aksi</div>`,
                            field: "id",
                            width: 80,
                            minWidth: 76,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            headerSort: false,
                            formatter: (cell) => {
                                const d = cell.getData();
                                const safeNoKlaim = (d.no_klaim || '').replace(/'/g, "\\'");
                                return `
                                    <div class="flex items-center justify-center gap-0.5">
                                        <!-- Detail -->
                                        <button
                                            type="button"
                                            onclick="window.openDetailKlaim(${d.id})"
                                            class="p-1 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                            title="Detail Klaim"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            onclick="window.openEditKlaim(${d.id})"
                                            class="p-1 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Klaim"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Hapus -->
                                        <button
                                            type="button"
                                            onclick="window.confirmDeleteKlaim(${d.id}, '${safeNoKlaim}')"
                                            class="p-1 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Klaim"
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

                this.rowCount = data.length;
            }
        };
    };

    // Global action helpers
    window.openDetailKlaim = function(id) {
        if (window.activeKlaimGaransiWire) {
            window.activeKlaimGaransiWire.openDetailModal(id);
        }
    };

    window.openEditKlaim = function(id) {
        if (window.activeKlaimGaransiWire) {
            window.activeKlaimGaransiWire.openEditModal(id);
        }
    };

    window.confirmDeleteKlaim = function(id, noKlaim) {
        if (window.activeKlaimGaransiWire) {
            window.activeKlaimGaransiWire.confirmDelete(id, noKlaim);
        }
    };
</script>
@endpush

<div class="space-y-6" x-data="{
    tableSize: localStorage.getItem('soulmate_table_size') || 'M',
    init() {
        window.addEventListener('table-size-changed', (e) => {
            this.tableSize = e.detail;
        });
    }
}">

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
            @can('klaim_garansi.export_excel')
            <button wire:click="exportExcel"
                    type="button"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-bold text-xs shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Export Excel</span>
            </button>
            @endcan

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

        <!-- Baris 2: Search, Distributor Filter, Branch Selector (HQ), & Table Size -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100">
            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-[280px]">
                <!-- Search Input Non-Strict (Rule 3) -->
                <div class="relative flex-1 min-w-[220px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           class="w-full text-xs font-semibold pl-9 pr-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                </div>

                <!-- Distributor Filter -->
                <div class="w-48">
                    <select wire:model.live="distributorFilter"
                            class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                        <option value="semua">Semua Distributor</option>
                        @foreach($distributors as $dist)
                            <option value="{{ $dist->id }}">{{ $dist->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cabang Filter (HQ Only Rule 4) -->
                @if($isHQ && $cabangs->isNotEmpty())
                <div class="w-48">
                    <select wire:model.live="cabangFilter"
                            class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                        <option value="semua">Semua Cabang</option>
                        @foreach($cabangs as $cab)
                            <option value="{{ $cab->id }}">{{ $cab->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <!-- Table Size Selector (Rule 11) -->
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-400">Ukuran:</span>
                <x-table-size-selector />
            </div>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse"
                   :class="{
                       'text-[11px]': tableSize === 'S',
                       'text-xs': tableSize === 'M',
                       'text-[13px]': tableSize === 'L'
                   }">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3 font-extrabold text-[#5E17EB]">Tanggal Selesai Klaim</th>
                        <th class="py-3 px-3">Tanggal barang masuk</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3 min-w-[160px]">Barang</th>
                        <th class="py-3 px-3 min-w-[180px]">Keluhan</th>
                        <th class="py-3 px-3">Distributor</th>
                        <th class="py-3 px-3">Tanggal Kirim</th>
                        <th class="py-3 px-3 min-w-[160px]">Tindakan</th>
                        <th class="py-3 px-3">Tanggal Barang Kembali</th>
                        <th class="py-3 px-3">Penerima Barang</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold text-gray-800">
                    @forelse($klaimList as $index => $item)
                        <tr class="hover:bg-purple-50/30 transition-colors">
                            <td class="py-2.5 px-3 text-center text-gray-400 font-medium">
                                {{ $klaimList->firstItem() + $index }}
                            </td>

                            <!-- 1. Tanggal Selesai Klaim (Posisi 1 terdepan) -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($item->tanggal_selesai_klaim)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                                        {{ \Carbon\Carbon::parse($item->tanggal_selesai_klaim)->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 font-normal">&mdash;</span>
                                @endif
                            </td>

                            <!-- 2. Tanggal barang masuk -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                {{ $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk)->format('d/m/Y') : '-' }}
                            </td>

                            <!-- 3. Customer -->
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-gray-900 leading-tight">{{ $item->customer_nama }}</div>
                                @if($item->customer_kontak)
                                    <div class="text-[11px] text-gray-400 font-normal">{{ $item->customer_kontak }}</div>
                                @endif
                            </td>

                            <!-- 4. Barang -->
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-gray-900">{{ $item->barang }}</span>
                            </td>

                            <!-- 5. Keluhan -->
                            <td class="py-2.5 px-3">
                                <p class="line-clamp-2 text-gray-600 font-normal text-[11px] leading-relaxed">{{ $item->keluhan }}</p>
                            </td>

                            <!-- 6. Distributor -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($item->distributor_nama)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold text-purple-700 bg-purple-50 border border-purple-200">
                                        {{ $item->distributor_nama }}
                                    </span>
                                @else
                                    <span class="text-gray-400 font-normal">&mdash;</span>
                                @endif
                            </td>

                            <!-- 7. Tanggal Kirim -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                {{ $item->tanggal_kirim ? \Carbon\Carbon::parse($item->tanggal_kirim)->format('d/m/Y') : '-' }}
                            </td>

                            <!-- 8. Tindakan -->
                            <td class="py-2.5 px-3">
                                @if($item->tindakan)
                                    <p class="line-clamp-2 text-gray-600 font-normal text-[11px] leading-relaxed">{{ $item->tindakan }}</p>
                                @else
                                    <span class="text-gray-400 font-normal">&mdash;</span>
                                @endif
                            </td>

                            <!-- 9. Tanggal Barang Kembali -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                {{ $item->tanggal_barang_kembali ? \Carbon\Carbon::parse($item->tanggal_barang_kembali)->format('d/m/Y') : '-' }}
                            </td>

                            <!-- 10. Penerima Barang -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($item->penerima_nama)
                                    <span class="font-semibold text-gray-700">{{ $item->penerima_nama }}</span>
                                @else
                                    <span class="text-gray-400 font-normal">&mdash;</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1">
                                    @can('klaim_garansi.detail')
                                    <button wire:click="openDetailModal({{ $item->id }})"
                                            type="button"
                                            title="Detail Klaim"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                    @can('klaim_garansi.edit')
                                    <button wire:click="openEditModal({{ $item->id }})"
                                            type="button"
                                            title="Edit Klaim"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#5E17EB] hover:bg-purple-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                    @can('klaim_garansi.delete')
                                    <button wire:click="confirmDelete({{ $item->id }}, '{{ $item->no_klaim }}')"
                                            type="button"
                                            title="Hapus Klaim"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 stroke-current mb-2 text-gray-300" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-gray-600">Belum Ada Data Klaim Garansi</p>
                                    <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Input Klaim Garansi" untuk mencatat pengajuan klaim baru</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($klaimList->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $klaimList->links() }}
        </div>
        @endif
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

                    <!-- Barang (Copy string Varchar, no mutation) -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700">
                            Barang <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   wire:model="formBarang"
                                   wire:model.live.debounce.250ms="barangSearch"
                                   class="w-full text-xs font-semibold px-3.5 py-2 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">

                            @if($productSuggestions->isNotEmpty())
                                <div class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-100 max-h-44 overflow-y-auto divide-y divide-gray-50">
                                    @foreach($productSuggestions as $pSug)
                                        <button type="button"
                                                wire:click="selectProduct('{{ addslashes($pSug->nama_produk) }}')"
                                                class="w-full text-left px-3.5 py-2 text-xs hover:bg-purple-50 flex items-center justify-between transition-colors cursor-pointer">
                                            <span class="font-bold text-gray-900">{{ $pSug->nama_produk }}</span>
                                            <span class="text-gray-400 text-[10px]">Pilih</span>
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


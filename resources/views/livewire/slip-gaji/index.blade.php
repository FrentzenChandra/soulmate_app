<div class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOMBOL AKSI UTAMA             -->
    <!-- ============================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-black">
                    Slip Gaji
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB] border border-purple-200' : 'bg-blue-100 text-[#0052FF] border border-blue-200' }}">
                    {{ $isHQ ? 'Konteks HQ (Semua Cabang)' : 'Soulmate Komputer' }}
                </span>
            </div>
            <p class="text-sm font-bold text-black mt-1">
                Penggajian staf operasional, kalkulasi kehadiran otomatis, riwayat draft, dan cetak slip gaji.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('gaji.tarif.edit')
            <button type="button"
                    wire:click="openTarifModal"
                    class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-black font-bold text-xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Atur Tarif</span>
            </button>
            @endcan

            @can('gaji.create')
            <button type="button"
                    wire:click="openSalinSemuaModal"
                    class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-black font-bold text-xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                <span>Salin Semua dari Bulan Lalu</span>
            </button>

            <a href="{{ route('slip-gaji.create') }}"
               class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-black text-xs shadow-md shadow-purple-600/25 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Buat Slip Gaji</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Alert Flash Message -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-black flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-black hover:opacity-75 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- Banner Peringatan Kenaikan Gaji 6 Bulan (Jika Ada Staf Menunggu) -->
    @if ($alertCount > 0)
        <div class="p-4 rounded-2xl bg-pink-50 border border-[#E6007A]/30 text-black flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-[#E6007A] border border-pink-200 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <span class="text-xs font-black block">Terdapat {{ $alertCount }} staf yang telah mencapai milestone 6 bulan masa kerja.</span>
                    <span class="text-[11px] font-bold block">Waktunya meninjau penyesuaian gaji pokok</span>
                </div>
            </div>
            <button type="button"
                    wire:click="$set('modalAlertOpen', true)"
                    class="px-3.5 py-1.5 rounded-lg bg-[#E6007A] text-white text-xs font-black hover:bg-pink-700 transition-all cursor-pointer shrink-0">
                Lihat Daftar Staf
            </button>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- FILTER BAR, PERIODE & PENCARIAN                -->
    <!-- ============================================== -->
    <div class="card-custom bg-white p-5 space-y-4 border border-gray-200 shadow-xs rounded-2xl">
        <!-- Baris 1: Mode Periode Switcher & Selector Tanggal -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-200">
            <div class="flex items-center space-x-1.5 bg-gray-100 p-1 rounded-xl">
                <button type="button"
                        wire:click="setPeriodeMode('semua')"
                        class="px-3 py-1.5 rounded-lg text-xs font-black transition-all {{ $periodeMode === 'semua' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-black hover:bg-gray-200' }}">
                    Semua
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('hari_ini')"
                        class="px-3 py-1.5 rounded-lg text-xs font-black transition-all {{ $periodeMode === 'hari_ini' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-black hover:bg-gray-200' }}">
                    Hari Ini
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('range')"
                        class="px-3 py-1.5 rounded-lg text-xs font-black transition-all {{ $periodeMode === 'range' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-black hover:bg-gray-200' }}">
                    Rentang Tanggal
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('bulanan')"
                        class="px-3 py-1.5 rounded-lg text-xs font-black transition-all {{ $periodeMode === 'bulanan' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-black hover:bg-gray-200' }}">
                    Bulanan
                </button>
                <button type="button"
                        wire:click="setPeriodeMode('tahunan')"
                        class="px-3 py-1.5 rounded-lg text-xs font-black transition-all {{ $periodeMode === 'tahunan' ? 'bg-[#5E17EB] text-white shadow-sm' : 'text-black hover:bg-gray-200' }}">
                    Tahunan
                </button>
            </div>

            <!-- Input Tanggal Dinamis -->
            <div class="flex items-center gap-2 sm:ml-auto">
                @if ($periodeMode === 'range' || $periodeMode === 'hari_ini')
                    <div class="flex items-center space-x-2">
                        <input type="date"
                               wire:model.live="startDate"
                               class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-2 text-black font-mono font-bold focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                        <span class="text-xs font-bold text-black">s/d</span>
                        <input type="date"
                               wire:model.live="endDate"
                               class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-2 text-black font-mono font-bold focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                    </div>
                @elseif ($periodeMode === 'bulanan')
                    <div class="flex items-center space-x-2">
                        <select wire:model.live="filterBulan"
                                class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-2 text-black font-bold focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->locale('id')->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <select wire:model.live="filterTahun"
                                class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-2 text-black font-mono font-bold focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            @for ($y = now()->year + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                @elseif ($periodeMode === 'tahunan')
                    <select wire:model.live="filterTahun"
                            class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-2 text-black font-mono font-bold focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                        @for ($y = now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                @endif
            </div>
        </div>

        <!-- Baris 2: Pencarian & Filter Cabang/Status -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative flex-1 min-w-[240px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-black focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
            </div>

            <!-- Filter Status -->
            <div class="flex items-center space-x-2">
                <label class="text-xs font-black text-black">Status:</label>
                <select wire:model.live="statusFilter"
                        class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-2 text-black font-bold focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                    <option value="semua">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="final">Final</option>
                </select>
            </div>

            <!-- Filter Cabang (Khusus Owner HQ) -->
            @if ($isHQ && count($cabangs) > 0)
            <div class="flex items-center space-x-2">
                <label class="text-xs font-black text-black">Cabang:</label>
                <select wire:model.live="cabangFilter"
                        class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-2 text-black font-bold focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                    <option value="semua">Semua Cabang</option>
                    @foreach ($cabangs as $cb)
                        <option value="{{ $cb->id }}">{{ $cb->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <x-table-size-selector />
        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL DATA SLIP GAJI                           -->
    <!-- ============================================== -->
    <div class="card-custom bg-white border border-gray-200 shadow-xs rounded-2xl overflow-hidden">
        <div wire:loading.block wire:target="setPeriodeMode, startDate, endDate, filterBulan, filterTahun, statusFilter, cabangFilter, search" class="w-full">
            <x-table-skeleton />
        </div>

        <div wire:loading.remove wire:target="setPeriodeMode, startDate, endDate, filterBulan, filterTahun, statusFilter, cabangFilter, search" class="overflow-x-auto">
            <table class="w-full text-left border-collapse" data-table-size="M">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-200 text-[11px] font-black text-black uppercase tracking-wider">
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3">Nama Staf</th>
                        <th class="px-4 py-3">Jabatan</th>
                        <th class="px-4 py-3 text-right">Total Bruto</th>
                        <th class="px-4 py-3 text-right">Pengurangan</th>
                        <th class="px-4 py-3 text-right">Total Diterima</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-xs text-black">
                    @forelse ($slips as $slip)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-bold whitespace-nowrap">
                                {{ $slip->periode_text }}
                            </td>
                            <td class="px-4 py-3 font-black whitespace-nowrap">
                                {{ $slip->nama_staf_snapshot }}
                            </td>
                            <td class="px-4 py-3 font-bold whitespace-nowrap">
                                {{ $slip->bagian_snapshot ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold whitespace-nowrap">
                                Rp {{ number_format($slip->total_bruto, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-rose-600 whitespace-nowrap">
                                {{ $slip->total_pengurangan > 0 ? '- Rp ' . number_format($slip->total_pengurangan, 0, ',', '.') : 'Rp 0' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-black text-emerald-700 whitespace-nowrap">
                                Rp {{ number_format($slip->total_diterima, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if ($slip->isFinal())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 border border-emerald-300 text-black">
                                        Final
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 border border-blue-300 text-black">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1.5">
                                    @if ($slip->isFinal())
                                        <button type="button"
                                                wire:click="showSlipPreview({{ $slip->id }})"
                                                class="p-1.5 rounded-lg border border-gray-200 hover:bg-purple-50 text-[#5E17EB] transition-all cursor-pointer"
                                                title="Lihat Slip Gaji (1:1 Sesuai Desain)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                    @else
                                        @can('gaji.edit')
                                        <a href="{{ route('slip-gaji.edit', $slip->id) }}"
                                           class="p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-black transition-all cursor-pointer"
                                           title="Edit Draft Slip">
                                            <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        @endcan
                                    @endif

                                    @if ($slip->isFinal())
                                        @can('gaji.export_pdf')
                                        <a href="{{ route('slip-gaji.pdf', $slip->id) }}" target="_blank" rel="noopener"
                                           class="p-1.5 rounded-lg border border-gray-200 hover:bg-purple-50 text-[#5E17EB] transition-all"
                                           title="Buka dialog cetak slip gaji">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>
                                        @endcan

                                        @can('gaji.reopen')
                                        <button type="button"
                                                wire:click="confirmReopen({{ $slip->id }}, '{{ $slip->no_slip }}')"
                                                class="p-1.5 rounded-lg border border-gray-200 hover:bg-amber-50 text-black transition-all"
                                                title="Buka Kembali ke Draft (Owner)">
                                            <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                        </button>
                                        @endcan
                                    @else
                                        @can('gaji.delete')
                                        <button type="button"
                                                wire:click="confirmDelete({{ $slip->id }}, '{{ $slip->no_slip }}')"
                                                class="p-1.5 rounded-lg border border-gray-200 hover:bg-rose-50 text-rose-600 transition-all cursor-pointer"
                                                title="Hapus Slip Draft">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-black font-bold">
                                Tidak ada data slip gaji pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($slips->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $slips->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================== -->
    <!-- MODAL ATUR TARIF                               -->
    <!-- ============================================== -->
    @if ($modalTarifOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 border border-gray-200 shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-base font-black text-black">Atur Tarif Default Gaji</h3>
                    <button type="button" wire:click="closeTarifModal" class="text-black hover:opacity-75">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-black text-black mb-1">
                            Uang Makan / Hari <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-gray-500 pointer-events-none select-none z-10">Rp</span>
                            <x-input-ribuan wire:model="tarifUangMakan"
                                            input-class="pl-9 pr-3 py-2 text-left bg-white border border-gray-200 rounded-xl text-xs font-mono font-bold text-black focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black mb-1">
                            Uang Transportasi / Hari <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-gray-500 pointer-events-none select-none z-10">Rp</span>
                            <x-input-ribuan wire:model="tarifUangTransport"
                                            input-class="pl-9 pr-3 py-2 text-left bg-white border border-gray-200 rounded-xl text-xs font-mono font-bold text-black focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black mb-1">
                            Kenaikan Gaji Pokok 6 Bulan <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-gray-500 pointer-events-none select-none z-10">Rp</span>
                            <x-input-ribuan wire:model="tarifKenaikan6Bulan"
                                            input-class="pl-9 pr-3 py-2 text-left bg-white border border-gray-200 rounded-xl text-xs font-mono font-bold text-black focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200">
                    <button type="button"
                            wire:click="closeTarifModal"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-black hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="saveTarif"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-black transition-all cursor-pointer disabled:opacity-50 inline-flex items-center space-x-1.5">
                        <span wire:loading.remove wire:target="saveTarif">Simpan Tarif</span>
                        <span wire:loading wire:target="saveTarif" class="inline-flex items-center space-x-1">
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Menyimpan...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL SALIN SEMUA DARI BULAN LALU              -->
    <!-- ============================================== -->
    @if ($modalSalinSemuaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 border border-gray-200 shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-base font-black text-black">Salin Semua Slip Gaji</h3>
                    <button type="button" wire:click="closeSalinSemuaModal" class="text-black hover:opacity-75">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-3 text-xs text-black">
                    <p class="font-bold">
                        Sistem akan membuat slip gaji (status Draft) untuk seluruh staf aktif cabang pada periode
                        <span class="font-black underline">{{ \Carbon\Carbon::create(null, $salinTargetBulan)->locale('id')->translatedFormat('F') }} {{ $salinTargetTahun }}</span>
                        dengan menyalin komponen Gaji Pokok, Tunjangan, dan Per Hari dari slip Final terakhir tiap staf.
                    </p>

                    @if (($salinSummary['kenaikan_menunggu_count'] ?? 0) > 0)
                        <div class="p-3.5 rounded-xl bg-pink-50 border border-[#E6007A]/40 space-y-1">
                            <span class="font-black text-black block">
                                Perhatian: {{ $salinSummary['kenaikan_menunggu_count'] }} staf memiliki pengingat kenaikan gaji pokok 6 bulan:
                            </span>
                            <span class="font-bold text-black block">
                                {{ implode(', ', $salinSummary['daftar_nama_kenaikan'] ?? []) }}
                            </span>
                            <span class="text-[11px] font-bold text-black block mt-1">
                                Nilai gaji pokok tetap disalin apa adanya dari bulan lalu tanpa kenaikan otomatis. Silakan tinjau dan terapkan kenaikan secara manual di form masing-masing.
                            </span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200">
                    <button type="button"
                            wire:click="closeSalinSemuaModal"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-black hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="executeSalinSemua"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-black transition-all cursor-pointer disabled:opacity-50 inline-flex items-center space-x-1.5">
                        <span wire:loading.remove wire:target="executeSalinSemua">Salin Sekarang</span>
                        <span wire:loading wire:target="executeSalinSemua" class="inline-flex items-center space-x-1">
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Memproses...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL ALERT KENAIKAN GAJI 6 BULAN              -->
    <!-- ============================================== -->
    @if ($modalAlertOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 border border-gray-200 shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-pink-100 text-[#E6007A] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-base font-black text-black">Pengingat Kenaikan Gaji Berkala</h3>
                    </div>
                    <button type="button" wire:click="closeAlertModal" class="text-black hover:opacity-75">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-bold text-black">
                        Daftar staf yang telah memasuki milestone kelipatan 6 bulan masa kerja dan memenuhi syarat penyesuaian gaji pokok:
                    </p>

                    <div class="divide-y divide-gray-200 border border-gray-200 rounded-xl overflow-hidden">
                        @foreach ($daftarAlertKenaikan as $item)
                            <div class="p-3 flex items-center justify-between bg-white text-xs text-black">
                                <div>
                                    <span class="font-black block">{{ $item['nama'] }}</span>
                                    <span class="font-bold text-[11px] block">Masa kerja genap {{ $item['milestone_bulan'] }} bulan.</span>
                                </div>
                                <span class="font-mono font-black text-emerald-700">
                                    + Rp {{ number_format($item['nominal'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end pt-3 border-t border-gray-200">
                    <button type="button"
                            wire:click="closeAlertModal"
                            class="px-5 py-2 rounded-xl bg-gray-900 text-white text-xs font-black hover:bg-black transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI BUKA KEMBALI (REOPEN)         -->
    <!-- ============================================== -->
    @if ($modalReopenOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 border border-gray-200 shadow-xl">
                <div class="flex items-center space-x-3 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h3 class="text-base font-black text-black">Buka Kembali Slip Final?</h3>
                </div>

                <p class="text-xs font-bold text-black">
                    Apakah Anda yakin ingin membuka kembali slip <span class="font-mono font-black">{{ $reopenSlipNo }}</span> ke status Draft? Pembukaan kembali akan dicatat dalam audit trail.
                </p>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-black">
                        Alasan Pembukaan Kembali <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <textarea wire:model="alasanBukaKembali" rows="2"
                              class="w-full bg-white border border-gray-300 rounded-xl p-2.5 text-xs font-bold text-black focus:border-[#5E17EB]"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200">
                    <button type="button"
                            wire:click="closeReopenModal"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-black hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="executeReopen"
                            class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black cursor-pointer">
                        Ya, Buka Kembali
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI HAPUS DRAFT                   -->
    <!-- ============================================== -->
    @if ($modalDeleteOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 border border-gray-200 shadow-xl">
                <div class="flex items-center space-x-3 text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <h3 class="text-base font-black text-black">Hapus Slip Draft?</h3>
                </div>

                <p class="text-xs font-bold text-black">
                    Data slip <span class="font-mono font-black">{{ $deleteSlipNo }}</span> akan dihapus dari sistem. Aksi ini tidak dapat dibatalkan.
                </p>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200">
                    <button type="button"
                            wire:click="closeDeleteModal"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-black hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="executeDelete"
                            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black cursor-pointer">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL PREVIEW SLIP GAJI (1:1 SESUAI DESAIN)    -->
    <!-- ============================================== -->
    @if ($modalPreviewOpen && $previewSlip)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/75 flex items-center justify-center p-3 sm:p-5 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-5xl w-full p-3 sm:p-5 space-y-3 shadow-2xl border border-purple-100">
                <div class="flex items-center justify-between pb-3 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#5E17EB]"></span>
                        <span class="text-xs font-black uppercase tracking-wider text-[#35117A]">Pratinjau Slip Gaji &bull; {{ $previewSlip->no_slip }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        @can('gaji.export_pdf')
                        <a href="{{ route('slip-gaji.pdf', $previewSlip->id) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-[#5E17EB] text-white hover:bg-purple-800 shadow-sm transition-all cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Cetak PDF</span>
                        </a>
                        <a href="{{ route('slip-gaji.cetak', $previewSlip->id) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl text-xs font-bold border border-gray-200 bg-white text-black hover:bg-gray-50 transition-all cursor-pointer"
                           title="Buka pratinjau sebelum mencetak">
                            <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>Pratinjau Cetak</span>
                        </a>
                        @endcan
                        <button type="button" wire:click="closePreviewModal" class="p-1.5 rounded-xl text-black hover:bg-gray-100 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto max-h-[82vh] rounded-xl bg-[#F8F7FC] p-2 sm:p-3">
                    <x-slip-gaji-card :slip="$previewSlip" :hide-empty-deductions="true" />
                </div>
            </div>
        </div>
    @endif

</div>


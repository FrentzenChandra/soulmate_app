<div x-data="{
    tableSize: localStorage.getItem('soulmate_table_size') || 'md',
    init() {
        window.addEventListener('table-size-changed', (e) => {
            this.tableSize = e.detail;
        });
    }
}" class="space-y-6">

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 shadow-2xs shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 tracking-tight">Riwayat Absensi Pegawai</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Daftar rekaman kehadiran harian staf dan rekap keterlambatan bulanan per role.</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('absensi.index') }}"
               class="px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004.07 9.878m11.93 6.966c-.302 1.002-.693 1.96-1.161 2.864m-4.839-2.864c.302 1.002.693 1.96 1.161 2.864" />
                </svg>
                <span>Absen Masuk</span>
            </a>
        </div>
    </div>

    <!-- Tab Switcher Navigation -->
    <div class="flex items-center space-x-2 bg-gray-100 p-1.5 rounded-2xl w-fit">
        <button type="button"
                wire:click="switchTab('log_harian')"
                class="flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $activeTab === 'log_harian' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-4 h-4 {{ $activeTab === 'log_harian' ? 'text-[#5E17EB]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Log Harian</span>
        </button>

        <button type="button"
                wire:click="switchTab('rekap_bulanan')"
                class="flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $activeTab === 'rekap_bulanan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-4 h-4 {{ $activeTab === 'rekap_bulanan' ? 'text-[#5E17EB]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Rekap Keterlambatan</span>
            @if(!$canViewRekapTim)
                <span class="text-[10px] bg-purple-100 text-[#5E17EB] px-1.5 py-0.5 rounded-md font-bold">Pribadi</span>
            @endif
        </button>
    </div>

    @if ($activeTab === 'log_harian')
        <!-- ============================================== -->
        <!-- TAB 1: LOG HARIAN                              -->
        <!-- ============================================== -->

        <!-- Summary Metrics Log Harian -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Absensi Hari Ini</span>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-gray-900">{{ number_format($todayCount, 0, ',', '.') }}</span>
                    <span class="text-xs font-bold text-gray-500">kehadiran</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Data Sesuai Filter</span>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-[#5E17EB]">{{ number_format($filteredCount, 0, ',', '.') }}</span>
                    <span class="text-xs font-bold text-gray-500">catatan</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar Mode Periode Log Harian -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-3.5 sm:space-y-4">

            <!-- Baris 1: Mode Periode Switcher & Input Tanggal Dinamis -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div class="w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0 -mx-1 px-1">
                    <div class="inline-flex items-center space-x-1 sm:space-x-1.5 bg-gray-100 p-1 rounded-xl whitespace-nowrap">
                        <button type="button"
                                wire:click="setPeriodeMode('semua')"
                                class="px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'semua' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                            Semua
                        </button>
                        <button type="button"
                                wire:click="setPeriodeMode('hari_ini')"
                                class="px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'hari_ini' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                            Hari Ini
                        </button>
                        <button type="button"
                                wire:click="setPeriodeMode('range')"
                                class="px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'range' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                            Rentang Tanggal
                        </button>
                        <button type="button"
                                wire:click="setPeriodeMode('bulanan')"
                                class="px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'bulanan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                            Bulanan
                        </button>
                        <button type="button"
                                wire:click="setPeriodeMode('tahunan')"
                                class="px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodeMode === 'tahunan' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800' }}">
                            Tahunan
                        </button>
                    </div>
                </div>

                <!-- Input Tanggal Dinamis -->
                <div class="w-full sm:w-auto flex flex-wrap sm:flex-nowrap items-center gap-2 sm:ml-auto">
                    @if($periodeMode === 'range' || $periodeMode === 'hari_ini')
                        <input type="date"
                               wire:model.live="startDate"
                               class="flex-1 sm:flex-none py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB]">
                        <span class="text-xs font-bold text-gray-400">s/d</span>
                        <input type="date"
                               wire:model.live="endDate"
                               class="flex-1 sm:flex-none py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB]">
                    @elseif($periodeMode === 'bulanan')
                        <select wire:model.live="filterBulan"
                                class="flex-1 sm:flex-none py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <select wire:model.live="filterTahun"
                                class="flex-1 sm:flex-none py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                            @for($y = (int) date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($periodeMode === 'tahunan')
                        <select wire:model.live="filterTahun"
                                class="w-full sm:w-auto py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                            @for($y = (int) date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    @endif
                </div>
            </div>

            <!-- Baris 2: Pencarian, Cabang & Table Size Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-col sm:flex-row flex-1 sm:items-center gap-2 sm:gap-3">
                    <!-- Search multi-kata -->
                    <div class="relative w-full sm:max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 text-xs font-semibold text-gray-800 focus:bg-white focus:border-[#5E17EB] transition-all">
                    </div>

                    <!-- Filter Cabang (KHUSUS OWNER) -->
                    @if ($isOwner)
                        <div class="w-full sm:w-48">
                            <select wire:model.live="cabangFilter"
                                    class="w-full py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                                <option value="semua">Semua Cabang</option>
                                @foreach ($cabangs as $cb)
                                    <option value="{{ $cb->id }}">{{ $cb->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Table Size Selector -->
                <div class="flex items-center justify-end space-x-2 shrink-0">
                    <x-table-size-selector />
                </div>
            </div>

        </div>

        <!-- Tabel Log Harian -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse"
                       :class="{
                           'text-[11px]': tableSize === 'sm',
                           'text-xs': tableSize === 'md',
                           'text-[13px]': tableSize === 'lg'
                       }">
                    <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-600 uppercase font-black tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-black">Tanggal</th>
                            <th class="py-3.5 px-4 font-black">Hari</th>
                            <th class="py-3.5 px-4 font-black">Nama Staf</th>
                            <th class="py-3.5 px-4 font-black text-center">Shift</th>
                            <th class="py-3.5 px-4 font-black">Jam Absen</th>
                            <th class="py-3.5 px-4 font-black text-center">Status</th>
                            @if ($isOwner)
                                <th class="py-3.5 px-4 font-black">Cabang</th>
                            @endif
                        </tr>
                    </thead>

                    <!-- Skeleton Loader saat Query -->
                    <x-table-skeleton :cols="$isOwner ? 7 : 6" :rows="5" target="search, periodeMode, startDate, endDate, filterBulan, filterTahun, cabangFilter, setPeriodeMode" />

                    <tbody wire:loading.remove wire:target="search, periodeMode, startDate, endDate, filterBulan, filterTahun, cabangFilter, setPeriodeMode"
                           class="divide-y divide-gray-100 bg-white">
                        @forelse ($absensis as $item)
                            <tr class="hover:bg-purple-50/20 transition-colors">
                                <!-- Tanggal -->
                                <td class="py-3 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                    {{ $item->tanggal->translatedFormat('d M Y') }}
                                </td>

                                <!-- Hari -->
                                <td class="py-3 px-4 font-bold text-gray-600 whitespace-nowrap">
                                    {{ $item->tanggal->translatedFormat('l') }}
                                </td>

                                <!-- Nama Staf -->
                                <td class="py-3 px-4 font-bold text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center font-black text-xs shrink-0">
                                            {{ strtoupper(substr($item->user?->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span>{{ $item->user?->name ?? 'Staf Terhapus' }}</span>
                                            @if($item->user?->trashed())
                                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-500 font-semibold ml-1">Nonaktif</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Shift -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if(($item->shift->value ?? $item->shift) === 'sore')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                            Shift Sore
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-[#0052FF] border border-blue-200">
                                            Shift Pagi
                                        </span>
                                    @endif
                                </td>

                                <!-- Jam Absen -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-gray-800">
                                        {{ $item->waktu_absen->format('H:i') }} WIB
                                    </span>
                                </td>

                                <!-- Status Kehadiran -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if(($item->status_kehadiran->value ?? $item->status_kehadiran) === 'terlambat')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                            Terlambat (+{{ $item->menit_keterlambatan }}m)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Tepat Waktu
                                        </span>
                                    @endif
                                </td>

                                <!-- Cabang (Khusus Owner) -->
                                @if ($isOwner)
                                    <td class="py-3 px-4 text-xs font-semibold text-gray-600 whitespace-nowrap">
                                        {{ $item->cabang?->nama_cabang ?? '-' }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isOwner ? 7 : 6 }}" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-gray-700">Tidak ada data absensi</p>
                                        <p class="text-xs text-gray-400 max-w-sm">
                                            Belum ada catatan kehadiran yang sesuai dengan rentang tanggal dan pencarian saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Infinite Loading -->
            @if ($absensis->hasMorePages())
                <div wire:intersect="loadMore" class="p-6 flex flex-col items-center justify-center border-t border-gray-100 bg-gray-50/40">
                    <div wire:loading wire:target="loadMore" class="flex flex-col items-center justify-center space-y-2 py-2">
                        <svg class="animate-spin w-5 h-5 text-[#5E17EB]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span class="text-xs font-bold text-gray-500 animate-pulse">Memuat riwayat kehadiran berikutnya...</span>
                    </div>
                    <div wire:loading.remove wire:target="loadMore" class="text-xs font-semibold text-gray-400">
                        Gulir ke bawah untuk memuat lebih banyak
                    </div>
                </div>
            @elseif ($absensis->count() > 0 && !$absensis->hasMorePages())
                <div class="py-4 text-center border-t border-gray-100 bg-gray-50/40">
                    <span class="text-xs font-semibold text-gray-400">Semua data riwayat absensi telah ditampilkan</span>
                </div>
            @endif
        </div>

    @else
        <!-- ============================================== -->
        <!-- TAB 2: REKAP KETERLAMBATAN BULANAN             -->
        <!-- ============================================== -->

        <!-- Filter Rekap Bulan, Tahun & Cabang -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-bold text-gray-600 whitespace-nowrap">Bulan:</label>
                    <select wire:model.live="rekapBulan"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <label class="text-xs font-bold text-gray-600 whitespace-nowrap">Tahun:</label>
                    <select wire:model.live="rekapTahun"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                        @for($y = (int) date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                @if($isOwner)
                    <div class="flex items-center space-x-2">
                        <label class="text-xs font-bold text-gray-600 whitespace-nowrap">Cabang:</label>
                        <select wire:model.live="cabangFilter"
                                class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                            <option value="semua">Semua Cabang</option>
                            @foreach ($cabangs as $cb)
                                <option value="{{ $cb->id }}">{{ $cb->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="flex items-center space-x-2">
                <x-table-size-selector />
            </div>
        </div>

        <!-- Metrics Cards Rekap Bulanan -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Pegawai</span>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-gray-900">{{ $rekapTotalStaf }}</span>
                    <span class="text-xs font-bold text-gray-500">orang</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Kehadiran</span>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-[#5E17EB]">{{ $rekapTotalHadir }}</span>
                    <span class="text-xs font-bold text-gray-500">kali</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Tepat Waktu</span>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-emerald-600">{{ $rekapTotalTepatWaktu }}</span>
                    <span class="text-xs font-bold text-gray-500">kali</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Terlambat</span>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-rose-600">{{ $rekapTotalTerlambat }}</span>
                    <span class="text-xs font-bold text-gray-500">kali</span>
                </div>
            </div>
        </div>

        <!-- Section Per Role (Urutan: Operasional -> Bendahara -> Teknisi -> Admin -> Magang) -->
        <div class="space-y-6">
            @forelse($roleGroups as $roleName => $staffList)
                @if($staffList->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                        <!-- Role Section Header -->
                        <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-200/80 flex items-center justify-between">
                            <div class="flex items-center space-x-2.5">
                                @php
                                    $badgeStyle = match($roleName) {
                                        'Operasional' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'Bendahara' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Teknisi' => 'bg-blue-50 text-[#0052FF] border-blue-200',
                                        'Admin' => 'bg-purple-50 text-[#5E17EB] border-purple-200',
                                        'Magang' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-extrabold border {{ $badgeStyle }}">
                                    Divisi {{ $roleName }}
                                </span>
                                <span class="text-xs font-bold text-gray-400">&bull;</span>
                                <span class="text-xs font-bold text-gray-500">{{ $staffList->count() }} Pegawai</span>
                            </div>
                        </div>

                        <!-- Table Staf per Role -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse"
                                   :class="{
                                       'text-[11px]': tableSize === 'sm',
                                       'text-xs': tableSize === 'md',
                                       'text-[13px]': tableSize === 'lg'
                                   }">
                                <thead class="bg-white border-b border-gray-100 text-gray-400 uppercase font-black tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-3 px-5">Nama Pegawai</th>
                                        <th class="py-3 px-4 text-center">Total Hadir</th>
                                        <th class="py-3 px-4 text-center">Tepat Waktu</th>
                                        <th class="py-3 px-4 text-center">Keterlambatan</th>
                                        <th class="py-3 px-4 text-center">Akumulasi Terlambat</th>
                                        @if($isOwner)
                                            <th class="py-3 px-5 text-right">Cabang</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($staffList as $staf)
                                        <tr class="hover:bg-purple-50/20 transition-colors">
                                            <!-- Nama Pegawai -->
                                            <td class="py-3.5 px-5 font-bold text-gray-900">
                                                <div class="flex items-center space-x-2.5">
                                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center font-black text-xs shrink-0">
                                                        {{ strtoupper(substr($staf->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="block leading-tight">{{ $staf->name }}</span>
                                                        @if($staf->trashed())
                                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-500 font-semibold">Nonaktif</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Total Hadir -->
                                            <td class="py-3.5 px-4 text-center font-mono font-bold text-gray-800">
                                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700">
                                                    {{ $staf->total_hadir }} Hari
                                                </span>
                                            </td>

                                            <!-- Tepat Waktu -->
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    {{ $staf->total_tepat_waktu }} Kali
                                                </span>
                                            </td>

                                            <!-- Keterlambatan (Clickable if > 0) -->
                                            <td class="py-3.5 px-4 text-center">
                                                @if($staf->total_terlambat > 0)
                                                    <button type="button"
                                                            wire:click="openDetailLate({{ $staf->id }})"
                                                            class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-lg text-xs font-extrabold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors cursor-pointer"
                                                            title="Klik untuk melihat detail tanggal keterlambatan">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        <span>{{ $staf->total_terlambat }} Kali</span>
                                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-500">
                                                        0 Kali
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Akumulasi Terlambat (Menit) -->
                                            <td class="py-3.5 px-4 text-center font-mono font-bold">
                                                @if($staf->total_menit_terlambat > 0)
                                                    <span class="text-rose-600 font-extrabold">
                                                        {{ $staf->total_menit_terlambat }} Menit
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 font-normal">
                                                        0 Menit
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Cabang (Owner Only) -->
                                            @if($isOwner)
                                                <td class="py-3.5 px-5 text-right font-medium text-gray-600">
                                                    {{ $staf->cabang?->nama_cabang ?? '-' }}
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @empty
                <div class="bg-white p-12 rounded-2xl border border-gray-200/80 text-center text-gray-400 shadow-xs">
                    <p class="font-bold text-sm text-gray-700">Tidak ada data staf untuk rekap.</p>
                </div>
            @endforelse
        </div>

    @endif

    <!-- ============================================== -->
    <!-- MODAL DETAIL KETERLAMBATAN STAF                -->
    <!-- ============================================== -->
    @if($modalDetailLateOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-late-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" wire:click="closeDetailLate"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-xs">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug" id="modal-late-title">
                                    Rincian Keterlambatan: {{ $selectedUserName }}
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">
                                    Periode {{ Carbon\Carbon::create(null, $rekapBulan, 1)->translatedFormat('F') }} {{ $rekapTahun }}
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeDetailLate" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="p-6">
                        <div class="overflow-x-auto rounded-2xl border border-gray-200/80">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase font-black text-[10px] tracking-wider">
                                    <tr>
                                        <th class="py-3 px-4">Tanggal</th>
                                        <th class="py-3 px-3 text-center">Shift</th>
                                        <th class="py-3 px-3 text-center">Target Masuk</th>
                                        <th class="py-3 px-3 text-center">Waktu Absen</th>
                                        <th class="py-3 px-4 text-right">Terlambat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($lateDetails as $late)
                                        <tr class="hover:bg-rose-50/30 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                                <div>{{ $late->tanggal->translatedFormat('d M Y') }}</div>
                                                <div class="text-[10px] text-gray-400 font-normal">{{ $late->tanggal->translatedFormat('l') }}</div>
                                            </td>
                                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                                @if(($late->shift->value ?? $late->shift) === 'sore')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                                        Shift Sore
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#0052FF] border border-blue-200">
                                                        Shift Pagi
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-center font-mono font-semibold text-gray-600 whitespace-nowrap">
                                                {{ $late->snapshot_jam_masuk ? substr((string) $late->snapshot_jam_masuk, 0, 5) : '-' }}
                                            </td>
                                            <td class="py-3 px-3 text-center font-mono font-bold text-gray-800 whitespace-nowrap">
                                                {{ $late->waktu_absen->format('H:i') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-extrabold text-rose-600 whitespace-nowrap">
                                                +{{ $late->menit_keterlambatan }} Menit
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-gray-400 font-medium">
                                                Tidak ada catatan keterlambatan untuk staf ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-gray-50 px-6 py-4 rounded-b-3xl border-t border-gray-100 flex items-center justify-end">
                        <button type="button"
                                wire:click="closeDetailLate"
                                class="px-5 py-2 text-xs font-bold text-gray-700 hover:text-gray-900 rounded-xl hover:bg-gray-200/60 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

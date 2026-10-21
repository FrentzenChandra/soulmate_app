<div x-data="{
    tableSize: localStorage.getItem('soulmate_table_size') || 'md',
    init() {
        window.addEventListener('table-size-changed', (e) => {
            this.tableSize = e.detail;
        });
    }
}" class="space-y-6">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 shadow-2xs shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 tracking-tight">Riwayat Absensi</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Daftar rekaman kehadiran staf dan riwayat jam masuk kerja.</p>
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

    <!-- Summary Metrics -->
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
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                    <select wire:model.live="filterTahun"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                        @for($y = (int) date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                @elseif($periodeMode === 'tahunan')
                    <select wire:model.live="filterTahun"
                            class="py-1.5 px-3 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-700 focus:bg-white focus:border-[#5E17EB] cursor-pointer">
                        @for($y = (int) date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                @endif
            </div>
        </div>

        <!-- Baris 2: Pencarian, Cabang (Owner Only) & Table Size Selector -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

            <div class="flex flex-1 items-center gap-3">
                <!-- Search multi-kata -->
                <div class="relative flex-1 max-w-sm">
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
                    <div class="w-48">
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
            <div class="flex items-center space-x-2 shrink-0">
                <x-table-size-selector />
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- TABEL DATA RIWAYAT                             -->
    <!-- ============================================== -->
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
                        <th class="py-3.5 px-4 font-black">Nama</th>
                        <th class="py-3.5 px-4 font-black">Jam Absen</th>
                        @if ($isOwner)
                            <th class="py-3.5 px-4 font-black">Cabang</th>
                        @endif
                    </tr>
                </thead>

                <!-- Skeleton Loader saat Query -->
                <x-table-skeleton :cols="$isOwner ? 5 : 4" :rows="5" target="search, periodeMode, startDate, endDate, filterBulan, filterTahun, cabangFilter, setPeriodeMode" />

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

                            <!-- Jam Absen -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ $item->waktu_absen->format('H:i') }} WIB</span>
                                </span>
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
                            <td colspan="{{ $isOwner ? 5 : 4 }}" class="py-12 px-4 text-center">
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

        <!-- Pagination -->
        @if ($absensis->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $absensis->links() }}
            </div>
        @endif
    </div>

</div>


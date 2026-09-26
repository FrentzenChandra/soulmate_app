<div class="space-y-6">

    <!-- ============================================== -->
    <!-- HEADER HALAMAN & TOOLBAR KONTROL (1 BARIS RAPI) -->
    <!-- ============================================== -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-xs flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <!-- Title & Subtitle -->
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl {{ $isHQ ? 'bg-purple-50 text-[#5E17EB] border border-purple-100' : 'bg-blue-50 text-[#0052FF] border border-blue-100' }} flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div>
                <div class="flex items-center space-x-2.5">
                    <h1 class="text-lg sm:text-xl font-black tracking-tight text-gray-900">
                        Target Bulanan
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $isHQ ? 'bg-purple-100 text-[#5E17EB]' : 'bg-blue-100 text-[#0052FF]' }}">
                        {{ $isHQ ? 'HQ Matrix' : 'Cabang Matrix' }}
                    </span>
                </div>
                <p class="text-xs font-bold text-gray-700 mt-0.5 flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="italic font-extrabold text-gray-900">&ldquo;Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!&rdquo;</span>
                    {{-- <span class="text-gray-400 font-medium">(&bull; Realisasi tahun {{ $filterTahun }})</span> --}}
                </p>
            </div>
        </div>

        <!-- Filter & Tombol Aksi (1 Baris) -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <!-- Filter Tahun -->
            <div class="flex items-center bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 shadow-2xs">
                <span class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider mr-1.5">Tahun:</span>
                <select wire:model.live="filterTahun"
                        class="bg-transparent text-xs font-black text-gray-900 border-none focus:ring-0 cursor-pointer py-0.5 pr-6 pl-0">
                    @for($y = (int)now()->format('Y') + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Filter Cabang (HQ Saja) -->
            @if($isHQ)
                <div class="w-44">
                    <select wire:model.live="cabangFilter"
                            class="w-full py-1.5 px-3 rounded-xl border border-purple-200 bg-purple-50/40 text-xs font-bold text-[#5E17EB] focus:bg-white focus:ring-2 focus:ring-purple-100 transition-all cursor-pointer">
                        <option value="semua">Semua Cabang (HQ)</option>
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Tombol Kelola Target (Owner & Superadmin Only) -->
            @if($canManageTarget)
                <div class="flex items-center gap-2 border-l border-gray-200 pl-2 sm:pl-3">
                    <button type="button"
                            wire:click="openModalDivisi"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-[#5E17EB] font-black text-xs border border-purple-200 transition-all cursor-pointer disabled:opacity-50">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Set Target Divisi</span>
                    </button>

                    <button type="button"
                            wire:click="openModalUser"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-black text-xs shadow-sm shadow-purple-600/30 transition-all cursor-pointer disabled:opacity-50">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Set Target Staf</span>
                    </button>
                </div>
            @endif
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
    <!-- TABEL 1: TARGET PER DIVISI                      -->
    <!-- ============================================== -->
    <div class="card-custom bg-white overflow-hidden border border-gray-200 shadow-sm rounded-2xl">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-900 to-indigo-950 text-white rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                    <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-white">Target Per Divisi</h2>
                    <p class="text-[11px] text-purple-200 font-medium">Realisasi unit divisi per bulan tahun {{ $filterTahun }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-center border-collapse table-fixed min-w-[1150px]">
                <colgroup>
                    <col style="width: 140px;">
                    <col style="width: 90px;">
                    @for($m = 1; $m <= 12; $m++)
                        <col style="width: 72px;">
                    @endfor
                    <col style="width: 90px;">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/90 text-[10px] font-black text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-3 text-left">Divisi</th>
                        <th class="py-3 px-2 text-center bg-gray-100/70">Target</th>
                        <th class="py-3 px-1 text-center">Jan</th>
                        <th class="py-3 px-1 text-center">Feb</th>
                        <th class="py-3 px-1 text-center">Mar</th>
                        <th class="py-3 px-1 text-center">Apr</th>
                        <th class="py-3 px-1 text-center">Mei</th>
                        <th class="py-3 px-1 text-center">Jun</th>
                        <th class="py-3 px-1 text-center">Jul</th>
                        <th class="py-3 px-1 text-center">Ags</th>
                        <th class="py-3 px-1 text-center">Sep</th>
                        <th class="py-3 px-1 text-center">Okt</th>
                        <th class="py-3 px-1 text-center">Nov</th>
                        <th class="py-3 px-1 text-center">Des</th>
                        <th class="py-3 px-2 text-center bg-purple-50 text-[#5E17EB]">Total YTD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @foreach($divisiMatrix as $key => $d)
                        <tr class="hover:bg-purple-50/20 transition-colors">
                            <!-- Nama Divisi -->
                            <td class="py-3.5 px-3 text-left">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $key === 'laptop' ? 'bg-blue-100 text-blue-700' : ($key === 'printer' ? 'bg-amber-100 text-amber-700' : ($key === 'komputer' ? 'bg-purple-100 text-[#5E17EB]' : 'bg-emerald-100 text-emerald-700')) }}">
                                        @if($key === 'laptop')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        @elseif($key === 'printer')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        @elseif($key === 'komputer')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        @endif
                                    </div>
                                    <span class="font-black text-gray-900 tracking-tight">{{ $d['name'] }}</span>
                                </div>
                            </td>

                            <!-- Target Bulanan -->
                            <td class="py-3.5 px-2 bg-gray-50/70 font-mono font-black text-sm text-gray-900">
                                {{ number_format($d['target_bulanan'], 0, ',', '.') }}
                            </td>

                            <!-- 12 Bulan -->
                            @for($m = 1; $m <= 12; $m++)
                                @php $cell = $d['months'][$m]; @endphp
                                <td class="py-2.5 px-1 align-middle">
                                    @if($cell['is_future'])
                                        <span class="text-gray-300 font-mono font-bold text-xs">-</span>
                                    @else
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="font-mono font-black text-gray-900 text-xs leading-tight">
                                                {{ $cell['count'] }}
                                            </span>
                                            <div class="mt-1">
                                                @if($cell['status'] === 'over')
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-[#0052FF] text-white shadow-2xs" title="Over Target (>150%)">
                                                        <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                        <span>{{ $cell['percent'] }}%</span>
                                                    </span>
                                                @elseif($cell['status'] === 'tercapai')
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-600 text-white shadow-2xs" title="Target Tercapai (100-149%)">
                                                        <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                        <span>{{ $cell['percent'] }}%</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-rose-600 text-white shadow-2xs" title="Belum Tercapai (<100%)">
                                                        <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        <span>{{ $cell['percent'] }}%</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            @endfor

                            <!-- Total YTD -->
                            <td class="py-3.5 px-2 bg-purple-50/50 text-center">
                                <span class="font-mono font-black text-sm text-[#5E17EB] block leading-tight">
                                    {{ number_format($d['total_ytd'], 0, ',', '.') }}
                                </span>
                                <div class="mt-1">
                                    @if($d['ytd_percent'] >= 150)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-[#0052FF] text-white">
                                            <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                            <span>{{ $d['ytd_percent'] }}%</span>
                                        </span>
                                    @elseif($d['ytd_percent'] >= 100)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-600 text-white">
                                            <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span>{{ $d['ytd_percent'] }}%</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-gray-200 text-gray-700">
                                            <span>{{ $d['ytd_percent'] }}%</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- TABEL 2: TARGET PER INDIVIDU (STAF)             -->
    <!-- ============================================== -->
    <div class="card-custom bg-white overflow-hidden border border-gray-200 shadow-sm rounded-2xl">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-900 to-indigo-950 text-white rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                    <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-white">Target Per Individu</h2>
                    <p class="text-[11px] text-purple-200 font-medium">Realisasi transaksi per staf/pegawai tahun {{ $filterTahun }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-center border-collapse table-fixed min-w-[1150px]">
                <colgroup>
                    <col style="width: 140px;">
                    <col style="width: 90px;">
                    @for($m = 1; $m <= 12; $m++)
                        <col style="width: 72px;">
                    @endfor
                    <col style="width: 90px;">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/90 text-[10px] font-black text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-3 text-left">Pegawai</th>
                        <th class="py-3 px-2 text-center bg-gray-100/70">Target</th>
                        <th class="py-3 px-1 text-center">Jan</th>
                        <th class="py-3 px-1 text-center">Feb</th>
                        <th class="py-3 px-1 text-center">Mar</th>
                        <th class="py-3 px-1 text-center">Apr</th>
                        <th class="py-3 px-1 text-center">Mei</th>
                        <th class="py-3 px-1 text-center">Jun</th>
                        <th class="py-3 px-1 text-center">Jul</th>
                        <th class="py-3 px-1 text-center">Ags</th>
                        <th class="py-3 px-1 text-center">Sep</th>
                        <th class="py-3 px-1 text-center">Okt</th>
                        <th class="py-3 px-1 text-center">Nov</th>
                        <th class="py-3 px-1 text-center">Des</th>
                        <th class="py-3 px-2 text-center bg-purple-50 text-[#5E17EB]">Total YTD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($userMatrix as $u)
                        <tr class="hover:bg-purple-50/20 transition-colors">
                            <!-- Nama Pegawai -->
                            <td class="py-3.5 px-3 text-left">
                                <span class="font-bold text-gray-900 block truncate" title="{{ $u['user']->name }}">
                                    {{ $u['user']->name }}
                                </span>
                                <span class="text-[10px] text-gray-500 font-medium block truncate">
                                    {{ $u['user']->roles->first()?->name ?? 'Staf' }}
                                </span>
                            </td>

                            <!-- Target Bulanan -->
                            <td class="py-3.5 px-2 bg-gray-50/70 font-mono font-black text-sm text-gray-900">
                                {{ number_format($u['target_bulanan'], 0, ',', '.') }}
                            </td>

                            <!-- 12 Bulan -->
                            @for($m = 1; $m <= 12; $m++)
                                @php $cell = $u['months'][$m]; @endphp
                                <td class="py-2.5 px-1 align-middle">
                                    @if($cell['is_future'])
                                        <span class="text-gray-300 font-mono font-bold text-xs">-</span>
                                    @else
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="font-mono font-black text-gray-900 text-xs leading-tight">
                                                {{ $cell['count'] }}
                                            </span>
                                            <div class="mt-1">
                                                @if($cell['status'] === 'over')
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-[#0052FF] text-white shadow-2xs" title="Over Target (>150%)">
                                                        <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                        <span>{{ $cell['percent'] }}%</span>
                                                    </span>
                                                @elseif($cell['status'] === 'tercapai')
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-600 text-white shadow-2xs" title="Target Tercapai (100-149%)">
                                                        <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                        <span>{{ $cell['percent'] }}%</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-rose-600 text-white shadow-2xs" title="Belum Tercapai (<100%)">
                                                        <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        <span>{{ $cell['percent'] }}%</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            @endfor

                            <!-- Total YTD -->
                            <td class="py-3.5 px-2 bg-purple-50/50 text-center">
                                <span class="font-mono font-black text-sm text-[#5E17EB] block leading-tight">
                                    {{ number_format($u['total_ytd'], 0, ',', '.') }}
                                </span>
                                <div class="mt-1">
                                    @if($u['ytd_percent'] >= 150)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-[#0052FF] text-white">
                                            <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                            <span>{{ $u['ytd_percent'] }}%</span>
                                        </span>
                                    @elseif($u['ytd_percent'] >= 100)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-600 text-white">
                                            <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span>{{ $u['ytd_percent'] }}%</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-gray-200 text-gray-700">
                                            <span>{{ $u['ytd_percent'] }}%</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="py-8 text-center text-gray-400 font-medium">
                                Belum ada data staf pada cabang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- LEGENDA STATUS PENCAPAIAN                       -->
    <!-- ============================================== -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-black text-gray-900 uppercase tracking-wider text-[11px]">Keterangan Status Target:</span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2 bg-blue-50/70 border border-blue-100 px-2.5 py-1.5 rounded-xl">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-black bg-[#0052FF] text-white shadow-2xs">
                    <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    <span>Over Target</span>
                </span>
                <span class="text-gray-700 font-bold text-[11px]">&ge; 150%</span>
            </div>
            <div class="flex items-center space-x-2 bg-emerald-50/70 border border-emerald-100 px-2.5 py-1.5 rounded-xl">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-600 text-white shadow-2xs">
                    <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    <span>Target Tercapai</span>
                </span>
                <span class="text-gray-700 font-bold text-[11px]">100% &ndash; 149%</span>
            </div>
            <div class="flex items-center space-x-2 bg-rose-50/70 border border-rose-100 px-2.5 py-1.5 rounded-xl">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-black bg-rose-600 text-white shadow-2xs">
                    <svg class="w-2.5 h-2.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>Belum Tercapai</span>
                </span>
                <span class="text-gray-700 font-bold text-[11px]">&lt; 100%</span>
            </div>
            <div class="flex items-center space-x-2 bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-xl">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black bg-gray-200 text-gray-700">
                    <span>-</span>
                </span>
                <span class="text-gray-500 font-bold text-[11px]">Bulan Mendatang</span>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: SET TARGET DIVISI (TAHUNAN)           -->
    <!-- ============================================== -->
    @if($modalDivisiOpen)
        <div wire:key="modal-target-divisi" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="$set('modalDivisiOpen', false)"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 text-xs">

                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 text-white flex items-center justify-between rounded-t-3xl gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white tracking-tight">Set Target Divisi</h3>
                                <p class="text-xs text-purple-200 font-medium">Input target bulanan per divisi untuk tahun {{ $formTahunDivisi }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('modalDivisiOpen', false)" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanTargetDivisi">
                        <div class="p-6 space-y-4">
                            <!-- Input Tahun -->
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Tahun Target <span class="text-rose-500 font-bold">*</span></label>
                                <select wire:model.live="formTahunDivisi"
                                        class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-black text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] cursor-pointer">
                                    @for($y = (int)now()->format('Y') + 1; $y >= 2024; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                                <!-- Laptop -->
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Target Laptop (Unit/Bulan) <span class="text-rose-500 font-bold">*</span></label>
                                    <input type="number"
                                           min="0"
                                           wire:model="targetDivisiForm.laptop"
                                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-mono font-bold text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                </div>

                                <!-- Printer -->
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Target Printer (Unit/Bulan) <span class="text-rose-500 font-bold">*</span></label>
                                    <input type="number"
                                           min="0"
                                           wire:model="targetDivisiForm.printer"
                                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-mono font-bold text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                </div>

                                <!-- Komputer -->
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Target Komputer (Unit/Bulan) <span class="text-rose-500 font-bold">*</span></label>
                                    <input type="number"
                                           min="0"
                                           wire:model="targetDivisiForm.komputer"
                                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-mono font-bold text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                </div>

                                <!-- Admin -->
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Target Admin (Unit/Bulan) <span class="text-rose-500 font-bold">*</span></label>
                                    <input type="number"
                                           min="0"
                                           wire:model="targetDivisiForm.admin"
                                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-mono font-bold text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="$set('modalDivisiOpen', false)"
                                    class="px-4 py-2 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-black shadow-md shadow-purple-900/20 transition-all cursor-pointer flex items-center space-x-1.5 disabled:opacity-50">
                                <svg wire:loading wire:target="simpanTargetDivisi" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="simpanTargetDivisi">Simpan Target</span>
                                <span wire:loading wire:target="simpanTargetDivisi">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 2: SET TARGET INDIVIDU (STAF)            -->
    <!-- ============================================== -->
    @if($modalUserOpen)
        <div wire:key="modal-target-user" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="$set('modalUserOpen', false)"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 text-xs">

                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 text-white flex items-center justify-between rounded-t-3xl gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white tracking-tight">Set Target Staf Pegawai</h3>
                                <p class="text-xs text-purple-200 font-medium">Input target bulanan per individu untuk tahun {{ $formTahunUser }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('modalUserOpen', false)" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="simpanTargetUser">
                        <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                            <!-- Input Tahun -->
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Tahun Target <span class="text-rose-500 font-bold">*</span></label>
                                <select wire:model.live="formTahunUser"
                                        class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-black text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB] cursor-pointer">
                                    @for($y = (int)now()->format('Y') + 1; $y >= 2024; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="space-y-2.5 pt-2 border-t border-gray-100">
                                @forelse($targetUserForm as $idx => $row)
                                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="font-bold text-gray-900 block truncate">{{ $row['name'] }}</span>
                                            <span class="text-[10px] text-gray-500 font-medium block truncate">{{ $row['role'] }}</span>
                                        </div>
                                        <div class="w-32 shrink-0">
                                            <div class="relative">
                                                <input type="number"
                                                       min="0"
                                                       wire:model="targetUserForm.{{ $idx }}.target"
                                                       class="w-full px-3 py-1.5 rounded-lg border border-gray-300 font-mono font-bold text-right text-gray-900 focus:ring-2 focus:ring-purple-200 focus:border-[#5E17EB]">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-400 font-medium">
                                        Tidak ada data pegawai aktif.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="$set('modalUserOpen', false)"
                                    class="px-4 py-2 rounded-xl bg-white border border-gray-200 font-bold text-gray-700 hover:bg-gray-100 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-black shadow-md shadow-purple-900/20 transition-all cursor-pointer flex items-center space-x-1.5 disabled:opacity-50">
                                <svg wire:loading wire:target="simpanTargetUser" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="simpanTargetUser">Simpan Target Staf</span>
                                <span wire:loading wire:target="simpanTargetUser">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

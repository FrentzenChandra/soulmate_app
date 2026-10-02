<div class="p-4 sm:p-6 space-y-6" x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }" @table-size-changed.window="tableSize = $event.detail">

    <!-- Flash Message Notification -->
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-[#1A1D20] tracking-tight">Saldo Soulmate Komputer</h1>
            <p class="text-xs text-gray-500 font-medium mt-0.5">Pantau dan kelola saldo rekening dengan aman dan terkontrol</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <div class="px-3.5 py-1.5 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center space-x-2 text-xs font-semibold text-gray-700">
                <svg class="w-4 h-4 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
            <button type="button"
                    wire:click="openCreateSumber"
                    wire:loading.attr="disabled"
                    wire:target="openCreateSumber"
                    class="px-3.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 disabled:opacity-50 text-[#5E17EB] border border-purple-200 text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer">
                <svg wire:loading.remove wire:target="openCreateSumber" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <svg wire:loading wire:target="openCreateSumber" class="animate-spin w-3.5 h-3.5 text-[#5E17EB]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Tambah Sumber Saldo</span>
            </button>
        </div>
    </div>

    <!-- Top Metric Cards (Mockup Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- 1. Total Saldo Keseluruhan -->
        <div class="bg-white rounded-2xl p-5 border border-purple-100 shadow-sm flex items-center space-x-4">
            <div class="w-13 h-13 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 flex-shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Total Saldo Keseluruhan</span>
                <h2 class="text-xl sm:text-2xl font-extrabold text-[#5E17EB] tracking-tight mt-0.5">
                    Rp {{ number_format($totalSaldoKeseluruhan, 0, ',', '.') }}
                </h2>
                <span class="text-[11px] font-semibold text-emerald-600 block mt-0.5">{{ $totalRekeningKeseluruhan }} rekening aktif</span>
            </div>
        </div>

        <!-- Dynamic Sumber Saldo Top Cards -->
        @foreach($sumberSaldos as $sumber)
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center space-x-4" wire:key="metric-sumber-{{ $sumber->id }}">
                <div class="w-13 h-13 rounded-2xl {{ $loop->first ? 'bg-blue-50 text-[#0052FF] border border-blue-100' : 'bg-pink-50 text-[#E6007A] border border-pink-100' }} flex items-center justify-center flex-shrink-0">
                    @if($loop->first)
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    @else
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    @endif
                </div>
                <div class="truncate">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block truncate">Saldo {{ $sumber->nama }}</span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight mt-0.5">
                        Rp {{ number_format($sumber->total_saldo ?? 0, 0, ',', '.') }}
                    </h2>
                    <span class="text-[11px] font-semibold text-gray-400 block mt-0.5">{{ $sumber->rekenings_count }} rekening</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Section-Based Tables (Grouped per Sumber Saldo) -->
    <div class="space-y-6">
        @forelse($sumberSaldos as $sumber)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden relative" wire:key="sumber-section-{{ $sumber->id }}">
                
                <!-- Inline Section Loading Overlay -->
                <div wire:loading wire:target="openCreateRekening({{ $sumber->id }}), openEditSumber({{ $sumber->id }}), confirmDeleteSumber({{ $sumber->id }})" class="absolute inset-0 bg-white/80 backdrop-blur-2xs rounded-2xl flex items-center justify-center z-20 space-x-2 transition-all">
                    <svg class="animate-spin w-5 h-5 text-[#5E17EB]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-bold text-[#5E17EB] bg-purple-50 px-2.5 py-1 rounded-full border border-purple-200 shadow-2xs">Memproses...</span>
                </div>

                <!-- Section Header -->
                <div class="p-4 bg-gray-50/70 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl {{ $loop->first ? 'bg-[#5E17EB] text-white' : 'bg-[#0052FF] text-white' }} flex items-center justify-center shadow-xs">
                            @if($loop->first)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-base font-extrabold text-gray-900 tracking-tight">{{ strtoupper($sumber->nama) }}</h2>
                            <button type="button"
                                    wire:click="openEditSumber({{ $sumber->id }})"
                                    wire:loading.attr="disabled"
                                    title="Edit Nama Sumber"
                                    class="text-gray-400 hover:text-[#5E17EB] p-1 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <button type="button"
                                    wire:click="confirmDeleteSumber({{ $sumber->id }})"
                                    wire:loading.attr="disabled"
                                    title="Hapus Sumber"
                                    class="text-gray-400 hover:text-red-500 p-1 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Right Controls: Subtotal & Add Button -->
                    <div class="flex items-center space-x-3">
                        <div class="px-3.5 py-1.5 rounded-xl bg-purple-50/80 border border-purple-100 flex items-center space-x-2">
                            <span class="text-xs font-semibold text-gray-600">Total Saldo {{ $sumber->nama }}</span>
                            <span class="text-sm font-extrabold text-[#5E17EB]">Rp {{ number_format($sumber->total_saldo ?? 0, 0, ',', '.') }}</span>
                        </div>

                        <button type="button"
                                wire:click="openCreateRekening({{ $sumber->id }})"
                                wire:loading.attr="disabled"
                                wire:target="openCreateRekening({{ $sumber->id }})"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold text-white bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 shadow-sm shadow-purple-900/20 transition-all flex items-center space-x-1.5 cursor-pointer">
                            <svg wire:loading.remove wire:target="openCreateRekening({{ $sumber->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <svg wire:loading wire:target="openCreateRekening({{ $sumber->id }})" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Tambah Rekening</span>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div :class="'table-size-' + tableSize" class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-200 text-gray-500 font-extrabold uppercase tracking-wider">
                                <th class="py-3 px-5 w-14 text-center">No</th>
                                <th class="py-3 px-5">Nama Bank</th>
                                <th class="py-3 px-5">Atas Nama</th>
                                <th class="py-3 px-5">Nomor Rekening</th>
                                <th class="py-3 px-5">Saldo</th>
                                <th class="py-3 px-5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            @forelse($sumber->rekenings as $idx => $item)
                                <tr class="hover:bg-purple-50/30 transition-colors relative" wire:key="rekening-row-{{ $item->id }}">
                                    <td class="py-3.5 px-5 text-center font-bold text-gray-400">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-5 font-bold text-gray-900">
                                        {{ $item->nama_bank }}
                                    </td>
                                    <td class="py-3.5 px-5 text-gray-700 font-medium">
                                        {{ $item->atas_nama ?: '-' }}
                                    </td>
                                    <td class="py-3.5 px-5 text-gray-600 font-mono text-xs">
                                        {{ $item->nomor_rekening ?: '-' }}
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="inline-flex items-center space-x-2.5">
                                            <span class="font-extrabold text-gray-900 tracking-tight text-sm">
                                                Rp {{ number_format($item->saldo, 0, ',', '.') }}
                                            </span>
                                            <button type="button"
                                                    wire:click="openQuickSaldo({{ $item->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openQuickSaldo({{ $item->id }})"
                                                    title="Ubah Cepat Saldo"
                                                    class="w-6 h-6 rounded-md bg-gray-100 hover:bg-[#5E17EB] disabled:opacity-50 text-gray-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer">
                                                <svg wire:loading.remove wire:target="openQuickSaldo({{ $item->id }})" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                <svg wire:loading wire:target="openQuickSaldo({{ $item->id }})" class="animate-spin w-3 h-3 text-[#5E17EB]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 text-center">
                                        <div class="inline-flex items-center justify-center space-x-2">
                                            <button type="button"
                                                    wire:click="openEditRekening({{ $item->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openEditRekening({{ $item->id }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-[#0052FF] hover:bg-blue-100 disabled:opacity-50 border border-blue-100 text-xs font-bold transition-all flex items-center space-x-1 cursor-pointer">
                                                <svg wire:loading.remove wire:target="openEditRekening({{ $item->id }})" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                <svg wire:loading wire:target="openEditRekening({{ $item->id }})" class="animate-spin w-3.5 h-3.5 text-[#0052FF]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button"
                                                    wire:click="confirmDeleteRekening({{ $item->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="confirmDeleteRekening({{ $item->id }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 disabled:opacity-50 border border-red-100 text-xs font-bold transition-all flex items-center space-x-1 cursor-pointer">
                                                <svg wire:loading.remove wire:target="confirmDeleteRekening({{ $item->id }})" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <svg wire:loading wire:target="confirmDeleteRekening({{ $item->id }})" class="animate-spin w-3.5 h-3.5 text-red-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400">
                                        <p class="text-xs font-semibold text-gray-400">Belum ada rekening tercatat di {{ $sumber->nama }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-200 shadow-sm space-y-3">
                <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <p class="text-sm font-bold text-gray-600">Belum ada Sumber Saldo yang dibuat</p>
                <button type="button" wire:click="openCreateSumber" class="px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-[#5E17EB] hover:bg-purple-700 transition-all cursor-pointer">
                    Buat Sumber Saldo Pertama
                </button>
            </div>
        @endforelse
    </div>

    <!-- Bottom Informational Widget (Matching Mockup) -->
    <div class="bg-emerald-50/50 rounded-2xl p-4 border border-emerald-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">TOTAL KESELURUHAN</span>
                <span class="text-xl font-extrabold text-emerald-900">Rp {{ number_format($totalSaldoKeseluruhan, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600">
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <span class="font-bold text-gray-800 block leading-tight">Sistem aman &amp; terkontrol</span>
                    <span class="text-[11px] text-gray-500">Hanya bendahara yang dapat mengubah data saldo</span>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <span class="font-bold text-gray-800 block leading-tight">Data terenkripsi</span>
                    <span class="text-[11px] text-gray-500">dan tersimpan dengan aman</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Tambah / Edit Rekening -->
    @if($showRekeningModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-scale-up space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-base font-extrabold text-gray-900">
                        {{ $isEditRekening ? 'Edit Rekening' : 'Tambah Rekening Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showRekeningModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit="saveRekening" class="space-y-3.5">
                    <!-- Sumber Saldo Selection -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Sumber Saldo</label>
                        <select wire:model="sumber_saldo_id"
                                class="w-full px-3 py-2 text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                            <option value="">Pilih Sumber Saldo</option>
                            @foreach($sumberSaldos as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                        @error('sumber_saldo_id') <span class="text-[11px] text-red-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Bank -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Bank</label>
                        <input type="text" wire:model="nama_bank"
                               class="w-full px-3 py-2 text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                        @error('nama_bank') <span class="text-[11px] text-red-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Atas Nama -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Atas Nama</label>
                        <input type="text" wire:model="atas_nama"
                               class="w-full px-3 py-2 text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                        @error('atas_nama') <span class="text-[11px] text-red-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nomor Rekening -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Rekening</label>
                        <input type="text" wire:model="nomor_rekening"
                               class="w-full px-3 py-2 text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                        @error('nomor_rekening') <span class="text-[11px] text-red-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nominal Saldo (Format Rupiah Indonesia) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nominal Saldo</label>
                        <x-input-rupiah wire:model="saldo" />
                        @error('saldo') <span class="text-[11px] text-red-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan</label>
                        <textarea wire:model="keterangan" rows="2"
                                  class="w-full px-3 py-2 text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]"></textarea>
                    </div>

                    <!-- Form Buttons with Exact Kasir Produk Loading -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="$set('showRekeningModal', false)"
                                class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveRekening"
                                class="px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-xs font-extrabold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer flex items-center space-x-2">
                            <svg wire:loading wire:target="saveRekening" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveRekening">{{ $isEditRekening ? 'Simpan Perubahan' : 'Tambah Rekening' }}</span>
                            <span wire:loading wire:target="saveRekening">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Quick Update Saldo -->
    @if($showQuickSaldoModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 animate-scale-up space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">Ubah Nominal Saldo</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $quickBankName }}</p>
                    </div>
                    <button type="button" wire:click="$set('showQuickSaldoModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit="saveQuickSaldo" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nominal Saldo Terkini</label>
                        <x-input-rupiah wire:model="quickSaldo" />
                        @error('quickSaldo') <span class="text-[11px] text-red-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-100">
                        <button type="button" wire:click="$set('showQuickSaldoModal', false)"
                                class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveQuickSaldo"
                                class="px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-xs font-extrabold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer flex items-center space-x-2">
                            <svg wire:loading wire:target="saveQuickSaldo" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveQuickSaldo">Update Saldo</span>
                            <span wire:loading wire:target="saveQuickSaldo">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Tambah / Edit Sumber Saldo -->
    @if($showSumberModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 animate-scale-up space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-base font-extrabold text-gray-900">
                        {{ $isEditSumber ? 'Edit Sumber Saldo' : 'Tambah Sumber Saldo' }}
                    </h3>
                    <button type="button" wire:click="$set('showSumberModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit="saveSumber" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Sumber Saldo</label>
                        <input type="text" wire:model="namaSumber"
                               class="w-full px-3 py-2 text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]">
                        @error('namaSumber') <span class="text-[11px] text-red-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi / Catatan</label>
                        <textarea wire:model="deskripsiSumber" rows="2"
                                  class="w-full px-3 py-2 text-xs font-medium text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB]"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-100">
                        <button type="button" wire:click="$set('showSumberModal', false)"
                                class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveSumber"
                                class="px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-xs font-extrabold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer flex items-center space-x-2">
                            <svg wire:loading wire:target="saveSumber" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveSumber">{{ $isEditSumber ? 'Simpan' : 'Tambah Sumber' }}</span>
                            <span wire:loading wire:target="saveSumber">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Konfirmasi Hapus -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 animate-scale-up space-y-4">
                <div class="flex items-center space-x-3 text-red-600">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">{{ $deleteTitle }}</h3>
                    </div>
                </div>

                <p class="text-xs text-gray-600 leading-relaxed">{{ $deleteMessage }}</p>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-100">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="deleteConfirmed"
                            wire:loading.attr="disabled"
                            wire:target="deleteConfirmed"
                            class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 disabled:opacity-50 text-xs font-extrabold text-white shadow-lg shadow-red-900/30 transition-all cursor-pointer flex items-center space-x-2">
                        <svg wire:loading wire:target="deleteConfirmed" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deleteConfirmed">Ya, Hapus</span>
                        <span wire:loading wire:target="deleteConfirmed">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

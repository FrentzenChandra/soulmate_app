<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    @if($isOwner)
        <!-- Switcher Dropdown Button for Owner -->
        <button @click="open = !open" 
                type="button" 
                class="flex items-center space-x-3 px-3 py-2 rounded-xl bg-gray-50 hover:bg-purple-50/70 border border-gray-200/80 transition-all text-left group">
            
            <div class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors {{ $activeBranchId === 'hq' || !$activeBranchId ? 'bg-purple-600 text-white shadow-sm shadow-purple-500/30' : 'bg-blue-600 text-white shadow-sm shadow-blue-500/30' }}">
                @if($activeBranchId === 'hq' || !$activeBranchId)
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                @endif
            </div>

            <div class="pr-2">
                <span class="text-[10px] uppercase font-extrabold tracking-wider block leading-none {{ $activeBranchId === 'hq' || !$activeBranchId ? 'text-purple-600' : 'text-blue-600' }}">
                    {{ $activeBranchId === 'hq' || !$activeBranchId ? 'Konteks Global' : 'Konteks Cabang' }}
                </span>
                <span class="text-sm font-bold text-gray-900 block leading-tight">
                    {{ $activeBranchId === 'hq' || !$activeBranchId ? 'Kantor Pusat (HQ)' : ($activeCabang?->nama_cabang ?? 'Pilih Cabang') }}
                </span>
            </div>

            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div x-show="open" 
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute left-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50 divide-y divide-gray-100">
            
            <div class="px-4 py-2">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Pilih Konteks Kerja</p>
            </div>

            <!-- HQ Option -->
            <div class="p-1">
                <button wire:click="switchBranch('hq')" 
                        @click="open = false" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all {{ $activeBranchId === 'hq' || !$activeBranchId ? 'bg-purple-50 text-purple-900 font-bold' : 'hover:bg-gray-50 text-gray-700' }}">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </span>
                        <div class="text-left">
                            <span class="text-sm block leading-none font-bold">Kantor Pusat (HQ)</span>
                            <span class="text-[11px] text-gray-400 font-normal">Akses global, analitik & master</span>
                        </div>
                    </div>
                    @if($activeBranchId === 'hq' || !$activeBranchId)
                        <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                    @endif
                </button>
            </div>

            <!-- Branch Options -->
            <div class="p-1 max-h-60 overflow-y-auto custom-scrollbar">
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Daftar Cabang Aktif</p>
                
                @forelse($allCabangs as $c)
                    <button wire:click="switchBranch({{ $c->id }})" 
                            @click="open = false" 
                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ $activeBranchId == $c->id ? 'bg-blue-50 text-blue-900 font-bold' : 'hover:bg-gray-50 text-gray-700' }}">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                                {{ substr($c->nama_cabang, 0, 2) }}
                            </span>
                            <div class="text-left">
                                <span class="text-sm block leading-none font-semibold">{{ $c->nama_cabang }}</span>
                                <span class="text-[11px] text-gray-400 font-normal">{{ $c->kode_cabang }}</span>
                            </div>
                        </div>
                        @if($activeBranchId == $c->id)
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        @endif
                    </button>
                @empty
                    <div class="px-3 py-3 text-xs text-gray-400 text-center">
                        Belum ada cabang terdaftar
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- Store Brand / Info for Non-Owner Roles -->
        <div class="flex items-center space-x-3 px-3 py-2 rounded-xl bg-gray-50 border border-gray-200/80">
            <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-extrabold text-purple-600 tracking-wider block leading-none">Point of Sale</span>
                <span class="text-sm font-bold text-gray-900 block leading-tight">
                    Soulmate Komputer
                </span>
            </div>
        </div>
    @endif

</div>

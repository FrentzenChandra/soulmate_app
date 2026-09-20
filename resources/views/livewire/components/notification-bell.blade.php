<div class="relative" x-data="{ bellOpen: false }" @click.outside="bellOpen = false">
    <!-- Trigger Button -->
    <button @click="bellOpen = !bellOpen"
            type="button"
            class="relative p-2.5 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors focus:outline-none cursor-pointer group"
            title="Notifikasi Stok & Peringatan">
        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>

        <!-- Red Badge if Empty Stock or Missing Modal Awal Exists -->
        @if($modalAwalAlertCount > 0 || $emptyCount > 0)
            <span class="absolute top-1 right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-600 text-[10px] font-black text-white ring-2 ring-white animate-pulse">
                {{ $totalAlerts }}
            </span>
        @elseif($criticalCount > 0)
            <span class="absolute top-1 right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-white ring-2 ring-white">
                {{ $criticalCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div x-show="bellOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl shadow-purple-950/15 border border-gray-100 py-3 z-50 divide-y divide-gray-100"
         style="display: none;">

        <!-- Dropdown Header -->
        <div class="px-4 pb-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full {{ ($modalAwalAlertCount > 0 || $emptyCount > 0) ? 'bg-rose-500 animate-pulse' : ($criticalCount > 0 ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                    <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Notifikasi</h4>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                    {{ $contextName }}
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                @if($modalAwalAlertCount > 0)
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 text-[10px] font-extrabold border border-rose-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Modal Awal Belum Input</span>
                    </span>
                @endif
                @if($emptyCount > 0)
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 text-[10px] font-extrabold border border-rose-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>{{ $emptyCount }} Stok Habis</span>
                    </span>
                @endif
                @if($criticalCount > 0)
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-extrabold border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>{{ $criticalCount }} Stok Menipis</span>
                    </span>
                @endif
                @if($totalAlerts === 0)
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-extrabold border border-emerald-200">
                        <span>Semua Kondisi Aman</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Modal Awal Alert Warning Box -->
        @if($modalAwalAlertCount > 0)
            <div class="p-3.5 bg-rose-50/90 border-y border-rose-100 flex items-start space-x-3">
                <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-black uppercase tracking-wider text-rose-900">Peringatan Modal Awal</span>
                        <span class="text-[10px] font-extrabold text-rose-700 font-mono bg-rose-100 px-1.5 py-0.2 rounded">&gt; 10:00</span>
                    </div>
                    <p class="text-xs font-bold text-rose-800 mt-0.5 leading-snug">
                        @if($isHQ && $missingModalCabangs->count() > 1)
                            {{ $missingModalCabangs->count() }} cabang belum mencatat modal awal kasir hari ini.
                        @elseif($isHQ && $missingModalCabangs->isNotEmpty())
                            Cabang {{ $missingModalCabangs->first()->nama_cabang }} belum mencatat modal awal kasir hari ini.
                        @else
                            Modal awal kasir hari ini belum dicatat!
                        @endif
                    </p>
                    <div class="mt-2">
                        <a href="{{ route('modal-awal') }}"
                           class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-black shadow-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>Input Modal Awal</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Alert Items List with Scrollbar -->
        <div class="py-1 max-h-80 overflow-y-auto divide-y divide-gray-50 custom-scrollbar overscroll-contain">
            @forelse($alertItems as $item)
                @php
                    $type = is_object($item) ? ($item->type ?? 'produk') : ($item['type'] ?? 'produk');
                    $qty = is_object($item) ? $item->jumlah : ($item['jumlah'] ?? 0);
                    $nama = is_object($item) ? ($item->nama ?? $item->nama_produk ?? 'Item') : ($item['nama'] ?? 'Item');
                    $kode = is_object($item) ? ($item->kode ?? $item->barcode ?? null) : null;
                    $cabangTag = is_object($item) ? ($item->cabang_nama ?? null) : null;
                    $fotoUrl = is_object($item) ? ($item->foto_url ?? null) : null;
                    $isZero = $qty <= 0;
                @endphp
                <div class="px-4 py-2.5 hover:bg-gray-50/90 transition-colors flex items-center justify-between space-x-3">
                    <!-- Foto Thumbnail / Icon Box -->
                    <div class="relative shrink-0">
                        @if($fotoUrl && file_exists(public_path($fotoUrl)))
                            <img src="{{ asset($fotoUrl) }}" alt="{{ $nama }}" class="w-9 h-9 rounded-lg object-cover border border-gray-200">
                        @elseif($fotoUrl && (str_starts_with($fotoUrl, 'http') || str_starts_with($fotoUrl, 'storage/') || str_starts_with($fotoUrl, 'images/')))
                            <img src="{{ asset($fotoUrl) }}" alt="{{ $nama }}" class="w-9 h-9 rounded-lg object-cover border border-gray-200" onerror="this.onerror=null;this.src='{{ asset('images/logo_bulat.png') }}';">
                        @else
                            <div class="w-9 h-9 rounded-lg {{ $type === 'sparepart' ? 'bg-purple-50 text-[#5E17EB] border border-purple-100' : ($isZero ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-blue-50 text-blue-600 border border-blue-100') }} flex items-center justify-center">
                                @if($type === 'sparepart')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center space-x-1.5 mb-0.5">
                            @if($type === 'sparepart')
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                    Sparepart
                                </span>
                            @else
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                    Produk
                                </span>
                            @endif

                            @if($isHQ && $cabangTag)
                                <span class="px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 text-[9px] font-bold border border-gray-200">{{ $cabangTag }}</span>
                            @endif
                        </div>
                        <p class="text-xs font-bold text-gray-900 truncate leading-tight">
                            {{ $nama }}
                        </p>
                        @if($kode)
                            <span class="text-[10px] text-gray-400 font-mono block mt-0.5">{{ $kode }}</span>
                        @endif
                    </div>
                    <div class="shrink-0">
                        @if($isZero)
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-black border border-rose-200">
                                <svg class="w-2.5 h-2.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>Habis (0)</span>
                            </span>
                        @else
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black border border-amber-200">
                                <svg class="w-2.5 h-2.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01"></path></svg>
                                <span>Sisa {{ $qty }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-gray-400">
                    <svg class="w-8 h-8 text-emerald-400 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs font-bold text-gray-700">Kondisi Stok Aman</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Tidak ada produk atau sparepart dengan stok habis / menipis.</p>
                </div>
            @endforelse
        </div>

        <!-- Dropdown Footer -->
        <div class="px-4 pt-2.5 flex items-center justify-between text-xs font-bold">
            <a href="{{ route('modal-awal') }}"
               class="text-gray-600 hover:text-[#5E17EB] flex items-center space-x-1 transition-colors">
                <span>Modal Awal</span>
            </a>
            <a href="{{ route('cabang.stok') }}"
               class="text-[#5E17EB] hover:text-purple-700 flex items-center space-x-1 transition-colors">
                <span>Stok Produk</span>
            </a>
            <a href="{{ route('master.servis') }}"
               class="text-gray-500 hover:text-[#5E17EB] flex items-center space-x-1 transition-colors">
                <span>Master Servis</span>
            </a>
        </div>
    </div>
</div>


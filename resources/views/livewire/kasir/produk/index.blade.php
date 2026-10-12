<div x-data="{
        openPayModal() {
            if ({{ count($cart) }} > 0) {
                $wire.openPaymentModal();
            }
        },
        openScanModal(tab = 'scanner') {
            $wire.openScanModal(tab);
        }
     }"
     x-on:open-print-invoice.window="window.printDocument ? window.printDocument($event.detail.url) : window.location.href = $event.detail.url"
     @keydown.window="
        if ($event.key === 'F4' || $event.code === 'F4') {
            $event.preventDefault();
            openPayModal();
        }
     "
     class="space-y-5">

    <!-- Top Bar: Header Info & Status -->
    <div class="bg-white card-custom p-4 sm:p-5 rounded-2xl border border-gray-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-[#5E17EB] text-white flex items-center justify-center shadow-md shadow-purple-900/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-black text-gray-900 tracking-tight">Kasir POS Produk</h1>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-black border border-emerald-200">Terminal Aktif</span>
                </div>
                <div class="flex items-center space-x-2 text-xs text-gray-500 font-medium mt-0.5">
                    @if(auth()->user()->isOwner())
                        <span>Outlet: <strong class="text-[#0052FF] font-bold">{{ $cabang?->nama_cabang ?? 'Pilih Cabang' }}</strong></span>
                    @else
                        <span>Toko: <strong class="text-[#0052FF] font-bold">Soulmate Komputer</strong></span>
                    @endif
                    <span>Kasir: <strong class="text-gray-800 font-bold">{{ auth()->user()->name }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Direct Stok Info & Actions -->
        <div class="flex items-center space-x-2.5">
            <!-- Tombol Buka Modal Scan (USB / Keyboard) -->
            <button type="button"
                    wire:click="openScanModal('scanner')"
                    class="px-3.5 py-2 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold transition-all shadow-sm inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span>Scan Barcode</span>
                <span class="px-1.5 py-0.5 rounded bg-white/20 text-[10px] font-mono font-black">`</span>
            </button>

            <!-- Tombol Buka Scanner Kamera Langsung -->
            <button type="button"
                    wire:click="openScanModal('camera')"
                    class="px-3 py-2 rounded-xl bg-purple-50 text-[#5E17EB] hover:bg-[#5E17EB] hover:text-white text-xs font-bold transition-colors inline-flex items-center space-x-1.5 cursor-pointer"
                    title="Scan Barcode menggunakan Kamera HP / Webcam">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Kamera</span>
            </button>

            <a href="{{ route('cabang.stok') }}"
               class="px-3 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-bold transition-colors inline-flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                <span>Stok</span>
            </a>
        </div>
    </div>

    <!-- Main Layout POS: Split 2 Kolom -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ============================================== -->
        <!-- KOLOM KIRI: KATALOG PRODUK & SEARCH (7/12)     -->
        <!-- ============================================== -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">

            <!-- Search Bar & Kategori Filters -->
            <div x-data
                 @keydown.window="if ($event.key === '`' || $event.code === 'Backquote') {
                     const active = document.activeElement;
                     const isOtherInput = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT') && active.id !== 'posSearchInput';
                     if (!isOtherInput) {
                         $event.preventDefault();
                         $wire.openScanModal('scanner');
                     }
                 }"
                 class="bg-white card-custom p-4 rounded-2xl border border-gray-100 shadow-xs space-y-3">

                <!-- Search Input Form (Murni Pencarian Nama Produk) -->
                <form wire:submit.prevent="handleSearchOrScan" class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text"
                           id="posSearchInput"
                           wire:model.live.debounce.300ms="search"
                           wire:keydown.enter.prevent="handleSearchOrScan"
                           class="w-full pl-11 pr-32 py-3 bg-gray-50/90 text-sm font-semibold border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all">

                    <div class="absolute inset-y-0 right-1.5 flex items-center space-x-1.5">
                        <button type="button"
                                wire:click="openScanModal('scanner')"
                                class="hidden sm:inline-flex items-center px-2 py-1 text-[10px] font-mono font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-lg cursor-pointer transition-colors"
                                title="Buka modal scan barcode khusus (Tekan `)">
                            ` Scan
                        </button>
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-lg bg-[#5E17EB] text-white hover:bg-purple-700 text-xs font-bold transition-all shadow-xs inline-flex items-center space-x-1 cursor-pointer">
                            <span>Cari</span>
                        </button>
                    </div>
                </form>

                <!-- Kategori Chips Filter -->
                <div class="flex items-center space-x-2 overflow-x-auto custom-scrollbar pb-1">
                    <button type="button"
                            wire:click="$set('kategoriFilter', 'semua')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all cursor-pointer {{ $kategoriFilter === 'semua' ? 'bg-[#5E17EB] text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua Kategori
                    </button>
                    @foreach($kategoris as $k)
                        <button type="button"
                                wire:click="$set('kategoriFilter', '{{ $k->id }}')"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all cursor-pointer {{ $kategoriFilter == $k->id ? 'bg-[#5E17EB] text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $k->nama_kategori }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Grid Produk POS (Infinite Scroll & Lazy Loading with Skeleton Loader) -->
            <x-grid-skeleton :count="8" target="search, kategoriFilter, handleSearchOrScan" />
            <div wire:loading.remove wire:target="search, kategoriFilter, handleSearchOrScan" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @forelse($produks as $p)
                    @php
                        $stokCabang = $p->stoks->first()?->jumlah ?? 0;
                        $isZero = $stokCabang <= 0;
                        $isCrit = !$isZero && $stokCabang <= 3;
                    @endphp
                    <div wire:key="pos-prod-{{ $p->id }}"
                         @if(!$isZero) wire:click="addToCart({{ $p->id }})" @endif
                         class="bg-white rounded-2xl border transition-all p-3 flex flex-col justify-between relative group select-none {{ $isZero ? 'border-gray-200 bg-gray-50/60 opacity-60 cursor-not-allowed' : 'border-gray-100 hover:border-[#5E17EB]/40 hover:shadow-lg hover:shadow-purple-900/10 cursor-pointer active:scale-98' }}">

                        <!-- Loading State Saat Ditambahkan ke Keranjang -->
                        <div wire:loading wire:target="addToCart({{ $p->id }})" class="absolute inset-0 bg-white/85 backdrop-blur-2xs rounded-2xl flex flex-col items-center justify-center z-20 space-y-1.5 transition-all">
                            <svg class="animate-spin w-6 h-6 text-[#5E17EB]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span class="text-[10px] font-black text-[#5E17EB] bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200 shadow-2xs">Menambahkan...</span>
                        </div>

                        <div>
                            <!-- Thumbnail & Floating Stock Indicator (Lazy Loaded with 1-Day Browser Cache) -->
                            <div class="relative w-full aspect-square rounded-xl bg-gray-50 overflow-hidden mb-2.5 flex items-center justify-center border border-gray-100">
                                @if($p->foto_url)
                                    <img src="{{ str_starts_with($p->foto_url, 'http') ? $p->foto_url : asset($p->foto_url) }}"
                                         alt="{{ $p->nama_produk }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-purple-50 text-[#5E17EB]\'><svg class=\'w-10 h-10 opacity-40\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg></div>';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-purple-50 text-[#5E17EB]">
                                        <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif

                                <!-- Stock Badge Indicator -->
                                <div class="absolute top-2 right-2">
                                    @if($isZero)
                                        <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-black text-[9px] shadow-sm flex items-center space-x-1">
                                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            <span>HABIS</span>
                                        </span>
                                    @elseif($isCrit)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-500 text-white font-black text-[9px] shadow-sm flex items-center space-x-1 animate-pulse">
                                            <span>Sisa {{ $stokCabang }}</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md bg-white/95 text-gray-700 font-extrabold text-[9px] shadow-sm backdrop-blur-xs border border-gray-200">
                                            Stok: {{ $stokCabang }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Detail Produk -->
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block truncate">
                                {{ $p->kategori?->nama_kategori ?? 'Umum' }}
                            </span>
                            <h3 class="text-xs font-extrabold text-gray-900 leading-snug line-clamp-2 mt-0.5 group-hover:text-[#5E17EB] transition-colors">
                                {{ $p->nama_produk }}
                            </h3>
                            @if($p->barcode)
                                <span class="text-[10px] font-mono text-gray-400 block mt-0.5 truncate">
                                    {{ $p->barcode }}
                                </span>
                            @endif
                        </div>

                        <!-- Harga & Action -->
                        <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs font-black text-[#0052FF]">
                                Rp {{ number_format($p->harga_jual, 0, ',', '.') }}
                            </span>
                            @if(!$isZero || !$isReal)
                                <button type="button"
                                        wire:click.stop="addToCart({{ $p->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="addToCart({{ $p->id }})"
                                        class="w-7 h-7 rounded-lg bg-purple-50 hover:bg-[#5E17EB] text-[#5E17EB] hover:text-white transition-all flex items-center justify-center cursor-pointer shadow-xs disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="addToCart({{ $p->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    <svg wire:loading wire:target="addToCart({{ $p->id }})" class="animate-spin w-3.5 h-3.5 text-[#5E17EB]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 sm:col-span-3 xl:col-span-4 py-16 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-gray-800">Tidak ada produk ditemukan</h4>
                        <p class="text-xs text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian atau kategori.</p>
                    </div>
                @endforelse
            </div>

            <!-- Infinite Scroll Sentinel & Status -->
            <div class="mt-2">
                <x-infinite-scroll-sentinel
                    :has-more="$hasMore"
                    target="loadMore"
                    label="produk"
                    :current="$produks->count()"
                    :total="$totalProduk"
                    color="purple" />
            </div>
        </div>

        <!-- ============================================== -->
        <!-- KOLOM KANAN: KERANJANG BELANJA & CHECKOUT (5/12)-->
        <!-- ============================================== -->
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-3 lg:sticky lg:top-4 lg:max-h-[calc(100vh-1.5rem)] lg:overflow-y-auto custom-scrollbar pr-0.5">

            <!-- Customer Selector Widget -->
            <div class="bg-white card-custom p-3.5 rounded-2xl border border-gray-100 shadow-xs space-y-2.5 shrink-0">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>Pelanggan</span>
                    </span>

                    <!-- Toggle Pelanggan Umum vs Langganan -->
                    <div class="flex items-center space-x-1 bg-gray-100 p-0.5 rounded-lg text-xs font-bold">
                        <button type="button"
                                wire:click="setCustomerUmum"
                                class="px-2.5 py-1 rounded-md transition-all cursor-pointer {{ $customerType === 'umum' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                            Umum
                        </button>
                        <button type="button"
                                wire:click="$set('customerType', 'langganan')"
                                class="px-2.5 py-1 rounded-md transition-all cursor-pointer {{ $customerType === 'langganan' ? 'bg-white text-[#5E17EB] shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                            Langganan
                        </button>
                    </div>
                </div>

                @if($customerType === 'langganan')
                    <div class="space-y-2 pt-1 border-t border-gray-100">
                        @if($selectedCustomer)
                            <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5 truncate">
                                    @if($selectedCustomer->foto_url)
                                        <img src="{{ str_starts_with($selectedCustomer->foto_url, 'http') ? $selectedCustomer->foto_url : asset($selectedCustomer->foto_url) }}"
                                             alt="{{ $selectedCustomer->nama }}"
                                             loading="lazy"
                                             decoding="async"
                                             class="w-8 h-8 rounded-full object-cover border border-purple-200 shrink-0"
                                             onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-8 h-8 rounded-full bg-purple-200 text-[#5E17EB] font-black flex items-center justify-center text-xs shrink-0\'>{{ strtoupper(substr($selectedCustomer->nama, 0, 1)) }}</div>';">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-purple-200 text-[#5E17EB] font-black flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($selectedCustomer->nama, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="truncate">
                                        <h4 class="text-xs font-extrabold text-[#5E17EB] truncate">{{ $selectedCustomer->nama }}</h4>
                                        {{-- <p class="text-[11px] text-gray-500">{{ $selectedCustomer->no_hp ?: 'Tanpa No HP' }}</p> --}}
                                    </div>
                                </div>
                                <button type="button"
                                        wire:click="setCustomerUmum"
                                        class="p-1 text-gray-400 hover:text-rose-600 transition-colors cursor-pointer"
                                        title="Ganti Pelanggan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        @else
                            <div class="relative">
                                <input type="text"
                                       wire:model.live.debounce.300ms="customerSearch"
                                       class="w-full text-xs font-medium pl-8 pr-20 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-1 focus:ring-[#5E17EB]">
                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <button type="button"
                                        wire:click="openNewCustomerModal"
                                        class="absolute right-1.5 top-1.5 px-2 py-1 bg-[#5E17EB] text-white text-[10px] font-bold rounded-lg hover:bg-purple-700 cursor-pointer">
                                    + Baru
                                </button>
                            </div>

                            @if($customers->count() > 0)
                                <div class="bg-white rounded-xl border border-gray-200 shadow-lg divide-y divide-gray-100 max-h-40 overflow-y-auto custom-scrollbar mt-1">
                                    @foreach($customers as $c)
                                        <button type="button"
                                                wire:click="selectCustomer({{ $c->id }})"
                                                class="w-full p-2.5 text-left hover:bg-purple-50 transition-colors flex items-center justify-between gap-2.5 cursor-pointer group">
                                            <div class="flex items-center space-x-2.5 min-w-0">
                                                <div class="w-9 h-9 rounded-xl overflow-hidden bg-purple-100 text-[#5E17EB] font-bold text-xs flex items-center justify-center shrink-0 border border-purple-200 shadow-2xs">
                                                    @if($c->foto_url)
                                                        <img src="{{ asset($c->foto_url) }}"
                                                             alt="{{ $c->nama }}"
                                                             class="w-full h-full object-cover"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <span style="display:none;" class="w-full h-full items-center justify-center bg-purple-100 text-[#5E17EB] font-bold text-xs">{{ strtoupper(substr($c->nama, 0, 1)) }}</span>
                                                    @else
                                                        <span>{{ strtoupper(substr($c->nama, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="text-xs font-bold text-gray-900 group-hover:text-[#5E17EB] transition-colors block truncate">{{ $c->nama }}</span>
                                                    {{-- <span class="text-[10px] text-gray-500 block truncate">{{ $c->no_hp ?: 'Tanpa No HP' }}</span> --}}
                                                </div>
                                            </div>
                                            <span class="px-2 py-0.5 rounded-md bg-purple-50 group-hover:bg-[#5E17EB] group-hover:text-white text-[#5E17EB] text-[10px] font-bold transition-all shrink-0">
                                                Pilih
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            <!-- Cart Items Container (Sticky Height with Independent Scroll) -->
            <div class="bg-white card-custom rounded-2xl border border-gray-100 shadow-xs flex flex-col flex-1 min-h-0 overflow-hidden">
                <div class="p-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 shrink-0">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider">Keranjang Belanja</h3>
                    </div>
                    @if(count($cart) > 0)
                        <button type="button"
                                wire:click="clearCart"
                                wire:confirm="Kosongkan seluruh produk dari keranjang belanja?"
                                class="text-[11px] font-bold text-rose-600 hover:underline cursor-pointer">
                            Kosongkan
                        </button>
                    @endif
                </div>

                <!-- Toggle Mode Penjualan: Real vs Non-Real -->
                <div class="px-3 py-2 bg-gray-50 border-b border-gray-100 flex items-center justify-between gap-2 shrink-0">
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full {{ $isReal ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span class="text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tipe:</span>
                    </div>
                    <div class="flex items-center space-x-1 bg-gray-200/80 p-0.5 rounded-lg text-[10px] font-extrabold">
                        <button type="button"
                                wire:click="toggleRealMode"
                                class="px-2 py-0.5 rounded-md transition-all cursor-pointer {{ $isReal ? 'bg-white text-emerald-700 shadow-2xs font-black' : 'text-gray-500 hover:text-gray-900' }}">
                            Real (Potong Stok)
                        </button>
                        <button type="button"
                                wire:click="toggleRealMode"
                                class="px-2 py-0.5 rounded-md transition-all cursor-pointer {{ !$isReal ? 'bg-amber-500 text-white shadow-2xs font-black' : 'text-gray-500 hover:text-gray-900' }}">
                            Non-Real (Tanpa Potong Stok)
                        </button>
                    </div>
                </div>

                @if(!$isReal)
                    <!-- Note Non-Real di Sidebar Kasir -->
                    <div class="mx-3 my-2 p-2.5 bg-amber-50 border-2 border-amber-300 rounded-xl flex items-start space-x-2 text-amber-900 shadow-2xs">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="text-[11px] leading-snug font-bold text-amber-800">
                            <span class="font-black text-amber-950 uppercase block">Mode Non-Real Aktif:</span>
                            Data <span class="underline font-black text-amber-950">TIDAK MASUK OMZET</span> &amp; <span class="underline font-black text-amber-950">TIDAK MERUBAH NILAI STOK</span>.
                        </div>
                    </div>
                @endif

                <!-- List Items Scrollable (Bounded Height: Never pushes Checkout button down) -->
                <div class="space-y-2.5 min-h-[140px] max-h-[340px] xl:max-h-[420px] overflow-y-auto overscroll-contain custom-scrollbar p-2.5">
                    @forelse($cart as $item)
                        <div wire:key="cart-item-{{ $item['id'] }}"
                             class="p-2.5 sm:p-3 bg-white hover:bg-purple-50/20 border {{ !empty($item['is_custom_price']) ? 'border-purple-300 ring-1 ring-purple-100 bg-purple-50/10' : 'border-gray-200/80' }} rounded-2xl transition-all shadow-2xs space-y-2"
                             x-data="{
                                 subtotal: {{ (float) $item['subtotal'] }},
                                 lastCommitted: {{ (float) $item['subtotal'] }},
                                 qty: {{ max(1, (int) $item['qty']) }},
                                 display: '',
                                 humanHelper: '',
                                 debounceTimer: null,
                                 get unitPrice() {
                                     return Math.round(this.subtotal / Math.max(1, this.qty));
                                 },
                                 formatRupiah(val) {
                                     if (val === null || val === undefined || val === '' || isNaN(val)) return '0';
                                     let num = parseInt(val.toString().replace(/[^0-9]/g, ''), 10);
                                     return isNaN(num) ? '0' : num.toLocaleString('id-ID');
                                 },
                                 calcHelper(val) {
                                     let num = parseInt((val || 0).toString().replace(/[^0-9]/g, ''), 10);
                                     if (isNaN(num) || num <= 0) return '';
                                     if (num >= 1000000000) {
                                         let b = (num / 1000000000).toLocaleString('id-ID', { maximumFractionDigits: 2 });
                                         return b + ' Miliar';
                                     }
                                     if (num >= 1000000) {
                                         let m = (num / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 2 });
                                         return m + ' Juta';
                                     }
                                     if (num >= 10000) {
                                         let k = (num / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                                         return k + ' Ribu';
                                     }
                                     return '';
                                 },
                                 syncFromServer(val) {
                                     if (document.activeElement === this.$refs.inputEl) return;
                                     this.subtotal = val;
                                     this.lastCommitted = val;
                                     this.display = this.formatRupiah(val);
                                     this.humanHelper = this.calcHelper(val);
                                 },
                                 init() {
                                     this.display = this.formatRupiah(this.subtotal);
                                     this.humanHelper = this.calcHelper(this.subtotal);
                                 },
                                 onInput(e) {
                                     let clean = e.target.value.replace(/[^0-9]/g, '');
                                     let num = clean ? parseInt(clean, 10) : 0;
                                     this.subtotal = num;
                                     this.display = num ? num.toLocaleString('id-ID') : '';
                                     this.humanHelper = this.calcHelper(num);
                                     e.target.value = this.display;

                                     clearTimeout(this.debounceTimer);
                                     if (num > 0) {
                                         this.debounceTimer = setTimeout(() => {
                                             this.onCommit();
                                         }, 500);
                                     }
                                 },
                                 onCommit() {
                                     clearTimeout(this.debounceTimer);
                                     if (!this.subtotal || this.subtotal <= 0) return;
                                     if (this.subtotal === this.lastCommitted) return;
                                     this.lastCommitted = this.subtotal;
                                     $wire.updateItemSubtotal({{ $item['id'] }}, this.subtotal);
                                 }
                             }"
                             x-effect="syncFromServer({{ (float) $item['subtotal'] }})">
                            <!-- Item Title (Full Width Single Line) & Delete Button -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                    <h4 class="text-xs font-black text-gray-900 truncate leading-snug" title="{{ $item['nama'] }}">{{ $item['nama'] }}</h4>
                                    @if(!empty($item['is_custom_price']))
                                        <span class="px-1.5 py-0.2 rounded-md bg-purple-100 text-[#5E17EB] text-[9px] font-black uppercase tracking-wider shrink-0 border border-purple-200">
                                            Kustom
                                        </span>
                                    @endif
                                </div>
                                <button type="button"
                                        wire:click="removeFromCart({{ $item['id'] }})"
                                        wire:loading.attr="disabled"
                                        class="text-gray-300 hover:text-rose-600 transition-colors p-0.5 cursor-pointer shrink-0"
                                        title="Hapus dari keranjang">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>

                            <!-- Item Qty Stepper & Editable Subtotal -->
                            <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-gray-100">
                                <!-- Stepper Qty -->
                                <div class="flex items-center space-x-1 bg-gray-100/90 p-1 rounded-xl border border-gray-200/80 shrink-0">
                                    <button type="button"
                                            wire:click="adjustQty({{ $item['id'] }}, -1)"
                                            wire:loading.attr="disabled"
                                            class="w-6 h-6 rounded-lg bg-white hover:bg-gray-200 text-gray-800 font-black text-xs flex items-center justify-center transition-colors cursor-pointer shadow-2xs disabled:opacity-50 shrink-0">
                                        &minus;
                                    </button>
                                    <div class="px-1 flex items-center space-x-1">
                                        <span class="text-[10px] font-extrabold text-gray-500 uppercase whitespace-nowrap">Qty:</span>
                                        <input type="number"
                                               value="{{ $item['qty'] }}"
                                               wire:change="updateQty({{ $item['id'] }}, $event.target.value)"
                                               min="1"
                                               max="{{ $item['stok_max'] }}"
                                               class="w-12 text-center text-xs font-black py-0.5 px-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5E17EB] bg-white text-gray-900 shadow-2xs">
                                    </div>
                                    <button type="button"
                                            wire:click="adjustQty({{ $item['id'] }}, 1)"
                                            wire:loading.attr="disabled"
                                            class="w-6 h-6 rounded-lg bg-[#5E17EB] hover:bg-purple-700 text-white font-black text-xs flex items-center justify-center transition-colors cursor-pointer shadow-2xs disabled:opacity-50 shrink-0">
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal Item Input -->
                                <div class="flex flex-col items-end min-w-0">
                                    <div class="relative w-36 sm:w-40">
                                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-[10px] font-bold text-gray-400 select-none pointer-events-none">Rp</span>
                                        <input type="text"
                                               x-ref="inputEl"
                                               inputmode="numeric"
                                               :value="display"
                                               @input="onInput($event)"
                                               @blur="onCommit()"
                                               @keydown.enter.prevent="onCommit(); $event.target.blur()"
                                               @focus="$event.target.select()"
                                               class="w-full pl-7 pr-2.5 py-1 text-xs sm:text-sm font-black font-mono tabular-nums text-right rounded-xl border {{ !empty($item['is_custom_price']) ? 'border-purple-300 bg-purple-50/30 text-[#5E17EB]' : 'border-gray-200 bg-white text-[#0052FF]' }} focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all shadow-2xs">
                                    </div>
                                </div>
                            </div>

                            <!-- Baris Helper Angka Besar & Rincian Harga Per Unit -->
                            <div class="flex items-center justify-between text-[10px] text-gray-500 font-bold px-0.5">
                                <div class="flex items-center gap-1.5">
                                    <template x-if="humanHelper">
                                        <span class="px-1.5 py-0.5 rounded-md bg-purple-100 text-[#5E17EB] font-black text-[10px]" x-text="humanHelper"></span>
                                    </template>
                                    @if(!empty($item['is_custom_price']))
                                        <button type="button"
                                                wire:click="resetItemPrice({{ $item['id'] }})"
                                                wire:loading.attr="disabled"
                                                title="Kembalikan ke harga katalog: Rp {{ number_format($item['harga_katalog'], 0, ',', '.') }}"
                                                class="text-[10px] text-amber-700 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 px-1.5 py-0.5 rounded-md border border-amber-200 transition-colors cursor-pointer inline-flex items-center space-x-1">
                                            <span>Reset</span>
                                        </button>
                                    @endif
                                </div>
                                <div class="text-right truncate">
                                    <span x-text="'@ Rp ' + formatRupiah(unitPrice) + ' / unit'">@ Rp {{ number_format($item['harga'], 0, ',', '.') }} / unit</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-gray-400">
                            <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="text-xs font-bold text-gray-600">Keranjang Masih Kosong</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Pilih produk di katalog atau scan barcode untuk transaksi.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Financial Calculation & PPN Section (Pinned Bottom) -->
                <div class="p-3.5 bg-gray-50 border-t border-gray-100 space-y-2 shrink-0">

                    <!-- Subtotal -->
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Subtotal Belanja</span>
                        <span class="font-extrabold text-gray-900">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <!-- Diskon Input -->
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Diskon Transaksi</span>
                        <div class="w-28">
                            <x-input-rupiah wire:model.live.debounce.250ms="diskon" inputClass="!py-1 !px-2 !text-right !text-xs !font-extrabold" />
                        </div>
                    </div>

                    <!-- PPN Selector -->
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Opsi Pajak (PPN)</span>
                        <select wire:model.live="selectedPpnId"
                                class="text-xs font-bold py-1 px-2.5 border border-gray-200 rounded-lg bg-white focus:ring-1 focus:ring-[#5E17EB]">
                            @foreach($masterPpns as $ppn)
                                <option value="{{ $ppn->id }}">{{ $ppn->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($this->ppnNominal > 0)
                        <div class="flex items-center justify-between text-purple-700 font-semibold text-xs">
                            <span>Nominal PPN ({{ $this->ppnPersen }}%)</span>
                            <span>+ Rp {{ number_format($this->ppnNominal, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <!-- Grand Total Banner -->
                    <div class="pt-2 border-t border-gray-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Grand Total</span>
                            <span class="text-xl font-black text-[#5E17EB]">
                                Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                            </span>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-1 rounded-md bg-purple-100 text-[#5E17EB]">
                            {{ count($cart) }} Item
                        </span>
                    </div>

                    <!-- Tombol Bayar Sekarang (Shortcut F4 with Loading Spinner) -->
                    <button type="button"
                            wire:click="openPaymentModal"
                            wire:loading.attr="disabled"
                            wire:target="openPaymentModal"
                            @if(count($cart) === 0) disabled @endif
                            class="w-full py-3 px-4 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-sm shadow-lg shadow-purple-900/30 transition-all flex items-center justify-between cursor-pointer">
                        <div class="flex items-center space-x-2">
                            <svg wire:loading.remove wire:target="openPaymentModal" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <svg wire:loading wire:target="openPaymentModal" class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="openPaymentModal">Bayar Sekarang</span>
                            <span wire:loading wire:target="openPaymentModal">Mempersiapkan Kasir...</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-mono text-xs font-black border border-white/25">F4</span>
                    </button>

                    <!-- Tombol Simpan ke Bon (Belum Lunas) -->
                    <button type="button"
                            wire:click="simpanSebagaiBon"
                            wire:loading.attr="disabled"
                            wire:target="simpanSebagaiBon"
                            @if(count($cart) === 0) disabled @endif
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-md shadow-amber-900/20 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                        <svg wire:loading.remove wire:target="simpanSebagaiBon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <svg wire:loading wire:target="simpanSebagaiBon" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span wire:loading.remove wire:target="simpanSebagaiBon">Simpan ke Bon (Belum Lunas)</span>
                        <span wire:loading wire:target="simpanSebagaiBon">Menyimpan Bon...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL SCANNER BARCODE (USB SCANNER & KAMERA)   -->
    <!-- ============================================== -->
    @if($modalScanOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             x-data="{
                scannerInstance: null,
                isCameraRunning: false,
                cameraError: null,
                lastScanCode: '',
                lastScanTime: 0,

                async initCameraScanner() {
                    this.cameraError = null;
                    if (!window.Html5Qrcode) {
                        this.cameraError = 'Library pemindai kamera tidak dimuat.';
                        return;
                    }
                    try {
                        if (!this.scannerInstance) {
                            this.scannerInstance = new Html5Qrcode('cameraBarcodeTarget');
                        }
                        const config = {
                            fps: 15,
                            qrbox: { width: 280, height: 160 },
                            aspectRatio: 1.333334
                        };
                        await this.scannerInstance.start(
                            { facingMode: 'environment' },
                            config,
                            (decodedText) => {
                                const now = Date.now();
                                if (decodedText === this.lastScanCode && (now - this.lastScanTime < 2000)) {
                                    return;
                                }
                                this.lastScanCode = decodedText;
                                this.lastScanTime = now;

                                // Audio Beep
                                try {
                                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                                    const osc = audioCtx.createOscillator();
                                    const gain = audioCtx.createGain();
                                    osc.connect(gain);
                                    gain.connect(audioCtx.destination);
                                    osc.frequency.value = 1000;
                                    gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                                    osc.start();
                                    osc.stop(audioCtx.currentTime + 0.12);
                                } catch(e) {}

                                $wire.processScanBarcode(decodedText);
                            },
                            (errorMessage) => {}
                        );
                        this.isCameraRunning = true;
                    } catch (err) {
                        console.error('Camera Scanner Error:', err);
                        this.cameraError = 'Gagal mengakses kamera: ' + (err.message || 'Izin kamera ditolak atau perangkat tidak mendukung.');
                        this.isCameraRunning = false;
                    }
                },

                async stopCameraScanner() {
                    if (this.scannerInstance && this.isCameraRunning) {
                        try {
                            await this.scannerInstance.stop();
                            this.scannerInstance.clear();
                        } catch(e) {
                            console.error('Error stopping camera:', e);
                        }
                        this.isCameraRunning = false;
                    }
                },

                async handleTabChange(tab) {
                    if (tab === 'camera') {
                        await this.$nextTick();
                        this.initCameraScanner();
                    } else {
                        await this.stopCameraScanner();
                        this.$nextTick(() => {
                            const el = document.getElementById('dedicatedUsbScanInput');
                            if (el) el.focus();
                        });
                    }
                }
             }"
             x-init="
                $watch('$wire.scanTab', (tab) => handleTabChange(tab));
                if ('{{ $scanTab }}' === 'camera') {
                    $nextTick(() => initCameraScanner());
                } else {
                    $nextTick(() => {
                        const el = document.getElementById('dedicatedUsbScanInput');
                        if (el) el.focus();
                    });
                }
             "
             @keydown.escape.window="stopCameraScanner(); $wire.closeScanModal();">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-950/80 backdrop-blur-xs transition-opacity" wire:click="closeScanModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Pemindai Barcode POS</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Pindai produk otomatis masuk keranjang</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="stopCameraScanner(); $wire.closeScanModal();"
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Tab Switcher: Scanner USB vs Kamera HP/Webcam -->
                    <div class="p-3 bg-gray-50 border-b border-gray-100 flex items-center justify-center space-x-2">
                        <button type="button"
                                wire:click="switchScanTab('scanner')"
                                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-2 cursor-pointer {{ $scanTab === 'scanner' ? 'bg-white text-[#5E17EB] shadow-xs border border-purple-200' : 'text-gray-500 hover:text-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            <span>Scanner USB / Laser</span>
                        </button>
                        <button type="button"
                                wire:click="switchScanTab('camera')"
                                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-2 cursor-pointer {{ $scanTab === 'camera' ? 'bg-white text-[#5E17EB] shadow-xs border border-purple-200' : 'text-gray-500 hover:text-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Kamera HP / Webcam</span>
                        </button>
                    </div>

                    <!-- Body Scanner Container -->
                    <div class="p-6">
                        @if($scanTab === 'scanner')
                            <div class="space-y-4 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center mx-auto border border-purple-100">
                                    <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Siap Menerima Scan</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Arahkan barcode scanner fisik ke barcode produk.</p>
                                </div>
                                <div class="relative">
                                    <input type="text"
                                           id="dedicatedUsbScanInput"
                                           wire:model="scanBarcodeQuery"
                                           wire:keydown.enter.prevent="submitScanBarcode"
                                           class="w-full text-center font-mono font-bold text-sm px-4 py-3 rounded-xl border border-purple-300 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] bg-purple-50/30"
                                           autofocus>
                                </div>
                                <p class="text-[11px] text-gray-400">Tekan Enter atau tembakkan scanner barcode untuk menambahkan.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                <div id="reader" class="rounded-2xl overflow-hidden border border-gray-200 bg-gray-900 min-h-[220px]"></div>
                                <template x-if="cameraError">
                                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-rose-700 text-xs font-bold" x-text="cameraError"></div>
                                </template>
                                <p class="text-xs text-gray-500 text-center">Arahkan kamera tepat ke barcode produk hingga terdengar bunyi beep.</p>
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end rounded-b-3xl border-t border-gray-100">
                        <button type="button"
                                @click="stopCameraScanner(); $wire.closeScanModal();"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold transition-all cursor-pointer">
                            Tutup Pemindai
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL PEMBAYARAN KASIR (CHECKOUT MODAL)       -->
    <!-- ============================================== -->
    @if($modalBayarOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity" wire:click="$set('modalBayarOpen', false)"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Selesaikan Pembayaran</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">{{ auth()->user()->isOwner() ? ('Outlet: ' . $cabang?->nama_cabang) : 'Soulmate Komputer' }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('modalBayarOpen', false)" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="p-5 bg-purple-50/50 border-b border-purple-100 text-center">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Grand Total</span>
                        <span class="text-3xl font-black text-[#5E17EB] mt-1 block">
                            Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                        </span>
                    </div>

                    @if(!$isReal)
                        <!-- Note Non-Real di Modal Pembayaran Kasir -->
                        <div class="mx-6 mt-4 p-3.5 bg-amber-50 border-2 border-amber-300 rounded-2xl flex items-start space-x-3 text-amber-900 shadow-xs">
                            <div class="w-7 h-7 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="text-xs">
                                <span class="font-black text-amber-950 uppercase tracking-wide block">Pemberitahuan Transaksi Non-Real:</span>
                                <p class="font-bold text-amber-800 mt-0.5 leading-snug">
                                    Transaksi ini <span class="underline font-black text-amber-950">TIDAK AKAN MASUK KE PERHITUNGAN OMZET</span> dan <span class="underline font-black text-amber-950">TIDAK AKAN MERUBAH / MEMOTONG NILAI STOK</span> fisik toko.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Body Modal -->
                    <form wire:submit.prevent="prosesPembayaran"
                          x-data="{
                              raw: @entangle('jumlahBayar').live,
                              gt: @entangle('grandTotal').live,
                              get grandTotal() {
                                  return Math.round(Number(this.gt || {{ (float) $this->grandTotal }} || 0));
                              },
                              get bayar() {
                                  return Math.round(Number(this.raw || 0));
                              },
                              get kembalian() {
                                  let pay = this.bayar;
                                  let gt = this.grandTotal;
                                  return pay > gt ? (pay - gt) : 0;
                              },
                              get kekurangan() {
                                  let pay = this.bayar;
                                  let gt = this.grandTotal;
                                  return pay > 0 && pay < gt ? (gt - pay) : 0;
                              },
                              get isKurang() {
                                  let pay = this.bayar;
                                  let gt = this.grandTotal;
                                  return pay > 0 && pay < gt;
                              },
                              get isUangPas() {
                                  let pay = this.bayar;
                                  let gt = this.grandTotal;
                                  return (pay > 0 && pay === gt) || (pay === 0 && gt === 0);
                              },
                              formatRupiah(val) {
                                  if (val === null || val === undefined || val === '' || isNaN(val)) return '0';
                                  let num = Math.round(Number(val));
                                  return isNaN(num) ? '0' : num.toLocaleString('id-ID');
                              },
                              formatInput(e) {
                                  let clean = e.target.value.toString().replace(/[^0-9]/g, '');
                                  let num = clean ? parseInt(clean, 10) : 0;
                                  this.raw = num;
                                  e.target.value = num ? num.toLocaleString('id-ID') : '';
                              },
                              setUangPas() {
                                  this.raw = this.grandTotal;
                              },
                              setQuickNominal(val) {
                                  this.raw = Math.round(Number(val));
                              },
                              init() {
                                  this.$watch('gt', (newGt) => {
                                      let gtNum = Math.round(Number(newGt || 0));
                                      let rawNum = Math.round(Number(this.raw || 0));
                                      if (rawNum <= 0 || rawNum < gtNum) {
                                          this.raw = gtNum;
                                      }
                                  });
                              }
                          }">
                        <div class="p-6 space-y-4">

                            <!-- Penyesuaian Diskon Transaksi & PPN Langsung di Modal -->
                            <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-200 space-y-2.5 text-xs">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Subtotal Belanja</span>
                                    <span class="font-extrabold text-gray-900">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                                </div>

                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="font-medium">Diskon Transaksi</span>
                                    <div class="w-32">
                                        <x-input-rupiah wire:model.live.debounce.250ms="diskon" inputClass="!py-1 !px-2 !text-right !text-xs !font-extrabold !border-gray-300" />
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="font-medium">Opsi Pajak (PPN)</span>
                                    <select wire:model.live="selectedPpnId"
                                            class="text-xs font-bold py-1 px-2 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-[#5E17EB]">
                                        @foreach($masterPpns as $ppn)
                                            <option value="{{ $ppn->id }}">{{ $ppn->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($this->ppnNominal > 0)
                                    <div class="flex items-center justify-between text-purple-700 font-semibold pt-1 border-t border-gray-200">
                                        <span>Nominal PPN ({{ $this->ppnPersen }}%)</span>
                                        <span>+ Rp {{ number_format($this->ppnNominal, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Pilihan Metode Pembayaran Dinamis -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Metode Pembayaran
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach($masterMetodePembayarans as $metode)
                                        <button type="button"
                                                wire:click="selectMetodeBayar('{{ $metode->nama }}')"
                                                class="py-2.5 px-2 rounded-xl border text-xs font-bold transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer text-center {{ $metodeBayar === $metode->nama ? 'bg-[#5E17EB] text-white border-[#5E17EB] shadow-md shadow-purple-900/20 ring-2 ring-purple-300' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                                            <span class="truncate w-full">{{ $metode->nama }}</span>
                                            @if($metode->nomor_rekening)
                                                <span class="text-[9px] font-mono {{ $metodeBayar === $metode->nama ? 'text-purple-200' : 'text-gray-400' }}">{{ $metode->nomor_rekening }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Input Uang Diterima & Quick Money Stepper -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Nominal Uang Diterima
                                    </label>
                                    <button type="button"
                                            @click="setUangPas()"
                                            class="text-[11px] font-bold text-[#5E17EB] hover:underline cursor-pointer">
                                        Pakai Uang Pas
                                    </button>
                                </div>

                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-gray-400 select-none pointer-events-none">Rp</span>
                                    <input type="text"
                                           inputmode="numeric"
                                           :value="raw ? Math.round(Number(raw)).toLocaleString('id-ID') : ''"
                                           @input="formatInput($event)"
                                           class="w-full pl-11 pr-4 py-3 text-lg font-black text-gray-900 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] bg-white">
                                </div>
                                @error('jumlahBayar') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror

                                <!-- Quick Money Pills -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <button type="button" @click="setUangPas()" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">Uang Pas</button>
                                    <button type="button" @click="setQuickNominal(50000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">50.000</button>
                                    <button type="button" @click="setQuickNominal(100000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">100.000</button>
                                    <button type="button" @click="setQuickNominal(200000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">200.000</button>
                                    <button type="button" @click="setQuickNominal(500000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">500.000</button>
                                </div>
                            </div>

                            <!-- Perhitungan Kembalian Kasir (Live Calculation Card) -->
                            <div class="p-4 rounded-2xl border transition-all"
                                 :class="kembalian > 0 ? 'bg-emerald-50/90 border-emerald-200 shadow-2xs' : (isKurang ? 'bg-rose-50 border-rose-200' : 'bg-gray-50 border-gray-200')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <!-- Single Icon Element (No duplication with template x-if) -->
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs transition-colors"
                                             :class="kembalian > 0 ? 'bg-emerald-500 text-white' : (isKurang ? 'bg-rose-500 text-white' : 'bg-gray-200 text-gray-600')">
                                            <svg x-show="kembalian > 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <svg x-show="isKurang" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            <svg x-show="!kembalian && !isKurang" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </div>

                                        <div>
                                            <span class="text-[10px] font-black uppercase tracking-wider block"
                                                  :class="kembalian > 0 ? 'text-emerald-800' : (isKurang ? 'text-rose-800' : 'text-gray-500')"
                                                  x-text="kembalian > 0 ? 'Uang Kembalian' : (isKurang ? 'Kekurangan Uang' : 'Uang Kembalian')">
                                            </span>
                                            <span class="text-[11px] font-bold"
                                                  :class="kembalian > 0 ? 'text-emerald-700' : (isKurang ? 'text-rose-600' : 'text-gray-400')"
                                                  x-text="kembalian > 0 ? 'Wajib diberikan ke pelanggan' : (isKurang ? 'Uang diterima masih kurang' : (isUangPas ? 'Uang pas' : 'Uang pas / belum diisi'))">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-black font-mono block"
                                              :class="kembalian > 0 ? 'text-emerald-600' : (isKurang ? 'text-rose-600' : 'text-gray-700')"
                                              x-text="kembalian > 0 ? ('Rp ' + formatRupiah(kembalian)) : (isKurang ? ('- Rp ' + formatRupiah(kekurangan)) : 'Rp 0')">
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Catatan Transaksi -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Catatan / Keterangan Transaksi
                                </label>
                                <input type="text"
                                       wire:model="catatan"
                                       class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB]">
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl">
                            <button type="button"
                                    wire:click="$set('modalBayarOpen', false)"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="prosesPembayaran"
                                    class="px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-xs font-extrabold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer flex items-center space-x-2">
                                <svg wire:loading wire:target="prosesPembayaran" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="prosesPembayaran">Konfirmasi & Selesaikan Transaksi</span>
                                <span wire:loading wire:target="prosesPembayaran">Memproses Transaksi...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL TAMBAH PELANGGAN BARU                   -->
    <!-- ============================================== -->
    @if($modalNewCustomerOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity" wire:click="$set('modalNewCustomerOpen', false)"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug">Tambah Pelanggan Baru</h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Registrasi pelanggan cepat saat transaksi kasir</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('modalNewCustomerOpen', false)" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveNewCustomer">
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Lengkap Pelanggan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       wire:model="newCustomerNama"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] @error('newCustomerNama') border-rose-500 @enderror">
                                @error('newCustomerNama') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    WhatsApp
                                </label>
                                <input type="text"
                                       wire:model="newCustomerHp"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB]">
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl">
                            <button type="button"
                                    wire:click="$set('modalNewCustomerOpen', false)"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="saveNewCustomer"
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-xs font-extrabold text-white shadow-md shadow-purple-900/20 transition-all cursor-pointer flex items-center space-x-2">
                                <svg wire:loading wire:target="saveNewCustomer" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span wire:loading.remove wire:target="saveNewCustomer">Simpan &amp; Pilih</span>
                                <span wire:loading wire:target="saveNewCustomer">Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

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
                    <span>Outlet: <strong class="text-[#0052FF] font-bold">{{ $cabang?->nama_cabang ?? 'Pilih Cabang' }}</strong></span>
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
                           placeholder="Cari nama produk di katalog (bebas huruf besar/kecil)..."
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

            <!-- Grid Produk POS (Infinite Scroll & Lazy Loading) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @forelse($produks as $p)
                    @php
                        $stokCabang = $p->stoks->first()?->jumlah ?? 0;
                        $isZero = $stokCabang <= 0;
                        $isCrit = !$isZero && $stokCabang <= 3;
                    @endphp
                    <div wire:key="pos-prod-{{ $p->id }}"
                         @if(!$isZero) wire:click="addToCart({{ $p->id }})" @endif
                         class="bg-white rounded-2xl border transition-all p-3 flex flex-col justify-between relative group select-none {{ $isZero ? 'border-gray-200 bg-gray-50/60 opacity-60 cursor-not-allowed' : 'border-gray-100 hover:border-[#5E17EB]/40 hover:shadow-lg hover:shadow-purple-900/10 cursor-pointer active:scale-98' }}">

                        <div>
                            <!-- Thumbnail & Floating Stock Indicator (Lazy Loaded) -->
                            <div class="relative w-full aspect-square rounded-xl bg-gray-50 overflow-hidden mb-2.5 flex items-center justify-center border border-gray-100">
                                @if($p->foto_url && file_exists(public_path($p->foto_url)))
                                    <img src="{{ asset($p->foto_url) }}"
                                         alt="{{ $p->nama_produk }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @elseif($p->foto_url && (str_starts_with($p->foto_url, 'http') || str_starts_with($p->foto_url, 'images/')))
                                    <img src="{{ asset($p->foto_url) }}"
                                         alt="{{ $p->nama_produk }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         onerror="this.onerror=null;this.src='{{ asset('images/logo_bulat.jpg') }}';">
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
                            @if(!$isZero)
                                <button type="button"
                                        wire:click.stop="addToCart({{ $p->id }})"
                                        class="w-7 h-7 rounded-lg bg-purple-50 hover:bg-[#5E17EB] text-[#5E17EB] hover:text-white transition-all flex items-center justify-center cursor-pointer shadow-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
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

            <!-- Infinite Scroll on Scroll & Lazy Loading Sentinel -->
            @if($hasMore)
                <div x-data="{
                        init() {
                            const observer = new IntersectionObserver((entries) => {
                                entries.forEach(entry => {
                                    if (entry.isIntersecting) {
                                        $wire.loadMore();
                                    }
                                });
                            }, {
                                rootMargin: '250px'
                            });
                            observer.observe(this.$el);
                        }
                     }"
                     class="p-4 bg-white rounded-2xl border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                    
                    <div class="flex items-center space-x-2 text-xs text-gray-500 font-medium">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#5E17EB] animate-ping"></span>
                        <span>Menampilkan <strong>{{ $produks->count() }}</strong> dari <strong>{{ $totalProduk }}</strong> produk</span>
                    </div>

                    <!-- Loading Indicator Spinner saat Scroll -->
                    <div wire:loading wire:target="loadMore" class="flex items-center space-x-2 text-xs font-bold text-[#5E17EB]">
                        <svg class="animate-spin w-4 h-4 text-[#5E17EB]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Memuat batch produk berikutnya...</span>
                    </div>

                    <!-- Fallback Manual Trigger -->
                    <div wire:loading.remove wire:target="loadMore">
                        <button type="button" 
                                wire:click="loadMore" 
                                class="px-4 py-2 bg-purple-50 text-[#5E17EB] hover:bg-[#5E17EB] hover:text-white font-bold rounded-xl text-xs transition-colors cursor-pointer inline-flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            <span>Scroll untuk Muat Otomatis</span>
                        </button>
                    </div>
                </div>
            @else
                @if($totalProduk > 0)
                    <div class="p-3.5 text-center bg-gray-50/60 rounded-2xl border border-gray-100 text-xs text-gray-400 font-medium">
                        ✓ Seluruh <strong>{{ $totalProduk }}</strong> produk dalam katalog telah dimuat.
                    </div>
                @endif
            @endif
        </div>

        <!-- ============================================== -->
        <!-- KOLOM KANAN: KERANJANG BELANJA & CHECKOUT (5/12)-->
        <!-- ============================================== -->
        <div class="lg:col-span-5 xl:col-span-4 space-y-4 sticky top-4">

            <!-- Customer Selector Widget -->
            <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 shadow-xs space-y-3">
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
                                    @if($selectedCustomer->foto_url && file_exists(public_path($selectedCustomer->foto_url)))
                                        <img src="{{ asset($selectedCustomer->foto_url) }}" alt="{{ $selectedCustomer->nama }}" class="w-8 h-8 rounded-full object-cover border border-purple-200 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-purple-200 text-[#5E17EB] font-black flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($selectedCustomer->nama, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="truncate">
                                        <h4 class="text-xs font-extrabold text-[#5E17EB] truncate">{{ $selectedCustomer->nama }}</h4>
                                        <p class="text-[11px] text-gray-500">{{ $selectedCustomer->no_hp ?: 'Tanpa No HP' }}</p>
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
                                       placeholder="Cari nama / nomor HP langganan..."
                                       class="w-full text-xs font-medium pl-8 pr-20 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-1 focus:ring-[#5E17EB]">
                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <button type="button"
                                        wire:click="openNewCustomerModal"
                                        class="absolute right-1.5 top-1.5 px-2 py-1 bg-[#5E17EB] text-white text-[10px] font-bold rounded-lg hover:bg-purple-700 cursor-pointer">
                                    + Baru
                                </button>
                            </div>

                            @if($customers->count() > 0)
                                <div class="bg-white rounded-xl border border-gray-200 shadow-md divide-y divide-gray-100 max-h-36 overflow-y-auto">
                                    @foreach($customers as $c)
                                        <button type="button"
                                                wire:click="selectCustomer({{ $c->id }})"
                                                class="w-full px-3 py-2 text-left hover:bg-purple-50 transition-colors flex items-center justify-between cursor-pointer">
                                            <div class="flex items-center space-x-2 truncate">
                                                @if($c->foto_url && file_exists(public_path($c->foto_url)))
                                                    <img src="{{ asset($c->foto_url) }}" alt="{{ $c->nama }}" class="w-6 h-6 rounded-full object-cover shrink-0 border border-gray-200">
                                                @else
                                                    <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 font-bold flex items-center justify-center text-[10px] shrink-0">
                                                        {{ strtoupper(substr($c->nama, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span class="text-xs font-bold text-gray-800 truncate">{{ $c->nama }}</span>
                                            </div>
                                            <span class="text-[10px] text-gray-400 shrink-0">{{ $c->no_hp }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            <!-- Cart Items Container -->
            <div class="bg-white card-custom rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
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

                <!-- List Items Scrollable -->
                <div class="divide-y divide-gray-100 max-h-[340px] overflow-y-auto custom-scrollbar p-2">
                    @forelse($cart as $item)
                        <div class="p-2.5 flex items-center justify-between gap-2 rounded-xl hover:bg-gray-50/80 transition-colors">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-extrabold text-gray-900 truncate">{{ $item['nama'] }}</h4>
                                <div class="flex items-center space-x-2 text-[11px] text-gray-500 mt-0.5">
                                    <span>Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                                    <span>Ã—</span>
                                    <span class="font-extrabold text-gray-800">{{ $item['qty'] }}</span>
                                    <span>=</span>
                                    <strong class="text-[#0052FF] font-black">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            <!-- Stepper Qty -->
                            <div class="flex items-center space-x-1 shrink-0">
                                <button type="button"
                                        wire:click="adjustQty({{ $item['id'] }}, -1)"
                                        class="w-6 h-6 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-xs flex items-center justify-center transition-colors cursor-pointer">
                                    -
                                </button>
                                <input type="number"
                                       value="{{ $item['qty'] }}"
                                       wire:change="updateQty({{ $item['id'] }}, $event.target.value)"
                                       min="1"
                                       max="{{ $item['stok_max'] }}"
                                       class="w-10 text-center text-xs font-black py-1 border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#5E17EB]">
                                <button type="button"
                                        wire:click="adjustQty({{ $item['id'] }}, 1)"
                                        class="w-6 h-6 rounded-lg bg-purple-50 hover:bg-purple-100 text-[#5E17EB] font-black text-xs flex items-center justify-center transition-colors cursor-pointer">
                                    +
                                </button>
                            </div>

                            <!-- Remove Button -->
                            <button type="button"
                                    wire:click="removeFromCart({{ $item['id'] }})"
                                    class="text-gray-300 hover:text-rose-600 transition-colors p-1 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="text-xs font-bold text-gray-600">Keranjang Masih Kosong</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Pilih produk di katalog atau scan barcode untuk transaksi.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Financial Calculation & PPN Section -->
                <div class="p-4 bg-gray-50 border-t border-gray-100 space-y-2.5">

                    <!-- Subtotal -->
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Subtotal Belanja</span>
                        <span class="font-extrabold text-gray-900">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <!-- Diskon Input -->
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Diskon Transaksi</span>
                        <div class="flex items-center space-x-1">
                            <span class="text-gray-400 text-[11px]">Rp</span>
                            <input type="number"
                                   wire:model.live.debounce.250ms="diskon"
                                   min="0"
                                   placeholder="0"
                                   class="w-24 text-right text-xs font-extrabold py-1 px-2 border border-gray-200 rounded-lg bg-white focus:ring-1 focus:ring-[#5E17EB]">
                        </div>
                    </div>

                    <!-- PPN Selector -->
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Opsi Pajak (PPN)</span>
                        <select wire:model.live="selectedPpnId"
                                class="text-xs font-bold py-1 px-2.5 border border-gray-200 rounded-lg bg-white focus:ring-1 focus:ring-[#5E17EB]">
                            @foreach($masterPpns as $ppn)
                                <option value="{{ $ppn->id }}">{{ $ppn->nama }} ({{ $ppn->persen }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    @if($this->ppnNominal > 0)
                        <div class="flex items-center justify-between text-purple-700 font-semibold">
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

                    <!-- Tombol Bayar Sekarang (Shortcut F4) -->
                    <button type="button"
                            wire:click="openPaymentModal"
                            @if(count($cart) === 0) disabled @endif
                            class="w-full py-3.5 px-4 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-extrabold text-sm shadow-lg shadow-purple-900/30 transition-all flex items-center justify-between cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span>Bayar Sekarang</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-mono text-xs font-black border border-white/25">F4</span>
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
                    <div class="bg-[#0E1726] px-6 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">Pemindai Barcode POS</h3>
                                <p class="text-xs text-purple-200">Pindai produk otomatis masuk keranjang</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="stopCameraScanner(); $wire.closeScanModal();"
                                class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                            <span>Scanner Kamera</span>
                        </button>
                    </div>

                    <!-- Scanner Body -->
                    <div class="p-6 space-y-4">

                        @if($scanTab === 'scanner')
                            <!-- TAB 1: MODE SCANNER USB / LASER -->
                            <div class="text-center space-y-3">
                                <div class="w-14 h-14 rounded-2xl bg-purple-50 text-[#5E17EB] flex items-center justify-center mx-auto border border-purple-100">
                                    <svg class="w-7 h-7 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </div>
                                <p class="text-xs text-gray-500 font-medium">Tembakkan barcode dengan scanner fisik atau ketik nomor barcode.</p>

                                <form wire:submit.prevent="processScanBarcode" class="space-y-3">
                                    <div class="relative">
                                        <input type="text"
                                               id="dedicatedUsbScanInput"
                                               wire:model="scanBarcode"
                                               placeholder="Arahkan scanner ke sini..."
                                               autofocus
                                               autocomplete="off"
                                               class="w-full text-center text-base font-mono font-black px-4 py-3.5 rounded-2xl border-2 border-[#5E17EB] bg-purple-50/40 text-gray-900 focus:ring-4 focus:ring-purple-200 focus:outline-hidden transition-all shadow-inner">
                                    </div>

                                    <button type="submit"
                                            class="w-full py-3 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold shadow-md transition-all cursor-pointer">
                                        Masukkan ke Keranjang (Enter)
                                    </button>
                                </form>
                            </div>
                        @else
                            <!-- TAB 2: MODE KAMERA (HP / WEBCAM) -->
                            <div class="space-y-3">
                                <div class="relative rounded-2xl overflow-hidden bg-black aspect-4/3 flex items-center justify-center border border-gray-200 shadow-inner">
                                    <div id="cameraBarcodeTarget" class="w-full h-full"></div>

                                    <!-- Fallback message if error -->
                                    <template x-if="cameraError">
                                        <div class="absolute inset-0 bg-gray-900/90 p-4 flex flex-col items-center justify-center text-center text-white">
                                            <svg class="w-10 h-10 text-rose-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            <p class="text-xs font-bold" x-text="cameraError"></p>
                                            <button type="button" @click="initCameraScanner()" class="mt-3 px-3 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 cursor-pointer">
                                                Coba Lagi
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-[11px] text-center text-gray-500 font-medium">Arahkan barcode produk ke dalam kotak bidik kamera.</p>
                            </div>
                        @endif

                        @if($lastScannedName)
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-center text-xs font-bold text-emerald-800 flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span class="truncate">Item Masuk: <strong>{{ $lastScannedName }}</strong></span>
                            </div>
                        @endif

                        <!-- Ringkasan Keranjang Singkat -->
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span>Total di Keranjang: <strong class="text-[#5E17EB] font-black">{{ count($cart) }} Item</strong></span>
                            <span>Grand Total: <strong class="text-gray-900 font-black">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</strong></span>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-3xl">
                        <span class="text-[11px] text-gray-400">Tekan <strong>Esc</strong> untuk menutup</span>
                        <button type="button"
                                @click="stopCameraScanner(); $wire.closeScanModal();"
                                class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-xs font-bold text-white transition-colors cursor-pointer">
                            Selesai Scan
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
                    <div class="bg-[#0E1726] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white">Selesaikan Pembayaran</h3>
                                <p class="text-xs text-blue-200">Outlet: {{ $cabang?->nama_cabang }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('modalBayarOpen', false)" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="p-5 bg-purple-50/50 border-b border-purple-100 text-center">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Grand Total</span>
                        <span class="text-3xl font-black text-[#5E17EB] mt-1 block">
                            Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Body Modal -->
                    <form wire:submit.prevent="prosesPembayaran">
                        <div class="p-6 space-y-4">

                            <!-- Penyesuaian Diskon Transaksi & PPN Langsung di Modal -->
                            <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-200 space-y-2.5 text-xs">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Subtotal Belanja</span>
                                    <span class="font-extrabold text-gray-900">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                                </div>

                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="font-medium">Diskon Transaksi</span>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-gray-400 font-bold">Rp</span>
                                        <input type="number"
                                               wire:model.live.debounce.250ms="diskon"
                                               min="0"
                                               placeholder="0"
                                               class="w-28 text-right font-extrabold py-1 px-2 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-[#5E17EB]">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="font-medium">Opsi Pajak (PPN)</span>
                                    <select wire:model.live="selectedPpnId"
                                            class="text-xs font-bold py-1 px-2 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-[#5E17EB]">
                                        @foreach($masterPpns as $ppn)
                                            <option value="{{ $ppn->id }}">{{ $ppn->nama }} ({{ $ppn->persen }}%)</option>
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

                            <!-- Tab Metode Bayar -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Metode Pembayaran <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button"
                                            wire:click="$set('metodeBayar', 'cash')"
                                            class="p-3 rounded-2xl border text-center transition-all cursor-pointer {{ $metodeBayar === 'cash' ? 'border-[#5E17EB] bg-purple-50/80 text-[#5E17EB] font-black shadow-xs ring-2 ring-purple-200' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-bold' }}">
                                        <svg class="w-5 h-5 mx-auto mb-1 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span class="text-xs block">Tunai (Cash)</span>
                                    </button>

                                    <button type="button"
                                            wire:click="$set('metodeBayar', 'qris')"
                                            class="p-3 rounded-2xl border text-center transition-all cursor-pointer {{ $metodeBayar === 'qris' ? 'border-[#5E17EB] bg-purple-50/80 text-[#5E17EB] font-black shadow-xs ring-2 ring-purple-200' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-bold' }}">
                                        <svg class="w-5 h-5 mx-auto mb-1 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                        <span class="text-xs block">QRIS</span>
                                    </button>

                                    <button type="button"
                                            wire:click="$set('metodeBayar', 'transfer')"
                                            class="p-3 rounded-2xl border text-center transition-all cursor-pointer {{ $metodeBayar === 'transfer' ? 'border-[#5E17EB] bg-purple-50/80 text-[#5E17EB] font-black shadow-xs ring-2 ring-purple-200' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-bold' }}">
                                        <svg class="w-5 h-5 mx-auto mb-1 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                                        <span class="text-xs block">Transfer Bank</span>
                                    </button>
                                </div>
                            </div>

                            @if($metodeBayar === 'cash')
                                <!-- Input Uang Diterima & Quick Money Stepper -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Nominal Uang Diterima <span class="text-rose-500">*</span>
                                        </label>
                                        <button type="button"
                                                wire:click="setUangPas"
                                                class="text-[11px] font-bold text-[#5E17EB] hover:underline cursor-pointer">
                                            Pakai Uang Pas
                                        </button>
                                    </div>

                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-gray-400">Rp</span>
                                        <input type="number"
                                               wire:model.live="jumlahBayar"
                                               min="0"
                                               class="w-full pl-11 pr-4 py-3 text-lg font-black text-gray-900 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB]">
                                    </div>
                                    @error('jumlahBayar') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror

                                    <!-- Quick Money Pills -->
                                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                        <button type="button" wire:click="setUangPas" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">Uang Pas</button>
                                        <button type="button" wire:click="setQuickNominal(50000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">50.000</button>
                                        <button type="button" wire:click="setQuickNominal(100000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">100.000</button>
                                        <button type="button" wire:click="setQuickNominal(200000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">200.000</button>
                                        <button type="button" wire:click="setQuickNominal(500000)" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs cursor-pointer">500.000</button>
                                    </div>
                                </div>

                                <!-- Box Kembalian -->
                                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Kembalian</span>
                                        <span class="text-xl font-black {{ $this->kembalian >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                            Rp {{ number_format($this->kembalian, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @if($this->kembalian > 0)
                                        <span class="px-2 py-1 rounded-md bg-emerald-100 text-emerald-800 font-black text-[10px]">Ada Kembalian</span>
                                    @elseif($jumlahBayar == $this->grandTotal)
                                        <span class="px-2 py-1 rounded-md bg-blue-100 text-blue-800 font-black text-[10px]">Uang Pas</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Catatan Transaksi -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Catatan / Keterangan Transaksi
                                </label>
                                <input type="text"
                                       wire:model="catatan"
                                       placeholder="Contoh: Titip nota nama PT / Catatan garansi"
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
                                    class="px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-extrabold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
                                Konfirmasi & Selesaikan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL SUKSES & CETAK STRUK THERMAL             -->
    <!-- ============================================== -->
    @if($modalSuccessOpen && $lastPenjualan)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-950/80 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <!-- Header Sukses -->
                    <div class="p-6 text-center bg-emerald-50 border-b border-gray-100">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3 shadow-md shadow-emerald-600/15">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Transaksi Berhasil!</h3>
                        <p class="text-xs font-mono font-bold text-[#5E17EB] mt-0.5">{{ $lastPenjualan->no_transaksi }}</p>
                    </div>

                    <!-- Struk Preview (Thermal Simulator 58mm / 80mm) -->
                    <div class="p-6 space-y-3 bg-gray-50/70">
                        <div id="thermalReceiptArea" class="bg-white p-4 rounded-xl border border-gray-200 text-[11px] font-mono shadow-xs space-y-2 text-gray-800">
                            <!-- Store Header -->
                            <div class="text-center pb-2 border-b border-dashed border-gray-300">
                                <h4 class="font-black text-xs text-gray-900 uppercase">SOULMATE KOMPUTER</h4>
                                <p class="text-[10px] text-gray-500">{{ $lastPenjualan->cabang?->nama_cabang }}</p>
                                <p class="text-[9px] text-gray-400">{{ $lastPenjualan->cabang?->alamat }}</p>
                                <p class="text-[9px] text-gray-400">WA: {{ $lastPenjualan->cabang?->no_wa ?? $lastPenjualan->cabang?->no_telp }}</p>
                            </div>

                            <!-- Transaction Meta -->
                            <div class="text-[10px] space-y-0.5 pb-2 border-b border-dashed border-gray-300">
                                <div class="flex justify-between"><span>No:</span><span class="font-bold">{{ $lastPenjualan->no_transaksi }}</span></div>
                                <div class="flex justify-between"><span>Tgl:</span><span>{{ $lastPenjualan->tanggal->format('d/m/Y H:i') }}</span></div>
                                <div class="flex justify-between"><span>Kasir:</span><span>{{ $lastPenjualan->kasir?->name }}</span></div>
                                <div class="flex justify-between"><span>Customer:</span><span>{{ $lastPenjualan->customer?->nama ?? 'Pelanggan Umum' }}</span></div>
                            </div>

                            <!-- Item List -->
                            <div class="space-y-1.5 py-1 border-b border-dashed border-gray-300">
                                @foreach($lastPenjualan->details as $d)
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $d->nama_item }}</p>
                                        <div class="flex justify-between text-gray-600 text-[10px]">
                                            <span>{{ $d->qty }} x {{ number_format($d->harga, 0, ',', '.') }}</span>
                                            <span class="font-bold text-gray-900">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Totals -->
                            <div class="space-y-0.5 pt-1 text-[10px]">
                                <div class="flex justify-between"><span>Subtotal:</span><span>Rp {{ number_format($lastPenjualan->subtotal, 0, ',', '.') }}</span></div>
                                @if($lastPenjualan->diskon > 0)
                                    <div class="flex justify-between text-rose-600"><span>Diskon:</span><span>- Rp {{ number_format($lastPenjualan->diskon, 0, ',', '.') }}</span></div>
                                @endif
                                @if($lastPenjualan->ppn_nominal > 0)
                                    <div class="flex justify-between text-purple-700"><span>PPN ({{ $lastPenjualan->ppn_persen }}%):</span><span>Rp {{ number_format($lastPenjualan->ppn_nominal, 0, ',', '.') }}</span></div>
                                @endif
                                <div class="flex justify-between font-black text-xs text-gray-900 pt-1 border-t border-gray-200">
                                    <span>GRAND TOTAL:</span>
                                    <span>Rp {{ number_format($lastPenjualan->total, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between"><span>Bayar ({{ strtoupper($lastPenjualan->metode_bayar) }}):</span><span>Rp {{ number_format($lastPenjualan->jumlah_bayar, 0, ',', '.') }}</span></div>
                            </div>

                            <!-- Footer Struk -->
                            <div class="text-center pt-2 border-t border-dashed border-gray-300 text-[9px] text-gray-400">
                                <p class="font-bold text-gray-600">Terima Kasih Atas Kunjungan Anda!</p>
                                <p>Barang yang sudah dibeli dapat ditukar jika rusak (bawa struk ini).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="p-6 bg-white border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <button type="button"
                                onclick="window.print()"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white text-xs font-bold transition-all shadow-md shadow-purple-900/20 inline-flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Cetak Struk Nota</span>
                        </button>

                        <button type="button"
                                wire:click="newTransaction"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition-all cursor-pointer">
                            Transaksi Baru
                        </button>
                    </div>

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

                    <div class="bg-[#0E1726] px-6 py-5 text-white flex items-center justify-between">
                        <h3 class="text-base font-extrabold text-white">Tambah Pelanggan Baru</h3>
                        <button type="button" wire:click="$set('modalNewCustomerOpen', false)" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveNewCustomer">
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Pelanggan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       wire:model="newCustomerNama"
                                       placeholder="Contoh: Budi Santoso"
                                       class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] @error('newCustomerNama') border-rose-500 @enderror">
                                @error('newCustomerNama') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nomor HP / WhatsApp
                                </label>
                                <input type="text"
                                       wire:model="newCustomerHp"
                                       placeholder="0812xxxxxxxx"
                                       class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB]">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Alamat Singkat
                                </label>
                                <input type="text"
                                       wire:model="newCustomerAlamat"
                                       placeholder="Contoh: Jl. Sudirman No. 12"
                                       class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB]">
                            </div>

                            <!-- Upload Foto Langganan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Foto Langganan (Opsional)
                                </label>
                                <div class="flex items-center space-x-3">
                                    @if($newCustomerFoto)
                                        <img src="{{ $newCustomerFoto->temporaryUrl() }}" class="w-12 h-12 rounded-xl object-cover border border-purple-200 shadow-xs">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <input type="file"
                                               wire:model="newCustomerFoto"
                                               accept="image/*"
                                               class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-[#5E17EB] hover:file:bg-purple-100 cursor-pointer">
                                        @error('newCustomerFoto') <span class="text-[11px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl">
                            <button type="button"
                                    wire:click="$set('modalNewCustomerOpen', false)"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-extrabold text-white shadow-md shadow-purple-900/20 transition-all cursor-pointer">
                                Simpan & Pilih
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

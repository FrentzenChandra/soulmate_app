<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 relative"
     x-data="{ showCart: @entangle('cartOpen').live }"
     @open-cart-modal.window="showCart = true">



    <!-- FLOATING TOAST NOTIFICATION -->
    @if ($toastMessage)
        <div class="fixed top-24 right-4 z-50 max-w-sm w-full bg-white rounded-2xl shadow-2xl border-2 border-[#5E17EB]/20 p-4 flex items-start gap-3 transition-all animate-bounce">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-xs font-bold text-[#0c1330] leading-snug line-clamp-2">{{ $toastMessage }}</p>
                <div class="mt-2 flex items-center gap-3">
                    <button type="button" wire:click="toggleCartModal" class="text-xs font-black text-[#5E17EB] hover:underline">
                        Lihat Keranjang &rarr;
                    </button>
                    <button type="button" wire:click="dismissToast" class="text-xs font-medium text-gray-400 hover:text-gray-600">
                        Tutup
                    </button>
                </div>
            </div>
            <button type="button" wire:click="dismissToast" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT SIDEBAR: FILTERS (Order-2 on mobile, Order-1 on desktop) -->
        <aside class="order-2 lg:order-1 lg:col-span-3 space-y-6">
            
            <!-- 1. Kategori Card (With Accurate Distinct SVG Icons) -->
            <div class="bg-white rounded-3xl border border-gray-200/90 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h3 class="font-black text-sm text-[#0c1330] uppercase tracking-wider">
                        Kategori
                    </h3>
                    @if($kategori !== 'semua')
                    <button wire:click="setKategori('semua')" type="button" class="text-[11px] font-bold text-[#5E17EB] hover:underline">
                        Reset
                    </button>
                    @endif
                </div>
                <div class="space-y-1 max-h-[500px] overflow-y-auto pr-1">
                    @foreach ($categories as $cat)
                        <button wire:click="setKategori('{{ $cat['slug'] }}')"
                                type="button"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all text-left {{ $kategori === $cat['slug'] ? 'bg-purple-50 text-[#301ac4] font-extrabold shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center gap-3">
                                @if($cat['slug'] === 'semua')
                                    <!-- Grid Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                @elseif($cat['slug'] === 'laptop')
                                    <!-- Laptop Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="12" rx="2" stroke-width="2"/>
                                        <line x1="2" y1="20" x2="22" y2="20" stroke-width="2"/>
                                    </svg>
                                @elseif($cat['slug'] === 'komputer')
                                    <!-- Desktop PC Icon (Tower + Screen) -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="2" y="3" width="5.5" height="18" rx="1" stroke-width="2"/>
                                        <rect x="9.5" y="3" width="12.5" height="12" rx="1.5" stroke-width="2"/>
                                        <line x1="15.75" y1="15" x2="15.75" y2="19" stroke-width="2"/>
                                        <line x1="12" y1="19" x2="19.5" y2="19" stroke-width="2"/>
                                    </svg>
                                @elseif($cat['slug'] === 'monitor')
                                    <!-- Monitor Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2"/>
                                        <line x1="8" y1="21" x2="16" y2="21" stroke-width="2"/>
                                        <line x1="12" y1="17" x2="12" y2="21" stroke-width="2"/>
                                    </svg>
                                @elseif($cat['slug'] === 'printer')
                                    <!-- Printer Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <polyline points="6 9 6 2 18 2 18 9" stroke-width="2"/>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" stroke-width="2"/>
                                        <rect x="6" y="14" width="12" height="8" stroke-width="2"/>
                                    </svg>
                                @elseif($cat['slug'] === 'aksesoris')
                                    <!-- Headphones Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 003 3h1a3 3 0 003-3v-3a3 3 0 00-3-3H3v6zm18 0a3 3 0 01-3 3h-1a3 3 0 01-3-3v-3a3 3 0 013-3h1v6z"/>
                                    </svg>
                                @elseif($cat['slug'] === 'networking')
                                    <!-- Wifi / Router Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                    </svg>
                                @elseif($cat['slug'] === 'software')
                                    <!-- Disc / Software Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                    </svg>
                                @elseif($cat['slug'] === 'gaming')
                                    <!-- Gamepad Controller Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="2" y="6" width="20" height="12" rx="6" stroke-width="2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h4m-2-2v4m9-2h.01m2-2h.01m-2 4h.01m2 0h.01"/>
                                    </svg>
                                @elseif($cat['slug'] === 'apple')
                                    <!-- Apple Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.38c.62-.75 1.04-1.8 0.93-2.85-.9.04-1.99.6-2.63 1.35-.57.65-1.07 1.72-.94 2.74 1.01.08 2.02-.49 2.64-1.24z"/>
                                    </svg>
                                @else
                                    <!-- Generic Category Icon -->
                                    <svg class="w-4 h-4 {{ $kategori === $cat['slug'] ? 'text-[#301ac4]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                @endif
                                <span>{{ $cat['name'] }}</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @endforeach
                </div>
            </div>



            <!-- Reset All Filters Button -->
            @if($hasActiveFilters)
            <div class="pt-1">
                <button type="button" 
                        wire:click="resetFilters" 
                        class="w-full py-3 px-4 rounded-2xl bg-gray-100 hover:bg-rose-50 hover:text-rose-600 text-gray-600 text-xs font-bold transition-all flex items-center justify-center gap-2 border border-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Reset Semua Filter</span>
                </button>
            </div>
            @endif

        </aside>

        <!-- RIGHT MAIN AREA: PRODUCTS CATALOG (Order-1 on mobile, Order-2 on desktop) -->
        <main class="order-1 lg:order-2 lg:col-span-9 space-y-6">
            
            <!-- Top Toolbar -->
            <div class="bg-white rounded-3xl border border-gray-200/90 p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-black text-[#0c1330] tracking-tight">
                            {{ $kategori === 'semua' ? 'Semua Produk' : ucfirst($kategori) }}
                        </h1>
                        @if($hasActiveFilters)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100 text-[#5E17EB]">
                            Filter Aktif
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Temukan berbagai pilihan perangkat komputer, laptop, dan aksesoris dengan harga terbaik.
                    </p>
                    <span class="text-xs font-bold text-gray-400 mt-2 block">
                        Menampilkan {{ $products->count() > 0 ? 1 : 0 }} &ndash; {{ $products->count() }} dari {{ $totalFilteredCount }} produk
                    </span>
                </div>

                <!-- Search, Sort & Cart Controls -->
                <div class="flex flex-wrap items-center gap-3">
                    
                    <!-- Search Input (Zero placeholder standard) -->
                    <div class="relative w-full sm:w-56">
                        <label for="search-input" class="sr-only">Cari produk</label>
                        <input type="text" 
                               id="search-input"
                               wire:model.live.debounce.300ms="search" 
                               class="w-full text-xs font-semibold rounded-xl border border-gray-200 pl-9 pr-8 py-2.5 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] transition-all">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        @endif
                    </div>

                    <!-- Sort Dropdown -->
                    <select wire:model.live="sort" 
                            aria-label="Urutan Produk"
                            class="text-xs font-bold text-gray-700 rounded-xl border border-gray-200 py-2.5 px-3 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] cursor-pointer">
                        <option value="terbaru">Urutan: Terbaru</option>
                        <option value="termurah">Urutan: Termurah</option>
                        <option value="termahal">Urutan: Termahal</option>
                        <option value="nama_asc">Urutan: Nama A &ndash; Z</option>
                    </select>

                    <!-- Cart Trigger Button in Toolbar -->
                    <button type="button"
                            wire:click="toggleCartModal"
                            class="relative p-2.5 rounded-xl border border-gray-200 hover:border-[#5E17EB] text-gray-700 hover:text-[#5E17EB] bg-white transition-colors flex items-center gap-1.5"
                            title="Buka Keranjang Belanja">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="text-xs font-black">{{ $cartCount }}</span>
                        @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 rounded-full animate-ping"></span>
                        @endif
                    </button>

                    <!-- View Switcher -->
                    <div class="hidden sm:flex items-center border border-gray-200 rounded-xl p-1 bg-gray-50">
                        <button type="button" 
                                wire:click="$set('viewMode', 'grid')" 
                                class="p-1.5 rounded-lg {{ $viewMode === 'grid' ? 'bg-white text-[#5E17EB] shadow-xs' : 'text-gray-400 hover:text-gray-700' }}"
                                title="Tampilan Kotak Grid">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </button>
                        <button type="button" 
                                wire:click="$set('viewMode', 'list')" 
                                class="p-1.5 rounded-lg {{ $viewMode === 'list' ? 'bg-white text-[#5E17EB] shadow-xs' : 'text-gray-400 hover:text-gray-700' }}"
                                title="Tampilan Daftar List">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products View -->
            @if ($products->isEmpty())
                <div class="bg-white rounded-3xl border border-gray-200/90 p-12 text-center space-y-3">
                    <div class="w-16 h-16 bg-purple-50 text-[#5E17EB] rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-[#0c1330]">Produk tidak ditemukan</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">
                        Tidak ada produk yang cocok dengan kombinasi filter dan kata kunci saat ini. Silakan sesuaikan pencarian Anda.
                    </p>
                    <div class="pt-2">
                        <button type="button" wire:click="resetFilters" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#5E17EB] to-[#E6007A] text-white text-xs font-bold shadow-md hover:opacity-95">
                            Reset Semua Filter
                        </button>
                    </div>
                </div>
            @elseif ($viewMode === 'grid')
                <!-- GRID VIEW (Matching Image 3) -->
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5">
                    @foreach ($products as $item)
                        @php $inCart = isset($cart[$item['id']]); @endphp
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 flex flex-col group hover:shadow-xl hover:border-purple-300 transition-all cursor-pointer relative">
                            
                            <!-- Badges -->
                            @if($item['badge'])
                                <div class="absolute top-3 left-3 z-10 {{ $item['badge']['color'] }} text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-xs">
                                    {{ $item['badge']['text'] }}
                                </div>
                            @endif

                            @if($inCart)
                                <div class="absolute top-3 right-3 z-10 bg-emerald-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                                    <span>{{ $cart[$item['id']]['qty'] }} di Keranjang</span>
                                </div>
                            @endif

                            <!-- Product Image (Authentic Hardware Photo) -->
                            <div class="aspect-square bg-gray-50 rounded-xl mb-3 p-2.5 flex items-center justify-center overflow-hidden relative">
                                <img src="{{ $item['image'] }}" 
                                     alt="{{ $item['name'] }}" 
                                     loading="lazy"
                                     decoding="async"
                                     class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300">
                            </div>

                            <!-- Brand -->
                            @if(!empty($item['brand']))
                                <span class="text-[11px] font-black uppercase tracking-wider text-[#5E17EB] block mb-0.5 truncate" title="{{ $item['brand'] }}">
                                    {{ $item['brand'] }}
                                </span>
                            @endif

                            <!-- Product Title (Truncate to 2 lines for uniform grid cards) -->
                            <h3 class="font-extrabold text-xs sm:text-sm text-[#0c1330] leading-snug mb-2 line-clamp-2" title="{{ $item['name'] }}">
                                {{ $item['name'] }}
                            </h3>

                            <!-- Price Row -->
                            <div class="mt-auto pt-2 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-baseline gap-1.5 flex-wrap">
                                    <span class="text-[#0c1330] font-black text-xs sm:text-sm block">
                                        Rp {{ $item['formatted_price'] }}
                                    </span>
                                </div>
                                @if($item['badge'])
                                    <span class="text-[9px] font-black text-white {{ $item['badge']['color'] }} px-1.5 py-0.5 rounded shadow-xs">
                                        {{ $item['badge']['text'] }}
                                    </span>
                                @endif
                            </div>

                            <!-- Full-Width "Tambah ke Keranjang" Button (Matching Image 2) -->
                            <button type="button"
                                    wire:click="addToCart({{ $item['id'] }})"
                                    wire:loading.attr="disabled"
                                    class="w-full mt-2.5 py-2 px-2.5 rounded-xl bg-[#5E17EB] hover:bg-[#4912bd] text-white flex items-center justify-center gap-2 text-xs font-bold transition-all shadow-xs shadow-purple-500/20 active:scale-95 group/btn"
                                    title="Tambah ke Keranjang">
                                <svg class="w-4 h-4 text-white shrink-0 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="truncate">Tambah ke Keranjang</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- LIST VIEW -->
                <div class="space-y-3">
                    @foreach ($products as $item)
                        @php $inCart = isset($cart[$item['id']]); @endphp
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 flex flex-col sm:flex-row items-center justify-between gap-4 group hover:shadow-lg hover:border-purple-200 transition-all">
                            <div class="flex items-center gap-4 flex-1 min-w-0 w-full sm:w-auto">
                                <div class="w-20 h-20 bg-gray-50 rounded-xl p-2 shrink-0 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" class="w-full h-full object-contain mix-blend-multiply">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-0.5 rounded truncate max-w-[160px]">{{ $item['brand'] }}</span>
                                        @if($item['badge'])
                                        <span class="text-[10px] font-bold text-white {{ $item['badge']['color'] }} px-2 py-0.5 rounded shrink-0">{{ $item['badge']['text'] }}</span>
                                        @endif
                                    </div>
                                    <h3 class="font-bold text-sm text-[#0c1330] leading-snug truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</h3>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100 shrink-0">
                                <div class="text-left sm:text-right shrink-0">
                                    <span class="text-rose-600 font-black text-base block whitespace-nowrap">Rp {{ $item['formatted_price'] }}</span>
                                    <span class="text-[10px] text-gray-400 font-semibold block whitespace-nowrap">Tersedia di Toko</span>
                                </div>
                                <button type="button"
                                        wire:click="addToCart({{ $item['id'] }})"
                                        class="px-4 py-2.5 rounded-xl {{ $inCart ? 'bg-emerald-600' : 'bg-[#5E17EB]' }} text-white text-xs font-bold flex items-center gap-2 shadow-xs hover:opacity-95 transition-all shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>{{ $inCart ? 'Tambah Lagi' : '+ Keranjang' }}</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- INFINITE LOADING SENTINEL (3 Bouncing Dots Standard) -->
            <div class="mt-8">
                <x-infinite-scroll-sentinel
                    :has-more="$hasMorePages"
                    target="loadMore"
                    label="produk"
                    :current="$products->count()"
                    :total="$totalFilteredCount"
                    color="purple" />
            </div>

        </main>
    </div>

    <!-- FLOATING CART BUTTON (Pill at bottom right) -->
    <div class="fixed bottom-6 right-6 z-40">
        <button type="button"
                wire:click="toggleCartModal"
                class="bg-gradient-to-r from-[#5E17EB] to-[#E6007A] text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 hover:scale-105 active:scale-95 transition-all shadow-[#5E17EB]/40 group border-2 border-white/20">
            <div class="relative">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                @if($cartCount > 0)
                <span class="absolute -top-2 -right-2 bg-emerald-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                    {{ $cartCount }}
                </span>
                @endif
            </div>
            <div class="flex flex-col text-left">
                <span class="text-xs font-black tracking-wide leading-none uppercase">Keranjang</span>
                <span class="text-xs font-bold text-white/90 leading-tight mt-0.5">Rp {{ $formattedCartTotal }}</span>
            </div>
        </button>
    </div>

    <!-- SLIDE-OVER CART DRAWER & WHATSAPP ORDER MODAL -->
    <div x-cloak 
         x-show="showCart" 
         class="fixed inset-0 z-50 overflow-hidden" 
         aria-labelledby="slide-over-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="showCart" 
             x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showCart = false"
             class="fixed inset-0 bg-[#0c1330]/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="showCart" 
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-md bg-white shadow-2xl flex flex-col">
                
                <!-- Drawer Header -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-r from-[#5E17EB] to-[#E6007A] text-white flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-[#0c1330] leading-none">Keranjang Belanja</h2>
                            <p class="text-xs text-gray-500 font-semibold mt-1">{{ $cartCount }} produk dipilih</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!empty($cart))
                        <button type="button" 
                                wire:click="clearCart"
                                class="text-xs font-bold text-rose-500 hover:text-rose-700 px-2 py-1 rounded-lg hover:bg-rose-50 transition-colors">
                            Kosongkan
                        </button>
                        @endif
                        <button type="button" 
                                @click="showCart = false"
                                class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Drawer Content -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    @if(empty($cart))
                        <div class="py-16 text-center space-y-3">
                            <div class="w-16 h-16 bg-purple-50 text-[#5E17EB] rounded-full flex items-center justify-center mx-auto">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <h3 class="text-sm font-black text-[#0c1330]">Keranjang Masih Kosong</h3>
                            <p class="text-xs text-gray-400 max-w-xs mx-auto">
                                Silakan pilih produk dari katalog kami untuk memulai pemesanan via WhatsApp.
                            </p>
                            <div class="pt-2">
                                <button type="button" 
                                        @click="showCart = false"
                                        class="px-5 py-2.5 rounded-xl bg-[#5E17EB] text-white text-xs font-bold hover:bg-[#301ac4] transition-colors">
                                    Mulai Pilih Produk
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Cart Items List -->
                        <div class="divide-y divide-gray-100 space-y-3">
                            @foreach ($cart as $id => $item)
                                <div class="pt-3 first:pt-0 flex items-start gap-3">
                                    <div class="w-14 h-14 bg-gray-50 rounded-xl p-1 shrink-0 flex items-center justify-center overflow-hidden border border-gray-100">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain mix-blend-multiply">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-[#0c1330] leading-snug truncate" title="{{ $item['name'] }}">
                                            {{ $item['name'] }}
                                        </h4>
                                        <span class="text-xs font-black text-rose-600 block mt-0.5">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </span>
                                        
                                        <!-- Quantity stepper -->
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                                <button type="button" 
                                                        wire:click="decrementQty({{ $id }})"
                                                        class="px-2 py-0.5 text-xs font-bold text-gray-600 hover:bg-gray-200 transition-colors">
                                                    &minus;
                                                </button>
                                                <span class="px-2.5 py-0.5 text-xs font-black text-[#0c1330] bg-white">
                                                    {{ $item['qty'] }}
                                                </span>
                                                <button type="button" 
                                                        wire:click="incrementQty({{ $id }})"
                                                        class="px-2 py-0.5 text-xs font-bold text-gray-600 hover:bg-gray-200 transition-colors">
                                                    +
                                                </button>
                                            </div>
                                            <span class="text-[11px] font-semibold text-gray-400">
                                                Subtotal: Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            wire:click="removeFromCart({{ $id }})"
                                            class="text-gray-300 hover:text-rose-500 p-1 transition-colors"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Form Informasi Pemesan (Zero Placeholder Standard) -->
                        <div class="mt-6 pt-6 border-t border-gray-100 space-y-3.5">
                            <h3 class="text-xs font-black text-[#0c1330] uppercase tracking-wider">
                                Data Pemesan
                            </h3>
                            
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="customerName" class="block text-xs font-bold text-gray-700 mb-1">
                                    Nama Lengkap <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text" 
                                       id="customerName" 
                                       wire:model.defer="customerName"
                                       class="w-full text-xs font-medium rounded-xl border border-gray-200 p-2.5 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] @error('customerName') border-rose-500 @enderror">
                                @error('customerName')
                                <span class="text-[11px] text-rose-500 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- No. WhatsApp -->
                            <div>
                                <label for="customerPhone" class="block text-xs font-bold text-gray-700 mb-1">
                                    No. WhatsApp / HP
                                </label>
                                <input type="text" 
                                       id="customerPhone" 
                                       wire:model.defer="customerPhone"
                                       class="w-full text-xs font-medium rounded-xl border border-gray-200 p-2.5 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB]">
                            </div>

                            <!-- Catatan / Alamat -->
                            <div>
                                <label for="customerNotes" class="block text-xs font-bold text-gray-700 mb-1">
                                    Catatan / Alamat Pengiriman
                                </label>
                                <textarea id="customerNotes" 
                                          wire:model.defer="customerNotes" 
                                          rows="2"
                                          class="w-full text-xs font-medium rounded-xl border border-gray-200 p-2.5 focus:border-[#5E17EB] focus:ring-1 focus:ring-[#5E17EB] resize-none"></textarea>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Drawer Footer & Checkout Button -->
                @if(!empty($cart))
                <div class="p-6 border-t border-gray-100 bg-gray-50/80 space-y-4">
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs text-gray-500 font-semibold">
                            <span>Total Item</span>
                            <span>{{ $cartCount }} unit</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-black text-[#0c1330] pt-1 border-t border-gray-200/60">
                            <span>Total Tagihan</span>
                            <span class="text-rose-600 text-lg">Rp {{ $formattedCartTotal }}</span>
                        </div>
                    </div>

                    <!-- WhatsApp Checkout Button with Heroicon Spinner loading standard -->
                    <button type="button" 
                            wire:click="orderViaWhatsApp"
                            wire:loading.attr="disabled"
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2.5 transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="orderViaWhatsApp" class="flex items-center gap-2">
                            <!-- WhatsApp Icon SVG -->
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            <span>Pesan via WhatsApp</span>
                        </span>
                        <span wire:loading wire:target="orderViaWhatsApp" class="inline-flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Membuka WhatsApp...</span>
                        </span>
                    </button>
                    <p class="text-[10px] text-gray-400 text-center font-medium">
                        Rincian pesanan akan otomatis terformat rapi dan dikirim ke customer service toko.
                    </p>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>

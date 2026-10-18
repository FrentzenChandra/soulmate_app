<div class="w-full bg-[#f8f9fc] pb-14">

    <!-- 1. HERO SECTION (100% Identical to Mockup with 4K Storefront Image) -->
    <section class="relative bg-gradient-to-r from-[#f5f0ff] via-[#faf8ff] to-[#f5f0ff] overflow-hidden border-b border-purple-100/60">
        <!-- Right-Side Real Storefront Photo with Seamless Left Fade -->
        <div class="relative lg:absolute lg:inset-y-0 lg:right-0 w-full lg:w-[58%] h-[280px] sm:h-[360px] lg:h-full z-0">
            <img src="{{ asset('images/toko_depan_real.jpg') }}?v={{ filemtime(public_path('images/toko_depan_real.jpg')) }}"
                 alt="Toko Soulmate Komputer Pangkalpinang"
                 class="w-full h-full object-cover object-center lg:object-right">
            <!-- Smooth Left-to-Right Blend Mask on Desktop -->
            <div class="hidden lg:block absolute inset-y-0 left-0 w-56 bg-gradient-to-r from-[#faf8ff] via-[#faf8ff]/85 to-transparent"></div>
            <!-- Subtle Bottom Vignette for Mobile -->
            <div class="lg:hidden absolute inset-0 bg-gradient-to-b from-[#f5f0ff]/80 via-transparent to-black/20"></div>

            <!-- Floating Card: Kunjungi Toko Kami (Bottom Right) -->
            <a href="https://maps.google.com/?q=Soulmate+Komputer+Jl.+Masjid+Jamik+Pangkalpinang"
               target="_blank"
               rel="noopener noreferrer"
               class="absolute bottom-4 right-4 sm:bottom-6 sm:right-6 lg:bottom-7 lg:right-8 z-20 bg-white/95 backdrop-blur-md hover:bg-white rounded-2xl px-4 py-3 sm:px-5 sm:py-3.5 shadow-xl border border-white/90 flex items-center gap-3.5 max-w-[320px] sm:max-w-[350px] group transition-all hover:scale-[1.01]">
                <!-- Pink Location Pin Icon -->
                <div class="w-9 h-9 rounded-full bg-pink-50 text-[#E6007A] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-extrabold text-xs sm:text-sm text-[#0c1330] leading-tight group-hover:text-[#5E17EB] transition-colors">
                        Kunjungi Toko Kami
                    </h4>
                    <p class="text-[11px] text-gray-600 font-medium leading-snug mt-0.5">
                        Jl. Masjid Jamik, Rangkui,<br>Pangkal Pinang, Kep. Bangka Belitung 33116
                    </p>
                </div>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-[#5E17EB] group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Left Hero Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-8 sm:py-10 lg:py-12">
            <div class="w-full lg:w-[52%] xl:w-[50%]">
                
                <!-- Top Store Badge Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#ede5ff] border border-purple-200/80 text-[#48169c] text-[10px] sm:text-xs font-extrabold tracking-wider uppercase mb-4 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#5E17EB] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 9l2-5h14l2 5M3 9v10a1 1 0 001 1h16a1 1 0 001-1V9M3 9h18M9 20v-6h6v6"/>
                    </svg>
                    <span>TOKO KOMPUTER TERPERCAYA DI PANGKALPINANG</span>
                </div>

                <!-- Main Stacked Heading -->
                <h1 class="text-4xl sm:text-5xl xl:text-[3.6rem] font-black leading-[1.02] tracking-tight mb-3.5">
                    <span class="block text-[#0c1324]">SOULMATE</span>
                    <span class="block text-[#E6007A]">KOMPUTER</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-xs sm:text-sm lg:text-[15px] text-gray-700 font-medium leading-relaxed mb-6 max-w-lg">
                    Laptop, komputer, printer dan perlengkapan IT lengkap dengan harga terbaik dan pelayanan profesional.
                </p>

                <!-- 4 Feature Highlights Row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-2.5 mb-6 bg-white/75 backdrop-blur-xs rounded-2xl p-2.5 border border-purple-100/60 lg:bg-transparent lg:backdrop-blur-none lg:p-0 lg:border-0">
                    <!-- 1. Produk Original -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-100/80 text-[#5E17EB] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="leading-tight">
                            <span class="block text-[11px] font-extrabold text-[#0c1330]">Produk Original</span>
                            <span class="block text-[10px] text-gray-500 font-medium">Garansi Resmi</span>
                        </div>
                    </div>

                    <!-- 2. Harga Terbaik -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-100/80 text-[#5E17EB] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div class="leading-tight">
                            <span class="block text-[11px] font-extrabold text-[#0c1330]">Harga Terbaik</span>
                            <span class="block text-[10px] text-gray-500 font-medium">Update Terbaru</span>
                        </div>
                    </div>

                    <!-- 3. Pelayanan Profesional -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-100/80 text-[#5E17EB] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 003 3h1a3 3 0 003-3v-3a3 3 0 00-3-3H3v6zm18 0a3 3 0 01-3 3h-1a3 3 0 01-3-3v-3a3 3 0 013-3h1v6z"/>
                            </svg>
                        </div>
                        <div class="leading-tight">
                            <span class="block text-[11px] font-extrabold text-[#0c1330]">Pelayanan Profesional</span>
                            <span class="block text-[10px] text-gray-500 font-medium">Tim Berpengalaman</span>
                        </div>
                    </div>

                    <!-- 4. Bisa Kredit -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-100/80 text-[#5E17EB] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                        </div>
                        <div class="leading-tight">
                            <span class="block text-[11px] font-extrabold text-[#0c1330]">Bisa Kredit</span>
                            <span class="block text-[10px] text-gray-500 font-medium">Proses Mudah &amp; Cepat</span>
                        </div>
                    </div>
                </div>

                <!-- 2 Social Media CTA Buttons (Instagram & TikTok) -->
                <div class="flex flex-wrap items-center gap-3.5">
                    <!-- Instagram Button -->
                    <a href="https://www.instagram.com/soulmatekomputer"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="flex-1 min-w-[200px] sm:max-w-[235px] px-4 py-3 rounded-2xl bg-gradient-to-r from-[#ff7a18] via-[#e6007a] to-[#8a19d6] hover:opacity-95 text-white shadow-lg shadow-pink-500/20 flex items-center justify-between gap-3 transition-all group">
                        <div class="flex items-center gap-3">
                            <!-- Instagram Icon -->
                            <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </div>
                            <div class="leading-tight">
                                <span class="block text-[11px] font-semibold text-white/90">Instagram</span>
                                <span class="block text-xs sm:text-[13px] font-extrabold text-white">Soulmate Komputer</span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-white/80 group-hover:translate-x-0.5 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <!-- TikTok Button -->
                    <a href="https://www.tiktok.com/@soulmatekomputer"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="flex-1 min-w-[200px] sm:max-w-[235px] px-4 py-3 rounded-2xl bg-[#0c1324] hover:bg-[#162038] text-white shadow-lg shadow-slate-900/15 flex items-center justify-between gap-3 transition-all group">
                        <div class="flex items-center gap-3">
                            <!-- TikTok Icon -->
                            <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/>
                                </svg>
                            </div>
                            <div class="leading-tight">
                                <span class="block text-[11px] font-semibold text-gray-300">TikTok</span>
                                <span class="block text-xs sm:text-[13px] font-extrabold text-white">Soulmate Komputer</span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-0.5 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-7 space-y-6">

        <!-- 2. 8 CATEGORY CARDS ROW -->
        <section>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-3.5">
                @foreach ($categories as $cat)
                    <a href="{{ route('web.produk', ['kategori' => $cat['category']]) }}"
                       class="bg-white rounded-2xl border border-gray-100 shadow-2xs hover:shadow-md hover:border-purple-200 p-3 flex flex-col justify-between group transition-all">
                        <div class="h-20 sm:h-24 w-full flex items-center justify-center p-1.5 mb-2 overflow-hidden">
                            <img src="{{ $cat['image'] }}"
                                 alt="{{ $cat['name'] }}"
                                 class="max-h-16 sm:max-h-20 max-w-full w-auto h-auto object-contain group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs sm:text-[13px] font-extrabold text-[#0c1330] group-hover:text-[#5E17EB] transition-colors">
                                {{ $cat['name'] }}
                            </span>
                            <svg class="w-3.5 h-3.5 text-[#5E17EB] group-hover:translate-x-0.5 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- 3. BRAND LOGOS BAR -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-2xs px-4 sm:px-6 py-4">
            <div class="flex flex-wrap lg:flex-nowrap items-center justify-between gap-4 sm:gap-6">
                <!-- 1. ASUS (Official Blue) -->
                <a href="{{ route('web.produk', ['brand' => 'ASUS']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="ASUS">
                    <img src="{{ asset('images/brands/asus.svg') }}" alt="ASUS" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 2. Lenovo -->
                <a href="{{ route('web.produk', ['brand' => 'Lenovo']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Lenovo">
                    <img src="{{ asset('images/brands/lenovo.svg') }}" alt="Lenovo" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 3. Acer -->
                <a href="{{ route('web.produk', ['brand' => 'Acer']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Acer">
                    <img src="{{ asset('images/brands/acer.svg') }}" alt="Acer" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 4. HP -->
                <a href="{{ route('web.produk', ['brand' => 'HP']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="HP">
                    <img src="{{ asset('images/brands/hp.svg') }}" alt="HP" class="h-6 sm:h-7 max-h-7 w-auto object-contain" height="28">
                </a>

                <!-- 5. Advan -->
                <a href="{{ route('web.produk', ['search' => 'Advan']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Advan">
                    <img src="{{ asset('images/brands/advan.png') }}" alt="Advan" class="h-3.5 sm:h-4.5 max-h-5 w-auto object-contain" height="18">
                </a>

                <!-- 6. Axioo -->
                <a href="{{ route('web.produk', ['search' => 'Axioo']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Axioo">
                    <img src="{{ asset('images/brands/axioo.png') }}" alt="Axioo" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 7. Canon -->
                <a href="{{ route('web.produk', ['brand' => 'Canon']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Canon">
                    <img src="{{ asset('images/brands/canon.svg') }}" alt="Canon" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 8. Epson -->
                <a href="{{ route('web.produk', ['brand' => 'Epson']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Epson">
                    <img src="{{ asset('images/brands/epson.svg') }}" alt="Epson" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 9. Logitech -->
                <a href="{{ route('web.produk', ['brand' => 'Logitech']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Logitech">
                    <img src="{{ asset('images/brands/logitech.svg') }}" alt="Logitech" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 10. Rexus -->
                <a href="{{ route('web.produk', ['search' => 'Rexus']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Rexus">
                    <img src="{{ asset('images/brands/rexus.png') }}" alt="Rexus" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 11. Ezviz -->
                <a href="{{ route('web.produk', ['search' => 'Ezviz']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="Ezviz">
                    <img src="{{ asset('images/brands/ezviz.svg') }}" alt="Ezviz" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- 12. TP-Link -->
                <a href="{{ route('web.produk', ['search' => 'TP-Link']) }}" class="hover:opacity-75 transition-opacity shrink-0 flex items-center" title="TP-Link">
                    <img src="{{ asset('images/brands/tplink.svg') }}" alt="TP-Link" class="h-4 sm:h-5 max-h-5 w-auto object-contain" height="20">
                </a>

                <!-- Lihat Semua Brand > -->
                <a href="{{ route('web.produk') }}"
                   class="text-xs font-extrabold text-[#5E17EB] hover:text-[#E6007A] flex items-center gap-1 whitespace-nowrap shrink-0 transition-colors ml-auto lg:ml-0">
                    <span>Lihat Semua Brand</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </section>

        <!-- 4. OFFICIAL PLATFORMS BANNER (KAMI TERSEDIA DI BERBAGAI PLATFORM RESMI) -->
        <section class="bg-gradient-to-r from-[#f3edff] via-[#f7f3ff] to-[#f3edff] rounded-3xl border border-purple-100/90 p-5 sm:p-6 lg:p-7 shadow-xs">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                
                <!-- Left (5 Cols): 3D Phone Illustration + Title -->
                <div class="lg:col-span-5 flex flex-col sm:flex-row items-center gap-4 sm:gap-5 text-center sm:text-left">
                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden shrink-0">
                        <img src="{{ asset('images/platform_shopping_3d.jpg') }}?v=4k"
                             alt="Platform Resmi Soulmate Komputer"
                             class="w-full h-full object-contain mix-blend-multiply">
                    </div>
                    <div>
                        <span class="block text-xs sm:text-sm font-black text-[#0c1330] uppercase tracking-wider">
                            KAMI TERSEDIA DI
                        </span>
                        <h2 class="text-2xl sm:text-[1.75rem] font-black leading-[1.08] tracking-tight mt-0.5">
                            <span class="block text-[#E6007A]">BERBAGAI</span>
                            <span class="block text-[#5E17EB]">PLATFORM RESMI</span>
                        </h2>
                        <p class="text-[11px] sm:text-xs text-gray-600 font-medium leading-relaxed mt-1.5">
                            Belanja lebih mudah, aman dan terpercaya di platform resmi.
                        </p>
                    </div>
                </div>

                <!-- Right (7 Cols): 6 Official Platform Cards with Links -->
                <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
                    @foreach ($platforms as $platform)
                        <a href="{{ $platform['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="bg-white rounded-2xl border border-purple-100/80 hover:border-[#5E17EB] p-3 flex flex-col items-center text-center shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all group">
                            
                            <!-- Platform Icon -->
                            <div class="h-12 w-full flex items-center justify-center mb-2 px-1">
                                <img src="{{ $platform['logo'] }}" 
                                     alt="{{ $platform['title'] }}" 
                                     class="h-8 sm:h-9 max-h-9 w-auto max-w-[85px] object-contain group-hover:scale-105 transition-transform duration-200">
                            </div>

                            <!-- Platform Title -->
                            <h3 class="font-extrabold text-[11px] sm:text-xs text-[#0c1330] group-hover:text-[#5E17EB] leading-tight transition-colors">
                                {{ $platform['title'] }}
                            </h3>

                            <!-- Platform Subtitle -->
                            <p class="text-[9px] sm:text-[10px] text-gray-500 font-medium leading-tight mt-1">
                                {{ $platform['subtitle'] }}
                            </p>
                        </a>
                    @endforeach
                </div>

            </div>
        </section>

    </div>
</div>

<div class="w-full bg-[#f8f9fc] pb-16"
     x-data="{
         locatingMaps: false,
         mapsStatus: '',
         ambilLokasiMaps() {
             if (!navigator.geolocation) {
                 window.open('https://www.google.com/maps', '_blank');
                 return;
             }
             this.locatingMaps = true;
             this.mapsStatus = 'Mendeteksi lokasi...';
             navigator.geolocation.getCurrentPosition(
                 (pos) => {
                     const lat = pos.coords.latitude.toFixed(6);
                     const lng = pos.coords.longitude.toFixed(6);
                     const gmapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
                     $wire.set('jemputMapsUrl', gmapsUrl);
                     if (!$wire.get('jemputAlamat')) {
                         $wire.set('jemputAlamat', `Titik Koordinat Maps (${lat}, ${lng})`);
                     }
                     this.locatingMaps = false;
                     this.mapsStatus = 'Titik Maps berhasil disematkan!';
                 },
                 () => {
                     this.locatingMaps = false;
                     this.mapsStatus = '';
                     window.open('https://www.google.com/maps', '_blank');
                 },
                 { enableHighAccuracy: true, timeout: 8000 }
             );
         }
     }">

    <!-- 1. HERO SECTION (Dynamic for both 'cek' and 'jemput' modes) -->
    <section class="relative bg-[#110c2e] text-white overflow-hidden">
        <!-- Right-aligned Technician Image with Seamless Gradient Blend -->
        <div class="absolute inset-y-0 right-0 w-full lg:w-[62%] z-0">
            <img src="{{ asset('images/hero_servis_teknisi.jpg') }}?v=4k" 
                 alt="Teknisi Soulmate Komputer" 
                 class="w-full h-full object-cover object-center lg:object-right opacity-45 lg:opacity-100">
            <!-- Left-to-right gradient mask so left text is crisp and blends smoothly into the photo -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#110c2e] via-[#110c2e]/90 lg:via-[#110c2e]/45 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#110c2e]/80 via-transparent to-[#110c2e]/30 lg:hidden"></div>
        </div>

        <!-- Subtle Ambient Glow on Left -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#5E17EB]/25 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-12 sm:py-14 lg:py-16">
            @if($activeMode === 'cek')
                <!-- HERO CONTENT: CEK STATUS SERVIS (IMAGE 2) -->
                <div class="max-w-xl">
                    <span class="text-xs sm:text-sm font-bold tracking-[0.25em] text-gray-200 uppercase mb-2.5 block">
                        LAYANAN SERVIS
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-[3.4rem] font-black leading-[1.1] tracking-tight mb-4">
                        <span class="text-white">Cek Status </span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff1a8c] to-[#e6007a]">Servis</span>
                    </h1>
                    <p class="text-xs sm:text-sm lg:text-base text-gray-200 font-normal leading-relaxed mb-7 max-w-lg">
                        Pantau proses servis perangkat Anda dengan mudah, cepat, dan transparan di Soulmate Komputer.
                    </p>

                    <!-- 3 Feature Points -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <!-- Mudah -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#6c19ff] flex items-center justify-center text-white shadow-lg shadow-purple-900/50 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-white text-xs sm:text-sm leading-tight">Mudah</h4>
                                <p class="text-[11px] text-gray-300 leading-snug mt-0.5">Cukup masukkan kode servis</p>
                            </div>
                        </div>

                        <!-- Cepat -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#6c19ff] flex items-center justify-center text-white shadow-lg shadow-purple-900/50 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-white text-xs sm:text-sm leading-tight">Cepat</h4>
                                <p class="text-[11px] text-gray-300 leading-snug mt-0.5">Informasi real-time</p>
                            </div>
                        </div>

                        <!-- Terpercaya -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#6c19ff] flex items-center justify-center text-white shadow-lg shadow-purple-900/50 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-white text-xs sm:text-sm leading-tight">Terpercaya</h4>
                                <p class="text-[11px] text-gray-300 leading-snug mt-0.5">Data langsung dari sistem kami</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- HERO CONTENT: AJUKAN JEMPUT SERVISAN (IMAGE 1) -->
                <div class="max-w-xl py-2">
                    <span class="text-xs sm:text-sm font-bold tracking-[0.25em] text-gray-200 uppercase mb-2.5 block">
                        LAYANAN SERVIS
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-[3.3rem] font-black leading-[1.1] tracking-tight mb-4">
                        <span class="text-white">Ajukan </span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff1a8c] to-[#e6007a]">Jemput Servisan</span>
                    </h1>
                    <p class="text-xs sm:text-sm lg:text-base text-gray-200 font-normal leading-relaxed max-w-lg">
                        Tidak sempat antar ke toko? Kami siap jemput perangkat Anda di rumah atau kantor dengan aman dan cepat.
                    </p>
                </div>
            @endif
        </div>
    </section>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20 space-y-6">

        @if($activeMode === 'cek')
            <!-- ========================================================== -->
            <!-- MODE 1: CEK STATUS SERVIS VIEW (100% MATCHING IMAGE 2)     -->
            <!-- ========================================================== -->

            <!-- 1. CEK STATUS SERVIS CARD -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 lg:px-10 lg:py-8 shadow-[0_10px_35px_rgba(12,19,48,0.06)]">
                <div class="flex flex-col md:flex-row items-start gap-5 sm:gap-7">
                    
                    <!-- Left Document + Magnifier Icon -->
                    <div class="shrink-0 mt-1">
                        <svg class="w-14 h-14 sm:w-16 sm:h-16" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Document Body -->
                            <path d="M12 10C12 6.68629 14.6863 4 18 4H36L48 16V50C48 53.3137 45.3137 56 42 56H18C14.6863 56 12 53.3137 12 50V10Z" fill="url(#docGrad)"/>
                            <!-- Folded Corner -->
                            <path d="M36 4L48 16H40C37.7909 16 36 14.2091 36 12V4Z" fill="#a78bfa" fill-opacity="0.6"/>
                            <!-- Document Lines -->
                            <rect x="20" y="24" width="18" height="3.5" rx="1.75" fill="white"/>
                            <rect x="20" y="32" width="14" height="3.5" rx="1.75" fill="white"/>
                            <rect x="20" y="40" width="10" height="3.5" rx="1.75" fill="white"/>
                            <!-- Magnifying Glass Circle -->
                            <circle cx="43" cy="43" r="10" fill="white" stroke="#4c1ae6" stroke-width="4"/>
                            <circle cx="43" cy="43" r="6" fill="#4c1ae6"/>
                            <!-- Magnifying Glass Handle -->
                            <path d="M51 51L58 58" stroke="#4c1ae6" stroke-width="5" stroke-linecap="round"/>
                            <defs>
                                <linearGradient id="docGrad" x1="12" y1="4" x2="48" y2="56" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#6c19ff"/>
                                    <stop offset="1" stop-color="#3b0ca3"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>

                    <!-- Right Form Content -->
                    <div class="flex-1 w-full">
                        <h2 class="text-xl sm:text-2xl font-black text-[#0c1330] tracking-tight">
                            Cek Status Servis
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 mb-5 font-medium">
                            Masukkan kode servis Anda untuk melihat status, progres, dan estimasi penyelesaian.
                        </p>

                        <form wire:submit.prevent="cekStatus" class="space-y-3">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                                <!-- Input Box with Overlay Hint (Zero Placeholder Attribute) -->
                                <div class="relative flex-1">
                                    <input type="text" 
                                           id="kodeInput"
                                           wire:model="kodeInput"
                                           aria-label="Kode Servis"
                                           class="w-full text-sm font-semibold text-[#0c1330] rounded-xl border border-gray-200 bg-white px-4 py-3.5 focus:border-[#5E17EB] focus:ring-2 focus:ring-[#5E17EB]/15 outline-none transition-all">
                                    <span x-show="!$wire.kodeInput" 
                                          x-cloak
                                          class="absolute inset-y-0 left-4 flex items-center text-xs sm:text-sm text-gray-400 pointer-events-none select-none font-normal">
                                        Contoh: SK-20260715-001
                                    </span>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" 
                                        wire:loading.attr="disabled"
                                        wire:target="cekStatus"
                                        class="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-gradient-to-r from-[#4c1ae6] via-[#6e17eb] to-[#c800b8] text-white font-bold text-sm tracking-wide shadow-md shadow-purple-900/20 hover:opacity-95 transition-all shrink-0 disabled:opacity-50 cursor-pointer">
                                    <svg wire:loading.remove wire:target="cekStatus" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <svg wire:loading wire:target="cekStatus" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="cekStatus">Cek Status</span>
                                    <span wire:loading wire:target="cekStatus">Mencari...</span>
                                </button>
                            </div>

                            <p class="text-xs text-gray-500 flex items-center gap-1.5 font-medium pt-0.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span>Kode servis dapat dilihat pada lembar tanda terima servis yang Anda terima di toko Soulmate Komputer.</span>
                            </p>
                        </form>
                    </div>

                </div>
            </div>

            <!-- ERROR ALERT WHEN NOT FOUND -->
            @if($errorMessage)
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-5 rounded-2xl text-xs sm:text-sm font-semibold flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ $errorMessage }}</span>
                    </div>
                    <a href="https://wa.me/6281995466255?text=Halo%20Soulmate%20Komputer,%20saya%20ingin%20menanyakan%20status%20servis%20saya" 
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 transition-colors shrink-0">
                        <span>Tanya Admin via WhatsApp</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif

            <!-- TRACKING RESULT CARD (Shown when a real service record is found) -->
            @if($servisFound)
                @php
                    $kode = $servisFound->no_servis_frontend ?? $servisFound->no_servis;
                    $customerName = $servisFound->customer?->nama ?? 'Umum';
                    $perangkat = $servisFound->tipe_unit ?? 'Unit Servis';
                    $keluhan = $servisFound->keluhan ?: 'Pemeriksaan / Perbaikan rutin';
                    $teknisi = $servisFound->teknisi?->name ?? 'Tim Teknisi Soulmate';
                    $tglMasuk = $servisFound->tanggal_masuk ? $servisFound->tanggal_masuk->translatedFormat('d F Y H:i') : '-';
                    $statusRaw = $servisFound->status;
                    $statusLabel = $servisFound->status_label;
                    $statusBadgeClass = $servisFound->status_badge_class;

                    $statusOrder = [
                        'antrean_masuk' => 1,
                        'pengecekan' => 2,
                        'menunggu_konfirmasi' => 3,
                        'menunggu_sparepart' => 4,
                        'perbaikan' => 5,
                        'pengerjaan' => 5,
                        'selesai' => 6,
                        'sudah_diambil' => 7,
                        'sudah_dibayar' => 7,
                    ];
                    $currentStep = $statusOrder[$statusRaw] ?? 1;

                    $progressWidth = match($currentStep) {
                        7 => '100%',
                        6 => '83.3%',
                        5 => '66.6%',
                        4 => '50%',
                        3 => '33.3%',
                        2 => '16.6%',
                        default => '0%',
                    };

                    $kategoriLower = strtolower($servisFound->kategori_perangkat ?? '');
                @endphp

                <div class="bg-white rounded-3xl border border-gray-200/90 p-6 sm:p-8 shadow-xl shadow-purple-950/5 space-y-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <!-- Unit Category Icon & Basic Info -->
                        <div class="lg:col-span-4 flex flex-col sm:flex-row gap-5 items-start">
                            <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-2xl bg-gradient-to-br from-purple-50 via-indigo-50 to-purple-100 border border-purple-200/60 p-4 shrink-0 flex flex-col items-center justify-center text-center shadow-inner">
                                @if(str_contains($kategoriLower, 'printer'))
                                    <svg class="w-12 h-12 text-[#301ac4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase text-[#301ac4] tracking-wider mt-2">Printer</span>
                                @elseif(str_contains($kategoriLower, 'komputer') || str_contains($kategoriLower, 'pc'))
                                    <svg class="w-12 h-12 text-[#301ac4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase text-[#301ac4] tracking-wider mt-2">Komputer PC</span>
                                @else
                                    <svg class="w-12 h-12 text-[#301ac4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase text-[#301ac4] tracking-wider mt-2">Laptop</span>
                                @endif
                            </div>
                            <div class="space-y-3 flex-1">
                                <div>
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">KODE SERVIS</span>
                                    <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                        <span class="text-xl sm:text-2xl font-black text-[#0c1330] font-mono tracking-tight">{{ $kode }}</span>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusBadgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-1.5 text-xs text-gray-600">
                                    <div>
                                        <span class="text-gray-400 font-medium block">Jenis Perangkat</span>
                                        <span class="font-bold text-[#0c1330]">{{ $perangkat }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium block">Tanggal Masuk</span>
                                        <span class="font-bold text-[#0c1330]">{{ $tglMasuk }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Customer & Keluhan -->
                        <div class="lg:col-span-3 space-y-3.5 border-t lg:border-t-0 lg:border-l border-gray-100 lg:pl-6 text-xs">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#5E17EB] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <div>
                                    <span class="text-gray-400 block font-medium">Pelanggan</span>
                                    <span class="font-black text-[#0c1330] text-sm">{{ $customerName }}</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#5E17EB] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                <div>
                                    <span class="text-gray-400 block font-medium">Keluhan</span>
                                    <span class="font-bold text-[#0c1330]">{{ $keluhan }}</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#5E17EB] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                <div>
                                    <span class="text-gray-400 block font-medium">Teknisi</span>
                                    <span class="font-bold text-[#0c1330]">{{ $teknisi }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 7-Step Progress Timeline -->
                        <div class="lg:col-span-5 border-t lg:border-t-0 lg:border-l border-gray-100 lg:pl-6 w-full">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-4">Proses Pengerjaan (7 Tahap)</span>
                            
                            <div class="flex items-center justify-between relative">
                                <div class="absolute top-3.5 left-3 right-3 h-0.5 bg-gray-200 z-0"></div>
                                <div class="absolute top-3.5 left-3 h-0.5 bg-[#5E17EB] z-0 transition-all duration-500"
                                     style="width: {{ $progressWidth }};"></div>

                                <!-- Step 1: Antrean Masuk (Merah) -->
                                <div class="flex flex-col items-center relative z-10 text-center">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentStep === 1 ? 'bg-rose-500 text-white ring-2 ring-rose-300 animate-pulse' : ($currentStep > 1 ? 'bg-rose-500 text-white' : 'bg-gray-200 text-gray-400') }} flex items-center justify-center shadow-xs">
                                        @if($currentStep > 1)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-black {{ $currentStep >= 1 ? 'text-[#0c1330]' : 'text-gray-400' }} mt-1.5">Antrean Masuk</span>
                                    <span class="text-[8px] sm:text-[9px] text-gray-400 leading-tight">{{ $servisFound->tanggal_masuk ? $servisFound->tanggal_masuk->format('d M') : '-' }}</span>
                                </div>

                                <!-- Step 2: Pengecekan (Kuning) -->
                                <div class="flex flex-col items-center relative z-10 text-center">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentStep === 2 ? 'bg-amber-500 text-white ring-2 ring-amber-300 animate-pulse' : ($currentStep > 2 ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-400') }} flex items-center justify-center shadow-xs">
                                        @if($currentStep > 2)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-black {{ $currentStep >= 2 ? 'text-[#0c1330]' : 'text-gray-400' }} mt-1.5">Pengecekan</span>
                                    <span class="text-[8px] sm:text-[9px] text-gray-400 leading-tight">{{ $currentStep === 2 ? 'Dicek' : ($currentStep > 2 ? 'Selesai' : '-') }}</span>
                                </div>

                                <!-- Step 3: Menunggu Konfirmasi (Pink) -->
                                <div class="flex flex-col items-center relative z-10 text-center">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentStep === 3 ? 'bg-[#E6007A] text-white ring-2 ring-pink-300 animate-pulse' : ($currentStep > 3 ? 'bg-[#E6007A] text-white' : 'bg-gray-200 text-gray-400') }} flex items-center justify-center shadow-xs">
                                        @if($currentStep > 3)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-black {{ $currentStep >= 3 ? 'text-[#0c1330]' : 'text-gray-400' }} mt-1.5">Konfirmasi</span>
                                    <span class="text-[8px] sm:text-[9px] text-gray-400 leading-tight">{{ $currentStep === 3 ? 'Menunggu' : ($currentStep > 3 ? 'Disetujui' : '-') }}</span>
                                </div>

                                <!-- Step 4: Menunggu Sparepart (Ungu) -->
                                <div class="flex flex-col items-center relative z-10 text-center">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentStep === 4 ? 'bg-[#5E17EB] text-white ring-2 ring-purple-300 animate-pulse' : ($currentStep > 4 ? 'bg-[#5E17EB] text-white' : 'bg-gray-200 text-gray-400') }} flex items-center justify-center shadow-xs">
                                        @if($currentStep > 4)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-black {{ $currentStep >= 4 ? 'text-[#0c1330]' : 'text-gray-400' }} mt-1.5">Sparepart</span>
                                    <span class="text-[8px] sm:text-[9px] text-gray-400 leading-tight">{{ $currentStep === 4 ? 'Menunggu' : ($currentStep > 4 ? 'Lengkap' : '-') }}</span>
                                </div>

                                <!-- Step 5: Perbaikan (Biru) -->
                                <div class="flex flex-col items-center relative z-10 text-center">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentStep === 5 ? 'bg-[#0052FF] text-white ring-2 ring-blue-300 animate-pulse' : ($currentStep > 5 ? 'bg-[#0052FF] text-white' : 'bg-gray-200 text-gray-400') }} flex items-center justify-center shadow-xs">
                                        @if($currentStep > 5)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-black {{ $currentStep >= 5 ? 'text-[#0c1330]' : 'text-gray-400' }} mt-1.5">Perbaikan</span>
                                    <span class="text-[8px] sm:text-[9px] text-gray-400 leading-tight">{{ $currentStep === 5 ? 'Dikerjakan' : ($currentStep > 5 ? 'Selesai' : '-') }}</span>
                                </div>

                                <!-- Step 6: Selesai (Hijau) -->
                                <div class="flex flex-col items-center relative z-10 text-center">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentStep === 6 ? 'bg-emerald-500 text-white ring-2 ring-emerald-300 animate-pulse' : ($currentStep > 6 ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-400') }} flex items-center justify-center shadow-xs">
                                        @if($currentStep > 6)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-black {{ $currentStep >= 6 ? 'text-[#0c1330]' : 'text-gray-400' }} mt-1.5">Selesai</span>
                                    <span class="text-[8px] sm:text-[9px] text-gray-400 leading-tight">{{ $servisFound->tanggal_selesai ? $servisFound->tanggal_selesai->format('d M') : '-' }}</span>
                                </div>

                                <!-- Step 7: Sudah Diambil (Indigo) -->
                                <div class="flex flex-col items-center relative z-10 text-center">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentStep >= 7 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center shadow-xs">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-black {{ $currentStep >= 7 ? 'text-indigo-700' : 'text-gray-400' }} mt-1.5">Diambil</span>
                                    <span class="text-[8px] sm:text-[9px] text-gray-400 leading-tight">{{ $servisFound->tanggal_ambil ? $servisFound->tanggal_ambil->format('d M') : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Note Card & Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-purple-50/70 border border-purple-100 rounded-2xl p-4 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-purple-100 text-[#301ac4] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-[#301ac4] block">Catatan Terakhir:</span>
                                <span class="text-gray-700 font-medium">
                                    {{ $servisFound->catatan ?: ($servisFound->status === 'menunggu_konfirmasi' ? 'Menunggu konfirmasi persetujuan estimasi biaya dan tindakan dari pelanggan.' : ($servisFound->status === 'menunggu_sparepart' ? 'Sedang menunggu ketersediaan komponen atau sparepart pengganti.' : ($servisFound->status === 'selesai' ? 'Unit telah selesai diperbaiki dan siap diambil di toko Soulmate Komputer.' : ($servisFound->status === 'sudah_diambil' ? 'Unit telah diambil oleh pelanggan. Terima kasih atas kepercayaan Anda.' : 'Unit sedang dalam penanganan teknisi kami.')))) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="https://wa.me/6281995466255?text={{ urlencode('Halo Admin Soulmate Komputer, saya ingin menanyakan perihal servis kode ' . ($servisFound->no_servis_frontend ?? $servisFound->no_servis)) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-colors">
                                <span>Tanya Admin</span>
                            </a>
                            <button type="button" 
                                    wire:click="resetSearch"
                                    class="px-3 py-2 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 transition-colors cursor-pointer">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 2. JEMPUT SERVISAN BANNER CARD (MATCHING IMAGE 2) -->
            <div class="bg-gradient-to-r from-[#f6f1ff] via-[#fbf4ff] to-[#fff0f8] rounded-3xl border border-purple-100/90 p-5 sm:p-7 flex flex-col md:flex-row items-center justify-between gap-5 shadow-xs">
                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- Fast Delivery Truck SVG Icon -->
                    <div class="shrink-0">
                        <svg class="w-16 h-12 sm:w-20 sm:h-14" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Speed Lines -->
                            <rect x="2" y="16" width="14" height="3" rx="1.5" fill="#d8b4fe"/>
                            <rect x="6" y="24" width="10" height="3" rx="1.5" fill="#c084fc"/>
                            <rect x="0" y="32" width="16" height="3" rx="1.5" fill="#d8b4fe"/>
                            <!-- Truck Cargo Box -->
                            <rect x="18" y="10" width="34" height="28" rx="4" fill="url(#truckBoxGrad)"/>
                            <!-- Truck Cabin -->
                            <path d="M52 18H64C66.5 18 68.7 19.5 69.6 21.8L73 30V38H52V18Z" fill="#4c1ae6"/>
                            <!-- Cabin Window -->
                            <path d="M55 21H63.5L67 28H55V21Z" fill="white" fill-opacity="0.9"/>
                            <!-- Wheels -->
                            <circle cx="30" cy="40" r="6.5" fill="#1e1b4b" stroke="white" stroke-width="2"/>
                            <circle cx="30" cy="40" r="2.5" fill="white"/>
                            <circle cx="62" cy="40" r="6.5" fill="#1e1b4b" stroke="white" stroke-width="2"/>
                            <circle cx="62" cy="40" r="2.5" fill="white"/>
                            <defs>
                                <linearGradient id="truckBoxGrad" x1="18" y1="10" x2="52" y2="38" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#9333ea"/>
                                    <stop offset="1" stop-color="#4c1ae6"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-[#0c1330] tracking-tight">
                            Jemput Servisan
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-600 mt-0.5 font-medium leading-relaxed">
                            Tidak sempat antar ke toko? Kami siap jemput perangkat Anda di rumah atau kantor dengan aman dan cepat.
                        </p>
                    </div>
                </div>

                <!-- CTA Button: Ajukan Jemput Servisan -->
                <button type="button"
                        wire:click="openJemputMode"
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2.5 px-6 sm:px-7 py-3.5 rounded-xl bg-gradient-to-r from-[#e6007a] via-[#f0007f] to-[#ff1a8c] hover:opacity-95 text-white font-bold text-xs sm:text-sm shadow-md shadow-pink-500/25 transition-all shrink-0 cursor-pointer">
                    <svg class="w-5 h-3.5 sm:w-6 sm:h-4 shrink-0" fill="currentColor" fill-rule="evenodd" viewBox="0 0 275 155">
                        <path d="M 208 96 L 201 98 L 196 101 L 189 108 L 186 113 L 184 119 L 184 132 L 186 138 L 189 143 L 196 150 L 199 152 L 208 155 L 219 155 L 226 153 L 231 150 L 238 143 L 242 135 L 243 130 L 243 120 L 242 116 L 238 108 L 231 101 L 223 97 L 219 96 Z M 211 111 L 215 111 L 218 112 L 222 114 L 225 117 L 227 120 L 228 125 L 228 127 L 226 133 L 222 137 L 219 139 L 216 140 L 211 140 L 208 139 L 206 138 L 201 133 L 200 131 L 199 128 L 199 123 L 200 120 L 202 117 L 206 113 Z M 51 96 L 44 98 L 39 101 L 33 106 L 33 107 L 31 109 L 27 118 L 27 133 L 31 142 L 34 146 L 37 149 L 43 153 L 50 155 L 62 155 L 71 152 L 75 149 L 80 144 L 84 137 L 86 130 L 86 121 L 83 112 L 79 106 L 74 101 L 69 98 L 62 96 Z M 55 111 L 58 111 L 61 112 L 65 114 L 69 119 L 71 123 L 71 126 L 70 130 L 69 132 L 65 137 L 59 140 L 53 140 L 49 138 L 43 132 L 42 129 L 42 122 L 44 118 L 49 113 Z M 1 12 L 0 109 L 3 116 L 13 124 L 17 122 L 21 107 L 30 96 L 41 89 L 52 86 L 69 88 L 80 94 L 90 105 L 94 113 L 95 122 L 98 125 L 172 125 L 181 103 L 191 93 L 209 86 L 226 88 L 236 93 L 249 108 L 254 125 L 266 125 L 275 119 L 274 60 L 243 10 L 228 0 L 13 0 Z M 195 18 L 198 15 L 224 15 L 226 16 L 228 18 L 229 18 L 233 23 L 245 42 L 246 45 L 244 47 L 242 48 L 199 48 L 197 47 L 196 46 L 195 44 Z"/>
                    </svg>
                    <span>Ajukan Jemput Servisan</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- 3. BOTTOM 4 FEATURE CARDS (MATCHING IMAGE 2) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-[#0c1330] text-xs sm:text-sm">Simpan Kode Servis</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Pastikan Anda menyimpan tanda terima servis dengan baik.</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-[#0c1330] text-xs sm:text-sm">Notifikasi Otomatis</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Anda akan dihubungi otomatis ketika servis selesai.</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-[#0c1330] text-xs sm:text-sm">Data Aman</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Informasi servis hanya dapat diakses dengan kode resmi.</p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-[#0c1330] text-xs sm:text-sm">Butuh Bantuan?</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Hubungi kami melalui WhatsApp atau datang ke bengkel kami.</p>
                    </div>
                </div>
            </div>

        @else
            <!-- ========================================================== -->
            <!-- MODE 2: AJUKAN JEMPUT SERVISAN VIEW (100% MATCHING IMAGE 1) -->
            <!-- ========================================================== -->

            <!-- 1. MAIN FORM & INFO CARD -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 lg:p-10 shadow-[0_10px_35px_rgba(12,19,48,0.06)]">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

                    <!-- LEFT COLUMN (7 COLS): FORMULIR JEMPUT SERVISAN -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <!-- Form Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
                            <div class="flex items-center gap-4">
                                <!-- Delivery Truck Icon -->
                                <div class="shrink-0">
                                    <svg class="w-16 h-12" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="2" y="16" width="14" height="3" rx="1.5" fill="#d8b4fe"/>
                                        <rect x="6" y="24" width="10" height="3" rx="1.5" fill="#c084fc"/>
                                        <rect x="0" y="32" width="16" height="3" rx="1.5" fill="#d8b4fe"/>
                                        <rect x="18" y="10" width="34" height="28" rx="4" fill="url(#truckFormGrad)"/>
                                        <path d="M52 18H64C66.5 18 68.7 19.5 69.6 21.8L73 30V38H52V18Z" fill="#4c1ae6"/>
                                        <path d="M55 21H63.5L67 28H55V21Z" fill="white" fill-opacity="0.9"/>
                                        <circle cx="30" cy="40" r="6.5" fill="#1e1b4b" stroke="white" stroke-width="2"/>
                                        <circle cx="30" cy="40" r="2.5" fill="white"/>
                                        <circle cx="62" cy="40" r="6.5" fill="#1e1b4b" stroke="white" stroke-width="2"/>
                                        <circle cx="62" cy="40" r="2.5" fill="white"/>
                                        <defs>
                                            <linearGradient id="truckFormGrad" x1="18" y1="10" x2="52" y2="38" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#9333ea"/>
                                                <stop offset="1" stop-color="#4c1ae6"/>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl sm:text-2xl font-black text-[#0c1330] tracking-tight">
                                        Formulir Jemput Servisan
                                    </h2>
                                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5 font-medium">
                                        Isi data berikut, tim kami akan segera menghubungi Anda untuk konfirmasi.
                                    </p>
                                </div>
                            </div>

                            <!-- Switch back to Cek Status Servis -->
                            <button type="button"
                                    wire:click="openCekMode"
                                    class="self-start sm:self-center inline-flex items-center gap-1.5 text-xs font-bold text-[#5E17EB] hover:underline shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                                <span>Cek Status Servis</span>
                            </button>
                        </div>

                        <!-- FORM -->
                        <form wire:submit.prevent="kirimJemputKeWA" class="space-y-4">
                            
                            <!-- Row 1: Nama Lengkap & Nomor WhatsApp -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Nama Lengkap -->
                                <div class="space-y-1.5">
                                    <label for="jemputNama" class="block text-xs sm:text-sm font-bold text-[#0c1330]">
                                        Nama Lengkap
                                    </label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3.5 text-gray-400 pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </span>
                                        <input type="text"
                                               id="jemputNama"
                                               wire:model="jemputNama"
                                               class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 py-2.5 text-xs sm:text-sm font-medium text-[#0c1330] focus:border-[#5E17EB] focus:ring-2 focus:ring-[#5E17EB]/15 outline-none transition-all @error('jemputNama') border-rose-500 @enderror">
                                        <span x-show="!$wire.jemputNama"
                                              x-cloak
                                              class="absolute left-10 text-xs sm:text-sm text-gray-400 pointer-events-none select-none">
                                            Nama lengkap Anda
                                        </span>
                                    </div>
                                    @error('jemputNama')
                                        <span class="text-rose-500 text-[11px] font-semibold block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Nomor WhatsApp -->
                                <div class="space-y-1.5">
                                    <label for="jemputWhatsapp" class="block text-xs sm:text-sm font-bold text-[#0c1330]">
                                        Nomor WhatsApp
                                    </label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3.5 text-emerald-500 pointer-events-none">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                            </svg>
                                        </span>
                                        <input type="text"
                                               id="jemputWhatsapp"
                                               wire:model="jemputWhatsapp"
                                               class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 py-2.5 text-xs sm:text-sm font-medium text-[#0c1330] focus:border-[#5E17EB] focus:ring-2 focus:ring-[#5E17EB]/15 outline-none transition-all @error('jemputWhatsapp') border-rose-500 @enderror">
                                        <span x-show="!$wire.jemputWhatsapp"
                                              x-cloak
                                              class="absolute left-10 text-xs sm:text-sm text-gray-400 pointer-events-none select-none">
                                            08xxxxxxxxxx
                                        </span>
                                    </div>
                                    @error('jemputWhatsapp')
                                        <span class="text-rose-500 text-[11px] font-semibold block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Row 2: Jenis Perangkat -->
                            <div class="space-y-1.5">
                                <label for="jemputPerangkat" class="block text-xs sm:text-sm font-bold text-[#0c1330]">
                                    Jenis Perangkat
                                </label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3.5 text-[#5E17EB] pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <input type="text"
                                           id="jemputPerangkat"
                                           wire:model="jemputPerangkat"
                                           class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 py-2.5 text-xs sm:text-sm font-medium text-[#0c1330] focus:border-[#5E17EB] focus:ring-2 focus:ring-[#5E17EB]/15 outline-none transition-all @error('jemputPerangkat') border-rose-500 @enderror">
                                    <span x-show="!$wire.jemputPerangkat"
                                          x-cloak
                                          class="absolute left-10 text-xs sm:text-sm text-gray-400 pointer-events-none select-none truncate right-4">
                                        Contoh: Laptop Asus, Printer Epson, Komputer Lenovo
                                    </span>
                                </div>
                                @error('jemputPerangkat')
                                    <span class="text-rose-500 text-[11px] font-semibold block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Row 3: Jumlah Perangkat -->
                            <div class="flex flex-wrap items-center gap-4 pt-1">
                                <label class="text-xs sm:text-sm font-bold text-[#0c1330] shrink-0">
                                    Jumlah Perangkat
                                </label>
                                <div class="inline-flex items-center rounded-xl border border-gray-200 bg-white p-1 shadow-2xs">
                                    <button type="button"
                                            wire:click="decrementJumlah"
                                            class="w-8 h-8 rounded-lg bg-purple-50 hover:bg-purple-100 text-[#5E17EB] font-black text-base flex items-center justify-center transition-colors cursor-pointer">
                                        &minus;
                                    </button>
                                    <span class="w-10 text-center font-black text-sm text-[#0c1330]">
                                        {{ $jemputJumlah }}
                                    </span>
                                    <button type="button"
                                            wire:click="incrementJumlah"
                                            class="w-8 h-8 rounded-lg bg-purple-50 hover:bg-purple-100 text-[#5E17EB] font-black text-base flex items-center justify-center transition-colors cursor-pointer">
                                        &#43;
                                    </button>
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-gray-400 font-medium">
                                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Masukkan jumlah perangkat yang akan dijemput.</span>
                                </div>
                            </div>

                            <!-- Row 4: Keluhan / Keterangan (Moved to top of Alamat) -->
                            <div class="space-y-1.5">
                                <label for="jemputKeluhan" class="block text-xs sm:text-sm font-bold text-[#0c1330]">
                                    Keluhan / Keterangan
                                </label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3.5 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </span>
                                    <input type="text"
                                           id="jemputKeluhan"
                                           wire:model="jemputKeluhan"
                                           class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 py-3 text-xs sm:text-sm font-medium text-[#0c1330] focus:border-[#5E17EB] focus:ring-2 focus:ring-[#5E17EB]/15 outline-none transition-all @error('jemputKeluhan') border-rose-500 @enderror">
                                    <span x-show="!$wire.jemputKeluhan"
                                          x-cloak
                                          class="absolute left-10 text-xs sm:text-sm text-gray-400 pointer-events-none select-none truncate right-4">
                                        Jelaskan kerusakan atau keluhan perangkat Anda
                                    </span>
                                </div>
                                @error('jemputKeluhan')
                                    <span class="text-rose-500 text-[11px] font-semibold block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Row 5: Alamat Jemput -->
                            <div class="space-y-1.5">
                                <label for="jemputAlamat" class="block text-xs sm:text-sm font-bold text-[#0c1330]">
                                    Alamat Jemput
                                </label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3.5 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </span>
                                    <input type="text"
                                           id="jemputAlamat"
                                           wire:model="jemputAlamat"
                                           class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-36 py-3 text-xs sm:text-sm font-medium text-[#0c1330] focus:border-[#5E17EB] focus:ring-2 focus:ring-[#5E17EB]/15 outline-none transition-all @error('jemputAlamat') border-rose-500 @enderror">
                                    <span x-show="!$wire.jemputAlamat"
                                          x-cloak
                                          class="absolute left-10 text-xs sm:text-sm text-gray-400 pointer-events-none select-none truncate right-36">
                                        Nama Kantor atau Alamat Lengkap
                                    </span>
                                    <!-- Pilih di Maps Button -->
                                    <button type="button"
                                            @click="ambilLokasiMaps()"
                                            class="absolute right-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 text-[11px] font-bold text-[#0c1330] shadow-2xs transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                        <span x-text="locatingMaps ? 'Mencari...' : 'Pilih di Maps'">Pilih di Maps</span>
                                    </button>
                                </div>
                                <template x-if="mapsStatus">
                                    <p class="text-[11px] font-semibold text-emerald-600" x-text="mapsStatus"></p>
                                </template>
                                @error('jemputAlamat')
                                    <span class="text-rose-500 text-[11px] font-semibold block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Row 6: Pilih Jadwal Jemput -->
                            <div class="space-y-2 pt-1">
                                <label class="block text-xs sm:text-sm font-bold text-[#0c1330]">
                                    Pilih Jadwal Jemput
                                </label>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <!-- Option 1: Segera -->
                                    <div wire:click="$set('jemputJadwalTipe', 'segera')"
                                         class="sm:col-span-5 rounded-2xl p-3.5 cursor-pointer transition-all flex items-start gap-2.5 {{ $jemputJadwalTipe === 'segera' ? 'border-2 border-[#5E17EB] bg-[#f8f5ff]' : 'border border-gray-200 bg-white hover:bg-gray-50' }}">
                                        <!-- Radio Indicator -->
                                        <div class="mt-0.5 w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 {{ $jemputJadwalTipe === 'segera' ? 'border-[#5E17EB]' : 'border-gray-300' }}">
                                            @if($jemputJadwalTipe === 'segera')
                                                <div class="w-2 h-2 rounded-full bg-[#5E17EB]"></div>
                                            @endif
                                        </div>
                                        <!-- Lightning Icon -->
                                        <svg class="w-4 h-4 text-[#5E17EB] shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <span class="block text-xs font-black text-[#0c1330]">Segera</span>
                                            <span class="block text-[11px] text-gray-500 leading-snug mt-0.5">Secepatnya (Hari ini jika memungkinkan)</span>
                                        </div>
                                    </div>

                                    <!-- Option 2: Pilih Tanggal & Jam -->
                                    <div wire:click="$set('jemputJadwalTipe', 'terjadwal')"
                                         class="sm:col-span-7 rounded-2xl p-3.5 cursor-pointer transition-all {{ $jemputJadwalTipe === 'terjadwal' ? 'border-2 border-[#5E17EB] bg-[#f8f5ff]' : 'border border-gray-200 bg-white hover:bg-gray-50' }}">
                                        <div class="flex items-center gap-2 mb-2.5">
                                            <!-- Radio Indicator -->
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 {{ $jemputJadwalTipe === 'terjadwal' ? 'border-[#5E17EB]' : 'border-gray-300' }}">
                                                @if($jemputJadwalTipe === 'terjadwal')
                                                    <div class="w-2 h-2 rounded-full bg-[#5E17EB]"></div>
                                                @endif
                                            </div>
                                            <svg class="w-4 h-4 text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs font-black text-[#0c1330]">Pilih Tanggal &amp; Jam</span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2" @click.stop="$wire.set('jemputJadwalTipe', 'terjadwal')">
                                            <!-- Pilih Tanggal -->
                                            <div>
                                                <input type="date"
                                                       wire:model="jemputTanggal"
                                                       min="{{ now()->toDateString() }}"
                                                       aria-label="Pilih Tanggal Jemput"
                                                       class="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-gray-700 focus:border-[#5E17EB] outline-none">
                                            </div>
                                            <!-- Pilih Waktu -->
                                            <div>
                                                <select wire:model="jemputWaktu"
                                                        aria-label="Pilih Waktu Jemput"
                                                        class="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-gray-700 focus:border-[#5E17EB] outline-none">
                                                    <option value="">Pilih waktu</option>
                                                    <option value="09:00 - 11:00 WIB">09:00 - 11:00</option>
                                                    <option value="11:00 - 13:00 WIB">11:00 - 13:00</option>
                                                    <option value="13:00 - 15:00 WIB">13:00 - 15:00</option>
                                                    <option value="15:00 - 17:00 WIB">15:00 - 17:00</option>
                                                    <option value="17:00 - 19:00 WIB">17:00 - 19:00</option>
                                                </select>
                                            </div>
                                        </div>
                                        @error('jemputTanggal')
                                            <span class="text-rose-500 text-[10px] font-semibold block mt-1">{{ $message }}</span>
                                        @enderror
                                        @error('jemputWaktu')
                                            <span class="text-rose-500 text-[10px] font-semibold block mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Row 7: Submit Button -->
                            <div class="pt-2">
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="kirimJemputKeWA"
                                        class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-[#e6007a] via-[#f0007f] to-[#ff1a8c] hover:opacity-95 text-white font-bold text-sm shadow-lg shadow-pink-500/25 flex items-center justify-center gap-2.5 transition-all cursor-pointer disabled:opacity-50">
                                    <span wire:loading.remove wire:target="kirimJemputKeWA" class="inline-flex items-center gap-2.5">
                                        <svg class="w-5 h-3.5 sm:w-6 sm:h-4 shrink-0" fill="currentColor" fill-rule="evenodd" viewBox="0 0 275 155">
                                            <path d="M 208 96 L 201 98 L 196 101 L 189 108 L 186 113 L 184 119 L 184 132 L 186 138 L 189 143 L 196 150 L 199 152 L 208 155 L 219 155 L 226 153 L 231 150 L 238 143 L 242 135 L 243 130 L 243 120 L 242 116 L 238 108 L 231 101 L 223 97 L 219 96 Z M 211 111 L 215 111 L 218 112 L 222 114 L 225 117 L 227 120 L 228 125 L 228 127 L 226 133 L 222 137 L 219 139 L 216 140 L 211 140 L 208 139 L 206 138 L 201 133 L 200 131 L 199 128 L 199 123 L 200 120 L 202 117 L 206 113 Z M 51 96 L 44 98 L 39 101 L 33 106 L 33 107 L 31 109 L 27 118 L 27 133 L 31 142 L 34 146 L 37 149 L 43 153 L 50 155 L 62 155 L 71 152 L 75 149 L 80 144 L 84 137 L 86 130 L 86 121 L 83 112 L 79 106 L 74 101 L 69 98 L 62 96 Z M 55 111 L 58 111 L 61 112 L 65 114 L 69 119 L 71 123 L 71 126 L 70 130 L 69 132 L 65 137 L 59 140 L 53 140 L 49 138 L 43 132 L 42 129 L 42 122 L 44 118 L 49 113 Z M 1 12 L 0 109 L 3 116 L 13 124 L 17 122 L 21 107 L 30 96 L 41 89 L 52 86 L 69 88 L 80 94 L 90 105 L 94 113 L 95 122 L 98 125 L 172 125 L 181 103 L 191 93 L 209 86 L 226 88 L 236 93 L 249 108 L 254 125 L 266 125 L 275 119 L 274 60 L 243 10 L 228 0 L 13 0 Z M 195 18 L 198 15 L 224 15 L 226 16 L 228 18 L 229 18 L 233 23 L 245 42 L 246 45 L 244 47 L 242 48 L 199 48 L 197 47 L 196 46 L 195 44 Z"/>
                                        </svg>
                                        <span>Ajukan Jemput Servisan</span>
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="kirimJemputKeWA" class="inline-flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span>Mengarahkan ke WhatsApp...</span>
                                    </span>
                                </button>
                            </div>

                        </form>
                    </div>

                    <!-- RIGHT COLUMN (5 COLS): VAN IMAGE, 4 FEATURES, CATATAN PENTING -->
                    <div class="lg:col-span-5 space-y-4">
                        
                        <!-- 1. Soulmate Delivery Van Photo -->
                        <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-xs">
                            <img src="{{ asset('images/mobil_jemput_new.jpeg') }}" 
                                 alt="Armada Jemput Servisan Soulmate Komputer" 
                                 class="w-full h-auto object-cover">
                        </div>

                        <!-- 2. 2x2 Feature Mini Cards -->
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Aman & Terpercaya -->
                            <div class="bg-[#fcfaff] rounded-2xl border border-purple-100/80 p-3.5 flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-[#0c1330] text-xs leading-snug">Aman &amp; Terpercaya</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">Perangkat dijemput dengan aman oleh tim kami.</p>
                                </div>
                            </div>

                            <!-- Jadwal Fleksibel -->
                            <div class="bg-[#fcfaff] rounded-2xl border border-purple-100/80 p-3.5 flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-[#0c1330] text-xs leading-snug">Jadwal Fleksibel</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">Pilih waktu jemput sesuai kenyamanan Anda.</p>
                                </div>
                            </div>

                            <!-- Area Pangkalpinang -->
                            <div class="bg-[#fcfaff] rounded-2xl border border-purple-100/80 p-3.5 flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-[#0c1330] text-xs leading-snug">Area Pangkalpinang</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">dan sekitarnya.</p>
                                </div>
                            </div>

                            <!-- Gratis Jemput -->
                            <div class="bg-[#fcfaff] rounded-2xl border border-purple-100/80 p-3.5 flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-[#0c1330] text-xs leading-snug">Gratis Jemput</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">Syarat berlaku*</p>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Catatan Penting Box -->
                        <div class="bg-[#f8f5ff] border border-purple-100 rounded-2xl p-4 sm:p-5">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="w-5 h-5 rounded-full bg-[#5E17EB] text-white flex items-center justify-center text-xs font-black shrink-0">
                                    i
                                </div>
                                <h4 class="font-extrabold text-[#5E17EB] text-xs sm:text-sm">Catatan Penting</h4>
                            </div>
                            <ul class="space-y-1.5 text-[11px] text-gray-600 font-medium pl-1">
                                <li class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 shrink-0"></span>
                                    <span>Tim kami akan menghubungi Anda melalui WhatsApp untuk konfirmasi jadwal jemput.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 shrink-0"></span>
                                    <span>Pastikan perangkat dikemas dengan aman.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 shrink-0"></span>
                                    <span>Layanan jemput hanya untuk area Pangkalpinang dan sekitarnya.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 shrink-0"></span>
                                    <span>Gratis jemput dengan syarat tertentu.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 shrink-0"></span>
                                    <span>Untuk pertanyaan lebih lanjut, silakan hubungi <strong class="text-[#0c1330] font-black">0819-9546-6255.</strong></span>
                                </li>
                            </ul>
                        </div>

                    </div>

                </div>
            </div>

            <!-- 2. BOTTOM STEPPER BAR: PROSES JEMPUT SERVISAN (MATCHING IMAGE 1) -->
            <div class="bg-white rounded-2xl border border-gray-100 px-6 py-5 shadow-xs">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
                    <h4 class="font-black text-[#0c1330] text-sm shrink-0">
                        Proses Jemput Servisan
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-center lg:justify-between gap-4 w-full">
                        <!-- Step 1 -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-[#0c1330] text-xs">Ajukan Jemput</h5>
                                <p class="text-[10px] text-gray-500">Isi formulir online</p>
                            </div>
                        </div>

                        <div class="hidden lg:block text-gray-300">&rarr;</div>

                        <!-- Step 2 -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-[#0c1330] text-xs">Konfirmasi</h5>
                                <p class="text-[10px] text-gray-500">Tim kami hubungi via WhatsApp</p>
                            </div>
                        </div>

                        <div class="hidden lg:block text-gray-300">&rarr;</div>

                        <!-- Step 3 -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-[#0c1330] text-xs">Penjemputan</h5>
                                <p class="text-[10px] text-gray-500">Tim kami jemput perangkat</p>
                            </div>
                        </div>

                        <div class="hidden lg:block text-gray-300">&rarr;</div>

                        <!-- Step 4 -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-[#0c1330] text-xs">Proses Servis</h5>
                                <p class="text-[10px] text-gray-500">Perangkat masuk ke teknisi</p>
                            </div>
                        </div>

                        <div class="hidden lg:block text-gray-300">&rarr;</div>

                        <!-- Step 5 -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#f3efff] text-[#5E17EB] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-[#0c1330] text-xs">Selesai</h5>
                                <p class="text-[10px] text-gray-500">Perangkat siap diambil / dikirim kembali</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

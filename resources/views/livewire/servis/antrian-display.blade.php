<div class="relative h-screen w-screen max-w-full max-h-screen bg-white flex flex-col justify-between overflow-hidden select-none"
     wire:poll.5s
     x-data="{
        isFullscreen: false,
        currentTime: '',
        currentDate: '',
        isCollapsed: false,
        numberScale: parseInt(localStorage.getItem('antrian_number_scale') || '100'),
        calcSize(digits) {
            const base = digits >= 3 ? 4.75 : (digits === 2 ? 5.75 : 6.75);
            return (base * (this.numberScale / 100)).toFixed(2) + 'rem';
        },
        updateTime() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            this.currentDate = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
        },
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
                this.isFullscreen = true;
            } else {
                document.exitFullscreen().catch(() => {});
                this.isFullscreen = false;
            }
        }
     }"
     x-init="
        updateTime();
        setInterval(() => updateTime(), 1000);
        document.addEventListener('fullscreenchange', () => {
            isFullscreen = !!document.fullscreenElement;
        });
     "
>
    <!-- Background Corner Decorative Accents (Diagonal vibrant modern stripes matching Mockup) -->
    <!-- Top-Left Corner -->
    <div class="absolute -top-6 -left-6 w-72 h-72 md:w-96 md:h-96 pointer-events-none opacity-45 overflow-hidden z-0">
        <svg viewBox="0 0 200 200" class="w-full h-full">
            <defs>
                <linearGradient id="gradTL1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#5E17EB" stop-opacity="0.8"/>
                    <stop offset="100%" stop-color="#0052FF" stop-opacity="0.2"/>
                </linearGradient>
                <linearGradient id="gradTL2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#BA007C" stop-opacity="0.7"/>
                    <stop offset="100%" stop-color="#5E17EB" stop-opacity="0.2"/>
                </linearGradient>
            </defs>
            <polygon points="0,0 75,0 0,75" fill="url(#gradTL1)"/>
            <polygon points="0,90 90,0 115,0 0,115" fill="url(#gradTL2)"/>
            <polygon points="0,130 130,0 155,0 0,155" fill="url(#gradTL1)"/>
            <polygon points="0,170 170,0 195,0 0,195" fill="url(#gradTL2)"/>
        </svg>
    </div>

    <!-- Top-Right Corner -->
    <div class="absolute -top-6 -right-6 w-72 h-72 md:w-96 md:h-96 pointer-events-none opacity-45 overflow-hidden z-0">
        <svg viewBox="0 0 200 200" class="w-full h-full">
            <defs>
                <linearGradient id="gradTR1" x1="100%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#5E17EB" stop-opacity="0.8"/>
                    <stop offset="100%" stop-color="#BA007C" stop-opacity="0.2"/>
                </linearGradient>
            </defs>
            <polygon points="200,0 125,0 200,75" fill="url(#gradTR1)"/>
            <polygon points="200,90 110,0 85,0 200,115" fill="url(#gradTR1)"/>
            <polygon points="200,130 70,0 45,0 200,155" fill="url(#gradTR1)"/>
            <polygon points="200,170 30,0 5,0 200,195" fill="url(#gradTR1)"/>
        </svg>
    </div>

    <!-- Bottom-Left Corner -->
    <div class="absolute -bottom-6 -left-6 w-64 h-64 md:w-80 md:h-80 pointer-events-none opacity-30 overflow-hidden z-0">
        <svg viewBox="0 0 200 200" class="w-full h-full">
            <polygon points="0,200 65,200 0,135" fill="#5E17EB"/>
            <polygon points="0,110 90,200 115,200 0,85" fill="#BA007C"/>
            <polygon points="0,60 140,200 165,200 0,35" fill="#0052FF"/>
        </svg>
    </div>

    <!-- Bottom-Right Corner -->
    <div class="absolute -bottom-6 -right-6 w-64 h-64 md:w-80 md:h-80 pointer-events-none opacity-30 overflow-hidden z-0">
        <svg viewBox="0 0 200 200" class="w-full h-full">
            <polygon points="200,200 135,200 200,135" fill="#BA007C"/>
            <polygon points="200,110 110,200 85,200 200,85" fill="#5E17EB"/>
            <polygon points="200,60 60,200 35,200 200,35" fill="#BA007C"/>
        </svg>
    </div>

    <!-- Floating Number Size Slider & Admin Controls (Bottom Right) -->
    <div class="fixed bottom-3 right-4 z-50 transition-all duration-300">
        
        <!-- Collapsed Mini Pill Button -->
        <button x-show="isCollapsed" 
                @click="isCollapsed = false" 
                class="bg-white/95 backdrop-blur-md hover:bg-white text-gray-700 hover:text-[#5E17EB] px-3 py-2 rounded-2xl border border-gray-200/90 shadow-xl flex items-center gap-2 text-xs font-bold transition-all">
            <svg class="w-4 h-4 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <span>Ukuran Angka: <span class="font-mono text-[#5E17EB]" x-text="numberScale + '%'"></span></span>
        </button>

        <!-- Expanded Full Control Bar with Interactive Slider -->
        <div x-show="!isCollapsed" 
             class="bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-2xl border border-gray-200/90 shadow-2xl flex items-center gap-3 text-xs font-bold text-gray-700 transition-all">
            
            <!-- Number Scale Slider -->
            <div class="flex items-center gap-2 pl-0.5">
                <div class="flex items-center gap-1.5 text-[#5E17EB]">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-700">Ukuran:</span>
                </div>

                <!-- Range Slider (50% to 150%) -->
                <input type="range" 
                       min="50" 
                       max="150" 
                       step="5" 
                       x-model="numberScale" 
                       @input="localStorage.setItem('antrian_number_scale', numberScale)"
                       class="w-24 sm:w-32 lg:w-40 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#5E17EB]"
                       title="Geser slider untuk memperbesar / memperkecil ukuran angka antrian">

                <!-- Value Badge -->
                <span class="font-mono text-xs font-black text-[#5E17EB] bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 w-12 text-center" 
                      x-text="numberScale + '%'"></span>

                <!-- Quick Reset to 100% -->
                <button type="button" 
                        @click="numberScale = 100; localStorage.setItem('antrian_number_scale', 100)" 
                        title="Kembalikan ukuran ke 100%"
                        class="text-[10px] text-gray-400 hover:text-[#5E17EB] font-bold underline transition-colors cursor-pointer">
                    Reset
                </button>
            </div>

            <span class="h-4 w-[1px] bg-gray-200"></span>

            <!-- Toggle Demo vs Real -->
            <button wire:click="toggleDemo" 
                    title="Ganti angka demo vs riil database"
                    class="px-2 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer {{ $useDemoNumbers ? 'bg-purple-100 text-purple-700' : 'text-gray-600 hover:text-gray-900' }}">
                {{ $useDemoNumbers ? 'Demo: 3,5,12' : 'Real DB' }}
            </button>

            <!-- Fullscreen Button -->
            <button @click="toggleFullscreen" 
                    class="p-1 rounded-lg text-gray-600 hover:text-purple-600 transition-colors cursor-pointer"
                    title="Layar Penuh (F11)">
                <svg x-show="!isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                <svg x-show="isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- Minimize Button -->
            <button type="button" 
                    @click="isCollapsed = true" 
                    class="text-gray-400 hover:text-gray-700 p-0.5 cursor-pointer" 
                    title="Perkecil panel kontrol">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>
    </div>

    <!-- 1. TOP HEADER SECTION (OPTIMIZED FOR 1920x1080p FULL VIEW) -->
    <header class="relative z-10 w-full shrink-0 pt-3 md:pt-4 lg:pt-5 pb-2 px-6 md:px-10 lg:px-14">
        <div class="max-w-[1840px] w-full mx-auto flex items-center justify-between">
            <!-- Top Left: Logo Soulmate Komputer + Rose Vertical Divider -->
            <div class="flex items-center gap-3 lg:gap-5 shrink-0">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="Soulmate Komputer" 
                     class="h-12 md:h-14 lg:h-16 xl:h-20 w-auto object-contain filter drop-shadow-sm">

                <!-- Vertical Divider Line in Rose Pink -->
                <div class="h-11 md:h-13 lg:h-16 w-[3.5px] bg-rose-500 rounded-full mx-1 lg:mx-2"></div>
            </div>

            <!-- Top Center: ANTRIAN SERVIS -->
            <div class="flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-black text-[#0A0E27] tracking-tight uppercase leading-none font-sans">
                    ANTRIAN SERVIS
                </h1>
                <p class="text-[11px] sm:text-xs md:text-sm lg:text-base xl:text-lg font-extrabold text-gray-700 tracking-[0.3em] md:tracking-[0.4em] uppercase mt-1 md:mt-2">
                    CEPAT &bull; TRANSPARAN &bull; TERPERCAYA
                </p>
            </div>

            <!-- Top Right: Vertical Divider + SOLUSI KOMPUTER TERPERCAYA -->
            <div class="flex items-center gap-3 lg:gap-5 shrink-0">
                <!-- Vertical Divider Line in Rose Pink / Purple -->
                <div class="h-11 md:h-13 lg:h-16 w-[3.5px] bg-rose-500 rounded-full mx-1 lg:mx-2"></div>

                <div class="flex flex-col justify-center text-left leading-tight">
                    <span class="text-xs md:text-sm lg:text-base xl:text-lg font-black text-[#0A0E27] tracking-wider uppercase">
                        SOLUSI
                    </span>
                    <span class="text-xs md:text-sm lg:text-base xl:text-lg font-black text-[#0A0E27] tracking-wider uppercase">
                        KOMPUTER
                    </span>
                    <span class="text-xs md:text-sm lg:text-base xl:text-lg font-black text-[#0A0E27] tracking-wider uppercase">
                        TERPERCAYA
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- 2. MAIN CONTENT (FULL-WIDTH 1080p CARDS WITH REAL-TIME SLIDER CONTROLLED NUMBERS) -->
    <main class="relative z-10 w-full px-4 md:px-6 lg:px-10 py-2 md:py-3 flex-1 min-h-0 flex flex-col justify-center">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-7 items-stretch w-full max-w-[1840px] mx-auto h-full max-h-full">
            
            <!-- CARD 1: LAPTOP -->
            <div class="bg-white rounded-2xl md:rounded-3xl border-2 border-gray-200/90 shadow-xl overflow-hidden flex flex-col h-full justify-between transition-all duration-300 hover:shadow-2xl hover:border-blue-300">
                <!-- Header Banner LAPTOP -->
                <div class="bg-[#0028C8] px-4 py-3 md:py-3.5 lg:py-4 flex items-center justify-center gap-3 text-white shadow-inner shrink-0">
                    <svg class="w-8 h-8 md:w-9 md:h-9 lg:w-11 lg:h-11 text-white shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9H4V5zm2 1v6h12V6H6zM1 17a1 1 0 0 1 1-1h20a1 1 0 0 1 1 1v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1z"/>
                    </svg>
                    <span class="text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black tracking-wider uppercase text-white font-sans">
                        LAPTOP
                    </span>
                </div>

                <!-- 3 Sub-Columns: PENGECEKAN | PERBAIKAN | SELESAI -->
                <div class="grid grid-cols-3 divide-x-2 divide-gray-200/90 flex-1 min-h-0 bg-white">
                    <!-- 1. Pengecekan -->
                    <div wire:click="showList('laptop', 'pengecekan')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-blue-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                PENGECEKAN
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['laptop']['pengecekan']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#0052FF] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. Perbaikan -->
                    <div wire:click="showList('laptop', 'perbaikan')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-pink-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                PERBAIKAN
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['laptop']['perbaikan']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#BA007C] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>

                    <!-- 3. Selesai -->
                    <div wire:click="showList('laptop', 'selesai')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-emerald-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                SELESAI
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['laptop']['selesai']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#00882B] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 2: PRINTER -->
            <div class="bg-white rounded-2xl md:rounded-3xl border-2 border-gray-200/90 shadow-xl overflow-hidden flex flex-col h-full justify-between transition-all duration-300 hover:shadow-2xl hover:border-pink-300">
                <!-- Header Banner PRINTER -->
                <div class="bg-[#D80064] px-4 py-3 md:py-3.5 lg:py-4 flex items-center justify-center gap-3 text-white shadow-inner shrink-0">
                    <svg class="w-8 h-8 md:w-9 md:h-9 lg:w-11 lg:h-11 text-white shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v4H6V3zm12 5H6a3 3 0 0 0-3 3v6a1 1 0 0 0 1 1h2v-3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v3h2a1 1 0 0 0 1-1v-6a3 3 0 0 0-3-3zm-1 3.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2zM8 16v5a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-5H8z"/>
                    </svg>
                    <span class="text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black tracking-wider uppercase text-white font-sans">
                        PRINTER
                    </span>
                </div>

                <!-- 3 Sub-Columns: PENGECEKAN | PERBAIKAN | SELESAI -->
                <div class="grid grid-cols-3 divide-x-2 divide-gray-200/90 flex-1 min-h-0 bg-white">
                    <!-- 1. Pengecekan -->
                    <div wire:click="showList('printer', 'pengecekan')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-blue-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                PENGECEKAN
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['printer']['pengecekan']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#0052FF] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. Perbaikan -->
                    <div wire:click="showList('printer', 'perbaikan')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-pink-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                PERBAIKAN
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['printer']['perbaikan']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#BA007C] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>

                    <!-- 3. Selesai -->
                    <div wire:click="showList('printer', 'selesai')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-emerald-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                SELESAI
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['printer']['selesai']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#00882B] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 3: KOMPUTER -->
            <div class="bg-white rounded-2xl md:rounded-3xl border-2 border-gray-200/90 shadow-xl overflow-hidden flex flex-col h-full justify-between transition-all duration-300 hover:shadow-2xl hover:border-slate-400">
                <!-- Header Banner KOMPUTER -->
                <div class="bg-[#0C1330] px-4 py-3 md:py-3.5 lg:py-4 flex items-center justify-center gap-3 text-white shadow-inner shrink-0">
                    <svg class="w-8 h-8 md:w-9 md:h-9 lg:w-11 lg:h-11 text-white shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="5.5" height="18" rx="1" fill="currentColor" fill-opacity="0.15"></rect>
                        <line x1="4.5" y1="6" x2="5" y2="6"></line>
                        <line x1="4" y1="9" x2="5.5" y2="9"></line>
                        <circle cx="4.75" cy="18" r="0.75" fill="currentColor"></circle>
                        <rect x="9.5" y="3" width="12.5" height="12" rx="1.5" fill="currentColor" fill-opacity="0.15"></rect>
                        <line x1="15.75" y1="15" x2="15.75" y2="19"></line>
                        <line x1="12" y1="19" x2="19.5" y2="19"></line>
                    </svg>
                    <span class="text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black tracking-wider uppercase text-white font-sans">
                        KOMPUTER
                    </span>
                </div>

                <!-- 3 Sub-Columns: PENGECEKAN | PERBAIKAN | SELESAI -->
                <div class="grid grid-cols-3 divide-x-2 divide-gray-200/90 flex-1 min-h-0 bg-white">
                    <!-- 1. Pengecekan -->
                    <div wire:click="showList('komputer', 'pengecekan')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-blue-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                PENGECEKAN
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['komputer']['pengecekan']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#0052FF] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. Perbaikan -->
                    <div wire:click="showList('komputer', 'perbaikan')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-pink-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                PERBAIKAN
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['komputer']['perbaikan']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#BA007C] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>

                    <!-- 3. Selesai -->
                    <div wire:click="showList('komputer', 'selesai')"
                         class="flex flex-col justify-between cursor-pointer group hover:bg-emerald-50/40 transition-colors overflow-hidden">
                        <div class="text-center py-2.5 md:py-3 px-1 border-b-2 border-gray-200/90 shrink-0 overflow-hidden">
                            <span class="text-xs sm:text-sm md:text-sm lg:text-[15px] xl:text-base font-black text-gray-900 tracking-normal sm:tracking-tight md:tracking-normal uppercase block truncate">
                                SELESAI
                            </span>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center py-2 md:py-3 px-1 overflow-hidden">
                            @php $val = $counts['komputer']['selesai']; @endphp
                            <span :style="{ fontSize: calcSize({{ strlen((string)$val) }}) }"
                                  class="font-black text-[#00882B] tabular-nums tracking-tighter leading-none group-hover:scale-105 transition-transform max-w-full truncate text-center">
                                {{ $val }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- 3. BOTTOM FOOTER BANNER (ENLARGED & PROMINENT CCTV BANNER FOR SMART TV 1920x1080p) -->
    <footer class="relative z-10 w-full shrink-0 pb-3 md:pb-4 lg:pb-5 px-4 md:px-6 lg:px-10">
        <div class="max-w-[1840px] w-full mx-auto">
            <div class="relative w-full rounded-2xl md:rounded-3xl bg-gradient-to-r from-[#1E0538] via-[#5E17EB] to-[#E6007A] text-white py-4 md:py-5 lg:py-6 px-6 md:px-12 lg:px-16 flex items-center justify-center shadow-2xl overflow-hidden">
                
                <!-- Subtle diagonal light flare -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent skew-x-12 pointer-events-none"></div>

                <div class="relative z-10 flex items-center justify-center gap-6 md:gap-10 lg:gap-14 w-full">
                    <!-- CCTV Camera Icon matching Mockup (Large & Prominent) -->
                    <div class="shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 md:w-12 md:h-12 lg:w-14 lg:h-14 xl:w-16 xl:h-16 text-white shrink-0 filter drop-shadow-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16.75 12h3.632a1 1 0 0 1 .894 1.447l-2.034 4.069a1 1 0 0 1-1.708.134l-2.124-2.97"/>
                            <path d="M17.106 9.053a1 1 0 0 1 .447 1.341l-3.106 6.211a1 1 0 0 1-1.342.447L3.61 12.3a2.92 2.92 0 0 1-1.3-3.91L3.69 5.6a2.92 2.92 0 0 1 3.92-1.3z"/>
                            <path d="M2 19h3.76a2 2 0 0 0 1.8-1.1L9 15"/>
                            <path d="M2 21v-4"/>
                            <path d="M7 9h.01"/>
                        </svg>
                    </div>

                    <!-- Vertical White Divider -->
                    <div class="h-10 md:h-12 lg:h-14 w-[3.5px] bg-white/70 rounded-full"></div>

                    <!-- Banner Text (Larger & Commandingly Bold for Smart TV) -->
                    <h2 class="text-xl md:text-2xl lg:text-3xl xl:text-[2.25rem] 2xl:text-[2.5rem] font-black tracking-[0.16em] lg:tracking-[0.22em] uppercase font-sans text-center filter drop-shadow-md leading-none">
                        PENGERJAAN SERVIS DIAWASI CCTV
                    </h2>

                    <!-- Vertical White Divider -->
                    <div class="h-10 md:h-12 lg:h-14 w-[3.5px] bg-white/70 rounded-full"></div>

                    <!-- Shield with Checkmark Icon matching Mockup (Large & Prominent) -->
                    <div class="shrink-0 flex items-center justify-center">
                        <svg class="w-10 h-10 md:w-12 md:h-12 lg:w-14 lg:h-14 xl:w-16 xl:h-16 text-white shrink-0 filter drop-shadow-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- MODAL DETAIL DAFTAR TIKET (SAAT NOMOR DIKLIK) -->
    @if($modalListOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full border border-gray-100 overflow-hidden"
             @click.away="$wire.closeModal()">
            
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-gray-900 to-purple-950 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black uppercase tracking-wider">
                        Daftar Unit: {{ strtoupper($selectedCategory) }} &bull; {{ strtoupper($selectedStatus) }}
                    </h3>
                    <p class="text-xs text-purple-200 mt-0.5">
                        Menampilkan hingga 20 unit teratas yang sedang aktif di bengkel
                    </p>
                </div>
                <button wire:click="closeModal" class="text-white/80 hover:text-white p-1 rounded-xl hover:bg-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Body (Unit List) -->
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                @if($unitList->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm font-bold text-gray-500">Tidak ada tiket dalam antrian ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($unitList as $unit)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 text-xs font-mono font-bold bg-purple-50 text-purple-700 rounded-lg border border-purple-200">
                                        {{ $unit->no_servis_frontend ?? $unit->no_servis }}
                                    </span>
                                    <h4 class="text-sm font-bold text-gray-900 mt-1">
                                        {{ $unit->tipe_unit ?? 'Unit ' . ucfirst($unit->kategori_perangkat) }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                                        Keluhan: {{ $unit->keluhan ?: '-' }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-xs font-bold text-gray-700 block">
                                        {{ $unit->customer?->nama ?? 'Umum' }}
                                    </span>
                                    <span class="text-[11px] text-gray-400 block mt-0.5">
                                        {{ $unit->tanggal_masuk?->translatedFormat('d M Y') ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button wire:click="closeModal" 
                        class="px-5 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 transition-colors shadow-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<div x-data="{
        fwInstance: null,
        showClosing: false,
        init() {
            this.$nextTick(() => {
                this.startFireworks();
                setTimeout(() => {
                    this.showClosing = true;
                }, 2200);
            });
        },
        startFireworks() {
            const container = this.$refs.fwFullscreen;
            if (!container || !window.Fireworks) return;

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            this.fwInstance = new window.Fireworks(container, {
                autoresize: true,
                opacity: 0.85,
                acceleration: 1.04,
                friction: 0.97,
                gravity: 1.5,
                particles: reducedMotion ? 25 : 85,
                traceLength: 3,
                traceSpeed: 10,
                explosion: 6,
                intensity: reducedMotion ? 12 : 38,
                flickering: 50,
                lineStyle: 'round',
                hue: { min: 0, max: 360 },
                delay: { min: reducedMotion ? 50 : 15, max: reducedMotion ? 90 : 35 },
                rocketsPoint: { min: 50, max: 50 },
                lineWidth: { explosion: { min: 1, max: 4 }, trace: { min: 1, max: 2 } },
                brightness: { min: 50, max: 80 },
                decay: { min: 0.015, max: 0.03 },
                mouse: { click: true, move: false, max: 2 }
            });

            this.fwInstance.start();
        },
        destroy() {
            if (this.fwInstance) {
                this.fwInstance.stop(true);
                this.fwInstance = null;
            }
        }
     }"
     class="relative min-h-screen w-full bg-black flex flex-col justify-between items-center p-6 sm:p-10 overflow-hidden select-none">

    <!-- Canvas Fireworks Layar Penuh -->
    <div x-ref="fwFullscreen" class="fixed inset-0 pointer-events-none z-0"></div>

    <!-- Tombol Kembali ke Aplikasi (Pojok Kiri Atas - Glass Style) -->
    <div class="fixed top-5 left-5 z-50 pointer-events-auto">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 active:bg-white/25 backdrop-blur-md border border-white/20 text-white text-xs sm:text-sm font-semibold tracking-wide transition shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>&larr; Kembali ke aplikasi</span>
        </a>
    </div>

    <!-- Top Spacing -->
    <div class="h-12 sm:h-16"></div>

    <!-- Konten Utama Tengah (Z-Index di Atas Canvas, Center Aligned) -->
    <div class="relative z-10 max-w-4xl w-full mx-auto text-center flex flex-col items-center justify-center my-auto pointer-events-none">

        <!-- Spaced Uppercase Subtitle -->
        <p class="text-xs sm:text-sm md:text-base uppercase tracking-[0.35em] font-bold text-amber-400 drop-shadow-[0_0_12px_rgba(251,191,36,0.8)] animate-pulse mb-3 sm:mb-4">
            ✦ HARI YANG ISTIMEWA ✦
        </p>

        <!-- Judul Raksasa Gradasi Emas-Pink-Ungu dengan Glow -->
        <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-pink-400 to-purple-400 drop-shadow-[0_0_35px_rgba(244,114,182,0.65)] leading-tight sm:leading-tight md:leading-tight mb-8">
            Hari Ini Hari Ulang Tahun Teman Kita
        </h1>

        <!-- Nama Selebran (Font Serif Elegan Cinzel / Playfair Display dengan Efek Shimmer) -->
        <div class="w-full space-y-6 sm:space-y-8 my-2">
            @foreach ($celebrants as $c)
                <div class="flex flex-col items-center">
                    <h2 class="font-cinzel text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-white via-pink-200 to-amber-200 animate-birthday-shimmer drop-shadow-[0_0_30px_rgba(255,255,255,0.7)] px-2">
                        {{ $c['name'] }}
                    </h2>
                    @if (! empty($c['bagian']))
                        <div class="mt-2 inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/5 border border-white/10 backdrop-blur-xs">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-pink-400 animate-ping"></span>
                            <span class="text-xs sm:text-sm font-medium tracking-widest text-gray-300 uppercase">
                                Bagian {{ $c['bagian'] }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- SVG Kue Ulang Tahun di Bawah Nama (Persis dari Brief) -->
        <div class="my-6 sm:my-8 transform scale-110 sm:scale-125">
            <svg viewBox="0 0 100 100" class="mx-auto h-24 w-24 sm:h-28 sm:w-28" fill="none" aria-hidden="true">
                <defs>
                    <linearGradient id="cakeGradHariH" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#f472b6"/>
                        <stop offset="1" stop-color="#be185d"/>
                    </linearGradient>
                </defs>
                <rect x="12" y="82" width="76" height="6" rx="3" fill="#4b5563"/>
                <rect x="20" y="58" width="60" height="24" rx="5" fill="url(#cakeGradHariH)"/>
                <rect x="32" y="40" width="36" height="18" rx="5" fill="#f9a8d4"/>
                <path d="M20 64 q7.5 9 15 0 t15 0 t15 0 t15 0" stroke="#fff" stroke-width="3.5" stroke-linecap="round"/>
                <path d="M32 46 q4.5 7 9 0 t9 0 t9 0" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                <rect x="47.5" y="26" width="5" height="14" rx="1.5" fill="#fde68a"/>
                <path d="M50 12 C56 18 55 23 50 25 C45 23 44 18 50 12Z" fill="#fbbf24" class="animate-pulse" style="transform-origin:50px 25px"/>
                <path d="M50 17 C53 20 52.5 23 50 24 C47.5 23 47 20 50 17Z" fill="#fff7ed"/>
            </svg>
        </div>

        <!-- Kalimat Penutup yang Muncul Bertahap (Delayed Transition) -->
        <div x-show="showClosing"
             x-transition:enter="ease-out duration-1000 transform"
             x-transition:enter-start="opacity-0 translate-y-6"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-3 max-w-2xl mx-auto px-4">
            <p class="text-sm sm:text-base md:text-lg text-gray-200 font-light leading-relaxed drop-shadow-md">
                &ldquo;Semoga setiap doa dan harapanmu terbang setinggi petasan malam ini. Terima kasih sudah menjadi bagian dari keluarga kami.&rdquo;
            </p>
            @php
                $firstCelebrant = $celebrants[0]['name'] ?? 'Sahabat';
                if (count($celebrants) > 1) {
                    $firstCelebrant = implode(' & ', array_column($celebrants, 'name'));
                }
            @endphp
            <p class="text-xs sm:text-sm md:text-base text-pink-300 font-semibold tracking-wide drop-shadow-[0_0_8px_rgba(244,114,182,0.6)]">
                Hari ini langit milikmu, {{ $firstCelebrant }}. ✨
            </p>
        </div>

        <!-- Tombol Opsional Kirim Ucapan -->
        <div x-show="showClosing"
             x-transition:enter="ease-out duration-1000 delay-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-8 pointer-events-auto">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-gradient-to-r from-pink-500/20 via-purple-500/20 to-amber-500/20 hover:from-pink-500/30 hover:via-purple-500/30 hover:to-amber-500/30 border border-pink-400/30 text-pink-200 hover:text-white text-xs sm:text-sm font-semibold tracking-wider transition shadow-lg backdrop-blur-md">
                Kirim ucapan 🎁
            </a>
        </div>

    </div>

    <!-- Bottom Spacing -->
    <div class="h-8 sm:h-12"></div>
</div>


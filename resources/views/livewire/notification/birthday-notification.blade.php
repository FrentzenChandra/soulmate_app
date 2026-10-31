<div>
    @if ($showModal && ! empty($celebrants))
        @php
            $count = count($celebrants);
            $formattedNames = '';
            $bagianText = null;

            if ($count === 1) {
                $formattedNames = $celebrants[0]['name'];
                $bagianText = $celebrants[0]['bagian'] ? 'Bagian ' . $celebrants[0]['bagian'] : null;
            } elseif ($count === 2) {
                $formattedNames = $celebrants[0]['name'] . ' & ' . $celebrants[1]['name'];
                $bagians = array_filter(array_unique([$celebrants[0]['bagian'], $celebrants[1]['bagian']]));
                $bagianText = !empty($bagians) ? 'Bagian ' . implode(', ', $bagians) : null;
            } else {
                $formattedNames = $celebrants[0]['name'] . ' & ' . $celebrants[1]['name'] . ' +' . ($count - 2);
            }

            $subtexts = [
                1 => 'BESOK! Siapkan ucapan terbaikmu malam ini. 🎂',
                2 => 'Dua hari lagi! Rahasiakan baik-baik, ya 🤫',
                3 => 'Tiga hari lagi! Saatnya merencanakan kejutan kecil.',
                4 => 'Empat hari menuju hari yang spesial. Siapkan ucapan terbaikmu.',
                5 => 'Tinggal sebentar lagi. Jangan sampai terlewat!',
                6 => 'Hitung mundur sudah berjalan. Sudah ada ide kado?',
                7 => 'Seminggu lagi pesta dimulai. Mulai siapkan kejutan! 🎁',
            ];
            $currentSubtext = $subtexts[$daysLeft] ?? 'Mari siapkan kejutan dan doa terbaik!';
        @endphp

        <div x-data="{
                open: @entangle('showModal'),
                fwInstance: null,
                init() {
                    this.$watch('open', (val) => {
                        if (val) {
                            this.$nextTick(() => this.startFireworks());
                        } else {
                            this.stopFireworks();
                        }
                    });
                    if (this.open) {
                        this.$nextTick(() => this.startFireworks());
                    }
                },
                startFireworks() {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        return;
                    }
                    const container = this.$refs.fwCanvas;
                    if (!container || !window.Fireworks) return;
                    if (this.fwInstance) {
                        this.fwInstance.stop(true);
                    }
                    this.fwInstance = new window.Fireworks(container, {
                        autoresize: true,
                        opacity: 0.65,
                        acceleration: 1.02,
                        friction: 0.97,
                        gravity: 1.5,
                        particles: 50,
                        traceLength: 3,
                        traceSpeed: 10,
                        explosion: 5,
                        intensity: 25,
                        flickering: 50,
                        lineStyle: 'round',
                        hue: { min: 320, max: 45 },
                        delay: { min: 30, max: 60 },
                        rocketsPoint: { min: 50, max: 50 },
                        lineWidth: { explosion: { min: 1, max: 3 }, trace: { min: 1, max: 2 } },
                        brightness: { min: 50, max: 80 },
                        decay: { min: 0.015, max: 0.03 },
                        mouse: { click: false, move: false, max: 1 }
                    });
                    this.fwInstance.start();
                    setTimeout(() => {
                        if (this.fwInstance) {
                            this.fwInstance.waitStop();
                        }
                    }, 6000);
                },
                stopFireworks() {
                    if (this.fwInstance) {
                        this.fwInstance.stop(true);
                        this.fwInstance = null;
                    }
                },
                destroy() {
                    this.stopFireworks();
                }
             }"
             x-show="open"
             x-cloak
             @keydown.escape.window="open = false; $wire.closeModal()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-hidden select-none"
             style="display: none;">

            <!-- Fireworks Canvas Layer behind Card -->
            <div x-ref="fwCanvas" class="absolute inset-0 pointer-events-none z-0"></div>

            <!-- Dark Overlay Backdrop -->
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="open = false; $wire.closeModal()"
                 class="fixed inset-0 bg-black/85 backdrop-blur-sm -z-10"></div>

            <!-- Modal Card Container with Pink Glow Blur -->
            <div class="relative w-full max-w-lg z-10">
                <!-- Pink/Amber Ambient Glow Behind Card -->
                <div class="absolute -inset-1.5 bg-gradient-to-r from-pink-500/35 via-purple-600/20 to-amber-500/25 rounded-3xl blur-2xl pointer-events-none"></div>

                <!-- Card Content -->
                <div x-show="open"
                     x-transition:enter="ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-6 scale-95"
                     class="relative bg-gradient-to-b from-gray-900 to-gray-950 border border-white/10 rounded-3xl p-6 sm:p-8 text-center shadow-2xl text-white">

                    <!-- SVGs Section (Petasan kiri, Kue tengah, Balon+Kado kanan) -->
                    <div class="flex items-center justify-center gap-2 sm:gap-4 mb-6">
                        <!-- 1. Petasan SVG (Kiri) -->
                        <div class="shrink-0">
                            <svg viewBox="0 0 100 100" class="mx-auto h-16 w-16 sm:h-20 sm:w-20" fill="none" aria-hidden="true">
                                <defs>
                                    <linearGradient id="fwGradModal" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0" stop-color="#ec4899"/>
                                        <stop offset="1" stop-color="#fbbf24"/>
                                    </linearGradient>
                                </defs>
                                <g class="animate-pulse" style="transform-origin:50px 50px" stroke="url(#fwGradModal)" stroke-width="4" stroke-linecap="round">
                                    @foreach ([0, 45, 90, 135, 180, 225, 270, 315] as $deg)
                                        <line x1="50" y1="8" x2="50" y2="24" transform="rotate({{ $deg }} 50 50)"/>
                                        <circle cx="50" cy="3" r="2.5" fill="#fbbf24" stroke="none" transform="rotate({{ $deg }} 50 50)"/>
                                    @endforeach
                                    @foreach ([22.5, 67.5, 112.5, 157.5, 202.5, 247.5, 292.5, 337.5] as $deg)
                                        <line x1="50" y1="20" x2="50" y2="30" transform="rotate({{ $deg }} 50 50)" opacity=".6"/>
                                    @endforeach
                                </g>
                                <circle cx="50" cy="50" r="7" fill="url(#fwGradModal)"/>
                            </svg>
                        </div>

                        <!-- 2. Kue Ulang Tahun SVG (Tengah - Sedikit Lebih Besar) -->
                        <div class="shrink-0 transform scale-110">
                            <svg viewBox="0 0 100 100" class="mx-auto h-20 w-20 sm:h-24 sm:w-24" fill="none" aria-hidden="true">
                                <defs>
                                    <linearGradient id="cakeGradModal" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0" stop-color="#f472b6"/>
                                        <stop offset="1" stop-color="#be185d"/>
                                    </linearGradient>
                                </defs>
                                <rect x="12" y="82" width="76" height="6" rx="3" fill="#4b5563"/>
                                <rect x="20" y="58" width="60" height="24" rx="5" fill="url(#cakeGradModal)"/>
                                <rect x="32" y="40" width="36" height="18" rx="5" fill="#f9a8d4"/>
                                <path d="M20 64 q7.5 9 15 0 t15 0 t15 0 t15 0" stroke="#fff" stroke-width="3.5" stroke-linecap="round"/>
                                <path d="M32 46 q4.5 7 9 0 t9 0 t9 0" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                                <rect x="47.5" y="26" width="5" height="14" rx="1.5" fill="#fde68a"/>
                                <path d="M50 12 C56 18 55 23 50 25 C45 23 44 18 50 12Z" fill="#fbbf24" class="animate-pulse" style="transform-origin:50px 25px"/>
                                <path d="M50 17 C53 20 52.5 23 50 24 C47.5 23 47 20 50 17Z" fill="#fff7ed"/>
                            </svg>
                        </div>

                        <!-- 3. Balon & Kado SVG (Kanan) -->
                        <div class="shrink-0">
                            <svg viewBox="0 0 100 100" class="mx-auto h-16 w-16 sm:h-20 sm:w-20" fill="none" aria-hidden="true">
                                <g class="animate-bounce" style="animation-duration:3s">
                                    <ellipse cx="38" cy="30" rx="15" ry="18" fill="#ec4899"/>
                                    <ellipse cx="32" cy="23" rx="4" ry="6" fill="#fff" opacity=".35"/>
                                    <path d="M38 48 q-3 12 2 22" stroke="#9ca3af" stroke-width="1.5"/>
                                    <ellipse cx="64" cy="26" rx="13" ry="16" fill="#fbbf24"/>
                                    <ellipse cx="59" cy="20" rx="3.5" ry="5" fill="#fff" opacity=".35"/>
                                    <path d="M64 42 q3 12 -2 28" stroke="#9ca3af" stroke-width="1.5"/>
                                </g>
                                <rect x="30" y="68" width="40" height="24" rx="3" fill="#6366f1"/>
                                <rect x="26" y="62" width="48" height="10" rx="3" fill="#818cf8"/>
                                <rect x="47" y="62" width="6" height="30" fill="#fde68a"/>
                                <path d="M50 62 C42 52 34 58 44 62 M50 62 C58 52 66 58 56 62" stroke="#fde68a" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Judul Notifikasi -->
                    <div class="space-y-1 mb-2">
                        @if ($daysLeft === 1)
                            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                                Hari ulang tahun <span class="text-pink-400">{{ $formattedNames }}</span> <span class="text-amber-400 font-extrabold">BESOK!</span>
                            </h2>
                        @else
                            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white leading-snug">
                                Hari ulang tahun <span class="text-pink-400">{{ $formattedNames }}</span> tinggal <span class="text-2xl sm:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-amber-400 px-1">{{ $daysLeft }}</span> hari lagi!
                            </h2>
                        @endif

                        @if ($bagianText)
                            <p class="text-xs sm:text-sm text-gray-400 font-medium">
                                {{ $bagianText }}
                            </p>
                        @endif
                    </div>

                    <!-- Subteks Dinamis -->
                    <p class="text-sm sm:text-base text-gray-300 mt-3 font-normal max-w-sm mx-auto leading-relaxed">
                        {{ $currentSubtext }}
                    </p>

                    <!-- Tombol Aksi -->
                    <div class="mt-7">
                        <button type="button"
                                @click="open = false; $wire.closeModal()"
                                class="inline-flex items-center justify-center w-full px-6 py-3.5 rounded-2xl bg-gradient-to-r from-[#5E17EB] to-[#E6007A] text-white font-semibold text-sm sm:text-base shadow-lg shadow-purple-900/40 hover:opacity-95 active:scale-98 transition transform focus:outline-none focus:ring-2 focus:ring-pink-400/50 cursor-pointer">
                            Siap, nanti saya ingatkan! 🎉
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>


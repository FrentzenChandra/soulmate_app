<div x-data="{
        deviceId: localStorage.getItem('soulmate_device_token') || '',
        init() {
            if (!this.deviceId) {
                this.deviceId = 'dev-' + (typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).substring(2) + Date.now().toString(36));
                localStorage.setItem('soulmate_device_token', this.deviceId);
            }
            $wire.set('deviceId', this.deviceId);
        },
        doAbsen() {
            $wire.submitAbsen(this.deviceId);
        }
     }"
     class="space-y-4 sm:space-y-6 max-w-xl mx-auto px-2 sm:px-0 py-2 sm:py-4">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center space-x-3 sm:space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#5E17EB] flex items-center justify-center border border-purple-100 shadow-2xs shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004.07 9.878m11.93 6.966c-.302 1.002-.693 1.96-1.161 2.864m-4.839-2.864c.302 1.002.693 1.96 1.161 2.864" />
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight">Absensi Staf</h1>
                <p class="text-[11px] sm:text-xs text-gray-500 font-medium mt-0.5">Catat kehadiran harian via WiFi Kantor resmi.</p>
            </div>
        </div>

        <div class="w-full sm:w-auto">
            <a href="{{ route('absensi.riwayat') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-3.5 py-2.5 sm:py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat Kehadiran</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if ($successMessage)
        <div class="p-3.5 sm:p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2.5 sm:space-x-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-bold leading-relaxed">{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', null)" class="text-emerald-500 hover:text-emerald-800 p-1 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-3.5 sm:p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2.5 sm:space-x-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-bold leading-relaxed">{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-500 hover:text-rose-800 p-1 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- Kartu Utama Absensi -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-200/80 p-5 sm:p-8 shadow-xs text-center space-y-5 sm:space-y-6">

        <!-- Jam Real-Time & Tanggal (Alpine.js) -->
        <div x-data="{
                time: '',
                date: '',
                updateClock() {
                    const now = new Date();
                    this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
                    this.date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                }
             }"
             x-init="updateClock(); setInterval(() => updateClock(), 1000);"
             class="space-y-1">
            <p class="text-[10px] sm:text-xs font-extrabold uppercase tracking-widest text-gray-400" x-text="date"></p>
            <p class="text-3xl sm:text-5xl font-black text-gray-900 tracking-tight font-mono" x-text="time"></p>
        </div>

        <!-- Status Jaringan WiFi Badge -->
        <div class="flex justify-center px-2">
            @if ($isWifiValid)
                <span class="inline-flex items-center space-x-2 px-3 sm:px-3.5 py-1.5 rounded-full text-[11px] sm:text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>WiFi Kantor Terhubung</span>
                </span>
            @else
                <span class="inline-flex items-center space-x-2 px-3 sm:px-3.5 py-1.5 rounded-full text-[11px] sm:text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Bukan Jaringan WiFi Kantor</span>
                </span>
            @endif
        </div>

        <!-- Tombol Sidik Jari Besar di Tengah (Touch-Optimized for Mobile) -->
        <div class="py-2 sm:py-4 flex flex-col items-center justify-center">

            @if ($statusState === 'sudah_absen')
                <!-- Keadaan 3: Sudah Absen (Emerald, Terkunci) -->
                <div class="relative w-36 h-36 sm:w-48 sm:h-48 rounded-full bg-emerald-600 text-white ring-6 sm:ring-8 ring-emerald-100 flex items-center justify-center shadow-sm select-none"
                     role="status"
                     aria-label="Status Absensi Selesai">
                    <svg class="w-14 h-14 sm:w-20 sm:h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="mt-4 sm:mt-5 space-y-1" aria-live="polite">
                    <p class="text-base sm:text-lg font-black text-emerald-700">Sudah selesai absen</p>
                    <p class="text-xs font-bold text-gray-600">
                        Jam {{ $todayAbsensi['jam_absen'] ?? '' }} WIB &bull; {{ $todayAbsensi['tanggal'] ?? '' }}
                    </p>
                    <div class="pt-2 flex items-center justify-center gap-2">
                        @if(($todayAbsensi['shift'] ?? '') === 'sore')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                Shift Sore
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-[#0052FF] border border-blue-200">
                                Shift Pagi
                            </span>
                        @endif

                        @if(($todayAbsensi['status_kehadiran'] ?? '') === 'terlambat_kritis')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                Kritis (+{{ $todayAbsensi['menit_keterlambatan'] ?? 0 }}m)
                            </span>
                        @elseif(($todayAbsensi['status_kehadiran'] ?? '') === 'terlambat')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                Terlambat (+{{ $todayAbsensi['menit_keterlambatan'] ?? 0 }}m)
                            </span>
                        @elseif(($todayAbsensi['status_kehadiran'] ?? '') === 'terlalu_awal')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-50 text-[#0052FF] border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#0052FF] mr-1.5"></span>
                                Lebih Awal
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                Tepat Waktu
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 font-medium max-w-xs sm:max-w-sm mx-auto pt-1">
                        Kehadiran Anda hari ini telah tercatat secara sah di sistem.
                    </p>
                </div>

            @elseif ($statusState === 'bukan_wifi')
                <!-- Keadaan 4: Bukan WiFi Kantor (Abu-abu, Terkunci) -->
                <div class="relative w-36 h-36 sm:w-48 sm:h-48 rounded-full bg-gray-100 text-gray-400 ring-6 sm:ring-8 ring-gray-200 border border-gray-200 flex items-center justify-center shadow-2xs cursor-not-allowed select-none"
                     role="status"
                     aria-label="Absensi Terkunci">
                    <svg class="w-14 h-14 sm:w-20 sm:h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004.07 9.878m11.93 6.966c-.302 1.002-.693 1.96-1.161 2.864m-4.839-2.864c.302 1.002.693 1.96 1.161 2.864" />
                    </svg>
                </div>
                <div class="mt-4 sm:mt-5 space-y-1 max-w-xs sm:max-w-sm mx-auto" aria-live="polite">
                    <p class="text-sm font-bold text-gray-700">Tombol Absensi Terkunci</p>
                    <p class="text-xs font-medium text-rose-600">
                        Sambungkan perangkat ke jaringan WiFi Kantor untuk melakukan absensi.
                    </p>
                    <p class="text-[11px] text-gray-400 pt-1 break-all">
                        IP Anda: <span class="font-mono font-bold text-gray-600">{{ $clientIp }}</span>
                    </p>
                </div>

            @else
                <!-- Keadaan 1 & 2: Siap / Memproses -->
                @can('absensi.submit')
                <button type="button"
                        @click="doAbsen"
                        wire:loading.attr="disabled"
                        wire:target="submitAbsen"
                        class="relative w-36 h-36 sm:w-48 sm:h-48 rounded-full bg-[#5E17EB] hover:bg-purple-800 active:scale-95 text-white ring-6 sm:ring-8 ring-purple-100 flex items-center justify-center shadow-md transition-all duration-150 cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-purple-600 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed group touch-manipulation"
                        aria-label="Tekan tombol untuk melakukan absensi kehadiran">

                    <!-- Spinner Loading Heroicons saat memproses -->
                    <div wire:loading wire:target="submitAbsen" class="flex flex-col items-center justify-center">
                        <svg class="animate-spin w-14 h-14 sm:w-20 sm:h-20 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>

                    <!-- Ikon Fingerprint Siap Absen -->
                    <div wire:loading.remove wire:target="submitAbsen" class="flex items-center justify-center">
                        <svg class="w-14 h-14 sm:w-20 sm:h-20 text-white transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004.07 9.878m11.93 6.966c-.302 1.002-.693 1.96-1.161 2.864m-4.839-2.864c.302 1.002.693 1.96 1.161 2.864" />
                        </svg>
                    </div>
                </button>

                <div class="mt-4 sm:mt-5 space-y-1" aria-live="polite">
                    <div wire:loading.remove wire:target="submitAbsen">
                        <p class="text-sm font-bold text-gray-900">Tekan untuk absen</p>
                        <p class="text-xs text-gray-500 mt-0.5">Pastikan Anda telah berada di toko dan siap bekerja</p>
                    </div>
                    <div wire:loading wire:target="submitAbsen">
                        <p class="text-sm font-bold text-gray-900">Memproses...</p>
                        <p class="text-xs text-gray-500 mt-0.5">Sedang memverifikasi jaringan dan mencatat waktu</p>
                    </div>
                </div>
                @else
                <div class="relative w-36 h-36 sm:w-48 sm:h-48 rounded-full bg-gray-100 text-gray-400 ring-6 sm:ring-8 ring-gray-200 border border-gray-200 flex items-center justify-center shadow-2xs select-none">
                    <svg class="w-14 h-14 sm:w-20 sm:h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="mt-4 sm:mt-5 space-y-1">
                    <p class="text-sm font-bold text-gray-700">Akses Rekam Absen Dibatasi</p>
                    <p class="text-xs text-gray-500">Akun Anda hanya memiliki izin melihat status absensi.</p>
                </div>
                @endcan
            @endif

        </div>

        <!-- Info Tambahan Pengguna (Responsive Layout) -->
        <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-2 text-xs text-gray-500">
            <div class="flex items-center space-x-2 text-left min-w-0">
                <div class="w-8 h-8 rounded-full bg-purple-100 text-[#5E17EB] flex items-center justify-center font-black text-xs shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->role_name }}</p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Jaringan IP</p>
                <p class="font-mono text-[11px] sm:text-xs font-bold text-gray-700 truncate max-w-[120px] sm:max-w-[160px]">{{ $clientIp }}</p>
            </div>
        </div>

    </div>

</div>


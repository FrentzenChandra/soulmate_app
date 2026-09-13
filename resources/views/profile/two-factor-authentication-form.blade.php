<x-action-section>
    <x-slot name="title">
        Otentikasi Dua Faktor (2FA)
    </x-slot>

    <x-slot name="description">
        Tingkatkan keamanan akun Anda dengan lapisan proteksi tambahan berbasis aplikasi autentikator.
    </x-slot>

    <x-slot name="content">
        <!-- Status Indicator Banner -->
        <div class="mb-6 p-4 rounded-xl border {{ $this->enabled ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' }} flex items-start space-x-3">
            <div class="shrink-0 mt-0.5">
                @if ($this->enabled)
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                @else
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                @endif
            </div>
            <div class="min-w-0">
                <h4 class="text-sm font-extrabold">
                    @if ($this->enabled)
                        @if ($showingConfirmation)
                            Selesaikan Konfirmasi Otentikasi Dua Faktor
                        @else
                            Otentikasi Dua Faktor Sedang Aktif
                        @endif
                    @else
                        Otentikasi Dua Faktor Belum Diaktifkan
                    @endif
                </h4>
                <p class="text-xs mt-0.5 leading-relaxed opacity-90">
                    Ketika 2FA aktif, sistem akan meminta 6 digit kode keamanan acak dari aplikasi seperti <strong>Google Authenticator</strong> atau <strong>Microsoft Authenticator</strong> setiap kali Anda login.
                </p>
            </div>
        </div>

        @if ($this->enabled)
            @if ($showingQrCode)
                <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/80 mb-6 space-y-4">
                    <p class="text-xs font-bold text-gray-700">
                        @if ($showingConfirmation)
                            Langkah 1: Scan kode QR di bawah ini menggunakan aplikasi Authenticator pada ponsel Anda, lalu masukkan 6-digit kode verifikasi yang muncul.
                        @else
                            Kode QR Otentikasi Anda:
                        @endif
                    </p>

                    <div class="p-4 bg-white rounded-xl border border-gray-200 inline-block shadow-xs">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="space-y-1.5" x-data="{ copied: false, key: '{{ decrypt($this->user->two_factor_secret) }}' }">
                        <span class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Kunci Penyiapan Manual (Setup Key):</span>
                        <div class="flex items-center space-x-2">
                            <code class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold text-purple-700 select-all tracking-wider" x-text="key"></code>
                            <button type="button" 
                                    @click="navigator.clipboard.writeText(key); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition-colors cursor-pointer flex items-center space-x-1">
                                <span x-text="copied ? 'Tersalin!' : 'Salin Kunci'"></span>
                            </button>
                        </div>
                    </div>

                    @if ($showingConfirmation)
                        <div class="pt-4 border-t border-gray-200/80 max-w-sm">
                            <x-label for="code" value="Langkah 2: Masukkan 6-Digit Kode Verifikasi" />
                            <x-input id="code" type="text" name="code" class="block mt-1 w-full text-center text-lg tracking-widest font-mono font-bold" inputmode="numeric" autofocus autocomplete="one-time-code"
                                placeholder="000000"
                                wire:model="code"
                                wire:keydown.enter="confirmTwoFactorAuthentication" />
                            <x-input-error for="code" class="mt-2" />
                        </div>
                    @endif
                </div>
            @endif

            @if ($showingRecoveryCodes)
                <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/80 mb-6 space-y-3" x-data="{ 
                    codes: '{{ implode("\n", json_decode(decrypt($this->user->two_factor_recovery_codes), true)) }}',
                    copiedAll: false
                }">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Kode Pemulihan Darurat (Recovery Codes)</h5>
                            <p class="text-xs text-gray-500 mt-0.5">Simpan kode-kode berikut di tempat yang aman. Kode ini digunakan untuk masuk jika Anda kehilangan akses ke aplikasi autentikator.</p>
                        </div>
                        <button type="button" 
                                @click="navigator.clipboard.writeText(codes); copiedAll = true; setTimeout(() => copiedAll = false, 2500)"
                                class="px-3 py-1.5 rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 text-xs font-bold text-[#5E17EB] transition-colors cursor-pointer shrink-0">
                            <span x-text="copiedAll ? 'Semua Tersalin!' : 'Salin Semua Kode'"></span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-xs font-bold bg-white p-4 rounded-xl border border-gray-200 text-gray-800">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div class="p-2 rounded bg-gray-50 text-center select-all border border-gray-100">{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-3 pt-2">
            @if (! $this->enabled)
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <x-button type="button" wire:loading.attr="disabled">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <span>Aktifkan 2FA Sekarang</span>
                    </x-button>
                </x-confirms-password>
            @else
                @if ($showingRecoveryCodes)
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <x-secondary-button>
                            <span>Regenerasi Kode Pemulihan</span>
                        </x-secondary-button>
                    </x-confirms-password>
                @elseif ($showingConfirmation)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <x-button type="button" wire:loading.attr="disabled">
                            <span>Konfirmasi & Simpan</span>
                        </x-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <x-secondary-button>
                            <span>Tampilkan Kode Pemulihan</span>
                        </x-secondary-button>
                    </x-confirms-password>
                @endif

                @if ($showingConfirmation)
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-secondary-button wire:loading.attr="disabled">
                            <span>Batal</span>
                        </x-secondary-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-danger-button wire:loading.attr="disabled">
                            <span>Nonaktifkan 2FA</span>
                        </x-danger-button>
                    </x-confirms-password>
                @endif
            @endif
        </div>
    </x-slot>
</x-action-section>
<x-action-section>
    <x-slot name="title">
        Sesi Browser & Perangkat Aktif
    </x-slot>

    <x-slot name="description">
        Pantau dan keluarkan akun Anda dari perangkat atau browser lain yang sedang aktif.
    </x-slot>

    <x-slot name="content">
        <div class="max-w-2xl text-xs text-gray-500 leading-relaxed">
            Jika diperlukan, Anda dapat mengeluarkan (logout) akun Anda dari semua sesi browser di seluruh perangkat lain. Jika Anda mencurigai adanya akses tidak sah, segera perbarui kata sandi akun Anda.
        </div>

        @if (count($this->sessions) > 0)
            <div class="mt-6 space-y-3">
                @foreach ($this->sessions as $session)
                    <div class="p-4 rounded-xl border {{ $session->is_current_device ? 'bg-purple-50/40 border-purple-200/80' : 'bg-white border-gray-200/80' }} flex items-center justify-between gap-4 shadow-xs">
                        <div class="flex items-center space-x-3.5 min-w-0">
                            <!-- Device Icon -->
                            <div class="w-10 h-10 rounded-xl {{ $session->is_current_device ? 'bg-purple-100 text-[#5E17EB]' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center shrink-0">
                                @if ($session->agent->isDesktop())
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                @endif
                            </div>

                            <!-- Session Meta -->
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-gray-900 flex items-center gap-2">
                                    <span>{{ $session->agent->platform() ? $session->agent->platform() : 'Platform Tidak Dikenal' }}</span>
                                    <span class="text-gray-300">â€¢</span>
                                    <span class="text-gray-600 font-semibold">{{ $session->agent->browser() ? $session->agent->browser() : 'Browser' }}</span>
                                </div>
                                <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                    IP: {{ $session->ip_address }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="shrink-0 text-right">
                            @if ($session->is_current_device)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    Perangkat Ini
                                </span>
                            @else
                                <span class="text-[11px] text-gray-400">
                                    Aktif: {{ $session->last_active }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center mt-6 pt-4 border-t border-gray-100">
            <x-button wire:click="confirmLogout" wire:loading.attr="disabled">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Keluarkan Sesi Perangkat Lain</span>
            </x-button>

            <x-action-message class="ms-3" on="loggedOut">
                <span class="text-xs font-bold text-emerald-600 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Sesi lain berhasil dikeluarkan.
                </span>
            </x-action-message>
        </div>

        <!-- Log Out Other Devices Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingLogout">
            <x-slot name="title">
                Konfirmasi Keluar Sesi Perangkat Lain
            </x-slot>

            <x-slot name="content">
                <p class="text-xs text-gray-500 leading-relaxed">
                    Masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin mengeluarkan akun Anda dari semua sesi browser di perangkat lain.
                </p>

                <div class="mt-4" x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-full"
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi Anda"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="logoutOtherBrowserSessions" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled">
                    Batal
                </x-secondary-button>

                <x-button class="ms-3"
                            wire:click="logoutOtherBrowserSessions"
                            wire:loading.attr="disabled">
                    Keluarkan Sesi Lain
                </x-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
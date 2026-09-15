<x-app-layout>
    <x-slot name="title">Pengaturan Profil & Keamanan</x-slot>

    <div class="max-w-6xl mx-auto space-y-6" x-data="{ activeTab: 'profile' }">
        
        <!-- Page Header & Title -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-gray-200/80">
            <div>
                <div class="flex items-center space-x-2 text-xs font-bold text-gray-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#5E17EB] transition-colors">Dashboard</a>
                    <span>/</span>
                    <span class="text-gray-700">Profil & Keamanan</span>
                </div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Pengaturan Profil & Keamanan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola identitas akun, kredensial kata sandi, otentikasi dua faktor (2FA), dan sesi perangkat.</p>
            </div>
        </div>

        <!-- Hero Overview Card -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-6 sm:p-7 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center space-x-5">
                <!-- Avatar with badge -->
                <div class="relative shrink-0">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden bg-brand-gradient text-white flex items-center justify-center font-extrabold text-2xl shadow-md ring-4 ring-purple-50">
                        @if(auth()->user()->profile_photo_url || auth()->user()->foto_url)
                            <img src="{{ auth()->user()->profile_photo_url ?? asset(auth()->user()->foto_url) }}" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="w-full h-full object-cover">
                            <span style="display:none;" class="w-full h-full flex items-center justify-center">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        @else
                            <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white shadow-xs" title="Akun Aktif"></span>
                </div>

                <!-- User Meta -->
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-extrabold text-gray-900 leading-tight">{{ auth()->user()->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-purple-50 text-[#5E17EB] border border-purple-200">
                            {{ auth()->user()->role_name }}
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 mt-0.5">
                        <span class="text-gray-600 font-bold">@ {{ auth()->user()->username ?? 'user' }}</span>
                        @if(auth()->user()->isOwner())
                            @if(auth()->user()->cabang)
                                <span class="inline-block w-1 h-1 rounded-full bg-gray-300 mx-1.5 align-middle"></span>
                                <span>{{ auth()->user()->cabang->nama_cabang }}</span>
                            @else
                                <span class="inline-block w-1 h-1 rounded-full bg-gray-300 mx-1.5 align-middle"></span>
                                <span class="text-purple-600 font-bold">Kantor Pusat (HQ)</span>
                            @endif
                        @endif
                    </p>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            {{ auth()->user()->email }}
                        </span>
                        @if(auth()->user()->no_hp)
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                {{ auth()->user()->no_hp }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Security Badges -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full md:w-auto pt-4 md:pt-0 border-t md:border-t-0 border-gray-100">
                <div class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-100 text-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Status 2FA</span>
                    @if(auth()->user()->two_factor_secret)
                        <span class="text-xs font-extrabold text-emerald-600 inline-flex items-center mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            Aktif Terlindungi
                        </span>
                    @else
                        <span class="text-xs font-extrabold text-amber-600 inline-flex items-center mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                            Belum Aktif
                        </span>
                    @endif
                </div>

                <div class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-100 text-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Role Akses</span>
                    <span class="text-xs font-extrabold text-[#5E17EB] block mt-0.5">
                        {{ auth()->user()->role_name }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center space-x-2 border-b border-gray-200/80 overflow-x-auto custom-scrollbar pb-px">
            <button type="button" 
                    @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' 
                        ? 'border-[#5E17EB] text-[#5E17EB] bg-purple-50/60 font-extrabold shadow-xs' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100/70 font-semibold'"
                    class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>Informasi Profil</span>
            </button>

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <button type="button" 
                        @click="activeTab = 'password'"
                        :class="activeTab === 'password' 
                            ? 'border-[#5E17EB] text-[#5E17EB] bg-purple-50/60 font-extrabold shadow-xs' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100/70 font-semibold'"
                        class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>Kata Sandi</span>
                </button>
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <button type="button" 
                        @click="activeTab = 'two-factor'"
                        :class="activeTab === 'two-factor' 
                            ? 'border-[#5E17EB] text-[#5E17EB] bg-purple-50/60 font-extrabold shadow-xs' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100/70 font-semibold'"
                        class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Keamanan 2FA</span>
                </button>
            @endif

            <button type="button" 
                    @click="activeTab = 'sessions'"
                    :class="activeTab === 'sessions' 
                        ? 'border-[#5E17EB] text-[#5E17EB] bg-purple-50/60 font-extrabold shadow-xs' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100/70 font-semibold'"
                    class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span>Sesi Perangkat</span>
            </button>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <button type="button" 
                        @click="activeTab = 'danger'"
                        :class="activeTab === 'danger' 
                            ? 'border-rose-600 text-rose-600 bg-rose-50/60 font-extrabold shadow-xs' 
                            : 'border-transparent text-gray-400 hover:text-rose-600 hover:bg-rose-50/30 font-semibold'"
                        class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 cursor-pointer shrink-0 ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Zona Bahaya</span>
                </button>
            @endif
        </div>

        <!-- Tab 1: Profile Information -->
        <div x-show="activeTab === 'profile'" x-cloak x-transition>
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif
        </div>

        <!-- Tab 2: Password -->
        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div x-show="activeTab === 'password'" x-cloak x-transition>
                @livewire('profile.update-password-form')
            </div>
        @endif

        <!-- Tab 3: Two Factor Authentication -->
        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div x-show="activeTab === 'two-factor'" x-cloak x-transition>
                @livewire('profile.two-factor-authentication-form')
            </div>
        @endif

        <!-- Tab 4: Browser Sessions -->
        <div x-show="activeTab === 'sessions'" x-cloak x-transition>
            @livewire('profile.logout-other-browser-sessions-form')
        </div>

        <!-- Tab 5: Account Deletion -->
        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <div x-show="activeTab === 'danger'" x-cloak x-transition>
                @livewire('profile.delete-user-form')
            </div>
        @endif

    </div>
</x-app-layout>
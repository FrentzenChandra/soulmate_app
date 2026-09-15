<div class="relative bg-white rounded-3xl shadow-2xl shadow-purple-950/40 border border-gray-100/10 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[580px]">

    <!-- Full-screen Loading Modal Overlay on Submit -->
    <div wire:loading.flex 
         wire:target="authenticate" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/75 backdrop-blur-sm transition-opacity"
         style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-11/12 shadow-2xl border border-gray-100 flex flex-col items-center text-center">
            <div class="relative mb-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#5E17EB] to-[#E6007A] flex items-center justify-center text-white shadow-xl shadow-purple-500/30 overflow-hidden">
                    <img src="{{ asset('images/logo_bulat.jpg') }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div class="absolute -inset-1.5 rounded-2xl border-2 border-[#5E17EB] animate-ping opacity-40"></div>
            </div>
            
            <h3 class="text-lg font-extrabold text-gray-900">Memproses Masuk...</h3>
            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Sedang memverifikasi kredensial & otorisasi sistem Soulmate Komputer.
            </p>

            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden mt-6">
                <div class="bg-gradient-to-r from-[#5E17EB] via-[#0052FF] to-[#E6007A] h-full rounded-full w-full animate-pulse"></div>
            </div>
            <span class="text-[10px] text-purple-600 font-bold uppercase tracking-wider mt-3">Mohon tunggu sebentar...</span>
        </div>
    </div>

    <!-- Left Hero Display Section (Desktop Only / Wide) -->
    <div class="lg:col-span-6 relative hidden lg:flex flex-col justify-between p-8 bg-cover bg-center overflow-hidden" 
         style="background-image: url('{{ asset('images/hero_display.jpeg') }}');">
        
        <!-- Dark Gradient Overlay for Readability -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#0E1726] via-[#0E1726]/80 to-[#0E1726]/60"></div>

        <!-- Top Header inside Hero -->
        <div class="relative z-10 flex items-center space-x-3">
            <img src="{{ asset('images/logo_bulat.jpg') }}" alt="Logo Soulmate" class="w-11 h-11 rounded-xl object-contain bg-white/10 backdrop-blur-md p-1 border border-white/20 shadow-md">
            <div>
                <span class="text-lg font-extrabold text-white tracking-tight block leading-tight">SOULMATE</span>
                <span class="text-[11px] text-purple-300 font-semibold tracking-wider uppercase">Pusat Servis & Point of Sale</span>
            </div>
        </div>

        <!-- Middle Branding Info -->
        <div class="relative z-10 my-auto py-6">
            <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md text-white text-xs font-bold px-3.5 py-1.5 rounded-full mb-3 border border-white/20 shadow-lg">
                <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                <span>Cepat &bull; Transparan &bull; Terpercaya</span>
            </div>
            <h2 class="text-2xl xl:text-3xl font-extrabold text-white leading-tight">
                Sistem Operasional Terpadu & Terintegrasi
            </h2>
            <p class="text-sm text-gray-300 mt-2 leading-relaxed">
                Manajemen servis komputer, laptop, printer, sistem kasir toko, katalog produk global, dan smart display antrian real-time.
            </p>

            <div class="grid grid-cols-2 gap-3 mt-6">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-3">
                    <span class="text-xs text-purple-200 block font-medium">Katalog & POS</span>
                    <span class="text-sm font-bold text-white">Stok Real-time</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-3">
                    <span class="text-xs text-emerald-200 block font-medium">Keamanan Terjamin</span>
                    <span class="text-sm font-bold text-white">CCTV Supervised</span>
                </div>
            </div>
        </div>

        <!-- Footer inside Hero -->
        <div class="relative z-10 text-[11px] text-gray-400 border-t border-white/10 pt-3 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} Soulmate Komputer. All rights reserved.</span>
            <span class="text-emerald-400 font-semibold">&bull; System Online</span>
        </div>
    </div>

    <!-- Right Login Form Section -->
    <div class="lg:col-span-6 p-7 sm:p-10 flex flex-col justify-center bg-white">
        
        <!-- Mobile Logo Display -->
        <div class="lg:hidden flex items-center space-x-3 mb-6">
            <img src="{{ asset('images/logo_bulat.jpg') }}" alt="Logo Soulmate" class="w-10 h-10 rounded-xl object-cover border border-gray-200 shadow-sm p-0.5">
            <div>
                <span class="text-lg font-extrabold text-gray-900 tracking-tight block leading-tight">SOULMATE KOMPUTER</span>
                <span class="text-[10px] text-purple-600 font-bold uppercase tracking-wider">Servis & POS System</span>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Selamat Datang Kembali</h2>
            <p class="text-xs text-gray-500 mt-1">Silakan masuk menggunakan akun internal staf atau super admin.</p>
        </div>

        @if (session()->has('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center space-x-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form wire:submit="authenticate" class="space-y-4">
            <!-- Email / Username -->
            <div>
                <label for="identity" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Email / Username
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <input type="text" 
                           id="identity" 
                           wire:model="identity" 
                           class="w-full pl-10 pr-4 py-2.5 bg-[#F8F9FA] border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] transition-all"
                           required autofocus />
                </div>
                @error('identity')
                    <p class="text-rose-500 text-xs font-semibold mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Kata Sandi
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <input :type="show ? 'text' : 'password'" 
                           id="password" 
                           wire:model="password" 
                           class="w-full pl-10 pr-10 py-2.5 bg-[#F8F9FA] border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#5E17EB]/20 focus:border-[#5E17EB] transition-all"
                           required />
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-rose-500 text-xs font-semibold mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center space-x-2 text-xs text-gray-600 cursor-pointer">
                    <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded text-[#5E17EB] focus:ring-[#5E17EB] border-gray-300">
                    <span>Ingat status login</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="btn-brand w-full py-3 px-4 rounded-xl text-white font-bold text-sm shadow-md shadow-purple-500/20 flex items-center justify-center space-x-2 cursor-pointer mt-2 disabled:opacity-50">
                <span wire:loading.remove wire:target="authenticate">Masuk ke Dashboard</span>
                <span wire:loading wire:target="authenticate" class="flex items-center space-x-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Memproses...</span>
                </span>
            </button>
        </form>

        <!-- Quick Demo Switcher (Super Admin & Staff) -->
        <div class="mt-6 pt-5 border-t border-gray-100">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2 text-center">Akses Cepat Login</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                <button type="button" 
                        wire:click="fillCredentials('superadmin')" 
                        class="px-2.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-950 font-extrabold text-left transition-colors flex items-center justify-between cursor-pointer shadow-2xs">
                    <span>Super Admin</span>
                    <span class="text-[9px] text-[#5E17EB] font-black bg-white px-1.5 py-0.5 rounded border border-purple-200">Utama</span>
                </button>
                <button type="button" 
                        wire:click="fillCredentials('kasir_pkp')" 
                        class="px-2.5 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-800 font-bold text-left transition-colors flex items-center justify-between cursor-pointer">
                    <span>Admin</span>
                    <span class="text-[9px] text-gray-500 font-normal">Kasir</span>
                </button>
                <button type="button" 
                        wire:click="fillCredentials('teknisi_pkp')" 
                        class="px-2.5 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-800 font-bold text-left transition-colors flex items-center justify-between cursor-pointer">
                    <span>Teknisi</span>
                    <span class="text-[9px] text-gray-500 font-normal">Servis</span>
                </button>
            </div>
            <p class="text-[10px] text-gray-400 text-center mt-2.5">Password default: <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700 font-mono font-bold">password</code></p>
        </div>
    </div>
</div>

<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA]">
        <div class="w-full max-w-md">
            
            <!-- Brand Logo & Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white border border-gray-200/80 shadow-md p-2.5 mb-4">
                    <img src="{{ asset('images/logo_bulat.jpg') }}" alt="Soulmate Logo" class="w-full h-full object-contain rounded-xl" onerror="this.src='{{ asset('images/logo.jpeg') }}'">
                </div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">SOULMATE KOMPUTER</h1>
                <p class="text-sm font-medium text-gray-500 mt-1">Sistem Manajemen Servis & Point of Sales Terpadu</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xl shadow-purple-950/5 p-8">
                
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Masuk ke Akun</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Gunakan email atau username terdaftar Anda</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                        <div class="flex items-center space-x-2 font-bold text-rose-900">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span>Gagal Masuk</span>
                        </div>
                        <ul class="list-disc list-inside pl-1 space-y-0.5 text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email / Username -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email atau Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <input id="email" 
                                   type="text" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   placeholder="email@soulmate.id atau username" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 text-sm font-semibold text-gray-900 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Password -->
                    <div x-data="{ showPass: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#5E17EB] hover:text-purple-800 transition-colors">
                                    Lupa Password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input id="password" 
                                   :type="showPass ? 'text' : 'password'" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" 
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 text-sm font-semibold text-gray-900 transition-all outline-none">
                            <button type="button" 
                                    @click="showPass = !showPass" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-[#5E17EB] focus:ring-[#5E17EB] transition-colors">
                            <span class="ms-2 text-xs font-semibold text-gray-600">Ingat sesi saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3 px-4 rounded-xl bg-[#5E17EB] hover:bg-[#4E10C7] text-white font-extrabold text-sm shadow-md shadow-purple-900/20 hover:shadow-lg hover:shadow-purple-900/30 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                            <span>Masuk ke Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </form>

            </div>

            <!-- Footer Info -->
            <p class="text-center text-xs text-gray-500 mt-6">
                Belum memiliki akses? Hubungi <span class="font-bold text-gray-700">Administrator</span> cabang atau Superadmin Anda.
            </p>

        </div>
    </div>
</x-guest-layout>
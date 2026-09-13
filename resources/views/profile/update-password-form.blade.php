<x-form-section submit="updatePassword">
    <x-slot name="title">
        Perbarui Kata Sandi
    </x-slot>

    <x-slot name="description">
        Pastikan akun Anda menggunakan kombinasi kata sandi yang aman dan tidak mudah ditebak.
    </x-slot>

    <x-slot name="form">
        <!-- Security Tip -->
        <div class="col-span-6">
            <div class="p-3.5 rounded-xl bg-purple-50/70 border border-purple-200/80 text-purple-900 text-xs flex items-start space-x-2.5">
                <svg class="w-4 h-4 text-[#5E17EB] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="leading-relaxed">
                    <span class="font-bold">Tips Keamanan:</span> Gunakan minimal 8 karakter dengan perpaduan huruf besar, huruf kecil, angka, dan simbol untuk melindungi akun operasional Anda.
                </div>
            </div>
        </div>

        <!-- Current Password -->
        <div class="col-span-6 sm:col-span-4" x-data="{ show: false }">
            <x-label for="current_password" value="Kata Sandi Saat Ini" />
            <div class="relative">
                <input id="current_password"
                       x-bind:type="show ? 'text' : 'password'"
                       class="border border-gray-200 focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 rounded-xl shadow-xs text-sm font-semibold text-gray-900 transition-all outline-none mt-1 block w-full pr-10"
                       wire:model="state.current_password"
                       autocomplete="current-password"
                       placeholder="Masukkan Password Sekarang" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                </button>
            </div>
            <x-input-error for="current_password" class="mt-2" />
        </div>

        <!-- New Password -->
        <div class="col-span-6 sm:col-span-4" x-data="{ show: false }">
            <x-label for="password" value="Kata Sandi Baru" />
            <div class="relative">
                <input id="password"
                       x-bind:type="show ? 'text' : 'password'"
                       class="border border-gray-200 focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 rounded-xl shadow-xs text-sm font-semibold text-gray-900 transition-all outline-none mt-1 block w-full pr-10"
                       wire:model="state.password"
                       autocomplete="new-password"
                       placeholder="Masukkan Password Baru" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                </button>
            </div>
            <x-input-error for="password" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="col-span-6 sm:col-span-4" x-data="{ show: false }">
            <x-label for="password_confirmation" value="Ulangi Kata Sandi Baru" />
            <div class="relative">
                <input id="password_confirmation"
                       x-bind:type="show ? 'text' : 'password'"
                       class="border border-gray-200 focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 rounded-xl shadow-xs text-sm font-semibold text-gray-900 transition-all outline-none mt-1 block w-full pr-10"
                       wire:model="state.password_confirmation"
                       autocomplete="new-password"
                       placeholder="Masukkan Password Baru lagi" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                </button>
            </div>
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            <span class="text-xs font-bold text-emerald-600 flex items-center">
                <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Kata sandi berhasil diperbarui.
            </span>
        </x-action-message>

        <x-button>
            <span>Simpan Kata Sandi</span>
        </x-button>
    </x-slot>
</x-form-section>

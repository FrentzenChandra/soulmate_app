<x-action-section>
    <x-slot name="title">
        Hapus Akun Pengguna
    </x-slot>

    <x-slot name="description">
        Hapus akun Anda secara permanen dari sistem beserta seluruh riwayat aktivitas personal.
    </x-slot>

    <x-slot name="content">
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-start space-x-3 mb-5">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="leading-relaxed">
                <span class="font-extrabold">Peringatan Kritis:</span> Setelah akun dihapus, seluruh data sesi dan akses personal Anda akan dihapus secara permanen dan tidak dapat dipulihkan. Pastikan Anda telah mendownload seluruh data yang Anda perlukan sebelum melanjutkan.
            </div>
        </div>

        <div>
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <span>Hapus Akun Saya Permanen</span>
            </x-danger-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                Konfirmasi Hapus Akun Permanen
            </x-slot>

            <x-slot name="content">
                <p class="text-xs text-gray-500 leading-relaxed">
                    Apakah Anda benar-benar yakin ingin menghapus akun Anda? Masukkan kata sandi akun Anda untuk mengonfirmasi tindakan penghapusan permanen ini.
                </p>

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-full"
                                autocomplete="current-password"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="deleteUser" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    Batal
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                    Ya, Hapus Akun Sekarang
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
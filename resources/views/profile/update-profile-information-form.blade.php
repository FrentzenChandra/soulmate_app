<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        Informasi Profil & Kontak
    </x-slot>

    <x-slot name="description">
        Perbarui data nama lengkap, email, nomor WhatsApp, dan foto profil akun Anda.
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden"
                            wire:model.live="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="Foto Profil" />

                <!-- Centered Photo Upload Loading State -->
                <div class="mt-2">
                    <x-photo-upload-loading target="photo" color="purple" />
                </div>

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview" wire:loading.remove wire:target="photo">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-2xl size-20 object-cover shadow-xs border border-gray-200">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-2xl size-20 bg-cover bg-no-repeat bg-center shadow-xs border border-gray-200"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <div class="flex items-center space-x-2 mt-3">
                    <x-secondary-button type="button" x-on:click.prevent="$refs.photo.click()">
                        Pilih Foto Baru
                    </x-secondary-button>

                    @if ($this->user->profile_photo_path || $this->user->foto_url)
                        <x-secondary-button type="button" wire:click="deleteProfilePhoto">
                            Hapus Foto
                        </x-secondary-button>
                    @endif
                </div>

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="Nama Lengkap" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Username (Read-Only) -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="username" value="Username" />
            <x-input id="username" type="text" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" wire:model="state.username" disabled />
            <p class="text-xs text-gray-400 mt-1">Username login ditetapkan oleh Administrator.</p>
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="Email" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-2" />
        </div>

        <!-- No HP / WhatsApp -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="no_hp" value="No. WhatsApp / HP" />
            <x-input id="no_hp" type="text" class="mt-1 block w-full" wire:model="state.no_hp" placeholder="08123456789" />
            <x-input-error for="no_hp" class="mt-2" />
        </div>

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
            <div class="col-span-6 sm:col-span-4">
                <p class="text-xs text-amber-600 font-bold">
                    Email Anda belum terverifikasi.
                    <button type="button" class="underline text-xs text-[#5E17EB] hover:text-purple-800" wire:click.prevent="sendEmailVerification">
                        Klik di sini untuk mengirim ulang link verifikasi.
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-bold text-xs text-emerald-600">
                        Link verifikasi baru telah dikirimkan ke alamat email Anda.
                    </p>
                @endif
            </div>
        @endif
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            <span class="text-xs font-bold text-emerald-600 flex items-center">
                <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Profil berhasil diperbarui.
            </span>
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            Simpan Perubahan
        </x-button>
    </x-slot>
</x-form-section>
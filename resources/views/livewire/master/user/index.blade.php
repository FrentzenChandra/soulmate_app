<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>🏢 Kantor Pusat (HQ)</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Master Pengguna</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Pengguna (Owner & Superadmin)</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola hak akses pemilik bisnis (Owner) dan administrator operasional per cabang (Superadmin).</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-sm shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>Tambah Pengguna Baru</span>
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama, username, email, HP..." 
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-gray-50/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] transition-all">
        </div>

        <!-- Filter Role & Cabang -->
        <div class="flex items-center space-x-3 w-full md:w-auto">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Role:</span>
                <select wire:model.live="roleFilter" 
                        class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-purple-400">
                    <option value="semua">Semua Role</option>
                    <option value="Owner">👑 Owner</option>
                    <option value="Superadmin">🛡️ Superadmin</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-500 whitespace-nowrap">Cabang:</span>
                <select wire:model.live="cabangFilter" 
                        class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-purple-400">
                    <option value="semua">Semua Wilayah</option>
                    <option value="hq">🏢 Kantor Pusat (Owner)</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white card-custom rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5">Nama Pengguna</th>
                        <th class="py-3.5 px-4">Username & Email</th>
                        <th class="py-3.5 px-4 text-center">Tipe Role</th>
                        <th class="py-3.5 px-4">Wilayah Tugas / Cabang</th>
                        <th class="py-3.5 px-4">No. HP</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- User Name & Avatar -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl {{ $u->isOwner() ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }} font-extrabold flex items-center justify-center text-xs shadow-xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 block leading-tight">
                                            {{ $u->name }}
                                            @if($u->id === auth()->id())
                                                <span class="ml-1 text-[10px] bg-purple-100 text-purple-700 font-bold px-1.5 py-0.5 rounded-full">Anda</span>
                                            @endif
                                        </span>
                                        <span class="text-[10px] text-gray-400">ID: #{{ $u->id }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Username & Email -->
                            <td class="py-4 px-4">
                                <span class="font-bold text-gray-800 block">{{ $u->username ?? '-' }}</span>
                                <span class="text-[11px] text-gray-400">{{ $u->email }}</span>
                            </td>

                            <!-- Role Badge -->
                            <td class="py-4 px-4 text-center">
                                @if($u->isOwner())
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 font-extrabold text-[11px] border border-purple-200">
                                        <span>👑 Owner</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-extrabold text-[11px] border border-blue-200">
                                        <span>🛡️ Superadmin</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Cabang -->
                            <td class="py-4 px-4">
                                @if($u->isOwner())
                                    <span class="inline-flex items-center space-x-1 text-gray-600 font-bold">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Semua Cabang (Global HQ)</span>
                                    </span>
                                @else
                                    <div class="flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span class="font-bold text-gray-800">{{ $u->cabang?->nama_cabang ?? 'Belum Ditugaskan' }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Kontak -->
                            <td class="py-4 px-4">
                                <span class="text-gray-700 font-medium">{{ $u->no_hp ?? '-' }}</span>
                            </td>

                            <!-- Status & Toggle -->
                            <td class="py-4 px-4 text-center">
                                @if($u->id === auth()->id())
                                    <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Aktif (Anda)</span>
                                    </span>
                                @else
                                    <button wire:click="toggleStatus({{ $u->id }})"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer {{ $u->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-300' }}">
                                        <span class="w-2 h-2 rounded-full {{ $u->status === 'aktif' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                        <span>{{ ucfirst($u->status) }}</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-right">
                                <button wire:click="openEditModal({{ $u->id }})" 
                                        class="p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer"
                                        title="Edit Akun Pengguna">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <p class="font-bold text-gray-600">Tidak ada pengguna ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau tambah pengguna baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah / Edit Pengguna -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-[#0E1726] to-[#5E17EB] px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white" id="modal-title">
                                    {{ $isEditMode ? 'Edit Akun Pengguna' : 'Tambah Pengguna Baru' }}
                                </h3>
                                <p class="text-xs text-purple-200">Tentukan role dan informasi login pengguna</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="text-gray-300 hover:text-white cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">

                            <!-- Pilihan Role (Owner vs Superadmin) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Pilih Peran Akun (Role) <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <!-- Role Superadmin Card -->
                                    <label class="relative flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition-all {{ $role_name === 'Superadmin' ? 'border-[#0052FF] bg-blue-50/50' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" wire:model.live="role_name" value="Superadmin" class="sr-only">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                            </span>
                                            <span class="text-xs font-extrabold text-gray-900">Superadmin</span>
                                        </div>
                                        <span class="text-[10px] text-gray-500 mt-1">Admin operasional khusus di 1 cabang toko</span>
                                    </label>

                                    <!-- Role Owner Card -->
                                    <label class="relative flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition-all {{ $role_name === 'Owner' ? 'border-[#5E17EB] bg-purple-50/50' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" wire:model.live="role_name" value="Owner" class="sr-only">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                            </span>
                                            <span class="text-xs font-extrabold text-gray-900">Owner</span>
                                        </div>
                                        <span class="text-[10px] text-gray-500 mt-1">Pemilik bisnis, hak akses penuh seluruh cabang</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Assignment Cabang (Hanya jika role Superadmin) -->
                            @if($role_name === 'Superadmin')
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Pilih Cabang Penugasan <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model="cabang_id" 
                                            class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#0052FF] focus:border-[#0052FF] @error('cabang_id') border-rose-500 @enderror">
                                        <option value="">-- Pilih Cabang --</option>
                                        @foreach($cabangs as $c)
                                            <option value="{{ $c->id }}">{{ $c->nama_cabang }} ({{ $c->kode_cabang }})</option>
                                        @endforeach
                                    </select>
                                    @error('cabang_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            @else
                                <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Akun <strong>Owner</strong> memiliki akses lintas cabang secara global (HQ).</span>
                                </div>
                            @endif


                            <!-- Nama Lengkap -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Lengkap <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       wire:model="name"
                                       placeholder="Contoh: Budi Santoso"
                                       class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('name') border-rose-500 @enderror">
                                @error('name') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Username -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Username Login <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           wire:model="username"
                                           placeholder="Contoh: budi_admin"
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('username') border-rose-500 @enderror">
                                    @error('username') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Alamat Email <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="email" 
                                           wire:model="email"
                                           placeholder="Contoh: budi@soulmate.test"
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('email') border-rose-500 @enderror">
                                    @error('email') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Password -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Password @if(!$isEditMode) <span class="text-rose-500">*</span> @else <span class="text-gray-400 font-normal">(Kosongkan jika tdk diubah)</span> @endif
                                    </label>
                                    <input type="password" 
                                           wire:model="password"
                                           placeholder="Minimal 6 karakter"
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('password') border-rose-500 @enderror">
                                    @error('password') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- No HP -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        No. Handphone / WhatsApp
                                    </label>
                                    <input type="text" 
                                           wire:model="no_hp"
                                           placeholder="Contoh: 081234567890"
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                    @error('no_hp') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status Akun -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Status Akun <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="status" 
                                        class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                    <option value="aktif">Aktif (Bisa Login)</option>
                                    <option value="nonaktif">Nonaktif (Diblokir)</option>
                                </select>
                                @error('status') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-3xl">
                            <button type="button" 
                                    wire:click="closeModal"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-xs font-bold text-white shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
                                {{ $isEditMode ? 'Simpan Perubahan' : 'Buat Akun Pengguna' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

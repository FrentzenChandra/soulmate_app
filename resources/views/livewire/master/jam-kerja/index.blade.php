<div class="space-y-6">

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="px-4 py-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-xs font-bold">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>Kantor Pusat (HQ)</span>
                <span>/</span>
                <span class="text-[#5E17EB] font-bold">Master Jam Masuk</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Jam Masuk Pegawai</h1>
            <p class="text-xs text-gray-500 mt-0.5">Atur rentang jam shift 24 jam, batas kedatangan awal, target jam masuk, dan toleransi keterlambatan.</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-sm shadow-lg shadow-purple-900/30 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Aturan Shift</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white card-custom p-4 rounded-2xl border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3 w-full md:w-auto">
            <label class="text-xs font-bold text-gray-600 whitespace-nowrap">Filter Cabang:</label>
            <select wire:model.live="cabangFilter"
                    class="bg-gray-50 border border-gray-200 text-xs font-bold rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-[#5E17EB]">
                <option value="">Semua Aturan</option>
                <option value="global">Default Global (Semua Cabang)</option>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}">Khusus Cabang: {{ $c->nama_cabang }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <x-table-size-selector />
        </div>
    </div>

    <!-- Table Card -->
    <div x-data="{ tableSize: localStorage.getItem('soulmate_table_size') || 'md' }"
         x-on:table-size-changed.window="tableSize = $event.detail"
         :class="'table-size-' + tableSize"
         class="bg-white card-custom rounded-2xl border border-gray-100 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5">Lingkup Cabang</th>
                        <th class="py-3.5 px-4 text-center">Shift</th>
                        <th class="py-3.5 px-4">Rentang Shift</th>
                        <th class="py-3.5 px-4">Buka Wajar</th>
                        <th class="py-3.5 px-4">Target Masuk</th>
                        <th class="py-3.5 px-4 text-center">Batas Kritis</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Diubah Terakhir</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <x-table-skeleton :columns="9" :rows="3" target="cabangFilter" />
                <tbody wire:loading.remove wire:target="cabangFilter" class="divide-y divide-gray-100">
                    @forelse($jamKerjas as $rule)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Lingkup Cabang -->
                            <td class="py-4 px-5">
                                @if($rule->cabang_id === null)
                                    <div class="inline-flex items-center space-x-2 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-extrabold text-[11px] border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#5E17EB]"></span>
                                        <span>Default Global (Semua Cabang)</span>
                                    </div>
                                @else
                                    <div class="inline-flex items-center space-x-2 px-2.5 py-1 rounded-lg bg-blue-50 text-[#0052FF] font-extrabold text-[11px] border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#0052FF]"></span>
                                        <span>Khusus: {{ $rule->cabang?->nama_cabang }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Shift Badge -->
                            <td class="py-4 px-4 text-center">
                                @if(($rule->nama_shift->value ?? $rule->nama_shift) === 'pagi')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#0052FF] border border-blue-200">
                                        Shift Pagi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-[#5E17EB] border border-purple-200">
                                        Shift Sore
                                    </span>
                                @endif
                            </td>

                            <!-- Rentang Shift -->
                            <td class="py-4 px-4 font-mono font-bold text-gray-800">
                                <span class="text-[#5E17EB]">{{ substr((string) ($rule->jam_rentang_mulai ?? '00:00'), 0, 5) }}</span>
                                <span class="text-gray-400 font-normal">s/d</span>
                                <span class="text-[#5E17EB]">{{ substr((string) $rule->jam_cutoff, 0, 5) }}</span>
                            </td>

                            <!-- Buka Wajar -->
                            <td class="py-4 px-4 font-mono font-semibold text-gray-700">
                                {{ substr((string) $rule->jam_mulai, 0, 5) }}
                                <span class="text-[10px] text-gray-400 block font-sans">Sebelum ini: Awal</span>
                            </td>

                            <!-- Target Masuk -->
                            <td class="py-4 px-4 font-semibold text-gray-800">
                                <span class="font-mono font-bold text-emerald-700">{{ substr((string) $rule->jam_masuk, 0, 5) }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono font-bold ml-1">+{{ $rule->toleransi_menit }}m</span>
                            </td>

                            <!-- Batas Kritis -->
                            <td class="py-4 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 font-mono text-xs font-bold border border-amber-200">
                                    > {{ $rule->batas_kritis_menit ?? 120 }} Menit
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 text-center">
                                <button type="button"
                                        wire:click="toggleStatus({{ $rule->id }})"
                                        wire:loading.attr="disabled"
                                        class="cursor-pointer transition-transform active:scale-95">
                                    @if($rule->is_aktif)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-extrabold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 text-[10px] font-extrabold border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </button>
                            </td>

                            <!-- Petugas Terakhir -->
                            <td class="py-4 px-4 text-gray-600">
                                <span class="font-bold text-gray-900 block">{{ $rule->updatedBy?->name ?? $rule->createdBy?->name ?? 'Sistem' }}</span>
                                <span class="text-[10px] text-gray-400">{{ $rule->updated_at ? $rule->updated_at->format('d/m/Y H:i') : '-' }}</span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1">
                                    <button wire:click="openEditModal({{ $rule->id }})"
                                            class="p-1.5 text-gray-500 hover:text-[#5E17EB] hover:bg-purple-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Aturan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    @if($rule->cabang_id !== null)
                                        <button wire:click="deleteJamKerja({{ $rule->id }})"
                                                wire:confirm="Yakin ingin menghapus aturan khusus cabang ini? Pengaturan akan kembali mengikuti default global."
                                                class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Hapus Override Cabang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="font-bold text-xs">Belum ada aturan shift jam masuk.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit Jam Kerja -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    
                    <!-- Header Modal -->
                    <div class="bg-[#5E17EB] px-6 sm:px-7 py-5 sm:py-6 text-white flex items-center justify-between rounded-t-3xl gap-4 shadow-xs">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-white/15 text-white flex items-center justify-center border border-white/20 shrink-0 shadow-xs p-2.5">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white tracking-tight leading-snug" id="modal-title">
                                    {{ $isEditMode ? 'Edit Aturan Jam Masuk' : 'Tambah Aturan Jam Masuk' }}
                                </h3>
                                <p class="text-xs text-purple-200 font-medium mt-0.5">Konfigurasi rentang shift, target jam, dan batas kritis</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <!-- Lingkup Cabang -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Lingkup Cabang
                                </label>
                                <select wire:model="cabang_id"
                                        class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB]">
                                    <option value="">Default Global (Semua Cabang)</option>
                                    @foreach($cabangs as $c)
                                        <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                                    @endforeach
                                </select>
                                <span class="text-[10px] text-gray-400 block mt-1">Kosongkan cabang untuk berlaku di semua cabang.</span>
                                @error('cabang_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Shift -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Shift Kerja <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model.live="nama_shift"
                                        class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('nama_shift') border-rose-500 @enderror">
                                    <option value="pagi">Shift Pagi</option>
                                    <option value="sore">Shift Sore</option>
                                </select>
                                @error('nama_shift') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Rentang Shift (Dari - Sampai) -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Dari Jam <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="time"
                                           wire:model="jam_rentang_mulai"
                                           class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('jam_rentang_mulai') border-rose-500 @enderror">
                                    @error('jam_rentang_mulai') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Sampai Jam <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="time"
                                           wire:model="jam_cutoff"
                                           class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('jam_cutoff') border-rose-500 @enderror">
                                    @error('jam_cutoff') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Buka Toko Wajar & Target Jam Masuk -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Buka Wajar <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="time"
                                           wire:model="jam_mulai"
                                           class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('jam_mulai') border-rose-500 @enderror">
                                    <span class="text-[10px] text-gray-400 block mt-0.5">Sebelum jam ini: Lebih Awal</span>
                                    @error('jam_mulai') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Target Masuk <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="time"
                                           wire:model="jam_masuk"
                                           class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('jam_masuk') border-rose-500 @enderror">
                                    <span class="text-[10px] text-gray-400 block mt-0.5">Batas jam kerja dimulai</span>
                                    @error('jam_masuk') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Toleransi & Batas Kritis -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Toleransi (Menit) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number"
                                           min="0"
                                           max="120"
                                           wire:model="toleransi_menit"
                                           class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('toleransi_menit') border-rose-500 @enderror">
                                    @error('toleransi_menit') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Batas Kritis (Menit) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number"
                                           min="10"
                                           max="720"
                                           wire:model="batas_kritis_menit"
                                           class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] @error('batas_kritis_menit') border-rose-500 @enderror">
                                    <span class="text-[10px] text-gray-400 block mt-0.5">> batas ini: Kritis (Amber)</span>
                                    @error('batas_kritis_menit') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status Aktif -->
                            <div class="pt-2">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" wire:model="is_aktif" class="w-4 h-4 text-[#5E17EB] rounded border-gray-300 focus:ring-[#5E17EB]">
                                    <div>
                                        <span class="text-xs font-bold text-gray-800 block">Status Aktif</span>
                                        <span class="text-[10px] text-gray-400">Aturan ini digunakan untuk mengevaluasi kehadiran pegawai</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-gray-50 px-6 py-4 rounded-b-3xl border-t border-gray-100 flex items-center justify-end space-x-3">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 rounded-xl hover:bg-gray-200/60 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-purple-900/30 transition-all cursor-pointer">
                                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Aturan' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

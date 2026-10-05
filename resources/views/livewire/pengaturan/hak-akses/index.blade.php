<div class="space-y-6 pb-20">

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <span>Kantor Pusat (HQ)</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Tata Kelola Pusat</span>
                <span>/</span>
                <span class="text-purple-600 font-bold">Atur Akses</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                    Pengaturan Hak Akses Peran
                </h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-purple-100 text-purple-700 border border-purple-200">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Owner Exclusive
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                Tentukan menu sidebar yang tampil serta tombol aksi CRUD yang dapat diakses oleh masing-masing peran staf.
            </p>
        </div>

        <!-- Stat Counter Card -->
        <div class="flex items-center space-x-3 bg-white px-4 py-2.5 rounded-2xl border border-gray-100 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div>
                <span class="text-[11px] font-extrabold text-gray-400 block uppercase tracking-wider">Izin Terpilih ({{ $selectedRole }})</span>
                <span class="text-sm font-extrabold text-purple-700">
                    {{ count($selectedPermissions) }} <span class="text-xs text-gray-400 font-semibold">/ {{ $totalSystemPermissions }} Izin</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Role Tab Selector Card -->
    <div class="bg-white card-custom p-3 rounded-2xl border border-gray-100 shadow-xs">
        <div class="flex items-center justify-between mb-2 px-1">
            <span class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">Pilih Peran yang Ingin Diatur:</span>
            <span class="text-[11px] text-gray-400 font-medium">Role Owner otomatis memiliki akses 100% penuh</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
            @foreach($roles as $role)
                @php
                    $isActive = $selectedRole === $role;
                    $badgeColors = [
                        'Superadmin' => $isActive ? 'bg-[#0052FF] text-white shadow-blue-900/30' : 'bg-blue-50/70 text-blue-700 hover:bg-blue-100 border-blue-200',
                        'Teknisi' => $isActive ? 'bg-[#0D9488] text-white shadow-teal-900/30' : 'bg-teal-50/70 text-teal-700 hover:bg-teal-100 border-teal-200',
                        'Admin' => $isActive ? 'bg-[#E6007A] text-white shadow-pink-900/30' : 'bg-pink-50/70 text-pink-700 hover:bg-pink-100 border-pink-200',
                        'Bendahara' => $isActive ? 'bg-[#D97706] text-white shadow-amber-900/30' : 'bg-amber-50/70 text-amber-700 hover:bg-amber-100 border-amber-200',
                        'Operasional' => $isActive ? 'bg-[#0891B2] text-white shadow-cyan-900/30' : 'bg-cyan-50/70 text-cyan-700 hover:bg-cyan-100 border-cyan-200',
                        'Magang' => $isActive ? 'bg-[#4F46E5] text-white shadow-indigo-900/30' : 'bg-indigo-50/70 text-indigo-700 hover:bg-indigo-100 border-indigo-200',
                    ];
                @endphp
                <button type="button"
                        wire:click="selectRole('{{ $role }}')"
                        class="px-3.5 py-3 rounded-xl font-extrabold text-xs transition-all cursor-pointer border flex flex-col items-center justify-center space-y-1 {{ $badgeColors[$role] ?? 'bg-gray-100 text-gray-700' }} {{ $isActive ? 'shadow-md scale-[1.02] border-transparent' : '' }}">
                    <div class="flex items-center space-x-1.5">
                        <span>{{ $role }}</span>
                        @if($isActive)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Quick Actions Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-gray-50/80 p-3.5 rounded-2xl border border-gray-200/80">
        <div class="flex items-center space-x-2">
            <button type="button"
                    wire:click="selectAll"
                    class="px-3 py-1.5 rounded-xl bg-white text-gray-700 hover:text-purple-700 hover:bg-purple-50 text-xs font-bold border border-gray-200 shadow-xs transition-colors cursor-pointer">
                Pilih Semua
            </button>
            <button type="button"
                    wire:click="deselectAll"
                    class="px-3 py-1.5 rounded-xl bg-white text-gray-700 hover:text-rose-700 hover:bg-rose-50 text-xs font-bold border border-gray-200 shadow-xs transition-colors cursor-pointer">
                Kosongkan Semua
            </button>
            <button type="button"
                    wire:click="resetToDefault"
                    class="px-3 py-1.5 rounded-xl bg-white text-gray-700 hover:text-blue-700 hover:bg-blue-50 text-xs font-bold border border-gray-200 shadow-xs transition-colors cursor-pointer flex items-center space-x-1">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>Reset ke Default ({{ $selectedRole }})</span>
            </button>
        </div>

        <button type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-900/30 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="save">Simpan Pengaturan Akses</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>

    <!-- Permission Matrix Groups -->
    <div class="space-y-6">
        @foreach($schema as $groupKey => $group)
            <div class="bg-white card-custom rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <!-- Group Header -->
                <div class="bg-gray-50/90 px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900 tracking-tight">{{ $group['group_label'] }}</h2>
                        <p class="text-[11px] text-gray-500">{{ $group['description'] }}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-200/70 text-gray-600">
                        {{ count($group['modules']) }} Modul
                    </span>
                </div>

                <!-- Modules List in this Group -->
                <div class="divide-y divide-gray-100">
                    @foreach($group['modules'] as $modKey => $mod)
                        @php
                            $mainPerm = $mod['main_permission'];
                            $actionPerms = array_keys($mod['actions'] ?? []);
                            $isMainActive = in_array($mainPerm, $selectedPermissions);
                            
                            // Hitung sub-actions yang aktif
                            $activeSubCount = count(array_intersect($actionPerms, $selectedPermissions));
                            $totalSubCount = count($actionPerms);
                        @endphp
                        <div class="p-5 transition-colors {{ $isMainActive ? 'bg-white' : 'bg-gray-50/40 opacity-75' }}">
                            
                            <!-- Master Module Toggle Row -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox"
                                           id="mod_{{ $modKey }}"
                                           wire:click="toggleModule('{{ $mainPerm }}', {{ json_encode($actionPerms) }})"
                                           {{ $isMainActive ? 'checked' : '' }}
                                           class="mt-0.5 w-4 h-4 rounded text-[#5E17EB] focus:ring-[#5E17EB] border-gray-300 cursor-pointer">
                                    <div>
                                        <label for="mod_{{ $modKey }}" class="text-sm font-extrabold text-gray-900 cursor-pointer flex items-center space-x-2">
                                            <span>{{ $mod['label'] }}</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isMainActive ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                                                {{ $isMainActive ? 'Tampil di Sidebar' : 'Tersembunyi' }}
                                            </span>
                                        </label>
                                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $mod['description'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 sm:self-center pl-7 sm:pl-0">
                                    <span class="text-[11px] font-bold text-gray-400">Aksi Aktif:</span>
                                    <span class="text-xs font-extrabold {{ $activeSubCount > 0 ? 'text-purple-700' : 'text-gray-400' }}">
                                        {{ $activeSubCount }} / {{ $totalSubCount }}
                                    </span>
                                </div>
                            </div>

                            <!-- Granular Sub-Action Checkboxes -->
                            @if(!empty($mod['actions']))
                                <div class="mt-3.5 pl-7">
                                    <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-2">
                                        Hak Aksi & Tombol CRUD Modul:
                                    </span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
                                        @foreach($mod['actions'] as $actPerm => $actLabel)
                                            @php
                                                $isActActive = in_array($actPerm, $selectedPermissions);
                                            @endphp
                                            <label class="relative flex items-center space-x-2.5 p-2.5 rounded-xl border transition-all cursor-pointer {{ $isActActive ? 'bg-purple-50/60 border-purple-200 text-purple-900' : 'bg-gray-50/60 border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                                <input type="checkbox"
                                                       wire:model.live="selectedPermissions"
                                                       value="{{ $actPerm }}"
                                                       class="w-3.5 h-3.5 rounded text-[#5E17EB] focus:ring-[#5E17EB] border-gray-300 cursor-pointer">
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-xs font-bold block truncate leading-tight">{{ $actLabel }}</span>
                                                    <span class="text-[9px] text-gray-400 font-mono block truncate">{{ $actPerm }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Floating Bottom Bar for Quick Saving -->
    <div class="fixed bottom-4 inset-x-4 md:left-72 md:right-8 z-30 flex items-center justify-between bg-white/95 backdrop-blur-md px-5 py-3 rounded-2xl border border-purple-200 shadow-xl shadow-purple-950/10">
        <div class="flex items-center space-x-3">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-600 animate-pulse"></span>
            <div>
                <span class="text-xs font-extrabold text-gray-800 block leading-tight">
                    Mengatur Peran: <span class="text-purple-700">{{ $selectedRole }}</span>
                </span>
                <span class="text-[11px] text-gray-500 font-medium">
                    {{ count($selectedPermissions) }} izin aktif terpilih
                </span>
            </div>
        </div>

        <button type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-[#5E17EB] hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-900/30 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="save">Simpan Pengaturan Akses</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>

</div>

<?php

namespace App\Livewire\Pengaturan\HakAkses;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app')]
#[Title('Atur Akses Peran - Soulmate Komputer')]
class Index extends Component
{
    public array $roles = [
        'Superadmin',
        'Teknisi',
        'Admin',
        'Bendahara',
        'Operasional',
        'Magang',
    ];

    public string $selectedRole = 'Superadmin';

    public array $selectedPermissions = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) {
            abort(403, 'Akses pengaturan hak akses peran dibatasi khusus untuk Owner (HQ).');
        }

        // Pastikan permissions terdaftar di database
        $this->ensurePermissionsExist();

        // Muat permissions untuk role default
        $this->loadRolePermissions($this->selectedRole);
    }

    protected function ensurePermissionsExist(): void
    {
        $allPermissions = PermissionService::getAllPermissionNames();
        $existing = Permission::pluck('name')->toArray();
        $missing = array_diff($allPermissions, $existing);

        if (! empty($missing)) {
            foreach ($missing as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // Pastikan role-role default telah memiliki permissions jika masih kosong
        $presets = PermissionService::getDefaultRolePresets();
        foreach ($presets as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if ($role->permissions()->count() === 0) {
                $role->syncPermissions($perms);
            }
        }
    }

    public function selectRole(string $role): void
    {
        if (in_array($role, $this->roles)) {
            $this->selectedRole = $role;
            $this->loadRolePermissions($role);
        }
    }

    protected function loadRolePermissions(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function toggleModule(string $mainPermission, array $actionPermissions): void
    {
        $allModulePerms = array_merge([$mainPermission], $actionPermissions);
        $isMainChecked = in_array($mainPermission, $this->selectedPermissions);

        if ($isMainChecked) {
            // Uncheck semua izin modul ini
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $allModulePerms));
        } else {
            // Check semua izin modul ini
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $allModulePerms)));
        }
    }

    public function updatedSelectedPermissions(): void
    {
        $schema = PermissionService::getModuleSchema();
        foreach ($schema as $group) {
            foreach ($group['modules'] as $mod) {
                $mainPerm = $mod['main_permission'];
                $actionPerms = array_keys($mod['actions'] ?? []);

                // Jika ada salah satu sub-action yang aktif tapi main_permission belum, aktifkan main_permission
                $hasActiveSubAction = ! empty(array_intersect($actionPerms, $this->selectedPermissions));
                if ($hasActiveSubAction && ! in_array($mainPerm, $this->selectedPermissions)) {
                    $this->selectedPermissions[] = $mainPerm;
                }
            }
        }
        $this->selectedPermissions = array_values(array_unique($this->selectedPermissions));
    }

    public function selectAll(): void
    {
        $this->selectedPermissions = PermissionService::getAllPermissionNames();
    }

    public function deselectAll(): void
    {
        $this->selectedPermissions = [];
    }

    public function resetToDefault(): void
    {
        $presets = PermissionService::getDefaultRolePresets();
        $this->selectedPermissions = $presets[$this->selectedRole] ?? [];
        $this->dispatch('swal:toast', [
            'type' => 'info',
            'title' => 'Reset ke Default',
            'text' => "Daftar hak akses peran {$this->selectedRole} dikembalikan ke konfigurasi standar.",
        ]);
    }

    public function save(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) {
            $this->dispatch('swal:error', ['title' => 'Akses Ditolak', 'text' => 'Hanya Owner yang berhak menyimpan pengaturan ini.']);

            return;
        }

        $role = Role::firstOrCreate(['name' => $this->selectedRole, 'guard_name' => 'web']);

        // Sinkronkan permission ke database Spatie
        $role->syncPermissions($this->selectedPermissions);

        // Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $count = count($this->selectedPermissions);
        $this->dispatch('swal:success', [
            'title' => 'Berhasil Disimpan!',
            'text' => "Hak akses peran {$this->selectedRole} berhasil diperbarui ({$count} izin aktif).",
        ]);
    }

    public function render()
    {
        $schema = PermissionService::getModuleSchema();

        return view('livewire.pengaturan.hak-akses.index', [
            'schema' => $schema,
            'totalSystemPermissions' => count(PermissionService::getAllPermissionNames()),
        ]);
    }
}

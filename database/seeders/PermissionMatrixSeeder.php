<?php

namespace Database\Seeders;

use App\Services\PermissionService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Pastikan seluruh permissions terdaftar di database
        $allPermissions = PermissionService::getAllPermissionNames();
        foreach ($allPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 2. Terapkan default role presets
        $presets = PermissionService::getDefaultRolePresets();
        foreach ($presets as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            // Sinkronkan permissions jika role belum memiliki permissions sama sekali atau saat seeding
            if ($role->permissions()->count() === 0) {
                $role->syncPermissions($perms);
            }
        }

        // Pastikan role Owner ada (Owner tidak butuh sync permissions karena otomatis bypass via Gate::before)
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
    }
}

<?php

namespace Tests\Traits;

use App\Models\Cabang;
use App\Models\User;
use Database\Seeders\PermissionMatrixSeeder;
use Spatie\Permission\PermissionRegistrar;

trait InteractsWithRolesAndPermissions
{
    protected ?Cabang $defaultTestCabang = null;

    /**
     * Inisialisasi seeder role & permission dan reset cache Spatie.
     */
    protected function setupRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(PermissionMatrixSeeder::class);

        if (! $this->defaultTestCabang) {
            $this->defaultTestCabang = Cabang::firstOrCreate(
                ['kode_cabang' => 'CB-TEST'],
                [
                    'nama_cabang' => 'Cabang Testing PKP',
                    'alamat' => 'Jl. Testing No. 1',
                    'no_wa' => '081234567890',
                    'status' => 'aktif',
                ]
            );
        }
    }

    /**
     * Login sebagai pengguna dengan role tertentu.
     */
    protected function actingAsRole(string $roleName, array $attributes = [], ?Cabang $cabang = null): User
    {
        $this->setupRolesAndPermissions();

        $cabang = $cabang ?? $this->defaultTestCabang;
        $isOwner = strtolower($roleName) === 'owner';

        $userData = array_merge([
            'name' => "User {$roleName}",
            'username' => strtolower($roleName) . '_' . uniqid(),
            'email' => strtolower($roleName) . '_' . uniqid() . '@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $isOwner ? null : $cabang->id,
            'status' => 'aktif',
        ], $attributes);

        $user = User::create($userData);
        $user->assignRole($roleName);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($user);

        return $user;
    }

    /**
     * Login sebagai pengguna dengan permission langsung (bukan lewat role).
     */
    protected function actingAsUserWithPermission(string|array $permissions, array $attributes = [], ?Cabang $cabang = null): User
    {
        $this->setupRolesAndPermissions();

        $cabang = $cabang ?? $this->defaultTestCabang;
        $perms = (array) $permissions;

        $userData = array_merge([
            'name' => 'User Direct Permission',
            'username' => 'perm_' . uniqid(),
            'email' => 'perm_' . uniqid() . '@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ], $attributes);

        $user = User::create($userData);
        $user->givePermissionTo($perms);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($user);

        return $user;
    }

    /**
     * Login sebagai pengguna dengan multiple roles.
     */
    protected function actingAsUserWithRoles(array $roleNames, array $attributes = [], ?Cabang $cabang = null): User
    {
        $this->setupRolesAndPermissions();

        $cabang = $cabang ?? $this->defaultTestCabang;

        $userData = array_merge([
            'name' => 'User Multi Role',
            'username' => 'multi_' . uniqid(),
            'email' => 'multi_' . uniqid() . '@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ], $attributes);

        $user = User::create($userData);
        $user->syncRoles($roleNames);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($user);

        return $user;
    }
}


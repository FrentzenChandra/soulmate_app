<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $teknisiRole = Role::firstOrCreate(['name' => 'Teknisi']);
        $kasirRole = Role::firstOrCreate(['name' => 'Kasir']);

        $cabangPkp = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $cabangSgl = Cabang::where('kode_cabang', 'CB-SGL')->first();
        $cabangKba = Cabang::where('kode_cabang', 'CB-KBA')->first();

        // 2. Akun Owner & Dev (Akses Penuh Semua Cabang & Semua Menu)
        $owner = User::updateOrCreate(
            ['email' => 'owner@soulmate.test'],
            [
                'name' => 'Owner / Pemilik Bisnis',
                'username' => 'owner',
                'password' => Hash::make('password'),
                'cabang_id' => null, // null = Akses semua cabang tanpa batasan
                'no_hp' => '081199887766',
                'status' => 'aktif',
            ]
        );
        $owner->syncRoles([$ownerRole]);

        $dev = User::updateOrCreate(
            ['email' => 'dev@soulmate.test'],
            [
                'name' => 'Developer Mode',
                'username' => 'dev',
                'password' => Hash::make('password'),
                'cabang_id' => null, // null = Akses semua cabang tanpa batasan
                'no_hp' => '081200000000',
                'status' => 'aktif',
            ]
        );
        $dev->syncRoles([$ownerRole]);

        // 3. Akun Superadmin (Admin Cabang - Akses Semua Menu di 1 Cabang)
        $superadminPkp = User::updateOrCreate(
            ['email' => 'superadmin@soulmate.test'],
            [
                'name' => 'Superadmin Cabang PKP',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPkp?->id, // Terikat ke Cabang Pangkalpinang
                'no_hp' => '081234567890',
                'status' => 'aktif',
            ]
        );
        $superadminPkp->syncRoles([$superadminRole]);

        $superadminSgl = User::updateOrCreate(
            ['email' => 'superadmin.sgl@soulmate.test'],
            [
                'name' => 'Superadmin Cabang SGL',
                'username' => 'superadmin_sgl',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSgl?->id, // Terikat ke Cabang Sungailiat
                'no_hp' => '081234567891',
                'status' => 'aktif',
            ]
        );
        $superadminSgl->syncRoles([$superadminRole]);

        // 4. Akun Teknisi per Cabang
        $teknisiUsers = [
            [
                'name' => 'Budi Teknisi PKP',
                'email' => 'teknisi.pkp@soulmate.test',
                'username' => 'teknisi_pkp',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPkp?->id,
                'no_hp' => '08127111001',
                'status' => 'aktif',
            ],
            [
                'name' => 'Andi Teknisi SGL',
                'email' => 'teknisi.sgl@soulmate.test',
                'username' => 'teknisi_sgl',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSgl?->id,
                'no_hp' => '08127111002',
                'status' => 'aktif',
            ],
            [
                'name' => 'Eko Teknisi KBA',
                'email' => 'teknisi.kba@soulmate.test',
                'username' => 'teknisi_kba',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangKba?->id,
                'no_hp' => '08127111003',
                'status' => 'aktif',
            ],
        ];

        foreach ($teknisiUsers as $data) {
            $user = User::updateOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$teknisiRole]);
        }

        // 5. Akun Kasir per Cabang
        $kasirUsers = [
            [
                'name' => 'Siti Kasir PKP',
                'email' => 'kasir.pkp@soulmate.test',
                'username' => 'kasir_pkp',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPkp?->id,
                'no_hp' => '08127222001',
                'status' => 'aktif',
            ],
            [
                'name' => 'Dewi Kasir SGL',
                'email' => 'kasir.sgl@soulmate.test',
                'username' => 'kasir_sgl',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSgl?->id,
                'no_hp' => '08127222002',
                'status' => 'aktif',
            ],
            [
                'name' => 'Rina Kasir KBA',
                'email' => 'kasir.kba@soulmate.test',
                'username' => 'kasir_kba',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangKba?->id,
                'no_hp' => '08127222003',
                'status' => 'aktif',
            ],
        ];

        foreach ($kasirUsers as $data) {
            $user = User::updateOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$kasirRole]);
        }
    }
}

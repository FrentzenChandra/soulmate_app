
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
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'Bendahara']);
        $kasirRole = Role::firstOrCreate(['name' => 'Kasir']);

        $cabangPkp = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $cabangSgl = Cabang::where('kode_cabang', 'CB-SGL')->first();
        $cabangKba = Cabang::where('kode_cabang', 'CB-KBA')->first();

        // 2. Akun Owner & Dev (Akses Penuh Semua Cabang & Semua Menu)
        $owner = User::withTrashed()->updateOrCreate(
            ['email' => 'owner@soulmate.test'],
            [
                'name' => 'Owner / Pemilik Bisnis',
                'username' => 'owner',
                'password' => Hash::make('password'),
                'cabang_id' => null, // null = Akses semua cabang tanpa batasan
                'no_hp' => '081199887766',
                'status' => 'aktif',
                'deleted_at' => null,
            ]
        );
        $owner->syncRoles([$ownerRole]);


        // 3. Akun Superadmin (Admin Cabang - Akses Semua Menu di 1 Cabang)
        $superadminPkp = User::withTrashed()->updateOrCreate(
            ['email' => 'superadmin@soulmate.test'],
            [
                'name' => 'Superadmin Cabang PKP',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPkp?->id, // Terikat ke Cabang Pangkalpinang
                'no_hp' => '081234567890',
                'status' => 'aktif',
                'deleted_at' => null,
            ]
        );
        $superadminPkp->syncRoles([$superadminRole]);

        $superadminSgl = User::withTrashed()->updateOrCreate(
            ['email' => 'superadmin.sgl@soulmate.test'],
            [
                'name' => 'Superadmin Cabang SGL',
                'username' => 'superadmin_sgl',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSgl?->id, // Terikat ke Cabang Sungailiat
                'no_hp' => '081234567891',
                'status' => 'aktif',
                'deleted_at' => null,
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
                'deleted_at' => null,
            ],
            [
                'name' => 'Andi Teknisi SGL',
                'email' => 'teknisi.sgl@soulmate.test',
                'username' => 'teknisi_sgl',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSgl?->id,
                'no_hp' => '08127111002',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
            [
                'name' => 'Eko Teknisi KBA',
                'email' => 'teknisi.kba@soulmate.test',
                'username' => 'teknisi_kba',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangKba?->id,
                'no_hp' => '08127111003',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
        ];

        foreach ($teknisiUsers as $data) {
            $user = User::withTrashed()->updateOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$teknisiRole]);
        }

        // 5. Akun Admin (Staf Kasir POS) per Cabang
        $adminUsers = [
            [
                'name' => 'Siti Admin PKP',
                'email' => 'admin.pkp@soulmate.test',
                'username' => 'admin_pkp',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPkp?->id,
                'no_hp' => '08127222001',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
            [
                'name' => 'Dewi Admin SGL',
                'email' => 'admin.sgl@soulmate.test',
                'username' => 'admin_sgl',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSgl?->id,
                'no_hp' => '08127222002',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
            [
                'name' => 'Rina Admin KBA',
                'email' => 'admin.kba@soulmate.test',
                'username' => 'admin_kba',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangKba?->id,
                'no_hp' => '08127222003',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
            // Alias akun lama kasir.pkp untuk kompatibilitas
            [
                'name' => 'Siti Kasir PKP',
                'email' => 'kasir.pkp@soulmate.test',
                'username' => 'kasir_pkp',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPkp?->id,
                'no_hp' => '08127222001',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
        ];

        foreach ($adminUsers as $data) {
            $user = User::withTrashed()->updateOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$adminRole]);
        }

        // 6. Akun Bendahara per Cabang
        $bendaharaUsers = [
            [
                'name' => 'Maya Bendahara PKP',
                'email' => 'bendahara.pkp@soulmate.test',
                'username' => 'bendahara_pkp',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangPkp?->id,
                'no_hp' => '08127333001',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
            [
                'name' => 'Lina Bendahara SGL',
                'email' => 'bendahara.sgl@soulmate.test',
                'username' => 'bendahara_sgl',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangSgl?->id,
                'no_hp' => '08127333002',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
            [
                'name' => 'Putri Bendahara KBA',
                'email' => 'bendahara.kba@soulmate.test',
                'username' => 'bendahara_kba',
                'password' => Hash::make('password'),
                'cabang_id' => $cabangKba?->id,
                'no_hp' => '08127333003',
                'status' => 'aktif',
                'deleted_at' => null,
            ],
        ];

        foreach ($bendaharaUsers as $data) {
            $user = User::withTrashed()->updateOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$bendaharaRole]);
        }
    }
}

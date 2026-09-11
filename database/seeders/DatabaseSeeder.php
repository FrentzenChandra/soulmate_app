<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\MasterPpn;
use App\Models\KategoriProduk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CabangSeeder::class,
            RoleAndUserSeeder::class,
            MasterPpnSeeder::class,
            CustomerSeeder::class,
            KategoriDanProdukSeeder::class,
            ServisMasukSeeder::class,
            PenjualanSeeder::class,
        ]);
    }
}

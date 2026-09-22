<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
            MasterServisSeeder::class,
            ServisMasukSeeder::class,
            PenjualanSeeder::class,
            CekKasLogSeeder::class,
            TransaksiFullSeeder::class,
        ]);
    }
}

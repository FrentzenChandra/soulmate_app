<?php

namespace Database\Seeders;

use App\Models\MasterPpn;
use Illuminate\Database\Seeder;

class MasterPpnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ppnList = [
            [
                'nama' => 'Tanpa PPN',
                'persen' => 0.00,
                'is_aktif' => true,
            ],
            [
                'nama' => 'PPN 11%',
                'persen' => 11.00,
                'is_aktif' => true,
            ],
            [
                'nama' => 'PPN 12%',
                'persen' => 12.00,
                'is_aktif' => true,
            ],
        ];

        foreach ($ppnList as $ppn) {
            MasterPpn::updateOrCreate(
                ['nama' => $ppn['nama']],
                $ppn
            );
        }
    }
}

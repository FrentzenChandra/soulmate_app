<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cabangs = [
            [
                'kode_cabang' => 'CB-PKP',
                'nama_cabang' => 'Soulmate Pangkalpinang',
                'alamat' => 'Jl. Masjid Jamik ,Pangkalpinang',
                'no_telp' => '-',
                'no_wa' => '0819-9546-6255',
                'status' => 'aktif',
            ],
            [
                'kode_cabang' => 'CB-SGL',
                'nama_cabang' => 'Soulmate Sungailiat',
                'alamat' => 'Jl. Muhidin No. 45, Sungailiat, Bangka',
                'no_telp' => '0717-911222',
                'no_wa' => '6281271000222',
                'status' => 'aktif',
            ],
            [
                'kode_cabang' => 'CB-KBA',
                'nama_cabang' => 'Soulmate Koba',
                'alamat' => 'Jl. Kenanga No. 88, Koba, Bangka Tengah',
                'no_telp' => '0717-733333',
                'no_wa' => '6281271000333',
                'status' => 'aktif',
            ],
            [
                'kode_cabang' => 'CB-TBO',
                'nama_cabang' => 'Soulmate Toboali (Nonaktif)',
                'alamat' => 'Jl. Sudirman No. 10, Toboali, Bangka Selatan',
                'no_telp' => '0717-844444',
                'no_wa' => '6281271000444',
                'status' => 'nonaktif',
            ],
        ];

        foreach ($cabangs as $cabang) {
            Cabang::updateOrCreate(
                ['kode_cabang' => $cabang['kode_cabang']],
                $cabang
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkp = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $sgl = Cabang::where('kode_cabang', 'CB-SGL')->first();
        $kba = Cabang::where('kode_cabang', 'CB-KBA')->first();

        $customers = [
            // Cabang Pangkalpinang
            [
                'cabang_id' => $pkp?->id,
                'nama' => 'Hendra Setiawan',
                'no_hp' => '081367891234',
                'alamat' => 'Jl. Mentok No. 12, Pangkalpinang',
                'tipe' => 'langganan',
                'foto_url' => null,
            ],
            [
                'cabang_id' => $pkp?->id,
                'nama' => 'Mega Lestari',
                'no_hp' => '081273456789',
                'alamat' => 'Komp. Perumahan Bukit Merapin, Pangkalpinang',
                'tipe' => 'langganan',
                'foto_url' => null,
            ],
            [
                'cabang_id' => $pkp?->id,
                'nama' => 'PT Bangka Media Utama (Bpk. Fajar)',
                'no_hp' => '082178901234',
                'alamat' => 'Jl. RE Martadinata No. 5, Pangkalpinang',
                'tipe' => 'langganan',
                'foto_url' => null,
            ],
            [
                'cabang_id' => $pkp?->id,
                'nama' => 'Budi Walk-In',
                'no_hp' => '081277889900',
                'alamat' => 'Pangkalpinang',
                'tipe' => 'umum',
                'foto_url' => null,
            ],

            // Cabang Sungailiat
            [
                'cabang_id' => $sgl?->id,
                'nama' => 'Rizky Pratama',
                'no_hp' => '085267123456',
                'alamat' => 'Jl. Batin Tikal, Sungailiat',
                'tipe' => 'langganan',
                'foto_url' => null,
            ],
            [
                'cabang_id' => $sgl?->id,
                'nama' => 'Indah Permata',
                'no_hp' => '081923456789',
                'alamat' => 'Jl. Sinar Baru, Sungailiat',
                'tipe' => 'langganan',
                'foto_url' => null,
            ],

            // Cabang Koba
            [
                'cabang_id' => $kba?->id,
                'nama' => 'Agus Budiman',
                'no_hp' => '085389012345',
                'alamat' => 'Jl. Padang Mulia, Koba',
                'tipe' => 'langganan',
                'foto_url' => null,
            ],
        ];

        foreach ($customers as $data) {
            if ($data['cabang_id']) {
                Customer::updateOrCreate(
                    [
                        'cabang_id' => $data['cabang_id'],
                        'no_hp' => $data['no_hp'],
                    ],
                    $data
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\User;
use App\Models\WifiAbsen;
use Illuminate\Database\Seeder;

class WifiAbsenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Owner', 'Superadmin']))->first();
        $creatorId = $creator?->id;

        $cabangs = Cabang::where('status', 'aktif')->get();

        foreach ($cabangs as $cabang) {
            // Localhost / testing IP
            WifiAbsen::firstOrCreate(
                [
                    'cabang_id' => $cabang->id,
                    'ip_address' => '127.0.0.1',
                ],
                [
                    'nama' => 'WiFi Toko ' . $cabang->nama_cabang,
                    'is_aktif' => true,
                    'created_by' => $creatorId,
                ]
            );

            // IPv6 localhost
            WifiAbsen::firstOrCreate(
                [
                    'cabang_id' => $cabang->id,
                    'ip_address' => '::1',
                ],
                [
                    'nama' => 'WiFi Toko IPv6 ' . $cabang->nama_cabang,
                    'is_aktif' => true,
                    'created_by' => $creatorId,
                ]
            );
        }
    }
}


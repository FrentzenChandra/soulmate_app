<?php

namespace Database\Seeders;

use App\Enums\ShiftType;
use App\Models\JamKerja;
use Illuminate\Database\Seeder;

class JamKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Shift Pagi Global (cabang_id = null)
        JamKerja::updateOrCreate(
            [
                'cabang_id' => null,
                'nama_shift' => ShiftType::Pagi->value,
            ],
            [
                'jam_mulai' => '07:00:00',
                'jam_masuk' => '08:00:00',
                'toleransi_menit' => 2,
                'jam_cutoff' => '13:00:00',
                'is_aktif' => true,
            ]
        );

        // 2. Shift Sore Global (cabang_id = null)
        JamKerja::updateOrCreate(
            [
                'cabang_id' => null,
                'nama_shift' => ShiftType::Sore->value,
            ],
            [
                'jam_mulai' => '13:00:00',
                'jam_masuk' => '14:00:00',
                'toleransi_menit' => 2,
                'jam_cutoff' => '13:00:00',
                'is_aktif' => true,
            ]
        );
    }
}


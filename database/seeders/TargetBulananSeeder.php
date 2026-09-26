<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\TargetDivisi;
use App\Models\TargetUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class TargetBulananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = (int) now()->format('Y');
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first() ?? User::first();
        $cabangs = Cabang::where('status', 'aktif')->get();

        foreach ($cabangs as $cabang) {
            // 1. Seed Target Divisi per Cabang
            $divisiTargets = [
                'laptop' => 20,
                'printer' => 12,
                'komputer' => 15,
                'admin' => 25,
            ];

            foreach ($divisiTargets as $divisi => $target) {
                TargetDivisi::updateOrCreate(
                    [
                        'cabang_id' => $cabang->id,
                        'tahun' => $currentYear,
                        'divisi' => $divisi,
                    ],
                    [
                        'target_bulanan' => $target,
                        'created_by' => $superadmin?->id,
                    ]
                );
            }

            // 2. Seed Target Staf per Cabang
            $staffUsers = User::where('cabang_id', $cabang->id)
                ->where('status', 'aktif')
                ->get();

            foreach ($staffUsers as $staf) {
                TargetUser::updateOrCreate(
                    [
                        'cabang_id' => $cabang->id,
                        'user_id' => $staf->id,
                        'tahun' => $currentYear,
                    ],
                    [
                        'target_bulanan' => 15,
                        'created_by' => $superadmin?->id,
                    ]
                );
            }
        }
    }
}

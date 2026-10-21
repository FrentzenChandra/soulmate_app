<?php

namespace Database\Factories;

use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absensi>
 */
class AbsensiFactory extends Factory
{
    protected $model = Absensi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $waktu = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'cabang_id' => Cabang::factory(),
            'user_id' => User::factory(),
            'tanggal' => $waktu->format('Y-m-d'),
            'waktu_absen' => $waktu,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}


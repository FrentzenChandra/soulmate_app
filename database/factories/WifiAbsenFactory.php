<?php

namespace Database\Factories;

use App\Models\Cabang;
use App\Models\User;
use App\Models\WifiAbsen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WifiAbsen>
 */
class WifiAbsenFactory extends Factory
{
    protected $model = WifiAbsen::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cabang_id' => Cabang::factory(),
            'nama' => 'WiFi ' . fake()->company(),
            'ip_address' => fake()->unique()->ipv4(),
            'is_aktif' => true,
            'created_by' => User::factory(),
        ];
    }
}


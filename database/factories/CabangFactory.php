<?php

namespace Database\Factories;

use App\Models\Cabang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cabang>
 */
class CabangFactory extends Factory
{
    protected $model = Cabang::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_cabang' => 'CB-' . strtoupper(fake()->unique()->lexify('???')),
            'nama_cabang' => 'Cabang ' . fake()->city(),
            'alamat' => fake()->address(),
            'no_telp' => fake()->phoneNumber(),
            'no_wa' => fake()->phoneNumber(),
            'status' => 'aktif',
        ];
    }
}


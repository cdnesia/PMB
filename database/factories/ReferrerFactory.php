<?php

namespace Database\Factories;

use App\Models\Referrer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referrer>
 */
class ReferrerFactory extends Factory
{
    protected $model = Referrer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kode' => strtoupper('REF-'.fake()->unique()->bothify('??##??')),
            'jenis' => 'mitra',
            'nama_instansi' => fake()->company(),
            'is_active' => true,
        ];
    }

    public function karyawan(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis' => 'karyawan',
            'nama_instansi' => null,
        ]);
    }

    public function mitra(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis' => 'mitra',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

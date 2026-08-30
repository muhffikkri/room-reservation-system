<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Ruang '.fake()->unique()->word(),
            'type' => fake()->randomElement(['ruang_kelas', 'aula', 'laboratorium', 'alat', 'lapangan']),
            'location' => 'Gedung '.fake()->randomLetter(),
            'capacity' => fake()->numberBetween(10, 300),
            'description' => fake()->sentence(),
            'status' => 'aktif',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(['kerusakan_alat', 'listrik', 'kebersihan', 'sarana_prasarana', 'lainnya']),
            'description' => fake()->paragraph(),
            'status' => 'baru',
        ];
    }
}

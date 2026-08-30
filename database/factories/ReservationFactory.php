<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+14 days');

        return [
            'purpose' => fake()->sentence(),
            'start_time' => $start->format('Y-m-d H:00:00'),
            'end_time' => $start->modify('+2 hours')->format('Y-m-d H:00:00'),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'approved']);
    }
}

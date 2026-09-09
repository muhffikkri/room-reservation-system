<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        // Slot operasional: 26 slot/hari 07.00–20.00 (BR-1), durasi 1–8 slot (BR-2).
        $day = fake()->dateTimeBetween('+1 day', '+14 days')->format('Y-m-d');
        $startSlot = fake()->numberBetween(0, 25);
        $durationSlots = fake()->numberBetween(1, min(8, 26 - $startSlot));

        $start = Carbon::parse("{$day} 07:00")->addMinutes($startSlot * 30);
        $end = $start->copy()->addMinutes($durationSlots * 30);

        return [
            'purpose' => fake()->sentence(8),
            'start_time' => $start->format('Y-m-d H:i:00'),
            'end_time' => $end->format('Y-m-d H:i:00'),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'approved']);
    }
}

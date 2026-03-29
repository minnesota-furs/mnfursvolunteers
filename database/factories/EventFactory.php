<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 years', '-1 month');
        $end = (clone $start)->modify('+' . $this->faker->numberBetween(1, 3) . ' days');

        return [
            'name'              => $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['Con', 'Fest', 'Meet', 'Expo', 'Gathering']),
            'description'       => $this->faker->paragraph(),
            'start_date'        => $start,
            'end_date'          => $end,
            'location'          => $this->faker->address(),
            'visibility'        => 'public',
            'auto_credit_hours' => $this->faker->boolean(30),
            'hide_past_shifts'  => false,
            'require_eligibility' => false,
        ];
    }

    public function past(): static
    {
        return $this->state(function () {
            $start = $this->faker->dateTimeBetween('-2 years', '-2 months');
            $end = (clone $start)->modify('+' . $this->faker->numberBetween(1, 3) . ' days');

            return [
                'start_date' => $start,
                'end_date'   => $end,
            ];
        });
    }

    public function upcoming(): static
    {
        return $this->state(function () {
            $start = $this->faker->dateTimeBetween('+1 week', '+6 months');
            $end = (clone $start)->modify('+' . $this->faker->numberBetween(1, 3) . ' days');

            return [
                'start_date' => $start,
                'end_date'   => $end,
            ];
        });
    }
}

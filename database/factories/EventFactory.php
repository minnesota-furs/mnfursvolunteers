<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s') . ' +14 days');
        
        return [
            'name' => fake()->words(3, true) . ' Volunteer Event',
            'description' => fake()->paragraphs(2, true),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'auto_credit_hours' => fake()->boolean(80), // 80% chance to auto-credit hours
            'location' => fake()->randomElement([
                'Main Convention Center',
                'Community Hall',
                'Conference Room A',
                'Outdoor Park Area',
                'Hotel Ballroom',
            ]),
            'created_by' => User::factory(),
            'visibility' => fake()->randomElement(['public', 'unlisted', 'internal', 'draft']),
            'hide_past_shifts' => fake()->boolean(30), // 30% chance to hide past shifts
            'signup_open_date' => fake()->optional(0.7)->dateTimeBetween('-1 month', $startDate),
        ];
    }

    /**
     * Indicate that the event is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'public',
        ]);
    }

    /**
     * Indicate that the event is unlisted.
     */
    public function unlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'unlisted',
        ]);
    }

    /**
     * Indicate that the event is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'draft',
        ]);
    }

    /**
     * Indicate that the event is upcoming.
     */
    public function upcoming(): static
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s') . ' +7 days');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'signup_open_date' => now(),
        ]);
    }

    /**
     * Indicate that the event is a single day event.
     */
    public function singleDay(): static
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $startTime = $startDate->format('Y-m-d') . ' 09:00:00';
        $endTime = $startDate->format('Y-m-d') . ' 17:00:00';
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startTime,
            'end_date' => $endTime,
        ]);
    }

    /**
     * Indicate that the event is multi-day.
     */
    public function multiDay(): static
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate->format('Y-m-d H:i:s') . ' +2 days', $startDate->format('Y-m-d H:i:s') . ' +7 days');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}

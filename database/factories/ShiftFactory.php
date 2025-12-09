<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('now', '+3 months');
        $duration = fake()->randomElement([2, 3, 4, 6, 8]); // Common shift durations in hours
        $endTime = (clone $startTime)->modify("+{$duration} hours");
        
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement([
                'Registration Desk',
                'Information Booth',
                'Setup Crew',
                'Teardown Crew',
                'Event Support',
                'Merchandise Table',
                'Room Monitor',
                'Check-in Staff',
                'Gopher',
                'Parking Attendant',
            ]),
            'description' => fake()->optional(0.7)->paragraph(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'double_hours' => fake()->boolean(10), // 10% chance for double hours
            'max_volunteers' => fake()->randomElement([2, 3, 4, 5, 6, 8, 10]),
            'original_shift_id' => null,
            'duplicate_series_id' => null,
            'duplicate_sequence' => null,
        ];
    }

    /**
     * Indicate that the shift is for a specific event.
     */
    public function forEvent(Event $event): static
    {
        // Generate shift time within the event's date range
        $eventStart = $event->start_date;
        $eventEnd = $event->end_date;
        
        return $this->state(function (array $attributes) use ($event, $eventStart, $eventEnd) {
            $startTime = fake()->dateTimeBetween($eventStart, $eventEnd);
            $duration = fake()->randomElement([2, 3, 4, 6, 8]);
            $endTime = (clone $startTime)->modify("+{$duration} hours");
            
            // Make sure end time doesn't exceed event end time
            if ($endTime > $eventEnd) {
                $endTime = clone $eventEnd;
            }
            
            return [
                'event_id' => $event->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the shift offers double hours.
     */
    public function doubleHours(): static
    {
        return $this->state(fn (array $attributes) => [
            'double_hours' => true,
        ]);
    }

    /**
     * Indicate that the shift is a short shift (2 hours).
     */
    public function shortShift(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $attributes['start_time'] ?? fake()->dateTimeBetween('now', '+3 months');
            $endTime = (clone $startTime)->modify('+2 hours');
            
            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the shift is a long shift (8 hours).
     */
    public function longShift(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $attributes['start_time'] ?? fake()->dateTimeBetween('now', '+3 months');
            $endTime = (clone $startTime)->modify('+8 hours');
            
            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the shift has limited capacity.
     */
    public function limitedCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_volunteers' => fake()->randomElement([1, 2, 3]),
        ]);
    }

    /**
     * Indicate that the shift has high capacity.
     */
    public function highCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_volunteers' => fake()->randomElement([10, 15, 20, 25]),
        ]);
    }
}

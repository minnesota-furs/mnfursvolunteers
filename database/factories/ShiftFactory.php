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
    protected $model = Shift::class;

    public function definition(): array
    {
        $startHour = $this->faker->numberBetween(8, 18);
        $duration = $this->faker->numberBetween(2, 6);

        return [
            'event_id'       => Event::factory(),
            'name'           => $this->faker->randomElement([
                'Registration', 'Setup Crew', 'Teardown Crew', 'Info Booth',
                'Stage Monitor', 'Security Patrol', 'Hospitality', 'Merchandise Table',
                'Green Room Support', 'Panel Room Monitor', 'Art Show Watch',
                'Con Store', 'Runner', 'Floater',
            ]),
            'description'    => $this->faker->sentence(),
            'start_time'     => now()->setTime($startHour, 0),
            'end_time'       => now()->setTime($startHour + $duration, 0),
            'max_volunteers' => $this->faker->numberBetween(2, 10),
            'double_hours'   => $this->faker->boolean(15),
        ];
    }

    public function forEvent(Event $event, \DateTimeInterface $day): static
    {
        return $this->state(function () use ($event, $day) {
            $startHour = $this->faker->numberBetween(8, 20);
            $duration = $this->faker->numberBetween(2, 6);

            $start = (clone $day)->setTime($startHour, 0);
            $end = (clone $start)->modify("+{$duration} hours");

            return [
                'event_id'   => $event->id,
                'start_time' => $start,
                'end_time'   => $end,
            ];
        });
    }
}

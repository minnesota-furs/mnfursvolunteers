<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventCategory>
 */
class EventCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->unique()->randomElement(['Badge Checker', 'Setup', 'Teardown', 'Registration', 'Greeter']),
            'color' => $this->faker->hexColor(),
            'description' => null,
            'sort_order' => 0,
        ];
    }
}

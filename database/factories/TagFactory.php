<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'color' => fake()->hexColor(),
            'description' => fake()->sentence(),
        ];
    }

    public function forUsers(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'user',
        ]);
    }

    public function forShifts(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shift',
        ]);
    }
}

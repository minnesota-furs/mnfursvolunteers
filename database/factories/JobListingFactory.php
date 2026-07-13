<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobListing>
 */
class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'position_title' => fake()->randomElement([
                'Volunteer Coordinator', 'Registration Assistant', 'Gaming Room Lead',
                'Consuite Attendant', 'Art Show Assistant', 'Security Volunteer',
                'Tech Crew Member', 'Panels Moderator', 'Guest Relations Liaison',
                'Logistics Runner',
            ]),
            'visibility' => fake()->randomElement(['draft', 'public', 'internal']),
            'description' => fake()->paragraphs(3, true),
            'number_of_openings' => fake()->numberBetween(1, 10),
            'closing_date' => fake()->optional(0.6)->dateTimeBetween('now', '+3 months'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['visibility' => 'draft']);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => ['visibility' => 'public']);
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => ['visibility' => 'internal']);
    }
}

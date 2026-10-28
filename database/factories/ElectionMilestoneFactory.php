<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\ElectionMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectionMilestone>
 */
class ElectionMilestoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'title' => fake()->randomElement(['Candidate Bios Due', 'Q&A Debate', 'Meet the Candidates']),
            'description' => fake()->sentence(),
            'location' => null,
            'starts_at' => now()->addDays(fake()->numberBetween(1, 14)),
        ];
    }

    public function withLocation(string $location = 'Main Hall'): static
    {
        return $this->state(fn (array $attributes) => ['location' => $location]);
    }
}

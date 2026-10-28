<?php

namespace Database\Factories;

use App\Models\Election;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Election>
 */
class ElectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true).' Election',
            'description' => fake()->sentence(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
            'active' => true,
            'max_positions' => 1,
            'allow_self_nomination' => false,
            'requires_approval' => false,
            'min_candidate_hours' => 0,
            'min_voter_hours' => 0,
        ];
    }
}

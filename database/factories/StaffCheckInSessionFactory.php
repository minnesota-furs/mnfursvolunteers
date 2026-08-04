<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\StaffCheckInSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffCheckInSession>
 */
class StaffCheckInSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'scope' => 'department',
            'department_id' => Department::factory(),
            'checklist_items' => ['Badge given'],
            'custom_field_ids' => [],
            'collect_signature' => false,
            'created_by' => User::factory(),
        ];
    }
}

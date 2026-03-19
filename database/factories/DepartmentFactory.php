<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Administration', 'Art Show', 'Communications', 'Consuite',
                'Dealers Room', 'Events', 'Finance', 'Gaming', 'Guest Relations',
                'Hospitality', 'Logistics', 'Main Stage', 'Marketing',
                'Operations', 'Programming', 'Registration', 'Security',
                'Tech', 'Volunteers', 'Web & Technology',
            ]),
            'sector_id' => Sector::factory(),
        ];
    }
}

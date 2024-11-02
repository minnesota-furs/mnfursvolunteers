<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\FakerProviders\DepartmentProvider;

use App\Models\Sector;
use App\Models\Department;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function __construct()
    {
        parent::__construct();
        $this->faker->addProvider(new DepartmentProvider($this->faker));
    }

    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->departmentName(),
            'sector_id' => Sector::inRandomOrder()->first()->id, // Associate with a Sector
        ];
    }
}

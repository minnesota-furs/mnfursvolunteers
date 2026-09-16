<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\User;
use App\Models\VolunteerHours;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VolunteerHours>
 */
class VolunteerHoursFactory extends Factory
{
    protected $model = VolunteerHours::class;

    public function definition(): array
    {
        $volunteerDate = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'user_id' => User::factory(),
            'volunteer_date' => $volunteerDate,
            'primary_dept_id' => Department::factory(),
            'hours' => fake()->randomFloat(2, 0.5, 8),
            'description' => fake()->randomElement([
                'Registration desk coverage', 'Setup and teardown', 'Art show assistance',
                'Dealers room support', 'Con suite stocking', 'Panel moderation',
                'Security patrol', 'Tech support for main stage', 'Guest relations',
                'Gaming room supervision', 'Volunteer check-in staffing', 'Load-out crew',
            ]),
            'notes' => fake()->boolean(40) ? fake()->sentence() : null,
            'counts_toward_perks' => fake()->boolean(80),
            'perk_set_id' => null,
            'fiscal_ledger_id' => optional(
                FiscalLedger::where('start_date', '<=', $volunteerDate)
                    ->where('end_date', '>=', $volunteerDate)
                    ->first()
            )->id,
        ];
    }
}

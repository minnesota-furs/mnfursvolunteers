<?php

namespace Database\Factories;

use App\Models\StaffCheckIn;
use App\Models\StaffCheckInSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffCheckIn>
 */
class StaffCheckInFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_check_in_session_id' => StaffCheckInSession::factory(),
            'user_id' => User::factory(),
            'completed_items' => ['Badge given'],
            'checked_in_by' => User::factory(),
            'checked_in_at' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->userName(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'onboarded_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'admin' => true,
        ]);
    }

    /**
     * Attach one or more random departments to the user after creation.
     * Optionally pass $count to attach multiple departments.
     */
    public function withDepartments(int $count = 1): static
    {
        return $this->afterCreating(function ($user) use ($count) {
            $departments = Department::inRandomOrder()->limit($count)->pluck('id');
            $user->departments()->attach($departments);

            $primary = $departments->first();
            if ($primary) {
                $dept = Department::find($primary);
                $user->update([
                    'primary_dept_id' => $dept->id,
                    'primary_sector_id' => $dept->sector_id,
                ]);
            }
        });
    }
}

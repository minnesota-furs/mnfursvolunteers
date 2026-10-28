<?php

use App\Models\ApplicationSetting;
use App\Models\Department;
use App\Models\User;

function setRequireDepartmentForSelfReport(bool $value): void
{
    ApplicationSetting::set('require_department_for_self_report', $value, 'boolean', 'Require department', 'volunteers');
}

it('shows the explanation page to users without a department when required', function () {
    setRequireDepartmentForSelfReport(true);
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('hours.create', $user->id))
        ->assertOk()
        ->assertSee('have a department assigned', false)
        ->assertSee('How many hours');
});

it('shows the log hours button on the dashboard to users without a department when required', function () {
    setRequireDepartmentForSelfReport(true);
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)->get(route('dashboard'))->assertSee('Log New Hours');
});

it('shows the form to users with a department when required', function () {
    setRequireDepartmentForSelfReport(true);
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach(Department::factory()->create());

    $this->actingAs($user)
        ->get(route('hours.create', $user->id))
        ->assertOk()
        ->assertDontSee('How many hours you volunteered');
});

it('shows the form to users without a department when not required', function () {
    setRequireDepartmentForSelfReport(false);
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('hours.create', $user->id))
        ->assertOk()
        ->assertDontSee('How many hours you volunteered');
});

it('blocks storing self-reported hours without a department when required', function () {
    setRequireDepartmentForSelfReport(true);
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->post(route('hours.store'), ['user_id' => $user->id, 'hours' => 2])
        ->assertRedirect(route('hours.create', $user->id));

    expect($user->volunteerHours()->count())->toBe(0);
});

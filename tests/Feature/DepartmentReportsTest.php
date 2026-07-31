<?php

use App\Models\Department;
use App\Models\Sector;
use App\Models\User;

beforeEach(function () {
    $this->reporter = User::factory()->create([
        'active' => true,
        'admin' => false,
        'onboarded_at' => now(),
        'permissions' => ['View Reports'],
    ]);
});

it('lists active volunteers belonging to multiple departments', function () {
    $firstDepartment = Department::factory()->create(['name' => 'Operations']);
    $secondDepartment = Department::factory()->create(['name' => 'Registration']);
    $multipleDepartmentVolunteer = User::factory()->create(['name' => 'Many Departments', 'active' => true]);
    $singleDepartmentVolunteer = User::factory()->create(['name' => 'One Department', 'active' => true]);
    $inactiveVolunteer = User::factory()->create(['name' => 'Inactive Multiple', 'active' => false]);

    $multipleDepartmentVolunteer->departments()->attach([$firstDepartment->id, $secondDepartment->id]);
    $singleDepartmentVolunteer->departments()->attach($firstDepartment);
    $inactiveVolunteer->departments()->attach([$firstDepartment->id, $secondDepartment->id]);

    $this->actingAs($this->reporter)
        ->get(route('report.volunteersWithMultipleDepartments'))
        ->assertOk()
        ->assertSee('Many Departments')
        ->assertSee('Operations')
        ->assertSee('Registration')
        ->assertDontSee('One Department')
        ->assertDontSee('Inactive Multiple');
});

it('reports active department membership totals', function () {
    $department = Department::factory()->create(['name' => 'Volunteers']);
    $activeVolunteer = User::factory()->create(['active' => true]);
    $inactiveVolunteer = User::factory()->create(['active' => false]);
    $department->users()->attach([$activeVolunteer->id, $inactiveVolunteer->id]);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.departmentMembership'));

    $response->assertOk();

    $reportedDepartment = $response->viewData('departments')->firstWhere('id', $department->id);

    expect($reportedDepartment->active_users_count)->toBe(1)
        ->and($response->viewData('activeVolunteerCount'))->toBe(1)
        ->and($response->viewData('activeMembershipCount'))->toBe(1);
});

it('groups current memberships by the month they were added', function () {
    $department = Department::factory()->create();
    $volunteer = User::factory()->create(['active' => true]);
    $addedAt = now()->startOfMonth()->subMonth();

    $department->users()->attach($volunteer, [
        'created_at' => $addedAt,
        'updated_at' => $addedAt,
    ]);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.departmentMembership', ['months' => 6]));

    $reportedDepartment = $response->viewData('departments')->firstWhere('id', $department->id);

    $response->assertOk();
    expect($reportedDepartment->monthly_memberships->get($addedAt->format('Y-m')))->toBe(1);
});

it('filters membership totals and trends by sector', function () {
    $selectedSector = Sector::factory()->create(['name' => 'Selected Sector']);
    $otherSector = Sector::factory()->create(['name' => 'Other Sector']);
    $firstSelectedDepartment = Department::factory()->for($selectedSector)->create(['name' => 'Selected One']);
    $secondSelectedDepartment = Department::factory()->for($selectedSector)->create(['name' => 'Selected Two']);
    $otherDepartment = Department::factory()->for($otherSector)->create(['name' => 'Not Selected']);
    $multipleDepartmentVolunteer = User::factory()->create(['active' => true]);
    $otherVolunteer = User::factory()->create(['active' => true]);

    $multipleDepartmentVolunteer->departments()->attach([
        $firstSelectedDepartment->id,
        $secondSelectedDepartment->id,
    ]);
    $otherVolunteer->departments()->attach($otherDepartment);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.departmentMembership', ['sector_id' => $selectedSector->id]));

    $response->assertOk()
        ->assertSee('Selected One')
        ->assertSee('Selected Two')
        ->assertDontSee('Not Selected');

    expect($response->viewData('activeVolunteerCount'))->toBe(1)
        ->and($response->viewData('activeMembershipCount'))->toBe(2)
        ->and($response->viewData('multipleDepartmentCount'))->toBe(1);
});

it('requires report permission for department reports', function (string $routeName) {
    $user = User::factory()->create([
        'admin' => false,
        'onboarded_at' => now(),
        'permissions' => [],
    ]);

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertForbidden();
})->with([
    'multiple departments' => 'report.volunteersWithMultipleDepartments',
    'membership totals and trends' => 'report.departmentMembership',
]);

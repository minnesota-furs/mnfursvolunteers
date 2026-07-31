<?php

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Event;
use App\Models\FiscalLedger;
use App\Models\Shift;
use App\Models\User;
use App\Models\VolunteerHours;

beforeEach(function () {
    $this->department = Department::factory()->create(['name' => 'Operations']);
    $this->head = User::factory()->create([
        'name' => 'Department Head',
        'admin' => false,
        'onboarded_at' => now(),
    ]);
    $this->department->heads()->attach($this->head);
});

it('allows a department head to view only their department workspace', function () {
    $otherDepartment = Department::factory()->create(['name' => 'Registration']);
    $member = User::factory()->create([
        'name' => 'Operations Volunteer',
        'email' => 'operations@example.com',
        'active' => true,
    ]);
    $outsider = User::factory()->create([
        'name' => 'Registration Volunteer',
        'email' => 'registration@example.com',
        'active' => true,
    ]);
    $this->department->users()->attach($member);
    $otherDepartment->users()->attach($outsider);

    $this->actingAs($this->head)
        ->get(route('departments.manage', $this->department))
        ->assertOk()
        ->assertSee('Operations Volunteer')
        ->assertSee('operations@example.com')
        ->assertDontSee('Registration Volunteer')
        ->assertDontSee('registration@example.com');

    $this->actingAs($this->head)
        ->get(route('departments.manage', $otherDepartment))
        ->assertForbidden();
});

it('allows administrators to manage any department', function () {
    $admin = User::factory()->admin()->create(['onboarded_at' => now()]);

    $this->actingAs($admin)
        ->get(route('departments.manage', $this->department))
        ->assertOk();
});

it('lets heads see member emails on profiles but not unrelated user emails', function () {
    FiscalLedger::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);
    $member = User::factory()->create(['email' => 'member@example.com']);
    $outsider = User::factory()->create(['email' => 'outsider@example.com']);
    $this->department->users()->attach($member);

    $this->actingAs($this->head)
        ->get(route('users.show', $member))
        ->assertOk()
        ->assertSee('member@example.com');

    $this->actingAs($this->head)
        ->get(route('users.show', $outsider))
        ->assertOk()
        ->assertDontSee('outsider@example.com');
});

it('shows department activity and upcoming assignments', function () {
    $ledger = FiscalLedger::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);
    $member = User::factory()->create([
        'name' => 'Active Volunteer',
        'active' => true,
    ]);
    $this->department->users()->attach($member);

    VolunteerHours::query()->create([
        'user_id' => $member->id,
        'volunteer_date' => now()->subDay(),
        'primary_dept_id' => $this->department->id,
        'hours' => 4.5,
        'description' => 'Department work',
        'fiscal_ledger_id' => $ledger->id,
    ]);

    $event = Event::factory()->create([
        'name' => 'Future Convention',
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek()->addDay(),
    ]);
    $shift = Shift::factory()->for($event)->create([
        'name' => 'Setup Crew',
        'start_time' => now()->addWeek(),
        'end_time' => now()->addWeek()->addHours(2),
    ]);
    $shift->users()->attach($member);

    $this->actingAs($this->head)
        ->get(route('departments.manage', $this->department))
        ->assertOk()
        ->assertSee('4.5')
        ->assertSee('Future Convention')
        ->assertSee('Setup Crew')
        ->assertSee('Active Volunteer');
});

it('exports a filtered department staff list without private fields and records an audit', function () {
    $includedMember = User::factory()->create([
        'name' => 'Included Volunteer',
        'email' => 'included@example.com',
        'active' => true,
        'notes' => 'Private personnel note',
        'accessibility_needs' => ['Limited standing/walking'],
    ]);
    $excludedMember = User::factory()->create([
        'name' => 'Excluded Volunteer',
        'email' => 'excluded@example.com',
        'active' => false,
    ]);
    $this->department->users()->attach([$includedMember->id, $excludedMember->id]);

    $response = $this->actingAs($this->head)
        ->get(route('departments.staff-export', [
            'department' => $this->department,
            'status' => 'active',
        ]));

    $response->assertOk()
        ->assertDownload('operations-staff.csv');

    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('Included Volunteer')
        ->toContain('included@example.com')
        ->not->toContain('Excluded Volunteer')
        ->not->toContain('Private personnel note')
        ->not->toContain('Limited standing/walking');

    expect(AuditLog::query()
        ->where('action', 'exported')
        ->where('auditable_type', Department::class)
        ->where('auditable_id', $this->department->id)
        ->where('user_id', $this->head->id)
        ->exists())->toBeTrue();
});

it('prevents non-heads from exporting a department staff list', function () {
    $user = User::factory()->create([
        'admin' => false,
        'onboarded_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('departments.staff-export', $this->department))
        ->assertForbidden();
});

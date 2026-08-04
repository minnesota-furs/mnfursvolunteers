<?php

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;

beforeEach(function () {
    $this->reporter = User::factory()->create([
        'admin' => false,
        'permissions' => ['View Reports'],
    ]);
});

it('requires report permission', function () {
    $this->actingAs(User::factory()->create(['admin' => false, 'permissions' => []]))
        ->get(route('report.staffCheckIn'))
        ->assertForbidden();
});

it('builds a sector check-in sheet with custom fields and checklist items', function () {
    $selectedSector = Sector::factory()->create(['name' => 'Convention Operations']);
    $otherSector = Sector::factory()->create();
    $selectedDepartment = Department::factory()->for($selectedSector)->create(['name' => 'Registration']);
    $otherDepartment = Department::factory()->for($otherSector)->create();
    $shirtSize = CustomField::query()->create([
        'name' => 'T-shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['Small', 'Large'],
        'is_active' => true,
    ]);
    $selectedStaff = User::factory()->create(['name' => 'Selected Staff', 'active' => true]);
    $otherStaff = User::factory()->create(['name' => 'Other Staff', 'active' => true]);
    $inactiveStaff = User::factory()->create(['name' => 'Inactive Staff', 'active' => false]);
    $selectedStaff->departments()->attach($selectedDepartment);
    $otherStaff->departments()->attach($otherDepartment);
    $inactiveStaff->departments()->attach($selectedDepartment);
    CustomFieldValue::query()->create([
        'user_id' => $selectedStaff->id,
        'custom_field_id' => $shirtSize->id,
        'value' => 'Large',
    ]);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn', [
            'scope' => 'sector',
            'sector_id' => $selectedSector->id,
            'custom_fields' => [$shirtSize->id],
            'checklist_items' => ['Badge', 'T-shirt collected'],
        ]))
        ->assertOk()
        ->assertSee('Convention Operations')
        ->assertSee('Selected Staff')
        ->assertSee('Registration')
        ->assertSee('T-shirt Size')
        ->assertSee('Large')
        ->assertSee('Badge')
        ->assertSee('T-shirt collected')
        ->assertDontSee('Other Staff')
        ->assertDontSee('Inactive Staff');
});

it('limits a check-in sheet to a selected department', function () {
    $sector = Sector::factory()->create(['name' => 'Events']);
    $selectedDepartment = Department::factory()->for($sector)->create(['name' => 'Hospitality']);
    $otherDepartment = Department::factory()->for($sector)->create(['name' => 'Operations']);
    $selectedStaff = User::factory()->create(['name' => 'Hospitality Staff', 'active' => true]);
    $otherStaff = User::factory()->create(['name' => 'Operations Staff', 'active' => true]);
    $selectedStaff->departments()->attach($selectedDepartment);
    $otherStaff->departments()->attach($otherDepartment);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn', [
            'scope' => 'department',
            'department_id' => $selectedDepartment->id,
        ]));

    $response->assertOk()
        ->assertSee('Events: Hospitality')
        ->assertSee('Hospitality Staff')
        ->assertDontSee('Operations Staff');

    expect($response->viewData('staff'))->toHaveCount(1);
});

it('validates the selected group and report columns', function () {
    $response = $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn', [
            'scope' => 'sector',
            'custom_fields' => [999999],
            'checklist_items' => array_fill(0, 13, 'Item'),
        ]));

    $response->assertSessionHasErrors(['sector_id', 'custom_fields.0', 'checklist_items']);
});

it('adds signatures and can display legal names grouped by legal last name', function () {
    $department = Department::factory()->create();
    $zebra = User::factory()->create([
        'name' => 'First Alias',
        'first_name' => 'Amy',
        'last_name' => 'Zebra',
        'active' => true,
    ]);
    $adams = User::factory()->create([
        'name' => 'Second Alias',
        'first_name' => 'Zoe',
        'last_name' => 'Adams',
        'active' => true,
    ]);
    $department->users()->attach([$zebra->id, $adams->id]);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn', [
            'scope' => 'department',
            'department_id' => $department->id,
            'include_signature' => true,
            'group_alphabetically' => true,
            'alphabetical_by' => 'last_name',
            'list_legal_name' => true,
        ]));

    $response->assertOk()
        ->assertSee('Signature')
        ->assertSee('Amy Zebra')
        ->assertSee('Zoe Adams')
        ->assertDontSee('First Alias')
        ->assertDontSee('Second Alias');

    expect($response->viewData('staff')->pluck('last_name')->all())->toBe(['Adams', 'Zebra'])
        ->and($response->viewData('staffGroups')->keys()->all())->toBe(['A', 'Z']);
});

it('validates the alphabetical grouping option', function () {
    $department = Department::factory()->create();

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn', [
            'scope' => 'department',
            'department_id' => $department->id,
            'group_alphabetically' => true,
            'alphabetical_by' => 'email',
        ]))
        ->assertSessionHasErrors('alphabetical_by');
});

it('links the generated report to a dedicated print layout', function () {
    $department = Department::factory()->create();
    $staffMember = User::factory()->create(['name' => 'Printable Staff', 'active' => true]);
    $department->users()->attach($staffMember);
    $query = [
        'scope' => 'department',
        'department_id' => $department->id,
        'include_signature' => true,
        'checklist_items' => ['Badge'],
    ];

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn', $query))
        ->assertOk()
        ->assertSee(route('report.staffCheckIn.print'), false);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn.print', $query))
        ->assertOk()
        ->assertViewIs('reports.staff-check-in-print')
        ->assertSee('Printable Staff')
        ->assertSee('Signature')
        ->assertSee('Badge');
});

it('requires report permission for the print layout', function () {
    $this->actingAs(User::factory()->create(['admin' => false, 'permissions' => []]))
        ->get(route('report.staffCheckIn.print'))
        ->assertForbidden();
});

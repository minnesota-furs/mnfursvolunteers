<?php

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\Sector;
use App\Models\StaffCheckIn;
use App\Models\StaffCheckInSession;
use App\Models\User;

beforeEach(function () {
    $this->reporter = User::factory()->create([
        'admin' => false,
        'permissions' => ['View Reports'],
    ]);
});

it('offers paper and digital check-in experiences', function () {
    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn'))
        ->assertOk()
        ->assertSee('Paper Printout experience')
        ->assertSee('Digital experience')
        ->assertSee(route('report.staffCheckIn.paper'), false)
        ->assertSee(route('report.staffCheckIn.digital.index'), false);
});

it('creates a digital session for a sector', function () {
    $sector = Sector::factory()->create();

    $response = $this->actingAs($this->reporter)
        ->post(route('report.staffCheckIn.digital.store'), [
            'name' => 'Friday Registration',
            'scope' => 'sector',
            'sector_id' => $sector->id,
            'checklist_items' => ['Staff gift given', 'Staff badge given'],
            'collect_signature' => true,
        ]);

    $session = StaffCheckInSession::query()->sole();

    $response->assertRedirect(route('report.staffCheckIn.digital.show', $session));
    expect($session->name)->toBe('Friday Registration')
        ->and($session->sector_id)->toBe($sector->id)
        ->and($session->department_id)->toBeNull()
        ->and($session->checklist_items)->toBe(['Staff gift given', 'Staff badge given'])
        ->and($session->collect_signature)->toBeTrue()
        ->and($session->created_by)->toBe($this->reporter->id);
});

it('lists only active staff eligible for the session', function () {
    $selectedDepartment = Department::factory()->create();
    $otherDepartment = Department::factory()->create();
    $eligible = User::factory()->create(['name' => 'Eligible Staff', 'active' => true]);
    $other = User::factory()->create(['name' => 'Other Staff', 'active' => true]);
    $inactive = User::factory()->create(['name' => 'Inactive Staff', 'active' => false]);
    $selectedDepartment->users()->attach([$eligible->id, $inactive->id]);
    $otherDepartment->users()->attach($other);
    $session = StaffCheckInSession::factory()->create([
        'department_id' => $selectedDepartment->id,
        'created_by' => $this->reporter->id,
    ]);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn.digital.show', $session))
        ->assertOk()
        ->assertSee('Eligible Staff')
        ->assertDontSee('Other Staff')
        ->assertDontSee('Inactive Staff');
});

it('completes a staff check-in with tracked items and a signature', function () {
    $department = Department::factory()->create();
    $staffMember = User::factory()->create(['name' => 'Signing Staff', 'active' => true]);
    $department->users()->attach($staffMember);
    $session = StaffCheckInSession::factory()->create([
        'department_id' => $department->id,
        'checklist_items' => ['Staff gift given', 'Staff badge given'],
        'collect_signature' => true,
        'created_by' => $this->reporter->id,
    ]);
    $signature = 'data:image/png;base64,'.base64_encode('signature');

    $response = $this->actingAs($this->reporter)
        ->put(route('report.staffCheckIn.digital.complete', [$session, $staffMember]), [
            'completed_items' => ['Staff gift given'],
            'signature_data' => $signature,
        ]);

    $response->assertRedirect(route('report.staffCheckIn.digital.show', $session));
    $checkIn = StaffCheckIn::query()->sole();
    expect($checkIn->completed_items)->toBe(['Staff gift given'])
        ->and($checkIn->signature_data)->toBe($signature)
        ->and($checkIn->checked_in_by)->toBe($this->reporter->id)
        ->and($checkIn->user_id)->toBe($staffMember->id);
});

it('requires a signature when configured for the session', function () {
    $department = Department::factory()->create();
    $staffMember = User::factory()->create(['active' => true]);
    $department->users()->attach($staffMember);
    $session = StaffCheckInSession::factory()->create([
        'department_id' => $department->id,
        'collect_signature' => true,
        'created_by' => $this->reporter->id,
    ]);

    $this->actingAs($this->reporter)
        ->put(route('report.staffCheckIn.digital.complete', [$session, $staffMember]), [])
        ->assertSessionHasErrors('signature_data');

    expect(StaffCheckIn::query()->count())->toBe(0);
});

it('prevents checking in staff outside the configured group', function () {
    $session = StaffCheckInSession::factory()->create(['created_by' => $this->reporter->id]);
    $outsider = User::factory()->create(['active' => true]);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn.digital.staff', [$session, $outsider]))
        ->assertNotFound();
});

it('requires report permission for digital sessions', function () {
    $this->actingAs(User::factory()->create(['admin' => false, 'permissions' => []]))
        ->get(route('report.staffCheckIn.digital.index'))
        ->assertForbidden();
});

it('stores selected custom fields and shows their values during check-in', function () {
    $department = Department::factory()->create();
    $staffMember = User::factory()->create(['active' => true]);
    $department->users()->attach($staffMember);
    $shirtSize = CustomField::query()->create([
        'name' => 'T-shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['Small', 'Large'],
        'is_active' => true,
    ]);
    CustomFieldValue::query()->create([
        'user_id' => $staffMember->id,
        'custom_field_id' => $shirtSize->id,
        'value' => 'Large',
    ]);

    $this->actingAs($this->reporter)->post(route('report.staffCheckIn.digital.store'), [
        'name' => 'Custom Field Session',
        'scope' => 'department',
        'department_id' => $department->id,
        'custom_fields' => [$shirtSize->id],
    ]);

    $session = StaffCheckInSession::query()->sole();
    expect($session->custom_field_ids)->toBe([$shirtSize->id]);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn.digital.staff', [$session, $staffMember]))
        ->assertOk()
        ->assertSee('T-shirt Size')
        ->assertSee('Large');
});

it('shows follow-up details for a partially completed check-in', function () {
    $department = Department::factory()->create();
    $staffMember = User::factory()->create(['name' => 'Partial Staff', 'active' => true]);
    $department->users()->attach($staffMember);
    $session = StaffCheckInSession::factory()->create([
        'department_id' => $department->id,
        'checklist_items' => ['Staff badge given', 'Staff gift given'],
        'created_by' => $this->reporter->id,
    ]);
    StaffCheckIn::factory()->create([
        'staff_check_in_session_id' => $session->id,
        'user_id' => $staffMember->id,
        'completed_items' => ['Staff badge given'],
        'checked_in_by' => $this->reporter->id,
    ]);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn.digital.show', $session))
        ->assertOk()
        ->assertSee('Partial Staff')
        ->assertSee('Needs follow-up')
        ->assertSee('Missing: Staff gift given')
        ->assertDontSee('Missing: Staff badge given');
});

it('provides check-in status filters and searchable legal names', function () {
    $department = Department::factory()->create();
    $staffMember = User::factory()->create([
        'name' => 'BadgeHelper',
        'first_name' => 'Alexandra',
        'last_name' => 'Northwood',
        'active' => true,
    ]);
    $department->users()->attach($staffMember);
    $session = StaffCheckInSession::factory()->create([
        'department_id' => $department->id,
        'created_by' => $this->reporter->id,
    ]);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn.digital.show', $session))
        ->assertOk()
        ->assertSee('Hide fully checked in')
        ->assertSee('Follow-up needed only')
        ->assertSee('badgehelper alexandra northwood', false);
});

it('edits an existing check-in session from the page header', function () {
    $originalDepartment = Department::factory()->create();
    $newDepartment = Department::factory()->create();
    $session = StaffCheckInSession::factory()->create([
        'name' => 'Original Session',
        'department_id' => $originalDepartment->id,
        'created_by' => $this->reporter->id,
    ]);

    $this->actingAs($this->reporter)
        ->get(route('report.staffCheckIn.digital.show', $session))
        ->assertOk()
        ->assertSee(route('report.staffCheckIn.digital.edit', $session), false);

    $this->actingAs($this->reporter)
        ->put(route('report.staffCheckIn.digital.update', $session), [
            'name' => 'Updated Session',
            'scope' => 'department',
            'department_id' => $newDepartment->id,
            'checklist_items' => ['Gift given', 'Badge given'],
            'collect_signature' => true,
        ])
        ->assertRedirect(route('report.staffCheckIn.digital.show', $session));

    expect($session->refresh()->name)->toBe('Updated Session')
        ->and($session->department_id)->toBe($newDepartment->id)
        ->and($session->checklist_items)->toBe(['Gift given', 'Badge given'])
        ->and($session->collect_signature)->toBeTrue();
});

<?php

use App\Models\CustomField;
use App\Models\CustomFieldValue;
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

it('requires report permission', function () {
    $user = User::factory()->create([
        'admin' => false,
        'onboarded_at' => now(),
        'permissions' => [],
    ]);

    $this->actingAs($user)
        ->get(route('report.customFields'))
        ->assertForbidden();
});

it('counts custom field responses for active volunteers', function () {
    $field = CustomField::query()->create([
        'name' => 'T-Shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['S', 'M', 'L'],
        'is_active' => true,
    ]);
    $smallVolunteer = User::factory()->create(['active' => true]);
    $mediumVolunteer = User::factory()->create(['active' => true]);
    $inactiveVolunteer = User::factory()->create(['active' => false]);
    User::factory()->create(['active' => true]);

    CustomFieldValue::query()->create(['user_id' => $smallVolunteer->id, 'custom_field_id' => $field->id, 'value' => 'S']);
    CustomFieldValue::query()->create(['user_id' => $mediumVolunteer->id, 'custom_field_id' => $field->id, 'value' => 'M']);
    CustomFieldValue::query()->create(['user_id' => $inactiveVolunteer->id, 'custom_field_id' => $field->id, 'value' => 'L']);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.customFields', ['custom_field_id' => $field->id, 'mode' => 'count']));

    $response->assertOk();

    expect($response->viewData('counts')->all())->toBe([
        'S' => 1,
        'M' => 1,
        'L' => 0,
        'Not provided' => 2,
    ]);
});

it('lists each active volunteer and their custom field response', function () {
    $field = CustomField::query()->create([
        'name' => 'T-Shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['M'],
        'is_active' => true,
    ]);
    $volunteer = User::factory()->create(['name' => 'Shirt Volunteer', 'active' => true]);
    $withoutResponse = User::factory()->create(['name' => 'No Shirt Response', 'active' => true]);
    CustomFieldValue::query()->create([
        'user_id' => $volunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'M',
    ]);

    $this->actingAs($this->reporter)
        ->get(route('report.customFields', ['custom_field_id' => $field->id, 'mode' => 'people']))
        ->assertOk()
        ->assertSee('Shirt Volunteer')
        ->assertSee('No Shirt Response')
        ->assertSee('Not provided');
});

it('splits checkbox selections when counting responses', function () {
    $field = CustomField::query()->create([
        'name' => 'Interests',
        'field_key' => 'interests',
        'field_type' => 'checkbox',
        'options' => ['Setup', 'Teardown'],
        'is_active' => true,
    ]);
    $volunteer = User::factory()->create(['active' => true]);
    CustomFieldValue::query()->create([
        'user_id' => $volunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'Setup,Teardown',
    ]);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.customFields', ['custom_field_id' => $field->id, 'mode' => 'count']));

    $response->assertOk();

    expect($response->viewData('counts')->only(['Setup', 'Teardown'])->all())->toBe([
        'Setup' => 1,
        'Teardown' => 1,
    ]);
});

it('filters response counts by sector', function () {
    $field = CustomField::query()->create([
        'name' => 'T-Shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['M', 'L'],
        'is_active' => true,
    ]);
    $selectedSector = Sector::factory()->create(['name' => 'Furry Migration']);
    $otherSector = Sector::factory()->create(['name' => 'Community']);
    $selectedDepartment = Department::factory()->for($selectedSector)->create();
    $otherDepartment = Department::factory()->for($otherSector)->create();
    $selectedVolunteer = User::factory()->create(['active' => true]);
    $withoutResponse = User::factory()->create(['active' => true]);
    $otherVolunteer = User::factory()->create(['active' => true]);
    $selectedDepartment->users()->attach([$selectedVolunteer->id, $withoutResponse->id]);
    $otherDepartment->users()->attach($otherVolunteer);

    CustomFieldValue::query()->create([
        'user_id' => $selectedVolunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'M',
    ]);
    CustomFieldValue::query()->create([
        'user_id' => $otherVolunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'L',
    ]);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.customFields', [
            'custom_field_id' => $field->id,
            'mode' => 'count',
            'sector_id' => $selectedSector->id,
        ]));

    $response->assertOk()
        ->assertSee('Furry Migration');

    expect($response->viewData('counts')->all())->toBe([
        'M' => 1,
        'L' => 0,
        'Not provided' => 1,
    ]);
});

it('filters the people view by sector and preserves the filter', function () {
    $field = CustomField::query()->create([
        'name' => 'T-Shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'text',
        'is_active' => true,
    ]);
    $selectedSector = Sector::factory()->create();
    $otherSector = Sector::factory()->create();
    $selectedDepartment = Department::factory()->for($selectedSector)->create();
    $otherDepartment = Department::factory()->for($otherSector)->create();
    $selectedVolunteer = User::factory()->create(['name' => 'Selected Volunteer', 'active' => true]);
    $otherVolunteer = User::factory()->create(['name' => 'Other Volunteer', 'active' => true]);
    $selectedDepartment->users()->attach($selectedVolunteer);
    $otherDepartment->users()->attach($otherVolunteer);

    $this->actingAs($this->reporter)
        ->get(route('report.customFields', [
            'custom_field_id' => $field->id,
            'mode' => 'people',
            'sector_id' => $selectedSector->id,
        ]))
        ->assertOk()
        ->assertSee('Selected Volunteer')
        ->assertDontSee('Other Volunteer')
        ->assertSee('name="sector_id"', false)
        ->assertSee('value="'.$selectedSector->id.'"', false);
});

it('filters the people view by a response or missing response', function (string $response, string $visible, string $hidden) {
    $field = CustomField::query()->create([
        'name' => 'T-Shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['Large', 'Small'],
        'is_active' => true,
    ]);
    $largeVolunteer = User::factory()->create(['name' => 'Large Shirt Volunteer', 'active' => true]);
    $withoutResponse = User::factory()->create(['name' => 'Missing Shirt Volunteer', 'active' => true]);
    CustomFieldValue::query()->create([
        'user_id' => $largeVolunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'Large',
    ]);

    $this->actingAs($this->reporter)
        ->get(route('report.customFields', [
            'custom_field_id' => $field->id,
            'mode' => 'people',
            'response' => $response,
        ]))
        ->assertOk()
        ->assertSee($visible)
        ->assertDontSee($hidden);
})->with([
    'provided response' => ['Large', 'Large Shirt Volunteer', 'Missing Shirt Volunteer'],
    'not provided' => ['Not provided', 'Missing Shirt Volunteer', 'Large Shirt Volunteer'],
]);

it('exports only the filtered people results as csv', function () {
    $field = CustomField::query()->create([
        'name' => 'T-Shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['Large', 'Small'],
        'is_active' => true,
    ]);
    $largeVolunteer = User::factory()->create([
        'name' => 'Large Shirt Volunteer',
        'email' => 'large@example.com',
        'active' => true,
    ]);
    $smallVolunteer = User::factory()->create([
        'name' => 'Small Shirt Volunteer',
        'email' => 'small@example.com',
        'active' => true,
    ]);
    CustomFieldValue::query()->create([
        'user_id' => $largeVolunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'Large',
    ]);
    CustomFieldValue::query()->create([
        'user_id' => $smallVolunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'Small',
    ]);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.customFields.export', [
            'custom_field_id' => $field->id,
            'mode' => 'people',
            'response' => 'Large',
        ]));

    $response->assertOk()
        ->assertDownload('t-shirt-size-responses-'.now()->format('Y-m-d').'.csv');

    expect($response->streamedContent())
        ->toContain('Large Shirt Volunteer')
        ->toContain('large@example.com')
        ->not->toContain('Small Shirt Volunteer')
        ->not->toContain('small@example.com');
});

it('exports response counts as csv', function () {
    $field = CustomField::query()->create([
        'name' => 'T-Shirt Size',
        'field_key' => 'tshirt_size',
        'field_type' => 'select',
        'options' => ['Large'],
        'is_active' => true,
    ]);
    $volunteer = User::factory()->create(['active' => true]);
    CustomFieldValue::query()->create([
        'user_id' => $volunteer->id,
        'custom_field_id' => $field->id,
        'value' => 'Large',
    ]);

    $response = $this->actingAs($this->reporter)
        ->get(route('report.customFields.export', [
            'custom_field_id' => $field->id,
            'mode' => 'count',
        ]));

    $response->assertOk();

    expect($response->streamedContent())
        ->toContain('Response,Volunteers')
        ->toContain('Large,1');
});

<?php

use App\Models\CustomField;
use App\Models\CustomFieldValue;
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

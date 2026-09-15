<?php

use App\Models\CustomField;

it('returns only active custom fields, ordered', function () {
    CustomField::create([
        'name' => 'T-Shirt Size', 'field_key' => 'tshirt_size', 'field_type' => 'select',
        'options' => ['Small', 'Medium', 'Large'], 'is_active' => true, 'sort_order' => 2,
    ]);
    CustomField::create([
        'name' => 'Emergency Contact', 'field_key' => 'emergency_contact', 'field_type' => 'text',
        'is_active' => true, 'sort_order' => 1,
    ]);
    CustomField::create([
        'name' => 'Retired Field', 'field_key' => 'retired_field', 'field_type' => 'text',
        'is_active' => false, 'sort_order' => 0,
    ]);

    $response = $this->getJson('/api/custom-fields');

    $response->assertSuccessful()
        ->assertJson(['status' => 'success'])
        ->assertJsonPath('data.0.field_key', 'emergency_contact')
        ->assertJsonPath('data.1.field_key', 'tshirt_size')
        ->assertJsonCount(2, 'data');
});

it('does not require authentication', function () {
    $this->getJson('/api/custom-fields')->assertSuccessful();
});

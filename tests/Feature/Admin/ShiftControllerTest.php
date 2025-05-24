<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use App\Models\Permission; // Assuming a Permission model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class ShiftControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Event $event;
    protected User $assignableUser1;
    protected User $assignableUser2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $manageEventsPermission = Permission::firstOrCreate(['name' => 'manage-events']); // Example permission
        // Add any other permissions that might be implicitly required by the controller or middleware.
        // For simplicity, we assume 'manage-events' covers what's needed for shift management under an event.
        // Or, create a more specific 'manage-shifts' permission if that's the granularity.

        // Create admin user and assign permission
        $this->adminUser = User::factory()->create([
            'email' => 'admin_shift@example.com',
        ]);
        $this->adminUser->permissions()->attach($manageEventsPermission);

        // Authenticate as admin user
        $this->actingAs($this->adminUser);

        // Create a test event
        $this->event = Event::factory()->create([
            'name' => 'Test Event for Shifts',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDay()->addHours(5),
        ]);

        // Create users who can be assigned to shifts
        $this->assignableUser1 = User::factory()->create(['email' => 'user1@example.com']);
        $this->assignableUser2 = User::factory()->create(['email' => 'user2@example.com']);
    }

    private function validShiftData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Morning Shift',
            'description' => 'Helping with setup.',
            'start_time' => $this->event->start_date->format('Y-m-d H:i:s'),
            'end_time' => $this->event->start_date->addHours(2)->format('Y-m-d H:i:s'),
            'max_volunteers' => 5,
            'double_hours' => false,
        ], $overrides);
    }

    /** @test */
    public function admin_can_create_shift_with_user_assignment()
    {
        $shiftData = $this->validShiftData(['user_id' => $this->assignableUser1->id]);

        $response = $this->post(route('admin.events.shifts.store', $this->event), $shiftData);

        $response->assertRedirect(route('admin.events.shifts.index', $this->event));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('shifts', ['name' => 'Morning Shift', 'event_id' => $this->event->id]);
        
        $createdShift = Shift::where('name', 'Morning Shift')->first();
        $this->assertNotNull($createdShift);
        $this->assertDatabaseHas('shift_user', [
            'shift_id' => $createdShift->id,
            'user_id' => $this->assignableUser1->id,
        ]);
    }

    /** @test */
    public function admin_can_create_shift_without_user_assignment()
    {
        $shiftData = $this->validShiftData(); // No user_id

        $response = $this->post(route('admin.events.shifts.store', $this->event), $shiftData);

        $response->assertRedirect(route('admin.events.shifts.index', $this->event));
        $this->assertDatabaseHas('shifts', ['name' => 'Morning Shift']);
        
        $createdShift = Shift::where('name', 'Morning Shift')->first();
        $this->assertNotNull($createdShift);
        $this->assertDatabaseMissing('shift_user', ['shift_id' => $createdShift->id]);
    }

    /** @test */
    public function admin_can_update_shift_and_assign_a_user()
    {
        $shift = Shift::factory()->create(['event_id' => $this->event->id]);
        $updateData = $this->validShiftData([
            'name' => 'Updated Morning Shift',
            'user_id' => $this->assignableUser1->id,
        ]);

        $response = $this->put(route('admin.events.shifts.update', [$this->event, $shift]), $updateData);

        $response->assertRedirect(route('admin.events.shifts.index', $this->event));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('shifts', ['id' => $shift->id, 'name' => 'Updated Morning Shift']);
        $this->assertDatabaseHas('shift_user', [
            'shift_id' => $shift->id,
            'user_id' => $this->assignableUser1->id,
        ]);
    }

    /** @test */
    public function admin_can_update_shift_assigning_user_when_another_is_already_assigned()
    {
        $shift = Shift::factory()->create(['event_id' => $this->event->id]);
        // Assign initial user directly
        $shift->users()->attach($this->assignableUser1->id);
        $this->assertDatabaseHas('shift_user', ['shift_id' => $shift->id, 'user_id' => $this->assignableUser1->id]);

        $updateData = $this->validShiftData([
            'name' => $shift->name, // Keep name the same
            'user_id' => $this->assignableUser2->id, // Assign the second user
        ]);

        $response = $this->put(route('admin.events.shifts.update', [$this->event, $shift]), $updateData);

        $response->assertRedirect(route('admin.events.shifts.index', $this->event));
        $this->assertDatabaseHas('shift_user', [ // New user assigned
            'shift_id' => $shift->id,
            'user_id' => $this->assignableUser2->id,
        ]);
        $this->assertDatabaseHas('shift_user', [ // Initial user still assigned
            'shift_id' => $shift->id,
            'user_id' => $this->assignableUser1->id,
        ]);
    }

    /** @test */
    public function admin_can_update_shift_by_only_changing_user_assignment()
    {
        $shift = Shift::factory()->create(['event_id' => $this->event->id, 'name' => 'Original Shift Name']);
        $originalShiftData = $shift->toArray(); // Keep a copy of original data

        $updateData = [
            'name' => $originalShiftData['name'],
            'description' => $originalShiftData['description'],
            'start_time' => Carbon::parse($originalShiftData['start_time'])->format('Y-m-d H:i:s'),
            'end_time' => Carbon::parse($originalShiftData['end_time'])->format('Y-m-d H:i:s'),
            'max_volunteers' => $originalShiftData['max_volunteers'],
            'double_hours' => (bool)$originalShiftData['double_hours'],
            'user_id' => $this->assignableUser1->id, // Only new piece of info effectively
        ];

        $response = $this->put(route('admin.events.shifts.update', [$this->event, $shift]), $updateData);
        $response->assertRedirect(route('admin.events.shifts.index', $this->event));

        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'name' => 'Original Shift Name', // Ensure other data didn't change
        ]);
        $this->assertDatabaseHas('shift_user', [
            'shift_id' => $shift->id,
            'user_id' => $this->assignableUser1->id,
        ]);
    }
    
    /** @test */
    public function update_shift_with_invalid_user_id_fails_validation()
    {
        $shift = Shift::factory()->create(['event_id' => $this->event->id]);
        $updateData = $this->validShiftData(['user_id' => 99999]); // Non-existent user ID

        $response = $this->put(route('admin.events.shifts.update', [$this->event, $shift]), $updateData);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseMissing('shift_user', ['shift_id' => $shift->id, 'user_id' => 99999]);
    }

    /** @test */
    public function create_shift_with_invalid_user_id_fails_validation()
    {
        $shiftData = $this->validShiftData(['user_id' => 99999]); // Non-existent user ID

        $response = $this->post(route('admin.events.shifts.store', $this->event), $shiftData);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('shifts', 0); // No shift should be created
    }
}
?>

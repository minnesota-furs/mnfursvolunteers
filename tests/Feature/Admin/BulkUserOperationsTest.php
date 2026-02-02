<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\Permission;
use App\Models\Sector;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUserOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $user1;
    protected User $user2;
    protected Department $department;
    protected FiscalLedger $fiscalLedger;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage-users']);

        // Create admin user
        $this->adminUser = User::factory()->create(['email' => 'admin@test.com']);
        $this->adminUser->permissions()->attach($manageUsersPermission);

        // Create test users
        $this->user1 = User::factory()->create(['name' => 'Test User 1']);
        $this->user2 = User::factory()->create(['name' => 'Test User 2']);

        // Create sector and department
        $sector = Sector::factory()->create(['name' => 'Test Sector']);
        $this->department = Department::factory()->create([
            'name' => 'Test Department',
            'sector_id' => $sector->id,
        ]);

        // Create fiscal ledger for current year
        $this->fiscalLedger = FiscalLedger::factory()->create([
            'name' => 'Current Fiscal Year',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);
    }

    /** @test */
    public function admin_can_bulk_log_hours_for_users()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.users.bulk-log-hours'), [
            'user_ids' => "{$this->user1->id},{$this->user2->id}",
            'hours' => 5.0,
            'date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
            'description' => 'Bulk test hours',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // Verify hours were logged for both users
        $this->assertDatabaseHas('volunteer_hours', [
            'user_id' => $this->user1->id,
            'hours' => 5.0,
            'department_id' => $this->department->id,
        ]);

        $this->assertDatabaseHas('volunteer_hours', [
            'user_id' => $this->user2->id,
            'hours' => 5.0,
            'department_id' => $this->department->id,
        ]);
    }

    /** @test */
    public function admin_can_bulk_add_tags_to_users()
    {
        $this->actingAs($this->adminUser);

        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2']);

        $response = $this->post(route('admin.users.bulk-add-tags'), [
            'user_ids' => "{$this->user1->id},{$this->user2->id}",
            'tag_ids' => [$tag1->id, $tag2->id],
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // Verify tags were added to both users
        $this->assertTrue($this->user1->fresh()->tags->contains($tag1));
        $this->assertTrue($this->user1->fresh()->tags->contains($tag2));
        $this->assertTrue($this->user2->fresh()->tags->contains($tag1));
        $this->assertTrue($this->user2->fresh()->tags->contains($tag2));
    }

    /** @test */
    public function admin_can_bulk_remove_tags_from_users()
    {
        $this->actingAs($this->adminUser);

        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2']);

        // Attach tags first
        $this->user1->tags()->attach([$tag1->id, $tag2->id]);
        $this->user2->tags()->attach([$tag1->id, $tag2->id]);

        $response = $this->post(route('admin.users.bulk-remove-tags'), [
            'user_ids' => "{$this->user1->id},{$this->user2->id}",
            'tag_ids' => [$tag1->id],
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // Verify tag1 was removed but tag2 remains
        $this->assertFalse($this->user1->fresh()->tags->contains($tag1));
        $this->assertTrue($this->user1->fresh()->tags->contains($tag2));
        $this->assertFalse($this->user2->fresh()->tags->contains($tag1));
        $this->assertTrue($this->user2->fresh()->tags->contains($tag2));
    }

    /** @test */
    public function admin_can_bulk_assign_department_to_users()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.users.bulk-assign-department'), [
            'user_ids' => "{$this->user1->id},{$this->user2->id}",
            'department_id' => $this->department->id,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // Verify department was assigned to both users
        $this->assertTrue($this->user1->fresh()->departments->contains($this->department));
        $this->assertTrue($this->user2->fresh()->departments->contains($this->department));
    }

    /** @test */
    public function non_admin_cannot_perform_bulk_operations()
    {
        $regularUser = User::factory()->create();
        $this->actingAs($regularUser);

        $response = $this->post(route('admin.users.bulk-log-hours'), [
            'user_ids' => "{$this->user1->id}",
            'hours' => 5.0,
            'date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function bulk_operations_require_selected_users()
    {
        $this->actingAs($this->adminUser);

        $tag = Tag::factory()->create();

        $response = $this->post(route('admin.users.bulk-add-tags'), [
            'user_ids' => '',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function bulk_log_hours_validates_input()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.users.bulk-log-hours'), [
            'user_ids' => "{$this->user1->id}",
            'hours' => -5, // Invalid: negative hours
            'date' => 'invalid-date',
            'department_id' => 999999, // Invalid: non-existent department
        ]);

        $response->assertSessionHasErrors(['hours', 'date', 'department_id']);
    }
}

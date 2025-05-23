<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Permission; // Assuming a Permission model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $userAlice;
    protected User $userBob;
    protected User $userCharlie;
    protected User $userAlex;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage-users']);
        // Assuming other permissions might be needed by default or for other tests
        Permission::firstOrCreate(['name' => 'view-dashboard']);


        // Create admin user and assign permission
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'username' => 'adminuser',
        ]);
        $this->adminUser->permissions()->attach($manageUsersPermission);

        // Create other users for searching
        $this->userAlice = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Wonderland',
            'username' => 'alicew',
            'email' => 'alice@example.com',
        ]);

        $this->userBob = User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'The Builder',
            'username' => 'bobthebuilder',
            'email' => 'bob@example.com',
        ]);

        $this->userCharlie = User::factory()->create([
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'username' => 'charlieb',
            'email' => 'charlie@example.com',
        ]);

        $this->userAlex = User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'P. Keaton',
            'username' => 'alexpk',
            'email' => 'alex@example.com',
        ]);

        // Another Alex for multiple match testing
        User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'Smith',
            'username' => 'alexs',
            'email' => 'alex.smith@example.com',
        ]);
    }

    /** @test */
    public function admin_can_search_users_by_first_name()
    {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.users.search', ['term' => 'Alic']));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $this->userAlice->id,
            'first_name' => 'Alice',
            'username' => 'alicew',
            'email' => 'alice@example.com',
        ]);
        $response->assertJsonMissing([
            'email' => $this->userBob->email, // Ensure Bob is not in results
        ]);
    }

    /** @test */
    public function admin_can_search_users_by_last_name()
    {
        $this->actingAs($this->adminUser);

        // userBob has last_name 'The Builder'
        $response = $this->getJson(route('admin.users.search', ['term' => 'Builder']));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $this->userBob->id,
            'first_name' => 'Bob',
            'last_name' => 'The Builder',
            'username' => 'bobthebuilder',
            'email' => 'bob@example.com',
        ]);
        // Ensure it's not accidentally matching first_name or email of another user
        $response->assertJsonMissing(['email' => $this->userAlice->email]);
    }

    /** @test */
    public function admin_can_search_users_by_email()
    {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.users.search', ['term' => 'charlie@examp']));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $this->userCharlie->id,
            'email' => 'charlie@example.com',
        ]);
    }

    /** @test */
    public function search_returns_multiple_users_if_term_matches_multiple()
    {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.users.search', ['term' => 'Alex']));

        $response->assertOk();
        $response->assertJsonCount(2); // Expecting Alex P. Keaton and Alex Smith
        $response->assertJsonFragment(['email' => 'alex@example.com']);
        $response->assertJsonFragment(['email' => 'alex.smith@example.com']);
    }

    /** @test */
    public function search_returns_empty_array_for_no_matches()
    {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.users.search', ['term' => 'NoOneMatchesThis']));

        $response->assertOk();
        $response->assertJsonCount(0);
        $response->assertExactJson([]);
    }

    /** @test */
    public function search_returns_empty_array_for_term_too_short()
    {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.users.search', ['term' => 'A'])); // Term of 1 char

        $response->assertOk(); // The controller returns JSON [] with 200 OK
        $response->assertExactJson([]);
    }

    /** @test */
    public function user_without_manage_users_permission_cannot_search_users()
    {
        $nonAdminUser = User::factory()->create();
        // Ensure this user does NOT have 'manage-users' permission.
        // If permissions are default deny, this is enough.
        // If there's a default role, ensure it doesn't include 'manage-users'.

        $this->actingAs($nonAdminUser);

        $response = $this->getJson(route('admin.users.search', ['term' => 'test']));

        $response->assertForbidden();
    }

    /** @test */
    public function guest_cannot_search_users()
    {
        $response = $this->getJson(route('admin.users.search', ['term' => 'test']));
        $response->assertUnauthorized(); // Expect redirect to login or 401 if API
    }
}
?>

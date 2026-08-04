<?php

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;

it('allows users with the announcement permission to create targeted announcements', function () {
    $manager = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Announcements'],
    ]);
    $sector = Sector::factory()->create();
    $department = Department::factory()->for($sector)->create();

    $this->actingAs($manager)->post(route('announcements.store'), [
        'title' => 'Operations update',
        'body' => 'Please review the updated procedure.',
        'expires_at' => now()->addWeek()->format('Y-m-d H:i:s'),
        'departments' => [$department->id],
        'sectors' => [$sector->id],
    ])->assertRedirect(route('announcements.index'));

    $announcement = Announcement::query()->where('title', 'Operations update')->firstOrFail();

    expect($announcement->creator->is($manager))->toBeTrue()
        ->and($announcement->departments->modelKeys())->toBe([$department->id])
        ->and($announcement->sectors->modelKeys())->toBe([$sector->id]);
});

it('prevents users without the announcement permission from managing announcements', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('announcements.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('announcements.store'), [
            'title' => 'Unauthorized announcement',
            'body' => 'This should not be created.',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('announcements', ['title' => 'Unauthorized announcement']);
});

it('allows announcement managers to update and delete announcements', function () {
    $manager = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Announcements'],
    ]);
    $announcement = Announcement::factory()->create();

    $this->actingAs($manager)->put(route('announcements.update', $announcement), [
        'title' => 'Updated announcement',
        'body' => 'Updated announcement body.',
        'volunteers_only' => true,
    ])->assertRedirect(route('announcements.index'));

    $this->assertDatabaseHas('announcements', [
        'id' => $announcement->id,
        'title' => 'Updated announcement',
        'volunteers_only' => true,
    ]);

    $this->actingAs($manager)
        ->delete(route('announcements.destroy', $announcement))
        ->assertRedirect(route('announcements.index'));

    $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
});

it('shows active announcements on the dashboard for matching audiences', function () {
    $sector = Sector::factory()->create();
    $department = Department::factory()->for($sector)->create();
    $otherDepartment = Department::factory()->create();
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach($department);

    Announcement::factory()->create([
        'title' => 'Everyone announcement',
        'created_at' => now()->subHours(2),
    ]);

    $departmentAnnouncement = Announcement::factory()->create(['title' => 'Department announcement']);
    $departmentAnnouncement->departments()->attach($department);

    $sectorAnnouncement = Announcement::factory()->create(['title' => 'Sector announcement']);
    $sectorAnnouncement->sectors()->attach($sector);

    $otherAnnouncement = Announcement::factory()->create(['title' => 'Other department announcement']);
    $otherAnnouncement->departments()->attach($otherDepartment);

    Announcement::factory()->create([
        'title' => 'Volunteers only announcement',
        'volunteers_only' => true,
    ]);

    Announcement::factory()->expired()->create(['title' => 'Expired announcement']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('id="dashboard-announcements-heading"', false)
        ->assertSee('md:grid-cols-2', false)
        ->assertSeeText('Everyone announcement')
        ->assertSeeText('Posted 2 hours ago')
        ->assertSeeText('Department announcement')
        ->assertSeeText('Sector announcement')
        ->assertDontSeeText('Other department announcement')
        ->assertDontSeeText('Volunteers only announcement')
        ->assertDontSeeText('Expired announcement');
});

it('shows volunteer-only announcements exclusively to users without departments', function () {
    $volunteer = User::factory()->create(['onboarded_at' => now()]);
    $departmentMember = User::factory()->create(['onboarded_at' => now()]);
    $departmentMember->departments()->attach(Department::factory()->create());

    $announcement = Announcement::factory()->create([
        'title' => 'Volunteer update',
        'volunteers_only' => true,
    ]);

    $this->actingAs($volunteer)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSeeText('Volunteer update');

    $this->actingAs($volunteer)
        ->get(route('announcements.show', $announcement))
        ->assertSuccessful();

    $this->actingAs($departmentMember)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertDontSeeText('Volunteer update');

    $this->actingAs($departmentMember)
        ->get(route('announcements.show', $announcement))
        ->assertNotFound();
});

it('removes announcements from the active query at their expiration time', function () {
    $active = Announcement::factory()->create(['expires_at' => now()->addMinute()]);
    Announcement::factory()->expired()->create();

    expect(Announcement::query()->active()->pluck('id')->all())->toBe([$active->id]);
});

it('renders announcement markdown safely on the reading page', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $announcement = Announcement::factory()->create([
        'title' => 'Markdown announcement',
        'body' => "This is **important**.\n\n<script>alert('unsafe')</script>",
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSeeText('Markdown announcement')
        ->assertDontSee('<strong>important</strong>', false);

    $this->actingAs($user)
        ->get(route('announcements.show', $announcement))
        ->assertSuccessful()
        ->assertSee('<strong>important</strong>', false)
        ->assertDontSee('<script>', false);
});

it('does not allow direct access to expired or restricted announcements', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $department = Department::factory()->create();

    $restricted = Announcement::factory()->create();
    $restricted->departments()->attach($department);
    $expired = Announcement::factory()->expired()->create();

    $this->actingAs($user)
        ->get(route('announcements.show', $restricted))
        ->assertNotFound();

    $this->actingAs($user)
        ->get(route('announcements.show', $expired))
        ->assertNotFound();
});

it('validates announcement audience selections', function () {
    $manager = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Announcements'],
    ]);

    $this->actingAs($manager)->post(route('announcements.store'), [
        'title' => 'Invalid audience',
        'body' => 'Invalid audience selections should fail.',
        'departments' => [PHP_INT_MAX],
        'sectors' => [PHP_INT_MAX],
    ])->assertSessionHasErrors(['departments.0', 'sectors.0']);

    $department = Department::factory()->create();

    $this->actingAs($manager)->post(route('announcements.store'), [
        'title' => 'Conflicting audience',
        'body' => 'Audience modes must be mutually exclusive.',
        'volunteers_only' => true,
        'departments' => [$department->id],
    ])->assertSessionHasErrors('departments');
});

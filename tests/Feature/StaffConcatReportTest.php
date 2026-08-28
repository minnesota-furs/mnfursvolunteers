<?php

use App\Models\ApplicationSetting;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use App\Services\ConcatService;

beforeEach(function () {
    $this->reporter = User::factory()->create([
        'active' => true,
        'admin' => false,
        'onboarded_at' => now(),
        'permissions' => ['View Reports'],
    ]);
});

it('redirects to settings when ConCat is not connected', function () {
    $this->actingAs($this->reporter)
        ->get(route('report.staffConcat'))
        ->assertRedirect(route('settings.index'))
        ->assertSessionHas('error');
});

it('hides the nav link when ConCat is not connected and shows it when connected', function () {
    $this->actingAs($this->reporter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Staff & Concat', false);

    ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');

    $this->actingAs($this->reporter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Staff & Concat', false);
});

describe('when ConCat is connected', function () {
    beforeEach(function () {
        ApplicationSetting::set('concat_api_base_url', 'https://fm-test.concat.app', 'string', null, 'integrations');
        ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
        ApplicationSetting::set('concat_client_secret', 'super-secret', 'encrypted', null, 'integrations');
    });

    it('shows the landing page linking to the three sub-reports', function () {
        $this->mock(ConcatService::class, fn ($mock) => $mock->shouldReceive('isConfigured')->andReturn(true));

        $this->actingAs($this->reporter)
            ->get(route('report.staffConcat'))
            ->assertOk()
            ->assertSee('Unlinked Users')
            ->assertSee('Staff With Registrations')
            ->assertSee('Staff Without Registration')
            ->assertSee(route('report.staffConcat.unlinked'), false)
            ->assertSee(route('report.staffConcat.withRegistration'), false)
            ->assertSee(route('report.staffConcat.withoutRegistration'), false);
    });

    describe('Unlinked Users report', function () {
        it('lists active staff with no ConCat account, filterable by sector', function () {
            $sectorA = Sector::factory()->create(['name' => 'Operations']);
            $sectorB = Sector::factory()->create(['name' => 'Registration']);
            $departmentA = Department::factory()->create(['sector_id' => $sectorA->id]);
            $departmentB = Department::factory()->create(['sector_id' => $sectorB->id]);

            $inSectorA = User::factory()->create(['name' => 'Sector A Unlinked', 'active' => true]);
            $inSectorB = User::factory()->create(['name' => 'Sector B Unlinked', 'active' => true]);
            $inactive = User::factory()->create(['name' => 'Inactive Unlinked', 'active' => false]);
            $departmentA->users()->attach($inSectorA);
            $departmentB->users()->attach($inSectorB);
            $departmentA->users()->attach($inactive);

            $this->mock(ConcatService::class, fn ($mock) => $mock->shouldReceive('isConfigured')->andReturn(true));

            $this->actingAs($this->reporter)
                ->get(route('report.staffConcat.unlinked', ['sector_id' => $sectorA->id]))
                ->assertOk()
                ->assertSee('Sector A Unlinked')
                ->assertDontSee('Sector B Unlinked')
                ->assertDontSee('Inactive Unlinked');
        });

        it('exports unlinked staff as CSV', function () {
            $department = Department::factory()->create();
            $user = User::factory()->create(['name' => 'CSV Unlinked', 'email' => 'csv-unlinked@example.com', 'active' => true]);
            $department->users()->attach($user);

            $this->mock(ConcatService::class, fn ($mock) => $mock->shouldReceive('isConfigured')->andReturn(true));

            $response = $this->actingAs($this->reporter)->get(route('report.staffConcat.unlinked.export'));

            $response->assertOk();
            expect($response->headers->get('Content-Type'))->toContain('text/csv');
            expect($response->streamedContent())
                ->toContain('CSV Unlinked')
                ->toContain('csv-unlinked@example.com');
        });
    });

    describe('Staff With Registrations report', function () {
        it('lists linked staff who have a ConCat registration', function () {
            $department = Department::factory()->create();
            $registered = User::factory()->create(['name' => 'Registered Volunteer', 'active' => true, 'concat_user_id' => 'concat-1']);
            $unregistered = User::factory()->create(['name' => 'Unregistered Volunteer', 'active' => true, 'concat_user_id' => 'concat-2']);
            $department->users()->attach([$registered->id, $unregistered->id]);

            $this->mock(ConcatService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('searchRegistrationsByUserIds')->once()
                    ->andReturn([['uuid' => 'reg-1', 'user' => ['id' => 'concat-1']]]);
            });

            $this->actingAs($this->reporter)
                ->get(route('report.staffConcat.withRegistration'))
                ->assertOk()
                ->assertSee('Registered Volunteer')
                ->assertDontSee('Unregistered Volunteer');
        });

        it('exports staff with registrations as CSV', function () {
            $department = Department::factory()->create();
            $registered = User::factory()->create(['name' => 'CSV Registered', 'email' => 'csv-registered@example.com', 'active' => true, 'concat_user_id' => 'concat-1']);
            $department->users()->attach($registered);

            $this->mock(ConcatService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('searchRegistrationsByUserIds')->once()
                    ->andReturn([['uuid' => 'reg-1', 'user' => ['id' => 'concat-1']]]);
            });

            $response = $this->actingAs($this->reporter)->get(route('report.staffConcat.withRegistration.export'));

            $response->assertOk();
            expect($response->streamedContent())
                ->toContain('CSV Registered')
                ->toContain('csv-registered@example.com');
        });
    });

    describe('Staff Without Registration report', function () {
        it('lists linked staff who do not have a ConCat registration', function () {
            $department = Department::factory()->create();
            $registered = User::factory()->create(['name' => 'Registered Volunteer', 'active' => true, 'concat_user_id' => 'concat-1']);
            $unregistered = User::factory()->create(['name' => 'Unregistered Volunteer', 'active' => true, 'concat_user_id' => 'concat-2']);
            $department->users()->attach([$registered->id, $unregistered->id]);

            $this->mock(ConcatService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('searchRegistrationsByUserIds')->once()
                    ->andReturn([['uuid' => 'reg-1', 'user' => ['id' => 'concat-1']]]);
            });

            $this->actingAs($this->reporter)
                ->get(route('report.staffConcat.withoutRegistration'))
                ->assertOk()
                ->assertSee('Unregistered Volunteer')
                ->assertDontSee('Registered Volunteer');
        });

        it('exports staff without registration as CSV', function () {
            $department = Department::factory()->create();
            $unregistered = User::factory()->create(['name' => 'CSV Unregistered', 'email' => 'csv-unregistered@example.com', 'active' => true, 'concat_user_id' => 'concat-2']);
            $department->users()->attach($unregistered);

            $this->mock(ConcatService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('searchRegistrationsByUserIds')->once()->andReturn([]);
            });

            $response = $this->actingAs($this->reporter)->get(route('report.staffConcat.withoutRegistration.export'));

            $response->assertOk();
            expect($response->streamedContent())
                ->toContain('CSV Unregistered')
                ->toContain('csv-unregistered@example.com');
        });
    });

    it('does not call ConCat when there are no linked staff to check', function () {
        $user = User::factory()->create(['active' => true]);
        Department::factory()->create()->users()->attach($user);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldNotReceive('searchRegistrationsByUserIds');
        });

        $this->actingAs($this->reporter)
            ->get(route('report.staffConcat.withRegistration'))
            ->assertOk();
    });
});

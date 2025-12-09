<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventsAndShiftsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an admin user to be the event creator
        $creator = User::where('admin', true)->first() ?? User::factory()->create([
            'name' => 'Event Coordinator',
            'email' => 'coordinator@mnfurs.org',
            'admin' => true,
        ]);

        // Create a public single-day event with multiple shifts
        $singleDayEvent = Event::factory()
            ->public()
            ->singleDay()
            ->create([
                'name' => 'Annual Charity Fundraiser',
                'description' => 'Join us for our annual charity fundraiser! We need volunteers for various roles throughout the day.',
                'location' => 'Main Convention Center',
                'created_by' => $creator->id,
            ]);

        // Create shifts for the single-day event
        Shift::factory()
            ->forEvent($singleDayEvent)
            ->count(3)
            ->sequence(
                [
                    'name' => 'Morning Setup',
                    'description' => 'Help set up tables, chairs, and decorations',
                    'start_time' => $singleDayEvent->start_date->format('Y-m-d') . ' 08:00:00',
                    'end_time' => $singleDayEvent->start_date->format('Y-m-d') . ' 11:00:00',
                    'max_volunteers' => 8,
                ],
                [
                    'name' => 'Registration Desk',
                    'description' => 'Welcome attendees and manage check-in',
                    'start_time' => $singleDayEvent->start_date->format('Y-m-d') . ' 11:00:00',
                    'end_time' => $singleDayEvent->start_date->format('Y-m-d') . ' 15:00:00',
                    'max_volunteers' => 4,
                ],
                [
                    'name' => 'Evening Cleanup',
                    'description' => 'Help clean up and pack away event materials',
                    'start_time' => $singleDayEvent->start_date->format('Y-m-d') . ' 15:00:00',
                    'end_time' => $singleDayEvent->start_date->format('Y-m-d') . ' 18:00:00',
                    'max_volunteers' => 10,
                    'double_hours' => true,
                ],
            )
            ->create();

        // Create a public multi-day convention event
        $conventionEvent = Event::factory()
            ->public()
            ->multiDay()
            ->upcoming()
            ->create([
                'name' => 'Furry Migration Convention 2026',
                'description' => 'Our annual convention needs volunteers! Multiple shifts available across the weekend.',
                'location' => 'Hotel Convention Center',
                'created_by' => $creator->id,
                'auto_credit_hours' => true,
            ]);

        // Create multiple shifts across different days for the convention
        $conventionStart = $conventionEvent->start_date;
        
        // Day 1 shifts
        Shift::factory()
            ->forEvent($conventionEvent)
            ->count(5)
            ->sequence(
                [
                    'name' => 'Con Setup - Day 1',
                    'start_time' => $conventionStart->format('Y-m-d') . ' 08:00:00',
                    'end_time' => $conventionStart->format('Y-m-d') . ' 12:00:00',
                    'max_volunteers' => 15,
                ],
                [
                    'name' => 'Registration - Morning',
                    'start_time' => $conventionStart->format('Y-m-d') . ' 12:00:00',
                    'end_time' => $conventionStart->format('Y-m-d') . ' 16:00:00',
                    'max_volunteers' => 6,
                ],
                [
                    'name' => 'Registration - Afternoon',
                    'start_time' => $conventionStart->format('Y-m-d') . ' 16:00:00',
                    'end_time' => $conventionStart->format('Y-m-d') . ' 20:00:00',
                    'max_volunteers' => 6,
                ],
                [
                    'name' => 'Info Booth - Evening',
                    'start_time' => $conventionStart->format('Y-m-d') . ' 18:00:00',
                    'end_time' => $conventionStart->format('Y-m-d') . ' 22:00:00',
                    'max_volunteers' => 3,
                ],
                [
                    'name' => 'Room Monitor - Panel Rooms',
                    'start_time' => $conventionStart->format('Y-m-d') . ' 14:00:00',
                    'end_time' => $conventionStart->format('Y-m-d') . ' 18:00:00',
                    'max_volunteers' => 4,
                ],
            )
            ->create();

        // Day 2 shifts
        $day2 = (clone $conventionStart)->modify('+1 day');
        Shift::factory()
            ->forEvent($conventionEvent)
            ->count(4)
            ->sequence(
                [
                    'name' => 'Dealers Den Support',
                    'start_time' => $day2->format('Y-m-d') . ' 10:00:00',
                    'end_time' => $day2->format('Y-m-d') . ' 14:00:00',
                    'max_volunteers' => 5,
                ],
                [
                    'name' => 'Artists Alley Support',
                    'start_time' => $day2->format('Y-m-d') . ' 14:00:00',
                    'end_time' => $day2->format('Y-m-d') . ' 18:00:00',
                    'max_volunteers' => 4,
                ],
                [
                    'name' => 'Events Coordinator',
                    'start_time' => $day2->format('Y-m-d') . ' 12:00:00',
                    'end_time' => $day2->format('Y-m-d') . ' 20:00:00',
                    'max_volunteers' => 3,
                    'double_hours' => true,
                ],
                [
                    'name' => 'Gopher Team',
                    'start_time' => $day2->format('Y-m-d') . ' 09:00:00',
                    'end_time' => $day2->format('Y-m-d') . ' 17:00:00',
                    'max_volunteers' => 8,
                ],
            )
            ->create();

        // Create an unlisted event (for staff only)
        $staffEvent = Event::factory()
            ->unlisted()
            ->create([
                'name' => 'Staff Meeting & Planning',
                'description' => 'Internal staff meeting and event planning session',
                'location' => 'Conference Room B',
                'created_by' => $creator->id,
            ]);

        Shift::factory()
            ->forEvent($staffEvent)
            ->count(2)
            ->create();

        // Create a draft event (not yet published)
        $draftEvent = Event::factory()
            ->draft()
            ->create([
                'name' => 'Future Community Picnic',
                'description' => 'Planning in progress - outdoor community gathering',
                'location' => 'City Park',
                'created_by' => $creator->id,
            ]);

        Shift::factory()
            ->forEvent($draftEvent)
            ->count(3)
            ->create();

        // Create some additional random events with shifts
        Event::factory()
            ->count(3)
            ->create(['created_by' => $creator->id])
            ->each(function ($event) {
                // Create 2-6 random shifts for each event
                Shift::factory()
                    ->forEvent($event)
                    ->count(fake()->numberBetween(2, 6))
                    ->create();
            });

        $this->command->info('Events and shifts seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 1 Single-day charity event with 3 shifts');
        $this->command->info('- 1 Multi-day convention with 9 shifts');
        $this->command->info('- 1 Unlisted staff event with 2 shifts');
        $this->command->info('- 1 Draft event with 3 shifts');
        $this->command->info('- 3 Additional random events with multiple shifts each');
    }
}

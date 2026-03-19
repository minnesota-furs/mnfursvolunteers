<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class MusicConSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('admin', true)->first();

        // 3-day event: June 19–21, 2026
        $event = Event::create([
            'name'              => 'Music Con 2026',
            'description'       => 'A three-day celebration of music, performances, and community. Volunteers needed for a variety of roles across the weekend.',
            'location'          => 'The Grand Ballroom & Convention Center',
            'start_date'        => '2026-06-19 08:00:00',
            'end_date'          => '2026-06-21 22:00:00',
            'visibility'        => 'public',
            'signup_open_date'  => '2026-05-01 00:00:00',
            'auto_credit_hours' => true,
            'created_by'        => $creator?->id,
        ]);

        $shifts = [
            // --- Day 1: June 19 ---
            ['name' => 'Setup Crew',           'day' => '2026-06-19', 'start' => '08:00', 'end' => '12:00', 'max' => 12, 'description' => 'Unload equipment, set up stages, signage, and common areas.'],
            ['name' => 'Registration – AM',    'day' => '2026-06-19', 'start' => '11:00', 'end' => '15:00', 'max' => 6,  'description' => 'Check in attendees and hand out badges.'],
            ['name' => 'Registration – PM',    'day' => '2026-06-19', 'start' => '15:00', 'end' => '19:00', 'max' => 6,  'description' => 'Continue check-in and handle walk-up registrations.'],
            ['name' => 'Stage Monitor – Main', 'day' => '2026-06-19', 'start' => '14:00', 'end' => '22:00', 'max' => 4,  'description' => 'Manage crowd flow and safety around the main stage.'],
            ['name' => 'Info Booth',           'day' => '2026-06-19', 'start' => '12:00', 'end' => '20:00', 'max' => 3,  'description' => 'Answer attendee questions and hand out schedules.'],

            // --- Day 2: June 20 ---
            ['name' => 'Stage Monitor – Main', 'day' => '2026-06-20', 'start' => '10:00', 'end' => '18:00', 'max' => 4,  'description' => 'Manage crowd flow and safety around the main stage.'],
            ['name' => 'Stage Monitor – Side', 'day' => '2026-06-20', 'start' => '10:00', 'end' => '18:00', 'max' => 4,  'description' => 'Manage crowd flow around the side stage.'],
            ['name' => 'Merchandise Table',    'day' => '2026-06-20', 'start' => '10:00', 'end' => '14:00', 'max' => 4,  'description' => 'Assist vendors and handle sales at the merchandise table.'],
            ['name' => 'Merchandise Table',    'day' => '2026-06-20', 'start' => '14:00', 'end' => '18:00', 'max' => 4,  'description' => 'Assist vendors and handle sales at the merchandise table.'],
            ['name' => 'Green Room Support',   'day' => '2026-06-20', 'start' => '12:00', 'end' => '22:00', 'max' => 3,  'description' => 'Support performers in the green room — refreshments and logistics.', 'double_hours' => true],
            ['name' => 'Info Booth',           'day' => '2026-06-20', 'start' => '10:00', 'end' => '18:00', 'max' => 3,  'description' => 'Answer attendee questions and hand out schedules.'],
            ['name' => 'Hospitality Lounge',   'day' => '2026-06-20', 'start' => '18:00', 'end' => '22:00', 'max' => 5,  'description' => 'Staff the hospitality lounge for evening social events.'],

            // --- Day 3: June 21 ---
            ['name' => 'Stage Monitor – Main', 'day' => '2026-06-21', 'start' => '10:00', 'end' => '20:00', 'max' => 4,  'description' => 'Manage crowd flow and safety around the main stage.'],
            ['name' => 'Merchandise Table',    'day' => '2026-06-21', 'start' => '10:00', 'end' => '16:00', 'max' => 4,  'description' => 'Assist vendors and handle sales at the merchandise table.'],
            ['name' => 'Closing Ceremony',     'day' => '2026-06-21', 'start' => '18:00', 'end' => '20:00', 'max' => 8,  'description' => 'Help coordinate the closing ceremony and award presentations.'],
            ['name' => 'Teardown Crew',        'day' => '2026-06-21', 'start' => '20:00', 'end' => '23:00', 'max' => 15, 'description' => 'Break down stages, pack equipment, and restore the venue.', 'double_hours' => true],
        ];

        foreach ($shifts as $shift) {
            Shift::create([
                'event_id'      => $event->id,
                'name'          => $shift['name'],
                'description'   => $shift['description'] ?? null,
                'start_time'    => $shift['day'] . ' ' . $shift['start'] . ':00',
                'end_time'      => $shift['day'] . ' ' . $shift['end'] . ':00',
                'max_volunteers' => $shift['max'],
                'double_hours'  => $shift['double_hours'] ?? false,
            ]);
        }
    }
}

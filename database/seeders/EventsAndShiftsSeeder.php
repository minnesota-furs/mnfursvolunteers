<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventsAndShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('admin', true)->first();

        // Create 15 past events with shifts
        Event::factory()
            ->count(15)
            ->past()
            ->state(['created_by' => $creator?->id])
            ->create()
            ->each(function (Event $event) {
                $start = Carbon::parse($event->start_date);
                $end = Carbon::parse($event->end_date);
                $days = $start->daysUntil($end);

                foreach ($days as $day) {
                    $shiftCount = rand(2, 5);
                    Shift::factory()
                        ->count($shiftCount)
                        ->forEvent($event, $day)
                        ->create();
                }
            });
    }
}

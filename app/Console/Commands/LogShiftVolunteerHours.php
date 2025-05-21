<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Shift;
use App\Models\VolunteerHours;
use App\Models\FiscalLedger;
use Carbon\Carbon;

class LogShiftVolunteerHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'volunteer:log-shift-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically log volunteer hours for completed shifts the day after an event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday()->toDateString();

        // Fetch events that ended yesterday
        $events = Event::whereDate('end_date', $yesterday)->get();

        // Get the current date
        $currentDate = now();

        // Find the fiscal ledger that covers the volunteer date
        $fiscalLedger = FiscalLedger::where('start_date', '<=', $currentDate)
                                    ->where('end_date', '>=', $currentDate)
                                    ->first();

        foreach ($events as $event) {
            $this->line($event->name);
            if ($event->auto_credit_hours) {
                foreach ($event->shifts as $shift) {
                    $this->line('  '.$shift->name);
                    foreach ($shift->users as $user) {
                        $pivot = $user->pivot; // Access pivot data
                        
                        if ($pivot->hours_logged_at) {
                            continue; // already logged for this user/shift
                        }
    
                        $this->line('    '.$user->name);

                        $hoursToCredit = 0;
                        $description = '';
                        $notes = '';

                        if ($shift->double_hours) {
                            $hoursToCredit = round($shift->start_time->diffInMinutes($shift->end_time) / 60, 2) * 2;
                            $description = "[VOL][DBL] Volunteered {$shift->name} for {$event->name}";
                            $notes = "Auto-logged from shift participation. (Shift ID {$shift->id} on event ID {$event->id}) with double hours";
                        } else {
                            $hoursToCredit = round($shift->start_time->diffInMinutes($shift->end_time) / 60, 2);
                            $description = "[VOL] Volunteered {$shift->name} for {$event->name}";
                            $notes = "Auto-logged from shift participation. (Shift ID {$shift->id} on event ID {$event->id})";
                        }
    
                        VolunteerHours::create([
                            'user_id'   => $user->id,
                            'hours'     => $hoursToCredit,
                            'volunteer_date' => $shift->start_time->toDateString(),
                            'description' => $description,
                            'notes'     => $notes,
                            'primary_dept_id' => null,
                            'fiscal_ledger_id' => $fiscalLedger->id,
                        ]);
    
                        $shift->users()->updateExistingPivot($user->id, [
                            'hours_logged_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->info("Volunteer hours logged for shifts completed on $yesterday.");
    }
}

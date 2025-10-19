<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shift;
use App\Models\User;
use App\Mail\ShiftReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendShiftReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shifts:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to volunteers about their upcoming shifts today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting shift reminder emails...');

        // Get today's date range (from now until end of day)
        $today = Carbon::today();
        $endOfDay = Carbon::today()->endOfDay();

        // Find all shifts happening today that haven't been completed
        $shiftsToday = Shift::with(['event', 'users'])
            ->where('start_time', '>=', $today)
            ->where('start_time', '<=', $endOfDay)
            ->whereHas('users') // Only shifts with signups
            ->get();

        if ($shiftsToday->isEmpty()) {
            $this->info('No shifts scheduled for today.');
            return 0;
        }

        $this->info("Found {$shiftsToday->count()} shifts scheduled for today.");

        // Group shifts by user
        $userShifts = [];
        
        foreach ($shiftsToday as $shift) {
            foreach ($shift->users as $user) {
                $userId = $user->id;
                
                // Skip if user doesn't have email
                if (!$user->email) {
                    continue;
                }

                // Check if user has email reminders enabled
                if (!$user->email_shift_reminders) {
                    continue;
                }
                
                if (!isset($userShifts[$userId])) {
                    $userShifts[$userId] = [
                        'user' => $user,
                        'shifts' => collect(),
                    ];
                }
                
                $userShifts[$userId]['shifts']->push($shift);
            }
        }

        $emailsSent = 0;
        $emailsFailed = 0;

        // Send one email per user with all their shifts
        foreach ($userShifts as $userId => $data) {
            try {
                $user = $data['user'];
                $shifts = $data['shifts'];

                Mail::to($user->email)->send(new ShiftReminder($user, $shifts));
                
                $emailsSent++;
                $this->info("✓ Sent reminder to {$user->name} ({$user->email}) for {$shifts->count()} shift(s)");
            } catch (\Exception $e) {
                $emailsFailed++;
                $this->error("✗ Failed to send email to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info('');
        $this->info("Summary:");
        $this->info("- Emails sent: {$emailsSent}");
        $this->info("- Emails failed: {$emailsFailed}");
        $this->info("- Total shifts: {$shiftsToday->count()}");

        return 0;
    }
}

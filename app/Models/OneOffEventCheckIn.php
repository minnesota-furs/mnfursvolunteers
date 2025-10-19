<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneOffEventCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'one_off_event_id',
        'checked_in_at',
        'hours_credited',
    ];

    // setup casts
    protected $casts = [
        'checked_in_at' => 'datetime',
        'hours_credited' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(OneOffEvent::class, 'one_off_event_id');
    }

    public function volunteerHours()
    {
        return $this->hasOne(VolunteerHours::class, 'id', 'volunteer_hours_id');
    }

    /**
     * Credit volunteer hours for this check-in
     */
    public function creditHours(): ?VolunteerHours
    {
        // Don't credit if already credited
        if ($this->hours_credited) {
            return null;
        }

        $event = $this->event;
        $duration = $event->start_time->floatDiffInHours($event->end_time);

        // Get the current fiscal ledger
        $currentLedger = FiscalLedger::where('start_date', '<=', $event->start_time)
            ->where('end_date', '>=', $event->start_time)
            ->first();

        // Create volunteer hours record
        $volunteerHours = VolunteerHours::create([
            'user_id' => $this->user_id,
            'volunteer_date' => $event->start_time->toDateString(),
            'hours' => $duration,
            'description' => "Check-in: {$event->name}",
            'notes' => "Automatically credited from one-off event check-in",
            'fiscal_ledger_id' => $currentLedger?->id,
        ]);

        // Mark as credited
        $this->update(['hours_credited' => true]);

        return $volunteerHours;
    }
}

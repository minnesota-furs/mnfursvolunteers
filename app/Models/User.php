<?php

namespace App\Models;

use App\Models\Concerns\GeneratesVolCode;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, GeneratesVolCode;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'active',
        'wordpress_user_id',
        'primary_dept_id',
        'primary_sector_id',
        'is_linked_to_wp',
        'notes',
        'admin',
        'email_shift_reminders',
        'email_event_updates',
        'email_hour_approvals',
        'email_election_reminders',
        'hour_submission_token',
        'token_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'permissions'       => 'array',
        'email_shift_reminders' => 'boolean',
        'email_event_updates' => 'boolean',
        'email_hour_approvals' => 'boolean',
        'email_election_reminders' => 'boolean',
        'token_expires_at' => 'datetime',
    ];

    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHours::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'primary_dept_id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_user');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'primary_sector_id');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'shift_signups')->withTimestamps();
    }

    public function communicationLogs()
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function userNotes()
    {
        return $this->hasMany(Note::class);
    }

    public function createdNotes()
    {
        return $this->hasMany(Note::class, 'created_by');
    }

    public function shiftsForEvent($eventId)
    {
        return $this->shifts->filter(fn ($shift) => $shift->event_id == $eventId);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function performedAudits()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function totalVolunteerHours()
    {
        if (!$this->relationLoaded('volunteerHours')) {
            $this->load('volunteerHours');
        }

        return $this->volunteerHours->sum('hours');
    }

    public function totalVolunteerHoursForFiscalPeriod($fiscalLedgerId)
    {
        return $this->volunteerHours()
            ->where('fiscal_ledger_id', $fiscalLedgerId)
            ->sum('hours');
    }

    /**
     * Get the total volunteer hours for the current fiscal ledger period.
     *
     * @return float
     */
    public function totalHoursForCurrentFiscalLedger()
    {
        if (!$this->relationLoaded('volunteerHours')) {
            $this->load('volunteerHours');
        }
        // Get the current date
        $currentDate = now();

        // Find the current fiscal ledger based on the current date
        $currentFiscalLedger = FiscalLedger::where('start_date', '<=', $currentDate)
                                            ->where('end_date', '>=', $currentDate)
                                            ->first();

        if ($currentFiscalLedger) {
            // Sum the volunteer hours for the current fiscal ledger
            return $this->volunteerHours()
                        ->where('fiscal_ledger_id', $currentFiscalLedger->id)
                        ->sum('hours');
        }

        // Return 0 if no fiscal ledger is active for the current date
        return 0;
    }

    /**
     * Check if the volunteer entry has notes set.
     *
     * @return bool
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }

    /**   
     * Check if the volunteer entry has department set.
     *
     * @return bool
     */
    public function hasDept(): bool
    {
        return $this->departments()->exists();
    }
    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function givePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $this->permissions = array_values(array_diff($permissions, [$permission]));
        $this->save();
    }

    public function getVolCodeAttribute($value)
    {
        return strtoupper($value);
    }

    // Attribute to get if user is volunteer or staff based on having any departments or not
    public function getIsStaffAttribute(): bool
    {
        return $this->hasDept();
    }

    /**
     * Get volunteer hours for the current fiscal year (alias for totalHoursForCurrentFiscalLedger)
     *
     * @return float
     */
    public function getCurrentFiscalYearHours()
    {
        return $this->totalHoursForCurrentFiscalLedger();
    }

    /**
     * Get volunteer hours for a specific fiscal ledger period.
     *
     * @param int $fiscalLedgerId
     * @return float
     */
    public function getHoursForFiscalLedger($fiscalLedgerId)
    {
        return $this->totalVolunteerHoursForFiscalPeriod($fiscalLedgerId);
    }

    /**
     * Get timeline events for this user combining volunteer hours, shifts, and audit logs
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getTimelineEvents()
    {
        $events = collect();

        // Add volunteer hours entries
        if ($this->volunteerHours) {
            $this->volunteerHours->each(function ($hour) use ($events) {
                $events->push([
                    'type' => 'volunteer_hours',
                    'date' => $hour->volunteer_date ?? $hour->created_at,
                    'title' => 'Logged ' . format_hours($hour->hours) . ' hours',
                    'description' => $hour->description ?? 'Hour entry',
                    'department' => $hour->department->name ?? null,
                    'sector' => $hour->department->sector->name ?? null,
                    'model' => $hour,
                ]);
            });
        }

        // Add shift signups
        if ($this->shifts) {
            $this->shifts->each(function ($shift) use ($events) {
                $events->push([
                    'type' => 'shift_signup',
                    'date' => $shift->pivot->created_at ?? $shift->start_time,
                    'title' => 'Signed up for shift',
                    'description' => $shift->name ?? 'Unnamed shift',
                    'event_name' => $shift->event->name ?? null,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'model' => $shift,
                ]);
            });
        }

        // Add notes
        if ($this->userNotes) {
            $this->userNotes->each(function ($note) use ($events) {
                $events->push([
                    'type' => 'note',
                    'date' => $note->created_at,
                    'title' => $note->title ?: 'Note added',
                    'description' => \Illuminate\Support\Str::limit($note->content, 100),
                    'note_type' => $note->type,
                    'is_private' => $note->private,
                    'created_by' => $note->creator->name ?? 'Unknown',
                    'model' => $note,
                ]);
            });
        }

        // Add audit log entries
        if ($this->auditLogs) {
            $this->auditLogs->each(function ($log) use ($events) {
                $events->push([
                'type' => 'audit_log',
                'date' => $log->created_at,
                'title' => ucfirst($log->action ?? 'Activity'),
                'description' => $log->comment ?? 'User record ' . $log->action,
                'changes' => $log->changes,
                'performed_by' => $log->user->name ?? 'System',
                'model' => $log,
            ]);
            });
        }

        // Sort by date descending
        return $events->sortByDesc('date');
    }

    /**
     * Generate a unique UUID token for hour submission.
     * Token expires after 90 days by default.
     *
     * @param int $expirationDays
     * @return string
     */
    public function generateHourSubmissionToken($expirationDays = 90)
    {
        $this->hour_submission_token = \Illuminate\Support\Str::uuid()->toString();
        $this->token_expires_at = now()->addDays($expirationDays);
        $this->save();

        return $this->hour_submission_token;
    }

    /**
     * Check if the user has a valid hour submission token.
     *
     * @return bool
     */
    public function hasValidHourSubmissionToken()
    {
        return $this->hour_submission_token !== null 
            && $this->token_expires_at !== null 
            && $this->token_expires_at->isFuture();
    }

    /**
     * Clear the hour submission token.
     *
     * @return void
     */
    public function clearHourSubmissionToken()
    {
        $this->hour_submission_token = null;
        $this->token_expires_at = null;
        $this->save();
    }

    /**
     * Get the public hour submission URL.
     *
     * @return string|null
     */
    public function getHourSubmissionUrl()
    {
        if (!$this->hasValidHourSubmissionToken()) {
            return null;
        }

        return route('hours.public.show', ['token' => $this->hour_submission_token]);
    }
}

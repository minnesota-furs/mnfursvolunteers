<?php

namespace App\Models;

use App\Models\Concerns\GeneratesVolCode;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use GeneratesVolCode, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public const ACCESSIBILITY_NEEDS = [
        'Wheelchair accessible',
        'Limited standing/walking',
        'Seating available during assignments',
        'Cannot lift heavy objects',
        'Deaf',
        'Service animal',
        'Visually impaired',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'pronouns',
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
        'calendar_token',
        'telegram_chat_id',
        'telegram_username',
        'telegram_link_token',
        'telegram_link_token_expires_at',
        'telegram_linked_at',
        'timezone',
        'onboarded_at',
        'accessibility_needs',
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
        'password' => 'hashed',
        'permissions' => 'array',
        'email_shift_reminders' => 'boolean',
        'email_event_updates' => 'boolean',
        'email_hour_approvals' => 'boolean',
        'email_election_reminders' => 'boolean',
        'token_expires_at' => 'datetime',
        'calendar_token' => 'string',
        'telegram_link_token_expires_at' => 'datetime',
        'telegram_linked_at' => 'datetime',
        'onboarded_at' => 'datetime',
        'accessibility_needs' => 'array',
    ];

    /**
     * Get the user's display name based on the application setting.
     *
     * Returns the alias (name) or legal name (first + last) depending
     * on the 'user_display_name' application setting.
     */
    public function displayName(): string
    {
        $format = ApplicationSetting::get('user_display_name', 'alias');

        if ($format === 'legal_name') {
            $full = trim($this->first_name.' '.$this->last_name);

            return $full ?: $this->name;
        }

        return $this->name;
    }

    /**
     * Get the timezone this user views dates/times in, falling back to the
     * application's configured timezone if they haven't set one.
     */
    public function effectiveTimezone(): string
    {
        return $this->timezone ?: app_timezone();
    }

    /**
     * Whether this user still needs to go through the first-time onboarding wizard.
     */
    public function needsOnboarding(): bool
    {
        return is_null($this->onboarded_at);
    }

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
        return $this->belongsToMany(Department::class, 'department_user')->withTimestamps();
    }

    public function headDepartments()
    {
        return $this->belongsToMany(Department::class, 'department_head');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'primary_sector_id');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'shift_signups')
            ->withPivot(['hours_logged_at', 'no_show', 'no_show_marked_at'])
            ->withTimestamps();
    }

    public function oneOffEventRsvps()
    {
        return $this->hasMany(OneOffEventRsvp::class);
    }

    public function oneOffEventCheckIns()
    {
        return $this->hasMany(OneOffEventCheckIn::class);
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

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function customFieldValues()
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function recognitions()
    {
        return $this->hasMany(Recognition::class);
    }

    public function grantedRecognitions()
    {
        return $this->hasMany(Recognition::class, 'granted_by_user_id');
    }

    public function relationships()
    {
        return $this->hasMany(UserRelationship::class);
    }

    public function favoritedUsers()
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'user_id', 'target_user_id')
            ->wherePivot('type', 'favorite')
            ->withTimestamps();
    }

    public function avoidedUsers()
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'user_id', 'target_user_id')
            ->wherePivot('type', 'avoid')
            ->withTimestamps();
    }

    public function getRelationshipWith(int $userId): ?string
    {
        return $this->relationships()
            ->where('target_user_id', $userId)
            ->value('type');
    }

    public function getCustomFieldValue($fieldKey)
    {
        $customField = CustomField::where('field_key', $fieldKey)->first();
        if (! $customField) {
            return null;
        }

        $customFieldValue = $this->customFieldValues()
            ->where('custom_field_id', $customField->id)
            ->first();

        return $customFieldValue ? $customFieldValue->value : null;
    }

    public function totalVolunteerHours()
    {
        if (! $this->relationLoaded('volunteerHours')) {
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
        if (! $this->relationLoaded('volunteerHours')) {
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
     */
    public function hasNotes(): bool
    {
        return ! empty($this->notes);
    }

    /**
     * Check if the volunteer entry has department set.
     */
    public function hasDept(): bool
    {
        return $this->departments()->exists();
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function canViewEmailOf(User $user): bool
    {
        if ($this->isAdmin() || $this->is($user)) {
            return true;
        }

        return $this->headDepartments()
            ->whereHas('users', fn ($query) => $query->whereKey($user->getKey()))
            ->exists();
    }

    /**
     * Sanctum's HasApiTokens trait doesn't define this, but Passport's
     * scope-checking middleware (CheckScopes/CheckForAnyScope) requires it.
     * tokenCan() is already inherited from Sanctum and works for both:
     * it just delegates to $this->accessToken->can(), which both
     * Sanctum's PersonalAccessToken and Passport's Token implement.
     */
    public function token()
    {
        return $this->accessToken;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        // Check if permission exists by key (e.g., 'manage-users')
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check if permission exists by label from config (e.g., 'Manage Users')
        $permissionConfig = config('permissions.'.$permission);
        if ($permissionConfig && isset($permissionConfig['label'])) {
            return in_array($permissionConfig['label'], $permissions);
        }

        return false;
    }

    public function givePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (! in_array($permission, $permissions)) {
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

    /**
     * Generate (or regenerate) this user's iCal calendar subscription token.
     */
    public function generateCalendarToken(): string
    {
        $token = (string) Str::uuid();
        $this->calendar_token = $token;
        $this->save();

        return $token;
    }

    // Attribute to get if user is volunteer or staff based on having any departments or not
    public function getIsStaffAttribute(): bool
    {
        return $this->hasDept();
    }

    /**
     * Generate a fresh Telegram link token, valid for 15 minutes, and return the
     * t.me deep link the user should open (or scan as a QR code) to link their account.
     */
    public function generateTelegramLinkToken(): string
    {
        $token = (string) Str::uuid();
        $this->telegram_link_token = $token;
        $this->telegram_link_token_expires_at = now()->addMinutes(15);
        $this->save();

        return $token;
    }

    public function hasValidTelegramLinkToken(): bool
    {
        return $this->telegram_link_token !== null
            && $this->telegram_link_token_expires_at !== null
            && $this->telegram_link_token_expires_at->isFuture();
    }

    public function getTelegramLinkUrl(): ?string
    {
        $botUsername = ApplicationSetting::get('telegram_bot_username');

        if (! $botUsername || ! $this->hasValidTelegramLinkToken()) {
            return null;
        }

        return "https://t.me/{$botUsername}?start={$this->telegram_link_token}";
    }

    public function hasTelegramLinked(): bool
    {
        return ! empty($this->telegram_chat_id);
    }

    /**
     * Complete linking: attach the Telegram chat and clear the one-time token.
     */
    public function completeTelegramLink(string $chatId, ?string $username): void
    {
        $this->telegram_chat_id = $chatId;
        $this->telegram_username = $username;
        $this->telegram_linked_at = now();
        $this->telegram_link_token = null;
        $this->telegram_link_token_expires_at = null;
        $this->save();
    }

    public function unlinkTelegram(): void
    {
        $this->telegram_chat_id = null;
        $this->telegram_username = null;
        $this->telegram_linked_at = null;
        $this->telegram_link_token = null;
        $this->telegram_link_token_expires_at = null;
        $this->save();
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
     * @param  int  $fiscalLedgerId
     * @return float
     */
    public function getHoursForFiscalLedger($fiscalLedgerId)
    {
        return $this->totalVolunteerHoursForFiscalPeriod($fiscalLedgerId);
    }

    /**
     * Get timeline events for this user combining volunteer hours, shifts, and audit logs
     *
     * @return Collection
     */
    public function getTimelineEvents()
    {
        $events = collect();
        $canManageNotes = auth()->check() && auth()->user()->hasPermission('manage-user-notes');

        // Add volunteer hours entries
        if ($this->volunteerHours) {
            $this->volunteerHours->each(function ($hour) use ($events) {
                $events->push([
                    'type' => 'volunteer_hours',
                    'date' => $hour->volunteer_date ?? $hour->created_at,
                    'title' => 'Logged '.format_hours($hour->hours).' hours',
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

        // Add notes (only if user has permission)
        if ($canManageNotes && $this->userNotes) {
            $this->userNotes->each(function ($note) use ($events) {
                $events->push([
                    'type' => 'note',
                    'date' => $note->created_at,
                    'title' => $note->title ?: 'Note added',
                    'description' => Str::limit($note->content, 100),
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
                    'description' => $log->comment ?? 'User record '.$log->action,
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
     * @param  int  $expirationDays
     * @return string
     */
    public function generateHourSubmissionToken($expirationDays = 90)
    {
        $this->hour_submission_token = Str::uuid()->toString();
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
        if (! $this->hasValidHourSubmissionToken()) {
            return null;
        }

        return route('hours.public.show', ['token' => $this->hour_submission_token]);
    }
}

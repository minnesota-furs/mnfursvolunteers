<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
    ];

    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHours::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'primary_dept_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'primary_sector_id');
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
        if (!$this->relationLoaded('volunteerHours')) {
            $this->load('volunteerHours');
        }
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
        return !is_null($this->primary_dept_id);
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
}

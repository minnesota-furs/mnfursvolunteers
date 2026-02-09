<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Parsedown;

class JobListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'slug',
        'position_title',
        'visibility',
        'description',
        'number_of_openings',
        'closing_date',
    ];

    protected $appends = ['publicPermalink'];

    protected $casts = [
        'closing_date' => 'datetime'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($jobListing) {
            $jobListing->slug = static::generateUniqueSlug($jobListing->position_title);
        });

        static::updating(function ($jobListing) {
            // Only update slug if the title has changed
            if ($jobListing->isDirty('position_title')) {
                $jobListing->slug = static::generateUniqueSlug($jobListing->position_title);
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the applications for this job listing.
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Generate the full permalink for the job listing.
     */
    public function getPublicPermalinkAttribute()
    {
        return url('/openings/' . $this->id);
    }

    /**
     * Convert Markdown description to HTML when requested.
     */
    public function getHtmlDescriptionAttribute()
    {
        return Parsedown::instance()->text($this->description);
    }

    /**
     * Convert Markdown to plain text (strips all HTML).
     */
    public function getPlainTextDescriptionAttribute()
    {
        return strip_tags($this->parsedDescription);
    }

    /**
     * Generate a unique slug with a random 4-digit number.
     */
    protected static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title); // Convert to hyphenated, lowercase string
        $random = rand(1000, 9999); // Generate a 4-digit random number
        $finalSlug = "{$slug}-{$random}";

        // Ensure uniqueness
        while (static::where('slug', $finalSlug)->exists()) {
            $random = rand(1000, 9999);
            $finalSlug = "{$slug}-{$random}";
        }

        return $finalSlug;
    }
}

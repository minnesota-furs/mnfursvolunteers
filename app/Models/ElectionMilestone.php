<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;
use Parsedown;

class ElectionMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'title',
        'description',
        'location',
        'starts_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    /**
     * The description rendered from Markdown. Raw HTML in the source is
     * escaped, and single line breaks are kept.
     */
    public function descriptionHtml(): ?HtmlString
    {
        if (blank($this->description)) {
            return null;
        }

        $parser = (new Parsedown)->setSafeMode(true)->setBreaksEnabled(true);

        return new HtmlString($parser->text($this->description));
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }
}

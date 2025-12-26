<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'message',
        'recipient_email',
        'status',
        'metadata',
        'sent_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user who received the communication
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who sent the communication
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}

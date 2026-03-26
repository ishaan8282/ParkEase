<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInLog extends Model
{
    // Append-only table — no updated_at column
    const UPDATED_AT = null;

    protected $fillable = [
        'booking_id',
        'event_type',
        'method',
        'performed_by_user_id',
        'ip_address',
        'device_info',
        'is_successful',
        'failure_reason',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}

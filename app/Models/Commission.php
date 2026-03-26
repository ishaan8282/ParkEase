<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $fillable = [
        'booking_id',
        'owner_id',
        'booking_amount',
        'platform_fee',
        'owner_amount',
        'dev_amount',
        'owner_rate_pct',
        'dev_rate_pct',
        'owner_payout_status',
        'owner_paid_at',
        'owner_payout_ref',
    ];

    protected $casts = [
        'booking_amount' => 'decimal:2',
        'platform_fee'   => 'decimal:2',
        'owner_amount'   => 'decimal:2',
        'dev_amount'     => 'decimal:2',
        'owner_paid_at'  => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}

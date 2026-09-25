<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'payment_id',
        'cancellation_setting_id',
        'booking_amount',
        'refund_amount',
        'cancellation_fee',
        'owner_earnings',
        'dev_earnings',
        'gateway_refund_id',
        'status',
        'notes',
        'initiated_by',
        'processed_at',
    ];

    protected $casts = [
        'booking_amount'   => 'decimal:2',
        'refund_amount'    => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'owner_earnings'   => 'decimal:2',
        'dev_earnings'     => 'decimal:2',
        'processed_at'     => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function cancellationSetting(): BelongsTo
    {
        return $this->belongsTo(CancellationSetting::class);
    }
}

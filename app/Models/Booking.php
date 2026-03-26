<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_ref',
        'user_id',
        'parking_space_id',
        'parking_slot_id',
        'check_in',
        'check_out',
        'actual_check_in',
        'actual_check_out',
        'vehicle_number',
        'vehicle_type',
        'amount',
        'platform_fee',
        'total_amount',
        'refund_amount',
        'refunded_at',
        'owner_commission',
        'dev_commission',
        'status',
        'qr_code',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'check_in'         => 'datetime',
        'check_out'        => 'datetime',
        'actual_check_in'  => 'datetime',
        'actual_check_out' => 'datetime',
        'refunded_at'      => 'datetime',
        'cancelled_at'     => 'datetime',
        'amount'           => 'decimal:2',
        'platform_fee'     => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'refund_amount'    => 'decimal:2',
        'owner_commission' => 'decimal:2',
        'dev_commission'   => 'decimal:2',
    ];

    // ── Status constants ───────────────────────────────────────────────────────
    const STATUS_PENDING    = 'pending';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_NO_SHOW    = 'no_show';

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parkingSpace(): BelongsTo
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    public function parkingSlot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function checkInLogs(): HasMany
    {
        return $this->hasMany(CheckInLog::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function isCheckedIn(): bool
    {
        return $this->status === self::STATUS_CHECKED_IN;
    }

    /**
     * How many minutes have passed since this booking was created?
     * Used by the refund policy engine.
     */
    public function minutesSinceCreated(): int
    {
        return (int) $this->created_at->diffInMinutes(now());
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopeForSlotBetween($query, int $slotId, string $checkIn, string $checkOut)
    {
        return $query
            ->where('parking_slot_id', $slotId)
            ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_NO_SHOW])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            });
    }
}

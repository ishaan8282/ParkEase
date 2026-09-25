<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_space_id',
        'slot_number',
        'type',
        'status',
        'reserved_by_user_id',
        'reserved_until',
    ];

    protected $casts = [
        'reserved_until' => 'datetime',
    ];

    // ── Status constants ───────────────────────────────────────────────────────
    const STATUS_AVAILABLE   = 'available';
    const STATUS_RESERVED    = 'reserved';   // payment pending (5-min lock)
    const STATUS_BOOKED      = 'booked';     // payment confirmed
    const STATUS_OCCUPIED    = 'occupied';   // user checked in
    const STATUS_COMPLETED   = 'completed';  // user checked out
    const STATUS_BLOCKED     = 'blocked';
    const STATUS_MAINTENANCE = 'maintenance';

    // ── Relationships ──────────────────────────────────────────────────────────

    public function parkingSpace(): BelongsTo
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    public function reservedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by_user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(SlotStatusLog::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    /**
     * Check if a live reservation has expired.
     * The scheduler calls this, but you can also call it inline
     * before accepting a booking to handle race conditions.
     */
    public function reservationExpired(): bool
    {
        return $this->isReserved()
            && $this->reserved_until !== null
            && $this->reserved_until->isPast();
    }

    /**
     * Mark the slot as available and clear the reservation lock.
     * Always log the transition via SlotStatusLog.
     */
    public function release(?int $userId = null, string $source = 'system'): void
    {
        // Avoid duplicate release logs
        if ($this->status === self::STATUS_AVAILABLE) {
            return;
        }

        $old = $this->status;

        // Log first
        SlotStatusLog::create([
            'parking_slot_id'     => $this->id,
            'old_status'          => $old,
            'new_status'          => self::STATUS_AVAILABLE,
            'changed_by_user_id'  => $userId,
            'trigger_source'      => $source,
            'notes'               => "Slot released by {$source}",
        ]);

        // Update actual slot state
        $this->update([
            'status'              => self::STATUS_AVAILABLE,
            'reserved_by_user_id' => null,
            'reserved_until'      => null,
        ]);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeForVehicleType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithExpiredReservations($query)
    {
        return $query
            ->where('status', self::STATUS_RESERVED)
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<', now());
    }
}

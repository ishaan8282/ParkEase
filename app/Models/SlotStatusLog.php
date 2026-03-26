<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotStatusLog extends Model
{
    // Only "created_at" exists, no updated_at
    public $timestamps = false;

    protected $table = 'slot_status_logs';

    protected $fillable = [
        'parking_slot_id',
        'booking_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'trigger_source',
        'notes',
        'created_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parkingSlot()
    {
        return $this->belongsTo(ParkingSlot::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}

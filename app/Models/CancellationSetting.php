<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CancellationSetting extends Model
{
    protected $fillable = [
        'label',
        'window_from_minutes',
        'window_to_minutes',
        'refund_percentage',
        'owner_share_percentage',
        'dev_share_percentage',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'window_from_minutes'    => 'integer',
        'window_to_minutes'      => 'integer',
        'refund_percentage'      => 'integer',
        'owner_share_percentage' => 'integer',
        'dev_share_percentage'   => 'integer',
        'is_active'              => 'boolean',
    ];

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Find the applicable policy for a booking that is N minutes old.
     *
     * Usage: CancellationSetting::policyFor($booking->minutesSinceCreated())
     */
    public static function policyFor(int $minutesElapsed): ?self
    {
        return self::where('is_active', true)
            ->where('window_from_minutes', '<=', $minutesElapsed)
            ->where(function ($q) use ($minutesElapsed) {
                $q->whereNull('window_to_minutes')
                  ->orWhere('window_to_minutes', '>', $minutesElapsed);
            })
            ->orderBy('window_from_minutes', 'desc')
            ->first();
    }
}

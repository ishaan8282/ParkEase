<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\ParkingSlot;
use App\Models\SlotStatusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationService
{
    /**
     * How many minutes a slot stays locked while the user pays.
     */
    const RESERVATION_MINUTES = 5;

    /**
     * Platform fee percentage charged to the user on top of base amount.
     * Owner gets (100 - PLATFORM_FEE_PCT)% of the base amount.
     * Developer gets PLATFORM_FEE_PCT% of the base amount.
     */
    const PLATFORM_FEE_PCT = 5;

    // ──────────────────────────────────────────────────────────────────────────
    // STEP 1 — Reserve a slot (before payment)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Atomically lock a slot for a user for RESERVATION_MINUTES.
     * Returns the updated ParkingSlot or throws if unavailable.
     *
     * Uses a DB transaction + pessimistic lock (lockForUpdate) to prevent
     * two simultaneous requests from grabbing the same slot.
     */
    public function reserveSlot(ParkingSlot $slot, int $userId): ParkingSlot
    {
        // dd($slot, $userId);
        return DB::transaction(function () use ($slot, $userId) {

            // Re-fetch with lock so no other request can sneak in
            $slot = ParkingSlot::lockForUpdate()->findOrFail($slot->id);

            // Auto-release if a previous reservation expired
            if ($slot->reservationExpired()) {
                $slot->release(null, 'system');
                $slot->refresh();
            }

            if (!$slot->isAvailable()) {
                throw new \RuntimeException('Slot is no longer available.');
            }

            $old = $slot->status;

            $slot->update([
                'status'              => ParkingSlot::STATUS_RESERVED,
                'reserved_by_user_id' => $userId,
                'reserved_until'      => now()->addMinutes(self::RESERVATION_MINUTES),
            ]);

            SlotStatusLog::create([
                'parking_slot_id'    => $slot->id,
                'old_status'         => $old,
                'new_status'         => ParkingSlot::STATUS_RESERVED,
                'changed_by_user_id' => $userId,
                'trigger_source'     => 'user',
                'notes'              => "Reserved for " . self::RESERVATION_MINUTES . " min payment window",
            ]);

            return $slot->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STEP 2 — Confirm booking after successful payment
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Convert a temporary reservation into a confirmed booking.
     * Creates the Booking + Commission records and marks slot as 'booked'.
     *
     * @param  ParkingSlot $slot          The reserved slot
     * @param  int         $userId
     * @param  array       $data          Validated booking data from the controller
     * @param  string      $transactionId Gateway transaction ID
     * @return Booking
     */
    public function confirmBooking(
        ParkingSlot $slot,
        int         $userId,
        array       $data,
        string      $transactionId
    ): Booking {
        return DB::transaction(function () use ($slot, $userId, $data, $transactionId) {

            // Guard: slot must still be reserved by this user
            $slot = ParkingSlot::lockForUpdate()->findOrFail($slot->id);

            if ($slot->status !== ParkingSlot::STATUS_RESERVED) {
                throw new \RuntimeException('Reservation expired before payment completed.');
            }

            if ($slot->reserved_by_user_id !== $userId) {
                throw new \RuntimeException('Slot is reserved by another user.');
            }

            if ($slot->reservationExpired()) {
                $slot->release(null, 'system');
                throw new \RuntimeException('Your 5-minute reservation window has expired. Please start over.');
            }

            // ── Calculate amounts ───────────────────────────────────────────
            $checkIn   = new \DateTime($data['check_in']);
            $checkOut  = new \DateTime($data['check_out']);
            $hours     = max(1, ($checkOut->getTimestamp() - $checkIn->getTimestamp()) / 3600);

            $space        = $slot->parkingSpace;
            $baseAmount   = round($hours * $space->price_per_hour, 2);
            $platformFee  = round($baseAmount * (self::PLATFORM_FEE_PCT / 100), 2);
            $totalAmount  = $baseAmount + $platformFee;

            // Commission split: owner gets base, developer gets platform fee
            $ownerCommission = $baseAmount;
            $devCommission   = $platformFee;

            // ── Create Booking ──────────────────────────────────────────────
            $booking = Booking::create([
                'booking_ref'       => $this->generateBookingRef(),
                'user_id'           => $userId,
                'parking_space_id'  => $space->id,
                'parking_slot_id'   => $slot->id,
                'check_in'          => $data['check_in'],
                'check_out'         => $data['check_out'],
                'vehicle_type'      => $data['vehicle_type'],
                'vehicle_number'    => $data['vehicle_number'],
                'amount'            => $baseAmount,
                'platform_fee'      => $platformFee,
                'total_amount'      => $totalAmount,
                'owner_commission'  => $ownerCommission,
                'dev_commission'    => $devCommission,
                'status'            => Booking::STATUS_CONFIRMED,
                'qr_code'           => $this->generateQrToken(),
            ]);

            // ── Update slot to booked ───────────────────────────────────────
            $slot->update([
                'status'              => ParkingSlot::STATUS_BOOKED,
                'reserved_by_user_id' => $userId,  // Keep track of who booked
                'reserved_until'      => $data['check_out'],  // Store booking end time
            ]);

            SlotStatusLog::create([
                'parking_slot_id'    => $slot->id,
                'booking_id'         => $booking->id,
                'old_status'         => ParkingSlot::STATUS_RESERVED,
                'new_status'         => ParkingSlot::STATUS_BOOKED,
                'changed_by_user_id' => $userId,
                'trigger_source'     => 'payment_webhook',
            ]);

            // ── Create Commission record ────────────────────────────────────
            Commission::create([
                'booking_id'          => $booking->id,
                'owner_id'            => $space->owner_id,
                'booking_amount'      => $baseAmount,
                'platform_fee'        => $platformFee,
                'owner_amount'        => $ownerCommission,
                'dev_amount'          => $devCommission,
                'owner_rate_pct'      => 100 - self::PLATFORM_FEE_PCT,
                'dev_rate_pct'        => self::PLATFORM_FEE_PCT,
                'owner_payout_status' => 'pending',
            ]);

            return $booking->load(['parkingSpace', 'parkingSlot']);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STEP 3 — Release expired reservations (called by scheduler)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Find all slots whose reservation window has passed and release them.
     * Run every minute via the Laravel scheduler.
     *
     * Returns the count of slots released.
     */
    public function releaseExpiredReservations(): int
    {
        $expired = ParkingSlot::withExpiredReservations()->get();

        $count = 0;
        foreach ($expired as $slot) {
            try {
                $slot->release(null, 'scheduler');
                $count++;
                Log::info("Released expired reservation", ['slot_id' => $slot->id]);
            } catch (\Throwable $e) {
                Log::error("Failed to release slot", [
                    'slot_id' => $slot->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function generateBookingRef(): string
    {
        do {
            $ref = 'BK-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Booking::where('booking_ref', $ref)->exists());

        return $ref;
    }

    /**
     * Generates a secure token stored in bookings.qr_code.
     * The actual QR image is rendered on the frontend from this token.
     */
    private function generateQrToken(): string
    {
        return 'QR-' . bin2hex(random_bytes(16));
    }
}

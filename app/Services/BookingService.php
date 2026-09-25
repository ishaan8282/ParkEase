<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CheckInLog;
use App\Models\Commission;
use App\Models\ParkingSlot;
use App\Models\SlotStatusLog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingService
{
    public function createWalkIn(ParkingSlot $slot, int $ownerId, array $data, ?string $ipAddress = null, ?string $userAgent = null): Booking
    {
        return DB::transaction(function () use ($slot, $ownerId, $data, $ipAddress, $userAgent) {
            $slot = ParkingSlot::with('parkingSpace')
                ->lockForUpdate()
                ->findOrFail($slot->id);

            if ($slot->parkingSpace->owner_id !== $ownerId) {
                throw new RuntimeException('This parking slot does not belong to your space.');
            }

            if ($slot->reservationExpired()) {
                $slot->release(null, 'system');
                $slot->refresh();
            }

            if (!$slot->isAvailable()) {
                throw new RuntimeException('This slot is no longer available.');
            }

            $checkIn = now();
            $durationHours = max(1, (int) $data['duration_hours']);
            $checkOut = $checkIn->copy()->addHours($durationHours);
            $baseAmount = round($durationHours * $slot->parkingSpace->price_per_hour, 2);
            $platformFee = round($baseAmount * (ReservationService::PLATFORM_FEE_PCT / 100), 2);
            $totalAmount = $baseAmount + $platformFee;
            $paidAt = in_array($data['payment_status'], Booking::paymentStatuses(), true)
                ? $checkIn
                : null;

            $booking = Booking::create([
                'booking_ref' => $this->generateBookingRef(),
                'user_id' => null,
                'parking_space_id' => $slot->parking_space_id,
                'parking_slot_id' => $slot->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'actual_check_in' => $checkIn,
                'vehicle_number' => $data['vehicle_number'],
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $baseAmount,
                'platform_fee' => $platformFee,
                'total_amount' => $totalAmount,
                'owner_commission' => $baseAmount,
                'dev_commission' => $platformFee,
                'status' => Booking::STATUS_CHECKED_IN,
                'qr_code' => $this->generateQrToken(),
                'is_walk_in' => true,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'payment_status' => $data['payment_status'],
                'paid_at' => $paidAt,
                'notes' => $data['notes'] ?? null,
            ]);

            $slot->update([
                'status' => ParkingSlot::STATUS_OCCUPIED,
                'reserved_by_user_id' => null,
                'reserved_until' => $checkOut,
            ]);

            SlotStatusLog::create([
                'parking_slot_id' => $slot->id,
                'booking_id' => $booking->id,
                'old_status' => ParkingSlot::STATUS_AVAILABLE,
                'new_status' => ParkingSlot::STATUS_OCCUPIED,
                'changed_by_user_id' => $ownerId,
                'trigger_source' => 'owner',
                'notes' => 'Walk-in customer checked in at the entry gate',
            ]);

            CheckInLog::create([
                'booking_id' => $booking->id,
                'event_type' => 'check_in',
                'method' => 'manual',
                'performed_by_user_id' => $ownerId,
                'ip_address' => $ipAddress,
                'device_info' => $userAgent,
                'is_successful' => true,
            ]);

            Commission::create([
                'booking_id' => $booking->id,
                'owner_id' => $ownerId,
                'booking_amount' => $baseAmount,
                'platform_fee' => $platformFee,
                'owner_amount' => $baseAmount,
                'dev_amount' => $platformFee,
                'owner_rate_pct' => 100 - ReservationService::PLATFORM_FEE_PCT,
                'dev_rate_pct' => ReservationService::PLATFORM_FEE_PCT,
                'owner_payout_status' => 'pending',
            ]);

            return $booking->fresh(['parkingSpace', 'parkingSlot']);
        });
    }

    public function completeWalkIn(Booking $booking, int $ownerId, ?string $ipAddress = null, ?string $userAgent = null): Booking
    {
        return DB::transaction(function () use ($booking, $ownerId, $ipAddress, $userAgent) {
            $booking = Booking::with(['parkingSpace', 'parkingSlot', 'commission', 'payment'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if (!$booking->is_walk_in) {
                throw new RuntimeException('This is not a walk-in booking.');
            }

            if ($booking->status !== Booking::STATUS_CHECKED_IN) {
                throw new RuntimeException('This walk-in booking is not currently checked in.');
            }

            $slot = ParkingSlot::lockForUpdate()->findOrFail($booking->parking_slot_id);
            $actualCheckOut = now();
            $durationHours = max(1, ($actualCheckOut->timestamp - $booking->check_in->timestamp) / 3600);
            $baseAmount = round($durationHours * $booking->parkingSpace->price_per_hour, 2);
            $platformFee = round($baseAmount * (ReservationService::PLATFORM_FEE_PCT / 100), 2);
            $totalAmount = $baseAmount + $platformFee;

            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
                'actual_check_out' => $actualCheckOut,
                'amount' => $baseAmount,
                'platform_fee' => $platformFee,
                'total_amount' => $totalAmount,
                'owner_commission' => $baseAmount,
                'dev_commission' => $platformFee,
            ]);

            $slot->release($ownerId, 'owner');

            if ($booking->commission) {
                $booking->commission->update([
                    'booking_amount' => $baseAmount,
                    'platform_fee' => $platformFee,
                    'owner_amount' => $baseAmount,
                    'dev_amount' => $platformFee,
                ]);
            }

            if ($booking->payment && in_array($booking->payment->status, ['pending', 'success'], true)) {
                $booking->payment->update(['amount' => $totalAmount]);
            }

            CheckInLog::create([
                'booking_id' => $booking->id,
                'event_type' => 'check_out',
                'method' => 'manual',
                'performed_by_user_id' => $ownerId,
                'ip_address' => $ipAddress,
                'device_info' => $userAgent,
                'is_successful' => true,
            ]);

            return $booking->fresh(['parkingSpace', 'parkingSlot', 'commission', 'payment']);
        });
    }

    public function cancel(Booking $booking, int $ownerId, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $ownerId, $reason) {
            $booking = Booking::with('parkingSlot')->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status === Booking::STATUS_CANCELLED) {
                throw new RuntimeException('This booking is already cancelled.');
            }

            if ($booking->status === Booking::STATUS_COMPLETED) {
                throw new RuntimeException('A completed booking cannot be cancelled.');
            }

            $wasCheckedIn = $booking->status === Booking::STATUS_CHECKED_IN;
            $cancelledAt = now();

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_by' => 'owner',
                'cancelled_at' => $cancelledAt,
                'actual_check_out' => $wasCheckedIn ? $cancelledAt : $booking->actual_check_out,
                'refund_amount' => $booking->is_walk_in
                    && in_array($booking->payment_status, [Booking::PAYMENT_PAID, Booking::PAYMENT_CASH], true)
                    ? $booking->total_amount
                    : $booking->refund_amount,
                'refunded_at' => $booking->is_walk_in
                    && in_array($booking->payment_status, [Booking::PAYMENT_PAID, Booking::PAYMENT_CASH], true)
                    ? $cancelledAt
                    : $booking->refunded_at,
            ]);

            $slot = ParkingSlot::lockForUpdate()->findOrFail($booking->parking_slot_id);
            $slot->release($ownerId, 'owner');

            if ($wasCheckedIn) {
                CheckInLog::create([
                    'booking_id' => $booking->id,
                    'event_type' => 'check_out',
                    'method' => 'manual',
                    'performed_by_user_id' => $ownerId,
                    'is_successful' => true,
                    'failure_reason' => 'Booking cancelled by owner',
                ]);
            }

            return $booking->fresh(['parkingSpace', 'parkingSlot']);
        });
    }

    public static function paymentStatuses(): array
    {
        return [
            Booking::PAYMENT_PAID,
            Booking::PAYMENT_CASH,
            Booking::PAYMENT_WAIVED,
        ];
    }

    public function generateBookingRef(): string
    {
        do {
            $ref = 'WK-' . strtoupper(bin2hex(random_bytes(4)));
        } while (Booking::where('booking_ref', $ref)->exists());

        return $ref;
    }

    private function generateQrToken(): string
    {
        return 'QR-' . bin2hex(random_bytes(16));
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ParkingSlot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredBookings extends Command
{
    protected $signature = 'bookings:release-expired';
    protected $description = 'Release parking slots for bookings whose check_out time has passed';

    public function handle(): int
    {
        // Find confirmed or checked_in bookings where check_out time has passed
        $expiredBookings = Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
            ->where('check_out', '<', now())
            ->with('parkingSlot')
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('No expired bookings found.');
            return Command::SUCCESS;
        }

        $count = 0;
        $failedCount = 0;

        foreach ($expiredBookings as $booking) {
            try {
                // Mark booking as completed
                $booking->update([
                    'status' => Booking::STATUS_COMPLETED,
                ]);

                // Release the slot
                if ($booking->parkingSlot) {
                    $booking->parkingSlot->update([
                        'status' => ParkingSlot::STATUS_AVAILABLE,
                        'reserved_by_user_id' => null,
                        'reserved_until' => null,
                    ]);

                    $count++;
                    $this->info("Released booking {$booking->booking_ref} - slot {$booking->parkingSlot->slot_number} is now available");

                    Log::info('Released expired booking', [
                        'booking_id' => $booking->id,
                        'booking_ref' => $booking->booking_ref,
                        'slot_id' => $booking->parking_slot_id,
                        'check_out' => $booking->check_out,
                    ]);
                } else {
                    $this->warn("Booking {$booking->booking_ref} has no associated parking slot");
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("Failed to release booking {$booking->booking_ref}: {$e->getMessage()}");

                Log::error('Failed to release expired booking', [
                    'booking_id' => $booking->id,
                    'booking_ref' => $booking->booking_ref,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Released {$count} expired booking(s).");
        if ($failedCount > 0) {
            $this->error("Failed to release {$failedCount} booking(s).");
        }

        return Command::SUCCESS;
    }
}

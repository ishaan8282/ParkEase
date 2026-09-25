<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ParkingSlot;
use Illuminate\Console\Command;

class ReleaseAllExpiredBookingsNow extends Command
{
    protected $signature = 'bookings:release-all-expired-now';
    protected $description = 'Manually release all expired bookings and free up parking slots (run this once to fix current issues)';

    public function handle(): int
    {
        $this->info('Starting to release all expired bookings...');

        // Find all confirmed or checked_in bookings where check_out time has passed
        $expiredBookings = Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
            ->where('check_out', '<', now())
            ->with('parkingSlot')
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('No expired bookings found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredBookings->count()} expired booking(s) to release.");

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

                    $this->info("✓ Released booking {$booking->booking_ref} - Slot {$booking->parkingSlot->slot_number} is now available");
                } else {
                    $this->warn("⚠ Booking {$booking->booking_ref} has no associated parking slot");
                }

                $count++;
            } catch (\Throwable $e) {
                $this->error("✗ Failed to release booking {$booking->booking_ref}: {$e->getMessage()}");
                $failedCount++;
            }
        }

        $this->info('');
        $this->info("Summary:");
        $this->info("  - Successfully released: {$count} booking(s)");
        if ($failedCount > 0) {
            $this->error("  - Failed to release: {$failedCount} booking(s)");
        }

        return Command::SUCCESS;
    }
}

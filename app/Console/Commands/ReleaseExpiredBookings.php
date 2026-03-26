<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ParkingSlot;
use Illuminate\Console\Command;

class ReleaseExpiredBookings extends Command
{
    protected $signature = 'bookings:release-expired';
    protected $description = 'Release parking slots for bookings whose check_out time has passed';

    public function handle(): int
    {
        // Find confirmed bookings where check_out time has passed
        $expiredBookings = Booking::where('status', Booking::STATUS_CONFIRMED)
            ->where('check_out', '<', now())
            ->get();

        $count = 0;
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
                }

                $count++;
                $this->info("Released booking {$booking->booking_ref} - slot is now available");
            } catch (\Throwable $e) {
                $this->error("Failed to release booking {$booking->id}: {$e->getMessage()}");
            }
        }

        $this->info("Released {$count} expired booking(s).");
        return Command::SUCCESS;
    }
}

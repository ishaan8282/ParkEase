<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature   = 'reservations:release-expired';
    protected $description = 'Auto-release parking slots whose 5-minute payment window has expired';

    public function handle(ReservationService $service): int
    {
        $count = $service->releaseExpiredReservations();

        $this->info("Released {$count} expired reservation(s).");

        return Command::SUCCESS;
    }
}

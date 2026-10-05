<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Expire overdue parking reservations and release their spots';

    public function handle(ReservationService $reservationService): int
    {
        $count = $reservationService->expireDueReservations();

        $this->info(
            "Expired {$count} reservation(s)."
        );

        return self::SUCCESS;
    }
}
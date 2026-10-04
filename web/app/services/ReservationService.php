<?php

namespace App\Services;

use App\Enums\SpotStatus;
use App\Models\Reservation;
use App\Models\Site;
use App\Models\Spot;
use App\Models\SpotState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservationService
{
    public function create(
        User $user,
        Site $site,
        int $spotId,
    ): Reservation {
        return DB::transaction(function () use ($user, $site, $spotId, ) {
            $spot = Spot::query()
                ->whereKey($spotId)
                ->where('is_active', true)
                ->where('is_bookable', true)
                ->whereHas(
                    'zone',
                    fn($query) => $query->where('site_id', $site->id)
                )
                ->with('zone')
                ->lockForUpdate()
                ->firstOrFail();

            $spotState = SpotState::query()
                ->where('spot_id', $spot->id)
                ->lockForUpdate()
                ->first();

            if (!$spotState || $spotState->status !== SpotStatus::Free) {
                throw new RuntimeException(
                    'This parking spot is not currently available.',
                );
            }

            $existingReservation = Reservation::query()
                ->where('spot_id', $spot->id)
                ->whereIn('status', ['held', 'confirmed'])
                ->where('expires_at', '>', now())
                ->exists();

            if ($existingReservation) {
                throw new RuntimeException(
                    'This parking spot is already reserved.',
                );
            }

            $reservation = Reservation::create([
                'site_id' => $site->id,
                'zone_id' => $spot->zone_id,
                'spot_id' => $spot->id,
                'user_id' => $user->id,
                'vehicle_id' => null,
                'payment_id' => null,
                'session_id' => null,
                'starts_at' => now(),
                'expires_at' => now()->addMinutes(15),
                'status' => 'held',
                'amount_minor' => 0,
            ]);

            $spotState->update([
                'status' => SpotStatus::Reserved,
                'reservation_id' => $reservation->id,
                'source' => 'manual',
                'last_changed_at' => now(),
            ]);

            return $reservation->fresh([
                'site',
                'zone',
                'spot',
            ]);
        });
    }
}
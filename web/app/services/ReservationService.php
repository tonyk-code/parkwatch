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

    public function cancel(
        User $user,
        int $reservationId,
    ): Reservation {
        return DB::transaction(function () use ($user, $reservationId, ) {
            $reservation = Reservation::query()
                ->whereKey($reservationId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($reservation->status, ['held', 'confirmed'], true)) {
                throw new RuntimeException(
                    'This reservation can no longer be cancelled.',
                );
            }

            $spotState = SpotState::query()
                ->where('spot_id', $reservation->spot_id)
                ->lockForUpdate()
                ->first();

            $reservation->update([
                'status' => 'cancelled',
            ]);

            if (
                $spotState &&
                $spotState->reservation_id === $reservation->id
            ) {
                $spotState->update([
                    'status' => SpotStatus::Free,
                    'reservation_id' => null,
                    'source' => 'manual',
                    'last_changed_at' => now(),
                ]);
            }

            return $reservation->fresh([
                'site',
                'zone',
                'spot',
            ]);
        });
    }

    public function expireDueReservations(): int
    {
        $expiredCount = 0;

        Reservation::query()
            ->whereIn('status', ['held', 'confirmed'])
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($reservations) use (&$expiredCount) {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation, &$expiredCount, ) {
                        $lockedReservation = Reservation::query()
                            ->whereKey($reservation->id)
                            ->lockForUpdate()
                            ->first();

                        if (!$lockedReservation) {
                            return;
                        }

                        if (
                            !in_array(
                                $lockedReservation->status,
                                ['held', 'confirmed'],
                                true,
                            ) ||
                            $lockedReservation->expires_at->isFuture()
                        ) {
                            return;
                        }

                        $spotState = SpotState::query()
                            ->where('spot_id', $lockedReservation->spot_id)
                            ->lockForUpdate()
                            ->first();

                        $lockedReservation->update([
                            'status' => 'expired',
                        ]);

                        if (
                            $spotState &&
                            $spotState->reservation_id === $lockedReservation->id
                        ) {
                            $spotState->update([
                                'status' => SpotStatus::Free,
                                'reservation_id' => null,
                                'source' => 'manual',
                                'last_changed_at' => now(),
                            ]);
                        }

                        $expiredCount++;
                    });
                }
            });

        return $expiredCount;
    }
}
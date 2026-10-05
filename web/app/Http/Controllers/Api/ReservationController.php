<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reservations = $request->user()
            ->reservations()
            ->with([
                'site:id,name,code',
                'zone:id,name,code',
                'spot:id,code,spot_type',
            ])
            ->latest('id')
            ->get();

        return response()->json([
            'reservations' => $reservations->map(
                fn($reservation) => [
                    'id' => $reservation->id,
                    'status' => $reservation->status,
                    'starts_at' => $reservation->starts_at,
                    'expires_at' => $reservation->expires_at,
                    'amount' => $reservation->amount_minor,
                    'site' => $reservation->site
                        ? [
                            'id' => $reservation->site->id,
                            'name' => $reservation->site->name,
                            'code' => $reservation->site->code,
                        ]
                        : null,
                    'zone' => $reservation->zone
                        ? [
                            'id' => $reservation->zone->id,
                            'name' => $reservation->zone->name,
                            'code' => $reservation->zone->code,
                        ]
                        : null,
                    'spot' => $reservation->spot
                        ? [
                            'id' => $reservation->spot->id,
                            'code' => $reservation->spot->code,
                            'spot_type' => $reservation->spot->spot_type,
                        ]
                        : null,
                ],
            )->values(),
        ]);
    }

    public function store(
        Request $request,
        ReservationService $reservationService,
    ): JsonResponse {
        $validated = $request->validate([
            'site_id' => ['required', 'integer'],
            'spot_id' => ['required', 'integer'],
        ]);

        $site = Site::query()
            ->whereKey($validated['site_id'])
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $reservation = $reservationService->create(
                user: $request->user(),
                site: $site,
                spotId: $validated['spot_id'],
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'reservation' => [
                'id' => $reservation->id,
                'site_id' => $reservation->site_id,
                'zone_id' => $reservation->zone_id,
                'spot_id' => $reservation->spot_id,
                'status' => $reservation->status,
                'starts_at' => $reservation->starts_at,
                'expires_at' => $reservation->expires_at,
                'amount' => $reservation->amount_minor,
            ],
        ], 201);
    }

    public function cancel(
        Request $request,
        int $reservation,
        ReservationService $reservationService,
    ): JsonResponse {
        try {
            $cancelledReservation = $reservationService->cancel(
                user: $request->user(),
                reservationId: $reservation,
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'reservation' => [
                'id' => $cancelledReservation->id,
                'status' => $cancelledReservation->status,
                'spot_id' => $cancelledReservation->spot_id,
                'starts_at' => $cancelledReservation->starts_at,
                'expires_at' => $cancelledReservation->expires_at,
            ],
        ]);
    }
}
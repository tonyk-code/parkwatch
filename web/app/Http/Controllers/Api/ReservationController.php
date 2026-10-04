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
}
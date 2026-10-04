<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sessions = $request->user()
            ->parkingSessions()
            ->with([
                'site:id,name,code',
                'spot:id,zone_id,code',
                'zone:id,site_id,name,code',
            ])
            ->latest('id')
            ->get([
                'id',
                'site_id',
                'zone_id',
                'spot_id',
                'reference_code',
                'entered_at',
                'exited_at',
                'status',
                'session_mode',
                'amount_due',
                'amount_paid',
                'currency',
            ]);

        return response()->json([
            'sessions' => $sessions->map(fn($session) => [
                'id' => $session->id,
                'reference_code' => $session->reference_code,
                'status' => $session->status->value,
                'session_mode' => $session->session_mode->value,
                'entered_at' => $session->entered_at,
                'exited_at' => $session->exited_at,
                'amount_due' => (int) $session->amount_due,
                'amount_paid' => (int) $session->amount_paid,
                'remaining' => max(
                    0,
                    (int) $session->amount_due
                    - (int) $session->amount_paid,
                ),
                'currency' => $session->currency,
                'site' => [
                    'id' => $session->site->id,
                    'name' => $session->site->name,
                    'code' => $session->site->code,
                ],
                'zone' => $session->zone ? [
                    'id' => $session->zone->id,
                    'name' => $session->zone->name,
                    'code' => $session->zone->code,
                ] : null,
                'spot' => $session->spot ? [
                    'id' => $session->spot->id,
                    'code' => $session->spot->code,
                ] : null,
            ]),
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Enums\SpotStatus;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\UserType;

class LiveParkingController extends Controller
{
    public function __invoke(Request $request, Site $site): Response
    {
        $user = $request->user();

        $sites = $user->accessibleSitesQuery()
            ->select([
                'id',
                'name',
                'code',
                'address',
                'total_capacity',
            ])
            ->orderBy('name')
            ->get();

        $site->load([
            'zones' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('display_order')
                    ->with([
                        'spots' => function ($query) {
                            $query
                                ->where('is_active', true)
                                ->orderBy('code')
                                ->with('spotState');
                        },
                    ]);
            },
        ]);

        $spots = $site->zones
            ->flatMap(fn($zone) => $zone->spots);

        $occupied = $spots->filter(
            fn($spot) =>
                $spot->spotState?->status === SpotStatus::Occupied
        )->count();

        $reserved = $spots->filter(
            fn($spot) =>
                $spot->spotState?->status === SpotStatus::Reserved
        )->count();

        $offline = $spots->filter(
            fn($spot) =>
                $spot->spotState?->status === SpotStatus::Offline
        )->count();

        $available = max(
            $site->total_capacity - $occupied - $reserved,
            0
        );

        $occupancy = $site->total_capacity > 0
            ? round(($occupied / $site->total_capacity) * 100)
            : 0;

        return Inertia::render('live/index', [
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'code' => $site->code,
                'address' => $site->address,
                'total_capacity' => $site->total_capacity,
            ],

            'availableSites' => $sites,

            'canOverride' => in_array(
                $user->user_type,
                [
                    UserType::Owner,
                    UserType::Staff,
                ],
                true
            ),

            'stats' => [
                'occupied' => $occupied,
                'available' => $available,
                'reserved' => $reserved,
                'offline' => $offline,
                'occupancy' => $occupancy,
            ],

            'zones' => $site->zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'code' => $zone->code,
                    'floor_label' => $zone->floor_label,
                    'capacity' => $zone->capacity,
                    'spots' => $zone->spots->map(function ($spot) {
                        return [
                            'id' => $spot->id,
                            'code' => $spot->code,
                            'spot_type' => $spot->spot_type,
                            'status' => $spot->spotState?->status?->value
                                ?? SpotStatus::Free->value,
                            'confidence' => $spot->spotState?->confidence,
                            'manual_override' => $spot->spotState?->hasActiveOverride() ?? false,
                            'last_changed_at' => $spot->spotState?->last_changed_at,
                            'last_seen_at' => $spot->spotState?->last_seen_at,
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }
}
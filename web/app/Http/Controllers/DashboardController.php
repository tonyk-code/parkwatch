<?php

namespace App\Http\Controllers;

use App\Enums\SessionStatus;
use App\Enums\SpotStatus;
use App\Enums\UserType;
use App\Models\ParkingSession;
use App\Models\Site;
use App\Models\SpotState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $siteQuery = Site::query()
            ->where('organization_id', $user->organization_id)
            ->where('is_active', true);

        if ($user->user_type === UserType::Staff) {
            $siteQuery->whereHas(
                'staffAssignments',
                function (Builder $query) use ($user) {
                    $query
                        ->where('user_id', $user->id)
                        ->where('is_active', true)
                        ->where('starts_at', '<=', now())
                        ->where(function (Builder $query) {
                            $query
                                ->whereNull('ends_at')
                                ->orWhere('ends_at', '>=', now());
                        });
                }
            );
        }

        $siteIds = (clone $siteQuery)->pluck('id');

        $occupiedSpots = SpotState::query()
            ->whereHas(
                'spot.zone',
                fn(Builder $query) => $query->whereIn('site_id', $siteIds)
            )
            ->where('status', SpotStatus::Occupied->value)
            ->count();

        $activeSessions = ParkingSession::query()
            ->whereIn('site_id', $siteIds)
            ->where('status', SessionStatus::Active->value)
            ->count();

        $sites = (clone $siteQuery)
            ->select([
                'id',
                'name',
                'code',
                'address',
                'total_capacity',
            ])
            ->withCount('zones')
            ->orderBy('name')
            ->limit(6)
            ->get();

        return Inertia::render('dashboard', [
            'scope' => $user->user_type === UserType::Owner
                ? 'organization'
                : 'assigned',

            'stats' => [
                'sites' => $siteIds->count(),
                'capacity' => (clone $siteQuery)->sum('total_capacity'),
                'occupied' => $occupiedSpots,
                'active_sessions' => $activeSessions,
            ],

            'sites' => $sites,
        ]);
    }
}
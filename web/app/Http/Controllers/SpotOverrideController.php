<?php

namespace App\Http\Controllers;

use App\Enums\SpotStatus;
use App\Models\Site;
use App\Models\Spot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SpotOverrideController extends Controller
{
    use AuthorizesRequests;
    public function __invoke(
        Request $request,
        Site $site,
        Spot $spot
    ): RedirectResponse {
        $this->authorize('override', $spot);

        if ($spot->zone?->site_id !== $site->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:free,occupied,reserved,offline'],
            'reason' => ['required', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $overrideUntil = isset($validated['duration_minutes'])
            ? now()->addMinutes((int) $validated['duration_minutes'])
            : null;

        $spotState = $spot->spotState()->firstOrCreate(
            ['spot_id' => $spot->id],
            [
                'status' => SpotStatus::Free,
                'source' => 'cv',
                'manual_override' => false,
                'polygon_version' => $spot->polygon_version,
                'last_changed_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        $spotState->update([
            'status' => SpotStatus::from($validated['status']),
            'manual_override' => true,
            'override_by' => $request->user()->id,
            'override_reason' => $validated['reason'],
            'override_until' => $overrideUntil,
            'last_changed_at' => now(),
        ]);

        return back();
    }
}
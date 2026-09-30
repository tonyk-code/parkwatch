<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\RatePlan;
use App\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RateManagementController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Site $site): Response
    {
        $this->authorize('update', $site);

        $currentPlan = $site->ratePlans()
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query
                    ->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->with('rateRules')
            ->orderBy('priority')
            ->first();

        $rateHistory = $site->ratePlans()
            ->with('rateRules')
            ->orderByDesc('valid_from')
            ->get();

        return Inertia::render('rates/index', [
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'code' => $site->code,
            ],
            'currentPlan' => $currentPlan,
            'rateHistory' => $rateHistory,
        ]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $validated = $request->validate([
            'hourly_rate' => [
                'required',
                'string',
                'regex:/^\d+(?:\.\d{1,2})?$/',
            ],
            'grace_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:1440',
            ],
            'daily_max' => [
                'nullable',
                'string',
                'regex:/^\d+(?:\.\d{1,2})?$/',
            ],
        ]);

        $hourlyRateMinor = $this->toMinorUnits(
            $validated['hourly_rate'],
        );

        $dailyMaxMinor = $validated['daily_max'] !== null
            ? $this->toMinorUnits($validated['daily_max'])
            : null;

        if (
            $dailyMaxMinor !== null &&
            $dailyMaxMinor < $hourlyRateMinor
        ) {
            return back()->withErrors([
                'daily_max' =>
                    'The daily maximum must be at least the hourly rate.',
            ]);
        }

        $effectiveAt = CarbonImmutable::now('UTC');

        DB::transaction(function () use ($request, $site, $validated, $hourlyRateMinor, $dailyMaxMinor, $effectiveAt, ) {
            $currentPlan = $site->ratePlans()
                ->where('is_active', true)
                ->where('valid_from', '<=', $effectiveAt)
                ->where(function ($query) use ($effectiveAt) {
                    $query
                        ->whereNull('valid_to')
                        ->orWhere('valid_to', '>=', $effectiveAt);
                })
                ->with('rateRules')
                ->orderBy('priority')
                ->lockForUpdate()
                ->first();

            if (!$currentPlan) {
                abort(
                    422,
                    'No active rate plan is configured for this site.',
                );
            }

            $oldRule = $currentPlan->rateRules
                ->sortBy('sequence')
                ->first();

            $newPlan = RatePlan::create([
                'site_id' => $site->id,
                'name' => $currentPlan->name,
                'currency' => $currentPlan->currency,
                'grace_minutes' => (int) $validated['grace_minutes'],
                'daily_max_minor' => $dailyMaxMinor,
                'rounding' => $currentPlan->rounding,
                'rounding_increment_minutes' =>
                    $currentPlan->rounding_increment_minutes,
                'priority' => $currentPlan->priority,
                'valid_from' => $effectiveAt,
                'valid_to' => null,
                'is_active' => true,
            ]);

            $newPlan->rateRules()->create([
                'sequence' => 1,
                'from_minute' => 0,
                'to_minute' => null,
                'unit' => 'hour',
                'price_minor' => $hourlyRateMinor,
                'day_of_week_mask' => $oldRule?->day_of_week_mask ?? 127,
                'time_from' => $oldRule?->time_from,
                'time_to' => $oldRule?->time_to,
                'vehicle_type' => $oldRule?->vehicle_type,
                'spot_type' => $oldRule?->spot_type,
            ]);

            $currentPlan->update([
                'valid_to' => $effectiveAt->subMicrosecond(),
                'is_active' => false,
            ]);

            AuditLog::create([
                'organization_id' => $site->organization_id,
                'user_id' => $request->user()->id,
                'action' => 'rate_plan.changed',
                'auditable_type' => RatePlan::class,
                'auditable_id' => $newPlan->id,
                'old_values' => [
                    'rate_plan_id' => $currentPlan->id,
                    'hourly_rate_minor' => $oldRule?->price_minor,
                    'grace_minutes' => $currentPlan->grace_minutes,
                    'daily_max_minor' => $currentPlan->daily_max_minor,
                ],
                'new_values' => [
                    'rate_plan_id' => $newPlan->id,
                    'hourly_rate_minor' => $hourlyRateMinor,
                    'grace_minutes' => (int) $validated['grace_minutes'],
                    'daily_max_minor' => $dailyMaxMinor,
                    'effective_at' => $effectiveAt->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => $effectiveAt,
            ]);
        });

        return to_route('sites.rates', $site)->with(
            'success',
            'Parking rate updated successfully.',
        );
    }

    private function toMinorUnits(string $amount): int
    {
        $parts = explode('.', $amount, 2);

        $whole = $parts[0];
        $fraction = $parts[1] ?? '';

        $fraction = str_pad($fraction, 2, '0');

        return ((int) $whole * 100)
            + (int) substr($fraction, 0, 2);
    }
}
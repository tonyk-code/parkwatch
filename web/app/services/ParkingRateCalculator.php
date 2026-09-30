<?php

namespace App\Services;

use App\Models\ParkingSession;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class ParkingRateCalculator
{
    public function resolveRatePlan(
        Site $site,
        CarbonImmutable $at,
    ): RatePlan {
        $plan = $site->ratePlans()
            ->where("is_active", true)
            ->where("valid_from", "<=", $at)
            ->where(function ($query) use ($at) {
                $query
                    ->whereNull("valid_to")
                    ->orWhere("valid_to", ">=", $at);
            })
            ->orderBy("priority")
            ->first();

        if (!$plan) {
            throw ValidationException::withMessages([
                "billing" => "No active rate plan is configured for this site.",
            ]);
        }

        return $plan;
    }

    public function calculate(
        ParkingSession $session,
        ?CarbonImmutable $endedAt = null,
    ): array {
        $session->loadMissing([
            "site",
            "ratePlan",
            "spot",
            "vehicle",
        ]);

        $enteredAt = $session->entered_at?->toImmutable();

        if (!$enteredAt) {
            throw ValidationException::withMessages([
                "billing" => "The parking session has no entry time.",
            ]);
        }

        $endedAt ??= CarbonImmutable::now("UTC");

        if ($endedAt->lessThan($enteredAt)) {
            throw ValidationException::withMessages([
                "billing" => "The exit time cannot be before the entry time.",
            ]);
        }

        $ratePlan = $session->ratePlan
            ?? $this->resolveRatePlan(
                $session->site,
                $enteredAt,
            );

        $durationMinutes = (int) ceil(
            $enteredAt->diffInSeconds($endedAt) / 60
        );

        $billableMinutes = max(
            0,
            $durationMinutes - (int) $ratePlan->grace_minutes
        );

        if ($billableMinutes === 0) {
            return [
                "rate_plan_id" => $ratePlan->id,
                "currency" => $ratePlan->currency,
                "duration_minutes" => $durationMinutes,
                "billable_minutes" => 0,
                "rounded_minutes" => 0,
                "amount_minor" => 0,
                "breakdown" => [],
            ];
        }

        $roundedMinutes = $this->roundMinutes(
            $billableMinutes,
            (int) $ratePlan->rounding_increment_minutes,
            $ratePlan->rounding,
        );

        $rules = $ratePlan->rateRules()
            ->orderBy("sequence")
            ->get();

        $amountMinor = 0;
        $coveredMinutes = 0;
        $breakdown = [];

        foreach ($rules as $rule) {
            if (!$this->matchesRule($rule, $session, $enteredAt)) {
                continue;
            }

            $fromMinute = max(0, (int) $rule->from_minute);

            if ($roundedMinutes <= $fromMinute) {
                continue;
            }

            $toMinute = $rule->to_minute === null
                ? $roundedMinutes
                : (int) $rule->to_minute;

            $segmentEnd = min(
                $roundedMinutes,
                $toMinute,
            );

            if ($segmentEnd <= $fromMinute) {
                continue;
            }

            $segmentMinutes = $segmentEnd - $fromMinute;

            $units = $this->calculateUnits(
                $segmentMinutes,
                $rule->unit,
            );

            $lineAmount = $units * (int) $rule->price_minor;

            $amountMinor += $lineAmount;
            $coveredMinutes = max(
                $coveredMinutes,
                $segmentEnd
            );

            $breakdown[] = [
                "sequence" => $rule->sequence,
                "from_minute" => $fromMinute,
                "to_minute" => $rule->to_minute,
                "unit" => $rule->unit,
                "units" => $units,
                "price_minor" => (int) $rule->price_minor,
                "amount_minor" => $lineAmount,
            ];
        }

        if ($coveredMinutes < $roundedMinutes) {
            throw ValidationException::withMessages([
                "billing" =>
                    "The active rate plan does not contain a rate rule for the full parking duration.",
            ]);
        }

        if ($ratePlan->daily_max_minor !== null) {
            $amountMinor = min(
                $amountMinor,
                (int) $ratePlan->daily_max_minor,
            );
        }

        return [
            "rate_plan_id" => $ratePlan->id,
            "currency" => $ratePlan->currency,
            "duration_minutes" => $durationMinutes,
            "billable_minutes" => $billableMinutes,
            "rounded_minutes" => $roundedMinutes,
            "amount_minor" => $amountMinor,
            "breakdown" => $breakdown,
        ];
    }

    private function roundMinutes(
        int $minutes,
        int $increment,
        string $rounding,
    ): int {
        if ($minutes <= 0 || $increment <= 0) {
            return $minutes;
        }

        return match ($rounding) {
            "down" => intdiv($minutes, $increment) * $increment,

            "nearest" => (int) (
                round($minutes / $increment) * $increment
            ),

            default => (int) (
                ceil($minutes / $increment) * $increment
            ),
        };
    }

    private function calculateUnits(
        int $minutes,
        string $unit,
    ): int {
        return match ($unit) {
            "minute", "minutes" => $minutes,

            "hour", "hours" => (int) ceil($minutes / 60),

            "day", "days" => (int) ceil($minutes / 1440),

            "flat", "fixed" => 1,

            default => throw ValidationException::withMessages([
                "billing" => "Unsupported rate rule unit: {$unit}.",
            ]),
        };
    }

    private function matchesRule(
        RateRule $rule,
        ParkingSession $session,
        CarbonImmutable $enteredAt,
    ): bool {
        if (
            $rule->vehicle_type !== null
            && $session->vehicle?->vehicle_type !== $rule->vehicle_type
        ) {
            return false;
        }

        if (
            $rule->spot_type !== null
            && $session->spot?->spot_type !== $rule->spot_type
        ) {
            return false;
        }

        $siteTimezone = $session->site->timezone ?: "UTC";

        $localTime = $enteredAt->setTimezone($siteTimezone);

        $dayBit = 1 << $localTime->dayOfWeek;

        if (
            ((int) $rule->day_of_week_mask & $dayBit) === 0
        ) {
            return false;
        }

        if (
            $rule->time_from === null
            && $rule->time_to === null
        ) {
            return true;
        }

        $currentTime = $localTime->format("H:i:s");

        $from = $rule->time_from
            ? substr($rule->time_from, 0, 8)
            : null;

        $to = $rule->time_to
            ? substr($rule->time_to, 0, 8)
            : null;

        if ($from !== null && $to !== null) {
            if ($from <= $to) {
                return $currentTime >= $from
                    && $currentTime <= $to;
            }

            return $currentTime >= $from
                || $currentTime <= $to;
        }

        if ($from !== null) {
            return $currentTime >= $from;
        }

        return $currentTime <= $to;
    }
}
<?php

namespace App\Http\Controllers;

use App\Enums\SessionMode;
use App\Enums\SessionStatus;
use App\Enums\SpotStatus;
use App\Models\AuditLog;
use App\Models\ParkingSession;
use App\Models\Site;
use App\Models\Spot;
use App\Services\ParkingRateCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ParkingSessionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Site $site): Response
    {
        $this->authorize("view", $site);

        $user = $request->user();

        $sessions = ParkingSession::query()
            ->where("site_id", $site->id)
            ->with([
                "spot:id,zone_id,code",
                "zone:id,name,code",
            ])
            ->latest("entered_at")
            ->limit(50)
            ->get([
                "id",
                "site_id",
                "zone_id",
                "spot_id",
                "reference_code",
                "entered_at",
                "exited_at",
                "status",
                "session_mode",
                "amount_due",
                "amount_paid",
                "currency",
            ]);

        $availableSpots = Spot::query()
            ->whereHas(
                "zone",
                fn(Builder $query) =>
                    $query->where("site_id", $site->id)
            )
            ->where("is_active", true)
            ->where("is_bookable", true)
            ->where(function (Builder $query) {
                $query->whereHas(
                    "spotState",
                    fn(Builder $query) =>
                        $query->where(
                            "status",
                            SpotStatus::Free->value
                        )
                )->orWhereDoesntHave("spotState");
            })
            ->with("zone:id,name,code")
            ->orderBy("code")
            ->get([
                "id",
                "zone_id",
                "code",
            ]);

        $sites = $user
            ->accessibleSitesQuery()
            ->select([
                "id",
                "name",
                "code",
            ])
            ->orderBy("name")
            ->get();

        return Inertia::render("sessions/index", [
            "site" => [
                "id" => $site->id,
                "name" => $site->name,
                "code" => $site->code,
                "address" => $site->address,
            ],

            "sites" => $sites,

            "sessions" => $sessions->map(
                function (ParkingSession $session) {
                    return [
                        "id" => $session->id,
                        "reference_code" => $session->reference_code,
                        "status" => $session->status->value,
                        "session_mode" => $session->session_mode->value,
                        "entered_at" => $session->entered_at?->toISOString(),
                        "exited_at" => $session->exited_at?->toISOString(),
                        "amount_due" => $session->amount_due,
                        "amount_paid" => $session->amount_paid,
                        "currency" => $session->currency,
                        "spot" => $session->spot
                            ? [
                                "code" => $session->spot->code,
                            ]
                            : null,
                        "zone" => $session->zone
                            ? [
                                "name" => $session->zone->name,
                                "code" => $session->zone->code,
                            ]
                            : null,
                    ];
                }
            )->values(),

            "availableSpots" => $availableSpots->map(
                function (Spot $spot) {
                    return [
                        "id" => $spot->id,
                        "code" => $spot->code,
                        "zone" => [
                            "name" => $spot->zone->name,
                            "code" => $spot->zone->code,
                        ],
                    ];
                }
            )->values(),
        ]);
    }

    public function store(
        Request $request,
        Site $site,
        ParkingRateCalculator $rateCalculator,
    ): RedirectResponse {
        $this->authorize("create", [
            ParkingSession::class,
            $site,
        ]);

        if ($site->session_mode !== SessionMode::Spot->value) {
            abort(
                422,
                "Manual spot sessions are not available for this site mode."
            );
        }

        $validated = $request->validate([
            "spot_id" => [
                "required",
                "integer",
                "exists:spots,id",
            ],
        ]);

        $user = $request->user();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        $ratePlan = $rateCalculator->resolveRatePlan(
            $site,
            now()->toImmutable(),
        );

        DB::transaction(function () use ($site, $validated, $user, $ipAddress, $userAgent, $ratePlan) {
            /** @var Spot $spot */
            $spot = Spot::query()
                ->where("id", $validated["spot_id"])
                ->whereHas(
                    "zone",
                    fn(Builder $query) =>
                        $query->where("site_id", $site->id)
                )
                ->with("zone")
                ->lockForUpdate()
                ->firstOrFail();

            $spotState = $spot->spotState()
                ->lockForUpdate()
                ->first();

            if (
                $spotState &&
                $spotState->status !== SpotStatus::Free
            ) {
                abort(
                    422,
                    "This parking spot is no longer available."
                );
            }

            $referenceCode = $this->generateReferenceCode();

            $session = ParkingSession::create([
                "site_id" => $site->id,
                "zone_id" => $spot->zone_id,
                "spot_id" => $spot->id,
                "vehicle_id" => null,
                "user_id" => null,
                "rate_plan_id" => $ratePlan->id,
                "permit_id" => null,
                "entry_gate_id" => null,
                "exit_gate_id" => null,
                "reference_code" => $referenceCode,
                "entered_at" => now(),
                "exited_at" => null,
                "status" => SessionStatus::Active,
                "session_mode" => SessionMode::Spot,
                "amount_due" => 0,
                "amount_paid" => 0,
                "amount_waived" => 0,
                "currency" => $ratePlan->currency,
                "entry_plate_read" => null,
                "exit_plate_read" => null,
                "entry_media_id" => null,
                "exit_media_id" => null,
                "opened_by" => $user->id,
                "closed_by" => null,
                "closed_reason" => null,
                "notes" => null,
            ]);

            if (!$spotState) {
                $spot->spotState()->create([
                    "status" => SpotStatus::Occupied,
                    "session_id" => $session->id,
                    "camera_id" => $spot->camera_id,
                    "source" => "manual",
                    "manual_override" => false,
                    "override_by" => null,
                    "override_reason" => null,
                    "override_until" => null,
                    "polygon_version" => $spot->polygon_version,
                    "last_changed_at" => now(),
                    "last_seen_at" => now(),
                ]);
            } else {
                $spotState->update([
                    "status" => SpotStatus::Occupied,
                    "session_id" => $session->id,
                    "source" => "manual",
                    "last_changed_at" => now(),
                    "last_seen_at" => now(),
                ]);
            }

            $session->events()->create([
                "event_type" => "session.opened",
                "from_status" => null,
                "to_status" => SessionStatus::Active->value,
                "payload" => [
                    "spot_id" => $spot->id,
                    "spot_code" => $spot->code,
                    "rate_plan_id" => $ratePlan->id,
                ],
                "actor_type" => get_class($user),
                "actor_id" => $user->id,
                "occurred_at" => now(),
            ]);

            AuditLog::create([
                "organization_id" => $user->organization_id,
                "user_id" => $user->id,
                "action" => "parking_session.opened",
                "auditable_type" => ParkingSession::class,
                "auditable_id" => $session->id,
                "old_values" => null,
                "new_values" => [
                    "reference_code" => $session->reference_code,
                    "site_id" => $site->id,
                    "spot_id" => $spot->id,
                    "status" => SessionStatus::Active->value,
                    "rate_plan_id" => $ratePlan->id,
                    "currency" => $ratePlan->currency,
                ],
                "ip_address" => $ipAddress,
                "user_agent" => $userAgent,
                "created_at" => now(),
            ]);
        });

        return back();
    }

    public function close(
        Request $request,
        Site $site,
        ParkingSession $session,
        ParkingRateCalculator $rateCalculator,
    ): RedirectResponse {
        if ($session->site_id !== $site->id) {
            abort(404);
        }

        $this->authorize("close", $session);

        $validated = $request->validate([
            "reason" => [
                "nullable",
                "string",
                "max:500",
            ],
        ]);

        $user = $request->user();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        DB::transaction(function () use ($session, $validated, $user, $ipAddress, $userAgent, $rateCalculator) {
            /** @var ParkingSession $lockedSession */
            $lockedSession = ParkingSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status !== SessionStatus::Active) {
                abort(
                    422,
                    "This parking session is no longer active."
                );
            }

            $lockedSession->load([
                "site",
                "ratePlan",
                "spot",
            ]);

            $spotState = $lockedSession->spot
                ? $lockedSession->spot
                    ->spotState()
                    ->lockForUpdate()
                    ->first()
                : null;

            $fromStatus = $lockedSession->status->value;
            $exitTime = now()->toImmutable();

            $calculation = $rateCalculator->calculate(
                $lockedSession,
                $exitTime,
            );

            $amountDue = max(
                0,
                (int) $calculation["amount_minor"]
                - (int) $lockedSession->amount_waived
            );

            $newStatus = $amountDue > 0
                ? SessionStatus::AwaitingPayment
                : SessionStatus::Completed;

            $lockedSession->update([
                "status" => $newStatus,
                "exited_at" => $exitTime,
                "amount_due" => $amountDue,
                "currency" => $calculation["currency"],
                "closed_by" => $user->id,
                "closed_reason" => $validated["reason"] ?? null,
                "rate_plan_id" => $calculation["rate_plan_id"],
            ]);

            if ($spotState) {
                $spotState->update([
                    "status" => SpotStatus::Free,
                    "session_id" => null,
                    "source" => "manual",
                    "manual_override" => false,
                    "override_by" => null,
                    "override_reason" => null,
                    "override_until" => null,
                    "last_changed_at" => $exitTime,
                    "last_seen_at" => $exitTime,
                ]);
            }

            $lockedSession->events()->create([
                "event_type" => "session.closed",
                "from_status" => $fromStatus,
                "to_status" => $newStatus->value,
                "payload" => [
                    "reason" => $validated["reason"] ?? null,
                    "spot_id" => $lockedSession->spot_id,
                    "rate_plan_id" => $calculation["rate_plan_id"],
                    "currency" => $calculation["currency"],
                    "duration_minutes" => $calculation["duration_minutes"],
                    "billable_minutes" => $calculation["billable_minutes"],
                    "rounded_minutes" => $calculation["rounded_minutes"],
                    "amount_minor" => $amountDue,
                    "breakdown" => $calculation["breakdown"],
                ],
                "actor_type" => get_class($user),
                "actor_id" => $user->id,
                "occurred_at" => $exitTime,
            ]);

            AuditLog::create([
                "organization_id" => $user->organization_id,
                "user_id" => $user->id,
                "action" => "parking_session.closed",
                "auditable_type" => ParkingSession::class,
                "auditable_id" => $lockedSession->id,
                "old_values" => [
                    "status" => $fromStatus,
                ],
                "new_values" => [
                    "status" => $newStatus->value,
                    "exited_at" => $lockedSession->exited_at?->toISOString(),
                    "amount_due" => $amountDue,
                    "currency" => $calculation["currency"],
                    "rate_plan_id" => $calculation["rate_plan_id"],
                    "closed_by" => $user->id,
                    "closed_reason" => $validated["reason"] ?? null,
                ],
                "ip_address" => $ipAddress,
                "user_agent" => $userAgent,
                "created_at" => now(),
            ]);
        });

        return back();
    }

    private function generateReferenceCode(): string
    {
        do {
            $referenceCode = "PW-" . Str::upper(
                Str::random(8)
            );
        } while (
            ParkingSession::query()
                ->where("reference_code", $referenceCode)
                ->exists()
        );

        return $referenceCode;
    }
}
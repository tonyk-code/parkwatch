import {
    Head,
    Link,
    router,
    useForm,
    usePage,
} from "@inertiajs/react";
import type { FormEvent } from "react";
import { useState } from "react";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout";
import type { Auth } from "@/types/auth";

type AvailableSite = {
    id: number;
    name: string;
    code: string;
    address: string | null;
    total_capacity: number;
};

type SpotStatus = "free" | "occupied" | "reserved" | "offline";

type Spot = {
    id: number;
    code: string;
    spot_type: string;
    status: SpotStatus;
    confidence: number | null;
    manual_override: boolean;
    last_changed_at: string | null;
    last_seen_at: string | null;
};

type Zone = {
    id: number;
    name: string;
    code: string;
    floor_label: string | null;
    capacity: number;
    spots: Spot[];
};

type LivePageProps = {
    auth: Auth;
    site: {
        id: number;
        name: string;
        code: string;
        address: string | null;
        total_capacity: number;
    };
    availableSites: AvailableSite[];
    canOverride: boolean;
    stats: {
        occupied: number;
        available: number;
        reserved: number;
        offline: number;
        occupancy: number;
    };
    zones: Zone[];
};

type OverrideForm = {
    status: SpotStatus;
    reason: string;
    duration_minutes: string;
};

const statusStyles: Record<
    SpotStatus,
    {
        label: string;
        className: string;
        dotClassName: string;
    }
> = {
    free: {
        label: "Free",
        className:
            "border-status-green-bg bg-status-green-bg text-status-green",
        dotClassName: "bg-status-green",
    },
    occupied: {
        label: "Occupied",
        className:
            "border-status-blue-bg bg-status-blue-bg text-status-blue",
        dotClassName: "bg-status-blue",
    },
    reserved: {
        label: "Reserved",
        className:
            "border-status-purple-bg bg-status-purple-bg text-status-purple",
        dotClassName: "bg-status-purple",
    },
    offline: {
        label: "Offline",
        className:
            "border-status-rose-bg bg-status-rose-bg text-status-rose",
        dotClassName: "bg-status-rose",
    },
};

export default function LiveParking() {
    const { site, availableSites, stats, zones, canOverride } =
        usePage<LivePageProps>().props;

    const [selectedSpot, setSelectedSpot] = useState<Spot | null>(null);

    const form = useForm<OverrideForm>({
        status: "free",
        reason: "",
        duration_minutes: "30",
    });

    function refresh() {
        router.reload({
            only: ["site", "availableSites", "stats", "zones"],
        });
    }

    function openOverride(spot: Spot) {
        if (!canOverride) {
            return;
        }

        setSelectedSpot(spot);

        form.setData({
            status: spot.status,
            reason: "",
            duration_minutes: "30",
        });

        form.clearErrors();
    }

    function closeOverride() {
        if (form.processing) {
            return;
        }

        setSelectedSpot(null);
        form.clearErrors();
    }

    function submitOverride(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!selectedSpot) {
            return;
        }

        form.post(
            `/sites/${site.id}/spots/${selectedSpot.id}/override`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedSpot(null);
                    form.reset();
                },
            },
        );
    }

    const statCards = [
        {
            label: "Capacity",
            value: site.total_capacity,
            hint: "total spaces",
            tone: "bg-status-amber-bg",
        },
        {
            label: "Available",
            value: stats.available,
            hint: "ready now",
            tone: "bg-status-green-bg",
        },
        {
            label: "Occupied",
            value: stats.occupied,
            hint: `${stats.occupancy}% occupancy`,
            tone: "bg-status-blue-bg",
        },
        {
            label: "Reserved",
            value: stats.reserved,
            hint: "held spaces",
            tone: "bg-status-purple-bg",
        },
    ];

    return (
        <>
            <Head title={`Live Parking — ${site.name}`} />

            <AuthenticatedLayout
                breadcrumbs={["ParkWatch", site.name, "Live parking"]}
            >
                <div className="grid h-full xl:grid-cols-[minmax(0,1fr)_20rem]">
                    <section className="min-w-0 xl:border-r xl:border-border-default">
                        <div className="flex flex-col gap-5 px-2 pb-5 pt-6 sm:px-3 lg:flex-row lg:items-end lg:justify-between">
                            <div className="min-w-0">
                                <div className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-pill bg-status-green" />

                                    <p className="text-xs font-semibold uppercase text-text-muted">
                                        Live parking
                                    </p>
                                </div>

                                <h1 className="mt-2 truncate text-xl font-semibold text-text-primary">
                                    {site.name}
                                </h1>

                                <p className="mt-1 truncate text-sm text-text-secondary">
                                    {site.address || "No address configured"}
                                </p>
                            </div>

                            <div className="flex flex-wrap items-center gap-2">
                                {availableSites.map((availableSite) => (
                                    <Link
                                        key={availableSite.id}
                                        href={`/sites/${availableSite.id}/live`}
                                        className={[
                                            "rounded-pill px-3 py-1.5 text-xs font-medium transition",
                                            availableSite.id === site.id
                                                ? "bg-action-dark text-action-dark-foreground"
                                                : "bg-bg-tag text-text-secondary hover:text-text-primary",
                                        ].join(" ")}
                                    >
                                        {availableSite.name}
                                    </Link>
                                ))}
                            </div>
                        </div>

                        <div className="grid gap-3 border-y border-border-default px-2 py-4 sm:grid-cols-2 sm:px-3 2xl:grid-cols-4">
                            {statCards.map((card) => (
                                <div
                                    key={card.label}
                                    className={`rounded-card p-4 ${card.tone}`}
                                >
                                    <p className="text-sm font-medium text-text-primary">
                                        {card.label}
                                    </p>

                                    <p className="mt-3 text-3xl font-semibold text-text-primary">
                                        {card.value}
                                    </p>

                                    <p className="mt-1 text-xs text-text-secondary">
                                        {card.hint}
                                    </p>
                                </div>
                            ))}
                        </div>

                        <div className="flex items-center justify-between px-3 py-4">
                            <div>
                                <h2 className="text-base font-semibold text-text-primary">
                                    Parking map
                                </h2>

                                <p className="mt-0.5 text-xs text-text-secondary">
                                    Space status by zone
                                </p>
                            </div>

                            <span className="rounded-pill bg-bg-tag px-3 py-1 text-xs font-medium text-text-tag">
                                {zones.length}{" "}
                                {zones.length === 1 ? "zone" : "zones"}
                            </span>
                        </div>

                        <div className="space-y-2 border-t border-border-default p-2 sm:p-3">
                            {zones.length === 0 ? (
                                <div className="px-3 py-8 text-sm text-text-muted">
                                    No parking zones are configured for this
                                    site.
                                </div>
                            ) : (
                                zones.map((zone) => (
                                    <section
                                        key={zone.id}
                                        className="rounded-card bg-bg-subtle p-4 sm:p-5"
                                    >
                                        <div className="flex items-center justify-between gap-4">
                                            <div className="min-w-0">
                                                <div className="flex items-center gap-2">
                                                    <span className="rounded-pill bg-bg-surface px-2 py-0.5 text-[10px] font-semibold uppercase text-text-secondary">
                                                        {zone.code}
                                                    </span>

                                                    {zone.floor_label && (
                                                        <span className="truncate text-xs text-text-muted">
                                                            {zone.floor_label}
                                                        </span>
                                                    )}
                                                </div>

                                                <h3 className="mt-2 truncate text-sm font-semibold text-text-primary">
                                                    {zone.name}
                                                </h3>
                                            </div>

                                            <div className="shrink-0 text-right">
                                                <p className="text-sm font-semibold text-text-primary">
                                                    {zone.spots.length}/
                                                    {zone.capacity}
                                                </p>

                                                <p className="text-[10px] text-text-muted">
                                                    spaces mapped
                                                </p>
                                            </div>
                                        </div>

                                        <div className="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 2xl:grid-cols-8">
                                            {zone.spots.map((spot) => {
                                                const config =
                                                    statusStyles[spot.status];

                                                return (
                                                    <button
                                                        key={spot.id}
                                                        type="button"
                                                        onClick={() =>
                                                            openOverride(spot)
                                                        }
                                                        disabled={!canOverride}
                                                        className={[
                                                            "min-h-28 rounded-card border p-3 text-left",
                                                            config.className,
                                                            canOverride
                                                                ? "cursor-pointer transition hover:shadow-nav-active"
                                                                : "cursor-default",
                                                        ].join(" ")}
                                                    >
                                                        <div className="flex items-center justify-between gap-2">
                                                            <span className="truncate text-sm font-semibold">
                                                                {spot.code}
                                                            </span>

                                                            <span
                                                                className={`h-2 w-2 shrink-0 rounded-pill ${config.dotClassName}`}
                                                            />
                                                        </div>

                                                        <p className="mt-1 truncate text-[10px] capitalize opacity-70">
                                                            {spot.spot_type}
                                                        </p>

                                                        <p className="mt-4 text-[10px] font-semibold uppercase opacity-80">
                                                            {config.label}
                                                        </p>

                                                        {spot.manual_override && (
                                                            <p className="mt-1 text-[10px] font-semibold">
                                                                Manual override
                                                            </p>
                                                        )}

                                                        {spot.confidence !==
                                                            null && (
                                                            <p className="mt-1 text-[10px] opacity-70">
                                                                {Math.round(
                                                                    spot.confidence *
                                                                        100,
                                                                )}
                                                                % confidence
                                                            </p>
                                                        )}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </section>
                                ))
                            )}
                        </div>
                    </section>

                    <aside className="min-w-0 border-t border-border-default xl:border-t-0">
                        <div className="px-4 pb-5 pt-6">
                            <div className="flex items-center justify-between gap-3">
                                <h2 className="text-lg font-semibold text-text-primary">
                                    Occupancy
                                </h2>

                                <button
                                    type="button"
                                    onClick={refresh}
                                    aria-label="Refresh live parking data"
                                    title="Refresh live parking data"
                                    className="grid h-9 w-9 place-items-center rounded-icon border border-border-default bg-bg-surface text-text-primary transition hover:bg-bg-subtle"
                                >
                                    <svg
                                        className="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="1.8"
                                    >
                                        <path d="M20 7v5h-5" />
                                        <path d="M4 17v-5h5" />
                                        <path d="M6.1 9a7 7 0 0 1 11.5-2.4L20 9" />
                                        <path d="M17.9 15a7 7 0 0 1-11.5 2.4L4 15" />
                                    </svg>
                                </button>
                            </div>

                            <div className="mt-5 flex items-end justify-between">
                                <div>
                                    <p className="text-4xl font-semibold text-text-primary">
                                        {stats.occupancy}%
                                    </p>

                                    <p className="mt-1 text-sm text-text-secondary">
                                        currently occupied
                                    </p>
                                </div>

                                <span className="grid h-14 w-14 place-items-center rounded-pill bg-action-dark text-sm font-semibold text-action-dark-foreground">
                                    {stats.available}
                                </span>
                            </div>

                            <div className="mt-6 h-2 overflow-hidden rounded-pill bg-bg-tag">
                                <div
                                    className="h-full rounded-pill bg-action-dark transition-all"
                                    style={{
                                        width: `${Math.min(
                                            stats.occupancy,
                                            100,
                                        )}%`,
                                    }}
                                />
                            </div>

                            <p className="mt-2 text-right text-[10px] text-text-muted">
                                {stats.available} spaces available
                            </p>
                        </div>

                        <div className="border-t border-border-default px-2 pb-4 pt-5">
                            <h3 className="px-2 text-base font-semibold text-text-primary">
                                Status overview
                            </h3>

                            <div className="mt-4 space-y-2">
                                {[
                                    {
                                        status: "free" as const,
                                        value: stats.available,
                                        text: "Ready for arrivals",
                                    },
                                    {
                                        status: "occupied" as const,
                                        value: stats.occupied,
                                        text: "Vehicles parked",
                                    },
                                    {
                                        status: "reserved" as const,
                                        value: stats.reserved,
                                        text: "Held spaces",
                                    },
                                    {
                                        status: "offline" as const,
                                        value: stats.offline,
                                        text: "Needs attention",
                                    },
                                ].map((item) => {
                                    const config =
                                        statusStyles[item.status];

                                    return (
                                        <div
                                            key={item.status}
                                            className={`rounded-card p-3 ${config.className}`}
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <p className="text-sm font-medium">
                                                        {config.label}
                                                    </p>

                                                    <p className="mt-0.5 truncate text-xs opacity-70">
                                                        {item.text}
                                                    </p>
                                                </div>

                                                <span className="shrink-0 rounded-pill bg-bg-surface px-2.5 py-1 text-xs font-semibold text-text-primary">
                                                    {item.value}
                                                </span>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </aside>
                </div>
            </AuthenticatedLayout>

            {selectedSpot && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-sm"
                    onMouseDown={(event) => {
                        if (event.currentTarget === event.target) {
                            closeOverride();
                        }
                    }}
                >
                    <div className="w-full max-w-md rounded-modal bg-bg-surface p-6 shadow-modal">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.12em] text-text-muted">
                                    Manual override
                                </p>

                                <h2 className="mt-1 text-xl font-semibold text-text-primary">
                                    {selectedSpot.code}
                                </h2>

                                <p className="mt-1 text-sm text-text-secondary">
                                    Temporarily change the occupancy state of
                                    this parking space.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={closeOverride}
                                disabled={form.processing}
                                className="text-sm font-medium text-text-muted transition hover:text-text-primary disabled:opacity-50"
                            >
                                Close
                            </button>
                        </div>

                        <form
                            onSubmit={submitOverride}
                            className="mt-6 space-y-5"
                        >
                            <div>
                                <label
                                    htmlFor="override-status"
                                    className="mb-2 block text-xs font-medium text-text-secondary"
                                >
                                    Status
                                </label>

                                <select
                                    id="override-status"
                                    value={form.data.status}
                                    onChange={(event) =>
                                        form.setData(
                                            "status",
                                            event.target
                                                .value as SpotStatus,
                                        )
                                    }
                                    className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none transition focus:border-border-strong focus:bg-bg-surface"
                                >
                                    <option value="free">Free</option>
                                    <option value="occupied">
                                        Occupied
                                    </option>
                                    <option value="reserved">
                                        Reserved
                                    </option>
                                    <option value="offline">Offline</option>
                                </select>

                                {form.errors.status && (
                                    <p className="mt-2 text-xs text-status-rose">
                                        {form.errors.status}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="override-reason"
                                    className="mb-2 block text-xs font-medium text-text-secondary"
                                >
                                    Reason
                                </label>

                                <textarea
                                    id="override-reason"
                                    value={form.data.reason}
                                    onChange={(event) =>
                                        form.setData(
                                            "reason",
                                            event.target.value,
                                        )
                                    }
                                    placeholder="Why are you changing this spot?"
                                    rows={3}
                                    className="w-full resize-none rounded-card border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none transition placeholder:text-text-muted focus:border-border-strong focus:bg-bg-surface"
                                />

                                {form.errors.reason && (
                                    <p className="mt-2 text-xs text-status-rose">
                                        {form.errors.reason}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="override-duration"
                                    className="mb-2 block text-xs font-medium text-text-secondary"
                                >
                                    Override duration
                                </label>

                                <select
                                    id="override-duration"
                                    value={form.data.duration_minutes}
                                    onChange={(event) =>
                                        form.setData(
                                            "duration_minutes",
                                            event.target.value,
                                        )
                                    }
                                    className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none transition focus:border-border-strong focus:bg-bg-surface"
                                >
                                    <option value="15">15 minutes</option>
                                    <option value="30">30 minutes</option>
                                    <option value="60">1 hour</option>
                                    <option value="120">2 hours</option>
                                    <option value="240">4 hours</option>
                                    <option value="480">8 hours</option>
                                    <option value="1440">24 hours</option>
                                </select>

                                {form.errors.duration_minutes && (
                                    <p className="mt-2 text-xs text-status-rose">
                                        {form.errors.duration_minutes}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-3 border-t border-border-light pt-5">
                                <button
                                    type="button"
                                    onClick={closeOverride}
                                    disabled={form.processing}
                                    className="rounded-pill px-5 py-3 text-sm font-medium text-text-secondary transition hover:bg-bg-subtle hover:text-text-primary disabled:opacity-50"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    disabled={
                                        form.processing ||
                                        form.data.reason.trim().length === 0
                                    }
                                    className="rounded-pill bg-action-dark px-5 py-3 text-sm font-semibold text-action-dark-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {form.processing
                                        ? "Saving..."
                                        : "Apply override"}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </>
    );
}
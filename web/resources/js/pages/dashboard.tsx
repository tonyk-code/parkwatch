import { Head, Link, usePage } from "@inertiajs/react";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout";
import type { Auth } from "@/types/auth";

type Site = {
    id: number;
    name: string;
    code: string;
    address: string | null;
    total_capacity: number;
    zones_count: number;
};

type DashboardProps = {
    auth: Auth;
    scope: "organization" | "assigned";
    stats: {
        sites: number;
        capacity: number;
        occupied: number;
        active_sessions: number;
    };
    sites: Site[];
};

const tones = [
    "bg-status-blue-bg",
    "bg-status-amber-bg",
    "bg-status-green-bg",
    "bg-status-purple-bg",
    "bg-status-rose-bg",
];

export default function Dashboard() {
    const { auth, scope, stats, sites } = usePage<DashboardProps>().props;

    const user = auth.user;

    if (!user) {
        return null;
    }

    const availableSpots = Math.max(stats.capacity - stats.occupied, 0);

    const occupancyPercentage =
        stats.capacity > 0
            ? Math.round((stats.occupied / stats.capacity) * 100)
            : 0;

    const firstName = user.full_name.split(" ")[0];

    const statCards = [
        { label: "Sites", value: stats.sites, hint: "active locations", tone: "bg-status-blue-bg" },
        { label: "Total capacity", value: stats.capacity, hint: "parking spots", tone: "bg-status-amber-bg" },
        { label: "Occupied", value: stats.occupied, hint: `${occupancyPercentage}% occupancy`, tone: "bg-status-rose-bg" },
        { label: "Active sessions", value: stats.active_sessions, hint: "in progress now", tone: "bg-status-green-bg" },
    ];

    return (
        <>
            <Head title="Dashboard" />

            <AuthenticatedLayout
                breadcrumbs={[
                    "ParkWatch",
                    scope === "organization" ? "Organization" : "Assigned sites",
                    "Dashboard",
                ]}
            >
                <div className="grid h-full xl:grid-cols-[minmax(0,1fr)_22rem]">
                    {/* Left column */}
                    <section className="min-w-0 xl:border-r xl:border-border-default">
                        <div className="flex items-center justify-between gap-4 px-2 pb-4 pt-6 sm:px-3">
                            <div className="min-w-0">
                                <h1 className="truncate text-lg font-semibold text-text-primary">
                                    Good to see you, {firstName}
                                </h1>
                                <p className="mt-0.5 text-sm text-text-secondary">
                                    Monitor your parking operations from one place.
                                </p>
                            </div>

                            <span className="inline-flex shrink-0 items-center gap-2 rounded-pill border border-border-default px-3 py-1.5 text-xs font-medium text-text-primary">
                                {scope === "organization" ? "All sites" : "Assigned"}
                                <span className="grid h-5 min-w-5 place-items-center rounded-pill bg-action-dark px-1.5 text-[10px] text-action-dark-foreground">
                                    {stats.sites}
                                </span>
                            </span>
                        </div>

                        <div className="grid gap-3 border-y border-border-default px-2 py-4 sm:grid-cols-2 sm:px-3 2xl:grid-cols-4">
                            {statCards.map((card) => (
                                <div key={card.label} className={`rounded-card p-4 ${card.tone}`}>
                                    <p className="text-sm font-medium text-text-primary">
                                        {card.label}
                                    </p>
                                    <p className="mt-3 text-3xl font-semibold tracking-tight text-text-primary">
                                        {card.value}
                                    </p>
                                    <p className="mt-1 text-xs text-text-secondary">
                                        {card.hint}
                                    </p>
                                </div>
                            ))}
                        </div>

                        <div className="flex items-center justify-between px-2 py-4 sm:px-3">
                            <h2 className="text-base font-semibold text-text-primary">
                                Parking sites
                            </h2>
                            <span className="rounded-pill bg-bg-tag px-3 py-1 text-xs font-medium text-text-tag">
                                {sites.length} shown
                            </span>
                        </div>

                        <div className="divide-y divide-border-default border-t border-border-default">
                            {sites.length === 0 ? (
                                <div className="flex items-center gap-6 px-3 py-6">
                                    <span className="w-6 text-sm text-text-primary">—</span>
                                    <p className="text-sm text-text-muted">
                                        No sites available. Your account does not currently have access to an active site.
                                    </p>
                                </div>
                            ) : (
                                sites.map((site, i) => (
                                    <div key={site.id} className="flex items-center gap-4 px-3 py-4">
                                        <span className="w-6 shrink-0 text-sm text-text-primary">
                                            {i + 1}
                                        </span>

                                        <Link
                                            href={`/sites/${site.id}/dashboard`}
                                            className={`flex min-w-0 items-center gap-3 rounded-card px-3 py-2.5 transition hover:shadow-soft ${tones[i % tones.length]}`}
                                        >
                                            <span className="grid h-9 w-9 shrink-0 place-items-center rounded-icon bg-bg-surface text-[10px] font-semibold uppercase text-text-primary">
                                                {site.code.slice(0, 3)}
                                            </span>
                                            <span className="min-w-0">
                                                <span className="block truncate text-sm font-medium text-text-primary">
                                                    {site.name}
                                                </span>
                                                <span className="block truncate text-xs text-text-secondary">
                                                    {site.address || "No address"}
                                                </span>
                                            </span>
                                        </Link>

                                        <span className="hidden shrink-0 rounded-card bg-bg-subtle px-3 py-2.5 text-xs text-text-secondary sm:block">
                                            <span className="block text-sm font-medium text-text-primary">
                                                {site.total_capacity} spots
                                            </span>
                                            {site.zones_count} zones
                                        </span>
                                    </div>
                                ))
                            )}
                        </div>
                    </section>

                    {/* Right column */}
                    <aside className="min-w-0 border-t border-border-default xl:border-t-0">
                        <div className="px-4 pb-4 pt-6">
                            <h2 className="text-lg font-semibold text-text-primary">
                                Availability
                            </h2>

                            <div className="mt-5 flex items-end justify-between">
                                <div>
                                    <p className="text-4xl font-semibold tracking-tight text-text-primary">
                                        {availableSpots}
                                    </p>
                                    <p className="mt-1 text-sm text-text-secondary">
                                        spaces available
                                    </p>
                                </div>
                                <span className="grid h-14 w-14 place-items-center rounded-pill bg-action-dark text-sm font-semibold text-action-dark-foreground">
                                    {occupancyPercentage}%
                                </span>
                            </div>

                            <div className="mt-6 h-2 overflow-hidden rounded-pill bg-bg-tag">
                                <div
                                    className="h-full rounded-pill bg-action-dark transition-all"
                                    style={{ width: `${occupancyPercentage}%` }}
                                />
                            </div>

                            <div className="mt-4 flex flex-wrap gap-4 text-xs text-text-primary">
                                <span className="inline-flex items-center gap-1.5">
                                    <span className="h-1.5 w-1.5 rounded-pill bg-status-green" />
                                    Free
                                    <span className="rounded-pill bg-bg-tag px-1.5 text-text-tag">{availableSpots}</span>
                                </span>
                                <span className="inline-flex items-center gap-1.5">
                                    <span className="h-1.5 w-1.5 rounded-pill bg-status-rose" />
                                    Occupied
                                    <span className="rounded-pill bg-bg-tag px-1.5 text-text-tag">{stats.occupied}</span>
                                </span>
                            </div>
                        </div>

                        <div className="border-t border-border-default px-2 pb-4 pt-5">
                            <h3 className="px-2 text-base font-semibold text-text-primary">
                                Overview
                            </h3>

                            <div className="mt-4 space-y-2">
                                {[
                                    { title: "Active sessions", sub: "Vehicles currently parked", value: stats.active_sessions, tone: "bg-status-purple-bg" },
                                    { title: "Total capacity", sub: `Across ${stats.sites} sites`, value: stats.capacity, tone: "bg-status-amber-bg" },
                                    { title: "Open spaces", sub: "Ready for new arrivals", value: availableSpots, tone: "bg-status-blue-bg" },
                                ].map((item) => (
                                    <div key={item.title} className={`rounded-card p-3 ${item.tone}`}>
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-medium text-text-primary">
                                                    {item.title}
                                                </p>
                                                <p className="mt-0.5 truncate text-xs text-text-secondary">
                                                    {item.sub}
                                                </p>
                                            </div>
                                            <span className="shrink-0 rounded-pill bg-bg-surface px-2.5 py-1 text-xs font-medium text-text-primary">
                                                {item.value}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </aside>
                </div>
            </AuthenticatedLayout>
        </>
    );
}

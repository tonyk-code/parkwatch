import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import type { FormEvent } from "react";
import { useState } from "react";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout";
import type { Auth } from "@/types/auth";

type Site = {
    id: number;
    name: string;
    code: string;
};

type AvailableSpot = {
    id: number;
    code: string;
    zone: {
        name: string;
        code: string;
    };
};

type Session = {
    id: number;
    reference_code: string;
    status: "active" | "awaiting_payment" | "paid" | "completed";
    session_mode: "gate" | "spot";
    entered_at: string | null;
    exited_at: string | null;
    amount_due: number;
    amount_paid: number;
    currency: string;
    spot: {
        code: string;
    } | null;
    zone: {
        name: string;
        code: string;
    } | null;
};

type SessionsPageProps = {
    auth: Auth;
    site: {
        id: number;
        name: string;
        code: string;
        address: string | null;
    };
    sites: Site[];
    sessions: Session[];
    availableSpots: AvailableSpot[];
};

type SessionForm = {
    spot_id: string;
};

const statusStyles: Record<
    Session["status"],
    { label: string; className: string; dotClassName: string }
> = {
    active: {
        label: "Active",
        className: "bg-status-green-bg text-status-green",
        dotClassName: "bg-status-green",
    },
    awaiting_payment: {
        label: "Awaiting payment",
        className: "bg-status-amber-bg text-status-amber",
        dotClassName: "bg-status-amber",
    },
    paid: {
        label: "Paid",
        className: "bg-status-blue-bg text-status-blue",
        dotClassName: "bg-status-blue",
    },
    completed: {
        label: "Completed",
        className: "bg-bg-tag text-text-secondary",
        dotClassName: "bg-text-muted",
    },
};

const rowTones = [
    "bg-status-blue-bg",
    "bg-status-green-bg",
    "bg-status-purple-bg",
    "bg-status-amber-bg",
];

function formatDate(value: string | null) {
    return value ? new Date(value).toLocaleString() : "—";
}

function formatMoney(amountMinor: number, currency: string) {
    const amount = amountMinor / 100;

    return `${amount.toLocaleString(undefined, {
        minimumFractionDigits: amount % 1 === 0 ? 0 : 2,
        maximumFractionDigits: 2,
    })} ${currency}`;
}

export default function Sessions() {
    const { site, sites, sessions, availableSpots } =
        usePage<SessionsPageProps>().props;

    const [showCreate, setShowCreate] = useState(false);

    const form = useForm<SessionForm>({
        spot_id: "",
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.post(`/sites/${site.id}/sessions`, {
            preserveScroll: true,
            onSuccess: () => {
                setShowCreate(false);
                form.reset();
            },
        });
    }

    function refresh() {
        router.reload({
            only: ["sessions", "availableSpots"],
        });
    }

    const activeCount = sessions.filter(
        (session) => session.status === "active",
    ).length;

    const awaitingCount = sessions.filter(
        (session) => session.status === "awaiting_payment",
    ).length;

    const collectedMinor = sessions.reduce(
        (sum, session) => sum + session.amount_paid,
        0,
    );

    const pendingMinor = sessions
        .filter((session) => session.status === "awaiting_payment")
        .reduce(
            (sum, session) =>
                sum + Math.max(0, session.amount_due - session.amount_paid),
            0,
        );

    const currency = sessions[0]?.currency ?? "ETB";

    const statCards = [
        {
            label: "Active",
            value: activeCount,
            hint: "vehicles parked now",
            tone: "bg-status-green-bg",
        },
        {
            label: "Available spots",
            value: availableSpots.length,
            hint: "ready for arrivals",
            tone: "bg-status-blue-bg",
        },
        {
            label: "Awaiting payment",
            value: awaitingCount,
            hint: "pending checkout",
            tone: "bg-status-amber-bg",
        },
        {
            label: "Recent sessions",
            value: sessions.length,
            hint: "in this list",
            tone: "bg-status-purple-bg",
        },
    ];

    const statusCounts = (Object.keys(statusStyles) as Session["status"][]).map(
        (status) => ({
            status,
            value: sessions.filter((session) => session.status === status)
                .length,
        }),
    );

    return (
        <>
            <Head title={`Sessions — ${site.name}`} />

            <AuthenticatedLayout
                breadcrumbs={["ParkWatch", site.name, "Sessions"]}
            >
                <div className="grid h-full xl:grid-cols-[minmax(0,1fr)_20rem]">
                    <section className="min-w-0 xl:border-r xl:border-border-default">
                        <div className="flex flex-col gap-5 px-2 pb-5 pt-6 sm:px-3 lg:flex-row lg:items-end lg:justify-between">
                            <div className="min-w-0">
                                <p className="text-xs font-semibold uppercase text-text-muted">
                                    Parking sessions
                                </p>

                                <div className="mt-2 flex items-center gap-2">
                                    <h1 className="truncate text-xl font-semibold text-text-primary">
                                        {site.name}
                                    </h1>

                                    <span className="rounded-pill bg-bg-tag px-2.5 py-0.5 text-xs font-semibold text-text-tag">
                                        {sessions.length}
                                    </span>
                                </div>

                                <p className="mt-1 truncate text-sm text-text-secondary">
                                    Track active and recent vehicles at this
                                    site.
                                </p>
                            </div>

                            <div className="flex flex-wrap items-center gap-2">
                                {sites.map((availableSite) => (
                                    <Link
                                        key={availableSite.id}
                                        href={`/sites/${availableSite.id}/sessions`}
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
                                    Recent sessions
                                </h2>

                                <p className="mt-0.5 text-xs text-text-secondary">
                                    Latest entries and exits
                                </p>
                            </div>

                            <span className="rounded-pill bg-bg-tag px-3 py-1 text-xs font-medium text-text-tag">
                                {sessions.length}{" "}
                                {sessions.length === 1 ? "session" : "sessions"}
                            </span>
                        </div>

                        <div className="space-y-2 border-t border-border-default p-2 sm:p-3">
                            {sessions.length === 0 ? (
                                <div className="rounded-card bg-bg-subtle px-5 py-12 text-center">
                                    <p className="text-sm font-medium text-text-primary">
                                        No parking sessions yet
                                    </p>

                                    <p className="mt-1 text-xs text-text-secondary">
                                        Start a session when a vehicle enters a
                                        parking space.
                                    </p>
                                </div>
                            ) : (
                                sessions.map((session, index) => {
                                    const config = statusStyles[session.status];

                                    return (
                                        <div
                                            key={session.id}
                                            className={`grid items-center gap-4 rounded-card px-4 py-3 sm:grid-cols-[auto_1.3fr_1fr_1fr_auto_5rem] ${rowTones[index % rowTones.length]}`}
                                        >
                                            <span className="grid h-9 w-9 place-items-center rounded-pill bg-bg-surface text-xs font-semibold text-text-primary">
                                                {String(index + 1).padStart(
                                                    2,
                                                    "0",
                                                )}
                                            </span>

                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-semibold text-text-primary">
                                                    {session.reference_code}
                                                </p>

                                                <p className="mt-0.5 truncate text-xs text-text-secondary">
                                                    {session.zone?.name ||
                                                        "No zone"}
                                                    {session.spot
                                                        ? ` · ${session.spot.code}`
                                                        : ""}
                                                    {` · ${session.session_mode}`}
                                                </p>
                                            </div>

                                            <div>
                                                <span
                                                    className={`inline-flex items-center gap-1.5 rounded-pill px-2.5 py-1 text-[10px] font-semibold ${config.className}`}
                                                >
                                                    <span
                                                        className={`h-1.5 w-1.5 rounded-pill ${config.dotClassName}`}
                                                    />

                                                    {config.label}
                                                </span>
                                            </div>

                                            <div className="min-w-0">
                                                <p className="text-[10px] text-text-muted">
                                                    {session.exited_at
                                                        ? "Exited"
                                                        : "Entered"}
                                                </p>

                                                <p className="mt-0.5 truncate text-xs text-text-secondary">
                                                    {formatDate(
                                                        session.exited_at ??
                                                            session.entered_at,
                                                    )}
                                                </p>
                                            </div>

                                            <div className="rounded-pill bg-bg-surface px-3 py-1.5 text-left sm:text-right">
                                                <p className="text-sm font-semibold text-text-primary">
                                                    {formatMoney(
                                                        session.amount_due,
                                                        session.currency,
                                                    )}
                                                </p>

                                                {session.amount_paid > 0 && (
                                                    <p className="mt-0.5 text-[10px] text-text-secondary">
                                                        Paid{" "}
                                                        {formatMoney(
                                                            session.amount_paid,
                                                            session.currency,
                                                        )}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="sm:text-right">
                                                {session.status ===
                                                    "active" && (
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            const confirmed =
                                                                window.confirm(
                                                                    `Close session ${session.reference_code}? The parking charge will be calculated and the session will move to awaiting payment.`,
                                                                );

                                                            if (!confirmed) {
                                                                return;
                                                            }

                                                            router.post(
                                                                `/sites/${site.id}/sessions/${session.id}/close`,
                                                                {},
                                                                {
                                                                    preserveScroll: true,
                                                                },
                                                            );
                                                        }}
                                                        className="rounded-pill bg-action-dark px-4 py-2 text-xs font-semibold text-action-dark-foreground transition hover:opacity-90"
                                                    >
                                                        Close
                                                    </button>
                                                )}

                                                {session.status ===
                                                    "awaiting_payment" && (
                                                    <div className="text-xs font-semibold text-status-amber">
                                                        {formatMoney(
                                                            Math.max(
                                                                0,
                                                                session.amount_due -
                                                                    session.amount_paid,
                                                            ),
                                                            session.currency,
                                                        )}{" "}
                                                        due
                                                    </div>
                                                )}

                                                {session.status === "paid" && (
                                                    <span className="text-xs font-semibold text-status-blue">
                                                        Paid
                                                    </span>
                                                )}

                                                {session.status ===
                                                    "completed" && (
                                                    <span className="text-xs font-semibold text-text-muted">
                                                        Completed
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </section>

                    <aside className="min-w-0 border-t border-border-default xl:border-t-0">
                        <div className="px-4 pb-5 pt-6">
                            <div className="flex items-center justify-between gap-3">
                                <h2 className="text-lg font-semibold text-text-primary">
                                    Quick actions
                                </h2>

                                <button
                                    type="button"
                                    onClick={refresh}
                                    aria-label="Refresh sessions"
                                    title="Refresh sessions"
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
                                        {activeCount}
                                    </p>

                                    <p className="mt-1 text-sm text-text-secondary">
                                        vehicles parked now
                                    </p>
                                </div>

                                <span className="grid h-14 w-14 place-items-center rounded-pill bg-action-dark text-sm font-semibold text-action-dark-foreground">
                                    {availableSpots.length}
                                </span>
                            </div>

                            <button
                                type="button"
                                onClick={() => setShowCreate(true)}
                                className="mt-6 flex w-full items-center justify-center gap-2 rounded-pill bg-action-dark px-4 py-3 text-sm font-semibold text-action-dark-foreground transition hover:opacity-90"
                            >
                                <svg
                                    className="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.8"
                                >
                                    <path d="M12 5v14" />
                                    <path d="M5 12h14" />
                                </svg>
                                Start session
                            </button>

                            <div className="mt-4 space-y-2">
                                <div className="flex items-center justify-between rounded-card bg-bg-subtle px-3 py-2.5">
                                    <span className="text-xs text-text-secondary">
                                        Pending
                                    </span>

                                    <span className="text-xs font-semibold text-status-amber">
                                        {formatMoney(pendingMinor, currency)}
                                    </span>
                                </div>

                                <div className="flex items-center justify-between rounded-card bg-bg-subtle px-3 py-2.5">
                                    <span className="text-xs text-text-secondary">
                                        Collected
                                    </span>

                                    <span className="text-xs font-semibold text-status-green">
                                        {formatMoney(collectedMinor, currency)}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="border-t border-border-default px-2 pb-4 pt-5">
                            <h3 className="px-2 text-base font-semibold text-text-primary">
                                Status overview
                            </h3>

                            <div className="mt-4 space-y-2">
                                {statusCounts.map(({ status, value }) => {
                                    const config = statusStyles[status];

                                    return (
                                        <div
                                            key={status}
                                            className={`flex items-center justify-between gap-3 rounded-card p-3 ${config.className}`}
                                        >
                                            <div className="flex items-center gap-2">
                                                <span
                                                    className={`h-2 w-2 rounded-pill ${config.dotClassName}`}
                                                />

                                                <span className="text-sm font-medium">
                                                    {config.label}
                                                </span>
                                            </div>

                                            <span className="shrink-0 rounded-pill bg-bg-surface px-2.5 py-1 text-xs font-semibold text-text-primary">
                                                {value}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </aside>
                </div>
            </AuthenticatedLayout>

            {showCreate && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-sm"
                    onMouseDown={(event) => {
                        if (event.currentTarget === event.target) {
                            setShowCreate(false);
                        }
                    }}
                >
                    <div className="w-full max-w-md rounded-modal bg-bg-surface p-6 shadow-modal">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <p className="text-xs font-semibold uppercase text-text-muted">
                                    New session
                                </p>

                                <h2 className="mt-1 text-xl font-semibold text-text-primary">
                                    Start parking session
                                </h2>

                                <p className="mt-1 text-sm text-text-secondary">
                                    Select the space where the vehicle is
                                    parked.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setShowCreate(false)}
                                aria-label="Close"
                                className="grid h-9 w-9 shrink-0 place-items-center rounded-icon border border-border-default text-text-secondary transition hover:bg-bg-subtle hover:text-text-primary"
                            >
                                <svg
                                    className="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.8"
                                >
                                    <path d="M6 6l12 12" />
                                    <path d="M18 6L6 18" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={submit} className="mt-6 space-y-5">
                            <div>
                                <label
                                    htmlFor="spot_id"
                                    className="mb-2 block text-xs font-medium text-text-secondary"
                                >
                                    Parking spot
                                </label>

                                <select
                                    id="spot_id"
                                    value={form.data.spot_id}
                                    onChange={(event) =>
                                        form.setData(
                                            "spot_id",
                                            event.target.value,
                                        )
                                    }
                                    className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none focus:border-border-strong focus:bg-bg-surface"
                                >
                                    <option value="">Select a free spot</option>

                                    {availableSpots.map((spot) => (
                                        <option key={spot.id} value={spot.id}>
                                            {spot.code} — {spot.zone.name}
                                        </option>
                                    ))}
                                </select>

                                {form.errors.spot_id && (
                                    <p className="mt-2 text-xs text-status-rose">
                                        {form.errors.spot_id}
                                    </p>
                                )}
                            </div>

                            <div className="flex justify-end gap-3 border-t border-border-light pt-5">
                                <button
                                    type="button"
                                    onClick={() => setShowCreate(false)}
                                    className="rounded-pill px-5 py-3 text-sm font-medium text-text-secondary hover:bg-bg-subtle hover:text-text-primary"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    disabled={
                                        form.processing || !form.data.spot_id
                                    }
                                    className="rounded-pill bg-action-dark px-5 py-3 text-sm font-semibold text-action-dark-foreground disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {form.processing
                                        ? "Starting..."
                                        : "Start session"}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </>
    );
}

import { Head, router, useForm } from "@inertiajs/react";
import { useState } from "react";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout";

type RateRule = {
    id: number;
    sequence: number;
    from_minute: number;
    to_minute: number | null;
    unit: string;
    price_minor: number;
};

type RatePlan = {
    id: number;
    name: string;
    currency: string;
    grace_minutes: number;
    daily_max_minor: number | null;
    rounding: string;
    rounding_increment_minutes: number;
    priority: number;
    valid_from: string;
    valid_to: string | null;
    is_active: boolean;
    rate_rules: RateRule[];
};

type RatesPageProps = {
    site: {
        id: number;
        name: string;
        code: string;
    };
    currentPlan: RatePlan | null;
    rateHistory: RatePlan[];
};

type RateForm = {
    hourly_rate: string;
    grace_minutes: string;
    daily_max: string;
};

function formatMoney(amountMinor: number | null, currency: string) {
    if (amountMinor === null) {
        return "No limit";
    }

    return `${(amountMinor / 100).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })} ${currency}`;
}

function formatDate(value: string) {
    return new Date(value).toLocaleString();
}

export default function Rates({
    site,
    currentPlan,
    rateHistory,
}: RatesPageProps) {
    const [showEdit, setShowEdit] = useState(false);

    const currentRule = currentPlan?.rate_rules?.[0] ?? null;

    const form = useForm<RateForm>({
        hourly_rate: currentRule ? String(currentRule.price_minor / 100) : "20",
        grace_minutes: currentPlan ? String(currentPlan.grace_minutes) : "15",
        daily_max:
            currentPlan?.daily_max_minor !== null &&
            currentPlan?.daily_max_minor !== undefined
                ? String(currentPlan.daily_max_minor / 100)
                : "",
    });

    function openEdit() {
        form.setData({
            hourly_rate: currentRule
                ? String(currentRule.price_minor / 100)
                : "20",
            grace_minutes: currentPlan
                ? String(currentPlan.grace_minutes)
                : "15",
            daily_max:
                currentPlan?.daily_max_minor !== null &&
                currentPlan?.daily_max_minor !== undefined
                    ? String(currentPlan.daily_max_minor / 100)
                    : "",
        });

        form.clearErrors();
        setShowEdit(true);
    }

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.post(`/sites/${site.id}/rates`, {
            preserveScroll: true,
            onSuccess: () => {
                setShowEdit(false);

                router.reload({
                    only: ["currentPlan", "rateHistory"],
                });
            },
        });
    }

    const hourly =
        currentRule && currentPlan
            ? formatMoney(currentRule.price_minor, currentPlan.currency)
            : "—";
    const rowTones = [
        "bg-status-blue-bg",
        "bg-status-purple-bg",
        "bg-status-amber-bg",
        "bg-bg-subtle",
    ];
    const statCards =
        currentPlan && currentRule
            ? [
                  {
                      label: "Hourly rate",
                      value: hourly,
                      hint: "per hour",
                      tone: "bg-status-blue-bg",
                  },
                  {
                      label: "Grace period",
                      value: String(currentPlan.grace_minutes),
                      hint: "minutes free",
                      tone: "bg-status-green-bg",
                  },
                  {
                      label: "Daily maximum",
                      value: formatMoney(
                          currentPlan.daily_max_minor,
                          currentPlan.currency,
                      ),
                      hint: "maximum charge",
                      tone: "bg-status-amber-bg",
                  },
                  {
                      label: "Rounding",
                      value: String(currentPlan.rounding_increment_minutes),
                      hint: `minutes, rounded ${currentPlan.rounding}`,
                      tone: "bg-status-purple-bg",
                  },
              ]
            : [];

    return (
        <>
            <Head title={`Rates — ${site.name}`} />

            <AuthenticatedLayout
                breadcrumbs={["ParkWatch", site.name, "Rates"]}
            >
                <div className="grid h-full xl:grid-cols-[minmax(0,1fr)_20rem]">
                    <section className="min-w-0 xl:border-r xl:border-border-default">
                        <div className="flex flex-col gap-4 px-2 pb-5 pt-6 sm:flex-row sm:items-end sm:justify-between sm:px-3">
                            <div className="min-w-0">
                                <p className="text-xs font-semibold uppercase text-text-muted">
                                    Management
                                </p>
                                <div className="mt-2 flex items-center gap-2">
                                    <h1 className="truncate text-xl font-semibold text-text-primary">
                                        Parking rates
                                    </h1>
                                    <span className="rounded-pill bg-bg-tag px-2.5 py-0.5 text-xs font-semibold text-text-tag">
                                        {site.code}
                                    </span>
                                </div>
                                <p className="mt-1 truncate text-sm text-text-secondary">
                                    Manage pricing for {site.name}.
                                </p>
                            </div>
                        </div>

                        <div className="border-y border-border-default px-2 py-4 sm:px-3">
                            {!currentPlan || !currentRule ? (
                                <div className="rounded-card bg-bg-subtle px-5 py-12 text-center">
                                    <p className="text-sm font-semibold text-text-primary">
                                        No active rate configured
                                    </p>
                                    <p className="mt-1 text-xs text-text-secondary">
                                        This site does not currently have an
                                        active parking rate.
                                    </p>
                                </div>
                            ) : (
                                <div className="grid gap-3 sm:grid-cols-2 2xl:grid-cols-4">
                                    {statCards.map((card) => (
                                        <div
                                            key={card.label}
                                            className={`rounded-card p-4 ${card.tone}`}
                                        >
                                            <p className="text-sm font-medium text-text-primary">
                                                {card.label}
                                            </p>
                                            <p className="mt-3 truncate text-3xl font-semibold text-text-primary">
                                                {card.value}
                                            </p>
                                            <p className="mt-1 text-xs text-text-secondary">
                                                {card.hint}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="flex items-center justify-between px-3 py-4">
                            <div>
                                <h2 className="text-base font-semibold text-text-primary">
                                    Rate history
                                </h2>
                                <p className="mt-0.5 text-xs text-text-secondary">
                                    Previous rates remain attached to existing
                                    sessions.
                                </p>
                            </div>
                            <span className="rounded-pill bg-bg-tag px-3 py-1 text-xs font-medium text-text-tag">
                                {rateHistory.length}{" "}
                                {rateHistory.length === 1
                                    ? "version"
                                    : "versions"}
                            </span>
                        </div>

                        <div className="space-y-2 border-t border-border-default p-2 sm:p-3">
                            {rateHistory.length === 0 ? (
                                <div className="rounded-card bg-bg-subtle px-5 py-12 text-center">
                                    <p className="text-sm font-medium text-text-primary">
                                        No rate history yet
                                    </p>
                                    <p className="mt-1 text-xs text-text-secondary">
                                        Rate versions will appear here once
                                        saved.
                                    </p>
                                </div>
                            ) : (
                                rateHistory.map((plan, index) => {
                                    const rule = plan.rate_rules?.[0] ?? null;

                                    return (
                                        <div
                                            key={plan.id}
                                            className={`grid items-center gap-4 rounded-card px-4 py-3 sm:grid-cols-[auto_1.4fr_1fr_1fr_auto] ${
                                                plan.is_active
                                                    ? "bg-status-green-bg"
                                                    : rowTones[
                                                          index %
                                                              rowTones.length
                                                      ]
                                            }`}
                                        >
                                            <span className="grid h-9 w-9 place-items-center rounded-pill bg-bg-surface text-xs font-semibold text-text-primary">
                                                {String(index + 1).padStart(
                                                    2,
                                                    "0",
                                                )}
                                            </span>

                                            <div className="min-w-0">
                                                <div className="flex items-center gap-2">
                                                    <p className="truncate text-sm font-semibold text-text-primary">
                                                        {plan.name}
                                                    </p>
                                                    {plan.is_active && (
                                                        <span className="inline-flex items-center gap-1.5 rounded-pill bg-bg-surface px-2.5 py-1 text-[10px] font-semibold text-status-green">
                                                            <span className="h-1.5 w-1.5 rounded-pill bg-status-green" />
                                                            Current
                                                        </span>
                                                    )}
                                                </div>
                                                <p className="mt-0.5 truncate text-xs text-text-secondary">
                                                    Started{" "}
                                                    {formatDate(
                                                        plan.valid_from,
                                                    )}
                                                </p>
                                            </div>

                                            <div className="rounded-pill bg-bg-surface px-3 py-1.5">
                                                <p className="text-[10px] text-text-muted">
                                                    Hourly
                                                </p>
                                                <p className="text-sm font-semibold text-text-primary">
                                                    {rule
                                                        ? formatMoney(
                                                              rule.price_minor,
                                                              plan.currency,
                                                          )
                                                        : "—"}
                                                </p>
                                            </div>

                                            <div className="min-w-0">
                                                <p className="text-[10px] text-text-muted">
                                                    Daily maximum
                                                </p>
                                                <p className="mt-0.5 truncate text-sm font-semibold text-text-primary">
                                                    {formatMoney(
                                                        plan.daily_max_minor,
                                                        plan.currency,
                                                    )}
                                                </p>
                                            </div>

                                            <div className="sm:text-right">
                                                <p className="text-[10px] text-text-muted">
                                                    Valid until
                                                </p>
                                                <p className="mt-0.5 text-xs font-medium text-text-secondary">
                                                    {plan.valid_to
                                                        ? formatDate(
                                                              plan.valid_to,
                                                          )
                                                        : "Current"}
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </section>

                    <aside className="min-w-0 border-t border-border-default xl:border-t-0">
                        <div className="px-4 pb-5 pt-6">
                            <h2 className="text-lg font-semibold text-text-primary">
                                Current rate
                            </h2>

                            <div className="mt-5 flex items-end justify-between">
                                <div className="min-w-0">
                                    <p className="truncate text-4xl font-semibold text-text-primary">
                                        {hourly}
                                    </p>
                                    <p className="mt-1 text-sm text-text-secondary">
                                        {currentPlan
                                            ? currentPlan.name
                                            : "No active plan"}
                                    </p>
                                </div>
                                <span className="grid h-14 w-14 shrink-0 place-items-center rounded-pill bg-action-dark text-action-dark-foreground">
                                    <svg
                                        className="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="1.8"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>
                                </span>
                            </div>

                            {currentPlan && (
                                <button
                                    type="button"
                                    onClick={openEdit}
                                    className="mt-6 flex w-full items-center justify-center gap-2 rounded-pill bg-action-dark px-4 py-3 text-sm font-semibold text-action-dark-foreground transition hover:opacity-90"
                                >
                                    <svg
                                        className="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="1.8"
                                    >
                                        <path d="M4 20h4L18.5 9.5a2.1 2.1 0 00-4-4L4 16v4z" />
                                    </svg>
                                    Change rate
                                </button>
                            )}

                            <p className="mt-2 text-right text-[10px] text-text-muted">
                                New pricing applies to new sessions only
                            </p>
                        </div>

                        <div className="border-t border-border-default px-2 pb-4 pt-5">
                            <h3 className="px-2 text-base font-semibold text-text-primary">
                                Plan details
                            </h3>
                            <div className="mt-4 space-y-2">
                                {[
                                    {
                                        label: "Currency",
                                        value: currentPlan?.currency ?? "—",
                                        tone: "bg-status-blue-bg",
                                    },
                                    {
                                        label: "Priority",
                                        value: currentPlan
                                            ? String(currentPlan.priority)
                                            : "—",
                                        tone: "bg-status-purple-bg",
                                    },
                                    {
                                        label: "Valid from",
                                        value: currentPlan
                                            ? new Date(
                                                  currentPlan.valid_from,
                                              ).toLocaleDateString()
                                            : "—",
                                        tone: "bg-status-amber-bg",
                                    },
                                    {
                                        label: "Status",
                                        value: currentPlan?.is_active
                                            ? "Active"
                                            : "Inactive",
                                        tone: "bg-status-green-bg",
                                    },
                                ].map((item) => (
                                    <div
                                        key={item.label}
                                        className={`flex items-center justify-between rounded-card px-4 py-3 ${item.tone}`}
                                    >
                                        <p className="text-sm font-medium text-text-primary">
                                            {item.label}
                                        </p>
                                        <span className="rounded-pill bg-bg-surface px-3 py-1 text-xs font-semibold text-text-primary">
                                            {item.value}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </aside>
                </div>
            </AuthenticatedLayout>

            {showEdit && currentPlan && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-sm"
                    onMouseDown={(event) => {
                        if (event.currentTarget === event.target) {
                            setShowEdit(false);
                        }
                    }}
                >
                    <div className="w-full max-w-md rounded-modal bg-bg-surface p-6 shadow-modal">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <p className="text-xs font-semibold uppercase text-text-muted">
                                    Rate configuration
                                </p>

                                <h2 className="mt-1 text-xl font-semibold text-text-primary">
                                    Change parking rate
                                </h2>

                                <p className="mt-1 text-sm text-text-secondary">
                                    New pricing will apply to new sessions.
                                    Existing sessions keep their current rate.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setShowEdit(false)}
                                className="grid h-9 w-9 shrink-0 place-items-center rounded-icon border border-border-default text-text-secondary transition hover:bg-bg-subtle hover:text-text-primary"
                                aria-label="Close"
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
                                    htmlFor="hourly_rate"
                                    className="mb-2 block text-xs font-medium text-text-secondary"
                                >
                                    Hourly rate ({currentPlan.currency})
                                </label>

                                <input
                                    id="hourly_rate"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={form.data.hourly_rate}
                                    onChange={(event) =>
                                        form.setData(
                                            "hourly_rate",
                                            event.target.value,
                                        )
                                    }
                                    className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none focus:border-border-strong focus:bg-bg-surface"
                                />

                                {form.errors.hourly_rate && (
                                    <p className="mt-2 text-xs text-status-rose">
                                        {form.errors.hourly_rate}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="grace_minutes"
                                    className="mb-2 block text-xs font-medium text-text-secondary"
                                >
                                    Grace period (minutes)
                                </label>

                                <input
                                    id="grace_minutes"
                                    type="number"
                                    min="0"
                                    max="1440"
                                    step="1"
                                    value={form.data.grace_minutes}
                                    onChange={(event) =>
                                        form.setData(
                                            "grace_minutes",
                                            event.target.value,
                                        )
                                    }
                                    className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none focus:border-border-strong focus:bg-bg-surface"
                                />

                                {form.errors.grace_minutes && (
                                    <p className="mt-2 text-xs text-status-rose">
                                        {form.errors.grace_minutes}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="daily_max"
                                    className="mb-2 block text-xs font-medium text-text-secondary"
                                >
                                    Daily maximum ({currentPlan.currency})
                                </label>

                                <input
                                    id="daily_max"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={form.data.daily_max}
                                    onChange={(event) =>
                                        form.setData(
                                            "daily_max",
                                            event.target.value,
                                        )
                                    }
                                    placeholder="No limit"
                                    className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none focus:border-border-strong focus:bg-bg-surface"
                                />

                                {form.errors.daily_max && (
                                    <p className="mt-2 text-xs text-status-rose">
                                        {form.errors.daily_max}
                                    </p>
                                )}
                            </div>

                            <div className="rounded-card bg-bg-subtle p-4">
                                <p className="text-xs font-semibold text-text-primary">
                                    How this works
                                </p>

                                <p className="mt-1 text-xs leading-5 text-text-secondary">
                                    Saving creates a new rate version. Parking
                                    sessions already in progress keep the rate
                                    they started with.
                                </p>
                            </div>

                            <div className="flex justify-end gap-3 border-t border-border-light pt-5">
                                <button
                                    type="button"
                                    onClick={() => setShowEdit(false)}
                                    className="rounded-pill px-5 py-3 text-sm font-medium text-text-secondary hover:bg-bg-subtle hover:text-text-primary"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="rounded-pill bg-action-dark px-5 py-3 text-sm font-semibold text-action-dark-foreground disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {form.processing
                                        ? "Saving..."
                                        : "Save rate"}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </>
    );
}

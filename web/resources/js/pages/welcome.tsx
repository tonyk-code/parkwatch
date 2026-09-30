import { useState } from "react";
import Logo from "@/components/Logo";
import { Link, usePage } from "@inertiajs/react";

type AuthProps = {
    auth: {
        user: {
            id: number;
            full_name: string;
            email: string;
            user_type: string;
            organization_id: number | null;
            is_active: boolean;
        } | null;
    };
};

export default function ParkWatchLanding() {
    const [menuOpen, setMenuOpen] = useState(false);
    const [slide, setSlide] = useState(0);
    const { auth } = usePage<AuthProps>().props;
    const isAuthed = Boolean(auth?.user);

    const authHref = isAuthed ? "/dashboard" : "/login";
    const authLabel = isAuthed ? "Dashboard" : "Login";

    const locations = [
        {
            name: "Redwood Reserve",
            place: "Northern California",
            status: "All clear",
            metric: "98% trails open",
            pos: "0%",
        },
        {
            name: "Mirror Lake Wetlands",
            place: "Grand Teton, Wyoming",
            status: "Habitat stable",
            metric: "42 species active",
            pos: "33.333%",
        },
        {
            name: "Summit Meadow",
            place: "Rocky Mountains",
            status: "Patrol active",
            metric: "12 zones monitored",
            pos: "66.666%",
        },
    ];

    const locationsImage =
        "https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=2000&q=85";

    const aerialImage =
        "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1600&q=85";

    return (
        <main className="overflow-hidden bg-[#f5f5f7] text-[#29272f]">
            <section className="relative mx-auto min-h-190 overflow-hidden bg-transparent lg:min-h-230">
                {/* Section-scoped animations (cloud drift, card float, live pulse, ticker marquee, bar grow) */}
                <style>{`
                                @keyframes pw-drift {
                                    0%, 100% { transform: translateX(0) translateY(0); }
                                    50% { transform: translateX(46px) translateY(-12px); }
                                }
                                @keyframes pw-float-soft {
                                    0%, 100% { transform: translateY(0) rotate(0deg); }
                                    50% { transform: translateY(-8px) rotate(0.5deg); }
                                }
                                @keyframes pw-pulse-ring {
                                    0% { transform: scale(1); opacity: 0.6; }
                                    100% { transform: scale(2.4); opacity: 0; }
                                }
                                @keyframes pw-marquee {
                                    from { transform: translateX(0); }
                                    to { transform: translateX(-50%); }
                                }
                                @keyframes pw-bar {
                                    from { transform: scaleY(0.15); }
                                    to { transform: scaleY(1); }
                                }
                                @media (prefers-reduced-motion: reduce) {
                                    [class*="pw-"] { animation: none !important; }
                                }
                            `}</style>

                {/* Sky wash + hero photo */}
                <div className="absolute inset-x-0 top-0 h-1/3 bg-linear-to-b from-[#7bc7e8] to-transparent" />
                <img
                    src={"/hero.png"}
                    alt="Park visitor center in a protected mountain landscape"
                    className="absolute inset-x-0 bottom-0 h-full w-full object-cover object-center"
                />

                <div className="pointer-events-none absolute left-[8%] top-[16%] size-64 rounded-full bg-white/60 blur-3xl animate-[pw-drift_22s_ease-in-out_infinite]" />
                <div className="pointer-events-none absolute right-[10%] top-[8%] size-80 rounded-full bg-[#f3d9ea]/50 blur-3xl animate-[pw-drift_28s_ease-in-out_infinite_reverse]" />
                <div className="pointer-events-none absolute left-[38%] top-[32%] size-96 rounded-full bg-white/40 blur-3xl animate-[pw-drift_34s_ease-in-out_infinite]" />

                <div className="absolute inset-x-0 bottom-0 h-40 bg-linear-to-t from-[#f5f5f7] to-transparent" />

                <header className="relative z-20 w-full border-b border-border-light bg-bg-surface/55 backdrop-blur-xl">
                    <div className="mx-auto flex w-[92%] max-w-7xl items-center justify-between py-4">
                        {/* BRAND */}
                        <a
                            href="#"
                            aria-label="ParkWatch home"
                            className="group flex items-center gap-3 text-text-primary"
                        >
                            <Logo
                                className="h-9 w-9 shadow-soft transition-transform duration-300 group-hover:-translate-y-0.5"
                                classNameIcon="bg-current"
                            />
                            <span className="font-editorial text-2xl leading-none">
                                ParkWatch
                            </span>
                        </a>
                
                        {/* DESKTOP NAV */}
                        <nav
                            aria-label="Primary navigation"
                            className="hidden items-center gap-1 rounded-pill border border-border-light bg-bg-surface/45 p-1.5 shadow-soft backdrop-blur-xl md:flex"
                        >
                            <a
                                href="#platform"
                                className="rounded-pill px-4 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-bg-surface/75 hover:text-text-primary"
                            >
                                Platform
                            </a>
                            <a
                                href="#insights"
                                className="rounded-pill px-4 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-bg-surface/75 hover:text-text-primary"
                            >
                                Insights
                            </a>
                            <a
                                href="#parks"
                                className="rounded-pill px-4 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-bg-surface/75 hover:text-text-primary"
                            >
                                Parks
                            </a>
                            <a
                                href="#contact"
                                className="rounded-pill px-4 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-bg-surface/75 hover:text-text-primary"
                            >
                                Contact
                            </a>
                        </nav>
                
                        {/* DESKTOP AUTH ACTION */}
                        <Link
                            href={authHref}
                            className="hidden rounded-pill bg-action-dark px-5 py-2.5 text-sm font-semibold text-action-dark-foreground shadow-cta transition-all duration-300 hover:-translate-y-0.5 hover:opacity-90 md:inline-flex"
                        >
                            {authLabel}
                        </Link>
                
                        {/* MOBILE MENU BUTTON */}
                        <button
                            type="button"
                            aria-label={menuOpen ? "Close menu" : "Open menu"}
                            aria-expanded={menuOpen}
                            onClick={() => setMenuOpen(!menuOpen)}
                            className="grid size-10 place-items-center rounded-icon border border-border-light bg-bg-surface/50 text-text-primary shadow-soft backdrop-blur-xl md:hidden"
                        >
                            {menuOpen ? (
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.8"
                                    className="size-5"
                                    aria-hidden="true"
                                >
                                    <path d="M6 6l12 12M18 6 6 18" />
                                </svg>
                            ) : (
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.8"
                                    className="size-5"
                                    aria-hidden="true"
                                >
                                    <path d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            )}
                        </button>
                    </div>
                
                    {/* MOBILE MENU */}
                    {menuOpen && (
                        <div className="absolute left-[4%] right-[4%] top-[calc(100%+0.75rem)] overflow-hidden rounded-3xl border border-border-light bg-bg-surface/90 p-5 text-text-primary shadow-dropdown backdrop-blur-xl md:hidden">
                            <div className="mb-3 flex items-center justify-between border-b border-border-light pb-4">
                                <span className="text-xs font-semibold uppercase text-text-muted">
                                    Navigation
                                </span>
                                <span className="font-editorial text-lg">ParkWatch</span>
                            </div>
                
                            <nav aria-label="Mobile navigation" className="flex flex-col">
                                {[
                                    ["Platform", "#platform"],
                                    ["Insights", "#insights"],
                                    ["Parks", "#parks"],
                                    ["Contact", "#contact"],
                                ].map(([label, href], index) => (
                                    <a
                                        key={href}
                                        href={href}
                                        onClick={() => setMenuOpen(false)}
                                        className={`flex items-center justify-between py-3.5 text-base font-medium transition-opacity hover:opacity-65 ${
                                            index < 3 ? "border-b border-border-light" : ""
                                        }`}
                                    >
                                        {label}
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="1.8"
                                            className="size-4 text-text-muted"
                                            aria-hidden="true"
                                        >
                                            <path d="M5 12h14M13 6l6 6-6 6" />
                                        </svg>
                                    </a>
                                ))}
                            </nav>
                
                            <Link
                                href={authHref}
                                onClick={() => setMenuOpen(false)}
                                className="mt-4 flex items-center justify-center rounded-pill bg-action-dark px-5 py-3 text-sm font-semibold text-action-dark-foreground shadow-cta"
                            >
                                {authLabel}
                            </Link>
                        </div>
                    )}
                </header>

                {/* HERO CONTENT */}
                <div className="relative z-10 mx-auto mt-10 max-w-4xl px-6 text-center md:mt-14">
                    <div className="inline-flex items-center gap-2 rounded-pill border border-black/10 bg-white/60 px-4 py-1.5 text-xs font-medium text-text-primary backdrop-blur-md">
                        <span className="relative flex size-2">
                            <span className="absolute inline-flex size-full rounded-full bg-status-green" />
                            <span className="relative inline-flex size-2 rounded-full bg-status-green" />
                        </span>
                        14 parks streaming live right now
                    </div>

                    <h1 className="mt-6 text-5xl font-medium leading-[1.05] text-text-primary md:text-7xl lg:text-8xl">
                        Discover healthier
                        <br />
                        parks{" "}
                        <em className="font-editorial font-medium">
                            in real time
                        </em>
                    </h1>

                    <p className="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-text-secondary md:text-base">
                        ParkWatch turns sensors, cameras and visitor data into
                        one live picture — so your team can see occupancy, air
                        quality and flow across every park, from anywhere.
                    </p>

                    {/* CTA ROW */}
                    <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                        <button
                            type="button"
                            className="inline-flex items-center gap-2 rounded-pill bg-action-dark px-6 py-3 text-sm font-medium text-action-dark-foreground shadow-cta transition-all duration-200 hover:-translate-y-0.5"
                            onClick={() =>
                                document
                                    .querySelector("#platform")
                                    ?.scrollIntoView({
                                        behavior: "smooth",
                                    })
                            }
                        >
                            Explore ParkWatch
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="1.8"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M5 12h14" />
                                <path d="m13 6 6 6-6 6" />
                            </svg>
                        </button>

                        <a
                            href="#platform"
                            className="inline-flex items-center gap-2 rounded-pill border border-black/10 bg-white/60 px-6 py-3 text-sm font-medium text-text-primary backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/80"
                        >
                            See how it works
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="1.8"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 5v14" />
                                <path d="m6 13 6 6 6-6" />
                            </svg>
                        </a>
                    </div>

                    {/* INLINE LIVE STATS */}
                    <div className="mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-xs text-text-secondary md:gap-x-8">
                        <span>
                            <b className="font-semibold text-text-primary">
                                12
                            </b>{" "}
                            parks live
                        </span>

                        <span className="text-black/20">•</span>

                        <span>
                            <b className="font-semibold text-text-primary">
                                2,842
                            </b>{" "}
                            visitors today
                        </span>

                        <span className="text-black/20">•</span>

                        <span>
                            <b className="font-semibold text-text-primary">
                                92
                            </b>{" "}
                            avg. health score
                        </span>
                    </div>
                </div>

                {/* PARK HEALTH CARD */}
                <div className="absolute left-[4%] top-[52%] z-10 hidden w-56 rounded-lg border border-white/55 bg-white/75 p-4 text-xs shadow-soft backdrop-blur-xl xl:block ">
                    <div className="mb-3 flex justify-between">
                        <span>Park health score</span>

                        <span className="flex items-center gap-1.5 text-status-green">
                            <span className="relative flex size-1.5">
                                <span className="absolute inline-flex size-full rounded-full bg-status-green animate-[pw-pulse-ring_2s_ease-out_infinite]" />
                                <span className="relative inline-flex size-1.5 rounded-full bg-status-green" />
                            </span>
                            Live
                        </span>
                    </div>

                    <div className="flex items-center gap-4">
                        <div className="grid size-20 place-items-center rounded-full border-12 border-[#eadff2] text-2xl font-semibold">
                            92
                        </div>

                        <div>
                            <b className="block">Excellent</b>

                            <span className="text-text-muted">
                                +4 this month
                            </span>
                        </div>
                    </div>
                </div>

                {/* AIR QUALITY CARD */}
                <div className="absolute right-[7%] top-[70%] z-10 hidden w-52 rounded-lg border border-white/55 bg-white/75 p-4 text-xs shadow-soft backdrop-blur-xl xl:block ">
                    <div className="mb-2 flex justify-between">
                        <span>Air quality</span>

                        <span className="font-semibold text-status-green">
                            Good
                        </span>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="grid size-12 place-items-center rounded-icon bg-status-green-bg font-semibold text-status-green">
                            38
                        </div>

                        <div>
                            <b className="block">AQI · PM2.5</b>

                            <span className="text-text-muted">
                                Fresh · light breeze
                            </span>
                        </div>
                    </div>
                </div>

                {/* VISITOR FLOW */}
                <div className="absolute right-[4%] top-[46%] z-10 hidden w-64 rounded-lg border border-white/55 bg-white/80 p-4 text-xs shadow-soft backdrop-blur-xl xl:block ">
                    <div className="mb-3 flex justify-between">
                        <span>Visitor flow</span>

                        <b className="text-status-green">▲ 12% today</b>
                    </div>

                    <div className="flex h-16 items-end gap-1">
                        {[55, 70, 92, 86, 62, 48, 34, 28, 23, 18].map(
                            (height, index) => (
                                <span
                                    key={index}
                                    className="flex-1 origin-bottom rounded-sm bg-[#7bc7e8] animate-[pw-bar_0.9s_ease-out_backwards]"
                                    style={{
                                        height: `${height}%`,
                                        animationDelay: `${index * 90}ms`,
                                    }}
                                />
                            ),
                        )}
                    </div>

                    <div className="mt-2 flex justify-between text-[10px] text-text-muted">
                        <span>8 AM</span>

                        <span>Now</span>
                    </div>
                </div>

                {/* LIVE PARK TICKER */}
                <div className="absolute inset-x-0 bottom-7 z-10 overflow-hidden">
                    <div className="mx-auto w-[92%] max-w-4xl mask-[linear-gradient(to_right,transparent,black_12%,black_88%,transparent)]">
                        <div className="flex w-max gap-3 animate-[pw-marquee_36s_linear_infinite]">
                            {[0, 1].map((copy) => (
                                <div
                                    key={copy}
                                    aria-hidden={copy === 1}
                                    className="flex gap-3 pr-3"
                                >
                                    {[
                                        {
                                            name: "Riverside Park",
                                            value: "62% occupied",
                                            dot: "bg-status-green",
                                        },
                                        {
                                            name: "Lakeview Gardens",
                                            value: "214 visitors now",
                                            dot: "bg-status-blue",
                                        },
                                        {
                                            name: "Cedar Trail",
                                            value: "Air quality · Good",
                                            dot: "bg-status-green",
                                        },
                                        {
                                            name: "Maple Commons",
                                            value: "18 spots free",
                                            dot: "bg-status-amber",
                                        },
                                        {
                                            name: "Willow Fields",
                                            value: "Health score 94",
                                            dot: "bg-status-purple",
                                        },
                                    ].map((park) => (
                                        <span
                                            key={`${copy}-${park.name}`}
                                            className="flex items-center gap-2 whitespace-nowrap rounded-pill border border-black/10 bg-white/70 px-4 py-2 text-xs text-text-primary shadow-soft backdrop-blur-md"
                                        >
                                            <span
                                                className={`size-1.5 rounded-full ${park.dot}`}
                                            />

                                            <b className="font-semibold">
                                                {park.name}
                                            </b>

                                            <span className="text-black/25">
                                                ·
                                            </span>

                                            {park.value}
                                        </span>
                                    ))}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            <section id="platform" className="px-5 py-20 md:px-10 lg:py-28">
                <div className="mb-14 grid gap-8 md:grid-cols-2 md:items-end">
                    <div>
                        <div className="mb-7 flex gap-2">
                            <span className="rounded-full bg-[#dff2dc] px-4 py-2 text-[10px] font-semibold uppercase text-[#285f39]">
                                Monitoring
                            </span>

                            <span className="rounded-full bg-[#eadff2] px-4 py-2 text-[10px] font-semibold uppercase text-[#684d79]">
                                Protection
                            </span>
                        </div>

                        <h2 className="text-5xl leading-[1] md:text-6xl">
                            Data-driven
                            <br />
                            park <em className="font-serif">insights</em>
                        </h2>
                    </div>

                    <p className="max-w-xl text-sm leading-7 text-[#77737d]">
                        ParkWatch brings trail conditions, visitor activity,
                        wildlife signals, and environmental readings into one
                        clear view—helping teams respond faster and protect what
                        matters.
                    </p>
                </div>

                {/* INSIGHTS */}
                <div
                    id="insights"
                    className="grid overflow-hidden rounded-2xl border-8 border-white bg-white shadow-[0_14px_40px_rgba(40,40,50,0.10)] lg:grid-cols-2"
                >
                    {/* LEFT */}
                    <div className="flex flex-col justify-center gap-5 p-7 md:p-10">
                        {[
                            {
                                number: "01",
                                title: "Environmental monitoring",
                                description:
                                    "Follow air quality, water levels, weather, and habitat health across every zone.",
                            },
                            {
                                number: "02",
                                title: "Predictive alerts",
                                description:
                                    "Spot unusual movement, crowding, fire risk, and maintenance needs before they escalate.",
                            },
                            {
                                number: "03",
                                title: "Actionable response",
                                description:
                                    "Give rangers clear priorities and live context so field teams can act with confidence.",
                            },
                        ].map((item, index) => (
                            <div
                                key={item.number}
                                className={
                                    index === 1
                                        ? "rounded-xl bg-[#2b2930] p-6 text-white"
                                        : "p-3"
                                }
                            >
                                <div className="flex gap-4">
                                    <span className="text-xs opacity-50">
                                        {item.number}
                                    </span>

                                    <div>
                                        <h3 className="mb-2 font-semibold">
                                            {item.title}
                                        </h3>

                                        <p className="text-xs leading-5 opacity-65">
                                            {item.description}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* RIGHT GRAPH */}
                    <div className="relative min-h-[430px] bg-gradient-to-br from-[#eadff2] via-white to-[#d9f0f8] p-8 md:p-14">
                        <div className="absolute inset-x-[12%] top-[13%] rounded-2xl border border-white/60 bg-white/80 p-6 shadow-[0_14px_40px_rgba(40,40,50,0.10)] backdrop-blur-xl">
                            <div className="mb-2 flex justify-between">
                                <span>Habitat health</span>

                                <span>•••</span>
                            </div>

                            <strong className="text-4xl">92.4%</strong>

                            <span className="ml-3 rounded-full bg-[#dff2dc] px-2 py-1 text-xs text-[#285f39]">
                                ↑ 8.4%
                            </span>

                            <div className="mt-10 flex h-32 items-end gap-2">
                                {[38, 55, 46, 73, 89, 58, 78].map(
                                    (height, index) => (
                                        <div
                                            key={index}
                                            className="flex-1 rounded-t-lg bg-[#7bc7e8]/50"
                                            style={{
                                                height: `${height}%`,
                                            }}
                                        />
                                    ),
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="parks" className="py-16 lg:py-24">
                <div className="mb-12 px-6 text-center">
                    <h2 className="text-5xl leading-none md:text-6xl">
                        Protected
                        <br />
                        <em className="font-serif">park landscapes</em>
                    </h2>

                    <p className="mx-auto mt-6 max-w-md text-sm leading-6 text-[#77737d]">
                        A live view of places under watch—from busy trailheads
                        to sensitive wetlands.
                    </p>
                </div>

                <div className="mx-auto max-w-7xl px-4">
                    <div className="grid gap-5 md:grid-cols-3">
                        {locations.map((location, index) => (
                            <article
                                key={location.name}
                                className={`rounded-2xl border-8 border-white bg-white p-2 shadow-[0_14px_40px_rgba(40,40,50,0.10)] transition-opacity ${
                                    slide === index
                                        ? "opacity-100"
                                        : "opacity-75"
                                }`}
                            >
                                <div className="relative aspect-[4/3] overflow-hidden rounded-xl">
                                    <img
                                        src={locationsImage}
                                        alt={location.name}
                                        loading="lazy"
                                        width={1536}
                                        height={768}
                                        className="h-full w-[300%] max-w-none object-cover"
                                        style={{
                                            transform: `translateX(-${location.pos})`,
                                        }}
                                    />

                                    <span className="absolute left-3 top-3 rounded-full bg-[#dff2dc] px-3 py-1 text-[10px] font-semibold text-[#285f39]">
                                        {location.status}
                                    </span>
                                </div>

                                <div className="p-4">
                                    <h3 className="text-xl font-semibold">
                                        {location.name}
                                    </h3>

                                    <p className="mt-1 text-xs text-[#77737d]">
                                        {location.place}
                                    </p>

                                    <div className="mt-5 flex items-center justify-between">
                                        <strong className="text-xl">
                                            {location.metric}
                                        </strong>

                                        <button
                                            type="button"
                                            className="h-9 rounded-full bg-[#2b2930] px-4 text-xs font-medium text-white shadow-[0_8px_20px_rgba(30,30,35,0.24)] transition hover:-translate-y-0.5"
                                        >
                                            View ↘
                                        </button>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>

                    {/* SLIDER CONTROLS */}
                    <div className="mt-8 flex justify-center gap-3">
                        <button
                            type="button"
                            aria-label="Previous park"
                            className="grid size-11 place-items-center rounded-full border border-[#dedde2] bg-white text-lg shadow-[0_14px_40px_rgba(40,40,50,0.10)] transition hover:-translate-y-0.5"
                            onClick={() => setSlide((slide + 2) % 3)}
                        >
                            ←
                        </button>

                        <button
                            type="button"
                            aria-label="Next park"
                            className="grid size-11 place-items-center rounded-full border border-[#dedde2] bg-white text-lg shadow-[0_14px_40px_rgba(40,40,50,0.10)] transition hover:-translate-y-0.5"
                            onClick={() => setSlide((slide + 1) % 3)}
                        >
                            →
                        </button>
                    </div>
                </div>
            </section>

            <section className="px-5 py-20 md:px-10">
                <div className="mb-16 text-center">
                    <span className="rounded-full bg-[#eadff2] px-4 py-2 text-[10px] font-semibold uppercase text-[#684d79]">
                        Park operations
                    </span>

                    <h2 className="mt-7 text-5xl leading-none md:text-6xl">
                        One platform, every signal,
                        <br />
                        <em className="font-serif">always connected</em>
                    </h2>

                    <p className="mx-auto mt-6 max-w-2xl text-sm leading-6 text-[#77737d]">
                        A calmer way to coordinate teams, understand park
                        conditions, and care for every acre.
                    </p>
                </div>

                <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    {/* UNIFIED DASHBOARD */}
                    <article className="min-h-80 overflow-hidden rounded-2xl bg-white p-7 shadow-[0_14px_40px_rgba(40,40,50,0.10)]">
                        <div className="mb-8 text-3xl">▥</div>

                        <h3 className="text-xl font-semibold">
                            Unified dashboard
                        </h3>

                        <p className="mt-2 text-sm text-[#77737d]">
                            Track incidents, occupancy, habitats, and field
                            teams in one place.
                        </p>

                        <div className="mt-8 flex h-24 items-end gap-2">
                            {[55, 75, 44, 92, 60, 82, 70].map(
                                (height, index) => (
                                    <span
                                        key={index}
                                        className="flex-1 rounded-t bg-[#eadff2]"
                                        style={{
                                            height: `${height}%`,
                                        }}
                                    />
                                ),
                            )}
                        </div>
                    </article>

                    {/* TEAM COORDINATION */}
                    <article className="min-h-80 rounded-2xl bg-[#2b2930] p-7 text-white shadow-[0_14px_40px_rgba(40,40,50,0.10)]">
                        <div className="mb-8 text-3xl">♟</div>

                        <h3 className="text-xl font-semibold">
                            Team coordination
                        </h3>

                        <p className="mt-2 text-sm opacity-60">
                            Connect dispatch, rangers, volunteers, and
                            maintenance crews.
                        </p>

                        <div className="relative mx-auto mt-9 grid size-36 place-items-center rounded-full border border-[#eadff2]/30">
                            <div className="grid size-24 place-items-center rounded-full border border-[#7bc7e8]/50">
                                <Logo
                                    className="h-12 w-12 bg-current"
                                    classNameIcon="bg-black"
                                />
                            </div>
                        </div>
                    </article>

                    {/* AERIAL IMAGE */}
                    <article className="relative row-span-2 min-h-152.5 overflow-hidden rounded-2xl bg-white shadow-[0_14px_40px_rgba(40,40,50,0.10)]">
                        <img
                            src={aerialImage}
                            alt="Protected park landscape"
                            loading="lazy"
                            width={1024}
                            height={1280}
                            className="absolute inset-0 h-full w-full object-cover"
                        />

                        <div className="absolute inset-x-0 top-0 bg-linear-to-b from-white via-white/90 to-transparent p-8 pb-28 text-center">
                            <span className="rounded-full bg-white px-3 py-1 text-[9px] uppercase shadow">
                                Sustainable
                            </span>

                            <h3 className="mt-6 text-2xl font-semibold">
                                Smarter parks.
                                <br />
                                Stronger ecosystems.
                            </h3>

                            <p className="mt-3 text-xs text-[#77737d]">
                                Better data helps every team protect more with
                                less.
                            </p>
                        </div>
                    </article>

                    {/* SCALE MONITORING */}
                    <article className="min-h-72 rounded-2xl bg-linear-to-br from-[#f6e2c9] to-[#eadff2] p-7 shadow-[0_14px_40px_rgba(40,40,50,0.10)] md:col-span-2">
                        <div className="grid gap-8 md:grid-cols-2 md:items-center">
                            <div>
                                <h3 className="text-2xl font-semibold">
                                    From one trail
                                    <br />
                                    to an entire park system
                                </h3>

                                <p className="mt-3 max-w-sm text-xs leading-5 text-[#77737d]">
                                    Scale monitoring from a single site to every
                                    park in your network without losing the
                                    detail that matters.
                                </p>

                                <button
                                    type="button"
                                    className="mt-8 h-11 rounded-full bg-[#2b2930] px-6 text-sm font-medium text-white shadow-[0_8px_20px_rgba(30,30,35,0.24)] transition-all hover:-translate-y-0.5"
                                >
                                    See the platform
                                </button>
                            </div>

                            <div className="space-y-3">
                                {[
                                    "Ranger team · North",
                                    "Visitor flow · Central",
                                    "Habitat sensor · East",
                                ].map((item, index) => (
                                    <div
                                        key={item}
                                        className="rounded-xl bg-white/80 p-4 shadow-[0_14px_40px_rgba(40,40,50,0.10)]"
                                    >
                                        <div className="flex justify-between text-xs">
                                            <b>{item}</b>

                                            <span>{[94, 81, 88][index]}%</span>
                                        </div>

                                        <div className="mt-3 flex gap-1">
                                            {Array.from({
                                                length: 12,
                                            }).map((_, barIndex) => (
                                                <span
                                                    key={barIndex}
                                                    className={`h-2 flex-1 rounded ${
                                                        barIndex <
                                                        ([11, 9, 10][index] ??
                                                            0)
                                                            ? "bg-[#dff2dc]"
                                                            : "bg-[#e7e6ea]"
                                                    }`}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <footer id="contact" className="px-7 pb-12 pt-16 md:px-12">
                <div className="grid gap-10 border-t border-[#dedde2] py-12 md:grid-cols-4">
                    <div>
                        <div className="flex items-center gap-3 text-xl font-semibold">
                            <Logo
                                className="h-9 w-9"
                                classNameIcon="bg-current"
                            />
                            parkwatch
                        </div>

                        <p className="mt-4 max-w-48 text-xs leading-5 text-[#77737d]">
                            From the first trail marker to the widest protected
                            landscape.
                        </p>
                    </div>

                    {/* FOOTER LINKS */}
                    {[
                        [
                            "Platform",
                            "Live map",
                            "Incident alerts",
                            "Visitor analytics",
                            "Team dispatch",
                        ],
                        [
                            "Resources",
                            "Park stories",
                            "Field guide",
                            "Reports",
                            "Support",
                        ],
                        [
                            "Company",
                            "About us",
                            "Partnerships",
                            "Careers",
                            "Contact",
                        ],
                    ].map(([heading, ...links]) => (
                        <div key={heading}>
                            <h4 className="mb-5 text-sm font-semibold">
                                {heading}
                            </h4>

                            {links.map((link) => (
                                <a
                                    href="#"
                                    key={link}
                                    className="mb-3 block text-xs text-[#77737d] transition hover:text-[#29272f]"
                                >
                                    {link}
                                </a>
                            ))}
                        </div>
                    ))}
                </div>

                <div className="flex flex-col justify-between gap-4 border-t border-[#dedde2] pt-7 text-[10px] text-[#77737d] sm:flex-row">
                    <span>Terms of use &nbsp;&nbsp; Privacy policy</span>

                    <span>© 2026 ParkWatch. All rights reserved.</span>
                </div>
            </footer>
        </main>
    );
}

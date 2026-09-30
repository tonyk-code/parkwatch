import { Link, usePage } from "@inertiajs/react";
import type { ReactNode } from "react";
import type { Auth } from "@/types/auth";
import Logo from "../Logo";

type Props = {
    auth: Auth;
};

type NavigationItem = {
    label: string;
    href?: string;
    available: boolean;
    icon: ReactNode;
};

function Icon({ d }: { d: string }) {
    return (
        <svg
            className="h-[18px] w-[18px] shrink-0"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.8"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <path d={d} />
        </svg>
    );
}

const icons = {
    dashboard: (
        <Icon d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z" />
    ),
    live: <Icon d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2" />,
    sessions: (
        <Icon d="M5 3h14a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM8 8h8M8 12h8M8 16h5" />
    ),
    reservations: <Icon d="M6 3h12v18l-6-4-6 4z" />,
    sites: (
        <Icon d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 9h3a1 1 0 0 1 1 1v11M2 21h20M8 7h4M8 11h4M8 15h4" />
    ),
    cameras: <Icon d="M15 10l5-3v10l-5-3M3 7h12v10H3z" />,
    rates: (
        <Icon d="M12 3v18M17 7.5c0-1.7-2.2-3-5-3s-5 1.3-5 3 2.2 3 5 3 5 1.3 5 3-2.2 3-5 3-5-1.3-5-3" />
    ),
};

export default function AppSidebar({ auth }: Props) {
    const { url } = usePage();

    const user = auth.user;

    if (!user) {
        return null;
    }

    const role = user.user_type;
    const isOperator = role === "owner" || role === "staff";
    const isOwner = role === "owner";
    const canManageRates = isOwner || user.is_manager;

    const operations: NavigationItem[] = [
        {
            label: "Dashboard",
            href: "/dashboard",
            available: true,
            icon: icons.dashboard,
        },
        {
            label: "Live Parking",
            href: "/live",
            available: isOperator,
            icon: icons.live,
        },
        {
            label: "Sessions",
            href: "/sessions",
            available: isOperator,
            icon: icons.sessions,
        },
        {
            label: "Reservations",
            href: "/reservations",
            available: isOperator,
            icon: icons.reservations,
        },
    ];

    const management: NavigationItem[] = [
        {
            label: "Sites",
            href: "/sites",
            available: isOwner,
            icon: icons.sites,
        },
        {
            label: "Cameras",
            href: "/cameras",
            available: isOwner,
            icon: icons.cameras,
        },
        {
            label: "Rates",
            href: "/rates",
            available: canManageRates,
            icon: icons.rates,
        },
    ];

    const roleLabel =
        role === "owner" ? "Owner" : role === "staff" ? "Staff" : "Customer";

    const initials = user.full_name
        .split(" ")
        .map((part) => part[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();

    function isItemActive(href: string): boolean {
        if (href === "/dashboard") {
            return url === "/dashboard";
        }

        if (href === "/live") {
            return url === "/live" || /^\/sites\/[^/]+\/live$/.test(url);
        }

        return url === href || url.startsWith(`${href}/`);
    }

    function renderItem(item: NavigationItem) {
        if (!item.available || !item.href) {
            return null;
        }

        const active = isItemActive(item.href);

        return (
            <Link
                key={item.label}
                href={item.href}
                className={[
                    "flex items-center gap-3 rounded-pill px-4 py-2.5 text-sm font-medium transition-colors",
                    active
                        ? "bg-bg-surface text-text-primary shadow-nav-active"
                        : "text-text-primary hover:bg-bg-surface/70",
                ].join(" ")}
            >
                {item.icon}
                <span className="truncate">{item.label}</span>
            </Link>
        );
    }

    return (
        <aside className="hidden w-64 shrink-0 flex-col px-4 py-5 lg:flex">
            <div className="flex items-center justify-between px-1">
                <Logo classNameIcon="bg-current" />

                <span className="text-sm font-semibold tracking-tight text-text-primary">
                    ParkWatch
                </span>
            </div>

            <nav className="mt-8 flex-1 space-y-8">
                <div>
                    <p className="mb-3 px-1 text-xs text-text-secondary">
                        Operations
                    </p>

                    <div className="space-y-1">
                        {operations.map(renderItem)}
                    </div>
                </div>

                <div>
                    <p className="mb-3 px-1 text-xs text-text-secondary">
                        Management
                    </p>

                    <div className="space-y-1">
                        {management.map(renderItem)}
                    </div>
                </div>
            </nav>

            <div className="pt-4">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    className="flex w-full items-center gap-3 rounded-pill px-4 py-2.5 text-left text-sm font-medium text-text-primary transition-colors hover:bg-status-rose-bg hover:text-status-rose"
                >
                    <Icon d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                    Sign out
                </Link>

                <p className="mb-3 mt-6 px-1 text-xs text-text-secondary">
                    Account
                </p>

                <div className="flex min-w-0 items-center gap-3 px-1">
                    <div className="grid h-11 w-11 shrink-0 place-items-center rounded-pill bg-bg-surface text-sm font-semibold text-text-primary shadow-nav-active">
                        {initials}
                    </div>

                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold text-text-primary">
                            {user.full_name}
                        </p>

                        <p className="truncate text-xs text-text-secondary">
                            {roleLabel}
                        </p>
                    </div>
                </div>
            </div>
        </aside>
    );
}

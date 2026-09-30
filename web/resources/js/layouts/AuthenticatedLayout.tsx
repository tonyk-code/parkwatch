import { usePage } from "@inertiajs/react";
import type { ReactNode } from "react";
import type { Auth } from "@/types/auth";
import AppSidebar from "@/components/navigation/AppSidebar";

type Props = {
    children: ReactNode;
    breadcrumbs?: string[];
};

type SharedPageProps = {
    auth: Auth;
};

function IconButton({
    children,
    dot = false,
}: {
    children: ReactNode;
    dot?: boolean;
}) {
    return (
        <button
            type="button"
            className="relative grid h-10 w-10 place-items-center rounded-icon border border-border-default bg-bg-surface text-text-primary transition-colors hover:bg-bg-subtle"
        >
            {children}
            {dot && (
                <span className="absolute -bottom-0.5 right-1 h-1.5 w-1.5 rounded-pill bg-accent-gold" />
            )}
        </button>
    );
}

export default function AuthenticatedLayout({
    children,
    breadcrumbs = ["ParkWatch", "Dashboard"],
}: Props) {
    const { auth } = usePage<SharedPageProps>().props;

    return (
        <div className="h-screen bg-bg-canvas font-sans text-text-primary overflow-hidden">
            <div className="flex h-screen  bg-bg-sidebar p-2 shadow-shell">
                <AppSidebar auth={auth} />

                <main className="flex min-w-0 flex-1 flex-col rounded-[2rem] bg-bg-surface p-3 sm:p-4 ">
                    <header className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-4 rounded-card border border-border-default px-4 py-3 sm:px-5">
                        <nav className="flex min-w-0 items-center gap-2 text-sm">
                            {breadcrumbs.map((crumb, i) => {
                                const last = i === breadcrumbs.length - 1;
                                return (
                                    <span
                                        key={crumb}
                                        className="flex min-w-0 items-center gap-2"
                                    >
                                        <span
                                            className={
                                                last
                                                    ? "truncate font-medium text-text-primary"
                                                    : "truncate text-text-secondary"
                                            }
                                        >
                                            {crumb}
                                        </span>
                                        {!last && (
                                            <svg
                                                className="h-3.5 w-3.5 shrink-0 text-text-muted"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                strokeWidth="2"
                                            >
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        )}
                                    </span>
                                );
                            })}
                        </nav>

                        <div className="flex shrink-0 items-center gap-2">
                            <IconButton>
                                <svg
                                    className="h-[18px] w-[18px]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.8"
                                >
                                    <path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                </svg>
                            </IconButton>
                            <IconButton dot>
                                <svg
                                    className="h-[18px] w-[18px]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.8"
                                >
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                            </IconButton>
                            <IconButton>
                                <svg
                                    className="h-[18px] w-[18px]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.8"
                                >
                                    <circle cx="11" cy="11" r="7" />
                                    <path d="m20 20-3.5-3.5" />
                                </svg>
                            </IconButton>
                        </div>
                    </header>

                    <div className="min-h-0 flex-1 overflow-y-scroll [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-button]:hidden [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-border-default">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}

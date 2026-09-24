import { Head, Link, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import { useState } from "react";

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
    error?: string;
};

export default function Login() {
    const [showPassword, setShowPassword] = useState(false);
    const form = useForm<LoginForm>({
        email: "",
        password: "",
        remember: false,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.post("/login", {
            onFinish: () => {
                form.reset("password");
            },
        });
    }

    return (
        <>
            <Head title="Login" />

            <main className="relative min-h-screen w-full bg-[radial-gradient(ellipse_at_top_right,var(--color-accent-gold-soft),transparent_60%),var(--color-bg-canvas)] antialiased selection:bg-status-amber-bg selection:text-text-primary">
                <div className="relative flex min-h-screen w-full overflow-hidden bg-bg-canvas p-3 shadow-app sm:p-4 lg:flex-row lg:p-5">
                    <div className="flex w-full flex-col justify-between rounded-modal bg-transparent p-6 sm:p-8 lg:w-1/2 lg:p-10">
                        <div className="flex items-center justify-between">
                            <div className="inline-flex items-center gap-2 rounded-pill border border-border-light bg-bg-subtle px-4 py-2 shadow-nav-active">
                                <span className="h-2 w-2 rounded-pill bg-action-dark" />
                                <span className="text-xs font-semibold text-text-primary">
                                    ParkWatch
                                </span>
                            </div>

                            <span className="hidden text-xs font-medium text-text-muted sm:block">
                                Operations Platform
                            </span>
                        </div>

                        <div className="w-full max-w-md py-12 sm:py-16 lg:mx-0">
                            <div className="mb-8 text-center">
                                <p className="mb-3 text-xs font-semibold text-text-muted">
                                    Staff Access
                                </p>

                                <h1 className="text-3xl font-semibold text-text-primary lg:text-[2rem]">
                                    Welcome back
                                </h1>

                                <p className="mt-2 max-w-sm text-sm leading-6 text-text-secondary">
                                    Sign in to monitor parking operations,
                                    manage sites, and keep every parking space
                                    running smoothly.
                                </p>
                            </div>

                            <form onSubmit={submit} className="space-y-5">
                                <div className="space-y-2">
                                    <label
                                        htmlFor="email"
                                        className="block px-1 text-xs font-medium text-text-secondary"
                                    >
                                        Email
                                    </label>

                                    <input
                                        id="email"
                                        type="email"
                                        value={form.data.email}
                                        onChange={(event) =>
                                            form.setData(
                                                "email",
                                                event.target.value,
                                            )
                                        }
                                        placeholder="name@company.com"
                                        autoComplete="email"
                                        autoFocus
                                        className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none transition duration-200 placeholder:text-text-muted focus:border-border-strong focus:bg-bg-surface focus:ring-4 focus:ring-status-blue-bg/60"
                                    />

                                    {form.errors.email && (
                                        <p className="rounded-pill border border-status-rose/20 bg-status-rose-bg px-4 py-2 text-xs font-medium text-status-rose">
                                            {form.errors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <label
                                        htmlFor="password"
                                        className="block px-1 text-xs font-medium text-text-secondary"
                                    >
                                        Password
                                    </label>

                                    <div className="relative">
                                        <input
                                            id="password"
                                            type={
                                                showPassword
                                                    ? "text"
                                                    : "password"
                                            }
                                            value={form.data.password}
                                            onChange={(event) =>
                                                form.setData(
                                                    "password",
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="Enter your password"
                                            autoComplete="current-password"
                                            className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 pr-14 text-sm text-text-primary outline-none transition duration-200 placeholder:text-text-muted focus:border-border-strong focus:bg-bg-surface focus:ring-4 focus:ring-status-blue-bg/60"
                                        />

                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowPassword(
                                                    (current) => !current,
                                                )
                                            }
                                            className="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted transition-colors hover:text-text-primary focus:outline-none"
                                            aria-label={
                                                showPassword
                                                    ? "Hide password"
                                                    : "Show password"
                                            }
                                            aria-pressed={showPassword}
                                        >
                                            {showPassword ? (
                                                <svg
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                >
                                                    <path
                                                        d="M3 3l18 18M10.58 10.58a2 2 0 002.83 2.83M9.36 5.11A9.77 9.77 0 0112 5c5 0 9 4 10 7-.4 1.2-1.16 2.5-2.24 3.66M6.6 6.6C4.4 8.1 2.8 10.1 2 12c1 3 5 7 10 7 1.26 0 2.46-.24 3.56-.67"
                                                        stroke="currentColor"
                                                        strokeWidth="1.6"
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                    />
                                                </svg>
                                            ) : (
                                                <svg
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                >
                                                    <path
                                                        d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z"
                                                        stroke="currentColor"
                                                        strokeWidth="1.6"
                                                        strokeLinejoin="round"
                                                    />
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="3"
                                                        stroke="currentColor"
                                                        strokeWidth="1.6"
                                                    />
                                                </svg>
                                            )}
                                        </button>
                                    </div>

                                    {form.errors.password && (
                                        <p className="rounded-pill border border-status-rose/20 bg-status-rose-bg px-4 py-2 text-xs font-medium text-status-rose">
                                            {form.errors.password}
                                        </p>
                                    )}
                                </div>

                                <div className="flex items-center justify-between px-1 pt-1">
                                    <label className="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-text-secondary transition-colors hover:text-text-primary">
                                        <input
                                            type="checkbox"
                                            checked={form.data.remember}
                                            onChange={(event) =>
                                                form.setData(
                                                    "remember",
                                                    event.target.checked,
                                                )
                                            }
                                            className="h-4 w-4 cursor-pointer rounded border-border-default accent-action-dark focus:ring-0 focus:ring-offset-0"
                                        />
                                        Remember me
                                    </label>

                                    <Link
                                        href="/forgot-password"
                                        className="text-xs font-medium text-text-secondary transition-colors hover:text-text-primary"
                                    >
                                        Forgot password?
                                    </Link>
                                </div>

                                {form.errors.error && (
                                    <div className="rounded-pill border border-status-rose/20 bg-status-rose-bg px-4 py-2 text-center text-xs font-medium text-status-rose">
                                        {form.errors.error}
                                    </div>
                                )}

                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="w-full rounded-pill bg-accent-gold px-6 py-3.5 text-sm font-semibold text-accent-gold-text shadow-nav-active transition-all duration-200 hover:bg-accent-gold-hover focus:outline-none focus:ring-2 focus:ring-accent-gold focus:ring-offset-2 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {form.processing
                                        ? "Signing in..."
                                        : "Sign in to ParkWatch"}
                                </button>

                                <div className="flex items-center gap-3 pt-1">
                                    <span className="h-px flex-1 bg-border-light" />
                                    <span className="text-[11px] font-medium text-text-muted">
                                        or continue with
                                    </span>
                                    <span className="h-px flex-1 bg-border-light" />
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        className="flex items-center justify-center gap-2 rounded-pill border border-border-default bg-bg-surface px-4 py-3 text-xs font-semibold text-text-primary transition-colors hover:bg-bg-subtle"
                                    >
                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                        >
                                            <path d="M16.365 1.43c0 1.14-.468 2.187-1.22 2.98-.845.885-2.212 1.57-3.313 1.48-.144-1.108.42-2.28 1.18-3.04.83-.84 2.242-1.46 3.353-1.42zM20.47 17.36c-.52 1.2-.77 1.73-1.44 2.79-.94 1.49-2.26 3.34-3.9 3.36-1.46.02-1.84-.95-3.82-.94-1.98.01-2.4.96-3.86.94-1.64-.02-2.9-1.69-3.84-3.18C1.16 16.8.4 12.94 2.02 10.4c1.13-1.77 2.9-2.8 4.56-2.8 1.7 0 2.77 1 4.18 1 1.37 0 2.19-1 4.18-1 1.48 0 3.05.8 4.16 2.19-3.66 2-3.06 7.22 1.37 7.57z" />
                                        </svg>
                                        Apple
                                    </button>

                                    <button
                                        type="button"
                                        className="flex items-center justify-center gap-2 rounded-pill border border-border-default bg-bg-surface px-4 py-3 text-xs font-semibold text-text-primary transition-colors hover:bg-bg-subtle"
                                    >
                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                fill="#4285F4"
                                                d="M23.52 12.27c0-.85-.08-1.66-.22-2.44H12v4.62h6.47c-.28 1.5-1.13 2.78-2.4 3.63v3h3.88c2.27-2.09 3.57-5.17 3.57-8.81z"
                                            />
                                            <path
                                                fill="#34A853"
                                                d="M12 24c3.24 0 5.96-1.07 7.95-2.92l-3.88-3c-1.08.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.95H1.26v3.1C3.24 21.3 7.28 24 12 24z"
                                            />
                                            <path
                                                fill="#FBBC05"
                                                d="M5.27 14.28A7.2 7.2 0 014.9 12c0-.79.14-1.56.37-2.28V6.62H1.26A11.98 11.98 0 000 12c0 1.93.46 3.76 1.26 5.38l4.01-3.1z"
                                            />
                                            <path
                                                fill="#EA4335"
                                                d="M12 4.77c1.76 0 3.34.6 4.58 1.79l3.44-3.44C17.95 1.19 15.24 0 12 0 7.28 0 3.24 2.7 1.26 6.62l4.01 3.1C6.22 6.88 8.87 4.77 12 4.77z"
                                            />
                                        </svg>
                                        Google
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div className="flex items-center justify-between border-t border-border-light pt-4">
                            <p className="text-xs text-text-muted">
                                Secure access for ParkWatch operations
                            </p>

                            <Link
                                href="/terms"
                                className="text-xs font-medium text-text-secondary underline decoration-border-strong underline-offset-4 transition-colors hover:text-text-primary"
                            >
                                Terms & Conditions
                            </Link>
                        </div>
                    </div>

                    <div className="relative mt-3 hidden min-h-140 w-full lg:mt-0 lg:flex lg:w-1/2">
                        <div
                            className="absolute inset-0 overflow-hidden rounded-modal bg-action-dark"
                            style={{
                                WebkitMaskRepeat: "no-repeat",
                                maskRepeat: "no-repeat",
                            }}
                        >
                            <img
                                src="https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=1200&q=85"
                                alt="Parking facility"
                                className="absolute inset-0 h-full w-full object-cover"
                            />

                            <div className="absolute inset-0 bg-black/45" />

                            <div className="absolute left-8 top-1/2 z-10 max-w-sm -translate-y-1/2">
                                <p className="mb-3 text-xs font-semibold text-white/60">
                                    Smart Parking Management
                                </p>

                                <h2 className="text-3xl font-semibold leading-tight text-white xl:text-4xl">
                                    See every parking space.
                                    <br />
                                    Manage every operation.
                                </h2>

                                <p className="mt-4 max-w-md text-sm leading-6 text-white/70">
                                    ParkWatch connects parking sites, occupancy
                                    monitoring, attendants, reservations, and
                                    payments in one operational workspace.
                                </p>
                            </div>

                            <div className="absolute bottom-8 left-6 right-6 z-10 space-y-3">
                                <div className="rounded-modal border border-white/20 bg-white/10 p-3 shadow-modal backdrop-blur-xl">
                                    <div className="grid grid-cols-7 text-center">
                                        {[
                                            "Sun",
                                            "Mon",
                                            "Tue",
                                            "Wed",
                                            "Thu",
                                            "Fri",
                                            "Sat",
                                        ].map((day, i) => (
                                            <div
                                                key={day}
                                                className="flex flex-col items-center"
                                            >
                                                <span className="text-[10px] font-light text-white/70">
                                                    {day}
                                                </span>
                                                <div
                                                    className={`mt-1 flex h-7 w-7 items-center justify-center rounded-lg text-xs font-semibold ${
                                                        i === 2
                                                            ? "border border-white/50 bg-white/30 text-white"
                                                            : "text-white/85"
                                                    }`}
                                                >
                                                    {22 + i}
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div
                                        className="mt-2 h-3 rounded-lg opacity-30"
                                        style={{
                                            backgroundImage:
                                                "repeating-linear-gradient(45deg, rgba(255,255,255,0.8), rgba(255,255,255,0.8) 2px, transparent 2px, transparent 6px)",
                                        }}
                                    />
                                </div>

                                <div className="w-52.5 rounded-modal border border-white/40 bg-bg-surface/95 p-3.5 shadow-modal backdrop-blur-md">
                                    <div className="flex items-center justify-between">
                                        <span className="text-xs font-semibold text-text-primary">
                                            Bole Site — Live
                                        </span>
                                        <span className="h-1.5 w-1.5 rounded-pill bg-status-green" />
                                    </div>

                                    <p className="mt-0.5 text-[10px] font-medium text-text-muted">
                                        43 / 60 spots occupied
                                    </p>

                                    <div className="mt-2.5 h-1.5 overflow-hidden rounded-pill bg-bg-tag">
                                        <div className="h-full w-[72%] rounded-pill bg-accent-gold" />
                                    </div>

                                    <div className="mt-3 flex -space-x-1.5">
                                        <img
                                            src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=facearea&facepad=2&w=80&h=80&q=80"
                                            alt=""
                                            className="h-5 w-5 rounded-pill object-cover ring-1 ring-white"
                                        />
                                        <img
                                            src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=facearea&facepad=2&w=80&h=80&q=80"
                                            alt=""
                                            className="h-5 w-5 rounded-pill object-cover ring-1 ring-white"
                                        />
                                        <img
                                            src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=facearea&facepad=2&w=80&h=80&q=80"
                                            alt=""
                                            className="h-5 w-5 rounded-pill object-cover ring-1 ring-white"
                                        />
                                        <img
                                            src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=facearea&facepad=2&w=80&h=80&q=80"
                                            alt=""
                                            className="h-5 w-5 rounded-pill object-cover ring-1 ring-white"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </>
    );
}

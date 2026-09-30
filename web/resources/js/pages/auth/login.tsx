import Logo from "@/components/Logo";
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

            <main className="relative flex min-h-screen w-full items-center justify-center antialiased selection:bg-status-amber-bg selection:text-text-primary ">
                <div className="relative flex min-h-screen w-full overflow-hidden bg-bg-surface/80 p-3 shadow-app backdrop-blur-md sm:p-4 lg:flex-row lg:p-5">
                    <div className="flex w-full flex-col justify-between rounded-modal bg-transparent p-6 sm:p-8 lg:w-1/2 lg:p-10">
                        <div className="flex items-center justify-between">
                            <div className="inline-flex items-center gap-2 ">
                                <Logo classNameIcon="bg-current" />
                                <span className="text-sm font-semibold text-text-primary">
                                    ParkWatch
                                </span>
                            </div>

                            <span className="hidden text-xs font-medium text-text-muted sm:block">
                                Operations Platform
                            </span>
                        </div>

                        <div className="mx-auto  w-full max-w-md py-8 sm:py-12 ">
                            <div className="mb-8 text-center">
                                <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-text-muted">
                                    Staff Access
                                </p>

                                <h1 className="text-3xl font-semibold text-text-primary lg:text-[2rem]">
                                    Welcome back
                                </h1>

                                <p className="mt-2 text-sm leading-6 text-text-secondary">
                                    Sign in to monitor parking operations,
                                    manage sites, and keep every parking space
                                    running smoothly.
                                </p>
                            </div>

                            <form onSubmit={submit} className="space-y-5">
                                {/* Email Field */}
                                <div className="space-y-1.5">
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
                                        onChange={(event) => {
                                            form.setData(
                                                "email",
                                                event.target.value,
                                            );
                                            if (form.errors.email)
                                                form.clearErrors("email");
                                        }}
                                        placeholder="name@company.com"
                                        autoComplete="email"
                                        autoFocus
                                        className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 text-sm text-text-primary outline-none transition-all duration-200 placeholder:text-text-muted focus:border-border-strong focus:bg-bg-surface focus:ring-2 focus:ring-input"
                                    />

                                    {form.errors.email && (
                                        <p className="mt-1 rounded-pill border border-status-rose/20 bg-status-rose-bg px-4 py-1.5 text-xs font-medium text-status-rose">
                                            {form.errors.email}
                                        </p>
                                    )}
                                </div>

                                {/* Password Field */}
                                <div className="space-y-1.5">
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
                                            onChange={(event) => {
                                                form.setData(
                                                    "password",
                                                    event.target.value,
                                                );
                                                if (form.errors.password)
                                                    form.clearErrors(
                                                        "password",
                                                    );
                                            }}
                                            placeholder="Enter your password"
                                            autoComplete="current-password"
                                            className="w-full rounded-pill border border-border-default bg-bg-subtle px-5 py-3.5 pr-14 text-sm text-text-primary outline-none transition-all duration-200 placeholder:text-text-muted focus:border-border-strong focus:bg-bg-surface focus:ring-2 focus:ring-input"
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
                                        <p className="mt-1 rounded-pill border border-status-rose/20 bg-status-rose-bg px-4 py-1.5 text-xs font-medium text-status-rose">
                                            {form.errors.password}
                                        </p>
                                    )}
                                </div>

                                {/* Remember Me & Forgot Password */}
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

                                {/* General Form Error */}
                                {form.errors.error && (
                                    <div className="rounded-pill border border-status-rose/20 bg-status-rose-bg px-4 py-2 text-center text-xs font-medium text-status-rose">
                                        {form.errors.error}
                                    </div>
                                )}

                                {/* Submit Button */}
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="w-full rounded-pill bg-[#2b2930] text-sm font-medium text-white shadow-[0_8px_20px_rgba(30,30,35,0.24)] transition-all duration-200 hover:-translate-y-0.5 px-6 py-3.5 focus:outline-none focus:ring-2 focus:ring-accent-gold focus:ring-offset-2 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {form.processing
                                        ? "Signing in..."
                                        : "Sign in to ParkWatch"}
                                </button>
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

                    <div className="relative hidden min-h-125 w-full overflow-hidden rounded-modal bg-action-dark lg:flex lg:w-1/2">
                        <img
                            src="https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=1200&q=85"
                            alt="Parking facility"
                            className="absolute inset-0 h-full w-full object-cover opacity-80"
                        />
                        <div className="absolute inset-0 bg-linear-to-t from-black/80 via-black/40 to-black/20" />

                        <div className="relative z-10 flex h-full flex-col justify-between p-10 text-white">
                            <div className="max-w-sm pt-8">
                                <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-white/60">
                                    Smart Parking Management
                                </p>

                                <h2 className="text-3xl font-semibold leading-tight xl:text-4xl">
                                    See every parking space.
                                    <br />
                                    Manage every operation.
                                </h2>

                                <p className="mt-4 text-sm leading-6 text-white/70">
                                    ParkWatch connects parking sites, occupancy
                                    monitoring, attendants, reservations, and
                                    payments in one operational workspace.
                                </p>
                            </div>

                            {/* Calendar Widget Overlay */}
                            <div className="w-full rounded-modal border border-white/20 bg-white/10 p-4 shadow-modal backdrop-blur-xl">
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
                                            <span className="text-[10px] font-medium text-white/70">
                                                {day}
                                            </span>
                                            <div
                                                className={`mt-1 flex h-7 w-7 items-center justify-center rounded-lg text-xs font-semibold transition-colors ${
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
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </>
    );
}

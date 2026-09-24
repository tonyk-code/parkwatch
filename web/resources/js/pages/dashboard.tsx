import { Head, useForm } from "@inertiajs/react";

export default function Dashboard() {
    const { post, processing } = useForm({});

    function logout() {
        post("/logout");
    }

    return (
        <>
            <Head title="Dashboard" />

            <div className="min-h-screen bg-bg-canvas p-8">
                <div className="mx-auto max-w-7xl">
                    <div className="flex items-center justify-between rounded-app bg-bg-surface p-8 shadow-app">
                        <div>
                            <p className="text-sm text-text-secondary">
                                ParkWatch
                            </p>

                            <h1 className="mt-2 text-3xl font-semibold text-text-primary">
                                Dashboard
                            </h1>

                            <p className="mt-2 text-text-secondary">
                                Authentication is working.
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={logout}
                            disabled={processing}
                            className="rounded-pill bg-action-dark px-5 py-2.5 text-sm font-medium text-action-dark-text transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {processing ? "Signing out..." : "Sign out"}
                        </button>
                    </div>
                </div>
            </div>
        </>
    );
}

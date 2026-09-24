import { Head } from "@inertiajs/react";

type Site = {
    id: number;
    name: string;
    code: string;
};

type Props = {
    site: Site;
};

export default function SiteDashboard({ site }: Props) {
    return (
        <>
            <Head title={`${site.name} - ParkWatch`} />

            <main className="min-h-screen bg-bg-canvas p-8">
                <div className="mx-auto max-w-7xl">
                    <div className="rounded-app bg-bg-surface p-8 shadow-app">
                        <p className="text-xs font-medium uppercase tracking-wide text-text-muted">
                            Site
                        </p>

                        <h1 className="mt-2 text-3xl font-semibold text-text-primary">
                            {site.name}
                        </h1>

                        <p className="mt-2 text-sm text-text-secondary">
                            Site code: {site.code}
                        </p>
                    </div>
                </div>
            </main>
        </>
    );
}

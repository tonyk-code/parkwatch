<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $isManager = false;

        if ($user && $user->user_type->value === 'staff') {
            $isManager = $user->staffAssignments()
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where(function ($query) {
                    $query
                        ->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->where('role_name', 'manager')
                ->exists();
        }

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'user_type' => $user->user_type->value,
                    'organization_id' => $user->organization_id,
                    'is_active' => $user->is_active,
                    'is_manager' => $isManager,
                ] : null,
            ],
        ];
    }
}

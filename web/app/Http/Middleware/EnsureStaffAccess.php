<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (!$user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        if ($user->user_type === UserType::Customer) {
            abort(403, 'Customers cannot access the staff portal.');
        }

        if (
            $user->user_type === UserType::Staff &&
            !$user->staffAssignments()
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where(function ($query) {
                    $query
                        ->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->exists()
        ) {
            abort(403, 'You are not assigned to an active parking site.');
        }

        return $next($request);
    }
}
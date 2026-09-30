<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\ParkingSession;
use App\Models\Site;
use App\Models\User;

class ParkingSessionPolicy
{
    public function view(User $user, ParkingSession $session): bool
    {
        if (!$user->is_active) {
            return false;
        }

        $site = $session->site;

        if (!$site || $user->organization_id !== $site->organization_id) {
            return false;
        }

        if ($user->user_type === UserType::Owner) {
            return true;
        }

        if ($user->user_type !== UserType::Staff) {
            return false;
        }

        return $user->hasActiveSiteAssignment($site->id);
    }

    public function create(User $user, Site $site): bool
    {
        if (!$user->is_active) {
            return false;
        }

        if ($user->organization_id !== $site->organization_id) {
            return false;
        }

        if ($user->user_type === UserType::Owner) {
            return true;
        }

        if ($user->user_type !== UserType::Staff) {
            return false;
        }

        return $user->hasActiveSiteAssignment($site->id);
    }

    public function close(User $user, ParkingSession $session): bool
    {
        if (!$this->view($user, $session)) {
            return false;
        }

        if ($user->user_type === UserType::Owner) {
            return true;
        }

        return in_array(
            $user->roleForSite($session->site_id),
            ["manager", "attendant"],
            true
        );
    }
}
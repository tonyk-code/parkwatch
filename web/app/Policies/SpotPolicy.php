<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\Site;
use App\Models\Spot;
use App\Models\User;

class SpotPolicy
{
    public function view(User $user, Spot $spot): bool
    {
        return $this->canAccessSpot($user, $spot);
    }

    public function override(User $user, Spot $spot): bool
    {
        if (!$this->canAccessSpot($user, $spot)) {
            return false;
        }

        return in_array(
            $user->user_type,
            [
                UserType::Owner,
                UserType::Staff,
            ],
            true
        );
    }

    private function canAccessSpot(User $user, Spot $spot): bool
    {
        $site = $spot->zone?->site;

        if (!$site || !$user->is_active) {
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
}
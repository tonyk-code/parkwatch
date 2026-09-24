<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function view(User $user, Site $site): bool
    {
        return $this->canAccess($user, $site);
    }

    public function update(User $user, Site $site): bool
    {
        if (!$this->canAccess($user, $site)) {
            return false;
        }

        if ($user->user_type === UserType::Owner) {
            return true;
        }

        return $user->roleForSite($site->id) === 'manager';
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->user_type === UserType::Owner
            && $user->organization_id === $site->organization_id;
    }

    private function canAccess(User $user, Site $site): bool
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
}
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active
            && $user->isCustomer();
    }

    public function create(User $user): bool
    {
        return $user->is_active
            && $user->isCustomer();
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->owner_user_id === $user->id;
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->owner_user_id === $user->id;
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->owner_user_id === $user->id;
    }
}
<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Property $property): bool
    {
        if ($property->status === PropertyStatus::Available) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Office) {
            return $user->offices()->where('id', $property->office_id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Office || $user->role === UserRole::Admin;
    }

    public function update(User $user, Property $property): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Office) {
            return $user->offices()->where('id', $property->office_id)->exists();
        }

        return false;
    }

    public function delete(User $user, Property $property): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Office) {
            return $user->offices()->where('id', $property->office_id)->exists();
        }

        return false;
    }

    public function moderate(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}

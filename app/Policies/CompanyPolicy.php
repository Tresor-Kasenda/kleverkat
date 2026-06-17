<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        if ($user->isAdmin() || $company->isManagedBy($user)) {
            return true;
        }

        return $company->team !== null && $user->belongsToTeam($company->team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        if ($user->isAdmin() || $company->isManagedBy($user)) {
            return true;
        }

        return $company->team !== null
            && $user->hasTeamPermission($company->team, TeamPermission::UpdateTeam);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }
}

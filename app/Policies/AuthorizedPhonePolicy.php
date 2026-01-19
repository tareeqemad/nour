<?php

namespace App\Policies;

use App\Models\AuthorizedPhone;
use App\Models\User;

class AuthorizedPhonePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // السوبر أدمن وسلطة الطاقة يمكنهم عرض الأرقام المصرح بها
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuthorizedPhone $authorizedPhone): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AuthorizedPhone $authorizedPhone): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AuthorizedPhone $authorizedPhone): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuthorizedPhone $authorizedPhone): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuthorizedPhone $authorizedPhone): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can delete all authorized phones.
     */
    public function deleteAll(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
    }
}

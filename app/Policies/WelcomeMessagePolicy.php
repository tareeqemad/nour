<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WelcomeMessage;

class WelcomeMessagePolicy
{
    /**
     * Determine whether the user can view any models.
     * فقط SuperAdmin و Admin يمكنهم رؤية الرسائل الترحيبية
     * المشغلون لا يمكنهم منح صلاحيات عليها
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WelcomeMessage $welcomeMessage): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WelcomeMessage $welcomeMessage): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }
}

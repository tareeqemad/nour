<?php

namespace App\Policies;

use App\Models\SmsTemplate;
use App\Models\User;

class SmsTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }
}

<?php

namespace App\Policies;

use App\Models\User;

class LogPolicy
{
    /**
     * Determine whether the user can view any logs.
     */
    public function viewAny(User $user): bool
    {
        // السوبر أدمن فقط
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        return $user->hasPermission('logs.view');
    }

    /**
     * Determine whether the user can clear logs.
     */
    public function clear(User $user): bool
    {
        // السوبر أدمن فقط
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        return $user->hasPermission('logs.clear');
    }

    /**
     * Determine whether the user can download logs.
     */
    public function download(User $user): bool
    {
        // السوبر أدمن فقط
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        return $user->hasPermission('logs.download');
    }
}

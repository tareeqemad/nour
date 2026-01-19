<?php

namespace App\Policies;

use App\Models\PermissionAuditLog;
use App\Models\User;

class PermissionAuditLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // السوبر أدمن والمشغل فقط
        if ($user->isSuperAdmin() || $user->isCompanyOwner()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        return $user->hasPermission('permissions.view') || $user->hasPermission('permissions.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PermissionAuditLog $permissionAuditLog): bool
    {
        // يجب أن يكون لديه صلاحية viewAny أولاً
        if (!$this->viewAny($user)) {
            return false;
        }

        // السوبر أدمن يرى كل شيء
        if ($user->isSuperAdmin()) {
            return true;
        }

        // المشغل يرى فقط سجلات موظفيه وفنييه
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && $permissionAuditLog->user) {
                return $operator->users->contains($permissionAuditLog->user);
            }
            return false;
        }

        // للآخرين: نفس صلاحية viewAny
        return $this->viewAny($user);
    }
}


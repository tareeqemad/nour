<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            // المشغل يمكنه رؤية سجلات المستخدمين التابعين له فقط
            $operator = $user->ownedOperators()->first();
            if ($operator && $auditLog->user_id) {
                $operatorUserIds = \App\Models\User::where(function ($q) use ($operator) {
                    $q->whereHas('operators', function ($qq) use ($operator) {
                        $qq->where('operators.id', $operator->id);
                    })->orWhere('id', $operator->owner_id);
                })->pluck('id')->toArray();
                
                return in_array($auditLog->user_id, $operatorUserIds);
            }
            // يمكنه رؤية سجلاته الخاصة
            return $auditLog->user_id === $user->id;
        }

        return false;
    }
}


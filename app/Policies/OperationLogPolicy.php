<?php

namespace App\Policies;

use App\Models\OperationLog;
use App\Models\User;

class OperationLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('operation_logs.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OperationLog $operationLog): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operation_logs.view')) {
            return false;
        }

        $operator = $operationLog->operator;
        if (! $operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من أن المشغل معتمد
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && !$operator->isApproved()) {
                return false;
            }
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('operation_logs.create')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OperationLog $operationLog): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operation_logs.update')) {
            return false;
        }

        $operator = $operationLog->operator;
        if (! $operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OperationLog $operationLog): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operation_logs.delete')) {
            return false;
        }

        $operator = $operationLog->operator;
        if (! $operator) {
            return false;
        }

        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OperationLog $operationLog): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OperationLog $operationLog): bool
    {
        return $user->isSuperAdmin();
    }
}

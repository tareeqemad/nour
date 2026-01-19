<?php

namespace App\Policies;

use App\Models\Operator;
use App\Models\User;

class OperatorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('operators.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Operator $operator): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('operators.view')) {
            // التحقق من العلاقة مع المشغل
            return $user->belongsToOperator($operator);
        }

        // Fallback للأدوار: CompanyOwner يمكنه رؤية مشغله الخاص
        if ($user->isCompanyOwner()) {
            return $user->ownsOperator($operator);
        }

        // Fallback للأدوار: Employee وTechnician يمكنهم رؤية المشغلين المرتبطين بهم
        if ($user->isEmployee() || $user->isTechnician()) {
            return $user->belongsToOperator($operator);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin لا يمكنه الإنشاء
        if ($user->isAdmin()) {
            return false;
        }

        // فقط SuperAdmin يمكنه إنشاء مشغل جديد
        return $user->isSuperAdmin() && $user->hasPermission('operators.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Operator $operator): bool
    {
        // Admin لا يمكنه التحديث
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operators.update')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Operator $operator): bool
    {
        // Admin لا يمكنه الحذف
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operators.delete')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Operator $operator): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Operator $operator): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can add employees to the operator.
     */
    public function addEmployee(User $user, Operator $operator): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can approve/activate the operator.
     * Only Super Admin, Admin, and Energy Authority with operators.approve permission can approve operators.
     */
    public function approve(User $user, Operator $operator): bool
    {
        // Super Admin always has permission to approve operators
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin and Energy Authority need operators.approve permission
        if ($user->isAdmin() || $user->isEnergyAuthority()) {
            return $user->hasPermission('operators.approve');
        }

        return false;
    }
}

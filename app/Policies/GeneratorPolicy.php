<?php

namespace App\Policies;

use App\Models\Generator;
use App\Models\User;

class GeneratorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // SuperAdmin و Admin و EnergyAuthority لديهم جميع الصلاحيات (Admin للعرض فقط)
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('generators.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Generator $generator): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('generators.view')) {
            // التحقق من العلاقة مع المشغل
            return $user->belongsToOperator($generator->operator);
        }

        // Fallback للأدوار: CompanyOwner وEmployee وTechnician يمكنهم رؤية المولدات المرتبطة بهم
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            return $user->belongsToOperator($generator->operator);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Company Owner can add generators even if not approved.
     * Users with generators.create permission who belong to an operator can also create.
     */
    public function create(User $user): bool
    {
        // Admin لا يمكنه الإنشاء
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Company Owner يمكنه إضافة المولدات حتى لو لم يكن معتمد
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            return $operator !== null;
        }

        // Energy Authority و Admin لا يمكنهم الإضافة
        if ($user->isEnergyAuthority() || $user->isAdmin()) {
            return false;
        }

        // التحقق من الصلاحية الديناميكية - يجب أن يكون المستخدم تابع لمشغل
        if ($user->hasPermission('generators.create')) {
            // يجب أن يكون تابع لمشغل واحد على الأقل
            return $user->operators()->exists() || $user->ownedOperators()->exists();
        }

        // Fallback للأدوار - Technician يمكنه الإضافة إذا كان تابع لمشغل
        if ($user->isTechnician()) {
            return $user->operators()->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Generator $generator): bool
    {
        // Admin لا يمكنه التحديث
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('generators.update')) {
            // التحقق من العلاقة مع المشغل
            return $user->belongsToOperator($generator->operator);
        }

        // Fallback للأدوار: CompanyOwner وTechnician يمكنهم التعديل إذا كانت المولد تتبع مشغلهم
        if ($user->isCompanyOwner() || $user->isTechnician()) {
            return $user->belongsToOperator($generator->operator);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Generator $generator): bool
    {
        // Admin لا يمكنه الحذف
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generators.delete')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل (يجب أن يكون صاحب المشغل)
        return $user->ownsOperator($generator->operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Generator $generator): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Generator $generator): bool
    {
        return $user->isSuperAdmin();
    }
}

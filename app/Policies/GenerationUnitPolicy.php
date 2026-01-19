<?php

namespace App\Policies;

use App\Models\GenerationUnit;
use App\Models\User;

class GenerationUnitPolicy
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
        if ($user->hasPermission('generation_units.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GenerationUnit $generationUnit): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // Company Owner يمكنه رؤية وحدات التوليد الخاصة به حتى لو لم يكن لديه الصلاحية الديناميكية
        if ($user->isCompanyOwner()) {
            return $user->ownsOperator($generationUnit->operator);
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generation_units.view')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->belongsToOperator($generationUnit->operator);
    }

    /**
     * Determine whether the user can create models.
     * صاحب المشغل (CompanyOwner) أو أي يوزر تابع للمشغل لديه صلاحية generation_units.create يمكنه إضافة وحدات التوليد.
     */
    public function create(User $user): bool
    {
        // Company Owner يمكنه إضافة وحدات التوليد
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            return $operator !== null;
        }

        // التحقق من الصلاحية الديناميكية للمستخدمين التابعين للمشغل (Employee/Technician)
        if ($user->hasPermission('generation_units.create')) {
            // يجب أن يكون المستخدم مرتبط بمشغل واحد على الأقل
            return $user->operators()->exists();
        }

        // جميع الأدوار الأخرى لا يمكنها الإنشاء
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * صاحب المشغل (CompanyOwner) أو أي يوزر تابع للمشغل لديه صلاحية generation_units.update يمكنه تعديل وحدات التوليد.
     */
    public function update(User $user, GenerationUnit $generationUnit): bool
    {
        // Company Owner يمكنه تعديل وحدات التوليد الخاصة به
        if ($user->isCompanyOwner()) {
            return $user->ownsOperator($generationUnit->operator);
        }

        // التحقق من الصلاحية الديناميكية للمستخدمين التابعين للمشغل (Employee/Technician)
        if ($user->hasPermission('generation_units.update')) {
            // التحقق من العلاقة مع المشغل
            return $user->belongsToOperator($generationUnit->operator);
        }

        // جميع الأدوار الأخرى لا يمكنها التعديل
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GenerationUnit $generationUnit): bool
    {
        // Admin لا يمكنه الحذف
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Company Owner يمكنه حذف وحدات التوليد الخاصة به حتى لو لم يكن معتمد
        if ($user->isCompanyOwner()) {
            return $user->ownsOperator($generationUnit->operator);
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generation_units.delete')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل (يجب أن يكون صاحب المشغل)
        return $user->ownsOperator($generationUnit->operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GenerationUnit $generationUnit): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GenerationUnit $generationUnit): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can generate QR code for the generation unit.
     * Only the operator owner (CompanyOwner) can generate QR codes.
     */
    public function generateQrCode(User $user, GenerationUnit $generationUnit): bool
    {
        // فقط صاحب المشغل يمكنه إنشاء QR Code
        return $user->isCompanyOwner() && $user->ownsOperator($generationUnit->operator);
    }
}


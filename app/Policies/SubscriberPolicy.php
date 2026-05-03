<?php

namespace App\Policies;

use App\Models\Subscriber;
use App\Models\User;

class SubscriberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // SuperAdmin و Admin و EnergyAuthority لديهم جميع الصلاحيات
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('subscribers.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subscriber $subscriber): bool
    {
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('subscribers.view')) {
            // التحقق من العلاقة مع المشغل عبر وحدات التوليد
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
        }

        // Fallback للأدوار
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
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

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Company Owner يمكنه إضافة المشتركين
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            return $operator !== null;
        }

        // Energy Authority لا يمكنه الإضافة
        if ($user->isEnergyAuthority()) {
            return false;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('subscribers.create')) {
            return $user->operators()->exists() || $user->ownedOperators()->exists();
        }

        // Fallback للأدوار
        if ($user->isTechnician() || ($user->isEmployee() && $user->hasPermission('subscribers.create'))) {
            return $user->operators()->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subscriber $subscriber): bool
    {
        // Admin لا يمكنه التحديث
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('subscribers.update')) {
            // التحقق من العلاقة مع المشغل
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
        }

        // Fallback للأدوار
        if ($user->isCompanyOwner() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subscriber $subscriber): bool
    {
        // فقط SuperAdmin يمكنه الحذف
        // المشغل لا يمكنه حذف المشتركين
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Subscriber $subscriber): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Subscriber $subscriber): bool
    {
        return $user->isSuperAdmin();
    }
}

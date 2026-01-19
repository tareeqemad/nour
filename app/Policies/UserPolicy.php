<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // SuperAdmin: إدارة كل المستخدمين
        if ($user->isSuperAdmin()) {
            return true;
        }

        // EnergyAuthority (سلطة الطاقة): يمكنه إدارة المستخدمين تحت سلطته
        if ($user->isEnergyAuthority()) {
            return true;
        }

        // CompanyOwner: يشوف فقط موظفينه/فنييه عبر Users module
        // يجب أن يكون المشغل معتمد حتى يرى صفحة المستخدمين
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && !$operator->isApproved()) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // (اختياري) المستخدم يشوف نفسه
        if ($user->id === $model->id) {
            return true;
        }

        // EnergyAuthority (سلطة الطاقة): يمكنه رؤية جميع المستخدمين
        if ($user->isEnergyAuthority()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            // المشغل يمكنه رؤية الموظفين والفنيين والمستخدمين بأدوار مخصصة التابعين له
            if (! $model->isEmployee() && ! $model->isTechnician() && ! $model->hasCustomRole()) {
                return false;
            }

            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                return false;
            }

            return $model->operators()
                ->where('operators.id', $operator->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // EnergyAuthority (سلطة الطاقة): يمكنه إضافة مستخدمين (admin, energy_authority, company_owner, employee, technician)
        if ($user->isEnergyAuthority()) {
            return true;
        }

        // CompanyOwner ينشئ موظف/فني فقط (التحقق النهائي بالداتا بالـ Controller/Request)
        if ($user->isCompanyOwner()) {
            // التحقق من أن المشغل معتمد
            $operator = $user->ownedOperators()->first();
            if ($operator && !$operator->isApproved()) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // EnergyAuthority (سلطة الطاقة): يمكنه تحديث المشغلين فقط
        if ($user->isEnergyAuthority()) {
            return $model->isCompanyOwner();
        }

        if ($user->isCompanyOwner()) {
            // المشغل يمكنه تحديث أي مستخدم تابع له (بما في ذلك الأدوار المخصصة التي أنشأها)
            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                return false;
            }

            return $model->operators()
                ->where('operators.id', $operator->id)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            // المشغل يمكنه حذف الموظفين والفنيين والمستخدمين بأدوار مخصصة التابعين له
            if (! $model->isEmployee() && ! $model->isTechnician() && ! $model->hasCustomRole()) {
                return false;
            }

            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                return false;
            }

            return $model->operators()
                ->where('operators.id', $operator->id)
                ->exists();
        }

        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\EmployeeDiscountRate;
use App\Models\User;

class EmployeeDiscountRatePolicy
{
    /**
     * Determine whether the user can view any models.
     * متاح فقط للسوبر أدمن أو من لديه صلاحية ديناميكية
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission('employee_discount_rates.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmployeeDiscountRate $rate): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission('employee_discount_rates.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission('employee_discount_rates.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmployeeDiscountRate $rate): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission('employee_discount_rates.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmployeeDiscountRate $rate): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission('employee_discount_rates.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmployeeDiscountRate $rate): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmployeeDiscountRate $rate): bool
    {
        return $user->isSuperAdmin();
    }
}

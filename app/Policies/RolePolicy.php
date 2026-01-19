<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority() || $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        if ($user->isSuperAdmin()) {
            return true; // Super Admin can see all roles
        }

        if ($user->isAdmin()) {
            // Admin can see system roles and general roles (operator_id = null) created by Admin or Super Admin
            if ($role->is_system) {
                return true;
            }
            if ($role->operator_id === null) {
                return $role->created_by === null || 
                       ($role->creator && ($role->creator->isAdmin() || $role->creator->isSuperAdmin()));
            }
            return false; // Admin cannot see operator-specific roles
        }

        if ($user->isEnergyAuthority()) {
            // Energy Authority can see:
            // 1. System roles
            // 2. General roles created by Energy Authority, Super Admin, or Admin (for reference)
            // 3. Operator-specific roles created by Energy Authority
            if ($role->is_system) {
                return true;
            }
            // Energy Authority can see roles they created (general or operator-specific)
            if ($role->created_by === $user->id) {
                return true;
            }
            // Energy Authority can also see general roles (operator_id = null) created by Super Admin or Admin (for reference)
            if ($role->operator_id === null) {
                return $role->created_by === null || 
                       ($role->creator && ($role->creator->isSuperAdmin() || $role->creator->isAdmin()));
            }
            return false;
        }

        if ($user->isCompanyOwner()) {
            // Company Owner can ONLY see roles they created for their operator
            // No system roles, no general roles, no roles from Energy Authority, no roles from other operators
            if ($role->is_system) {
                return false; // Cannot see system roles
            }
            // Company Owner can only see roles they created for their own operator
            if ($role->operator_id && $role->created_by === $user->id) {
                $operator = $role->operator;
                return $operator && $user->ownsOperator($operator);
            }
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super Admin and Admin can create general roles
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Energy Authority can create:
        // 1. General roles (operator_id = null) - for the entire system
        // 2. Operator-specific roles (operator_id = specific operator) - for specific operators
        if ($user->isEnergyAuthority()) {
            return true;
        }

        // Company Owner needs approved operator to create roles
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && !$operator->isApproved()) {
                return false;
            }
            return $operator !== null; // Must have an operator
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        // Admin يمكنه تحديث الأدوار غير النظامية فقط
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin can only update non-system general roles created by Admin or Super Admin
            if ($role->is_system) {
                return false;
            }
            // Admin can update general roles (operator_id = null) created by Admin or Super Admin
            if ($role->operator_id === null) {
                return $role->created_by === null || 
                       ($role->creator && ($role->creator->isAdmin() || $role->creator->isSuperAdmin()));
            }
            // Admin cannot update operator-specific roles
            return false;
        }

        if ($user->isEnergyAuthority()) {
            // Energy Authority can update roles they created (general or operator-specific)
            if ($role->is_system) {
                return false; // Cannot update system roles
            }
            return $role->created_by === $user->id;
        }

        if ($user->isCompanyOwner()) {
            // Company Owner can only update roles they created for their operator
            if ($role->is_system || !$role->operator_id || $role->created_by !== $user->id) {
                return false;
            }
            $operator = $role->operator;
            return $operator && $user->ownsOperator($operator);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        // لا يمكن حذف الأدوار النظامية
        if ($role->is_system) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin can delete general roles (operator_id = null) created by Admin or Super Admin
        if ($user->isAdmin()) {
            // Admin can delete general roles created by Admin or Super Admin
            if ($role->operator_id === null) {
                return $role->created_by === null || 
                       ($role->creator && ($role->creator->isAdmin() || $role->creator->isSuperAdmin()));
            }
            // Admin cannot delete operator-specific roles
            return false;
        }

        if ($user->isEnergyAuthority()) {
            // Energy Authority can only delete roles they created
            return $role->created_by === $user->id;
        }

        if ($user->isCompanyOwner()) {
            // Company Owner can only delete roles they created for their operator
            if (!$role->operator_id || $role->created_by !== $user->id) {
                return false;
            }
            $operator = $role->operator;
            return $operator && $user->ownsOperator($operator);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}

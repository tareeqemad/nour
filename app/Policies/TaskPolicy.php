<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        // SuperAdmin, Admin, EnergyAuthority يمكنهم رؤية جميع المهام
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return $user->hasPermission('tasks.view');
        }

        // CompanyOwner يمكنه رؤية المهام المتعلقة بمشغله
        if ($user->isCompanyOwner()) {
            return $user->hasPermission('tasks.view');
        }

        // Technician و CivilDefense يمكنهم رؤية المهام المكلفين بها فقط
        if ($user->isTechnician() || $user->isCivilDefense()) {
            return $user->hasPermission('tasks.view');
        }

        return false;
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // SuperAdmin, Admin, EnergyAuthority يمكنهم رؤية جميع المهام
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return $user->hasPermission('tasks.view');
        }

        // CompanyOwner يمكنه رؤية المهام المتعلقة بمشغله
        if ($user->isCompanyOwner()) {
            if (!$user->hasPermission('tasks.view')) {
                return false;
            }
            // التحقق من أن المهمة متعلقة بمشغل المستخدم
            $operator = $user->ownedOperators()->first();
            return $operator && $task->operator_id === $operator->id;
        }

        // Technician و CivilDefense يمكنهم رؤية المهام المكلفين بها فقط
        if ($user->isTechnician() || $user->isCivilDefense()) {
            return $user->hasPermission('tasks.view') && $task->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        // فقط SuperAdmin, Admin, EnergyAuthority يمكنهم إنشاء مهام
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return $user->hasPermission('tasks.create');
        }

        return false;
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // SuperAdmin, Admin, EnergyAuthority يمكنهم تحديث جميع المهام
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return $user->hasPermission('tasks.update');
        }

        // CompanyOwner يمكنه تحديث المهام المتعلقة بمشغله
        if ($user->isCompanyOwner()) {
            if (!$user->hasPermission('tasks.update')) {
                return false;
            }
            $operator = $user->ownedOperators()->first();
            return $operator && $task->operator_id === $operator->id;
        }

        // Technician و CivilDefense يمكنهم تحديث المهام المكلفين بها فقط
        if ($user->isTechnician() || $user->isCivilDefense()) {
            return $user->hasPermission('tasks.update') && $task->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // فقط SuperAdmin, Admin, EnergyAuthority يمكنهم حذف المهام
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return $user->hasPermission('tasks.delete');
        }

        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\MaintenanceRecord;
use App\Models\User;

class MaintenanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->hasPermission('maintenance_records.view')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    public function view(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if (! $user->hasPermission('maintenance_records.view')) {
            return false;
        }

        // التحقق من وجود المولد (حتى لو كان محذوفاً soft delete)
        if (!$maintenanceRecord->generator) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator;
        if (!$operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    public function create(User $user): bool
    {
        // Admin (سلطة الطاقة) يمكنه إنشاء سجلات الصيانة
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        // التحقق من أن المشغل معتمد
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && !$operator->isApproved()) {
                return false;
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operator = $user->operators()->first();
            if ($operator && !$operator->isApproved()) {
                return false;
            }
        }

        if ($user->hasPermission('maintenance_records.create')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    public function update(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        // Admin (سلطة الطاقة) يمكنه تحديث سجلات الصيانة
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('maintenance_records.update')) {
            return false;
        }

        // التحقق من وجود المولد (حتى لو كان محذوفاً soft delete)
        if (!$maintenanceRecord->generator) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator;
        if (!$operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    public function delete(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('maintenance_records.delete')) {
            return false;
        }

        // التحقق من وجود المولد (حتى لو كان محذوفاً soft delete)
        if (!$maintenanceRecord->generator) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator;
        if (!$operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }
}

<?php

namespace App\Policies;

use App\Models\FuelEfficiency;
use App\Models\User;

class FuelEfficiencyPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->hasPermission('fuel_efficiencies.view')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    public function view(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if (! $user->hasPermission('fuel_efficiencies.view')) {
            return false;
        }

        // التحقق من وجود المولد (حتى لو كان محذوفاً soft delete)
        if (!$fuelEfficiency->generator) {
            return false;
        }

        $operator = $fuelEfficiency->generator->operator;
        if (!$operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

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
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operator = $user->operators()->first();
            if ($operator && !$operator->isApproved()) {
                return false;
            }
        }

        if ($user->hasPermission('fuel_efficiencies.create')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee();
    }

    public function update(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('fuel_efficiencies.update')) {
            return false;
        }

        // التحقق من وجود المولد (حتى لو كان محذوفاً soft delete)
        if (!$fuelEfficiency->generator) {
            return false;
        }

        $operator = $fuelEfficiency->generator->operator;
        if (!$operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    public function delete(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('fuel_efficiencies.delete')) {
            return false;
        }

        // التحقق من وجود المولد (حتى لو كان محذوفاً soft delete)
        if (!$fuelEfficiency->generator) {
            return false;
        }

        $operator = $fuelEfficiency->generator->operator;
        if (!$operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }
}

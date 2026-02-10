<?php

namespace App\Policies;

use App\Models\InspectionViolationCase;
use App\Models\User;

class InspectionViolationCasePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('inspection_violation_cases.view');
    }

    public function view(User $user, InspectionViolationCase $case): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        if (!$user->hasPermission('inspection_violation_cases.view')) {
            return false;
        }
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            return $case->operator_id && $userOperatorIds->contains($case->operator_id);
        }
        return true;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('inspection_violation_cases.create');
    }

    public function update(User $user, InspectionViolationCase $case): bool
    {
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        if (!$user->hasPermission('inspection_violation_cases.update')) {
            return false;
        }
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            return $case->operator_id && $userOperatorIds->contains($case->operator_id);
        }
        return true;
    }

    public function delete(User $user, InspectionViolationCase $case): bool
    {
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('inspection_violation_cases.delete');
    }
}

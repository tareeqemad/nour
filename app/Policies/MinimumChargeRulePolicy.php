<?php

namespace App\Policies;

use App\Models\MinimumChargeRule;
use App\Models\User;

class MinimumChargeRulePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            return true;
        }
        return $user->hasPermission('minimum_charge_rules.view');
    }

    public function update(User $user, MinimumChargeRule $rule): bool
    {
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // CompanyOwner يعدل قواعد مشغّله فقط
        if ($user->isCompanyOwner()) {
            $operatorIds = $user->ownedOperators()->pluck('id')->toArray();
            return in_array($rule->operator_id, $operatorIds);
        }

        return $user->hasPermission('minimum_charge_rules.update');
    }
}

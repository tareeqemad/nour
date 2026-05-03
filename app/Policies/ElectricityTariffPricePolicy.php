<?php

namespace App\Policies;

use App\Models\ElectricityTariffPrice;
use App\Models\User;

class ElectricityTariffPricePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // السوبر أدمن والأدمن وسلطة الطاقة يمكنهم رؤية كل شيء
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        return $user->hasPermission('electricity_tariff_prices.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        // السوبر أدمن والأدمن وسلطة الطاقة يمكنهم رؤية كل شيء
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        // الأسعار عامة متاحة للجميع الذين لديهم صلاحية view
        return $user->hasPermission('electricity_tariff_prices.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // السوبر أدمن وسلطة الطاقة يمكنهم إنشاء الأسعار
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // الأدمن يمكنهم الاستعلام فقط (view only)
        if ($user->isAdmin()) {
            return false;
        }

        // التحقق من الصلاحية الديناميكية
        return $user->hasPermission('electricity_tariff_prices.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        // السوبر أدمن وسلطة الطاقة يمكنهم تحديث الأسعار
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // الأدمن يمكنهم الاستعلام فقط (view only)
        if ($user->isAdmin()) {
            return false;
        }

        // التحقق من الصلاحية الديناميكية
        // الأسعار عامة يمكن تحديثها فقط من قبل السوبر أدمن وسلطة الطاقة
        return $user->hasPermission('electricity_tariff_prices.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        // السوبر أدمن وسلطة الطاقة يمكنهم حذف الأسعار
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // الأدمن يمكنهم الاستعلام فقط (view only)
        if ($user->isAdmin()) {
            return false;
        }

        // التحقق من الصلاحية الديناميكية
        // الأسعار عامة يمكن حذفها فقط من قبل السوبر أدمن وسلطة الطاقة
        return $user->hasPermission('electricity_tariff_prices.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        return $user->isSuperAdmin() || $user->isEnergyAuthority();
    }
}

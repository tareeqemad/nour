<?php

namespace App\Policies;

use App\Models\ElectricityTariffPrice;
use App\Models\Operator;
use App\Models\User;

class ElectricityTariffPricePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // السوبر أدمن والأدمن يمكنهم رؤية كل شيء
        if ($user->isSuperAdmin() || $user->isAdmin()) {
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
        // السوبر أدمن والأدمن يمكنهم رؤية كل شيء
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('electricity_tariff_prices.view')) {
            return false;
        }

        // المشغل يمكنه رؤية أسعار مشغله فقط
        return $user->belongsToOperator($tariffPrice->operator);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // الأدمن يمكنهم الاستعلام فقط (view only)
        if ($user->isAdmin()) {
            return false;
        }

        // السوبر أدمن يمكنه إنشاء كل شيء
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        return $user->hasPermission('electricity_tariff_prices.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        // الأدمن يمكنهم الاستعلام فقط (view only)
        if ($user->isAdmin()) {
            return false;
        }

        // السوبر أدمن يمكنه تحديث كل شيء
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('electricity_tariff_prices.update')) {
            return false;
        }

        // المشغل يمكنه تحديث أسعار مشغله فقط
        return $user->ownsOperator($tariffPrice->operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        // الأدمن يمكنهم الاستعلام فقط (view only)
        if ($user->isAdmin()) {
            return false;
        }

        // السوبر أدمن يمكنه حذف كل شيء
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('electricity_tariff_prices.delete')) {
            return false;
        }

        // المشغل يمكنه حذف أسعار مشغله فقط
        return $user->ownsOperator($tariffPrice->operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ElectricityTariffPrice $tariffPrice): bool
    {
        return $user->isSuperAdmin();
    }
}

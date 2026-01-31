<?php

namespace App\Policies;

use App\Models\MeterReading;
use App\Models\User;

class MeterReadingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        if ($user->hasPermission('meter_readings.view')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MeterReading $meterReading): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        if ($user->hasPermission('meter_readings.view')) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $meterReading->subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
        }

        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $meterReading->subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            return $operator !== null;
        }

        if ($user->isEnergyAuthority()) {
            return false;
        }

        if ($user->hasPermission('meter_readings.create')) {
            return $user->operators()->exists() || $user->ownedOperators()->exists();
        }

        if ($user->isTechnician() || ($user->isEmployee() && $user->hasPermission('meter_readings.create'))) {
            return $user->operators()->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MeterReading $meterReading): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasPermission('meter_readings.update')) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $meterReading->subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
        }

        if ($user->isCompanyOwner() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->merge(
                $user->ownedOperators()->pluck('id')
            )->unique();
            
            $subscriberOperatorIds = $meterReading->subscriber->generationUnits()
                ->pluck('operator_id')
                ->unique();
            
            return $userOperatorIds->intersect($subscriberOperatorIds)->isNotEmpty();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MeterReading $meterReading): bool
    {
        // فقط SuperAdmin يمكنه الحذف
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MeterReading $meterReading): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MeterReading $meterReading): bool
    {
        return $user->isSuperAdmin();
    }
}

<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية (شجرة الصلاحيات)
        if ($user->hasPermission('invoices.view')) {
            return true;
        }

        return false;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        $userOperatorIds = $user->operators()->pluck('operators.id')
            ->merge($user->ownedOperators()->pluck('id'))->unique();

        $invoiceOperatorIds = $invoice->subscriber->generationUnits()
            ->pluck('operator_id')->unique();

        return $userOperatorIds->intersect($invoiceOperatorIds)->isNotEmpty();
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        return $user->isCompanyOwner() || $user->isEmployee();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if (!$invoice->isEditable()) {
            return false;
        }
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        if ($user->isCompanyOwner() || $user->isEmployee()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')
                ->merge($user->ownedOperators()->pluck('id'))->unique();
            $invoiceOperatorIds = $invoice->subscriber->generationUnits()
                ->pluck('operator_id')->unique();
            return $userOperatorIds->intersect($invoiceOperatorIds)->isNotEmpty();
        }
        return false;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        if (!$invoice->isEditable()) {
            return false;
        }
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        if (!$invoice->isIssuable()) {
            return false;
        }
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isCompanyOwner() || $user->isEmployee();
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        if (!$invoice->isCancellable()) {
            return false;
        }
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function viewReports(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            return true;
        }
        return $user->hasPermission('invoice_reports.view');
    }
}

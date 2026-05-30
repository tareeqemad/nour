<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasGlobalAccountingAccess()) {
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
        if ($user->hasGlobalAccountingAccess()) {
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
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isGeneralAccountant()) {
            return true;
        }
        return $user->isCompanyOwner()
            || $user->isEmployee()
            || $user->hasPermission('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if (!$invoice->isEditable()) {
            return false;
        }
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isGeneralAccountant()) {
            return true;
        }
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->hasPermission('invoices.update')) {
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

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        return $user->hasPermission('invoices.delete')
            && $this->invoiceIsInUserScope($user, $invoice);
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        if (!$invoice->isIssuable()) {
            return false;
        }
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isGeneralAccountant()) {
            return true;
        }

        return $user->isCompanyOwner()
            || $user->isEmployee()
            || $user->hasPermission('invoices.issue');
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        if (!$invoice->isCancellable()) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        return $user->hasPermission('invoices.delete')
            && $this->invoiceIsInUserScope($user, $invoice);
    }

    public function viewReports(User $user): bool
    {
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }
        if ($user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician()) {
            return true;
        }
        return $user->hasPermission('invoice_reports.view');
    }

    private function invoiceIsInUserScope(User $user, Invoice $invoice): bool
    {
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }

        $userOperatorIds = collect($user->getScopedOperatorIds());
        if ($userOperatorIds->isEmpty() || !$invoice->subscriber) {
            return false;
        }

        $invoiceOperatorIds = $invoice->subscriber->generationUnits()
            ->pluck('operator_id')
            ->unique();

        return $userOperatorIds->intersect($invoiceOperatorIds)->isNotEmpty();
    }
}

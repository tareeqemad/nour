<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * عرض قائمة الدفعات - نفس صلاحية عرض الفواتير
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }
        return $user->isCompanyOwner()
            || $user->isEmployee()
            || $user->isTechnician()
            || $user->hasPermission('invoices.view');
    }

    /**
     * عرض دفعة محددة
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasGlobalAccountingAccess()) {
            return true;
        }

        $userOperatorIds = $user->operators()->pluck('operators.id')
            ->merge($user->ownedOperators()->pluck('id'))->unique();

        $invoiceOperatorIds = $payment->invoice->subscriber->generationUnits()
            ->pluck('operator_id')->unique();

        return $userOperatorIds->intersect($invoiceOperatorIds)->isNotEmpty();
    }

    /**
     * تسجيل دفعة جديدة - يشترط أن تكون الفاتورة غير مسددة بالكامل
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isGeneralAccountant()) {
            return true;
        }
        return $user->isCompanyOwner()
            || $user->isEmployee()
            || $user->hasAnyPermission(['invoices.update', 'invoices.import_payments']);
    }

    /**
     * تسجيل دفعة على فاتورة بعينها
     */
    public function createForInvoice(User $user, Invoice $invoice): bool
    {
        if (!$this->create($user)) {
            return false;
        }

        // يجب أن تكون الفاتورة مُصدَرة أو مسددة جزئياً
        if (!in_array($invoice->invoice_status, [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL])) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isGeneralAccountant()) {
            return true;
        }

        $userOperatorIds = $user->operators()->pluck('operators.id')
            ->merge($user->ownedOperators()->pluck('id'))->unique();

        $invoiceOperatorIds = $invoice->subscriber->generationUnits()
            ->pluck('operator_id')->unique();

        return $userOperatorIds->intersect($invoiceOperatorIds)->isNotEmpty();
    }

    /**
     * حذف دفعة - SuperAdmin/Admin فقط
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }
}

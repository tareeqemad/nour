<?php

namespace App\Http\Requests\Admin;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // التحقق يتم في الـ Controller عبر Policy
    }

    public function rules(): array
    {
        return [
            // invoice_id يأتي من الـ URL عبر Route Model Binding وليس من الفورم
            'payment_date'   => ['required', 'date', 'before_or_equal:today'],
            'amount_paid'    => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:' . implode(',', array_keys(Payment::methodLabels()))],
            'receipt_number' => ['nullable', 'string', 'max:60'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_date'   => 'تاريخ السداد',
            'amount_paid'    => 'المبلغ المسدد',
            'payment_method' => 'طريقة الدفع',
            'receipt_number' => 'رقم الإيصال',
            'notes'          => 'ملاحظات',
        ];
    }
}

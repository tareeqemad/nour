<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Invoice::class);
    }

    public function rules(): array
    {
        return [
            'subscriber_id'           => ['required', 'exists:subscribers,id'],
            'meter_reading_id'        => ['nullable', 'exists:meter_readings,id'],
            'invoice_date'            => ['required', 'date'],
            'consumption_period_days' => ['required', 'integer', 'min:0'],
            'consumption_kwh'         => ['required', 'numeric'],
            'price_per_kwh'           => ['required', 'numeric', 'min:0'],
            'discount_rate'           => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_amount'          => ['required', 'numeric', 'min:0'],
            'minimum_charge'          => ['required', 'numeric', 'min:0'],
            'previous_balance'        => ['required', 'numeric'],
            'total_amount'            => ['required', 'numeric'],
            'due_date'                => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'subscriber_id'           => 'رقم الاشتراك',
            'meter_reading_id'        => 'رقم القراءة',
            'invoice_date'            => 'تاريخ الفاتورة',
            'consumption_period_days' => 'فترة الاستهلاك',
            'consumption_kwh'         => 'الاستهلاك kWh',
            'price_per_kwh'           => 'سعر الكيلوواط',
            'discount_rate'           => 'نسبة الخصم',
            'invoice_amount'          => 'ثمن الاستهلاك',
            'minimum_charge'          => 'الحد الأدنى',
            'previous_balance'        => 'الرصيد السابق',
            'total_amount'            => 'المبلغ المطلوب',
            'due_date'                => 'تاريخ الاستحقاق',
        ];
    }
}
